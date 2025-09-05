<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Branch;
use App\Models\InventoryBatch;
use App\Models\StockMovement;
use Illuminate\Http\Request;

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
     * Show the form for recording loss.
     */
    public function recordLossForm()
    {
        $products = Product::with(['branches'])->active()->get();
        $branches = Branch::all();

        return view('inventory.record-loss', compact('products', 'branches'));
    }

    /**
     * Display inventory batches.
     */
    public function batches()
    {
        $batches = InventoryBatch::with(['product', 'branch', 'vendor'])
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
    public function lossTracking()
    {
        $losses = StockMovement::where('type', 'loss')
            ->with(['product', 'branch'])
            ->latest()
            ->paginate(20);

        return view('inventory.loss-tracking', compact('losses'));
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