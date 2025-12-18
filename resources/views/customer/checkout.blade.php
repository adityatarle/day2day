<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout - Day2Day Fresh</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="/store/{{ $branch->id }}" class="flex items-center space-x-2">
                        <i class="fas fa-arrow-left text-gray-600"></i>
                        <span class="text-gray-600">Back to Store</span>
                    </a>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-apple-alt text-green-600 text-2xl"></i>
                        <h1 class="text-2xl font-bold text-gray-800">Day2Day Fresh</h1>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-gray-800 mb-8">Checkout</h2>

            <form action="{{ route('order.place') }}" method="POST" id="checkout-form">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                <input type="hidden" name="items" value="{{ json_encode($orderData['items']) }}">

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Left Column: Customer & Delivery Info -->
                    <div class="space-y-6">
                        <!-- Customer Information -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">
                                <i class="fas fa-user mr-2 text-green-600"></i>Customer Information
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('customer_name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Phone *</label>
                                    <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('customer_phone')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                    <input type="email" name="customer_email" value="{{ old('customer_email') }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('customer_email')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                                    <textarea name="customer_address" rows="2"
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">{{ old('customer_address') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Information -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">
                                <i class="fas fa-truck mr-2 text-green-600"></i>Delivery Information
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Delivery Address *</label>
                                    <textarea name="delivery_address" rows="3" required
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">{{ old('delivery_address') }}</textarea>
                                    @error('delivery_address')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Delivery Phone *</label>
                                    <input type="tel" name="delivery_phone" value="{{ old('delivery_phone') }}" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('delivery_phone')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Delivery Instructions</label>
                                    <textarea name="delivery_instructions" rows="2"
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                              placeholder="Any special instructions for delivery...">{{ old('delivery_instructions') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Order Summary & Payment -->
                    <div class="space-y-6">
                        <!-- Order Summary -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">
                                <i class="fas fa-shopping-cart mr-2 text-green-600"></i>Order Summary
                            </h3>
                            
                            <div class="space-y-3 mb-4">
                                @foreach($items as $item)
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $item['name'] }}</p>
                                        <p class="text-sm text-gray-600">{{ $item['quantity'] }} × ₹{{ number_format($item['unit_price'], 2) }}</p>
                                    </div>
                                    <p class="font-bold text-green-600">₹{{ number_format($item['total_price'], 2) }}</p>
                                </div>
                                @endforeach
                            </div>

                            <div class="border-t pt-4 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-semibold">₹{{ number_format($subtotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-xl font-bold pt-2 border-t">
                                    <span>Total:</span>
                                    <span class="text-green-600">₹{{ number_format($total, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">
                                <i class="fas fa-credit-card mr-2 text-green-600"></i>Payment Method
                            </h3>
                            
                            <div class="space-y-3">
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 transition">
                                    <input type="radio" name="payment_method" value="cod" checked class="mr-3">
                                    <div class="flex-1">
                                        <p class="font-semibold">Cash on Delivery (COD)</p>
                                        <p class="text-sm text-gray-600">Pay when you receive your order</p>
                                    </div>
                                </label>

                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 transition">
                                    <input type="radio" name="payment_method" value="upi" class="mr-3">
                                    <div class="flex-1">
                                        <p class="font-semibold">UPI</p>
                                        <p class="text-sm text-gray-600">Pay via UPI</p>
                                    </div>
                                </label>

                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 transition">
                                    <input type="radio" name="payment_method" value="card" class="mr-3">
                                    <div class="flex-1">
                                        <p class="font-semibold">Card</p>
                                        <p class="text-sm text-gray-600">Credit/Debit Card</p>
                                    </div>
                                </label>
                            </div>
                            @error('payment_method')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">
                                <i class="fas fa-sticky-note mr-2 text-green-600"></i>Additional Notes
                            </h3>
                            <textarea name="notes" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                      placeholder="Any special requests or notes...">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Place Order Button -->
                        <button type="submit" class="w-full bg-green-600 text-white px-6 py-4 rounded-lg font-bold text-lg hover:bg-green-700 transition shadow-lg">
                            <i class="fas fa-check-circle mr-2"></i>Place Order
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
