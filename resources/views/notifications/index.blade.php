<x-app-layout>
    <x-slot name="header">Notifications</x-slot>
    <div class="py-12 max-w-3xl mx-auto space-y-3">
        <div class="flex gap-2">
            <a href="/notifications?tab=all"
                class="{{ request('tab', 'all') === 'all' ? 'bg-gray-800 text-white' : 'bg-white border text-gray-700' }} px-4 py-2 rounded-md text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800">All</a>
            <a href="/notifications?tab=tasks"
                class="{{ request('tab') === 'tasks' ? 'bg-gray-800 text-white' : 'bg-white border text-gray-700' }} px-4 py-2 rounded-md text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800">Tasks</a>
            <a href="/notifications?tab=invites"
                class="{{ request('tab') === 'invites' ? 'bg-gray-800 text-white' : 'bg-white border text-gray-700' }} px-4 py-2 rounded-md text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800">Invites</a>
            <form method="POST" action="/notifications/read-all" class="ml-auto">@csrf<button class="px-4 py-2 rounded-md text-sm bg-white border text-gray-700">Mark all read</button></form>
        </div>
        @forelse ($items as $n)
            @if ($n->type === 'App\Notifications\TodoAssignedNotification')
                <div class="bg-white p-5 shadow-sm border rounded-lg flex flex-col sm:flex-row justify-between gap-3">
                    <div>
                        <div class="text-[11px] font-medium uppercase tracking-wide text-gray-400">Task assigned</div>
                        <div class="font-medium">{{ $n->data['title'] ?? 'Todo' }}</div>
                        <div class="text-sm text-gray-500">from {{ $n->data['assigned_by'] ?? '-' }} in
                            {{ $n->data['team_name'] ?? 'Personal' }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="flex gap-2 shrink-0 self-start sm:self-center">
                        @if (!empty($n->data['team_id']))
                            <a href="/teams/{{ $n->data['team_id'] }}"
                                class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm transition-colors hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800 active:bg-gray-900">Open →</a>
                        @else
                            <a href="/todos" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm transition-colors hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800 active:bg-gray-900">Open →</a>
                        @endif
                        <form method="POST" action="/notifications/{{ $n->id }}/read">@csrf<button
                                class="bg-white border text-gray-700 px-4 py-2 rounded-md text-sm transition-colors hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 active:bg-gray-100">Mark as Read</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="bg-white p-5 shadow-sm border rounded-lg flex flex-col sm:flex-row justify-between gap-3">
                    <div>
                        <div class="text-[11px] font-medium uppercase tracking-wide text-gray-400">Team invite</div>
                        <div class="font-medium">{{ $n->data['team_name'] ?? 'Team' }}</div>
                        <div class="text-sm text-gray-500">from {{ $n->data['invited_by'] ?? '-' }} —
                            {{ $n->data['email'] ?? '' }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="flex gap-2 shrink-0 self-start sm:self-center">
                        <form method="POST" action="/invites/{{ $n->data['invite_id'] }}/accept">@csrf<button
                                class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm transition-colors hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800 active:bg-gray-900">Accept</button></form>
                        <form method="POST" action="/invites/{{ $n->data['invite_id'] }}/decline">@csrf<button
                                class="bg-white border text-gray-700 px-4 py-2 rounded-md text-sm transition-colors hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 active:bg-gray-100">Decline</button>
                        </form>
                    </div>
                </div>
            @endif
        @empty
            <div class="text-center text-gray-500 py-12 border border-dashed rounded-lg">No notifications</div>
        @endforelse
        <div class="mt-4">{{ $items->links() }}</div>
    </div>
</x-app-layout>
