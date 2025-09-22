<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get latest notifications for authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->take((int) ($request->get('limit', 10)))
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->data['type'] ?? 'info',
                    'title' => $n->data['title'] ?? 'Notification',
                    'message' => $n->data['message'] ?? '',
                    'data' => $n->data,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at,
                ];
            });

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        if (!$notification->read_at) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
}

