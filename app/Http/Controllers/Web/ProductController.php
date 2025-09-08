<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
        $branches = Branch::all();
        $categories = ['fruit', 'vegetable', 'leafy', 'exotic'];

        return view('products.index', compact('products', 'branches', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $branches = Branch::all();
        $vendors = Vendor::all();
        $categories = ['fruit', 'vegetable', 'leafy', 'exotic'];
        $weight_units = ['kg', 'gm', 'pcs'];

        return view('products.create', compact('branches', 'vendors', 'categories', 'weight_units'));
    }

    /**
     * Store a newly created product (web form submission).
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:products,code|max:50',
            'description' => 'nullable|string',
            'category' => 'required|in:fruit,vegetable,leafy,exotic',
            'weight_unit' => 'required|in:kg,gm,pcs',
            'purchase_price' => 'required|numeric|min:0',
            'mrp' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_threshold' => 'required|integer|min:1',
            'shelf_life_days' => 'nullable|integer|min:0',
            'storage_temperature' => 'nullable|string|max:50',
            'is_perishable' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'branch_prices' => 'required|array',
            'branch_prices.*' => 'nullable|numeric|min:0',
            'vendor_supplies' => 'nullable|array',
            'vendor_supplies.*.vendor_id' => 'required|exists:vendors,id',
            'vendor_supplies.*.supply_price' => 'required|numeric|min:0',
            'vendor_supplies.*.is_primary_supplier' => 'sometimes|boolean',
        ];

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            $product = Product::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'],
                'weight_unit' => $validated['weight_unit'],
                'purchase_price' => $validated['purchase_price'],
                'mrp' => $validated['mrp'],
                'selling_price' => $validated['selling_price'],
                'stock_threshold' => $validated['stock_threshold'],
                'shelf_life_days' => $validated['shelf_life_days'] ?? null,
                'storage_temperature' => $validated['storage_temperature'] ?? null,
                'is_perishable' => (bool)($validated['is_perishable'] ?? false),
                'is_active' => (bool)($validated['is_active'] ?? true),
            ]);

            // Attach branch pricing (expecting branch_prices[branch_id] => price)
            foreach (($validated['branch_prices'] ?? []) as $branchId => $price) {
                if ($price === null || $price === '') {
                    continue;
                }
                $product->branches()->attach($branchId, [
                    'selling_price' => $price,
                    'current_stock' => 0,
                    'is_available_online' => true,
                ]);
            }

            // Attach vendor supplies if provided
            if (!empty($validated['vendor_supplies'])) {
                foreach ($validated['vendor_supplies'] as $supply) {
                    $product->vendors()->attach($supply['vendor_id'], [
                        'supply_price' => $supply['supply_price'],
                        'is_primary_supplier' => (bool)($supply['is_primary_supplier'] ?? false),
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('products.index')
                ->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['branches', 'vendors', 'orderItems']);
        
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $branches = Branch::all();
        $vendors = Vendor::all();
        $categories = ['fruit', 'vegetable', 'leafy', 'exotic'];
        $weight_units = ['kg', 'gm', 'pcs'];

        return view('products.edit', compact('product', 'branches', 'vendors', 'categories', 'weight_units'));
    }

    /**
     * Update the specified product (web form submission).
     */
    public function update(Request $request, Product $product)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:fruit,vegetable,leafy,exotic',
            'weight_unit' => 'required|in:kg,gm,pcs',
            'purchase_price' => 'required|numeric|min:0',
            'mrp' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_threshold' => 'required|integer|min:1',
            'shelf_life_days' => 'nullable|integer|min:0',
            'storage_temperature' => 'nullable|string|max:50',
            'is_perishable' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'branch_prices' => 'nullable|array',
            'branch_prices.*' => 'nullable|numeric|min:0',
            'vendor_supplies' => 'nullable|array',
            'vendor_supplies.*.vendor_id' => 'required|exists:vendors,id',
            'vendor_supplies.*.supply_price' => 'required|numeric|min:0',
            'vendor_supplies.*.is_primary_supplier' => 'sometimes|boolean',
        ];

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            $product->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'],
                'weight_unit' => $validated['weight_unit'],
                'purchase_price' => $validated['purchase_price'],
                'mrp' => $validated['mrp'],
                'selling_price' => $validated['selling_price'],
                'stock_threshold' => $validated['stock_threshold'],
                'shelf_life_days' => $validated['shelf_life_days'] ?? null,
                'storage_temperature' => $validated['storage_temperature'] ?? null,
                'is_perishable' => (bool)($validated['is_perishable'] ?? false),
                'is_active' => (bool)($validated['is_active'] ?? true),
            ]);

            // Update branch pricing
            if (!empty($validated['branch_prices'])) {
                foreach ($validated['branch_prices'] as $branchId => $price) {
                    if ($price === null || $price === '') {
                        continue;
                    }
                    // Attach if not exists, else update
                    if ($product->branches()->where('branches.id', $branchId)->exists()) {
                        $product->branches()->updateExistingPivot($branchId, [
                            'selling_price' => $price,
                            'updated_at' => now(),
                        ]);
                    } else {
                        $product->branches()->attach($branchId, [
                            'selling_price' => $price,
                            'current_stock' => 0,
                            'is_available_online' => true,
                        ]);
                    }
                }
            }

            // Sync vendor supplies if provided
            if (!empty($validated['vendor_supplies'])) {
                $syncData = [];
                foreach ($validated['vendor_supplies'] as $supply) {
                    $syncData[$supply['vendor_id']] = [
                        'supply_price' => $supply['supply_price'],
                        'is_primary_supplier' => (bool)($supply['is_primary_supplier'] ?? false),
                    ];
                }
                $product->vendors()->syncWithoutDetaching($syncData);
            }

            DB::commit();

            return redirect()
                ->route('products.show', $product)
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()]);
        }
    }

    /**
     * Display products by category.
     */
    public function byCategory($category)
    {
        $products = Product::byCategory($category)
            ->with(['branches', 'vendors'])
            ->active()
            ->paginate(20);

        return view('products.byCategory', compact('products', 'category'));
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists() || $product->stockMovements()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete product with existing orders or stock movements.']);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}