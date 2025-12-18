<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Confirmation - Day2Day Fresh</title>
    
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
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-apple-alt text-green-600 text-2xl"></i>
                    <h1 class="text-2xl font-bold text-gray-800">Day2Day Fresh</h1>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto">
            <!-- Success Message -->
            <div class="bg-green-50 border-2 border-green-500 rounded-lg p-6 mb-8 text-center">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-green-600 text-6xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-green-800 mb-2">Order Placed Successfully!</h2>
                <p class="text-gray-700">Your order has been received and is being processed.</p>
            </div>

            <!-- Order Details -->
            <div class="bg-white rounded-lg shadow-md p-8 mb-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Order Details</h3>
                
                <div class="space-y-4 mb-6">
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-600 font-semibold">Order Number:</span>
                        <span class="text-gray-800 font-bold text-lg">{{ $order->order_number }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-600 font-semibold">Status:</span>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full font-semibold capitalize">{{ $order->status }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-600 font-semibold">Payment Method:</span>
                        <span class="text-gray-800 font-semibold capitalize">{{ $order->payment_method }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-600 font-semibold">Payment Status:</span>
                        <span class="px-3 py-1 {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} rounded-full font-semibold capitalize">
                            {{ $order->payment_status }}
                        </span>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="mb-6">
                    <h4 class="text-lg font-bold text-gray-800 mb-4">Order Items</h4>
                    <div class="space-y-3">
                        @foreach($order->orderItems as $item)
                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $item->product->name }}</p>
                                <p class="text-sm text-gray-600">{{ $item->quantity }} {{ $item->unit }} × ₹{{ number_format($item->unit_price, 2) }}</p>
                            </div>
                            <p class="font-bold text-green-600">₹{{ number_format($item->total_price, 2) }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="border-t pt-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-semibold">₹{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between text-red-600">
                        <span>Discount:</span>
                        <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    @if($order->tax_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tax:</span>
                        <span class="font-semibold">₹{{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-xl font-bold pt-2 border-t">
                        <span>Total Amount:</span>
                        <span class="text-green-600">₹{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Delivery Information -->
            <div class="bg-white rounded-lg shadow-md p-8 mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-truck mr-2 text-green-600"></i>Delivery Information
                </h3>
                <div class="space-y-2">
                    <p class="text-gray-700"><strong>Address:</strong> {{ $order->delivery_address }}</p>
                    <p class="text-gray-700"><strong>Phone:</strong> {{ $order->delivery_phone }}</p>
                    @if($order->delivery_instructions)
                    <p class="text-gray-700"><strong>Instructions:</strong> {{ $order->delivery_instructions }}</p>
                    @endif
                </div>
            </div>

            <!-- Branch Information -->
            <div class="bg-white rounded-lg shadow-md p-8 mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-store mr-2 text-green-600"></i>Store Information
                </h3>
                <div class="space-y-2">
                    <p class="text-gray-700"><strong>Store:</strong> {{ $order->branch->name }}</p>
                    <p class="text-gray-700"><strong>Address:</strong> {{ $order->branch->address }}</p>
                    <p class="text-gray-700"><strong>Phone:</strong> {{ $order->branch->phone }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col md:flex-row gap-4">
                <a href="/" class="flex-1 bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-700 transition text-center">
                    <i class="fas fa-home mr-2"></i>Back to Home
                </a>
                <a href="/store/{{ $order->branch->id }}" class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition text-center">
                    <i class="fas fa-shopping-cart mr-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>
</body>
</html>
