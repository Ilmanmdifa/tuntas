<?php

namespace App\Notifications;

use App\Models\TeamInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;


/**
 * TeamInviteNotification — bell saat di-invite team
 * Why database? Bell di nav baca notifications table (tanpa email)
 * Why TeamInvite di construct? Bawa team_name + inviter agar toArray kaya
 * Avoid: jangan mail channel dulu (butuh SMTP), database cukup untuk porto
 */
class TeamInviteNotification extends Notification
{
    use Queueable;

    public function __construct(public TeamInvite $invite) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'team_id' => $this->invite->team_id,
            'team_name' => $this->invite->team->name,
            'invited_by' => $this->invite->inviter->name,
            'email' => $this->invite->email,
            'invite_id' => $this->invite->id,
        ];
    }
}
