<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /**
     * Team — group for shared todos (Many-to-Many via team_user)
     * Pattern: belongsToMany (N-to-N) + hasMany (Team has many Todos)
     * Why pivot team_user? 1 user N teams, 1 team N users
     * Why owner_id? Single owner can delete/invite, not just role in pivot
     * Avoid: don't put user logic in Team, keep via pivot
     */
    protected $fillable = ['name', 'owner_id'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user')->withPivot('role')->withTimestamps();
    }

    public function todos()
    {
        return $this->hasMany(Todo::class);
    }
}
