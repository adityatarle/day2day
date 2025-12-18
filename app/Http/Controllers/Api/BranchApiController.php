<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class BranchApiController extends Controller
{
    /**
     * Get current user's branch information
     */
    public function current()
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'No branch assigned to your account'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'address' => $branch->address,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'city_id' => $branch->city_id,
                'city' => $branch->city ? [
                    'id' => $branch->city->id,
                    'name' => $branch->city->name,
                ] : null,
                'pos_enabled' => (bool) $branch->pos_enabled,
                'is_active' => (bool) $branch->is_active,
                'opening_hours' => $branch->opening_hours,
                'closing_hours' => $branch->closing_hours,
            ],
        ]);
    }

    /**
     * Get branch statistics
     */
    public function statistics(Request $request)
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'No branch assigned to your account'
            ], 404);
        }

        // Date range filters
        $startDate = $request->start_date ?? now()->startOfDay();
        $endDate = $request->end_date ?? now()->endOfDay();

        // Today's statistics
        $todayStats = [
            'total_orders' => Order::where('branch_id', $branch->id)
                ->whereDate('created_at', today())
                ->count(),
            'total_sales' => (float) Order::where('branch_id', $branch->id)
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('total_amount'),
            'pending_orders' => Order::where('branch_id', $branch->id)
                ->whereDate('created_at', today())
                ->whereIn('status', ['pending', 'confirmed', 'processing'])
                ->count(),
            'completed_orders' => Order::where('branch_id', $branch->id)
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->count(),
        ];

        // Date range statistics
        $rangeStats = [
            'total_orders' => Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'total_sales' => (float) Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->sum('total_amount'),
            'cash_sales' => (float) Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_method', 'cash')
                ->where('status', 'completed')
                ->sum('total_amount'),
            'card_sales' => (float) Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_method', 'card')
                ->where('status', 'completed')
                ->sum('total_amount'),
            'upi_sales' => (float) Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_method', 'upi')
                ->where('status', 'completed')
                ->sum('total_amount'),
            'credit_sales' => (float) Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_method', 'credit')
                ->where('status', 'completed')
                ->sum('total_amount'),
        ];

        // Inventory statistics
        $inventoryStats = [
            'total_products' => Product::whereHas('branches', function($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })->where('is_active', true)->count(),
            'low_stock_products' => Product::whereHas('branches', function($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->whereColumn('current_stock', '<=', 'products.stock_threshold');
            })->where('is_active', true)->count(),
            'out_of_stock_products' => Product::whereHas('branches', function($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->where('current_stock', '<=', 0);
            })->where('is_active', true)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'branch' => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'code' => $branch->code,
                ],
                'today' => $todayStats,
                'date_range' => $rangeStats,
                'inventory' => $inventoryStats,
            ],
        ]);
    }

    /**
     * Get all branches (for admin)
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user->hasRole('admin') && !$user->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $branches = Branch::with('city')->get();

        return response()->json([
            'success' => true,
            'data' => $branches->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'code' => $branch->code,
                    'address' => $branch->address,
                    'phone' => $branch->phone,
                    'email' => $branch->email,
                    'city' => $branch->city ? [
                        'id' => $branch->city->id,
                        'name' => $branch->city->name,
                    ] : null,
                    'pos_enabled' => (bool) $branch->pos_enabled,
                    'is_active' => (bool) $branch->is_active,
                ];
            }),
        ]);
    }
}
