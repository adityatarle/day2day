<?php

namespace App\Notifications;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ManagerAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Branch $branch, public User $manager) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'manager_assigned',
            'title' => 'Branch Manager Assigned',
            'message' => "{$this->manager->name} assigned as manager to {$this->branch->name}",
            'branch_id' => $this->branch->id,
            'branch_name' => $this->branch->name,
            'manager_id' => $this->manager->id,
            'manager_name' => $this->manager->name,
            'created_at' => now()->toISOString(),
        ];
    }
}

