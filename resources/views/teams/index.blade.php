<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Teams ({{ $owned->total() }} owned · {{ $teams->total() }} member)</h2>
            <a href="{{ url('/todos') }}" class="text-sm underline">Tasks</a>
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
                        <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-md text-sm">{{ $errors->first() }}</div>
                    @endif

                    <h3 class="font-semibold text-gray-800">Buat tim baru</h3>
                    <form method="POST" action="/teams" class="flex gap-2 mt-1">
                        @csrf
                        <input name="name" value="{{ old('name') }}" placeholder="Add a new team..." required maxlength="255" class="flex-1 min-w-0 border border-gray-300 rounded-md px-3 py-2 text-sm h-[38px] focus:border-indigo-500 focus:ring-indigo-500">
                        <button class="min-w-[90px] h-[38px] px-4 bg-gray-900 text-white rounded-md text-sm font-medium hover:bg-black transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800 active:bg-black shrink-0">Create</button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">Owned <span class="text-xs font-normal text-gray-500">({{ $owned->total() }})</span></h3>
                            <div class="space-y-2">
                                @forelse($owned as $team)
                                    <a href="/teams/{{ $team->id }}" class="flex items-center justify-between border rounded-md px-3 py-2 bg-white hover:bg-gray-50 hover:shadow-sm transition">
                                        <span class="text-sm font-medium flex-1 truncate">{{ $team->name }}</span>
                                        <span class="text-xs font-medium min-w-[56px] text-center bg-blue-100 text-blue-700 px-2 py-1 rounded">Owner</span>
                                    </a>
                                @empty
                                    <div class="border-2 border-dashed rounded-lg p-6 text-center">
                                        <div class="text-sm text-gray-500">No owned teams yet</div>
                                        <div class="text-xs text-gray-400">Create one above</div>
                                    </div>
                                @endforelse
                            </div>
                            @if ($owned->hasPages())
                                <div class="mt-3">{{ $owned->links() }}</div>
                            @endif
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">Member <span class="text-xs font-normal text-gray-500">({{ $teams->total() }})</span></h3>
                            <div class="space-y-2">
                                @forelse($teams as $team)
                                    <a href="/teams/{{ $team->id }}" class="flex items-center justify-between border rounded-md px-3 py-2 bg-white hover:bg-gray-50 hover:shadow-sm transition">
                                        <span class="text-sm font-medium flex-1 truncate">{{ $team->name }}</span>
                                        <span class="text-xs font-medium min-w-[64px] text-center bg-gray-100 text-gray-600 px-2 py-1 rounded">Member</span>
                                    </a>
                                @empty
                                    <div class="border-2 border-dashed rounded-lg p-6 text-center">
                                        <div class="text-sm text-gray-500">No teams as member</div>
                                        <div class="text-xs text-gray-400">Ask an owner to invite you</div>
                                    </div>
                                @endforelse
                            </div>
                            @if ($teams->hasPages())
                                <div class="mt-3">{{ $teams->links() }}</div>
                            @endif
                        </div>
                    </div>

                    <p class="mt-6 text-sm text-gray-500"><a href="/todos" class="underline">← Tasks</a> · <a href="/dashboard" class="underline">Dashboard</a></p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
