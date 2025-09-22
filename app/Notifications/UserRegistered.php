<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'user_registered',
            'title' => 'New User Registered',
            'message' => "{$this->user->name} registered as {$this->user->role?->display_name ?? 'User'}",
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'role' => $this->user->role?->name,
            'branch_id' => $this->user->branch_id,
            'created_at' => now()->toISOString(),
        ];
    }
}

