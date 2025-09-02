<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of vendors.
     */
    public function index(Request $request)
    {
        $query = Vendor::withCount('purchaseOrders');

        // Search by name, email, or phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $vendors = $query->latest()->paginate(20);

        return view('vendors.index', compact('vendors'));
    }

    /**
     * Show the form for creating a new vendor.
     */
    public function create()
    {
        return view('vendors.create');
    }

    /**
     * Display the specified vendor.
     */
    public function show(Vendor $vendor)
    {
        $vendor->load(['purchaseOrders' => function ($query) {
            $query->latest()->take(10);
        }]);

        $totalPurchases = $vendor->purchaseOrders->sum('total_amount');
        $orderCount = $vendor->purchaseOrders->count();

        return view('vendors.show', compact('vendor', 'totalPurchases', 'orderCount'));
    }

    /**
     * Show the form for editing the specified vendor.
     */
    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    /**
     * Display purchase orders.
     */
    public function purchaseOrders()
    {
        $purchaseOrders = PurchaseOrder::with(['vendor', 'branch'])
            ->latest()
            ->paginate(20);

        return view('vendors.purchase-orders', compact('purchaseOrders'));
    }

    /**
     * Show the form for creating a purchase order.
     */
    public function createPurchaseOrder()
    {
        $vendors = Vendor::all();
        $branches = \App\Models\Branch::all();
        $products = \App\Models\Product::active()->get();

        return view('vendors.create-purchase-order', compact('vendors', 'branches', 'products'));
    }

    /**
     * Display the specified purchase order.
     */
    public function showPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['vendor', 'branch', 'items.product']);

        return view('vendors.show-purchase-order', compact('purchaseOrder'));
    }
}