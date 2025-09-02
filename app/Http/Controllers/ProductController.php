<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with(['branches', 'vendors']);

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

        // Search by name or code
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $products = $query->active()->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:products,code|max:50',
            'description' => 'nullable|string',
            'category' => 'required|in:fruit,vegetable,leafy,exotic',
            'weight_unit' => 'required|in:kg,gm,pcs',
            'purchase_price' => 'required|numeric|min:0',
            'mrp' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_threshold' => 'required|integer|min:1',
            'branch_prices' => 'required|array',
            'branch_prices.*.branch_id' => 'required|exists:branches,id',
            'branch_prices.*.selling_price' => 'required|numeric|min:0',
            'vendor_supplies' => 'nullable|array',
            'vendor_supplies.*.vendor_id' => 'required|exists:vendors,id',
            'vendor_supplies.*.supply_price' => 'required|numeric|min:0',
            'vendor_supplies.*.is_primary_supplier' => 'boolean',
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

            // Create product
            $product = Product::create($request->only([
                'name', 'code', 'description', 'category', 'weight_unit',
                'purchase_price', 'mrp', 'selling_price', 'stock_threshold'
            ]));

            // Add branch prices
            foreach ($request->branch_prices as $branchPrice) {
                $product->branches()->attach($branchPrice['branch_id'], [
                    'selling_price' => $branchPrice['selling_price'],
                    'current_stock' => 0,
                    'is_available_online' => true
                ]);
            }

            // Add vendor supplies
            if ($request->has('vendor_supplies')) {
                foreach ($request->vendor_supplies as $vendorSupply) {
                    $product->vendors()->attach($vendorSupply['vendor_id'], [
                        'supply_price' => $vendorSupply['supply_price'],
                        'is_primary_supplier' => $vendorSupply['is_primary_supplier'] ?? false
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully',
                'data' => $product->load(['branches', 'vendors'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['branches', 'vendors', 'batches', 'gstRates']);

        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|required|in:fruit,vegetable,leafy,exotic',
            'weight_unit' => 'sometimes|required|in:kg,gm,pcs',
            'purchase_price' => 'sometimes|required|numeric|min:0',
            'mrp' => 'sometimes|required|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'stock_threshold' => 'sometimes|required|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($request->only([
            'name', 'description', 'category', 'weight_unit',
            'purchase_price', 'mrp', 'selling_price', 'stock_threshold', 'is_active'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully',
            'data' => $product->load(['branches', 'vendors'])
        ]);
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        // Check if product has any orders or stock
        if ($product->orderItems()->exists() || $product->stockMovements()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete product with existing orders or stock'
            ], 400);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Update branch-specific pricing and availability.
     */
    public function updateBranchPricing(Request $request, Product $product, Branch $branch)
    {
        $validator = Validator::make($request->all(), [
            'selling_price' => 'required|numeric|min:0',
            'is_available_online' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product->branches()->updateExistingPivot($branch->id, [
            'selling_price' => $request->selling_price,
            'is_available_online' => $request->is_available_online ?? true
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Branch pricing updated successfully'
        ]);
    }

    /**
     * Get product stock information across branches.
     */
    public function getStockInfo(Product $product)
    {
        $stockInfo = $product->branches()
                            ->withPivot(['current_stock', 'is_available_online'])
                            ->get()
                            ->map(function ($branch) use ($product) {
                                return [
                                    'branch_id' => $branch->id,
                                    'branch_name' => $branch->name,
                                    'current_stock' => $branch->pivot->current_stock,
                                    'is_available_online' => $branch->pivot->is_available_online,
                                    'is_sold_out' => $product->isSoldOut($branch),
                                    'stock_threshold' => $product->stock_threshold,
                                    'weight_unit' => $product->weight_unit
                                ];
                            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'category' => $product->category,
                    'weight_unit' => $product->weight_unit
                ],
                'stock_info' => $stockInfo
            ]
        ]);
    }

    /**
     * Get products by category.
     */
    public function getByCategory($category)
    {
        $products = Product::byCategory($category)
                          ->active()
                          ->with(['branches'])
                          ->get();

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    /**
     * Search products.
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'category' => 'nullable|in:fruit,vegetable,leafy,exotic',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Product::with(['branches'])
                       ->where(function ($q) use ($request) {
                           $q->where('name', 'like', "%{$request->query}%")
                             ->orWhere('code', 'like', "%{$request->query}%")
                             ->orWhere('description', 'like', "%{$request->query}%");
                       });

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('branch_id')) {
            $query->whereHas('branches', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        $products = $query->active()->get();

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }
}