@extends('layouts.app')

@section('title', 'Record Loss')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Record Inventory Loss</h1>
                <p class="text-gray-600 mt-1">Record weight loss, wastage, or other inventory losses</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('inventory.lossTracking') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-chart-line mr-2"></i>View Loss Records
                </a>
                @if(auth()->user()->hasRole('branch_manager') || auth()->user()->hasRole('cashier'))
                    <a href="{{ route('branch.inventory.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
                    </a>
                @else
                    <a href="{{ route('inventory.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Record Loss Form -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-red-50 border-b border-red-200">
            <h2 class="text-lg font-medium text-red-900">Record New Loss</h2>
            <p class="text-sm text-red-700 mt-1">Accurately record any inventory loss to maintain proper tracking</p>
        </div>

        <form action="{{ route('inventory.recordLoss') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Product Selection -->
                <div>
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Product <span class="text-red-500">*</span>
                    </label>
                    <select id="product_id" name="product_id" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-weight-unit="{{ $product->weight_unit }}">
                                {{ $product->name }} ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Branch Selection -->
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Branch <span class="text-red-500">*</span>
                    </label>
                    <select id="branch_id" name="branch_id" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Loss Type -->
                <div>
                    <label for="loss_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Loss Type <span class="text-red-500">*</span>
                    </label>
                    <select id="loss_type" name="loss_type" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">Select Loss Type</option>
                        <option value="weight_loss">Weight Loss</option>
                        <option value="water_loss">Water Loss</option>
                        <option value="wastage">Wastage</option>
                        <option value="complimentary">Complimentary/Adjustment</option>
                    </select>
                    @error('loss_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Batch Selection -->
                <div>
                    <label for="batch_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Batch (Optional)
                    </label>
                    <select id="batch_id" name="batch_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">Select Batch (Optional)</option>
                    </select>
                    @error('batch_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Initial Quantity -->
                <div>
                    <label for="initial_quantity" class="block text-sm font-medium text-gray-700 mb-2">
                        Initial Quantity
                    </label>
                    <div class="relative">
                        <input type="number" id="initial_quantity" name="initial_quantity" step="0.01" min="0" 
                               placeholder="Enter initial quantity"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <span id="initial_unit" class="absolute right-3 top-2 text-gray-500 text-sm"></span>
                    </div>
                    @error('initial_quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Final Quantity -->
                <div>
                    <label for="final_quantity" class="block text-sm font-medium text-gray-700 mb-2">
                        Final Quantity
                    </label>
                    <div class="relative">
                        <input type="number" id="final_quantity" name="final_quantity" step="0.01" min="0" 
                               placeholder="Enter final quantity"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <span id="final_unit" class="absolute right-3 top-2 text-gray-500 text-sm"></span>
                    </div>
                    @error('final_quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Quantity Lost -->
                <div>
                    <label for="quantity_lost" class="block text-sm font-medium text-gray-700 mb-2">
                        Quantity Lost <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" id="quantity_lost" name="quantity_lost" step="0.01" min="0.01" required 
                               placeholder="Enter quantity lost"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <span id="lost_unit" class="absolute right-3 top-2 text-gray-500 text-sm"></span>
                    </div>
                    @error('quantity_lost')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Financial Loss -->
                <div>
                    <label for="financial_loss" class="block text-sm font-medium text-gray-700 mb-2">
                        Financial Loss (₹) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="financial_loss" name="financial_loss" step="0.01" min="0.01" required 
                           placeholder="Enter financial loss amount"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    @error('financial_loss')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Reason -->
            <div class="mt-6">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Loss <span class="text-red-500">*</span>
                </label>
                <textarea id="reason" name="reason" rows="3" required 
                          placeholder="Describe the reason for this loss..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                @error('reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Loss Calculation Display -->
            <div id="loss_calculation" class="mt-6 p-4 bg-gray-50 rounded-lg hidden">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Loss Calculation</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Loss Percentage:</span>
                        <span id="loss_percentage" class="font-medium text-red-600 ml-2">0%</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Cost per Unit:</span>
                        <span id="cost_per_unit" class="font-medium text-gray-900 ml-2">₹0.00</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Total Impact:</span>
                        <span id="total_impact" class="font-medium text-red-600 ml-2">₹0.00</span>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('inventory.lossTracking') }}" 
                   class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>Record Loss
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const initialQuantity = document.getElementById('initial_quantity');
    const finalQuantity = document.getElementById('final_quantity');
    const quantityLost = document.getElementById('quantity_lost');
    const financialLoss = document.getElementById('financial_loss');
    const lossCalculation = document.getElementById('loss_calculation');
    
    // Update unit displays when product changes
    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const weightUnit = selectedOption.getAttribute('data-weight-unit') || 'kg';
        
        document.getElementById('initial_unit').textContent = weightUnit;
        document.getElementById('final_unit').textContent = weightUnit;
        document.getElementById('lost_unit').textContent = weightUnit;
    });
    
    // Calculate quantity lost when initial and final quantities change
    function calculateQuantityLost() {
        const initial = parseFloat(initialQuantity.value) || 0;
        const final = parseFloat(finalQuantity.value) || 0;
        
        if (initial > 0 && final >= 0 && initial > final) {
            const lost = initial - final;
            quantityLost.value = lost.toFixed(2);
            updateCalculations();
        }
    }
    
    // Update loss calculations
    function updateCalculations() {
        const lost = parseFloat(quantityLost.value) || 0;
        const financial = parseFloat(financialLoss.value) || 0;
        const initial = parseFloat(initialQuantity.value) || 0;
        
        if (lost > 0) {
            lossCalculation.classList.remove('hidden');
            
            // Calculate loss percentage
            const lossPercentage = initial > 0 ? (lost / initial * 100).toFixed(2) : 0;
            document.getElementById('loss_percentage').textContent = lossPercentage + '%';
            
            // Calculate cost per unit
            const costPerUnit = lost > 0 ? (financial / lost).toFixed(2) : 0;
            document.getElementById('cost_per_unit').textContent = '₹' + costPerUnit;
            
            // Total impact is the financial loss
            document.getElementById('total_impact').textContent = '₹' + financial.toFixed(2);
        } else {
            lossCalculation.classList.add('hidden');
        }
    }
    
    initialQuantity.addEventListener('input', calculateQuantityLost);
    finalQuantity.addEventListener('input', calculateQuantityLost);
    quantityLost.addEventListener('input', updateCalculations);
    financialLoss.addEventListener('input', updateCalculations);
});
</script>
@endsection