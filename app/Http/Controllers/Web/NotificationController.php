<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Models\NotificationHistory;
use App\Models\NotificationType;
use App\Models\UserNotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->middleware('auth');
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount(): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount(auth()->id());
        
        return response()->json([
            'count' => $count
        ]);
    }

    /**
     * Get recent notifications
     */
    public function getRecent(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $notifications = $this->notificationService->getRecentNotifications(auth()->id(), $limit);
        
        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'icon' => $notification->icon,
                    'color' => $notification->color,
                    'is_read' => $notification->isRead(),
                    'created_at' => $notification->formatted_time,
                    'actions' => $notification->all_actions->map(function ($action) {
                        return [
                            'type' => $action->action_type,
                            'label' => $action->action_label,
                            'url' => $action->getUrlWithParams(),
                            'icon' => $action->icon,
                            'button_class' => $action->button_class,
                            'is_primary' => $action->is_primary,
                        ];
                    }),
                ];
            })
        ]);
    }

    /**
     * Get all notifications with pagination
     */
    public function index(Request $request)
    {
        // If limit parameter is provided, treat as API request and return JSON
        if ($request->has('limit') || $request->wantsJson() || $request->expectsJson()) {
            try {
                $limit = $request->get('limit', 10);
                
                // Check if user is authenticated
                if (!auth()->check()) {
                    return response()->json([
                        'notifications' => [],
                        'unread_count' => 0
                    ], 200);
                }
                
                $userId = auth()->id();
                
                if (!$userId) {
                    return response()->json([
                        'notifications' => [],
                        'unread_count' => 0
                    ], 200);
                }

                $notifications = NotificationHistory::where('user_id', $userId)
                    ->with(['notificationType', 'actions'])
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();

                $unreadCount = 0;
                try {
                    if (method_exists($this->notificationService, 'getUnreadCount')) {
                        $unreadCount = $this->notificationService->getUnreadCount($userId);
                    } else {
                        // Fallback to direct count if service method doesn't exist
                        $unreadCount = NotificationHistory::where('user_id', $userId)
                            ->whereNull('read_at')
                            ->count();
                    }
                } catch (\Exception $e) {
                    // Fallback to direct count if service fails
                    try {
                        $unreadCount = NotificationHistory::where('user_id', $userId)
                            ->whereNull('read_at')
                            ->count();
                    } catch (\Exception $e2) {
                        $unreadCount = 0;
                    }
                }

            return response()->json([
                'notifications' => $notifications->map(function ($notification) {
                    // Safely get icon and color
                    $icon = 'fa-bell';
                    $color = 'blue';
                    if ($notification->notificationType) {
                        $icon = $notification->notificationType->icon ?? 'fa-bell';
                        $color = $notification->notificationType->color ?? 'blue';
                    }
                    
                    // Safely get actions
                    $actions = [];
                    try {
                        // Use the relationship directly instead of accessor
                        $notificationActions = $notification->actions;
                        if ($notificationActions) {
                            $actions = $notificationActions->map(function ($action) {
                                try {
                                    return [
                                        'type' => $action->action_type ?? '',
                                        'label' => $action->action_label ?? '',
                                        'url' => $action->getUrlWithParams() ?? '#',
                                        'icon' => $action->icon ?? 'fa-arrow-right',
                                        'button_class' => $action->button_class ?? 'btn-primary',
                                        'is_primary' => $action->is_primary ?? false,
                                    ];
                                } catch (\Exception $e) {
                                    return null;
                                }
                            })->filter()->values()->toArray();
                        }
                    } catch (\Exception $e) {
                        // If actions fail, just use empty array
                        $actions = [];
                    }
                    
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title ?? 'Notification',
                        'message' => $notification->body ?? '',
                        'body' => $notification->body ?? '',
                        'icon' => $icon,
                        'color' => $color,
                        'is_read' => $notification->isRead(),
                        'read_at' => $notification->read_at ? $notification->read_at->toISOString() : null,
                        'created_at' => $notification->created_at ? $notification->created_at->toISOString() : null,
                        'formatted_time' => $notification->created_at ? $notification->created_at->diffForHumans() : 'Just now',
                        'actions' => $actions,
                    ];
                }),
                'unread_count' => $unreadCount
            ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Database errors (table doesn't exist, connection issues, etc.)
                \Log::warning('Notification API Database Error: ' . $e->getMessage());
                return response()->json([
                    'notifications' => [],
                    'unread_count' => 0
                ]);
            } catch (\Exception $e) {
                // Log the error for debugging
                \Log::error('Notification API Error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'user_id' => auth()->id(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                
                // Return empty response instead of error page
                return response()->json([
                    'notifications' => [],
                    'unread_count' => 0
                ]);
            }
        }

        // Otherwise return view for web requests
        $notifications = NotificationHistory::where('user_id', auth()->id())
            ->with(['notificationType', 'actions'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id = null): JsonResponse
    {
        // Support both route parameter and request body
        $notificationId = $id ?? $request->input('notification_id');
        
        if (!$notificationId) {
            return response()->json([
                'success' => false,
                'message' => 'Notification ID is required'
            ], 400);
        }

        $success = $this->notificationService->markAsRead(
            auth()->id(),
            $notificationId
        );

        if ($success) {
            $unreadCount = $this->notificationService->getUnreadCount(auth()->id());
            
            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to mark notification as read'
        ], 400);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(Request $request): JsonResponse
    {
        $request->validate([
            'notification_id' => 'required|integer|exists:notification_history,id'
        ]);

        $success = $this->notificationService->markAsUnread(
            auth()->id(),
            $request->notification_id
        );

        if ($success) {
            $unreadCount = $this->notificationService->getUnreadCount(auth()->id());
            
            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to mark notification as unread'
        ], 400);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $success = $this->notificationService->markAllAsRead(auth()->id());

        if ($success) {
            return response()->json([
                'success' => true,
                'unread_count' => 0
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to mark all notifications as read'
        ], 400);
    }

    /**
     * Alias for markAllAsRead (for API route compatibility)
     */
    public function markAllRead(): JsonResponse
    {
        return $this->markAllAsRead();
    }

    /**
     * Get notification preferences
     */
    public function getPreferences()
    {
        $preferences = $this->notificationService->getUserPreferences(auth()->id());
        $notificationTypes = NotificationType::active()->get();

        return view('notifications.preferences', compact('preferences', 'notificationTypes'));
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'preferences' => 'required|array',
            'preferences.*.database_enabled' => 'boolean',
            'preferences.*.email_enabled' => 'boolean',
            'preferences.*.sms_enabled' => 'boolean',
            'preferences.*.whatsapp_enabled' => 'boolean',
            'preferences.*.push_enabled' => 'boolean',
            'preferences.*.email_frequency' => 'in:realtime,digest_daily,digest_weekly,disabled',
        ]);

        $success = $this->notificationService->updateUserPreferences(
            auth()->id(),
            $request->preferences
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Preferences updated successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update preferences'
        ], 400);
    }

    /**
     * Handle notification action
     */
    public function handleAction(Request $request): JsonResponse
    {
        $request->validate([
            'notification_id' => 'required|integer|exists:notification_history,id',
            'action_type' => 'required|string'
        ]);

        $notification = NotificationHistory::where('user_id', auth()->id())
            ->where('id', $request->notification_id)
            ->with('actions')
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $action = $notification->actions()
            ->where('action_type', $request->action_type)
            ->first();

        if (!$action) {
            return response()->json([
                'success' => false,
                'message' => 'Action not found'
            ], 404);
        }

        // Mark notification as read when action is taken
        $notification->markAsRead();

        // Handle specific actions
        switch ($request->action_type) {
            case 'approve':
                return $this->handleApproveAction($notification, $action);
            case 'reject':
                return $this->handleRejectAction($notification, $action);
            case 'view':
                return $this->handleViewAction($notification, $action);
            case 'dismiss':
                return $this->handleDismissAction($notification, $action);
            default:
                return response()->json([
                    'success' => true,
                    'message' => 'Action completed',
                    'redirect_url' => $action->getUrlWithParams()
                ]);
        }
    }

    /**
     * Handle approve action
     */
    private function handleApproveAction(NotificationHistory $notification, $action): JsonResponse
    {
        // This would typically call a service to handle the approval
        // For now, we'll just return success
        
        return response()->json([
            'success' => true,
            'message' => 'Item approved successfully',
            'redirect_url' => $action->getUrlWithParams()
        ]);
    }

    /**
     * Handle reject action
     */
    private function handleRejectAction(NotificationHistory $notification, $action): JsonResponse
    {
        // This would typically call a service to handle the rejection
        
        return response()->json([
            'success' => true,
            'message' => 'Item rejected successfully',
            'redirect_url' => $action->getUrlWithParams()
        ]);
    }

    /**
     * Handle view action
     */
    private function handleViewAction(NotificationHistory $notification, $action): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Redirecting...',
            'redirect_url' => $action->getUrlWithParams()
        ]);
    }

    /**
     * Handle dismiss action
     */
    private function handleDismissAction(NotificationHistory $notification, $action): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Notification dismissed'
        ]);
    }

    /**
     * Get notification statistics
     */
    public function getStats(): JsonResponse
    {
        $userId = auth()->id();
        
        $stats = [
            'total' => NotificationHistory::where('user_id', $userId)->count(),
            'unread' => NotificationHistory::where('user_id', $userId)->whereNull('read_at')->count(),
            'today' => NotificationHistory::where('user_id', $userId)
                ->whereDate('created_at', today())
                ->count(),
            'this_week' => NotificationHistory::where('user_id', $userId)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];

        return response()->json($stats);
    }
}