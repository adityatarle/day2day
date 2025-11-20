@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-bell text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                    <p class="text-gray-600">Stay updated with your business activities</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="markAllAsRead()" class="btn-secondary">
                    <i class="fas fa-check-double mr-2"></i>Mark All Read
                </button>
                <a href="{{ route('notifications.preferences') }}" class="btn-primary">
                    <i class="fas fa-cog mr-2"></i>Preferences
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Notifications</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-count">{{ $notifications->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-bell text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Unread</p>
                    <p class="text-2xl font-bold text-red-600" id="unread-count">{{ $notifications->whereNull('read_at')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Today</p>
                    <p class="text-2xl font-bold text-blue-600" id="today-count">{{ $notifications->where('created_at', '>=', today())->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-day text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">This Week</p>
                    <p class="text-2xl font-bold text-green-600" id="week-count">{{ $notifications->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-week text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-modern-lg border border-gray-200/50 overflow-hidden">
        <div class="p-6 border-b border-gray-200/50">
            <h3 class="text-lg font-bold text-gray-900">All Notifications</h3>
        </div>
        
        @if($notifications->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($notifications as $notification)
                    <div class="p-6 hover:bg-gray-50 transition-colors duration-200 {{ $notification->isUnread() ? 'bg-blue-50/50' : '' }}" 
                         id="notification-{{ $notification->id }}">
                        <div class="flex items-start space-x-4">
                            <!-- Notification Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center" 
                                     style="background-color: {{ $notification->color }}20; color: {{ $notification->color }}">
                                    <i class="{{ $notification->icon }}"></i>
                                </div>
                            </div>

                            <!-- Notification Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900 {{ $notification->isUnread() ? 'font-bold' : '' }}">
                                        {{ $notification->title }}
                                    </h4>
                                    <div class="flex items-center space-x-2">
                                        @if($notification->isUnread())
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                New
                                            </span>
                                        @endif
                                        <span class="text-xs text-gray-500">
                                            {{ $notification->formatted_time }}
                                        </span>
                                    </div>
                                </div>
                                
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ $notification->body }}
                                </p>

                                <!-- Actions -->
                                @if($notification->all_actions->count() > 0)
                                    <div class="mt-3 flex items-center space-x-2">
                                        @foreach($notification->all_actions as $action)
                                            <button onclick="handleAction({{ $notification->id }}, '{{ $action->action_type }}')"
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md {{ $action->button_class }} hover:opacity-80 transition-opacity">
                                                <i class="{{ $action->icon }} mr-1"></i>
                                                {{ $action->action_label }}
                                            </button>
                                        @endforeach
                                        
                                        <button onclick="toggleReadStatus({{ $notification->id }}, {{ $notification->isRead() ? 'false' : 'true' }})"
                                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                            <i class="fas {{ $notification->isRead() ? 'fa-envelope-open' : 'fa-envelope' }} mr-1"></i>
                                            {{ $notification->isRead() ? 'Mark Unread' : 'Mark Read' }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="p-6 border-t border-gray-200/50">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bell-slash text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications yet</h3>
                <p class="text-gray-600">You'll see notifications about your business activities here.</p>
            </div>
        @endif
    </div>
</div>

<script>
// Mark all notifications as read
function markAllAsRead() {
    fetch('{{ route("notifications.mark-all-read") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update unread count
            document.getElementById('unread-count').textContent = '0';
            
            // Remove "New" badges and update styling
            document.querySelectorAll('[id^="notification-"]').forEach(notification => {
                notification.classList.remove('bg-blue-50/50');
                const title = notification.querySelector('h4');
                if (title) title.classList.remove('font-bold');
                
                const badge = notification.querySelector('.bg-blue-100');
                if (badge) badge.remove();
            });
            
            showToast('All notifications marked as read', 'success');
        } else {
            showToast('Failed to mark all notifications as read', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

// Toggle read status
function toggleReadStatus(notificationId, markAsRead) {
    const url = markAsRead ? 
        '{{ route("notifications.mark-read") }}' : 
        '{{ route("notifications.mark-unread") }}';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update unread count
            document.getElementById('unread-count').textContent = data.unread_count;
            
            // Update notification styling
            const notification = document.getElementById(`notification-${notificationId}`);
            if (markAsRead) {
                notification.classList.remove('bg-blue-50/50');
                const title = notification.querySelector('h4');
                if (title) title.classList.remove('font-bold');
                
                const badge = notification.querySelector('.bg-blue-100');
                if (badge) badge.remove();
            } else {
                notification.classList.add('bg-blue-50/50');
                const title = notification.querySelector('h4');
                if (title) title.classList.add('font-bold');
            }
            
            showToast(markAsRead ? 'Notification marked as read' : 'Notification marked as unread', 'success');
        } else {
            showToast('Failed to update notification status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

// Handle notification action
function handleAction(notificationId, actionType) {
    fetch('{{ route("notifications.handle-action") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            notification_id: notificationId,
            action_type: actionType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                showToast(data.message || 'Action completed successfully', 'success');
            }
        } else {
            showToast(data.message || 'Failed to complete action', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

// Toast notification function
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        'bg-blue-500'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endsection




