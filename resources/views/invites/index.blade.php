<x-app-layout>
    <x-slot name="header">Invites ({{ $invites->total() }})</x-slot>
    <div class="py-12 max-w-3xl mx-auto space-y-3">
        @forelse($invites as $n)
            <div class="bg-white p-5 shadow-sm border rounded-lg flex flex-col sm:flex-row justify-between gap-3">
                <div>
                    <div class="font-medium">{{ $n->data['team_name'] ?? 'Team' }}</div>
                    <div class="text-sm text-gray-500">from {{ $n->data['invited_by'] ?? '-' }} — {{ $n->data['email'] ?? '' }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                </div>
                <div class="flex gap-2 shrink-0 self-start sm:self-center">
                    <form method="POST" action="/invites/{{ $n->data['invite_id'] }}/accept">@csrf<button class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm">Accept</button></form>
                    <form method="POST" action="/invites/{{ $n->data['invite_id'] }}/decline">@csrf<button class="bg-white border text-gray-700 px-4 py-2 rounded-md text-sm">Decline</button></form>
                </div>
            </div>
        @empty <div class="text-center text-gray-500 py-12 border border-dashed rounded-lg">No invites</div>
        @endforelse
        <div class="mt-4">{{ $invites->links() }}</div>
    </div>
</x-app-layout>
