<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Day2Day Fresh - Order Fresh Fruits & Vegetables</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }

        /* Smooth hover + subtle glassmorphism (light theme) */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(14px);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        }

        .pill {
            border-radius: 999px;
        }

        .scrollbar-thin::-webkit-scrollbar {
            height: 6px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.8);
            border-radius: 999px;
        }
    </style>
</head>
<body class="bg-gray-50 text-slate-900">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <div class="h-9 w-9 rounded-full bg-gradient-to-tr from-emerald-400 to-lime-300 flex items-center justify-center shadow-lg">
                        <i class="fas fa-apple-alt text-slate-900 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm text-emerald-700 font-semibold tracking-wide uppercase">Day2Day</p>
                        <h1 class="text-lg md:text-xl font-bold text-slate-900 leading-tight">Fresh Fruits &amp; Vegetables</h1>
                    </div>
                </div>
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                    <a href="#stores" class="text-slate-600 hover:text-emerald-600 transition">Stores</a>
                    <a href="#featured" class="text-slate-600 hover:text-emerald-600 transition">Featured</a>
                    <a href="#how-it-works" class="text-slate-600 hover:text-emerald-600 transition">How it works</a>
                </nav>
                <a href="/staff/login"
                   class="inline-flex items-center gap-2 px-3 py-1.5 pill border border-slate-300 text-xs md:text-sm text-slate-700 hover:border-emerald-500 hover:text-emerald-700 transition">
                    <i class="fas fa-user-shield text-emerald-500"></i>
                    <span>Staff Login</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-emerald-50 via-emerald-100 to-sky-50">
        <div class="max-w-6xl mx-auto px-4 py-10 md:py-16 relative">
            <div class="grid md:grid-cols-2 gap-10 items-center">
                <div>
                    <p class="inline-flex items-center gap-2 px-3 py-1 pill bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-semibold mb-4">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Fresh stock every single morning
                    </p>
                    <h2 class="text-3xl md:text-5xl font-extrabold text-slate-900 mb-4 leading-tight">
                        Order <span class="text-emerald-600">farm-fresh</span><br class="hidden md:block" />
                        fruits &amp; veggies in minutes.
                    </h2>
                    <p class="text-slate-700 text-sm md:text-base mb-6 max-w-xl">
                        Choose your nearest Day2Day store, browse real-time stock, and get your order
                        packed and ready for pickup or quick delivery.
                    </p>

                    <!-- Location / store search bar -->
                    <div class="glass-card pill px-3 py-2 md:px-4 md:py-3 flex flex-col md:flex-row md:items-center gap-3 mb-4 border border-emerald-100">
                        <div class="flex-1 flex items-center gap-3">
                            <button type="button" onclick="getLocation()"
                                    class="shrink-0 h-9 w-9 md:h-10 md:w-10 rounded-full bg-emerald-500 flex items-center justify-center text-white shadow-lg hover:bg-emerald-400 transition">
                                <i class="fas fa-location-arrow text-sm"></i>
                            </button>
                            <div class="flex-1">
                                <p class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Delivery location</p>
                                <p class="text-sm text-slate-800">
                                    @if($nearestBranch && $nearestBranch->city)
                                        {{ $nearestBranch->city->name }} • {{ $nearestBranch->name }}
                                    @else
                                        Use current location or pick a store below
                                    @endif
                                </p>
                            </div>
                        </div>
                        <a href="#stores"
                           class="pill text-xs md:text-sm font-semibold bg-emerald-600 text-white px-4 py-2 md:px-5 md:py-2.5 shadow hover:bg-emerald-700 transition text-center">
                            <i class="fas fa-store mr-1.5"></i> Choose Store
                        </a>
                    </div>

                    <div class="flex items-center gap-4 text-xs md:text-sm text-slate-700">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-clock text-emerald-300"></i>
                            <span>Same-day delivery from nearby outlets</span>
                        </div>
                        <div class="hidden md:flex items-center gap-2">
                            <i class="fas fa-leaf text-emerald-300"></i>
                            <span>Weight-based &amp; piece-based billing supported</span>
                        </div>
                    </div>
                </div>

                <!-- Right: floating cards -->
                <div class="hidden md:flex justify-end">
                    <div class="relative w-full max-w-sm">
                        <div class="absolute -top-6 -right-6 h-24 w-24 bg-emerald-300/25 rounded-full blur-2xl"></div>
                        <div class="absolute -bottom-8 -left-4 h-24 w-24 bg-lime-300/25 rounded-full blur-2xl"></div>

                        <div class="glass-card rounded-3xl p-5 border border-emerald-100 relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-xs text-slate-500">Your closest outlet</p>
                                    <p class="text-base font-semibold text-slate-900">
                                        @if($nearestBranch)
                                            {{ $nearestBranch->name }}
                                        @else
                                            Select a store
                                        @endif
                                    </p>
                                </div>
                                <span class="pill bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1">
                                    Open now
                                </span>
                            </div>

                            <div class="flex items-center gap-3 mb-4">
                                <div class="h-11 w-11 rounded-2xl bg-gradient-to-tr from-emerald-400 to-lime-300 flex items-center justify-center">
                                    <i class="fas fa-shopping-basket text-slate-900"></i>
                                </div>
                                <div class="flex-1 text-xs text-slate-600">
                                    Real-time stock, city-based pricing and POS-connected billing — the same engine your
                                    staff uses in the outlet.
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs text-slate-500 border-t border-slate-200 pt-3">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-seedling text-emerald-500"></i>
                                    <span>Over {{ max($featuredProducts->count(), 12) }} fresh items</span>
                                </div>
                                <a href="#featured" class="text-emerald-600 font-semibold hover:text-emerald-700">
                                    Browse now →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nearest Store Section -->
    @if($nearestBranch)
    <section class="py-8 bg-green-50">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-map-marker-alt text-green-600 mr-2"></i>Nearest Store
                </h3>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-2">{{ $nearestBranch->name }}</h4>
                        <p class="text-gray-600 mb-2"><i class="fas fa-map-marker-alt mr-2"></i>{{ $nearestBranch->address }}</p>
                        <p class="text-gray-600 mb-2"><i class="fas fa-phone mr-2"></i>{{ $nearestBranch->phone }}</p>
                        @if($nearestBranch->city)
                        <p class="text-gray-600"><i class="fas fa-city mr-2"></i>{{ $nearestBranch->city->name }}</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-end">
                        <a href="/store/{{ $nearestBranch->id }}/products" class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                            <i class="fas fa-shopping-cart mr-2"></i>Shop Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Featured Products -->
    <section id="featured" class="py-10 md:py-14 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-900">Popular picks near you</h2>
                    <p class="text-slate-500 text-sm mt-1">Top-selling items from your nearest Day2Day outlets.</p>
                </div>
                <div class="hidden md:flex items-center gap-2 text-xs text-slate-400">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    <span>Live stock from POS</span>
                </div>
            </div>

            <div class="overflow-x-auto scrollbar-thin pb-1">
                <div class="flex gap-4 min-w-full">
                    @foreach($featuredProducts as $product)
                        <div class="min-w-[160px] max-w-[170px] bg-white border border-slate-200 rounded-2xl p-4 flex-shrink-0 hover:border-emerald-400/70 hover:-translate-y-1 transition-transform shadow-sm">
                            <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center
                                        {{ $product['category'] === 'fruit' ? 'bg-emerald-50 text-emerald-500' :
                                           ($product['category'] === 'vegetable' ? 'bg-lime-50 text-lime-500' :
                                           ($product['category'] === 'leafy' ? 'bg-green-50 text-green-600' : 'bg-purple-50 text-purple-500')) }}">
                                @if($product['category'] === 'fruit')
                                    <i class="fas fa-apple-alt text-xl"></i>
                                @elseif($product['category'] === 'vegetable')
                                    <i class="fas fa-carrot text-xl"></i>
                                @elseif($product['category'] === 'leafy')
                                    <i class="fas fa-leaf text-xl"></i>
                                @else
                                    <i class="fas fa-seedling text-xl"></i>
                                @endif
                            </div>
                            <h3 class="font-semibold text-slate-800 text-sm text-center line-clamp-2 mb-1">
                                {{ $product['name'] }}
                            </h3>
                            <p class="text-center text-xs text-slate-500 mb-3 capitalize">
                                {{ $product['category'] }}
                            </p>
                            <p class="text-center text-emerald-600 font-bold text-sm">
                                ₹{{ number_format($product['selling_price'], 2) }}
                                <span class="text-[11px] text-slate-400">/ {{ $product['weight_unit'] }}</span>
                            </p>
                            <p class="text-[11px] text-slate-500 text-center mt-1">
                                Stock: {{ $product['current_stock'] }} {{ $product['weight_unit'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Stores Section -->
    <section id="stores" class="py-12 md:py-16 bg-gray-50 border-t border-slate-200">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-900">Choose your store</h2>
                    <p class="text-slate-500 text-sm mt-1">Pick the outlet that’s closest or most convenient for you.</p>
                </div>
                <div class="hidden md:flex items-center gap-2">
                    <span class="pill bg-white text-slate-600 text-xs px-3 py-1 border border-slate-300">
                        <i class="fas fa-location-dot text-emerald-500 mr-1"></i>Location-aware
                    </span>
                </div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($branches as $branch)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-emerald-400/70 hover:-translate-y-1 transition-transform shadow-sm">
                            <div class="flex items-start justify-between gap-2 mb-3">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-1">{{ $branch->name }}</h3>
                                @if($branch->city)
                                    <p class="text-xs text-emerald-600 uppercase tracking-wide font-semibold">
                                        {{ $branch->city->name }}
                                    </p>
                                @endif
                            </div>
                            <span class="pill bg-emerald-500/10 text-emerald-300 text-[11px] font-semibold px-3 py-1 border border-emerald-400/30">
                                In-store &amp; online
                            </span>
                        </div>
                            <p class="text-sm text-slate-600 mb-1">
                                <i class="fas fa-map-marker-alt text-emerald-500 mr-2"></i>{{ $branch->address }}
                        </p>
                        <p class="text-sm text-slate-300 mb-4">
                            <i class="fas fa-phone text-emerald-300 mr-2"></i>{{ $branch->phone }}
                        </p>
                        <a href="/store/{{ $branch->id }}/products"
                           class="inline-flex items-center justify-center gap-2 w-full pill bg-emerald-600 text-white font-semibold text-sm px-4 py-2.5 hover:bg-emerald-700 transition">
                            <i class="fas fa-shopping-basket"></i>
                            <span>Shop from this store</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section id="how-it-works" class="py-10 md:py-14 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <h2 class="text-2xl md:text-3xl font-bold text-slate-900 mb-6 text-center">How ordering works</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="glass-card rounded-2xl p-5 border border-slate-200">
                    <div class="h-10 w-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                        <span class="font-bold">1</span>
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-2">Pick a nearby outlet</h3>
                    <p class="text-sm text-slate-600">Use your GPS or pick manually from the list of Day2Day stores in your city.</p>
                </div>
                <div class="glass-card rounded-2xl p-5 border border-slate-200">
                    <div class="h-10 w-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                        <span class="font-bold">2</span>
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-2">Add items to cart</h3>
                    <p class="text-sm text-slate-600">Browse live stock with branch-wise pricing, then add exactly what you need.</p>
                </div>
                <div class="glass-card rounded-2xl p-5 border border-slate-200">
                    <div class="h-10 w-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                        <span class="font-bold">3</span>
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-2">Confirm &amp; relax</h3>
                    <p class="text-sm text-slate-600">We prepare your order using the same POS system used at the counter, then notify you.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-100 border-t border-slate-200 text-slate-500 py-6">
        <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-3">
            <p class="text-xs md:text-sm">&copy; {{ date('Y') }} Day2Day Fresh. All rights reserved.</p>
            <p class="text-xs md:text-sm flex items-center gap-2">
                <i class="fas fa-leaf text-emerald-500"></i>
                Fresh fruits &amp; vegetables • POS-connected ordering
            </p>
        </div>
    </footer>

    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        window.location.href = `/?lat=${lat}&lng=${lng}`;
                    },
                    function(error) {
                        alert('Unable to get your location. Please select a store manually.');
                    }
                );
            } else {
                alert('Geolocation is not supported by your browser.');
            }
        }
    </script>
</body>
</html>
