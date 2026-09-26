<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    $base = \App\Models\Todo::visibleTo($user);
    $totalTodos = (clone $base)->count();
    $doneTodos = (clone $base)->where('is_done', true)->count();
    $pendingTodos = $totalTodos - $doneTodos;
    $teamsCount = $user->teams()->count();
    $ownedCount = $user->ownedTeams()->count();
    $recentTodos = (clone $base)->latest()->limit(5)->get();
    $myTeams = $user->teams()->latest()->limit(3)->get();
    $progress = $totalTodos > 0 ? round($doneTodos / $totalTodos * 100) : 0;
    return view('dashboard', compact('totalTodos', 'doneTodos', 'pendingTodos', 'teamsCount', 'ownedCount', 'recentTodos', 'myTeams', 'progress'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Todos — protected, need login (Day 28 Auth)
    Route::get('/todos', [TodoController::class, 'index']);
    Route::get('/todos/{todo}', [TodoController::class, 'show']);
    Route::post('/todos', [TodoController::class, 'store']);
    Route::patch('/todos/{todo}', [TodoController::class, 'update']);
    Route::delete('/todos/{todo}', [TodoController::class, 'destroy']);
    Route::patch('/todos/{todo}/toggle', [TodoController::class, 'toggle']);

    // Teams
    Route::get('/teams', [TeamController::class, 'index']);
    Route::post('/teams', [TeamController::class, 'store']);
    Route::get('/teams/{team}', [TeamController::class, 'show']);
    Route::get('/teams/{team}/members', [TeamController::class, 'members']);
    Route::post('/teams/{team}/members', [TeamController::class, 'addMember']);
    Route::post('/teams/{team}/todos', [TeamController::class, 'storeTodo']);
    Route::delete('/teams/{team}', [TeamController::class, 'destroy']);
    Route::post('/invites/{invite}/accept', [TeamController::class, 'acceptInvite']);
    Route::post('/invites/{invite}/decline', [TeamController::class, 'declineInvite']);
    Route::post('/teams/{team}/leave', [TeamController::class, 'leave']);
    Route::delete('/teams/{team}/members/{member}', [TeamController::class, 'removeMember']);
    Route::post('/notifications/read-all', [TeamController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read', [TeamController::class, 'markNotificationRead']);
    Route::get('/invites', fn() => view('invites.index', ['invites' => auth()->user()->notifications()->where('type', 'App\Notifications\TeamInviteNotification')->latest()->paginate(10)]));
    Route::get('/notifications', fn() => view('notifications.index', ['items' => auth()->user()->notifications()
        ->when(request('tab') === 'tasks', fn($q) => $q->where('type', 'App\Notifications\TodoAssignedNotification'))
        ->when(request('tab') === 'invites', fn($q) => $q->where('type', 'App\Notifications\TeamInviteNotification'))
        ->latest()->paginate(10)->appends(['tab' => request('tab')])]));
});

require __DIR__ . '/auth.php';
