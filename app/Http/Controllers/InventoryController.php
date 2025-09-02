<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\Batch;
use App\Models\StockMovement;
use App\Models\LossTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    /**
     * Display inventory status across branches.
     */
    public function index(Request $request)
    {
        $query = Product::with(['branches' => function ($query) {
            $query->select('branches.id', 'branches.name', 'branches.code')
                  ->withPivot(['current_stock', 'is_available_online']);
        }]);

        // Filter by category
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Filter by branch
        if ($request->has('branch_id')) {
            $query->whereHas('branches', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        // Filter by stock status
        if ($request->has('stock_status')) {
            if ($request->stock_status === 'low_stock') {
                $query->whereHas('branches', function ($q) {
                    $q->whereRaw('product_branches.current_stock <= products.stock_threshold');
                });
            } elseif ($request->stock_status === 'out_of_stock') {
                $query->whereHas('branches', function ($q) {
                    $q->where('product_branches.current_stock', 0);
                });
            }
        }

        $products = $query->active()->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    /**
     * Add stock to a product at a specific branch.
     */
    public function addStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|numeric|min:0.01',
            'purchase_price' => 'required|numeric|min:0',
            'batch_number' => 'nullable|string|unique:batches,batch_number',
            'expiry_date' => 'nullable|date|after:today',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
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

            // Generate batch number if not provided
            $batchNumber = $request->batch_number ?? 'BATCH-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Create batch
            $batch = Batch::create([
                'product_id' => $request->product_id,
                'branch_id' => $request->branch_id,
                'batch_number' => $batchNumber,
                'initial_quantity' => $request->quantity,
                'current_quantity' => $request->quantity,
                'expiry_date' => $request->expiry_date,
                'purchase_date' => $request->purchase_date,
                'purchase_price' => $request->purchase_price,
                'status' => 'active',
            ]);

            // Update branch stock
            $currentStock = $product->getCurrentStock($branch);
            $product->branches()->updateExistingPivot($branch->id, [
                'current_stock' => $currentStock + $request->quantity
            ]);

            // Record stock movement
            StockMovement::create([
                'product_id' => $request->product_id,
                'branch_id' => $request->branch_id,
                'batch_id' => $batch->id,
                'type' => 'purchase',
                'quantity' => $request->quantity,
                'unit_price' => $request->purchase_price,
                'notes' => $request->notes,
                'user_id' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Stock added successfully',
                'data' => [
                    'batch' => $batch,
                    'new_stock' => $currentStock + $request->quantity
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record stock loss/adjustment.
     */
    public function recordLoss(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'batch_id' => 'nullable|exists:batches,id',
            'loss_type' => 'required|in:weight_loss,water_loss,wastage,complimentary',
            'quantity_lost' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
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

            // Check if sufficient stock exists
            $currentStock = $product->getCurrentStock($branch);
            if ($currentStock < $request->quantity_lost) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient stock to record loss'
                ], 400);
            }

            // Calculate financial loss
            $productBranch = $product->branches()->where('branch_id', $branch->id)->first();
            $financialLoss = $request->quantity_lost * $productBranch->pivot->selling_price;

            // Record loss
            $loss = LossTracking::create([
                'product_id' => $request->product_id,
                'branch_id' => $request->branch_id,
                'batch_id' => $request->batch_id,
                'loss_type' => $request->loss_type,
                'quantity_lost' => $request->quantity_lost,
                'financial_loss' => $financialLoss,
                'reason' => $request->reason,
                'user_id' => $user->id,
            ]);

            // Update stock
            $product->branches()->updateExistingPivot($branch->id, [
                'current_stock' => $currentStock - $request->quantity_lost
            ]);

            // Record stock movement
            StockMovement::create([
                'product_id' => $request->product_id,
                'branch_id' => $request->branch_id,
                'batch_id' => $request->batch_id,
                'type' => 'loss',
                'quantity' => $request->quantity_lost,
                'unit_price' => $productBranch->pivot->selling_price,
                'notes' => "Loss recorded: {$request->loss_type} - {$request->reason}",
                'user_id' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Loss recorded successfully',
                'data' => $loss
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record loss',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get batch information for a product.
     */
    public function getBatches(Request $request, Product $product)
    {
        $query = $product->batches()->with('branch');

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $batches = $query->orderBy('purchase_date', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $batches
        ]);
    }

    /**
     * Update batch status.
     */
    public function updateBatchStatus(Request $request, Batch $batch)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,expired,sold_out',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $batch->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Batch status updated successfully',
            'data' => $batch
        ]);
    }

    /**
     * Get stock movements for a product.
     */
    public function getStockMovements(Request $request, Product $product)
    {
        $query = $product->stockMovements()
                        ->with(['branch', 'user', 'batch']);

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $movements
        ]);
    }

    /**
     * Get loss tracking summary.
     */
    public function getLossSummary(Request $request)
    {
        $query = LossTracking::with(['product', 'branch']);

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('loss_type')) {
            $query->where('loss_type', $request->loss_type);
        }

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $summary = $query->selectRaw('
                loss_type,
                SUM(quantity_lost) as total_quantity_lost,
                SUM(financial_loss) as total_financial_loss,
                COUNT(*) as total_incidents
            ')
            ->groupBy('loss_type')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $summary
        ]);
    }

    /**
     * Get low stock alerts.
     */
    public function getLowStockAlerts(Request $request)
    {
        $branchId = $request->get('branch_id');
        
        $query = Product::with(['branches' => function ($query) use ($branchId) {
            $query->select('branches.id', 'branches.name', 'branches.code')
                  ->withPivot(['current_stock', 'is_available_online']);
            
            if ($branchId) {
                $query->where('branches.id', $branchId);
            }
        }]);

        $lowStockProducts = $query->whereHas('branches', function ($q) {
            $q->whereRaw('product_branches.current_stock <= products.stock_threshold');
        })->get();

        $alerts = $lowStockProducts->map(function ($product) {
            return $product->branches->map(function ($branch) use ($product) {
                if ($branch->pivot->current_stock <= $product->stock_threshold) {
                    return [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_code' => $product->code,
                        'branch_id' => $branch->id,
                        'branch_name' => $branch->name,
                        'current_stock' => $branch->pivot->current_stock,
                        'stock_threshold' => $product->stock_threshold,
                        'weight_unit' => $product->weight_unit,
                        'is_critical' => $branch->pivot->current_stock == 0,
                    ];
                }
            })->filter();
        })->flatten();

        return response()->json([
            'status' => 'success',
            'data' => $alerts
        ]);
    }

    /**
     * Get inventory valuation.
     */
    public function getInventoryValuation(Request $request)
    {
        $branchId = $request->get('branch_id');
        
        $query = Product::with(['branches' => function ($query) use ($branchId) {
            $query->select('branches.id', 'branches.name', 'branches.code')
                  ->withPivot(['current_stock', 'selling_price']);
            
            if ($branchId) {
                $query->where('branches.id', $branchId);
            }
        }]);

        $products = $query->active()->get();

        $valuation = $products->map(function ($product) {
            return $product->branches->map(function ($branch) use ($product) {
                $stockValue = $branch->pivot->current_stock * $branch->pivot->selling_price;
                $costValue = $branch->pivot->current_stock * $product->purchase_price;
                
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'current_stock' => $branch->pivot->current_stock,
                    'stock_value' => $stockValue,
                    'cost_value' => $costValue,
                    'profit_margin' => $stockValue - $costValue,
                ];
            });
        })->flatten();

        $totalValuation = [
            'total_stock_value' => $valuation->sum('stock_value'),
            'total_cost_value' => $valuation->sum('cost_value'),
            'total_profit_margin' => $valuation->sum('profit_margin'),
            'product_count' => $products->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'valuation' => $valuation,
                'summary' => $totalValuation
            ]
        ]);
    }
}