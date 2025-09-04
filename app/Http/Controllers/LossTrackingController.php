<?php

namespace App\Http\Controllers;

use App\Models\LossTracking;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Batch;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LossTrackingController extends Controller
{
    /**
     * Display a listing of loss records.
     */
    public function index(Request $request)
    {
        $query = LossTracking::with(['product', 'branch', 'batch', 'user']);

        // Filter by loss type
        if ($request->has('loss_type')) {
            $query->where('loss_type', $request->loss_type);
        }

        // Filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by branch
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by financial impact
        if ($request->has('min_loss_amount')) {
            $query->where('financial_loss', '>=', $request->min_loss_amount);
        }

        $losses = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $losses
        ]);
    }

    /**
     * Store a new loss record.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'batch_id' => 'nullable|exists:batches,id',
            'loss_type' => 'required|in:weight_loss,water_loss,wastage,complimentary,damage,theft,expiry',
            'quantity_lost' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'initial_quantity' => 'nullable|numeric|min:0',
            'final_quantity' => 'nullable|numeric|min:0',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $product = Product::find($request->product_id);
            $branch = Branch::find($request->branch_id);
            $user = auth()->user();

            // Calculate financial loss
            $financialLoss = $request->quantity_lost * $product->selling_price;

            // Create loss record
            $lossRecord = LossTracking::create([
                'product_id' => $request->product_id,
                'branch_id' => $request->branch_id,
                'batch_id' => $request->batch_id,
                'loss_type' => $request->loss_type,
                'quantity_lost' => $request->quantity_lost,
                'financial_loss' => $financialLoss,
                'reason' => $request->reason,
                'user_id' => $user->id,
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'initial_quantity' => $request->initial_quantity,
                'final_quantity' => $request->final_quantity,
            ]);

            // Update stock if not already updated by another process
            if (!in_array($request->loss_type, ['complimentary'])) {
                $currentStock = $product->getCurrentStock($branch->id);
                $product->updateStock($branch->id, $currentStock - $request->quantity_lost);
                
                // Check and update online availability
                $inventoryService = new InventoryService();
                $inventoryService->checkAndUpdateOnlineAvailability(
                    $product, 
                    $branch->id, 
                    $currentStock - $request->quantity_lost
                );
            }

            // Update batch quantity if batch is specified
            if ($request->batch_id) {
                $batch = Batch::find($request->batch_id);
                if ($batch) {
                    $batch->update([
                        'current_quantity' => $batch->current_quantity - $request->quantity_lost
                    ]);

                    // Mark batch as finished if quantity becomes zero
                    if ($batch->current_quantity <= 0) {
                        $batch->update(['status' => 'finished']);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Loss record created successfully',
                'data' => $lossRecord->load(['product', 'branch', 'batch', 'user'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create loss record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified loss record.
     */
    public function show(LossTracking $lossTracking)
    {
        $lossTracking->load(['product', 'branch', 'batch', 'user']);

        return response()->json([
            'status' => 'success',
            'data' => $lossTracking
        ]);
    }

    /**
     * Update the specified loss record.
     */
    public function update(Request $request, LossTracking $lossTracking)
    {
        $validator = Validator::make($request->all(), [
            'loss_type' => 'required|in:weight_loss,water_loss,wastage,complimentary,damage,theft,expiry',
            'quantity_lost' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'initial_quantity' => 'nullable|numeric|min:0',
            'final_quantity' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Calculate new financial loss
        $product = $lossTracking->product;
        $financialLoss = $request->quantity_lost * $product->selling_price;

        $lossTracking->update([
            'loss_type' => $request->loss_type,
            'quantity_lost' => $request->quantity_lost,
            'financial_loss' => $financialLoss,
            'reason' => $request->reason,
            'initial_quantity' => $request->initial_quantity,
            'final_quantity' => $request->final_quantity,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Loss record updated successfully',
            'data' => $lossTracking->load(['product', 'branch', 'batch', 'user'])
        ]);
    }

    /**
     * Remove the specified loss record.
     */
    public function destroy(LossTracking $lossTracking)
    {
        $lossTracking->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Loss record deleted successfully'
        ]);
    }

    /**
     * Get loss analytics.
     */
    public function getLossAnalytics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'nullable|exists:branches,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = LossTracking::with(['product', 'branch'])
                            ->whereBetween('created_at', [$request->start_date, $request->end_date]);

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $losses = $query->get();

        $analytics = [
            'by_type' => $losses->groupBy('loss_type')->map(function ($typeLosses, $type) {
                return [
                    'type' => $type,
                    'total_quantity' => $typeLosses->sum('quantity_lost'),
                    'total_financial_loss' => $typeLosses->sum('financial_loss'),
                    'count' => $typeLosses->count(),
                    'average_loss' => $typeLosses->avg('financial_loss'),
                ];
            }),
            'by_product' => $losses->groupBy('product_id')->map(function ($productLosses, $productId) {
                $product = $productLosses->first()->product;
                return [
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'category' => $product->category,
                    'total_quantity' => $productLosses->sum('quantity_lost'),
                    'total_financial_loss' => $productLosses->sum('financial_loss'),
                    'loss_types' => $productLosses->groupBy('loss_type')->map->count(),
                ];
            }),
            'by_branch' => $losses->groupBy('branch_id')->map(function ($branchLosses, $branchId) {
                $branch = $branchLosses->first()->branch;
                return [
                    'branch_id' => $branchId,
                    'branch_name' => $branch->name,
                    'total_quantity' => $branchLosses->sum('quantity_lost'),
                    'total_financial_loss' => $branchLosses->sum('financial_loss'),
                    'loss_types' => $branchLosses->groupBy('loss_type')->map->count(),
                ];
            }),
            'summary' => [
                'total_quantity_lost' => $losses->sum('quantity_lost'),
                'total_financial_loss' => $losses->sum('financial_loss'),
                'total_records' => $losses->count(),
                'average_loss_per_record' => $losses->avg('financial_loss'),
                'most_common_loss_type' => $losses->groupBy('loss_type')->sortByDesc->count()->keys()->first(),
            ],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $analytics
        ]);
    }

    /**
     * Get loss trends over time.
     */
    public function getLossTrends(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'nullable|exists:branches,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'group_by' => 'required|in:day,week,month',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = LossTracking::whereBetween('created_at', [$request->start_date, $request->end_date]);

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        // Group by time period
        $dateFormat = match($request->group_by) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $trends = $query->selectRaw("
                DATE_FORMAT(created_at, '{$dateFormat}') as period,
                loss_type,
                COUNT(*) as loss_count,
                SUM(quantity_lost) as total_quantity,
                SUM(financial_loss) as total_financial_loss
            ")
            ->groupBy(['period', 'loss_type'])
            ->orderBy('period')
            ->get()
            ->groupBy('period');

        return response()->json([
            'status' => 'success',
            'data' => $trends
        ]);
    }

    /**
     * Get critical loss alerts.
     */
    public function getCriticalLossAlerts(Request $request)
    {
        $threshold = $request->threshold ?? 1000; // Default threshold
        $days = $request->days ?? 7; // Default to last 7 days

        $criticalLosses = LossTracking::with(['product', 'branch'])
                                    ->where('financial_loss', '>=', $threshold)
                                    ->where('created_at', '>=', now()->subDays($days))
                                    ->orderBy('financial_loss', 'desc')
                                    ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'critical_losses' => $criticalLosses,
                'summary' => [
                    'total_critical_losses' => $criticalLosses->count(),
                    'total_financial_impact' => $criticalLosses->sum('financial_loss'),
                    'most_affected_products' => $criticalLosses->groupBy('product_id')
                        ->sortByDesc(function ($productLosses) {
                            return $productLosses->sum('financial_loss');
                        })
                        ->take(5)
                        ->map(function ($productLosses, $productId) {
                            $product = $productLosses->first()->product;
                            return [
                                'product_id' => $productId,
                                'product_name' => $product->name,
                                'total_loss' => $productLosses->sum('financial_loss'),
                                'loss_count' => $productLosses->count(),
                            ];
                        })
                        ->values(),
                ],
            ]
        ]);
    }

    /**
     * Bulk record losses from batch processing.
     */
    public function bulkRecordLosses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'losses' => 'required|array|min:1',
            'losses.*.product_id' => 'required|exists:products,id',
            'losses.*.branch_id' => 'required|exists:branches,id',
            'losses.*.batch_id' => 'nullable|exists:batches,id',
            'losses.*.loss_type' => 'required|in:weight_loss,water_loss,wastage,complimentary,damage,theft,expiry',
            'losses.*.quantity_lost' => 'required|numeric|min:0.01',
            'losses.*.reason' => 'required|string|max:255',
            'losses.*.initial_quantity' => 'nullable|numeric|min:0',
            'losses.*.final_quantity' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $inventoryService = new InventoryService();
            $createdRecords = [];

            foreach ($request->losses as $lossData) {
                $product = Product::find($lossData['product_id']);
                $branch = Branch::find($lossData['branch_id']);

                // Calculate financial loss
                $financialLoss = $lossData['quantity_lost'] * $product->selling_price;

                // Create loss record
                $lossRecord = LossTracking::create([
                    'product_id' => $lossData['product_id'],
                    'branch_id' => $lossData['branch_id'],
                    'batch_id' => $lossData['batch_id'] ?? null,
                    'loss_type' => $lossData['loss_type'],
                    'quantity_lost' => $lossData['quantity_lost'],
                    'financial_loss' => $financialLoss,
                    'reason' => $lossData['reason'],
                    'user_id' => $user->id,
                    'initial_quantity' => $lossData['initial_quantity'] ?? null,
                    'final_quantity' => $lossData['final_quantity'] ?? null,
                ]);

                // Update stock
                $currentStock = $product->getCurrentStock($branch->id);
                $product->updateStock($branch->id, $currentStock - $lossData['quantity_lost']);

                // Check online availability
                $inventoryService->checkAndUpdateOnlineAvailability(
                    $product, 
                    $branch->id, 
                    $currentStock - $lossData['quantity_lost']
                );

                $createdRecords[] = $lossRecord;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Bulk loss records created successfully',
                'data' => [
                    'created_count' => count($createdRecords),
                    'total_financial_loss' => collect($createdRecords)->sum('financial_loss'),
                    'records' => $createdRecords,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create bulk loss records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get loss prevention recommendations.
     */
    public function getLossPreventionRecommendations(Request $request)
    {
        $branchId = $request->branch_id;
        $days = $request->days ?? 30;

        $query = LossTracking::with(['product', 'branch'])
                            ->where('created_at', '>=', now()->subDays($days));

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $losses = $query->get();

        $recommendations = [];

        // High wastage products
        $highWastageProducts = $losses->where('loss_type', 'wastage')
                                     ->groupBy('product_id')
                                     ->filter(function ($productLosses) {
                                         return $productLosses->sum('financial_loss') > 500;
                                     })
                                     ->map(function ($productLosses, $productId) {
                                         $product = $productLosses->first()->product;
                                         return [
                                             'product_id' => $productId,
                                             'product_name' => $product->name,
                                             'total_loss' => $productLosses->sum('financial_loss'),
                                             'recommendation' => 'Consider reducing order quantities or improving storage conditions',
                                         ];
                                     });

        if ($highWastageProducts->isNotEmpty()) {
            $recommendations[] = [
                'type' => 'high_wastage',
                'title' => 'High Wastage Products',
                'description' => 'Products with significant wastage losses',
                'products' => $highWastageProducts->values(),
            ];
        }

        // Weight loss patterns
        $weightLossProducts = $losses->where('loss_type', 'weight_loss')
                                    ->groupBy('product_id')
                                    ->map(function ($productLosses, $productId) {
                                        $product = $productLosses->first()->product;
                                        $avgLossPercentage = $productLosses->avg(function ($loss) {
                                            return $loss->getLossPercentage();
                                        });
                                        
                                        return [
                                            'product_id' => $productId,
                                            'product_name' => $product->name,
                                            'avg_loss_percentage' => round($avgLossPercentage, 2),
                                            'total_loss' => $productLosses->sum('financial_loss'),
                                        ];
                                    })
                                    ->filter(function ($data) {
                                        return $data['avg_loss_percentage'] > 5; // More than 5% loss
                                    });

        if ($weightLossProducts->isNotEmpty()) {
            $recommendations[] = [
                'type' => 'weight_loss',
                'title' => 'High Weight Loss Products',
                'description' => 'Products experiencing significant weight loss during storage',
                'products' => $weightLossProducts->values(),
                'suggestion' => 'Review storage conditions and consider improving humidity control',
            ];
        }

        // Frequent complimentary adjustments
        $complimentaryProducts = $losses->where('loss_type', 'complimentary')
                                       ->groupBy('product_id')
                                       ->filter(function ($productLosses) {
                                           return $productLosses->count() > 10; // More than 10 instances
                                       })
                                       ->map(function ($productLosses, $productId) {
                                           $product = $productLosses->first()->product;
                                           return [
                                               'product_id' => $productId,
                                               'product_name' => $product->name,
                                               'frequency' => $productLosses->count(),
                                               'total_loss' => $productLosses->sum('financial_loss'),
                                           ];
                                       });

        if ($complimentaryProducts->isNotEmpty()) {
            $recommendations[] = [
                'type' => 'complimentary_adjustments',
                'title' => 'Frequent Complimentary Adjustments',
                'description' => 'Products with frequent customer adjustments',
                'products' => $complimentaryProducts->values(),
                'suggestion' => 'Review pricing strategy or improve portion accuracy',
            ];
        }

        $summary = [
            'total_financial_loss' => $losses->sum('financial_loss'),
            'total_quantity_lost' => $losses->sum('quantity_lost'),
            'loss_by_type' => $losses->groupBy('loss_type')->map(function ($typeLosses) {
                return [
                    'count' => $typeLosses->count(),
                    'financial_loss' => $typeLosses->sum('financial_loss'),
                ];
            }),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'recommendations' => $recommendations,
                'summary' => $summary,
            ]
        ]);
    }

    /**
     * Export loss data.
     */
    public function exportLossData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'nullable|exists:branches,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:csv,json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = LossTracking::with(['product', 'branch', 'batch', 'user'])
                            ->whereBetween('created_at', [$request->start_date, $request->end_date]);

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $losses = $query->orderBy('created_at', 'desc')->get();

        $exportData = $losses->map(function ($loss) {
            return [
                'date' => $loss->created_at->format('Y-m-d H:i:s'),
                'product_name' => $loss->product->name,
                'product_code' => $loss->product->code,
                'branch_name' => $loss->branch->name,
                'batch_number' => $loss->batch ? $loss->batch->batch_number : 'N/A',
                'loss_type' => $loss->loss_type,
                'quantity_lost' => $loss->quantity_lost,
                'financial_loss' => $loss->financial_loss,
                'reason' => $loss->reason,
                'recorded_by' => $loss->user->name,
                'loss_percentage' => $loss->getLossPercentage(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $exportData,
            'meta' => [
                'total_records' => $exportData->count(),
                'export_format' => $request->format,
                'date_range' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ],
            ]
        ]);
    }
}