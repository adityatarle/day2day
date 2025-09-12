<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransportExpense;
use App\Models\StockTransfer;
use App\Services\TransportExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransportExpenseController extends Controller
{
    protected $expenseService;

    public function __construct(TransportExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
        $this->middleware('auth');
        $this->middleware('role:admin,super_admin');
    }

    /**
     * Display listing of transport expenses
     */
    public function index(Request $request)
    {
        $query = TransportExpense::with(['stockTransfer.toBranch'])
                                ->orderBy('expense_date', 'desc');

        // Apply filters
        if ($request->filled('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }

        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', "%{$request->vendor_name}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('transfer_id')) {
            $query->where('stock_transfer_id', $request->transfer_id);
        }

        $expenses = $query->paginate(20);

        // Get statistics
        $stats = $this->expenseService->getExpenseStatistics(
            null, 
            null, 
            $request->date_from, 
            $request->date_to
        );

        return view('admin.transport-expenses.index', compact('expenses', 'stats'));
    }

    /**
     * Show form for creating new transport expense
     */
    public function create(Request $request)
    {
        $transferId = $request->get('transfer_id');
        $transfer = $transferId ? StockTransfer::find($transferId) : null;

        return view('admin.transport-expenses.create', compact('transfer'));
    }

    /**
     * Store new transport expense
     */
    public function store(Request $request)
    {
        $request->validate([
            'stock_transfer_id' => 'required|exists:stock_transfers,id',
            'expense_type' => 'required|in:vehicle_rent,fuel,driver_payment,toll_charges,loading_charges,unloading_charges,insurance,other',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'vendor_name' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:100',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'receipts.*' => 'file|max:10240', // 10MB max per file
        ]);

        try {
            $transfer = StockTransfer::findOrFail($request->stock_transfer_id);
            
            $expenseData = $request->only([
                'expense_type', 'description', 'amount', 'vendor_name',
                'receipt_number', 'expense_date', 'payment_method', 'notes'
            ]);

            $expense = $this->expenseService->addExpense($transfer, $expenseData, Auth::user());

            // Upload receipts if provided
            if ($request->hasFile('receipts')) {
                $this->expenseService->uploadReceipts($expense, $request->file('receipts'));
            }

            return redirect()->route('admin.transport-expenses.show', $expense)
                           ->with('success', 'Transport expense added successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Failed to add transport expense: ' . $e->getMessage());
        }
    }

    /**
     * Show specific transport expense
     */
    public function show(TransportExpense $transportExpense)
    {
        $transportExpense->load(['stockTransfer.toBranch']);
        
        // Get transport metrics for the transfer
        $metrics = $this->expenseService->calculateTransportMetrics($transportExpense->stockTransfer);

        return view('admin.transport-expenses.show', compact('transportExpense', 'metrics'));
    }

    /**
     * Show form for editing transport expense
     */
    public function edit(TransportExpense $transportExpense)
    {
        $transportExpense->load(['stockTransfer']);
        
        return view('admin.transport-expenses.edit', compact('transportExpense'));
    }

    /**
     * Update transport expense
     */
    public function update(Request $request, TransportExpense $transportExpense)
    {
        $request->validate([
            'expense_type' => 'required|in:vehicle_rent,fuel,driver_payment,toll_charges,loading_charges,unloading_charges,insurance,other',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'vendor_name' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:100',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'receipts.*' => 'file|max:10240', // 10MB max per file
        ]);

        try {
            $updateData = $request->only([
                'expense_type', 'description', 'amount', 'vendor_name',
                'receipt_number', 'expense_date', 'payment_method', 'notes'
            ]);

            $result = $this->expenseService->updateExpense($transportExpense, $updateData, Auth::user());

            if ($result) {
                // Upload additional receipts if provided
                if ($request->hasFile('receipts')) {
                    $this->expenseService->uploadReceipts($transportExpense, $request->file('receipts'));
                }

                return redirect()->route('admin.transport-expenses.show', $transportExpense)
                               ->with('success', 'Transport expense updated successfully.');
            } else {
                return back()->with('error', 'Failed to update transport expense.');
            }

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Failed to update transport expense: ' . $e->getMessage());
        }
    }

    /**
     * Delete transport expense
     */
    public function destroy(TransportExpense $transportExpense)
    {
        try {
            $result = $this->expenseService->deleteExpense($transportExpense, Auth::user());

            if ($result) {
                return redirect()->route('admin.transport-expenses.index')
                               ->with('success', 'Transport expense deleted successfully.');
            } else {
                return back()->with('error', 'Failed to delete transport expense.');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete transport expense: ' . $e->getMessage());
        }
    }

    /**
     * Show expenses for specific transfer
     */
    public function transferExpenses(StockTransfer $stockTransfer)
    {
        $expenses = $stockTransfer->transportExpenses()
                                 ->orderBy('expense_date', 'desc')
                                 ->get();

        $metrics = $this->expenseService->calculateTransportMetrics($stockTransfer);
        
        $stats = $this->expenseService->getExpenseStatistics($stockTransfer->id);

        return view('admin.transport-expenses.transfer', compact('stockTransfer', 'expenses', 'metrics', 'stats'));
    }

    /**
     * Generate expense report
     */
    public function report(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'report_type' => 'required|in:summary,detailed,vendor_analysis',
        ]);

        $branchId = $request->branch_id;
        $startDate = $request->date_from;
        $endDate = $request->date_to;

        switch ($request->report_type) {
            case 'summary':
                $data = $this->expenseService->getExpenseStatistics(null, $branchId, $startDate, $endDate);
                break;
            case 'detailed':
                $data = $this->expenseService->generateExpenseReport($branchId, $startDate, $endDate);
                break;
            case 'vendor_analysis':
                $data = $this->generateVendorAnalysis($branchId, $startDate, $endDate);
                break;
        }

        return view('admin.transport-expenses.report', compact('data', 'request'));
    }

    /**
     * Export expenses to CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'expense_type' => 'nullable|string',
        ]);

        $query = TransportExpense::with(['stockTransfer.toBranch'])
                                ->orderBy('expense_date', 'desc');

        // Apply filters
        if ($request->filled('branch_id')) {
            $query->whereHas('stockTransfer', function ($q) use ($request) {
                $q->where('to_branch_id', $request->branch_id);
            });
        }

        if ($request->filled('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        $expenses = $query->get();

        $filename = 'transport_expenses_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($expenses) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Date', 'Transfer Number', 'Branch', 'Expense Type', 'Description',
                'Amount', 'Vendor', 'Receipt Number', 'Payment Method', 'Notes'
            ]);

            // CSV data
            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->expense_date->format('Y-m-d'),
                    $expense->stockTransfer->transfer_number,
                    $expense->stockTransfer->toBranch->name,
                    $expense->getExpenseTypeDisplayName(),
                    $expense->description,
                    $expense->amount,
                    $expense->vendor_name,
                    $expense->receipt_number,
                    $expense->payment_method,
                    $expense->notes,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Upload additional receipts for expense
     */
    public function uploadReceipts(Request $request, TransportExpense $transportExpense)
    {
        $request->validate([
            'receipts.*' => 'required|file|max:10240', // 10MB max per file
        ]);

        try {
            $uploadedFiles = $this->expenseService->uploadReceipts(
                $transportExpense, 
                $request->file('receipts')
            );

            return back()->with('success', count($uploadedFiles) . ' receipt(s) uploaded successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload receipts: ' . $e->getMessage());
        }
    }

    /**
     * Delete receipt file
     */
    public function deleteReceipt(Request $request, TransportExpense $transportExpense)
    {
        $request->validate([
            'receipt_index' => 'required|integer|min:0',
        ]);

        try {
            $receipts = $transportExpense->receipts ?? [];
            $index = $request->receipt_index;

            if (isset($receipts[$index])) {
                // Delete file from storage
                \Storage::disk('public')->delete($receipts[$index]['path']);
                
                // Remove from array
                unset($receipts[$index]);
                $receipts = array_values($receipts); // Re-index array

                $transportExpense->update(['receipts' => $receipts]);

                return back()->with('success', 'Receipt deleted successfully.');
            } else {
                return back()->with('error', 'Receipt not found.');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete receipt: ' . $e->getMessage());
        }
    }

    /**
     * Generate vendor analysis
     */
    protected function generateVendorAnalysis(?int $branchId, ?string $startDate, ?string $endDate): array
    {
        $query = TransportExpense::query();

        if ($branchId) {
            $query->whereHas('stockTransfer', function ($q) use ($branchId) {
                $q->where('to_branch_id', $branchId);
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        }

        $expenses = $query->whereNotNull('vendor_name')->get();

        $vendorStats = $expenses->groupBy('vendor_name')->map(function ($group) {
            $vendor = $group->first()->vendor_name;
            return [
                'vendor_name' => $vendor,
                'total_expenses' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'average_amount' => $group->avg('amount'),
                'expense_types' => $group->pluck('expense_type')->unique()->values(),
                'first_transaction' => $group->min('expense_date'),
                'last_transaction' => $group->max('expense_date'),
                'receipts_provided' => $group->filter(fn($e) => $e->hasReceipts())->count(),
                'receipt_compliance' => $group->count() > 0 ? 
                    ($group->filter(fn($e) => $e->hasReceipts())->count() / $group->count()) * 100 : 0,
            ];
        })->sortByDesc('total_amount')->values();

        return [
            'total_vendors' => $vendorStats->count(),
            'total_amount' => $expenses->sum('amount'),
            'vendor_statistics' => $vendorStats->toArray(),
            'expense_distribution' => $expenses->groupBy('expense_type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                    'unique_vendors' => $group->pluck('vendor_name')->unique()->count(),
                ];
            })->toArray(),
        ];
    }
}