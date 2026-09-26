<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Todo — Eloquent Model for `todos` table (1 class = 1 table)
 * Pattern: Active Record (Model = row, Eloquent handles SQL)
 * Why Todo -> todos? Laravel convention singular Model -> plural table (auto)
 * Why $fillable? Whitelist mass assignment guard — title, is_done, user_id, team_id, assigned_to
 * Avoid: $guarded=[] (allow all) in production; id/timestamps auto, don't put in fillable
 */
class Todo extends Model
{
    protected $fillable = ['title', 'is_done', 'user_id', 'team_id', 'assigned_to', 'priority', 'due_date', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // DRY scope untuk My Work vs Team Board
    public function scopeVisibleTo($query, User $user)
    {
        $teamIds = $user->teams()->pluck('teams.id');
        return $query->where(function ($q) use ($user, $teamIds) {
            $q->where('user_id', $user->id)
                ->orWhere('assigned_to', $user->id)
                ->orWhereIn('team_id', $teamIds);
        });
    }
}
