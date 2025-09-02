<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Vendor;
use Illuminate\Http\Request;

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
}