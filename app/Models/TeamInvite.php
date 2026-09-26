<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TeamInvite — undangan via email ke team
 * Pattern: Invite → Notification → Accept (pivot)
 * Why team_id + unique email? Cegah dobel invite ke orang yang sama
 * Why invited_by FK? Track siapa ngundang (audit)
 * Avoid: jangan attach langsung tanpa invite (lewati flow pending/accepted)
 */
class TeamInvite extends Model
{
    protected $fillable = ['team_id', 'email', 'invited_by', 'status'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
