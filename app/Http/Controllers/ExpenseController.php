<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     */
    public function index(Request $request)
    {
        $query = Expense::with(['expenseCategory', 'branch', 'user']);

        // Filter by category
        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        // Filter by branch
        if ($request->has('branch_id')) {
            $query->byBranch($request->branch_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        // Filter by expense type
        if ($request->has('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $expenses
        ]);
    }

    /**
     * Store a newly created expense.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expense_category_id' => 'required|exists:expense_categories,id',
            'branch_id' => 'required|exists:branches,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,upi,card,bank_transfer',
            'reference_number' => 'nullable|string|max:255',
            'expense_type' => 'required|in:transport,labour,operational,overhead,direct',
            'allocation_method' => 'required|in:equal,weighted,manual',
            'allocation_products' => 'nullable|array',
            'allocation_products.*' => 'exists:products,id',
            'allocation_weights' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();

            $expense = Expense::create([
                'expense_category_id' => $request->expense_category_id,
                'branch_id' => $request->branch_id,
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'amount' => $request->amount,
                'expense_date' => $request->expense_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'expense_type' => $request->expense_type,
                'allocation_method' => $request->allocation_method,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Handle cost allocation if specified
            if ($request->allocation_method !== 'manual' && $request->allocation_products) {
                $this->allocateExpenseToProducts($expense, $request->allocation_products, $request->allocation_weights);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Expense created successfully',
                'data' => $expense->load(['expenseCategory', 'branch', 'user', 'allocations'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create expense: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense)
    {
        $expense->load(['expenseCategory', 'branch', 'user', 'allocations.product']);

        return response()->json([
            'status' => 'success',
            'data' => $expense
        ]);
    }

    /**
     * Update the specified expense.
     */
    public function update(Request $request, Expense $expense)
    {
        $validator = Validator::make($request->all(), [
            'expense_category_id' => 'required|exists:expense_categories,id',
            'branch_id' => 'required|exists:branches,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,upi,card,bank_transfer',
            'reference_number' => 'nullable|string|max:255',
            'expense_type' => 'required|in:transport,labour,operational,overhead,direct',
            'allocation_method' => 'required|in:equal,weighted,manual',
            'allocation_products' => 'nullable|array',
            'allocation_products.*' => 'exists:products,id',
            'allocation_weights' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $expense->update([
                'expense_category_id' => $request->expense_category_id,
                'branch_id' => $request->branch_id,
                'title' => $request->title,
                'description' => $request->description,
                'amount' => $request->amount,
                'expense_date' => $request->expense_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'expense_type' => $request->expense_type,
                'allocation_method' => $request->allocation_method,
                'notes' => $request->notes,
            ]);

            // Clear existing allocations and recreate if needed
            $expense->allocations()->delete();
            if ($request->allocation_method !== 'manual' && $request->allocation_products) {
                $this->allocateExpenseToProducts($expense, $request->allocation_products, $request->allocation_weights);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Expense updated successfully',
                'data' => $expense->load(['expenseCategory', 'branch', 'user', 'allocations'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update expense: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified expense.
     */
    public function destroy(Expense $expense)
    {
        try {
            DB::beginTransaction();

            // Remove allocations first
            $expense->allocations()->delete();
            $expense->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Expense deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete expense: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve an expense.
     */
    public function approve(Expense $expense)
    {
        $expense->approve();

        return response()->json([
            'status' => 'success',
            'message' => 'Expense approved successfully',
            'data' => $expense
        ]);
    }

    /**
     * Reject an expense.
     */
    public function reject(Expense $expense)
    {
        $expense->reject();

        return response()->json([
            'status' => 'success',
            'message' => 'Expense rejected successfully',
            'data' => $expense
        ]);
    }

    /**
     * Mark expense as paid.
     */
    public function markAsPaid(Expense $expense)
    {
        $expense->markAsPaid();

        return response()->json([
            'status' => 'success',
            'message' => 'Expense marked as paid successfully',
            'data' => $expense
        ]);
    }

    /**
     * Get expense categories.
     */
    public function getCategories()
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Store a new expense category.
     */
    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'code' => 'required|string|max:50|unique:expense_categories,code',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = ExpenseCategory::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Update an expense category.
     */
    public function updateCategory(Request $request, ExpenseCategory $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $category->id,
            'code' => 'required|string|max:50|unique:expense_categories,code,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Expense category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Remove an expense category.
     */
    public function destroyCategory(ExpenseCategory $category)
    {
        if ($category->expenses()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete category with existing expenses'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Expense category deleted successfully'
        ]);
    }

    /**
     * Get expense allocation report.
     */
    public function getAllocationReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'nullable|exists:branches,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Expense::with(['expenseCategory', 'branch', 'allocations.product'])
                        ->byDateRange($request->start_date, $request->end_date);

        if ($request->branch_id) {
            $query->byBranch($request->branch_id);
        }

        $expenses = $query->get();

        // Calculate allocation summary
        $allocationSummary = $expenses->flatMap(function ($expense) {
            return $expense->allocations;
        })->groupBy('product_id')->map(function ($allocations, $productId) {
            $product = Product::find($productId);
            return [
                'product_id' => $productId,
                'product_name' => $product->name,
                'total_allocated_amount' => $allocations->sum('allocated_amount'),
                'allocation_count' => $allocations->count(),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'expenses' => $expenses,
                'allocation_summary' => $allocationSummary,
                'total_expenses' => $expenses->sum('amount'),
                'total_allocated' => $expenses->sum(function ($expense) {
                    return $expense->allocations->sum('allocated_amount');
                }),
            ]
        ]);
    }

    /**
     * Allocate expense to products based on method.
     */
    private function allocateExpenseToProducts(Expense $expense, array $productIds, array $weights = null)
    {
        $products = Product::whereIn('id', $productIds)->get();
        $totalAmount = $expense->amount;

        switch ($expense->allocation_method) {
            case 'equal':
                $amountPerProduct = $totalAmount / count($products);
                foreach ($products as $product) {
                    $this->createExpenseAllocation($expense, $product, $amountPerProduct);
                }
                break;

            case 'weighted':
                if (!$weights || count($weights) !== count($productIds)) {
                    throw new \Exception('Weights must be provided for weighted allocation');
                }

                $totalWeight = array_sum($weights);
                foreach ($products as $index => $product) {
                    $weight = $weights[$index] ?? 1;
                    $allocatedAmount = ($weight / $totalWeight) * $totalAmount;
                    $this->createExpenseAllocation($expense, $product, $allocatedAmount, $weight);
                }
                break;

            default:
                // Manual allocation - handled separately
                break;
        }
    }

    /**
     * Create expense allocation record.
     */
    private function createExpenseAllocation(Expense $expense, Product $product, float $allocatedAmount, float $weight = null)
    {
        return $expense->allocations()->create([
            'product_id' => $product->id,
            'allocated_amount' => $allocatedAmount,
            'allocation_weight' => $weight,
            'allocation_date' => now(),
        ]);
    }

    /**
     * Get cost analysis for products.
     */
    public function getCostAnalysis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'nullable|exists:products,id',
            'branch_id' => 'nullable|exists:branches,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Product::with(['branches', 'expenseAllocations' => function ($q) use ($request) {
            $q->whereHas('expense', function ($expenseQuery) use ($request) {
                $expenseQuery->byDateRange($request->start_date, $request->end_date);
                if ($request->branch_id) {
                    $expenseQuery->byBranch($request->branch_id);
                }
            });
        }]);

        if ($request->product_id) {
            $query->where('id', $request->product_id);
        }

        if ($request->branch_id) {
            $query->whereHas('branches', function ($q) use ($request) {
                $q->where('branches.id', $request->branch_id);
            });
        }

        $products = $query->get();

        $costAnalysis = $products->map(function ($product) use ($request) {
            $allocatedExpenses = $product->expenseAllocations->sum('allocated_amount');
            
            // Get branch-specific data
            $branchData = $product->branches->map(function ($branch) use ($allocatedExpenses, $product) {
                $currentStock = $branch->pivot->current_stock;
                $sellingPrice = $branch->pivot->selling_price;
                
                // Calculate cost per unit including allocated expenses
                $costPerUnit = $currentStock > 0 ? 
                    ($product->purchase_price + ($allocatedExpenses / $currentStock)) : 
                    $product->purchase_price;
                
                $profitMargin = $sellingPrice - $costPerUnit;
                $profitPercentage = $costPerUnit > 0 ? (($profitMargin / $costPerUnit) * 100) : 0;

                return [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'current_stock' => $currentStock,
                    'purchase_price' => $product->purchase_price,
                    'allocated_expenses' => $allocatedExpenses,
                    'cost_per_unit' => $costPerUnit,
                    'selling_price' => $sellingPrice,
                    'profit_margin' => $profitMargin,
                    'profit_percentage' => round($profitPercentage, 2),
                ];
            });

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'category' => $product->category,
                'total_allocated_expenses' => $allocatedExpenses,
                'branches' => $branchData,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $costAnalysis
        ]);
    }

    /**
     * Get expense summary by type.
     */
    public function getExpenseSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'nullable|exists:branches,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Expense::byDateRange($request->start_date, $request->end_date);

        if ($request->branch_id) {
            $query->byBranch($request->branch_id);
        }

        $expenses = $query->get();

        $summary = [
            'by_type' => $expenses->groupBy('expense_type')->map(function ($typeExpenses, $type) {
                return [
                    'type' => $type,
                    'total_amount' => $typeExpenses->sum('amount'),
                    'count' => $typeExpenses->count(),
                    'average_amount' => $typeExpenses->avg('amount'),
                ];
            }),
            'by_category' => $expenses->groupBy('expenseCategory.name')->map(function ($categoryExpenses, $category) {
                return [
                    'category' => $category,
                    'total_amount' => $categoryExpenses->sum('amount'),
                    'count' => $categoryExpenses->count(),
                ];
            }),
            'total_amount' => $expenses->sum('amount'),
            'total_count' => $expenses->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $summary
        ]);
    }
}