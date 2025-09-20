<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\CreditTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * Display a listing of vendors.
     */
    public function index(Request $request)
    {
        // Route protection: only admins should access. If mistakenly reachable, enforce here too.
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized');
        }

        $query = Vendor::withCount('purchaseOrders')
            ->withSum('purchaseOrders', 'total_amount');

        // Search by name, email, phone, or GST number
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('gst_number', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $vendors = $query->latest()->paginate(15);

        // Statistics
        $stats = [
            'total_vendors' => Vendor::count(),
            'active_vendors' => Vendor::where('is_active', true)->count(),
            'total_purchase_value' => PurchaseOrder::where('status', '!=', 'cancelled')->sum('total_amount'),
            'this_month_purchases' => PurchaseOrder::whereMonth('created_at', now()->month)->sum('total_amount'),
        ];

        return view('vendors.index', compact('vendors', 'stats'));
    }

    /**
     * Show the form for creating a new vendor.
     */
    public function create()
    {
        $products = Product::active()->orderBy('name')->get();
        return view('vendors.create', compact('products'));
    }

    /**
     * Store a newly created vendor in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:vendors,code',
                'email' => 'required|email|unique:vendors,email',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'gst_number' => 'nullable|string|max:15|unique:vendors,gst_number',
                // Only validate products when at least one product entry is present
                'products' => 'nullable|array',
                'products.*.product_id' => 'required_with:products.*|exists:products,id',
                'products.*.supply_price' => 'required_with:products.*|numeric|min:0.01',
                'products.*.is_primary_supplier' => 'boolean',
            ]);

            DB::transaction(function () use ($request) {
                $vendor = Vendor::create([
                    'name' => $request->name,
                    'code' => $request->code,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'gst_number' => $request->gst_number,
                    'is_active' => true,
                ]);

                // Attach products with pricing
                if ($request->filled('products') && is_array($request->products)) {
                    // Filter out any empty template rows
                    $validProducts = collect($request->products)
                        ->filter(fn ($p) => !empty($p['product_id']) && isset($p['supply_price']))
                        ->all();

                    foreach ($validProducts as $product) {
                        $vendor->products()->attach($product['product_id'], [
                            'supply_price' => $product['supply_price'],
                            'is_primary_supplier' => !empty($product['is_primary_supplier']),
                        ]);
                    }
                }
            });

            return redirect()->route('vendors.index')
                ->with('success', 'Vendor created successfully!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Vendor creation failed: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e
            ]);
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create vendor. Please check all fields and try again. Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified vendor.
     */
    public function show(Vendor $vendor)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized');
        }

        $vendor->load([
            'purchaseOrders' => function ($query) {
                $query->latest()->take(10);
            },
            'products' => function ($query) {
                $query->orderBy('name');
            },
            'creditTransactions' => function ($query) {
                $query->latest()->take(10);
            }
        ]);

        // Calculate statistics
        $stats = [
            'total_purchases' => $vendor->purchaseOrders->sum('total_amount'),
            'order_count' => $vendor->purchaseOrders->count(),
            'credit_balance' => $vendor->getCreditBalance(),
            'products_supplied' => $vendor->products->count(),
            'avg_order_value' => $vendor->purchaseOrders->count() > 0 
                ? $vendor->purchaseOrders->sum('total_amount') / $vendor->purchaseOrders->count() 
                : 0,
            'last_order_date' => $vendor->purchaseOrders->first()?->created_at,
        ];

        // Performance metrics
        $performance = [
            'on_time_deliveries' => $vendor->purchaseOrders()
                ->where('status', 'received')
                ->whereColumn('actual_delivery_date', '<=', 'expected_delivery_date')
                ->count(),
            'total_deliveries' => $vendor->purchaseOrders()
                ->where('status', 'received')
                ->count(),
            'this_month_orders' => $vendor->purchaseOrders()
                ->whereMonth('created_at', now()->month)
                ->count(),
            'this_month_value' => $vendor->purchaseOrders()
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount'),
        ];

        return view('vendors.show', compact('vendor', 'stats', 'performance'));
    }

    /**
     * Show the form for editing the specified vendor.
     */
    public function edit(Vendor $vendor)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized');
        }
        $vendor->load('products');
        $allProducts = Product::active()->orderBy('name')->get();
        
        return view('vendors.edit', compact('vendor', 'allProducts'));
    }

    /**
     * Update the specified vendor in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:vendors,code,' . $vendor->id,
            'email' => 'required|email|unique:vendors,email,' . $vendor->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'gst_number' => 'nullable|string|max:15|unique:vendors,gst_number,' . $vendor->id,
            'is_active' => 'boolean',
            'products' => 'nullable|array',
            'products.*.product_id' => 'required_with:products.*|exists:products,id',
            'products.*.supply_price' => 'required_with:products.*|numeric|min:0.01',
            'products.*.is_primary_supplier' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $vendor) {
            $vendor->update([
                'name' => $request->name,
                'code' => $request->code,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'gst_number' => $request->gst_number,
                'is_active' => $request->has('is_active'),
            ]);

            // Update product relationships
            $vendor->products()->detach();
            if ($request->filled('products')) {
                $validProducts = collect($request->products)
                    ->filter(fn ($p) => !empty($p['product_id']) && isset($p['supply_price']))
                    ->all();

                foreach ($validProducts as $product) {
                    $vendor->products()->attach($product['product_id'], [
                        'supply_price' => $product['supply_price'],
                        'is_primary_supplier' => !empty($product['is_primary_supplier']),
                    ]);
                }
            }
        });

        return redirect()->route('vendors.show', $vendor)
            ->with('success', 'Vendor updated successfully!');
    }

    /**
     * Remove the specified vendor from storage.
     */
    public function destroy(Vendor $vendor)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized');
        }
        // Check if vendor has any purchase orders
        if ($vendor->purchaseOrders()->count() > 0) {
            return redirect()->route('vendors.index')
                ->with('error', 'Cannot delete vendor with existing purchase orders. Please mark as inactive instead.');
        }

        $vendor->delete();

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor deleted successfully!');
    }

    /**
     * Show vendor analytics and performance.
     */
    public function analytics(Vendor $vendor)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized');
        }
        // Monthly purchase data for chart
        $monthlyData = $vendor->purchaseOrders()
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        // Product-wise purchase analysis
        $productAnalysis = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->where('purchase_orders.vendor_id', $vendor->id)
            ->where('purchase_orders.status', '!=', 'cancelled')
            ->selectRaw('
                products.name,
                products.category,
                SUM(purchase_order_items.quantity) as total_quantity,
                SUM(purchase_order_items.total_price) as total_value,
                AVG(purchase_order_items.unit_price) as avg_price,
                COUNT(DISTINCT purchase_orders.id) as order_count
            ')
            ->groupBy('products.id', 'products.name', 'products.category')
            ->orderByDesc('total_value')
            ->get();

        // Payment analysis
        $paymentStats = [
            'total_paid' => $vendor->creditTransactions()
                ->where('type', 'credit_paid')
                ->sum('amount'),
            'total_received' => $vendor->creditTransactions()
                ->where('type', 'credit_received')
                ->sum('amount'),
            'pending_payments' => $vendor->purchaseOrders()
                ->where('status', 'received')
                ->whereDoesntHave('payments')
                ->sum('total_amount'),
        ];

        return view('vendors.analytics', compact('vendor', 'monthlyData', 'productAnalysis', 'paymentStats'));
    }

    /**
     * Show vendor credit management.
     */
    public function creditManagement(Vendor $vendor)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized');
        }
        $creditTransactions = $vendor->creditTransactions()
            ->with('user')
            ->latest()
            ->paginate(20);

        $creditBalance = $vendor->getCreditBalance();

        return view('vendors.credit-management', compact('vendor', 'creditTransactions', 'creditBalance'));
    }

    /**
     * Add credit transaction for vendor.
     */
    public function addCreditTransaction(Request $request, Vendor $vendor)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized');
        }
        $request->validate([
            'type' => 'required|in:credit_received,credit_paid',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        CreditTransaction::create([
            'vendor_id' => $vendor->id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        return redirect()->route('vendors.credit-management', $vendor)
            ->with('success', 'Credit transaction added successfully!');
    }

    /**
     * Display vendor purchase orders overview.
     */
    public function purchaseOrders(Request $request)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized');
        }
        $query = PurchaseOrder::with(['vendor', 'items.product', 'branch'])
            ->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by vendor
        if ($request->has('vendor') && $request->vendor !== '') {
            $query->where('vendor_id', $request->vendor);
        }

        // Search by PO number
        if ($request->has('search') && $request->search !== '') {
            $query->where('po_number', 'like', "%{$request->search}%");
        }

        $purchaseOrders = $query->paginate(15);
        $vendors = Vendor::where('is_active', true)->get();

        // Statistics
        $stats = [
            'total_orders' => PurchaseOrder::count(),
            'pending_orders' => PurchaseOrder::where('status', 'pending')->count(),
            'confirmed_orders' => PurchaseOrder::where('status', 'confirmed')->count(),
            'total_value' => PurchaseOrder::where('status', '!=', 'cancelled')->sum('total_amount'),
        ];

        return view('vendors.purchase-orders', compact('purchaseOrders', 'vendors', 'stats'));
    }
}