<?php

namespace App\Services;

use App\Models\FinancialPeriod;
use App\Models\GeneralLedger;
use App\Models\GstTransaction;
use App\Models\CashFlowTransaction;
use App\Models\CashFlowCategory;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * Generate Profit & Loss Statement
     */
    public function generateProfitLossStatement(
        Carbon $startDate,
        Carbon $endDate,
        ?int $branchId = null
    ): array {
        $query = GeneralLedger::dateRange($startDate, $endDate);
        
        if ($branchId) {
            $query->branch($branchId);
        }

        // Revenue by channel
        $revenue = [
            'retail' => $this->getRevenueByChannel('on_shop', $startDate, $endDate, $branchId),
            'wholesale' => $this->getRevenueByChannel('wholesale', $startDate, $endDate, $branchId),
            'online' => $this->getRevenueByChannel('online', $startDate, $endDate, $branchId),
        ];

        $totalRevenue = array_sum($revenue);

        // Cost of Goods Sold (COGS)
        $cogs = $this->calculateCOGS($startDate, $endDate, $branchId);

        // Gross Profit
        $grossProfit = $totalRevenue - $cogs;

        // Operating Expenses
        $operatingExpenses = $this->getOperatingExpenses($startDate, $endDate, $branchId);

        // Net Profit
        $netProfit = $grossProfit - $operatingExpenses;

        // Net Profit Margin
        $netProfitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'branch_id' => $branchId,
            ],
            'revenue' => [
                'by_channel' => $revenue,
                'total' => $totalRevenue,
            ],
            'cost_of_goods_sold' => $cogs,
            'gross_profit' => $grossProfit,
            'gross_profit_margin' => $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0,
            'operating_expenses' => $operatingExpenses,
            'net_profit' => $netProfit,
            'net_profit_margin' => $netProfitMargin,
            'comparison' => $this->getMonthOverMonthComparison($startDate, $endDate, $branchId),
        ];
    }

    /**
     * Generate Cash Flow Statement
     */
    public function generateCashFlowStatement(
        Carbon $startDate,
        Carbon $endDate,
        ?int $branchId = null
    ): array {
        $query = CashFlowTransaction::dateRange($startDate, $endDate);
        
        if ($branchId) {
            $query->branch($branchId);
        }

        // Operating Activities
        $operatingActivities = $this->getCashFlowByType('operating', $startDate, $endDate, $branchId);

        // Investing Activities
        $investingActivities = $this->getCashFlowByType('investing', $startDate, $endDate, $branchId);

        // Financing Activities
        $financingActivities = $this->getCashFlowByType('financing', $startDate, $endDate, $branchId);

        // Net Cash Flow
        $netCashFlow = $operatingActivities['net'] + $investingActivities['net'] + $financingActivities['net'];

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'branch_id' => $branchId,
            ],
            'operating_activities' => $operatingActivities,
            'investing_activities' => $investingActivities,
            'financing_activities' => $financingActivities,
            'net_cash_flow' => $netCashFlow,
        ];
    }

    /**
     * Generate Balance Sheet
     */
    public function generateBalanceSheet(
        Carbon $asOfDate,
        ?int $branchId = null
    ): array {
        // Assets
        $assets = [
            'current_assets' => $this->getAssetsByType('current_asset', $asOfDate, $branchId),
            'fixed_assets' => $this->getAssetsByType('fixed_asset', $asOfDate, $branchId),
            'intangible_assets' => $this->getAssetsByType('intangible_asset', $asOfDate, $branchId),
        ];

        $totalAssets = array_sum(array_map('array_sum', $assets));

        // Liabilities
        $liabilities = [
            'current_liabilities' => $this->getLiabilitiesByType('current_liability', $asOfDate, $branchId),
            'long_term_liabilities' => $this->getLiabilitiesByType('long_term_liability', $asOfDate, $branchId),
        ];

        $totalLiabilities = array_sum(array_map('array_sum', $liabilities));

        // Equity
        $equity = [
            'owner_equity' => $this->getEquityByType('owner_equity', $asOfDate, $branchId),
            'retained_earnings' => $this->getEquityByType('retained_earnings', $asOfDate, $branchId),
        ];

        $totalEquity = array_sum(array_map('array_sum', $equity));

        return [
            'as_of_date' => $asOfDate->format('Y-m-d'),
            'branch_id' => $branchId,
            'assets' => [
                'current_assets' => $assets['current_assets'],
                'fixed_assets' => $assets['fixed_assets'],
                'intangible_assets' => $assets['intangible_assets'],
                'total_assets' => $totalAssets,
            ],
            'liabilities' => [
                'current_liabilities' => $liabilities['current_liabilities'],
                'long_term_liabilities' => $liabilities['long_term_liabilities'],
                'total_liabilities' => $totalLiabilities,
            ],
            'equity' => [
                'owner_equity' => $equity['owner_equity'],
                'retained_earnings' => $equity['retained_earnings'],
                'total_equity' => $totalEquity,
            ],
            'balance_check' => $totalAssets === ($totalLiabilities + $totalEquity),
        ];
    }

    /**
     * Generate Sales Register for GST Compliance
     */
    public function generateSalesRegister(
        Carbon $startDate,
        Carbon $endDate,
        ?int $branchId = null
    ): array {
        $query = GstTransaction::sales()->dateRange($startDate, $endDate);
        
        if ($branchId) {
            $query->branch($branchId);
        }

        $transactions = $query->with(['customer', 'branch'])->get();

        $summary = [
            'total_invoices' => $transactions->count(),
            'total_taxable_value' => $transactions->sum('taxable_value'),
            'total_cgst' => $transactions->sum('cgst_amount'),
            'total_sgst' => $transactions->sum('sgst_amount'),
            'total_igst' => $transactions->sum('igst_amount'),
            'total_gst' => $transactions->sum('total_gst'),
            'total_amount' => $transactions->sum('total_amount'),
        ];

        // Rate-wise summary for GSTR-1
        $rateWiseSummary = $transactions->groupBy('gst_rate')->map(function ($group) {
            return [
                'count' => $group->count(),
                'taxable_value' => $group->sum('taxable_value'),
                'cgst' => $group->sum('cgst_amount'),
                'sgst' => $group->sum('sgst_amount'),
                'igst' => $group->sum('igst_amount'),
                'total_gst' => $group->sum('total_gst'),
            ];
        });

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'branch_id' => $branchId,
            ],
            'transactions' => $transactions,
            'summary' => $summary,
            'rate_wise_summary' => $rateWiseSummary,
        ];
    }

    /**
     * Generate Purchase Register for GST Compliance
     */
    public function generatePurchaseRegister(
        Carbon $startDate,
        Carbon $endDate,
        ?int $branchId = null
    ): array {
        $query = GstTransaction::purchases()->dateRange($startDate, $endDate);
        
        if ($branchId) {
            $query->branch($branchId);
        }

        $transactions = $query->with(['vendor', 'branch'])->get();

        $summary = [
            'total_invoices' => $transactions->count(),
            'total_taxable_value' => $transactions->sum('taxable_value'),
            'total_cgst' => $transactions->sum('cgst_amount'),
            'total_sgst' => $transactions->sum('sgst_amount'),
            'total_igst' => $transactions->sum('igst_amount'),
            'total_gst' => $transactions->sum('total_gst'),
            'total_amount' => $transactions->sum('total_amount'),
            'total_itc' => $transactions->sum('total_gst'), // Input Tax Credit
        ];

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'branch_id' => $branchId,
            ],
            'transactions' => $transactions,
            'summary' => $summary,
        ];
    }

    /**
     * Generate Expense Analysis
     */
    public function generateExpenseAnalysis(
        Carbon $startDate,
        Carbon $endDate,
        ?int $branchId = null
    ): array {
        $query = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $expenses = $query->with(['category', 'branch'])->get();

        // Category-wise breakdown
        $categoryBreakdown = $expenses->groupBy('expense_category_id')->map(function ($group) {
            $category = $group->first()->category;
            return [
                'category_name' => $category->name,
                'total_amount' => $group->sum('amount'),
                'count' => $group->count(),
                'average_amount' => $group->avg('amount'),
            ];
        });

        // Variance analysis (budget vs actual)
        $varianceAnalysis = $this->getExpenseVariance($startDate, $endDate, $branchId);

        // Cost per unit calculation
        $costPerUnit = $this->calculateCostPerUnit($startDate, $endDate, $branchId);

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'branch_id' => $branchId,
            ],
            'total_expenses' => $expenses->sum('amount'),
            'category_breakdown' => $categoryBreakdown,
            'variance_analysis' => $varianceAnalysis,
            'cost_per_unit' => $costPerUnit,
            'expenses' => $expenses,
        ];
    }

    /**
     * Get revenue by channel
     */
    private function getRevenueByChannel(string $channel, Carbon $startDate, Carbon $endDate, ?int $branchId = null): float
    {
        $query = Order::where('order_type', $channel)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled');
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->sum('total_amount');
    }

    /**
     * Calculate Cost of Goods Sold
     */
    private function calculateCOGS(Carbon $startDate, Carbon $endDate, ?int $branchId = null): float
    {
        // This would typically involve calculating the cost of products sold
        // For now, we'll use a simplified calculation based on purchase orders
        $query = PurchaseOrder::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'received');
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->sum('total_amount');
    }

    /**
     * Get operating expenses
     */
    private function getOperatingExpenses(Carbon $startDate, Carbon $endDate, ?int $branchId = null): float
    {
        $query = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->sum('amount');
    }

    /**
     * Get cash flow by type
     */
    private function getCashFlowByType(string $type, Carbon $startDate, Carbon $endDate, ?int $branchId = null): array
    {
        $query = CashFlowTransaction::categoryType($type)->dateRange($startDate, $endDate);
        
        if ($branchId) {
            $query->branch($branchId);
        }

        $inflow = $query->clone()->inflow()->sum('amount');
        $outflow = $query->clone()->outflow()->sum('amount');

        return [
            'inflow' => $inflow,
            'outflow' => $outflow,
            'net' => $inflow - $outflow,
        ];
    }

    /**
     * Get assets by type
     */
    private function getAssetsByType(string $type, Carbon $asOfDate, ?int $branchId = null): array
    {
        // This would typically involve querying the chart of accounts
        // For now, we'll return a simplified structure
        return [
            'cash' => $this->getCashBalance($asOfDate, $branchId),
            'inventory' => $this->getInventoryValue($asOfDate, $branchId),
            'receivables' => $this->getReceivablesBalance($asOfDate, $branchId),
        ];
    }

    /**
     * Get liabilities by type
     */
    private function getLiabilitiesByType(string $type, Carbon $asOfDate, ?int $branchId = null): array
    {
        // This would typically involve querying the chart of accounts
        return [
            'payables' => $this->getPayablesBalance($asOfDate, $branchId),
            'loans' => $this->getLoansBalance($asOfDate, $branchId),
        ];
    }

    /**
     * Get equity by type
     */
    private function getEquityByType(string $type, Carbon $asOfDate, ?int $branchId = null): array
    {
        // This would typically involve querying the chart of accounts
        return [
            'capital' => $this->getCapitalBalance($asOfDate, $branchId),
            'retained_earnings' => $this->getRetainedEarnings($asOfDate, $branchId),
        ];
    }

    /**
     * Get month-over-month comparison
     */
    private function getMonthOverMonthComparison(Carbon $startDate, Carbon $endDate, ?int $branchId = null): array
    {
        $previousStart = $startDate->copy()->subMonth();
        $previousEnd = $endDate->copy()->subMonth();

        $current = $this->generateProfitLossStatement($startDate, $endDate, $branchId);
        $previous = $this->generateProfitLossStatement($previousStart, $previousEnd, $branchId);

        return [
            'revenue_change' => $current['revenue']['total'] - $previous['revenue']['total'],
            'revenue_change_percentage' => $previous['revenue']['total'] > 0 
                ? (($current['revenue']['total'] - $previous['revenue']['total']) / $previous['revenue']['total']) * 100 
                : 0,
            'profit_change' => $current['net_profit'] - $previous['net_profit'],
            'profit_change_percentage' => $previous['net_profit'] > 0 
                ? (($current['net_profit'] - $previous['net_profit']) / $previous['net_profit']) * 100 
                : 0,
        ];
    }

    /**
     * Get expense variance
     */
    private function getExpenseVariance(Carbon $startDate, Carbon $endDate, ?int $branchId = null): array
    {
        // This would compare actual expenses against budgeted amounts
        // For now, we'll return a simplified structure
        return [
            'total_budgeted' => 0,
            'total_actual' => 0,
            'variance' => 0,
            'variance_percentage' => 0,
        ];
    }

    /**
     * Calculate cost per unit
     */
    private function calculateCostPerUnit(Carbon $startDate, Carbon $endDate, ?int $branchId = null): array
    {
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $totalUnitsSold = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->sum('total_quantity');

        return [
            'total_expenses' => $totalExpenses,
            'total_units_sold' => $totalUnitsSold,
            'cost_per_unit' => $totalUnitsSold > 0 ? $totalExpenses / $totalUnitsSold : 0,
        ];
    }

    // Helper methods for balance sheet calculations
    private function getCashBalance(Carbon $asOfDate, ?int $branchId = null): float
    {
        // This would query the cash account from chart of accounts
        return 0; // Placeholder
    }

    private function getInventoryValue(Carbon $asOfDate, ?int $branchId = null): float
    {
        $query = Product::with(['branches' => function ($q) use ($branchId) {
            if ($branchId) {
                $q->where('branches.id', $branchId);
            }
            $q->withPivot(['current_stock', 'selling_price']);
        }]);

        $products = $query->get();
        $totalValue = 0;

        foreach ($products as $product) {
            foreach ($product->branches as $branch) {
                $totalValue += $branch->pivot->current_stock * $branch->pivot->selling_price;
            }
        }

        return $totalValue;
    }

    private function getReceivablesBalance(Carbon $asOfDate, ?int $branchId = null): float
    {
        // This would query outstanding receivables
        return 0; // Placeholder
    }

    private function getPayablesBalance(Carbon $asOfDate, ?int $branchId = null): float
    {
        // This would query outstanding payables
        return 0; // Placeholder
    }

    private function getLoansBalance(Carbon $asOfDate, ?int $branchId = null): float
    {
        // This would query outstanding loans
        return 0; // Placeholder
    }

    private function getCapitalBalance(Carbon $asOfDate, ?int $branchId = null): float
    {
        // This would query owner's capital
        return 0; // Placeholder
    }

    private function getRetainedEarnings(Carbon $asOfDate, ?int $branchId = null): float
    {
        // This would calculate retained earnings
        return 0; // Placeholder
    }
}
