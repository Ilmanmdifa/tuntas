<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Team: {{ $team->name }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ url('/teams') }}" class="text-sm underline">← Teams</a>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center justify-center w-8 h-8 rounded-md border border-gray-300 bg-white text-gray-500 hover:text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 8a2 2 0 110-4 2 2 0 010 4zm0 8a2 2 0 110-4 2 2 0 010 4zm0 8a2 2 0 110-4 2 2 0 010 4z" />
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        @if ($team->owner_id !== auth()->id())
                            <form method="POST" action="/teams/{{ $team->id }}/leave"
                                onsubmit="return confirm('Leave this team?')">
                                @csrf
                                <button
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Leave
                                    team</button>
                            </form>
                        @endif
                        <div class="py-1">
                            <div class="px-4 py-2 text-xs text-gray-400 uppercase tracking-widest">Team actions</div>
                            @can('delete', $team)
                                <form method="POST" action="/teams/{{ $team->id }}"
                                    onsubmit="return confirm('Delete team? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button
                                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete
                                        team</button>
                                </form>
                            @else
                                <div class="px-4 py-2 text-sm text-gray-400">No actions</div>
                            @endcan
                            {{-- Tambah opsi lain di sini: Edit name, Leave team, etc --}}
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-6">
                    @if (session('status'))
                        <div class="p-3 bg-green-50 text-green-700 rounded-md text-sm">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="p-3 bg-red-50 text-red-700 rounded-md text-sm">{{ $errors->first() }}</div>
                    @endif

                    <div class="flex items-center justify-between border rounded-md px-3 py-2 bg-white">
                        <span class="text-sm text-gray-700">Members ({{ $team->members->count() }})</span>
                        <a href="/teams/{{ $team->id }}/members" class="text-sm text-gray-700 underline">
                            @can('update', $team)
                                Kelola member →
                            @else
                                Lihat member →
                            @endcan
                        </a>
                    </div>

                    <div class="pt-4 border-t">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-semibold text-gray-800">Team tasks</h3>
                            <span class="text-xs text-gray-500">{{ $todos->total() }} total</span>
                        </div>
                        <div class="flex gap-2 mb-3 flex-wrap">
                            <a href="/teams/{{ $team->id }}?filter=pending"
                                class="px-3 py-1.5 rounded-md text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800 {{ !request('filter') || request('filter') === 'pending' ? 'bg-gray-900 text-white' : 'bg-white border' }}">Active</a>
                            <a href="/teams/{{ $team->id }}?filter=done"
                                class="px-3 py-1.5 rounded-md text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800 {{ request('filter') === 'done' ? 'bg-gray-900 text-white' : 'bg-white border' }}">Archived</a>
                        </div>
                        <form method="POST" action="/teams/{{ $team->id }}/todos"
                            class="flex flex-col gap-2 mb-4">
                            @csrf
                            <input name="title" value="{{ old('title') }}" placeholder="Add a team task..." required
                                maxlength="255"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm h-[38px]">
                            <div class="flex gap-2 flex-wrap items-end">
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-500 uppercase tracking-widest">Assignee</label>
                                    <select name="assigned_to" aria-label="Assignee"
                                        class="mt-1 border border-gray-300 rounded-md px-3 pr-12 text-sm h-[38px] min-w-[140px] bg-white">
                                        <option value="">Unassigned</option>
                                        @foreach ($team->members as $m)
                                            <option value="{{ $m->id }}"
                                                {{ old('assigned_to') == $m->id ? 'selected' : '' }}>
                                                {{ $m->name }}
                                                ({{ $m->pivot->role }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-500 uppercase tracking-widest">Priority</label>
                                    <select name="priority" aria-label="Priority"
                                        class="mt-1 border border-gray-300 rounded-md px-3 py-1.5 text-sm min-w-[120px] h-[38px] bg-white">
                                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low
                                        </option>
                                        <option value="medium"
                                            {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium
                                        </option>
                                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-widest">Due
                                        date</label>
                                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                                        aria-label="Due date" title="Due date (optional)"
                                        class="mt-1 border border-gray-300 rounded-md px-3 py-1.5 text-sm min-w-[140px] h-[38px] bg-white">
                                </div>
                            </div>
                            <div>
                                <label
                                    class="text-xs font-medium text-gray-500 uppercase tracking-widest">Description</label>
                                <textarea name="description" rows="2" placeholder="Description (optional)..."
                                    class="mt-1 w-full border border-gray-300 bg-white rounded-md px-3 py-2 text-sm">{{ old('description') }}</textarea>
                            </div>
                            <div class="flex justify-end">
                                <button
                                    class="w-full sm:w-auto min-w-[90px] h-[38px] px-4 bg-gray-800 text-white rounded-md text-sm transition-colors hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800 active:bg-gray-900">Add</button>
                            </div>
                        </form>
                        <ul class="space-y-2">
                            @forelse($todos as $todo)
                                <li class="border rounded-md p-3 space-y-2 {{ $todo->is_done ? 'bg-gray-50' : '' }}">
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="/todos/{{ $todo->id }}/toggle">@csrf
                                            @method('PATCH')
                                            <button
                                                class="w-6 h-6 border rounded flex items-center justify-center shrink-0 {{ $todo->is_done ? 'bg-green-600 text-white border-green-600' : 'bg-white' }}">{{ $todo->is_done ? '✓' : '' }}</button>
                                        </form>
                                        <form method="POST" action="/todos/{{ $todo->id }}"
                                            class="flex flex-1 gap-2 min-w-0">@csrf @method('PATCH')
                                            <input name="title" value="{{ old('title', $todo->title) }}"
                                                class="flex-1 min-w-0 border rounded-md px-2 py-1.5 text-sm {{ $todo->is_done ? 'line-through' : '' }}"
                                                required>
                                            <button class="text-sm underline px-2 shrink-0">Save</button>
                                        </form>
                                        </form>
                                    </div>
                                    <div class="flex gap-2 ml-8 flex-wrap items-center text-xs">
                                        <span
                                            class="px-2 py-1 rounded-md {{ $todo->is_done ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $todo->is_done ? 'Done' : 'Todo' }}</span>
                                        <span class="text-gray-500">by {{ $todo->user->name ?? '—' }}</span>
                                        @if ($todo->assigned_to)
                                            <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-md">→
                                                {{ $todo->assignedTo->name }}</span>
                                        @else
                                            <span class="text-gray-400 italic">· Unassigned</span>
                                        @endif
                                        <span
                                            class="px-2 py-1 rounded {{ $todo->priority === 'high' ? 'bg-red-100 text-red-700' : ($todo->priority === 'low' ? 'bg-gray-100 text-gray-600' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($todo->priority ?? 'medium') }}</span>
                                        @if ($todo->due_date)
                                            @if (!$todo->is_done && $todo->due_date < now()->toDateString())
                                                <span class="px-2 py-1 rounded bg-red-100 text-red-700">Overdue
                                                    {{ \Carbon\Carbon::parse($todo->due_date)->format('d M Y') }}</span>
                                            @else
                                                <span class="px-2 py-1 rounded bg-gray-100 text-gray-600">Due
                                                    {{ \Carbon\Carbon::parse($todo->due_date)->format('d M Y') }}</span>
                                            @endif
                                        @endif
                                    </div>
                                    <form method="POST" action="/todos/{{ $todo->id }}"
                                        class="flex gap-2 ml-8 items-center">
                                        @csrf @method('PATCH')
                                        <label class="text-xs text-gray-500">Assign:</label>
                                        <select name="assigned_to"
                                            class="border rounded-md px-2 py-1 text-xs h-[28px] min-w-[140px] bg-white">
                                            <option value="">Unassigned</option>
                                            @foreach ($team->members as $m)
                                                <option value="{{ $m->id }}"
                                                    {{ $todo->assigned_to == $m->id ? 'selected' : '' }}>
                                                    {{ $m->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="bg-gray-800 text-white px-3 py-1 rounded text-xs transition-colors hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800 active:bg-gray-900">Set</button>
                                    </form>
                                    <div class="flex justify-end items-center gap-3 ml-8">
                                        <a href="/todos/{{ $todo->id }}"
                                            class="text-xs text-gray-700 underline inline-flex items-center h-5">Details</a>
                                        <form method="POST" action="/todos/{{ $todo->id }}"
                                            onsubmit="return confirm('Delete?')" class="flex items-center">@csrf
                                            @method('DELETE')
                                            <button
                                                class="text-xs text-red-600 underline inline-flex items-center h-5">Delete</button>
                                        </form>
                                    </div>
                                </li>
                            @empty
                                <li class="border-2 border-dashed rounded-lg p-6 text-center text-sm text-gray-500">No
                                    team tasks yet — add one above.</li>
                            @endforelse
                        </ul>
                        @if ($todos->hasPages())
                            <div class="mt-3">{{ $todos->links() }}</div>
                        @endif
                    </div>

                    <p class="text-sm text-gray-500"><a href="/teams" class="underline">← Teams</a> · <a
                            href="/todos" class="underline">Tasks</a></p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
