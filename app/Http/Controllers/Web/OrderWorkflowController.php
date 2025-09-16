<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderWorkflowController extends Controller
{
    protected $workflowService;

    public function __construct(OrderWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display workflow dashboard
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $branchId = $request->get('branch_id', $user->branch_id);

        // Get workflow statistics
        $stats = $this->workflowService->getWorkflowStatistics([
            'branch_id' => $branchId,
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date')
        ]);

        // Get average processing times
        $processingTimes = $this->workflowService->getAverageProcessingTimes([
            'branch_id' => $branchId,
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date')
        ]);

        // Get recent orders by status
        $recentOrders = [];
        foreach (['pending', 'processing', 'ready', 'delivered'] as $status) {
            $recentOrders[$status] = Order::with(['customer', 'orderItems.product'])
                ->where('status', $status)
                ->when($branchId, function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId);
                })
                ->latest()
                ->limit(5)
                ->get();
        }

        return view('orders.workflow.dashboard', compact('stats', 'processingTimes', 'recentOrders'));
    }

    /**
     * Display order workflow details
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'orderItems.product', 'workflowLogs.user', 'qualityCheckedBy']);
        
        $possibleTransitions = $order->getPossibleTransitions();
        $workflowHistory = $order->getWorkflowHistory();
        $statusInfo = $order->getWorkflowStatusInfo();

        return view('orders.workflow.show', compact('order', 'possibleTransitions', 'workflowHistory', 'statusInfo'));
    }

    /**
     * Transition order to new status
     */
    public function transition(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'notes' => 'nullable|string|max:1000',
            'metadata' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $success = $order->transitionTo(
                $request->status,
                Auth::user(),
                $request->notes,
                $request->metadata ?? []
            );

            if ($success) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Order status updated successfully',
                    'data' => [
                        'order' => $order->fresh(['customer', 'orderItems.product']),
                        'possible_transitions' => $order->getPossibleTransitions()
                    ]
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update order status'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Bulk transition orders
     */
    public function bulkTransition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|string',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($request->order_ids as $orderId) {
            try {
                $order = Order::findOrFail($orderId);
                $success = $order->transitionTo(
                    $request->status,
                    Auth::user(),
                    $request->notes
                );

                if ($success) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Failed to update order {$order->order_number}";
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Order {$orderId}: " . $e->getMessage();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Updated {$successCount} orders successfully",
            'data' => [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]
        ]);
    }

    /**
     * Mark order as quality checked
     */
    public function qualityCheck(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'passed' => 'required|boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->passed) {
                $order->markQualityChecked(Auth::user());
                
                // Add to workflow metadata
                $metadata = $order->workflow_metadata ?? [];
                $metadata['quality_check'] = [
                    'passed' => true,
                    'checked_by' => Auth::user()->name,
                    'checked_at' => now()->toISOString(),
                    'notes' => $request->notes
                ];
                $order->update(['workflow_metadata' => $metadata]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Order passed quality check'
                ]);
            } else {
                // If quality check failed, transition back to processing
                $order->transitionTo('processing', Auth::user(), 'Quality check failed: ' . $request->notes);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Order sent back for reprocessing'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get workflow analytics
     */
    public function analytics(Request $request)
    {
        $branchId = $request->get('branch_id', Auth::user()->branch_id);
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $stats = $this->workflowService->getWorkflowStatistics([
            'branch_id' => $branchId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $processingTimes = $this->workflowService->getAverageProcessingTimes([
            'branch_id' => $branchId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        // Get daily order counts
        $dailyOrders = Order::selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        // Get status transition counts
        $transitions = \DB::table('order_workflow_logs')
            ->selectRaw('from_status, to_status, COUNT(*) as count')
            ->whereBetween('transitioned_at', [$startDate, $endDate])
            ->groupBy('from_status', 'to_status')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'statistics' => $stats,
                'processing_times' => $processingTimes,
                'daily_orders' => $dailyOrders,
                'transitions' => $transitions
            ]
        ]);
    }

    /**
     * Get orders by status
     */
    public function byStatus(Request $request, $status)
    {
        $user = Auth::user();
        $branchId = $request->get('branch_id', $user->branch_id);

        $orders = Order::with(['customer', 'orderItems.product', 'workflowLogs' => function ($query) {
            $query->latest()->limit(1);
        }])
        ->where('status', $status)
        ->when($branchId, function ($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })
        ->when($request->has('priority'), function ($query) use ($request) {
            return $query->where('priority', $request->priority);
        })
        ->when($request->has('urgent'), function ($query) use ($request) {
            return $query->where('is_urgent', $request->urgent);
        })
        ->orderBy('priority', 'desc')
        ->orderBy('is_urgent', 'desc')
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return view('orders.workflow.by-status', compact('orders', 'status'));
    }

    /**
     * Update order priority
     */
    public function updatePriority(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'priority' => 'required|in:low,normal,high,urgent',
            'is_urgent' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update([
            'priority' => $request->priority,
            'is_urgent' => $request->boolean('is_urgent', false)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Order priority updated successfully'
        ]);
    }
}