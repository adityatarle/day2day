<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $branch->name }} - Day2Day Fresh</title>
    
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
                    <a href="/" class="flex items-center space-x-2">
                        <i class="fas fa-arrow-left text-gray-600"></i>
                        <span class="text-gray-600">Back</span>
                    </a>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-apple-alt text-green-600 text-2xl"></i>
                        <h1 class="text-2xl font-bold text-gray-800">Day2Day Fresh</h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/staff/login" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-user-shield mr-2"></i>Staff Login
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Store Info Section -->
    <section class="bg-gradient-to-r from-green-500 to-green-600 text-white py-8">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-4">{{ $branch->name }}</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <p class="mb-2"><i class="fas fa-map-marker-alt mr-2"></i>{{ $branch->address }}</p>
                    <p class="mb-2"><i class="fas fa-phone mr-2"></i>{{ $branch->phone }}</p>
                    @if($branch->email)
                    <p class="mb-2"><i class="fas fa-envelope mr-2"></i>{{ $branch->email }}</p>
                    @endif
                    @if($branch->city)
                    <p><i class="fas fa-city mr-2"></i>{{ $branch->city->name }}</p>
                    @endif
                </div>
                <div class="flex items-center justify-end">
                    <button onclick="showOrderForm()" class="bg-white text-green-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                        <i class="fas fa-shopping-cart mr-2"></i>Place Order
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="py-8">
        <div class="container mx-auto px-4">
            <!-- Category Tabs -->
            <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200">
                <button onclick="filterCategory('all')" class="category-tab active px-4 py-2 font-semibold text-gray-700 border-b-2 border-green-600">
                    All Products
                </button>
                @foreach($productsByCategory->keys() as $category)
                <button onclick="filterCategory('{{ $category }}')" class="category-tab px-4 py-2 font-semibold text-gray-500 hover:text-gray-700 border-b-2 border-transparent">
                    {{ ucfirst($category) }}
                </button>
                @endforeach
            </div>

            <!-- Products Grid -->
            <div id="products-grid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($products as $product)
                <div class="product-card bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition" data-category="{{ $product['category'] }}">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-green-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                            @if($product['category'] === 'fruit')
                                <i class="fas fa-apple-alt text-green-600 text-3xl"></i>
                            @elseif($product['category'] === 'vegetable')
                                <i class="fas fa-carrot text-orange-600 text-3xl"></i>
                            @elseif($product['category'] === 'leafy')
                                <i class="fas fa-leaf text-green-700 text-3xl"></i>
                            @else
                                <i class="fas fa-seedling text-purple-600 text-3xl"></i>
                            @endif
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-1 text-sm">{{ $product['name'] }}</h3>
                        <p class="text-green-600 font-bold text-lg">₹{{ number_format($product['selling_price'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">/ {{ $product['weight_unit'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Stock: {{ number_format($product['current_stock'], 2) }} {{ $product['weight_unit'] }}</p>
                        <button onclick="addToCart({{ $product['id'] }}, '{{ $product['name'] }}', {{ $product['selling_price'] }}, '{{ $product['weight_unit'] }}')" 
                                class="mt-3 bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700 transition w-full">
                            <i class="fas fa-cart-plus mr-1"></i>Add to Cart
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            @if($products->isEmpty())
            <div class="text-center py-12">
                <i class="fas fa-box-open text-gray-400 text-6xl mb-4"></i>
                <p class="text-gray-600 text-xl">No products available at this store</p>
            </div>
            @endif
        </div>
    </section>

    <!-- Shopping Cart Sidebar -->
    <div id="cart-sidebar" class="fixed right-0 top-0 h-full w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50">
        <div class="p-6 h-full flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Shopping Cart</h2>
                <button onclick="closeCart()" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <div id="cart-items" class="flex-1 overflow-y-auto mb-4">
                <p class="text-gray-500 text-center py-8">Your cart is empty</p>
            </div>
            <div class="border-t pt-4">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-xl font-bold text-gray-800">Total:</span>
                    <span id="cart-total" class="text-xl font-bold text-green-600">₹0.00</span>
                </div>
                <button onclick="checkout()" class="w-full bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                    <i class="fas fa-check mr-2"></i>Checkout
                </button>
            </div>
        </div>
    </div>

    <!-- Cart Overlay -->
    <div id="cart-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40" onclick="closeCart()"></div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} Day2Day Fresh. All rights reserved.</p>
        </div>
    </footer>

    <script>
        let cart = [];

        function filterCategory(category) {
            // Update active tab
            document.querySelectorAll('.category-tab').forEach(tab => {
                tab.classList.remove('active', 'border-green-600', 'text-gray-700');
                tab.classList.add('border-transparent', 'text-gray-500');
            });
            event.target.classList.add('active', 'border-green-600', 'text-gray-700');
            event.target.classList.remove('border-transparent', 'text-gray-500');

            // Filter products
            const products = document.querySelectorAll('.product-card');
            products.forEach(product => {
                if (category === 'all' || product.dataset.category === category) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        }

        function addToCart(productId, name, price, unit) {
            const existingItem = cart.find(item => item.id === productId);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: productId,
                    name: name,
                    price: price,
                    unit: unit,
                    quantity: 1
                });
            }
            updateCart();
            openCart();
        }

        function removeFromCart(productId) {
            cart = cart.filter(item => item.id !== productId);
            updateCart();
        }

        function updateQuantity(productId, change) {
            const item = cart.find(item => item.id === productId);
            if (item) {
                item.quantity = Math.max(1, item.quantity + change);
                updateCart();
            }
        }

        function updateCart() {
            const cartItems = document.getElementById('cart-items');
            const cartTotal = document.getElementById('cart-total');
            
            if (cart.length === 0) {
                cartItems.innerHTML = '<p class="text-gray-500 text-center py-8">Your cart is empty</p>';
                cartTotal.textContent = '₹0.00';
                return;
            }

            let total = 0;
            cartItems.innerHTML = cart.map(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                return `
                    <div class="flex items-center justify-between mb-4 p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">${item.name}</h4>
                            <p class="text-sm text-gray-600">₹${item.price.toFixed(2)} / ${item.unit}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="updateQuantity(${item.id}, -1)" class="bg-gray-200 text-gray-700 px-2 py-1 rounded">-</button>
                            <span class="font-semibold w-8 text-center">${item.quantity}</span>
                            <button onclick="updateQuantity(${item.id}, 1)" class="bg-gray-200 text-gray-700 px-2 py-1 rounded">+</button>
                            <button onclick="removeFromCart(${item.id})" class="text-red-600 ml-2">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="ml-4">
                            <p class="font-bold text-green-600">₹${itemTotal.toFixed(2)}</p>
                        </div>
                    </div>
                `;
            }).join('');

            cartTotal.textContent = `₹${total.toFixed(2)}`;
        }

        function openCart() {
            document.getElementById('cart-sidebar').classList.remove('translate-x-full');
            document.getElementById('cart-overlay').classList.remove('hidden');
        }

        function closeCart() {
            document.getElementById('cart-sidebar').classList.add('translate-x-full');
            document.getElementById('cart-overlay').classList.add('hidden');
        }

        function showOrderForm() {
            if (cart.length === 0) {
                alert('Please add items to cart first');
                return;
            }
            openCart();
        }

        function checkout() {
            if (cart.length === 0) {
                alert('Your cart is empty');
                return;
            }

            // Prepare order data
            const orderData = {
                branch_id: {{ $branch->id }},
                items: cart.map(item => ({
                    product_id: item.id,
                    quantity: item.quantity,
                    unit_price: item.price
                }))
            };

            // Send order data to server via form submission
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("order.checkout") }}';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            const dataInput = document.createElement('input');
            dataInput.type = 'hidden';
            dataInput.name = 'order_data';
            dataInput.value = JSON.stringify(orderData);
            form.appendChild(dataInput);

            document.body.appendChild(form);
            form.submit();
        }

        // Close cart on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCart();
            }
        });
    </script>
</body>
</html>
