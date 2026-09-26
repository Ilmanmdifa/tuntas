<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard') }}</h2>
            <span class="text-sm text-gray-500">{{ auth()->user()->name }} · {{ auth()->user()->email }}</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Quick Add --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="POST" action="/todos" class="flex gap-2">
                    @csrf
                    <input name="title" placeholder="Add a new task..." required maxlength="255" class="flex-1 min-w-0 border border-gray-300 rounded-md px-3 py-2 text-sm h-[38px] focus:border-indigo-500 focus:ring-indigo-500">
                    <button class="min-w-[90px] h-[38px] px-4 bg-gray-900 text-white rounded-md text-sm font-medium hover:bg-black transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800 active:bg-black shrink-0">Add</button>
                </form>
            </div>

            {{-- Stats clickable + progress --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="{{ url('/todos') }}" class="bg-white shadow-sm sm:rounded-lg p-6 hover:shadow-md transition block">
                    <div class="text-sm text-gray-500">Total Tasks</div>
                    <div class="text-2xl font-bold">{{ $totalTodos }}</div>
                    <div class="text-xs text-gray-400">personal + team</div>
                </a>
                <a href="{{ url('/todos?filter=pending') }}" class="bg-white shadow-sm sm:rounded-lg p-6 hover:shadow-md transition block border-l-4 border-yellow-400">
                    <div class="text-sm text-gray-500">Pending</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $pendingTodos }}</div>
                    <div class="text-xs text-gray-400">belum selesai</div>
                </a>
                <a href="{{ url('/todos?filter=done') }}" class="bg-white shadow-sm sm:rounded-lg p-6 hover:shadow-md transition block border-l-4 border-green-500">
                    <div class="text-sm text-gray-500">Done</div>
                    <div class="text-2xl font-bold text-green-600">{{ $doneTodos }}</div>
                    <div class="text-xs text-gray-400">selesai</div>
                </a>
                <a href="{{ url('/teams') }}" class="bg-white shadow-sm sm:rounded-lg p-6 hover:shadow-md transition block">
                    <div class="text-sm text-gray-500">Teams</div>
                    <div class="text-2xl font-bold">{{ $teamsCount }}</div>
                    <div class="text-xs text-gray-400">joined · {{ $ownedCount }} owned</div>
                </a>
            </div>

            @if($totalTodos > 0)
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Progress</span><span>{{ $progress }}% done ({{ $doneTodos }}/{{ $totalTodos }})</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Recent Tasks --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold">Recent Tasks</h3>
                        <a href="{{ url('/todos') }}" class="text-sm underline">View all →</a>
                    </div>
                    <ul class="space-y-2">
                        @forelse($recentTodos as $todo)
                            <li class="flex items-center justify-between border rounded-md px-3 py-2 text-sm {{ $todo->is_done ? 'bg-gray-50 opacity-75' : 'bg-white' }}">
                                <a href="{{ url('/todos?q='.urlencode($todo->title)) }}" class="flex-1 truncate hover:underline {{ $todo->is_done ? 'line-through text-gray-500' : '' }}" title="{{ $todo->title }}">{{ $todo->title }}</a>
                                <span class="flex items-center gap-1 shrink-0 ml-2">
                                    <span class="w-5 h-5 border rounded flex items-center justify-center text-xs {{ $todo->is_done ? 'bg-green-600 text-white border-green-600' : 'bg-white' }}">{{ $todo->is_done ? '✓' : '' }}</span>
                                    <span class="text-xs font-medium min-w-[72px] text-center px-2 py-1 rounded-md {{ $todo->team_id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ $todo->team_id ? 'Team' : 'Personal' }}</span>
                                </span>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500">Belum ada todo — <a href="{{ url('/todos') }}" class="underline">buat di Tasks</a></li>
                        @endforelse
                    </ul>
                </div>

                {{-- My Teams --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold">My Teams</h3>
                        <a href="{{ url('/teams') }}" class="text-sm underline">View all →</a>
                    </div>
                    <ul class="space-y-2">
                        @forelse($myTeams as $team)
                            <li class="flex items-center justify-between border rounded-md px-3 py-2 text-sm bg-white">
                                <a href="/teams/{{ $team->id }}" class="underline flex-1 truncate">{{ $team->name }}</a>
                                <span class="text-xs font-medium min-w-[64px] text-center px-2 py-1 rounded {{ ($team->pivot->role ?? 'member') === 'owner' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($team->pivot->role ?? 'member') }}</span>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500">Belum join team — <a href="{{ url('/teams') }}" class="underline">buat team</a></li>
                        @endforelse
                    </ul>
                    <div class="mt-4 flex gap-2">
                        <a href="{{ url('/todos') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm transition-colors hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800 active:bg-gray-900">Go to Tasks</a>
                        <a href="{{ url('/teams') }}" class="px-4 py-2 bg-white border rounded-md text-sm transition-colors hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 active:bg-gray-100">Go to Teams</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
