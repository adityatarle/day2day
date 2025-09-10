@extends('layouts.app')

@section('title', 'Edit Outlet - ' . $outlet->name)

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Outlet</h1>
                    <p class="text-gray-600">Update details for {{ $outlet->name }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('outlets.show', $outlet) }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg">
                        View Details
                    </a>
                    <a href="{{ route('outlets.index') }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg">
                        Back to Outlets
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border">
            <form action="{{ route('outlets.update', $outlet) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                    </div>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Outlet Name *</label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $outlet->name) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                               required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Outlet Code *</label>
                        <input type="text" 
                               name="code" 
                               id="code" 
                               value="{{ old('code', $outlet->code) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('code') border-red-500 @enderror"
                               required>
                        @error('code')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                        <input type="tel" 
                               name="phone" 
                               id="phone" 
                               value="{{ old('phone', $outlet->phone) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror"
                               required>
                        @error('phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email', $outlet->email) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                               required>
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                        <textarea name="address" 
                                  id="address" 
                                  rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror"
                                  required>{{ old('address', $outlet->address) }}</textarea>
                        @error('address')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Location Information -->
                    <div class="md:col-span-2 border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Location Information</h3>
                    </div>

                    <div>
                        <label for="city_id" class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                        <select name="city_id" 
                                id="city_id" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('city_id') border-red-500 @enderror"
                                required>
                            <option value="">Select City</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id', $outlet->city_id) == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }} ({{ $city->state }})
                                </option>
                            @endforeach
                        </select>
                        @error('city_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="outlet_type" class="block text-sm font-medium text-gray-700 mb-2">Outlet Type *</label>
                        <select name="outlet_type" 
                                id="outlet_type" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('outlet_type') border-red-500 @enderror"
                                required>
                            <option value="">Select Type</option>
                            <option value="retail" {{ old('outlet_type', $outlet->outlet_type) == 'retail' ? 'selected' : '' }}>Retail</option>
                            <option value="wholesale" {{ old('outlet_type', $outlet->outlet_type) == 'wholesale' ? 'selected' : '' }}>Wholesale</option>
                            <option value="kiosk" {{ old('outlet_type', $outlet->outlet_type) == 'kiosk' ? 'selected' : '' }}>Kiosk</option>
                        </select>
                        @error('outlet_type')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
                        <input type="number" 
                               name="latitude" 
                               id="latitude" 
                               step="any"
                               value="{{ old('latitude', $outlet->latitude) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('latitude') border-red-500 @enderror">
                        @error('latitude')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
                        <input type="number" 
                               name="longitude" 
                               id="longitude" 
                               step="any"
                               value="{{ old('longitude', $outlet->longitude) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('longitude') border-red-500 @enderror">
                        @error('longitude')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- POS Configuration -->
                    <div class="md:col-span-2 border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">POS Configuration</h3>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="pos_enabled" 
                                   value="1" 
                                   {{ old('pos_enabled', $outlet->pos_enabled) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Enable POS System</span>
                        </label>
                    </div>

                    <div>
                        <label for="pos_terminal_id" class="block text-sm font-medium text-gray-700 mb-2">POS Terminal ID</label>
                        <input type="text" 
                               name="pos_terminal_id" 
                               id="pos_terminal_id" 
                               value="{{ old('pos_terminal_id', $outlet->pos_terminal_id) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('pos_terminal_id') border-red-500 @enderror">
                        @error('pos_terminal_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="md:col-span-2 border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1" 
                                   {{ old('is_active', $outlet->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Outlet is Active</span>
                        </label>
                        <p class="text-sm text-gray-500 mt-1">Inactive outlets cannot process orders or POS transactions</p>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4 mt-8 pt-6 border-t">
                    <a href="{{ route('outlets.show', $outlet) }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-6 py-3 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg">
                        Update Outlet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-generate POS terminal ID based on outlet code if it's empty
document.getElementById('code').addEventListener('input', function() {
    const code = this.value.toUpperCase();
    const terminalIdField = document.getElementById('pos_terminal_id');
    
    // Only auto-generate if the field is empty
    if (code && !terminalIdField.value) {
        terminalIdField.value = 'POS-' + code;
    }
});
</script>
@endsection