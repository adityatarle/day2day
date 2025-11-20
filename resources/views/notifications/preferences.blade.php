@extends('layouts.app')

@section('title', 'Notification Preferences')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-cog text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Notification Preferences</h1>
                    <p class="text-gray-600">Customize how you receive notifications</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('notifications.index') }}" class="btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Notifications
                </a>
                <button onclick="savePreferences()" class="btn-primary">
                    <i class="fas fa-save mr-2"></i>Save Preferences
                </button>
            </div>
        </div>
    </div>

    <!-- Preferences Form -->
    <form id="preferences-form">
        @csrf
        <div class="space-y-6">
            @foreach($notificationTypes as $notificationType)
                @php
                    $preference = $preferences->where('notification_type_id', $notificationType->id)->first();
                    $defaults = $notificationType->getDefaultUserPreferences();
                @endphp
                
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden">
                    <!-- Notification Type Header -->
                    <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-gray-50 to-blue-50">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" 
                                 style="background-color: {{ $notificationType->color }}20; color: {{ $notificationType->color }}">
                                <i class="{{ $notificationType->icon }}"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900">{{ $notificationType->display_name }}</h3>
                                <p class="text-sm text-gray-600">{{ $notificationType->description }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $notificationType->priority_badge_class }}">
                                    {{ $notificationType->priority_display_name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Channels -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Channel Preferences -->
                            <div class="space-y-4">
                                <h4 class="text-sm font-medium text-gray-900">Notification Channels</h4>
                                
                                <!-- Database Notifications -->
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-database text-blue-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">In-App Notifications</p>
                                            <p class="text-xs text-gray-500">Bell icon notifications</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               name="preferences[{{ $notificationType->id }}][database_enabled]"
                                               value="1"
                                               {{ ($preference->database_enabled ?? $defaults['database_enabled']) ? 'checked' : '' }}
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <!-- Email Notifications -->
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-envelope text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Email Notifications</p>
                                            <p class="text-xs text-gray-500">Email alerts</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               name="preferences[{{ $notificationType->id }}][email_enabled]"
                                               value="1"
                                               {{ ($preference->email_enabled ?? $defaults['email_enabled']) ? 'checked' : '' }}
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                    </label>
                                </div>

                                <!-- SMS Notifications -->
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-sms text-yellow-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">SMS Notifications</p>
                                            <p class="text-xs text-gray-500">Text messages (Critical only)</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               name="preferences[{{ $notificationType->id }}][sms_enabled]"
                                               value="1"
                                               {{ ($preference->sms_enabled ?? $defaults['sms_enabled']) ? 'checked' : '' }}
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-600"></div>
                                    </label>
                                </div>

                                <!-- WhatsApp Notifications -->
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                            <i class="fab fa-whatsapp text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">WhatsApp Notifications</p>
                                            <p class="text-xs text-gray-500">WhatsApp messages</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               name="preferences[{{ $notificationType->id }}][whatsapp_enabled]"
                                               value="1"
                                               {{ ($preference->whatsapp_enabled ?? $defaults['whatsapp_enabled']) ? 'checked' : '' }}
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                    </label>
                                </div>

                                <!-- Push Notifications -->
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-mobile-alt text-purple-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Push Notifications</p>
                                            <p class="text-xs text-gray-500">Mobile app notifications</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               name="preferences[{{ $notificationType->id }}][push_enabled]"
                                               value="1"
                                               {{ ($preference->push_enabled ?? $defaults['push_enabled']) ? 'checked' : '' }}
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                    </label>
                                </div>
                            </div>

                            <!-- Email Frequency -->
                            <div class="space-y-4">
                                <h4 class="text-sm font-medium text-gray-900">Email Frequency</h4>
                                
                                <div class="space-y-3">
                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="radio" 
                                               name="preferences[{{ $notificationType->id }}][email_frequency]"
                                               value="realtime"
                                               {{ ($preference->email_frequency ?? $defaults['email_frequency']) === 'realtime' ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Real-time</p>
                                            <p class="text-xs text-gray-500">Receive emails immediately</p>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="radio" 
                                               name="preferences[{{ $notificationType->id }}][email_frequency]"
                                               value="digest_daily"
                                               {{ ($preference->email_frequency ?? $defaults['email_frequency']) === 'digest_daily' ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Daily Digest</p>
                                            <p class="text-xs text-gray-500">Summary email once per day</p>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="radio" 
                                               name="preferences[{{ $notificationType->id }}][email_frequency]"
                                               value="digest_weekly"
                                               {{ ($preference->email_frequency ?? $defaults['email_frequency']) === 'digest_weekly' ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Weekly Digest</p>
                                            <p class="text-xs text-gray-500">Summary email once per week</p>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="radio" 
                                               name="preferences[{{ $notificationType->id }}][email_frequency]"
                                               value="disabled"
                                               {{ ($preference->email_frequency ?? $defaults['email_frequency']) === 'disabled' ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Disabled</p>
                                            <p class="text-xs text-gray-500">No email notifications</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </form>
</div>

<script>
function savePreferences() {
    const form = document.getElementById('preferences-form');
    const formData = new FormData(form);
    
    // Convert FormData to JSON
    const preferences = {};
    for (let [key, value] of formData.entries()) {
        const matches = key.match(/preferences\[(\d+)\]\[(.+)\]/);
        if (matches) {
            const notificationTypeId = matches[1];
            const setting = matches[2];
            
            if (!preferences[notificationTypeId]) {
                preferences[notificationTypeId] = {};
            }
            
            preferences[notificationTypeId][setting] = value === '1' ? true : value;
        }
    }

    fetch('{{ route("notifications.update-preferences") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            preferences: preferences
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Preferences saved successfully', 'success');
        } else {
            showToast('Failed to save preferences', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving preferences', 'error');
    });
}

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        'bg-blue-500'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endsection




