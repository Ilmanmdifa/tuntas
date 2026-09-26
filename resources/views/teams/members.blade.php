<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Members — {{ $team->name }}</h2>
            <a href="/teams/{{ $team->id }}" class="text-sm underline shrink-0">← Team workspace</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-3 bg-green-50 text-green-700 rounded-md text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="p-3 bg-red-50 text-red-700 rounded-md text-sm">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="font-semibold text-gray-800 mb-2">Members ({{ $team->members->count() }})</h3>
                    <ul class="space-y-2">
                        @foreach ($team->members as $m)
                            <li class="flex items-center justify-between border rounded-md px-3 py-2 text-sm bg-white">
                                <span class="flex items-center gap-2 min-w-0">
                                    <span
                                        class="w-7 h-7 rounded-full bg-gray-800 text-white flex items-center justify-center text-xs font-medium shrink-0">{{ strtoupper(substr($m->name, 0, 1)) }}</span>
                                    <span class="truncate">{{ $m->email }}</span>
                                </span>
                                <span class="flex items-center gap-2 shrink-0">
                                    <span
                                        class="text-xs font-medium min-w-[64px] text-center px-2 py-1 rounded {{ $m->pivot->role === 'owner' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($m->pivot->role) }}</span>
                                    @can('update', $team)
                                        @if ($m->id !== $team->owner_id)
                                            <form method="POST"
                                                action="/teams/{{ $team->id }}/members/{{ $m->id }}"
                                                onsubmit="return confirm('Remove this member?')">
                                                @csrf @method('DELETE')
                                                <button class="text-xs text-red-600 underline px-2">Remove</button>
                                            </form>
                                        @endif
                                    @endcan
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            @can('update', $team)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-800">Invite member</h3>
                        <form method="POST" action="/teams/{{ $team->id }}/members" class="flex gap-2 mt-1">
                            @csrf
                            <input name="email" value="{{ old('email') }}" placeholder="Add a team member by email..."
                                required type="email"
                                class="flex-1 min-w-0 border border-gray-300 rounded-md px-3 py-2 text-sm h-[38px] focus:border-indigo-500 focus:ring-indigo-500">
                            <button
                                class="min-w-[90px] h-[38px] px-4 bg-gray-800 text-white rounded-md text-sm font-medium hover:bg-gray-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800 active:bg-gray-900 shrink-0">Invite</button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
