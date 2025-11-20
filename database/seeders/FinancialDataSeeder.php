<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChartOfAccount;
use App\Models\CashFlowCategory;
use App\Models\FinancialPeriod;
use App\Models\Budget;
use Carbon\Carbon;

class FinancialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createChartOfAccounts();
        $this->createCashFlowCategories();
        $this->createFinancialPeriods();
        $this->createBudgets();
    }

    private function createChartOfAccounts(): void
    {
        $accounts = [
            // Assets
            [
                'account_code' => '1000',
                'account_name' => 'Assets',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'parent_account_id' => null,
                'description' => 'All company assets',
            ],
            [
                'account_code' => '1100',
                'account_name' => 'Current Assets',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'parent_account_id' => 1,
                'description' => 'Assets that can be converted to cash within one year',
            ],
            [
                'account_code' => '1110',
                'account_name' => 'Cash and Cash Equivalents',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'parent_account_id' => 2,
                'description' => 'Cash in hand and bank accounts',
            ],
            [
                'account_code' => '1120',
                'account_name' => 'Accounts Receivable',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'parent_account_id' => 2,
                'description' => 'Amounts owed by customers',
            ],
            [
                'account_code' => '1130',
                'account_name' => 'Inventory',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'parent_account_id' => 2,
                'description' => 'Stock of goods for sale',
            ],
            [
                'account_code' => '1200',
                'account_name' => 'Fixed Assets',
                'account_type' => 'asset',
                'account_subtype' => 'fixed_asset',
                'parent_account_id' => 1,
                'description' => 'Long-term assets used in business operations',
            ],
            [
                'account_code' => '1210',
                'account_name' => 'Equipment',
                'account_type' => 'asset',
                'account_subtype' => 'fixed_asset',
                'parent_account_id' => 6,
                'description' => 'Business equipment and machinery',
            ],
            [
                'account_code' => '1220',
                'account_name' => 'Vehicles',
                'account_type' => 'asset',
                'account_subtype' => 'fixed_asset',
                'parent_account_id' => 6,
                'description' => 'Company vehicles',
            ],

            // Liabilities
            [
                'account_code' => '2000',
                'account_name' => 'Liabilities',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'parent_account_id' => null,
                'description' => 'All company liabilities',
            ],
            [
                'account_code' => '2100',
                'account_name' => 'Current Liabilities',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'parent_account_id' => 9,
                'description' => 'Liabilities due within one year',
            ],
            [
                'account_code' => '2110',
                'account_name' => 'Accounts Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'parent_account_id' => 10,
                'description' => 'Amounts owed to suppliers',
            ],
            [
                'account_code' => '2120',
                'account_name' => 'Accrued Expenses',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'parent_account_id' => 10,
                'description' => 'Expenses incurred but not yet paid',
            ],
            [
                'account_code' => '2200',
                'account_name' => 'Long-term Liabilities',
                'account_type' => 'liability',
                'account_subtype' => 'long_term_liability',
                'parent_account_id' => 9,
                'description' => 'Liabilities due after one year',
            ],
            [
                'account_code' => '2210',
                'account_name' => 'Loans Payable',
                'account_type' => 'liability',
                'account_subtype' => 'long_term_liability',
                'parent_account_id' => 13,
                'description' => 'Long-term loans and borrowings',
            ],

            // Equity
            [
                'account_code' => '3000',
                'account_name' => 'Equity',
                'account_type' => 'equity',
                'account_subtype' => 'owner_equity',
                'parent_account_id' => null,
                'description' => 'Owner\'s equity in the business',
            ],
            [
                'account_code' => '3100',
                'account_name' => 'Owner\'s Capital',
                'account_type' => 'equity',
                'account_subtype' => 'owner_equity',
                'parent_account_id' => 15,
                'description' => 'Initial investment by owner',
            ],
            [
                'account_code' => '3200',
                'account_name' => 'Retained Earnings',
                'account_type' => 'equity',
                'account_subtype' => 'retained_earnings',
                'parent_account_id' => 15,
                'description' => 'Accumulated profits retained in the business',
            ],

            // Revenue
            [
                'account_code' => '4000',
                'account_name' => 'Revenue',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'parent_account_id' => null,
                'description' => 'All revenue accounts',
            ],
            [
                'account_code' => '4100',
                'account_name' => 'Sales Revenue',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'parent_account_id' => 18,
                'description' => 'Revenue from sales of goods',
            ],
            [
                'account_code' => '4110',
                'account_name' => 'Retail Sales',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'parent_account_id' => 19,
                'description' => 'Revenue from retail sales',
            ],
            [
                'account_code' => '4120',
                'account_name' => 'Wholesale Sales',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'parent_account_id' => 19,
                'description' => 'Revenue from wholesale sales',
            ],
            [
                'account_code' => '4130',
                'account_name' => 'Online Sales',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'parent_account_id' => 19,
                'description' => 'Revenue from online sales',
            ],

            // Expenses
            [
                'account_code' => '5000',
                'account_name' => 'Expenses',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'parent_account_id' => null,
                'description' => 'All expense accounts',
            ],
            [
                'account_code' => '5100',
                'account_name' => 'Cost of Goods Sold',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'parent_account_id' => 24,
                'description' => 'Direct costs of goods sold',
            ],
            [
                'account_code' => '5200',
                'account_name' => 'Operating Expenses',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'parent_account_id' => 24,
                'description' => 'General operating expenses',
            ],
            [
                'account_code' => '5210',
                'account_name' => 'Rent',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'parent_account_id' => 26,
                'description' => 'Rent and lease expenses',
            ],
            [
                'account_code' => '5220',
                'account_name' => 'Salaries',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'parent_account_id' => 26,
                'description' => 'Employee salaries and wages',
            ],
            [
                'account_code' => '5230',
                'account_name' => 'Utilities',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'parent_account_id' => 26,
                'description' => 'Electricity, water, and other utilities',
            ],
            [
                'account_code' => '5240',
                'account_name' => 'Transportation',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'parent_account_id' => 26,
                'description' => 'Transportation and delivery costs',
            ],
            [
                'account_code' => '5250',
                'account_name' => 'Marketing',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'parent_account_id' => 26,
                'description' => 'Marketing and advertising expenses',
            ],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::create($account);
        }
    }

    private function createCashFlowCategories(): void
    {
        $categories = [
            // Operating Activities
            [
                'name' => 'Sales Revenue',
                'type' => 'operating',
                'subtype' => 'revenue',
                'is_positive_flow' => true,
                'description' => 'Cash received from sales',
            ],
            [
                'name' => 'Purchase Payments',
                'type' => 'operating',
                'subtype' => 'expense',
                'is_positive_flow' => false,
                'description' => 'Cash paid for purchases',
            ],
            [
                'name' => 'Rent',
                'type' => 'operating',
                'subtype' => 'expense',
                'is_positive_flow' => false,
                'description' => 'Rent payments',
            ],
            [
                'name' => 'Salaries',
                'type' => 'operating',
                'subtype' => 'expense',
                'is_positive_flow' => false,
                'description' => 'Salary payments',
            ],
            [
                'name' => 'Utilities',
                'type' => 'operating',
                'subtype' => 'expense',
                'is_positive_flow' => false,
                'description' => 'Utility payments',
            ],
            [
                'name' => 'Transportation',
                'type' => 'operating',
                'subtype' => 'expense',
                'is_positive_flow' => false,
                'description' => 'Transportation costs',
            ],

            // Investing Activities
            [
                'name' => 'Equipment Purchase',
                'type' => 'investing',
                'subtype' => 'asset_purchase',
                'is_positive_flow' => false,
                'description' => 'Purchase of equipment',
            ],
            [
                'name' => 'Vehicle Purchase',
                'type' => 'investing',
                'subtype' => 'asset_purchase',
                'is_positive_flow' => false,
                'description' => 'Purchase of vehicles',
            ],
            [
                'name' => 'Equipment Sale',
                'type' => 'investing',
                'subtype' => 'asset_sale',
                'is_positive_flow' => true,
                'description' => 'Sale of equipment',
            ],

            // Financing Activities
            [
                'name' => 'Loan Proceeds',
                'type' => 'financing',
                'subtype' => 'debt',
                'is_positive_flow' => true,
                'description' => 'Cash received from loans',
            ],
            [
                'name' => 'Loan Repayment',
                'type' => 'financing',
                'subtype' => 'debt',
                'is_positive_flow' => false,
                'description' => 'Loan repayments',
            ],
            [
                'name' => 'Owner Investment',
                'type' => 'financing',
                'subtype' => 'equity',
                'is_positive_flow' => true,
                'description' => 'Owner capital investment',
            ],
            [
                'name' => 'Owner Drawings',
                'type' => 'financing',
                'subtype' => 'equity',
                'is_positive_flow' => false,
                'description' => 'Owner withdrawals',
            ],
        ];

        foreach ($categories as $category) {
            CashFlowCategory::create($category);
        }
    }

    private function createFinancialPeriods(): void
    {
        $currentYear = now()->year;
        
        // Create monthly periods for current year
        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($currentYear, $month, 1);
            $endDate = $startDate->copy()->endOfMonth();
            
            FinancialPeriod::create([
                'name' => $startDate->format('F Y'),
                'type' => 'monthly',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_closed' => $month < now()->month,
            ]);
        }

        // Create quarterly periods
        $quarters = [
            ['Q1', 1, 3],
            ['Q2', 4, 6],
            ['Q3', 7, 9],
            ['Q4', 10, 12],
        ];

        foreach ($quarters as $quarter) {
            $startDate = Carbon::create($currentYear, $quarter[1], 1);
            $endDate = Carbon::create($currentYear, $quarter[2], 1)->endOfMonth();
            
            FinancialPeriod::create([
                'name' => $quarter[0] . ' ' . $currentYear,
                'type' => 'quarterly',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_closed' => $quarter[2] < now()->month,
            ]);
        }

        // Create yearly period
        FinancialPeriod::create([
            'name' => $currentYear,
            'type' => 'yearly',
            'start_date' => Carbon::create($currentYear, 1, 1),
            'end_date' => Carbon::create($currentYear, 12, 31),
            'is_closed' => false,
        ]);
    }

    private function createBudgets(): void
    {
        $currentPeriod = FinancialPeriod::where('type', 'monthly')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$currentPeriod) {
            return;
        }

        $budgets = [
            // Revenue budgets
            ['category' => 'revenue', 'subcategory' => 'retail_sales', 'budgeted_amount' => 500000],
            ['category' => 'revenue', 'subcategory' => 'wholesale_sales', 'budgeted_amount' => 300000],
            ['category' => 'revenue', 'subcategory' => 'online_sales', 'budgeted_amount' => 200000],
            
            // COGS budget
            ['category' => 'cogs', 'subcategory' => 'purchases', 'budgeted_amount' => 600000],
            
            // Operating expense budgets
            ['category' => 'operating_expenses', 'subcategory' => 'rent', 'budgeted_amount' => 50000],
            ['category' => 'operating_expenses', 'subcategory' => 'salaries', 'budgeted_amount' => 150000],
            ['category' => 'operating_expenses', 'subcategory' => 'utilities', 'budgeted_amount' => 20000],
            ['category' => 'operating_expenses', 'subcategory' => 'transportation', 'budgeted_amount' => 30000],
            ['category' => 'operating_expenses', 'subcategory' => 'marketing', 'budgeted_amount' => 25000],
            ['category' => 'operating_expenses', 'subcategory' => 'other', 'budgeted_amount' => 15000],
        ];

        foreach ($budgets as $budget) {
            Budget::create([
                'financial_period_id' => $currentPeriod->id,
                'category' => $budget['category'],
                'subcategory' => $budget['subcategory'],
                'budgeted_amount' => $budget['budgeted_amount'],
                'actual_amount' => 0,
            ]);
        }
    }
}
