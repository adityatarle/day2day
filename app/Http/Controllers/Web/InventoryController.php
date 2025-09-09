<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Batch;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    /**
     * Display a listing of inventory items.
     */
    public function index()
    {
        $products = Product::with(['branches'])
            ->whereHas('branches', function ($query) {
                $query->where('current_stock', '>', 0);
            })
            ->paginate(20);

        $branches = Branch::all();

        // Calculate inventory statistics
        $inventory_stats = [
            'total_value' => Product::with(['branches'])->get()->sum(function ($product) {
                return $product->branches->sum(function ($branch) use ($product) {
                    return ($branch->pivot->current_stock ?? 0) * ($branch->pivot->selling_price ?? $product->selling_price);
                });
            }),
            'in_stock' => Product::whereHas('branches', function ($query) {
                $query->where('current_stock', '>', 0);
            })->count(),
            'low_stock' => Product::whereHas('branches', function ($query) {
                $query->where('current_stock', '<=', \DB::raw('stock_threshold'));
            })->count(),
            'out_of_stock' => Product::whereHas('branches', function ($query) {
                $query->where('current_stock', '<=', 0);
            })->count(),
        ];

        return view('inventory.index', compact('products', 'branches', 'inventory_stats'));
    }

    /**
     * Show the form for adding stock.
     */
    public function addStockForm()
    {
        $products = Product::with(['branches'])->active()->get();
        $branches = Branch::all();
        $vendors = \App\Models\Vendor::all();

        return view('inventory.add-stock', compact('products', 'branches', 'vendors'));
    }

    /**
     * Process the add stock form submission.
     */
    public function addStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'quantity' => 'required|numeric|min:0.01',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::find($request->product_id);
            $branch = Branch::find($request->branch_id);
            $user = auth()->user();

            // Generate batch number
            $batchNumber = 'BATCH-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Create batch
            $batch = Batch::create([
                'product_id' => $request->product_id,
                'branch_id' => $request->branch_id,
                'batch_number' => $batchNumber,
                'initial_quantity' => $request->quantity,
                'current_quantity' => $request->quantity,
                'purchase_date' => now(),
                'purchase_price' => $request->cost_price ?? 0,
                'status' => 'active',
            ]);

            // Update branch stock - ensure the relationship exists
            $currentStock = $product->getCurrentStock($branch);
            
            // Check if the product-branch relationship exists
            $existingPivot = $product->branches()->where('branch_id', $branch->id)->first();
            
            if ($existingPivot) {
                // Update existing pivot record
                $updateData = ['current_stock' => $currentStock + $request->quantity];
                if ($request->selling_price) {
                    $updateData['selling_price'] = $request->selling_price;
                }
                $product->branches()->updateExistingPivot($branch->id, $updateData);
            } else {
                // Create new pivot record
                $product->branches()->attach($branch->id, [
                    'current_stock' => $request->quantity,
                    'selling_price' => $request->selling_price ?? $product->selling_price,
                    'is_available_online' => true,
                    'stock_threshold' => $product->stock_threshold ?? 10,
                ]);
            }

            // Record stock movement
            StockMovement::create([
                'product_id' => $request->product_id,
                'branch_id' => $request->branch_id,
                'batch_id' => $batch->id,
                'type' => 'purchase',
                'quantity' => $request->quantity,
                'unit_price' => $request->cost_price ?? 0,
                'notes' => $request->notes,
                'user_id' => $user->id,
            ]);

            DB::commit();

            return redirect()->route('inventory.addStockForm')
                ->with('success', 'Stock added successfully! New stock quantity: ' . ($currentStock + $request->quantity));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add stock: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for recording loss.
     */
    public function recordLossForm()
    {
        $products = Product::with(['branches'])->active()->get();
        $branches = Branch::all();

        return view('inventory.record-loss', compact('products', 'branches'));
    }

    /**
     * Record a loss.
     */
    public function recordLoss(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'loss_type' => 'required|in:weight_loss,water_loss,wastage,complimentary',
            'quantity_lost' => 'required|numeric|min:0.01',
            'financial_loss' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
            'batch_id' => 'nullable|exists:batches,id',
            'initial_quantity' => 'nullable|numeric|min:0',
            'final_quantity' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Create loss tracking record
            \App\Models\LossTracking::create([
                'product_id' => $request->product_id,
                'branch_id' => $request->branch_id,
                'batch_id' => $request->batch_id,
                'loss_type' => $request->loss_type,
                'quantity_lost' => $request->quantity_lost,
                'financial_loss' => $request->financial_loss,
                'reason' => $request->reason,
                'user_id' => auth()->id(),
                'initial_quantity' => $request->initial_quantity,
                'final_quantity' => $request->final_quantity,
            ]);

            // Update inventory - reduce stock
            $product = Product::find($request->product_id);
            $branch = Branch::find($request->branch_id);
            
            // Get current stock
            $currentStock = $product->getCurrentStock($branch);
            $newStock = max(0, $currentStock - $request->quantity_lost);
            
            // Update stock in pivot table
            $product->branches()->updateExistingPivot($branch->id, [
                'current_stock' => $newStock
            ]);

            // Create stock movement record
            StockMovement::create([
                'product_id' => $request->product_id,
                'branch_id' => $request->branch_id,
                'type' => 'loss',
                'quantity' => -$request->quantity_lost, // Negative for loss
                'reference_type' => 'loss_tracking',
                'reference_id' => null, // Will be updated after loss tracking is created
                'user_id' => auth()->id(),
                'notes' => "Loss recorded: {$request->reason}",
            ]);
        });

        return redirect()->route('inventory.lossTracking')
            ->with('success', 'Loss recorded successfully!');
    }

    /**
     * Display inventory batches.
     */
    public function batches()
    {
        $batches = Batch::with(['product', 'branch'])
            ->latest()
            ->paginate(20);

        return view('inventory.batches', compact('batches'));
    }

    /**
     * Display stock movements.
     */
    public function stockMovements()
    {
        $movements = StockMovement::with(['product', 'branch'])
            ->latest()
            ->paginate(20);

        return view('inventory.stock-movements', compact('movements'));
    }

    /**
     * Display loss tracking.
     */
    public function lossTracking(Request $request)
    {
        $query = \App\Models\LossTracking::with(['product', 'branch', 'batch', 'user']);

        // Filter by loss type
        if ($request->has('loss_type') && $request->loss_type !== '') {
            $query->where('loss_type', $request->loss_type);
        }

        // Filter by branch
        if ($request->has('branch_id') && $request->branch_id !== '') {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by product
        if ($request->has('product_id') && $request->product_id !== '') {
            $query->where('product_id', $request->product_id);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $losses = $query->latest()->paginate(20);
        
        // Get filter options
        $products = Product::active()->get();
        $branches = Branch::all();
        
        // Statistics
        $stats = [
            'total_losses' => \App\Models\LossTracking::count(),
            'weight_losses' => \App\Models\LossTracking::where('loss_type', 'weight_loss')->count(),
            'total_financial_loss' => \App\Models\LossTracking::sum('financial_loss'),
            'avg_loss_per_incident' => \App\Models\LossTracking::avg('financial_loss'),
        ];

        return view('inventory.loss-tracking', compact('losses', 'products', 'branches', 'stats'));
    }

    /**
     * Display inventory valuation.
     */
    public function valuation()
    {
        $products = Product::with(['branches'])
            ->whereHas('branches', function ($query) {
                $query->where('current_stock', '>', 0);
            })
            ->get();

        $totalValue = $products->sum(function ($product) {
            return $product->branches->sum(function ($branch) use ($product) {
                return $branch->pivot->current_stock * $branch->pivot->selling_price;
            });
        });

        return view('inventory.valuation', compact('products', 'totalValue'));
    }

    /**
     * Display low stock alerts.
     */
    public function lowStockAlerts()
    {
        $lowStockProducts = Product::with(['branches'])
            ->whereHas('branches', function ($query) {
                $query->where('current_stock', '<=', \DB::raw('stock_threshold'));
            })
            ->get();

        return view('inventory.low-stock-alerts', compact('lowStockProducts'));
    }

    /**
     * Display inventory for the authenticated manager's branch.
     */
    public function branchIndex()
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')
                ->with('error', 'No branch assigned to your account.');
        }

        $products = Product::with(['branches' => function ($query) use ($branch) {
                $query->where('branches.id', $branch->id);
            }])
            ->whereHas('branches', function ($query) use ($branch) {
                $query->where('branches.id', $branch->id)
                      ->where('current_stock', '>', 0);
            })
            ->paginate(20);

        // Branch-specific inventory statistics
        $productsForStats = Product::with(['branches' => function ($query) use ($branch) {
                $query->where('branches.id', $branch->id);
            }])->get();

        $inventory_stats = [
            'total_value' => $productsForStats->sum(function ($product) {
                return $product->branches->sum(function ($productBranch) use ($product) {
                    return ($productBranch->pivot->current_stock ?? 0) * ($productBranch->pivot->selling_price ?? $product->selling_price);
                });
            }),
            'in_stock' => Product::whereHas('branches', function ($query) use ($branch) {
                $query->where('branches.id', $branch->id)
                      ->where('current_stock', '>', 0);
            })->count(),
            'low_stock' => Product::whereHas('branches', function ($query) use ($branch) {
                $query->where('branches.id', $branch->id)
                      ->where('current_stock', '<=', \DB::raw('stock_threshold'));
            })->count(),
            'out_of_stock' => Product::whereHas('branches', function ($query) use ($branch) {
                $query->where('branches.id', $branch->id)
                      ->where('current_stock', '<=', 0);
            })->count(),
        ];

        $branches = collect([$branch]);

        return view('inventory.index', compact('products', 'branches', 'inventory_stats'));
    }

    /**
     * Cashier read-only view of current branch inventory.
     */
    public function cashierView()
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')
                ->with('error', 'No branch assigned to your account.');
        }

        $products = Product::with(['branches' => function ($query) use ($branch) {
                $query->where('branches.id', $branch->id);
            }])
            ->whereHas('branches', function ($query) use ($branch) {
                $query->where('branches.id', $branch->id)
                      ->where('current_stock', '>', 0);
            })
            ->paginate(20);

        $productsForStats = Product::with(['branches' => function ($query) use ($branch) {
                $query->where('branches.id', $branch->id);
            }])->get();

        $inventory_stats = [
            'total_value' => $productsForStats->sum(function ($product) {
                return $product->branches->sum(function ($productBranch) use ($product) {
                    return ($productBranch->pivot->current_stock ?? 0) * ($productBranch->pivot->selling_price ?? $product->selling_price);
                });
            }),
            'in_stock' => Product::whereHas('branches', function ($query) use ($branch) {
                $query->where('branches.id', $branch->id)
                      ->where('current_stock', '>', 0);
            })->count(),
            'low_stock' => Product::whereHas('branches', function ($query) use ($branch) {
                $query->where('branches.id', $branch->id)
                      ->where('current_stock', '<=', \DB::raw('stock_threshold'));
            })->count(),
            'out_of_stock' => Product::whereHas('branches', function ($query) use ($branch) {
                $query->where('branches.id', $branch->id)
                      ->where('current_stock', '<=', 0);
            })->count(),
        ];

        $branches = collect([$branch]);

        return view('inventory.index', compact('products', 'branches', 'inventory_stats'));
    }
}