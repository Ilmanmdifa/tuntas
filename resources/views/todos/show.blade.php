<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate">
                {{ $todo->title }}
            </h2>
            <a href="{{ url('/todos') }}" class="text-sm underline shrink-0">← Tasks</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-3 bg-green-50 text-green-700 rounded-md text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="p-3 bg-red-50 text-red-700 rounded-md text-sm">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="/todos/{{ $todo->id }}" class="space-y-3">
                        @csrf @method('PATCH')
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-widest">Title</label>
                            <input name="title" value="{{ old('title', $todo->title) }}"
                                class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 text-sm h-[38px]"
                                required maxlength="255">
                        </div>
                        <div>
                            <label
                                class="text-xs font-medium text-gray-500 uppercase tracking-widest">Description</label>
                            <textarea name="description" rows="4" placeholder="Add details..."
                                class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 text-sm">{{ old('description', $todo->description) }}</textarea>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            <div>
                                <label
                                    class="text-xs font-medium text-gray-500 uppercase tracking-widest">Priority</label>
                                <select name="priority"
                                    class="mt-1 border border-gray-300 rounded-md px-3 py-1.5 text-sm min-w-[120px] h-[38px] bg-white">
                                    <option value="low"
                                        {{ old('priority', $todo->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium"
                                        {{ old('priority', $todo->priority) === 'medium' ? 'selected' : '' }}>Medium
                                    </option>
                                    <option value="high"
                                        {{ old('priority', $todo->priority) === 'high' ? 'selected' : '' }}>High
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-widest">Due
                                    date</label>
                                <input type="date" name="due_date" value="{{ old('due_date', $todo->due_date) }}"
                                    class="mt-1 border border-gray-300 rounded-md px-3 py-1.5 text-sm min-w-[140px] h-[38px] bg-white">
                            </div>
                            @if ($todo->team)
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-widest">Assigned
                                        to</label>
                                    <select name="assigned_to"
                                        class="mt-1 border border-gray-300 rounded-md px-3 py-1.5 text-sm min-w-[140px] h-[38px] bg-white">
                                        <option value="">Unassigned</option>
                                        @foreach ($todo->team->members as $m)
                                            <option value="{{ $m->id }}"
                                                {{ (int) old('assigned_to', $todo->assigned_to) === $m->id ? 'selected' : '' }}>
                                                {{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                        <button
                            class="min-w-[90px] h-[38px] px-4 bg-gray-900 text-white rounded-md text-sm font-medium hover:bg-black transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800 active:bg-black">Save
                            changes</button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex gap-2 flex-wrap items-center">
                    <span
                        class="text-xs px-2 py-1 rounded {{ $todo->is_done ? 'bg-green-100 text-green-700' : 'bg-yellow-100' }}">{{ $todo->is_done ? 'Done' : 'Todo' }}</span>
                    <span
                        class="text-xs px-2 py-1 rounded-md {{ $todo->team_id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100' }}">{{ $todo->team_id ? 'Team: ' . $todo->team->name : 'Personal' }}</span>
                    <span class="text-xs text-gray-500">by {{ $todo->user->name ?? '—' }}</span>
                    <span class="text-xs text-gray-400">updated {{ $todo->updated_at->diffForHumans() }}</span>
                    <span class="flex-1"></span>
                    <form method="POST" action="/todos/{{ $todo->id }}/toggle">@csrf @method('PATCH')
                        <button
                            class="px-4 py-2 bg-white border rounded-md text-sm transition-colors hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 active:bg-gray-100">{{ $todo->is_done ? 'Mark pending' : 'Mark done' }}</button>
                    </form>
                    <form method="POST" action="/todos/{{ $todo->id }}" onsubmit="return confirm('Delete?')">@csrf
                        @method('DELETE')
                        <button class="px-4 py-2 text-red-600 text-sm">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
