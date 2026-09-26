<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tasks ({{ $todos->total() }}) — {{ auth()->user()->name }}
            </h2>
            <a href="{{ route('dashboard') }}" class="text-sm underline">Dashboard</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if (session('status'))
                        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-md text-sm">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-md text-sm">
                            {{ $errors->first('title') ?: $errors->first() }}</div>
                    @endif

                    {{-- Search — subtle --}}
                    <div class="mb-2">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-widest">Cari</label>
                            @if (request('filter'))
                                <a href="{{ url('/todos' . (request('q') ? '?q=' . urlencode(request('q')) : '')) }}"
                                    class="text-xs px-2 py-1 bg-yellow-100 text-yellow-800 rounded">Filter:
                                    {{ ucfirst(request('filter')) }} ✕</a>
                            @endif
                        </div>
                        <form method="GET" action="/todos" class="flex gap-2 mt-1">
                            @if (request('filter'))
                                <input type="hidden" name="filter" value="{{ request('filter') }}">
                            @endif
                            <input name="q" value="{{ request('q') }}" placeholder="Search title..."
                                class="flex-1 min-w-0 border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-sm h-[38px] focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                            <button
                                class="min-w-[90px] h-[38px] px-4 bg-white border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 active:bg-gray-100 shrink-0">Search</button>
                            @if (request('q') || request('filter'))
                                <a href="/todos" class="px-3 py-2 text-sm underline self-center">Clear</a>
                            @endif
                        </form>
                    </div>

                    {{-- Add — primary --}}
                    <div class="mb-2">
                        <h3 class="font-semibold text-gray-800">Tambah tugas baru</h3>
                        <form method="POST" action="/todos" class="mt-1 space-y-2">
                            @csrf
                            <input name="title" value="{{ old('title') }}" placeholder="Add a new task..."
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm h-[38px] focus:border-indigo-500 focus:ring-indigo-500"
                                required maxlength="255">
                            <div class="flex gap-2 flex-wrap items-end">
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-widest">Priority</label>
                                    <select name="priority" aria-label="Priority"
                                        class="mt-1 border border-gray-300 rounded-md px-3 py-1.5 text-sm min-w-[120px] h-[38px] bg-white">
                                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low
                                        </option>
                                        <option value="medium"
                                            {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-widest">Due date</label>
                                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                                        aria-label="Due date" title="Due date (optional)"
                                        class="mt-1 border border-gray-300 rounded-md px-3 py-1.5 text-sm min-w-[140px] h-[38px] bg-white">
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-widest">Description</label>
                                <textarea name="description" rows="2" placeholder="Description (optional)..."
                                    class="mt-1 w-full border border-gray-300 bg-white rounded-md px-3 py-2 text-sm">{{ old('description') }}</textarea>
                            </div>
                            <div class="flex justify-end">
                                <button
                                    class="w-full sm:w-auto min-w-[90px] h-[38px] px-4 bg-gray-900 text-white rounded-md text-sm font-medium hover:bg-black transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800 active:bg-black">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex gap-2 mb-3 flex-wrap">
                        <a href="/todos"
                            class="px-3 py-1.5 rounded-md text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800 {{ !request('scope') ? 'bg-gray-900 text-white' : 'bg-white border' }}">All</a>
                        <a href="/todos?scope=my"
                            class="px-3 py-1.5 rounded-md text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800 {{ request('scope') === 'my' ? 'bg-gray-900 text-white' : 'bg-white border' }}">My
                            Work</a>
                        <a href="/todos?scope=team"
                            class="px-3 py-1.5 rounded-md text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800 {{ request('scope') === 'team' ? 'bg-gray-900 text-white' : 'bg-white border' }}">Team
                            Board</a>
                    </div>
                    <div class="flex gap-2 mb-3 flex-wrap">
                        @php $qs = fn($filter) => http_build_query(array_filter(['scope'=>request('scope'),'q'=>request('q'),'filter'=>$filter])) @endphp
                        <a href="/todos?{{ $qs('pending') }}"
                            class="px-3 py-1.5 rounded-md text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800 {{ !request('filter') || request('filter') === 'pending' ? 'bg-gray-900 text-white' : 'bg-white border' }}">Active</a>
                        <a href="/todos?{{ $qs('done') }}"
                            class="px-3 py-1.5 rounded-md text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800 {{ request('filter') === 'done' ? 'bg-gray-900 text-white' : 'bg-white border' }}">Archived</a>
                        <a href="/todos?{{ $qs('overdue') }}"
                            class="px-3 py-1.5 rounded-md text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800 {{ request('filter') === 'overdue' ? 'bg-gray-900 text-white' : 'bg-white border' }}">Overdue</a>
                    </div>
                    <ul class="space-y-2">
                        @forelse ($todos as $todo)
                            <li
                                class="border rounded-md p-3 space-y-2 {{ $todo->is_done ? 'bg-gray-50' : 'bg-white' }}">
                                <!-- Baris 1: input full -->
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="/todos/{{ $todo->id }}/toggle">@csrf
                                        @method('PATCH')
                                        <button
                                            class="w-6 h-6 border rounded flex items-center justify-center shrink-0 {{ $todo->is_done ? 'bg-green-600 text-white border-green-600' : 'bg-white hover:bg-gray-50' }}">{{ $todo->is_done ? '✓' : '' }}</button>
                                    </form>
                                    <form method="POST" action="/todos/{{ $todo->id }}"
                                        class="flex flex-1 gap-2 min-w-0">
                                        @csrf @method('PATCH')
                                        <input name="title" value="{{ old('title', $todo->title) }}"
                                            class="flex-1 min-w-0 border rounded-md px-2 py-1.5 text-sm {{ $todo->is_done ? 'line-through' : '' }}"
                                            required maxlength="255">
                                        <button class="text-sm underline px-2 shrink-0">Save</button>
                                    </form>
                                    </form>
                                </div>
                                <!-- Baris 2: meta tidak ganggu input -->
                                <div class="flex gap-2 ml-8 flex-wrap">
                                    <span
                                        class="text-xs px-2 py-1 rounded-md {{ $todo->team_id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100' }}">{{ $todo->team_id ? 'Team' : 'Personal' }}</span>
                                    @if ($todo->assigned_to)
                                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-md">→
                                            {{ $todo->assignedTo->name }}</span>
                                    @endif
                                    <span
                                        class="text-xs px-2 py-1 rounded {{ $todo->is_done ? 'bg-green-100 text-green-700' : 'bg-yellow-100' }}">{{ $todo->is_done ? 'Done' : 'Todo' }}</span>
                                    <span
                                        class="text-xs px-2 py-1 rounded {{ $todo->priority === 'high' ? 'bg-red-100 text-red-700' : ($todo->priority === 'low' ? 'bg-gray-100 text-gray-600' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($todo->priority ?? 'medium') }}</span>
                                    @if ($todo->due_date)
                                        @if (!$todo->is_done && $todo->due_date < now()->toDateString())
                                            <span class="text-xs px-2 py-1 rounded bg-red-100 text-red-700">Overdue {{ \Carbon\Carbon::parse($todo->due_date)->format('d M Y') }}</span>
                                        @else
                                            <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600">Due {{ \Carbon\Carbon::parse($todo->due_date)->format('d M Y') }}</span>
                                        @endif
                                    @endif

                                </div>
                                <!-- Baris 3: aksi -->
                                <div class="flex justify-end items-center gap-3 ml-8">
                                    <a href="/todos/{{ $todo->id }}" class="text-xs text-gray-700 underline inline-flex items-center h-5">Details</a>
                                    <form method="POST" action="/todos/{{ $todo->id }}" onsubmit="return confirm('Delete?')" class="flex items-center">@csrf @method('DELETE')
                                        <button class="text-xs text-red-600 underline inline-flex items-center h-5">Delete</button>
                                    </form>
                                </div>
                            </li>
                        @empty
                            <li class="border-2 border-dashed rounded-lg p-8 text-center">
                                <div class="text-4xl mb-2">📝</div>
                                <div class="font-medium text-gray-800">Belum ada todo</div>
                                <div class="text-sm text-gray-500 mb-3">Mulai dengan tugas pertama — personal atau
                                    team.
                                </div>
                                <div class="flex justify-center gap-2">
                                    <a href="#top"
                                        onclick="document.querySelector('input[name=title]').focus(); return false;"
                                        class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm">Buat todo</a>
                                    <a href="{{ url('/teams') }}"
                                        class="px-4 py-2 bg-white border rounded-md text-sm">Buat team</a>
                                </div>
                                @if (request('q'))
                                    <div class="text-xs text-gray-400 mt-2">Filter "{{ request('q') }}" tidak ada
                                        hasil — <a href="/todos" class="underline">clear</a></div>
                                @endif
                            </li>
                        @endforelse
                    </ul>

                    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="text-sm text-gray-500">
                            @if ($todos->total() > 0)
                                @if (request('q'))
                                    {{ $todos->total() }} results for "{{ request('q') }}" — Page
                                    {{ $todos->currentPage() }} of {{ $todos->lastPage() }}
                                @else
                                    {{ $todos->total() }} todos — Page {{ $todos->currentPage() }} of
                                    {{ $todos->lastPage() }}
                                @endif
                            @endif
                        </div>
                        <div>{{ $todos->links() }}</div>
                    </div>

                    <p class="mt-6 text-sm text-gray-500"><a href="/" class="underline">← Home</a> · <a
                            href="/todos" class="underline">Refresh</a></p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
