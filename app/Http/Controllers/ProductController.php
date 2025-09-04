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

    /**
     * Get product categories and subcategories.
     */
    public function getCategories()
    {
        $categories = Product::getCategories();
        $categoriesWithSub = [];

        foreach ($categories as $key => $name) {
            $categoriesWithSub[] = [
                'key' => $key,
                'name' => $name,
                'subcategories' => Product::getSubcategories($key),
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $categoriesWithSub
        ]);
    }

    /**
     * Update branch-specific pricing.
     */
    public function updateBranchPricing(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'branch_pricing' => 'required|array|min:1',
            'branch_pricing.*.branch_id' => 'required|exists:branches,id',
            'branch_pricing.*.selling_price' => 'required|numeric|min:0',
            'branch_pricing.*.is_available_online' => 'boolean',
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

            foreach ($request->branch_pricing as $pricing) {
                $product->branches()->updateExistingPivot($pricing['branch_id'], [
                    'selling_price' => $pricing['selling_price'],
                    'is_available_online' => $pricing['is_available_online'] ?? true,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Branch pricing updated successfully',
                'data' => $product->load('branches')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update pricing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update vendor pricing.
     */
    public function updateVendorPricing(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'vendor_pricing' => 'required|array|min:1',
            'vendor_pricing.*.vendor_id' => 'required|exists:vendors,id',
            'vendor_pricing.*.vendor_price' => 'required|numeric|min:0',
            'vendor_pricing.*.is_primary' => 'boolean',
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

            foreach ($request->vendor_pricing as $pricing) {
                $product->vendors()->updateExistingPivot($pricing['vendor_id'], [
                    'vendor_price' => $pricing['vendor_price'],
                    'is_primary' => $pricing['is_primary'] ?? false,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Vendor pricing updated successfully',
                'data' => $product->load('vendors')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update vendor pricing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update product categories.
     */
    public function bulkUpdateCategories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'updates' => 'required|array|min:1',
            'updates.*.product_id' => 'required|exists:products,id',
            'updates.*.category' => 'required|string',
            'updates.*.subcategory' => 'nullable|string',
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

            foreach ($request->updates as $update) {
                $product = Product::find($update['product_id']);
                $product->update([
                    'category' => $update['category'],
                    'subcategory' => $update['subcategory'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Product categories updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products by category with pricing analysis.
     */
    public function getByCategory(Request $request, string $category)
    {
        $query = Product::with(['branches', 'vendors', 'expenseAllocations'])
                        ->where('category', $category);

        if ($request->has('subcategory')) {
            $query->where('subcategory', $request->subcategory);
        }

        if ($request->has('branch_id')) {
            $query->whereHas('branches', function ($q) use ($request) {
                $q->where('branches.id', $request->branch_id);
            });
        }

        $products = $query->active()->get();

        $categoryData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'category' => $product->category,
                'subcategory' => $product->subcategory,
                'weight_unit' => $product->weight_unit,
                'purchase_price' => $product->purchase_price,
                'mrp' => $product->mrp,
                'selling_price' => $product->selling_price,
                'is_perishable' => $product->isPerishable(),
                'storage_temperature' => $product->getStorageTemperature(),
                'shelf_life_days' => $product->shelf_life_days,
                'total_allocated_expenses' => $product->getTotalAllocatedExpenses(),
                'cost_per_unit' => $product->getCostPerUnit(),
                'profit_margin' => $product->getProfitMargin(),
                'profit_percentage' => $product->getProfitPercentage(),
                'branches' => $product->branches->map(function ($branch) use ($product) {
                    return [
                        'branch_id' => $branch->id,
                        'branch_name' => $branch->name,
                        'current_stock' => $branch->pivot->current_stock,
                        'selling_price' => $branch->pivot->selling_price,
                        'is_available_online' => $branch->pivot->is_available_online,
                        'profit_margin' => $product->getProfitMargin($branch->id),
                    ];
                }),
                'vendors' => $product->vendors->map(function ($vendor) {
                    return [
                        'vendor_id' => $vendor->id,
                        'vendor_name' => $vendor->name,
                        'vendor_price' => $vendor->pivot->vendor_price,
                        'is_primary' => $vendor->pivot->is_primary,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'category' => $category,
                'products' => $categoryData,
                'summary' => [
                    'total_products' => $products->count(),
                    'average_profit_margin' => $products->avg(function ($product) {
                        return $product->getProfitPercentage();
                    }),
                    'total_stock_value' => $products->sum(function ($product) {
                        return $product->branches->sum(function ($branch) use ($product) {
                            return $branch->pivot->current_stock * $branch->pivot->selling_price;
                        });
                    }),
                ],
            ]
        ]);
    }
}