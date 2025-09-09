@extends('layouts.app')

@section('title', 'Add New Vendor')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('vendors.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add New Vendor</h1>
                <p class="text-gray-600">Create a new vendor profile and set up product pricing</p>
            </div>
        </div>
    </div>

    <!-- Error Summary -->
    @if ($errors->any())
        <div class="alert alert-error">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.694-.833-2.464 0L3.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <strong>Please fix the following errors:</strong>
            </div>
            <ul class="list-disc list-inside mt-2 ml-7">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Success Message -->
    @if (session('success'))
        <div class="alert alert-success">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('vendors.store') }}" class="space-y-8" id="vendor-form">
        @csrf

        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Basic Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group">
                    <label for="name" class="form-label">Vendor Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" 
                           class="form-input @error('name') border-red-500 @enderror" required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">Vendor Code *</label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" 
                           class="form-input @error('code') border-red-500 @enderror" 
                           placeholder="e.g., VEN001" required>
                    @error('code')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" 
                           class="form-input @error('email') border-red-500 @enderror" required>
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number *</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" 
                           class="form-input @error('phone') border-red-500 @enderror" required>
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group md:col-span-2">
                    <label for="address" class="form-label">Address *</label>
                    <textarea name="address" id="address" rows="3" 
                              class="form-input @error('address') border-red-500 @enderror" required>{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="gst_number" class="form-label">GST Number</label>
                    <input type="text" name="gst_number" id="gst_number" value="{{ old('gst_number') }}" 
                           class="form-input @error('gst_number') border-red-500 @enderror"
                           placeholder="e.g., 07AAACH7409R1ZZ">
                    @error('gst_number')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Product Pricing -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Product Pricing</h2>
                    <p class="text-gray-600">Set supply prices for products this vendor provides</p>
                </div>
                <button type="button" id="add-product" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Product
                </button>
            </div>

            <div id="products-container" class="space-y-4">
                <!-- Products will be added here dynamically -->
            </div>

            <!-- Product Template (hidden) -->
            <div id="product-template" class="hidden">
                <div class="product-row border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="form-label">Product</label>
                            <select name="products[INDEX][product_id]" class="form-input" required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ ucfirst($product->category) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Supply Price (₹)</label>
                            <input type="number" name="products[INDEX][supply_price]" step="0.01" min="0" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Primary Supplier</label>
                            <div class="flex items-center h-10">
                                <input type="checkbox" name="products[INDEX][is_primary_supplier]" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-600">Primary supplier for this product</span>
                            </div>
                        </div>
                        <div>
                            <button type="button" class="remove-product w-full bg-red-50 hover:bg-red-100 text-red-700 font-medium py-2 px-4 rounded-lg transition-colors">
                                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('vendors.index') }}" class="btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn-primary" id="submit-btn">
                <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="btn-text">Create Vendor</span>
                <span class="spinner hidden">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let productIndex = 0;
    const addProductBtn = document.getElementById('add-product');
    const productsContainer = document.getElementById('products-container');
    const productTemplate = document.getElementById('product-template');
    const form = document.getElementById('vendor-form');
    const submitBtn = document.getElementById('submit-btn');

    // Add product functionality
    addProductBtn.addEventListener('click', function() {
        const template = productTemplate.innerHTML;
        const newProduct = template.replace(/INDEX/g, productIndex);
        
        const div = document.createElement('div');
        div.innerHTML = newProduct;
        productsContainer.appendChild(div.firstElementChild);

        // Add remove functionality
        const removeBtn = productsContainer.lastElementChild.querySelector('.remove-product');
        removeBtn.addEventListener('click', function() {
            this.closest('.product-row').remove();
        });

        productIndex++;
    });

    // Auto-generate vendor code based on name
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        const codeField = document.getElementById('code');
        if (name && !codeField.value) {
            const code = 'VEN' + name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '') + String(Date.now()).slice(-3);
            codeField.value = code;
        }
    });

    // Form validation and submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        clearErrors();
        
        // Validate required fields
        const requiredFields = [
            { id: 'name', message: 'Vendor name is required' },
            { id: 'code', message: 'Vendor code is required' },
            { id: 'email', message: 'Email address is required' },
            { id: 'phone', message: 'Phone number is required' },
            { id: 'address', message: 'Address is required' }
        ];

        let hasErrors = false;

        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (!element.value.trim()) {
                showFieldError(element, field.message);
                hasErrors = true;
            }
        });

        // Validate email format
        const email = document.getElementById('email');
        if (email.value && !isValidEmail(email.value)) {
            showFieldError(email, 'Please enter a valid email address');
            hasErrors = true;
        }

        // Validate products if any are added
        const productRows = productsContainer.querySelectorAll('.product-row');
        productRows.forEach((row, index) => {
            const productSelect = row.querySelector('select[name*="product_id"]');
            const priceInput = row.querySelector('input[name*="supply_price"]');
            
            if (!productSelect.value) {
                showFieldError(productSelect, 'Please select a product');
                hasErrors = true;
            }
            
            if (!priceInput.value || parseFloat(priceInput.value) <= 0) {
                showFieldError(priceInput, 'Please enter a valid supply price');
                hasErrors = true;
            }
        });

        if (hasErrors) {
            // Scroll to first error
            const firstError = document.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        // Show loading state
        showLoadingState();

        // Submit the form
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                return response.text();
            }
            throw new Error('Network response was not ok');
        })
        .then(html => {
            // Check if response contains errors
            if (html.includes('alert-error') || html.includes('border-red-500')) {
                // Replace current page content with response (which includes errors)
                document.body.innerHTML = html;
            } else {
                // Success - redirect to vendors index
                window.location.href = '{{ route("vendors.index") }}';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoadingState();
            showAlert('An error occurred while creating the vendor. Please try again.', 'error');
        });
    });

    function clearErrors() {
        const errorFields = document.querySelectorAll('.border-red-500');
        errorFields.forEach(field => {
            field.classList.remove('border-red-500');
            field.style.boxShadow = '';
        });

        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach(msg => msg.remove());
    }

    function showFieldError(element, message) {
        element.classList.add('border-red-500');
        element.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
        
        // Add error message if not exists
        if (!element.parentNode.querySelector('.error-message')) {
            const errorDiv = document.createElement('p');
            errorDiv.className = 'text-red-500 text-sm mt-1 error-message';
            errorDiv.textContent = message;
            element.parentNode.appendChild(errorDiv);
        }
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showLoadingState() {
        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-text').classList.add('hidden');
        submitBtn.querySelector('.spinner').classList.remove('hidden');
    }

    function hideLoadingState() {
        submitBtn.disabled = false;
        submitBtn.querySelector('.btn-text').classList.remove('hidden');
        submitBtn.querySelector('.spinner').classList.add('hidden');
    }

    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} mb-4`;
        alertDiv.innerHTML = `
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                ${message}
            </div>
        `;
        
        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
});
</script>
@endsection