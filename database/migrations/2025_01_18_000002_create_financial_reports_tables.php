<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Financial Periods Table
        Schema::create('financial_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "January 2025", "Q1 2025"
            $table->enum('type', ['monthly', 'quarterly', 'yearly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamps();
            
            $table->foreign('closed_by')->references('id')->on('users');
            $table->index(['type', 'start_date', 'end_date']);
        });

        // Budget Table
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('financial_period_id');
            $table->string('category'); // revenue, cogs, operating_expenses, etc.
            $table->string('subcategory')->nullable(); // rent, salaries, utilities, etc.
            $table->decimal('budgeted_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('financial_period_id')->references('id')->on('financial_periods');
            $table->index(['financial_period_id', 'category']);
        });

        // Chart of Accounts
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code', 20)->unique();
            $table->string('account_name');
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('account_subtype', [
                'current_asset', 'fixed_asset', 'intangible_asset',
                'current_liability', 'long_term_liability',
                'owner_equity', 'retained_earnings',
                'operating_revenue', 'other_revenue',
                'operating_expense', 'other_expense'
            ]);
            $table->unsignedBigInteger('parent_account_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->foreign('parent_account_id')->references('id')->on('chart_of_accounts');
            $table->index(['account_type', 'account_subtype']);
        });

        // General Ledger
        Schema::create('general_ledger', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->date('transaction_date');
            $table->string('reference_type'); // order, purchase, expense, etc.
            $table->unsignedBigInteger('reference_id');
            $table->text('description');
            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->decimal('credit_amount', 15, 2)->default(0);
            $table->decimal('balance', 15, 2);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['account_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
        });

        // GST Transactions
        Schema::create('gst_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type'); // sale, purchase, return
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->decimal('taxable_value', 15, 2);
            $table->decimal('cgst_amount', 15, 2)->default(0);
            $table->decimal('sgst_amount', 15, 2)->default(0);
            $table->decimal('igst_amount', 15, 2)->default(0);
            $table->decimal('total_gst', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->string('gst_rate', 10); // 5%, 12%, 18%, 28%
            $table->boolean('is_reverse_charge')->default(false);
            $table->string('place_of_supply')->nullable();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->index(['invoice_date', 'transaction_type']);
            $table->index(['branch_id', 'invoice_date']);
        });

        // Cash Flow Categories
        Schema::create('cash_flow_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['operating', 'investing', 'financing']);
            $table->string('subtype')->nullable();
            $table->boolean('is_positive_flow')->default(true); // true for inflow, false for outflow
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Cash Flow Transactions
        Schema::create('cash_flow_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->date('transaction_date');
            $table->string('reference_type'); // order, purchase, expense, loan, etc.
            $table->unsignedBigInteger('reference_id');
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->enum('flow_type', ['inflow', 'outflow']);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('cash_flow_categories');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['category_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
        });

        // Report Templates
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // profit_loss, cash_flow, balance_sheet, etc.
            $table->json('configuration'); // report configuration settings
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users');
        });

        // Report Schedules
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->string('name');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
            $table->json('recipients'); // email list
            $table->json('filters'); // default filters
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('template_id')->references('id')->on('report_templates');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_schedules');
        Schema::dropIfExists('report_templates');
        Schema::dropIfExists('cash_flow_transactions');
        Schema::dropIfExists('cash_flow_categories');
        Schema::dropIfExists('gst_transactions');
        Schema::dropIfExists('general_ledger');
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('financial_periods');
    }
};
