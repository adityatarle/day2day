@extends('layouts.cashier')

@section('title', 'Payment - POS System')

@section('content')
<div x-data="paymentSystem()" class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Payment</h1>
                    <p class="text-sm text-gray-600">Complete your order payment</p>
                </div>
                <a href="{{ route('pos.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-times text-xl"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Order Summary -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                    
                    @if($customer)
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">Customer</p>
                        <p class="font-medium text-gray-900">{{ $customer->name }}</p>
                        @if($customer->phone)
                        <p class="text-xs text-gray-500">{{ $customer->phone }}</p>
                        @endif
                    </div>
                    @endif

                    <div class="space-y-3 mb-4">
                        @foreach($orderData['items'] as $item)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <div class="flex-1">
                                @php
                                    $product = \App\Models\Product::find($item['product_id']);
                                @endphp
                                <p class="text-sm font-medium text-gray-900">{{ $product->name ?? 'Product' }}</p>
                                <p class="text-xs text-gray-500">{{ $item['quantity'] }} {{ $item['unit'] ?? 'kg' }} × ₹{{ number_format($item['unit_price'] ?? $item['price'] ?? 0, 2) }}</p>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">₹{{ number_format($item['total_price'] ?? 0, 2) }}</p>
                        </div>
                        @endforeach
                    </div>

                    <div class="border-t pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium text-gray-900">₹{{ number_format($orderData['subtotal'] ?? 0, 2) }}</span>
                        </div>
                        @if(($orderData['discount'] ?? 0) > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Discount:</span>
                            <span class="font-medium text-green-600">-₹{{ number_format($orderData['discount'] ?? 0, 2) }}</span>
                        </div>
                        @endif
                        @if(($orderData['tax'] ?? 0) > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax:</span>
                            <span class="font-medium text-gray-900">₹{{ number_format($orderData['tax'] ?? 0, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-lg font-semibold border-t pt-2">
                            <span>Total:</span>
                            <span class="text-gray-900">₹{{ number_format($orderData['total'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Options -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h2>
                    
                    <div class="space-y-3 mb-6">
                        <button 
                            @click="selectPaymentMethod('cash')"
                            :class="paymentMethod === 'cash' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="w-full px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2"
                        >
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Cash</span>
                        </button>
                        
                        <button 
                            @click="selectPaymentMethod('upi')"
                            :class="paymentMethod === 'upi' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="w-full px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2"
                        >
                            <i class="fas fa-qrcode"></i>
                            <span>UPI</span>
                        </button>
                        
                        <button 
                            @click="selectPaymentMethod('card')"
                            :class="paymentMethod === 'card' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="w-full px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2"
                        >
                            <i class="fas fa-credit-card"></i>
                            <span>Card</span>
                        </button>
                        
                        <button 
                            @click="selectPaymentMethod('credit')"
                            :class="paymentMethod === 'credit' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="w-full px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2"
                        >
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Credit</span>
                        </button>
                    </div>

                    <!-- Cash Payment -->
                    <div x-show="paymentMethod === 'cash'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount Received</label>
                            <input 
                                type="number" 
                                x-model="amountReceived"
                                @input="calculateReturn()"
                                step="0.01"
                                min="0"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-lg font-semibold focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
                                placeholder="0.00"
                            >
                        </div>
                        <div x-show="returnAmount > 0" class="p-3 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-800">
                                <span class="font-medium">Return:</span> ₹<span x-text="formatPrice(returnAmount)"></span>
                            </p>
                        </div>
                        <button 
                            @click="processPayment()"
                            :disabled="amountReceived < total"
                            class="w-full bg-gray-900 hover:bg-gray-800 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium py-3 rounded-lg transition-colors"
                        >
                            Process Payment
                        </button>
                    </div>

                    <!-- Credit Payment -->
                    <div x-show="paymentMethod === 'credit'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount Received</label>
                            <input 
                                type="number" 
                                x-model="amountReceived"
                                @input="calculateReturn()"
                                step="0.01"
                                min="0"
                                max="total"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-lg font-semibold focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
                                placeholder="0.00"
                            >
                            <p class="text-xs text-gray-500 mt-1">Enter partial payment amount (if any)</p>
                        </div>
                        <div x-show="amountReceived > 0 && amountReceived < total" class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-800">
                                <span class="font-medium">Pending:</span> ₹<span x-text="formatPrice(total - amountReceived)"></span>
                            </p>
                        </div>
                        <button 
                            @click="processPayment()"
                            class="w-full bg-gray-900 hover:bg-gray-800 text-white font-medium py-3 rounded-lg transition-colors"
                        >
                            Process Credit Payment
                        </button>
                    </div>

                    <!-- UPI Payment -->
                    <div x-show="paymentMethod === 'upi'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">UPI ID</label>
                            <input 
                                type="text" 
                                x-model="upiId"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
                                placeholder="merchant@paytm"
                            >
                        </div>
                        <button 
                            @click="generateQrCode()"
                            :disabled="!upiId"
                            class="w-full bg-gray-900 hover:bg-gray-800 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium py-3 rounded-lg transition-colors"
                        >
                            Generate QR Code
                        </button>
                        
                        <!-- QR Code Display -->
                        <div x-show="qrCode" class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="mb-3" x-html="qrCode"></div>
                            <p class="text-sm text-gray-600 mb-2">Scan to pay ₹<span x-text="formatPrice(total)"></span></p>
                            <button 
                                @click="processPayment()"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-lg transition-colors"
                            >
                                Payment Received
                            </button>
                        </div>
                    </div>

                    <!-- Card Payment -->
                    <div x-show="paymentMethod === 'card'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                            <input 
                                type="number" 
                                x-model="amountReceived"
                                step="0.01"
                                min="0"
                                :value="total"
                                readonly
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-lg font-semibold bg-gray-50"
                            >
                        </div>
                        <button 
                            @click="processPayment()"
                            class="w-full bg-gray-900 hover:bg-gray-800 text-white font-medium py-3 rounded-lg transition-colors"
                        >
                            Process Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function paymentSystem() {
    return {
        paymentMethod: 'cash',
        amountReceived: {{ $orderData['total'] ?? 0 }}, // Default to total for cash
        upiId: '',
        qrCode: null,
        total: {{ $orderData['total'] ?? 0 }},
        returnAmount: 0,
        orderToken: '{{ $orderToken }}',
        processing: false,

        selectPaymentMethod(method) {
            this.paymentMethod = method;
            if (method === 'card' || method === 'upi') {
                this.amountReceived = this.total;
            } else if (method === 'credit') {
                this.amountReceived = 0;
            } else if (method === 'cash') {
                this.amountReceived = this.total;
            }
            this.calculateReturn();
        },

        calculateReturn() {
            if (this.paymentMethod === 'cash') {
                this.returnAmount = Math.max(this.amountReceived - this.total, 0);
            } else {
                this.returnAmount = 0;
            }
        },

        formatPrice(price) {
            return parseFloat(price).toFixed(2);
        },

        async generateQrCode() {
            if (!this.upiId) {
                alert('Please enter UPI ID');
                return;
            }

            try {
                const response = await fetch('/api/pos/generate-upi-qr', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        amount: this.total,
                        upi_id: this.upiId,
                        merchant_name: '{{ $branch->name ?? "Day2Day" }}',
                        transaction_note: 'POS Payment'
                    })
                });

                const result = await response.json();
                
                if (result.success && result.data.qr_code_svg) {
                    this.qrCode = result.data.qr_code_svg;
                } else {
                    alert('Error generating QR code: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error generating QR code. Please try again.');
            }
        },

        async processPayment() {
            if (this.processing) return;
            
            if (this.paymentMethod === 'cash' && this.amountReceived < this.total) {
                alert('Amount received is less than total amount');
                return;
            }

            if (this.paymentMethod === 'upi' && !this.qrCode) {
                alert('Please generate QR code first');
                return;
            }

            if (this.paymentMethod === 'credit' && this.amountReceived > this.total) {
                alert('Amount received cannot be greater than total amount');
                return;
            }

            this.processing = true;

            try {
                const response = await fetch('/api/pos/process-payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        order_token: this.orderToken,
                        payment_method: this.paymentMethod,
                        amount_received: parseFloat(this.amountReceived) || 0,
                        upi_id: this.paymentMethod === 'upi' ? this.upiId : null
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    let errorMessage = 'Failed to process payment';
                    try {
                        const errorJson = JSON.parse(errorText);
                        errorMessage = errorJson.message || errorMessage;
                        if (errorJson.errors) {
                            const errorDetails = Object.values(errorJson.errors).flat().join(', ');
                            errorMessage += ': ' + errorDetails;
                        }
                    } catch (e) {
                        errorMessage += ' (Status: ' + response.status + ')';
                    }
                    alert(errorMessage);
                    this.processing = false;
                    return;
                }

                const result = await response.json();
                
                if (result.success) {
                    // Redirect to invoice
                    window.location.href = result.data.invoice_url;
                } else {
                    alert('Error: ' + (result.message || 'Failed to process payment'));
                    this.processing = false;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to process payment: ' + error.message);
                this.processing = false;
            }
        }
    }
}
</script>
@endsection

