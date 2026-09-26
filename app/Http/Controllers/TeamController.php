<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Models\Team;
use App\Models\TeamInvite;
use App\Models\User;
use App\Notifications\TeamInviteNotification;
use App\Notifications\TodoAssignedNotification;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * TeamController — manage teams & members (Thin Controller)
     * Why authorize view/update? TeamPolicy checks owner/member
     * Why attach owner? Pivot needs owner role, not just teams.owner_id
     * Avoid: don't use $request->all(), validate first
     */

    public function index()
    {
        $user = auth()->user();
        $teams = $user->teams()->wherePivot('role', 'member')->latest()->paginate(10);
        $owned = $user->ownedTeams()->latest()->paginate(10, ['*'], 'owned_page');
        return view('teams.index', compact('teams', 'owned'));
    }

    public function store(StoreTeamRequest $request)
    {
        $validated = $request->validated();
        $team = Team::create(['name' => $validated['name'], 'owner_id' => auth()->id()]);
        $team->members()->attach(auth()->id(), ['role' => 'owner']);
        return redirect('/teams/' . $team->id);
    }

    public function show(Request $request, Team $team)
    {
        $this->authorize('view', $team);
        $team->load('members');
        $todos = $team
            ->todos()
            ->with(['user', 'assignedTo'])
            ->when($request->filter === 'done', fn($q) => $q->where('is_done', true), fn($q) => $q->where('is_done', false))
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(5)
            ->appends(['filter' => $request->filter ?? 'pending']);
        return view('teams.show', compact('team', 'todos'));
    }

    public function members(Team $team)
    {
        $this->authorize('view', $team);
        $team->load('members');
        return view('teams.members', ['team' => $team]);
    }

    public function addMember(Request $request, Team $team)
    {
        $this->authorize('update', $team);
        $validated = $request->validate(['email' => 'required|email|exists:users,email']);
        $user = User::where('email', $validated['email'])->first();
        if ($team->members->contains($user->id)) {
            return back()->withErrors(['email' => 'Already member']);
        }
        if (TeamInvite::where('team_id', $team->id)->where('email', $validated['email'])->where('status', 'pending')->exists()) {
            return back()->withErrors(['email' => 'Already invited']);
        }
        $invite = TeamInvite::create([
            'team_id' => $team->id,
            'email' => $validated['email'],
            'invited_by' => auth()->id(),
            'status' => 'pending',
        ]);
        $user?->notify(new TeamInviteNotification($invite));
        return back()->with('status', 'Invite sent + bell');
    }

    public function storeTodo(Request $request, Team $team)
    {
        $this->authorize('view', $team);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'sometimes|in:low,medium,high',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string'
        ]);
        // cegah assign ke bukan member? (optional: kalau assigned_to ada, cek member)
        if (!empty($validated['assigned_to']) && !$team->members->contains($validated['assigned_to'])) {
            return back()->withErrors(['assigned_to' => 'Assignee is not a team member']);
        }

        $todo = $team->todos()->create([
            'title' => $validated['title'],
            'user_id' => auth()->id(),
            'assigned_to' => $validated['assigned_to'] ?? null,
            'is_done' => false,
            'priority' => $validated['priority'] ?? 'medium',
            'due_date' => $validated['due_date'] ?? null,
            'description' => $validated['description'] ?? null
        ]);

        if (!empty($validated['assigned_to']) && $validated['assigned_to'] != auth()->id()) {
            User::find($validated['assigned_to'])?->notify(new TodoAssignedNotification($todo, auth()->user()->name));
        }

        return back()->with('status', 'Team todo added' . (!empty($validated['assigned_to']) ? ' → assigned' : ''));
    }

    public function acceptInvite(TeamInvite $invite)
    {
        abort_unless($invite->email === auth()->user()->email, 403);
        if ($invite->status !== 'pending') {
            return back()->with('status', 'Invite already handled');
        }
        $invite->update(['status' => 'accepted']);
        $invite->team->members()->syncWithoutDetaching([auth()->id() => ['role' => 'member']]);
        auth()
            ->user()
            ->notifications()
            ->where('data->invite_id', $invite->id)
            ->update(['read_at' => now()]);
        return redirect('/teams/' . $invite->team_id)->with('status', 'Joined team');
    }

    public function declineInvite(TeamInvite $invite)
    {
        abort_unless($invite->email === auth()->user()->email, 403);
        if ($invite->status !== 'pending') {
            return back()->with('status', 'Invite already handled');
        }
        $invite->update(['status' => 'declined']);
        auth()
            ->user()
            ->notifications()
            ->where('data->invite_id', $invite->id)
            ->update(['read_at' => now()]);
        return back()->with('status', 'Invite declined');
    }

    public function markNotificationRead(string $id)
    {
        auth()->user()->notifications()->where('id', $id)->firstOrFail()->markAsRead();
        return back();
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    }

    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);
        $team->delete();
        return redirect('/teams')->with('status', 'Team deleted');
    }

    public function leave(Team $team)
    {
        $this->authorize('view', $team);
        if ($team->owner_id === auth()->id()) {
            return back()->withErrors(["message" => "'Owner can't leave"]);
        }
        $team->members()->detach(auth()->id());
        return redirect('/teams')->with('status', 'Leave successfully!');
    }

    public function removeMember(Team $team, User $member)
    {
        $this->authorize('update', $team);
        if ($member->id === $team->owner_id) {
            return back()->withErrors(['email' => 'Cannot remove owner']);
        }
        if ($member->id === auth()->id()) {
            return back()->withErrors(['email' => 'Use Leave owner']);
        }
        $team->members()->detach($member->id);
        return back()->with('status', 'Member removed');
    }
}
