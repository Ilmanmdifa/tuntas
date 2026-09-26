<?php

namespace App\Notifications;

use App\Models\Todo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TodoAssignedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Todo $todo, public string $assignerName)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'todo_id' => $this->todo->id,
            'title' => $this->todo->title,
            'team_id' => $this->todo->team_id,
            'team_name' => $this->todo->team?->name ?? 'Personal',
            'assigned_by' => $this->assignerName,
        ];
    }
}
