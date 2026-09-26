<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Models\Todo;
use App\Models\User;
use App\Notifications\TodoAssignedNotification;
use Illuminate\Http\Request;

/**
 * TodoController — HTTP layer for todos (Thin Controller)
 * Pattern: Controller delegates to Model (Eloquent), no business logic here
 * Why Todo $todo in update/toggle/destroy? Route Model Binding — Laravel injects Model by {id}
 * Why validate? Guard HTTP input before Model::create (like $fillable guard at Model level)
 * Avoid: don't use $request->all() blindly; only allow fillable fields
 */
class TodoController extends Controller
{
    public function index(Request $request)
    {
        $query = Todo::visibleTo(auth()->user());
        if ($request->scope === 'my') {
            $query->where(function ($q) {
                $q->where('user_id', auth()->id())->orWhere('assigned_to', auth()->id());
            });
        } elseif ($request->scope === 'team') {
            $teamIds = auth()->user()->teams()->pluck('teams.id');
            $query->whereIn('team_id', $teamIds);
        }
        $todos = $query->with(['assignedTo', 'team'])
            ->when($request->q, fn($q) => $q->where(function ($w) use ($request) {
                $w->where('title', 'like', "%{$request->q}%")->orWhere('description', 'like', "%{$request->q}%");
            }))
            ->when($request->filter === 'done', fn($q) => $q->where('is_done', true), fn($q) => $q->where('is_done', false))
            ->when($request->filter === 'overdue', fn($q) => $q->where('due_date', '<', now()->toDateString()))
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->latest()->paginate(5)->appends(['q' => $request->q, 'filter' => $request->filter ?? 'pending', 'scope' => $request->scope]);
        return view('todos.index', ['todos' => $todos]);
    }

    public function show(Todo $todo)
    {
        $this->authorize('view', $todo);
        $todo->load(['assignedTo', 'team.members', 'user']);
        return view('todos.show', ['todo' => $todo]);
    }

    public function store(StoreTodoRequest $request)
    {
        $validated = $request->validated();
        Todo::create([
            'title' => $validated['title'],
            'is_done' => false,
            'user_id' => auth()->id(),
            'priority' => $validated['priority'] ?? 'medium',
            'due_date' => $validated['due_date'] ?? null,
            'description' => $validated['description'] ?? null
        ]);
        return redirect('/todos')->with('status', 'Todo added');
    }

    public function toggle(Todo $todo)
    {
        $this->authorize('update', $todo);
        $todo->update(['is_done' => !$todo->is_done]);
        return redirect('/todos')->with('status', $todo->is_done ? 'Marked done' : 'Marked pending');
    }

    public function update(UpdateTodoRequest $request, Todo $todo)
    {
        $this->authorize('update', $todo);
        $validated = $request->validated();
        $data = [];
        if (isset($validated['title'])) $data['title'] = $validated['title'];
        if (array_key_exists('assigned_to', $validated)) {
            if ($todo->team_id && $validated['assigned_to'] && !$todo->team->members->contains($validated['assigned_to'])) {
                return back()->withErrors(['assigned_to' => 'Assignee is not a team member']);
            }
            $data['assigned_to'] = $validated['assigned_to'];
        }
        foreach (['priority', 'due_date', 'description'] as $f) {
            if (array_key_exists($f, $validated)) $data[$f] = $validated[$f];
        }
        $old = $todo->assigned_to;
        if ($data) $todo->update($data);
        $new = $todo->fresh()->assigned_to;
        if (!empty($new) && $new != $old && $new != auth()->id()) {
            User::find($new)?->notify(new TodoAssignedNotification($todo, auth()->user()->name));
        }
        return back()->with('status', 'Todo updated');
    }

    public function destroy(Todo $todo)
    {
        $this->authorize('delete', $todo);
        $todo->delete();
        return redirect('/todos')->with('status', 'Todo deleted');
    }
}
