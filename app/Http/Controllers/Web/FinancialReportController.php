<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\FinancialReportService;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class FinancialReportController extends Controller
{
    protected $financialReportService;

    public function __construct(FinancialReportService $financialReportService)
    {
        $this->financialReportService = $financialReportService;
    }

    /**
     * Display financial reports dashboard
     */
    public function index()
    {
        $branches = Branch::active()->get();
        
        return view('reports.financial.index', compact('branches'));
    }

    /**
     * Generate Profit & Loss Statement
     */
    public function profitLoss(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'branch_id' => 'nullable|exists:branches,id',
            'format' => 'nullable|in:view,excel,pdf,csv',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $branchId = $request->branch_id;
        $format = $request->format ?? 'view';

        $report = $this->financialReportService->generateProfitLossStatement(
            $startDate,
            $endDate,
            $branchId
        );

        $branch = $branchId ? Branch::find($branchId) : null;

        switch ($format) {
            case 'excel':
                return $this->exportProfitLossToExcel($report, $branch);
            case 'pdf':
                return $this->exportProfitLossToPdf($report, $branch);
            case 'csv':
                return $this->exportProfitLossToCsv($report, $branch);
            default:
                return view('reports.financial.profit-loss', compact('report', 'branch'));
        }
    }

    /**
     * Generate Cash Flow Statement
     */
    public function cashFlow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'branch_id' => 'nullable|exists:branches,id',
            'format' => 'nullable|in:view,excel,pdf,csv',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $branchId = $request->branch_id;
        $format = $request->format ?? 'view';

        $report = $this->financialReportService->generateCashFlowStatement(
            $startDate,
            $endDate,
            $branchId
        );

        $branch = $branchId ? Branch::find($branchId) : null;

        switch ($format) {
            case 'excel':
                return $this->exportCashFlowToExcel($report, $branch);
            case 'pdf':
                return $this->exportCashFlowToPdf($report, $branch);
            case 'csv':
                return $this->exportCashFlowToCsv($report, $branch);
            default:
                return view('reports.financial.cash-flow', compact('report', 'branch'));
        }
    }

    /**
     * Generate Balance Sheet
     */
    public function balanceSheet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'as_of_date' => 'required|date',
            'branch_id' => 'nullable|exists:branches,id',
            'format' => 'nullable|in:view,excel,pdf,csv',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $asOfDate = Carbon::parse($request->as_of_date);
        $branchId = $request->branch_id;
        $format = $request->format ?? 'view';

        $report = $this->financialReportService->generateBalanceSheet(
            $asOfDate,
            $branchId
        );

        $branch = $branchId ? Branch::find($branchId) : null;

        switch ($format) {
            case 'excel':
                return $this->exportBalanceSheetToExcel($report, $branch);
            case 'pdf':
                return $this->exportBalanceSheetToPdf($report, $branch);
            case 'csv':
                return $this->exportBalanceSheetToCsv($report, $branch);
            default:
                return view('reports.financial.balance-sheet', compact('report', 'branch'));
        }
    }

    /**
     * Generate Sales Register (GST Compliance)
     */
    public function salesRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'branch_id' => 'nullable|exists:branches,id',
            'format' => 'nullable|in:view,excel,pdf,csv',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $branchId = $request->branch_id;
        $format = $request->format ?? 'view';

        $report = $this->financialReportService->generateSalesRegister(
            $startDate,
            $endDate,
            $branchId
        );

        $branch = $branchId ? Branch::find($branchId) : null;

        switch ($format) {
            case 'excel':
                return $this->exportSalesRegisterToExcel($report, $branch);
            case 'pdf':
                return $this->exportSalesRegisterToPdf($report, $branch);
            case 'csv':
                return $this->exportSalesRegisterToCsv($report, $branch);
            default:
                return view('reports.financial.sales-register', compact('report', 'branch'));
        }
    }

    /**
     * Generate Purchase Register (GST Compliance)
     */
    public function purchaseRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'branch_id' => 'nullable|exists:branches,id',
            'format' => 'nullable|in:view,excel,pdf,csv',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $branchId = $request->branch_id;
        $format = $request->format ?? 'view';

        $report = $this->financialReportService->generatePurchaseRegister(
            $startDate,
            $endDate,
            $branchId
        );

        $branch = $branchId ? Branch::find($branchId) : null;

        switch ($format) {
            case 'excel':
                return $this->exportPurchaseRegisterToExcel($report, $branch);
            case 'pdf':
                return $this->exportPurchaseRegisterToPdf($report, $branch);
            case 'csv':
                return $this->exportPurchaseRegisterToCsv($report, $branch);
            default:
                return view('reports.financial.purchase-register', compact('report', 'branch'));
        }
    }

    /**
     * Generate Expense Analysis
     */
    public function expenseAnalysis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'branch_id' => 'nullable|exists:branches,id',
            'format' => 'nullable|in:view,excel,pdf,csv',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $branchId = $request->branch_id;
        $format = $request->format ?? 'view';

        $report = $this->financialReportService->generateExpenseAnalysis(
            $startDate,
            $endDate,
            $branchId
        );

        $branch = $branchId ? Branch::find($branchId) : null;

        switch ($format) {
            case 'excel':
                return $this->exportExpenseAnalysisToExcel($report, $branch);
            case 'pdf':
                return $this->exportExpenseAnalysisToPdf($report, $branch);
            case 'csv':
                return $this->exportExpenseAnalysisToCsv($report, $branch);
            default:
                return view('reports.financial.expense-analysis', compact('report', 'branch'));
        }
    }

    // Export methods for Profit & Loss Statement
    private function exportProfitLossToExcel($report, $branch)
    {
        $filename = 'profit_loss_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.xlsx';

        return Excel::download(new \App\Exports\ProfitLossExport($report, $branch), $filename);
    }

    private function exportProfitLossToPdf($report, $branch)
    {
        $pdf = Pdf::loadView('reports.financial.exports.profit-loss-pdf', compact('report', 'branch'));
        
        $filename = 'profit_loss_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    private function exportProfitLossToCsv($report, $branch)
    {
        $filename = 'profit_loss_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.csv';

        return Excel::download(new \App\Exports\ProfitLossExport($report, $branch), $filename);
    }

    // Export methods for Cash Flow Statement
    private function exportCashFlowToExcel($report, $branch)
    {
        $filename = 'cash_flow_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.xlsx';

        return Excel::download(new \App\Exports\CashFlowExport($report, $branch), $filename);
    }

    private function exportCashFlowToPdf($report, $branch)
    {
        $pdf = Pdf::loadView('reports.financial.exports.cash-flow-pdf', compact('report', 'branch'));
        
        $filename = 'cash_flow_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    private function exportCashFlowToCsv($report, $branch)
    {
        $filename = 'cash_flow_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.csv';

        return Excel::download(new \App\Exports\CashFlowExport($report, $branch), $filename);
    }

    // Export methods for Balance Sheet
    private function exportBalanceSheetToExcel($report, $branch)
    {
        $filename = 'balance_sheet_' . $report['as_of_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.xlsx';

        return Excel::download(new \App\Exports\BalanceSheetExport($report, $branch), $filename);
    }

    private function exportBalanceSheetToPdf($report, $branch)
    {
        $pdf = Pdf::loadView('reports.financial.exports.balance-sheet-pdf', compact('report', 'branch'));
        
        $filename = 'balance_sheet_' . $report['as_of_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    private function exportBalanceSheetToCsv($report, $branch)
    {
        $filename = 'balance_sheet_' . $report['as_of_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.csv';

        return Excel::download(new \App\Exports\BalanceSheetExport($report, $branch), $filename);
    }

    // Export methods for Sales Register
    private function exportSalesRegisterToExcel($report, $branch)
    {
        $filename = 'sales_register_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.xlsx';

        return Excel::download(new \App\Exports\SalesRegisterExport($report, $branch), $filename);
    }

    private function exportSalesRegisterToPdf($report, $branch)
    {
        $pdf = Pdf::loadView('reports.financial.exports.sales-register-pdf', compact('report', 'branch'));
        
        $filename = 'sales_register_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    private function exportSalesRegisterToCsv($report, $branch)
    {
        $filename = 'sales_register_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.csv';

        return Excel::download(new \App\Exports\SalesRegisterExport($report, $branch), $filename);
    }

    // Export methods for Purchase Register
    private function exportPurchaseRegisterToExcel($report, $branch)
    {
        $filename = 'purchase_register_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.xlsx';

        return Excel::download(new \App\Exports\PurchaseRegisterExport($report, $branch), $filename);
    }

    private function exportPurchaseRegisterToPdf($report, $branch)
    {
        $pdf = Pdf::loadView('reports.financial.exports.purchase-register-pdf', compact('report', 'branch'));
        
        $filename = 'purchase_register_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    private function exportPurchaseRegisterToCsv($report, $branch)
    {
        $filename = 'purchase_register_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.csv';

        return Excel::download(new \App\Exports\PurchaseRegisterExport($report, $branch), $filename);
    }

    // Export methods for Expense Analysis
    private function exportExpenseAnalysisToExcel($report, $branch)
    {
        $filename = 'expense_analysis_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.xlsx';

        return Excel::download(new \App\Exports\ExpenseAnalysisExport($report, $branch), $filename);
    }

    private function exportExpenseAnalysisToPdf($report, $branch)
    {
        $pdf = Pdf::loadView('reports.financial.exports.expense-analysis-pdf', compact('report', 'branch'));
        
        $filename = 'expense_analysis_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    private function exportExpenseAnalysisToCsv($report, $branch)
    {
        $filename = 'expense_analysis_' . $report['period']['start_date'] . '_to_' . $report['period']['end_date'];
        if ($branch) {
            $filename .= '_' . str_replace(' ', '_', $branch->name);
        }
        $filename .= '.csv';

        return Excel::download(new \App\Exports\ExpenseAnalysisExport($report, $branch), $filename);
    }
}
