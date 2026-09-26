<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="url('/todos')" :active="request()->is('todos*')">
                        {{ __('Tasks') }}
                    </x-nav-link>
                    <x-nav-link :href="url('/teams')" :active="request()->is('teams*')">
                        {{ __('Teams') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Bell Hybrid Dropdown -->
                <x-dropdown align="right" width="96">
                    <x-slot name="trigger">
                        <button
                            class="relative inline-flex items-center justify-center w-10 h-10 text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                            @if (auth()->user()->unreadNotifications->count() > 0)
                                <span
                                    class="absolute bg-red-600 text-white text-[11px] leading-none min-w-[18px] h-[18px] flex items-center justify-center rounded-full px-1.5"
                                    style="top: 2px; right: 2px;">{{ auth()->user()->unreadNotifications->count() }}</span>
                            @endif
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        @php
                            $notifs = auth()->user()->unreadNotifications;
                            $tasks = $notifs->where('type', 'App\Notifications\TodoAssignedNotification')->take(2);
                            $invites = $notifs->where('type', 'App\Notifications\TeamInviteNotification')->take(2);
                        @endphp
                        <div x-data="{ tab: 'all' }">
                            <div class="px-4 py-2 text-xs font-medium text-gray-500">Notifications ({{ $notifs->count() }})</div>
                            <div class="flex gap-1 px-4 pb-2">
                                <button @click.stop="tab = 'all'" :class="tab === 'all' ? 'bg-gray-800 text-white' : 'text-gray-600'" class="px-3 py-1 rounded-md text-xs">All ({{ $notifs->count() }})</button>
                                <button @click.stop="tab = 'tasks'" :class="tab === 'tasks' ? 'bg-gray-800 text-white' : 'text-gray-600'" class="px-3 py-1 rounded-md text-xs">Tasks ({{ $notifs->where('type', 'App\Notifications\TodoAssignedNotification')->count() }})</button>
                                <button @click.stop="tab = 'invites'" :class="tab === 'invites' ? 'bg-gray-800 text-white' : 'text-gray-600'" class="px-3 py-1 rounded-md text-xs">Invites ({{ $notifs->where('type', 'App\Notifications\TeamInviteNotification')->count() }})</button>
                            </div>

                            <div x-show="tab === 'all' || tab === 'tasks'">
                                @if ($tasks->isNotEmpty())
                                    <div class="px-4 pt-2 text-[11px] font-medium uppercase tracking-wide text-gray-400">Tasks</div>
                                    @foreach ($tasks as $n)
                                        <div class="px-4 py-3 border-t flex justify-between items-start gap-4">
                                            <div class="min-w-0 flex-1">
                                                <div class="text-[11px] font-medium uppercase tracking-wide text-gray-400">Task assigned</div>
                                                <div class="text-sm font-medium text-gray-900 truncate">{{ $n->data['title'] ?? 'Todo' }}</div>
                                                <div class="text-xs text-gray-500">from {{ $n->data['assigned_by'] ?? '-' }} in {{ $n->data['team_name'] ?? 'Personal' }} · {{ $n->created_at->diffForHumans() }}</div>
                                            </div>
                                            <div class="flex flex-col gap-2 shrink-0 w-[110px]">
                                                @if (!empty($n->data['team_id']))
                                                    <a href="/teams/{{ $n->data['team_id'] }}" class="bg-gray-800 text-white text-xs px-3 py-1.5 rounded-md whitespace-nowrap text-center w-full transition-colors hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800 active:bg-gray-900">Open →</a>
                                                @else
                                                    <a href="/todos" class="bg-gray-800 text-white text-xs px-3 py-1.5 rounded-md whitespace-nowrap text-center w-full transition-colors hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800 active:bg-gray-900">Open →</a>
                                                @endif
                                                <form method="POST" action="/notifications/{{ $n->id }}/read">@csrf<button class="bg-white border text-gray-700 text-xs px-3 py-1.5 rounded-md whitespace-nowrap w-full transition-colors hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 active:bg-gray-100">Mark as Read</button></form>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <div x-show="tab === 'all' || tab === 'invites'">
                                @if ($invites->isNotEmpty())
                                    <div class="px-4 pt-2 text-[11px] font-medium uppercase tracking-wide text-gray-400 border-t">Invites</div>
                                    @foreach ($invites as $n)
                                        <div class="px-4 py-3 border-t flex justify-between items-start gap-4">
                                            <div class="min-w-0 flex-1">
                                                <div class="text-[11px] font-medium uppercase tracking-wide text-gray-400">Team invite</div>
                                                <div class="text-sm font-medium text-gray-900 truncate">{{ $n->data['team_name'] ?? 'Team' }}</div>
                                                <div class="text-xs text-gray-500">by {{ $n->data['invited_by'] ?? '-' }} · {{ $n->created_at->diffForHumans() }}</div>
                                            </div>
                                            <div class="flex flex-col gap-2 shrink-0 w-[110px]">
                                                <form method="POST" action="/invites/{{ $n->data['invite_id'] }}/accept">@csrf<button class="bg-gray-800 text-white text-xs px-3 py-1.5 rounded-md whitespace-nowrap w-full transition-colors hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800 active:bg-gray-900">Accept</button></form>
                                                <form method="POST" action="/invites/{{ $n->data['invite_id'] }}/decline">@csrf<button class="bg-white border text-gray-700 text-xs px-3 py-1.5 rounded-md whitespace-nowrap w-full transition-colors hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 active:bg-gray-100">Decline</button></form>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            @if ($notifs->isEmpty())
                                <div class="px-4 py-6 text-center">
                                    <svg class="w-8 h-8 mx-auto text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <div class="mt-2 text-sm font-medium text-gray-800">You're all caught up</div>
                                    <div class="text-xs text-gray-500">New tasks and invites will appear here</div>
                                </div>
                            @endif
                            <div class="border-t px-4 py-2 flex justify-between items-center">
                                <a href="{{ url('/notifications') }}" class="text-sm text-blue-600 hover:underline">View all →</a>
                                <form method="POST" action="/notifications/read-all">@csrf<button class="text-xs text-gray-500 underline">Mark all read</button></form>
                            </div>
                        </div>
                    </x-slot>
                </x-dropdown>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="url('/todos')" :active="request()->is('todos*')">
                {{ __('Tasks') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="url('/teams')" :active="request()->is('teams*')">
                {{ __('Teams') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
