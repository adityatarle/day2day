@extends('layouts.app')

@section('title', 'Outlet Management')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Outlet Management</h1>
                    <p class="text-gray-600">Manage your food company outlets across different cities</p>
                </div>
                <a href="{{ route('outlets.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Add New Outlet
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                    <select id="city-filter" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All Cities</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Outlet Type</label>
                    <select id="type-filter" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All Types</option>
                        <option value="retail">Retail</option>
                        <option value="wholesale">Wholesale</option>
                        <option value="kiosk">Kiosk</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">POS Status</label>
                    <select id="pos-filter" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All</option>
                        <option value="enabled">POS Enabled</option>
                        <option value="disabled">POS Disabled</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="applyFilters()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Outlets Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="outlets-grid">
            @foreach($outlets as $outlet)
                <div class="bg-white rounded-xl shadow-sm border hover:shadow-md transition-shadow outlet-card" 
                     data-city="{{ $outlet->city_id }}" 
                     data-type="{{ $outlet->outlet_type }}" 
                     data-pos="{{ $outlet->pos_enabled ? 'enabled' : 'disabled' }}">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $outlet->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $outlet->code }}</p>
                            </div>
                            <div class="flex space-x-2">
                                @if($outlet->pos_enabled)
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">POS</span>
                                @endif
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full capitalize">
                                    {{ $outlet->outlet_type }}
                                </span>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-map-marker-alt w-4 mr-2"></i>
                                <span>{{ $outlet->city->name ?? 'No City' }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-phone w-4 mr-2"></i>
                                <span>{{ $outlet->phone }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-envelope w-4 mr-2"></i>
                                <span>{{ $outlet->email }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-users w-4 mr-2"></i>
                                <span>{{ $outlet->users->count() }} Staff Members</span>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium text-gray-700">Status:</span>
                            @if($outlet->is_active)
                                <span class="flex items-center text-green-600">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                    Active
                                </span>
                            @else
                                <span class="flex items-center text-red-600">
                                    <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                    Inactive
                                </span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <a href="{{ route('outlets.show', $outlet) }}" 
                               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 rounded-lg text-sm font-medium">
                                View Details
                            </a>
                            <a href="{{ route('outlets.edit', $outlet) }}" 
                               class="flex-1 bg-gray-600 hover:bg-gray-700 text-white text-center py-2 rounded-lg text-sm font-medium">
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($outlets->isEmpty())
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-store text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Outlets Found</h3>
                <p class="text-gray-600 mb-4">Get started by creating your first outlet</p>
                <a href="{{ route('outlets.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg">
                    Create First Outlet
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function applyFilters() {
    const cityFilter = document.getElementById('city-filter').value;
    const typeFilter = document.getElementById('type-filter').value;
    const posFilter = document.getElementById('pos-filter').value;
    
    const cards = document.querySelectorAll('.outlet-card');
    
    cards.forEach(card => {
        let show = true;
        
        if (cityFilter && card.dataset.city !== cityFilter) {
            show = false;
        }
        
        if (typeFilter && card.dataset.type !== typeFilter) {
            show = false;
        }
        
        if (posFilter && card.dataset.pos !== posFilter) {
            show = false;
        }
        
        card.style.display = show ? 'block' : 'none';
    });
}
</script>
@endsection