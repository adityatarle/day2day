<?php

namespace App\Http\Controllers;

use App\Models\LocalPurchase;
use App\Models\LocalPurchaseItem;
use App\Models\LocalPurchaseNotification;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\User;
use App\Models\PurchaseOrder;
use App\Jobs\SendLocalPurchaseNotificationEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LocalPurchaseController extends Controller
{
    /**
     * Display a listing of local purchases.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = LocalPurchase::with(['branch', 'manager', 'vendor', 'items.product']);

        // Branch managers can only see their branch's purchases
        if ($user->isBranchManager()) {
            $query->where('branch_id', $user->branch_id);
        }

        // Admin can filter by branch
        if ($request->filled('branch_id') && ($user->isAdmin() || $user->isSuperAdmin())) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('purchase_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('purchase_date', '<=', $request->date_to);
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $localPurchases = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $vendors = Vendor::active()->get();
        $branches = $user->isAdmin() || $user->isSuperAdmin() 
            ? \App\Models\Branch::active()->get() 
            : collect();

        // Add statistics for admin view
        $stats = [];
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            $stats = [
                'pending' => LocalPurchase::where('status', 'pending')->count(),
                'approved_today' => LocalPurchase::where('status', 'approved')
                    ->whereDate('approved_at', today())->count(),
                'rejected_today' => LocalPurchase::where('status', 'rejected')
                    ->whereDate('approved_at', today())->count(),
                'total_value' => LocalPurchase::where('status', 'pending')->sum('total_amount'),
            ];
        }

        // Use admin view for admin users
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return view('admin.local-purchases.index', compact('localPurchases', 'vendors', 'branches', 'stats'));
        }

        return view('local-purchases.index', compact('localPurchases', 'vendors', 'branches'));
    }

    /**
     * Show the form for creating a new local purchase.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isBranchManager()) {
            abort(403, 'Only branch managers can create local purchases');
        }

        $products = Product::active()
            ->whereHas('branches', function ($query) use ($user) {
                $query->where('branch_id', $user->branch_id);
            })
            ->get();

        $vendors = Vendor::active()->get();
        
        // Get pending purchase orders for this branch if any
        $pendingOrders = PurchaseOrder::where('branch_id', $user->branch_id)
            ->whereIn('status', ['pending', 'partial'])
            ->with('items.product')
            ->get();

        $selectedOrder = null;
        if ($request->filled('purchase_order_id')) {
            $selectedOrder = $pendingOrders->find($request->purchase_order_id);
        }

        return view('local-purchases.create', compact('products', 'vendors', 'pendingOrders', 'selectedOrder'));
    }

    /**
     * Store a newly created local purchase.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isBranchManager()) {
            abort(403, 'Only branch managers can create local purchases');
        }

        Log::info('Local purchase form submitted', [
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'request_data' => $request->all()
        ]);

        $validated = $request->validate([
            'vendor_id' => 'nullable|exists:vendors,id',
            'vendor_name' => 'required_without:vendor_id|nullable|string|max:255',
            'vendor_phone' => 'nullable|string|max:20',
            'purchase_date' => 'required|date',
            'payment_method' => ['required', Rule::in(['cash', 'upi', 'credit', 'bank_transfer', 'card', 'other'])],
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'required|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
        ]);

        Log::info('Validation passed', ['validated_data' => $validated]);

        try {
            DB::beginTransaction();

            // Handle receipt upload
            $receiptPath = null;
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')->store('local-purchases/receipts', 'public');
            }

            // Create local purchase
            $localPurchase = LocalPurchase::create([
                'branch_id' => $user->branch_id,
                'manager_id' => $user->id,
                'vendor_id' => $validated['vendor_id'],
                'vendor_name' => $validated['vendor_name'] ?? null,
                'vendor_phone' => $validated['vendor_phone'] ?? null,
                'purchase_date' => $validated['purchase_date'],
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'receipt_path' => $receiptPath,
                'status' => 'pending',
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
            ]);

            // Create purchase items
            foreach ($validated['items'] as $itemData) {
                $localPurchase->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'discount_rate' => $itemData['discount_rate'] ?? 0,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Calculate totals
            $localPurchase->calculateTotals();

            // Create notifications for admins
            $this->createNotifications($localPurchase, 'created');

            DB::commit();

            return redirect()->route('branch.local-purchases.show', $localPurchase)
                ->with('success', 'Local purchase created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create local purchase: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            // Delete uploaded file if exists
            if (isset($receiptPath) && $receiptPath && Storage::disk('public')->exists($receiptPath)) {
                Storage::disk('public')->delete($receiptPath);
            }

            return back()->withInput()
                ->with('error', 'Failed to create local purchase: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified local purchase.
     */
    public function show(LocalPurchase $localPurchase)
    {
        $user = auth()->user();
        
        // Check authorization
        if ($user->isBranchManager() && $localPurchase->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized access');
        }

        $localPurchase->load(['branch', 'manager', 'vendor', 'items.product', 'approvedBy', 'expense', 'purchaseOrder']);

        // Use admin view for admin users
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return view('admin.local-purchases.show', compact('localPurchase'));
        }

        return view('local-purchases.show', compact('localPurchase'));
    }

    /**
     * Show the form for editing the local purchase.
     */
    public function edit(LocalPurchase $localPurchase)
    {
        $user = auth()->user();
        
        // Only allow editing if pending and by the creator
        if ($localPurchase->manager_id !== $user->id || !$localPurchase->isPending()) {
            abort(403, 'You cannot edit this purchase');
        }

        $products = Product::active()
            ->whereHas('branches', function ($query) use ($user) {
                $query->where('branch_id', $user->branch_id);
            })
            ->get();

        $vendors = Vendor::active()->get();
        $localPurchase->load('items.product');

        return view('local-purchases.edit', compact('localPurchase', 'products', 'vendors'));
    }

    /**
     * Update the specified local purchase.
     */
    public function update(Request $request, LocalPurchase $localPurchase)
    {
        $user = auth()->user();
        
        // Only allow updating if pending and by the creator
        if ($localPurchase->manager_id !== $user->id || !$localPurchase->isPending()) {
            abort(403, 'You cannot update this purchase');
        }

        $validated = $request->validate([
            'vendor_id' => 'nullable|exists:vendors,id',
            'vendor_name' => 'required_without:vendor_id|nullable|string|max:255',
            'vendor_phone' => 'nullable|string|max:20',
            'purchase_date' => 'required|date',
            'payment_method' => ['required', Rule::in(['cash', 'upi', 'credit', 'bank_transfer', 'card', 'other'])],
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:local_purchase_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'required|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Handle receipt upload
            if ($request->hasFile('receipt')) {
                // Delete old receipt if exists
                if ($localPurchase->receipt_path && Storage::disk('public')->exists($localPurchase->receipt_path)) {
                    Storage::disk('public')->delete($localPurchase->receipt_path);
                }
                
                $validated['receipt_path'] = $request->file('receipt')->store('local-purchases/receipts', 'public');
            }

            // Update local purchase
            $localPurchase->update([
                'vendor_id' => $validated['vendor_id'],
                'vendor_name' => $validated['vendor_name'] ?? null,
                'vendor_phone' => $validated['vendor_phone'] ?? null,
                'purchase_date' => $validated['purchase_date'],
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'receipt_path' => $validated['receipt_path'] ?? $localPurchase->receipt_path,
            ]);

            // Track existing items
            $existingItemIds = $localPurchase->items->pluck('id')->toArray();
            $updatedItemIds = [];

            // Update or create items
            foreach ($validated['items'] as $itemData) {
                if (!empty($itemData['id'])) {
                    // Update existing item
                    $item = $localPurchase->items()->find($itemData['id']);
                    if ($item) {
                        $item->update([
                            'product_id' => $itemData['product_id'],
                            'quantity' => $itemData['quantity'],
                            'unit' => $itemData['unit'],
                            'unit_price' => $itemData['unit_price'],
                            'tax_rate' => $itemData['tax_rate'] ?? 0,
                            'discount_rate' => $itemData['discount_rate'] ?? 0,
                            'notes' => $itemData['notes'] ?? null,
                        ]);
                        $updatedItemIds[] = $item->id;
                    }
                } else {
                    // Create new item
                    $localPurchase->items()->create([
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit' => $itemData['unit'],
                        'unit_price' => $itemData['unit_price'],
                        'tax_rate' => $itemData['tax_rate'] ?? 0,
                        'discount_rate' => $itemData['discount_rate'] ?? 0,
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            }

            // Delete removed items
            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            if (!empty($itemsToDelete)) {
                $localPurchase->items()->whereIn('id', $itemsToDelete)->delete();
            }

            // Calculate totals
            $localPurchase->calculateTotals();

            // Create update notification
            $this->createNotifications($localPurchase, 'updated');

            DB::commit();

            return redirect()->route('branch.local-purchases.show', $localPurchase)
                ->with('success', 'Local purchase updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update local purchase: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to update local purchase. Please try again.');
        }
    }

    /**
     * Remove the specified local purchase.
     */
    public function destroy(LocalPurchase $localPurchase)
    {
        $user = auth()->user();
        
        // Only allow deletion if pending and by the creator
        if ($localPurchase->manager_id !== $user->id || !$localPurchase->isPending()) {
            abort(403, 'You cannot delete this purchase');
        }

        try {
            // Delete receipt if exists
            if ($localPurchase->receipt_path && Storage::disk('public')->exists($localPurchase->receipt_path)) {
                Storage::disk('public')->delete($localPurchase->receipt_path);
            }

            $localPurchase->delete();

            return redirect()->route('branch.local-purchases.index')
                ->with('success', 'Local purchase deleted successfully');

        } catch (\Exception $e) {
            Log::error('Failed to delete local purchase: ' . $e->getMessage());

            return back()->with('error', 'Failed to delete local purchase. Please try again.');
        }
    }

    /**
     * Approve a local purchase (Admin only).
     */
    public function approve(Request $request, LocalPurchase $localPurchase)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Only admins can approve local purchases');
        }

        if (!$localPurchase->isPending()) {
            return back()->with('error', 'This purchase cannot be approved');
        }

        try {
            DB::beginTransaction();

            // Approve the purchase
            $localPurchase->approve($user->id);

            // Update stock
            foreach ($localPurchase->items as $item) {
                $item->updateStock();
            }

            // Create expense record
            $localPurchase->createExpenseRecord();

            // Create notification
            $this->createNotifications($localPurchase, 'approved');

            // Mark as completed
            $localPurchase->markAsCompleted();

            DB::commit();

            return back()->with('success', 'Local purchase approved successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve local purchase: ' . $e->getMessage());

            return back()->with('error', 'Failed to approve local purchase. Please try again.');
        }
    }

    /**
     * Reject a local purchase (Admin only).
     */
    public function reject(Request $request, LocalPurchase $localPurchase)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Only admins can reject local purchases');
        }

        if (!$localPurchase->isPending()) {
            return back()->with('error', 'This purchase cannot be rejected');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            // Reject the purchase
            $localPurchase->reject($user->id, $validated['rejection_reason']);

            // Create notification
            $this->createNotifications($localPurchase, 'rejected');

            return back()->with('success', 'Local purchase rejected');

        } catch (\Exception $e) {
            Log::error('Failed to reject local purchase: ' . $e->getMessage());

            return back()->with('error', 'Failed to reject local purchase. Please try again.');
        }
    }

    /**
     * Create notifications for the local purchase.
     */
    private function createNotifications(LocalPurchase $localPurchase, string $type)
    {
        // Get all admin users
        $adminUsers = User::whereHas('role', function ($query) {
            $query->whereIn('name', ['admin', 'super_admin']);
        })->get();

        // Create notifications for each admin
        foreach ($adminUsers as $admin) {
            $notification = LocalPurchaseNotification::create([
                'local_purchase_id' => $localPurchase->id,
                'user_id' => $admin->id,
                'type' => $type,
            ]);

            // Dispatch email job
            SendLocalPurchaseNotificationEmail::dispatch($notification)
                ->delay(now()->addSeconds(10)); // Small delay to ensure transaction is committed
        }

        // If approved/rejected, also notify the manager
        if (in_array($type, ['approved', 'rejected'])) {
            $notification = LocalPurchaseNotification::create([
                'local_purchase_id' => $localPurchase->id,
                'user_id' => $localPurchase->manager_id,
                'type' => $type,
            ]);

            // Dispatch email job
            SendLocalPurchaseNotificationEmail::dispatch($notification)
                ->delay(now()->addSeconds(10));
        }
    }

    /**
     * Export local purchases data.
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $format = $request->get('format', 'csv');

        $query = LocalPurchase::with(['branch', 'manager', 'vendor', 'items.product']);

        // Apply same filters as index
        if ($user->isBranchManager()) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($request->filled('branch_id') && ($user->isAdmin() || $user->isSuperAdmin())) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('purchase_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('purchase_date', '<=', $request->date_to);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $localPurchases = $query->orderBy('purchase_date', 'desc')->get();

        if ($format === 'pdf') {
            return $this->exportPdf($localPurchases);
        }

        return $this->exportCsv($localPurchases);
    }

    /**
     * Export as CSV.
     */
    private function exportCsv($localPurchases)
    {
        $filename = 'local_purchases_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($localPurchases) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Purchase Number',
                'Branch',
                'Manager',
                'Vendor',
                'Purchase Date',
                'Total Amount',
                'Payment Method',
                'Status',
                'Items',
                'Created At',
            ]);

            // Data
            foreach ($localPurchases as $purchase) {
                $items = $purchase->items->map(function ($item) {
                    return $item->product->name . ' (' . $item->quantity . ' ' . $item->unit . ')';
                })->implode(', ');

                fputcsv($file, [
                    $purchase->purchase_number,
                    $purchase->branch->name,
                    $purchase->manager->name,
                    $purchase->vendor_display_name,
                    $purchase->purchase_date->format('Y-m-d'),
                    $purchase->total_amount,
                    $purchase->payment_method,
                    $purchase->status,
                    $items,
                    $purchase->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export as PDF.
     */
    private function exportPdf($localPurchases)
    {
        // Note: This requires a PDF package like dompdf or barryvdh/laravel-dompdf
        // For now, returning a simple view that can be printed
        
        return view('local-purchases.export-pdf', compact('localPurchases'));
    }
}