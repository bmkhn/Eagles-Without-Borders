@php
    $user = auth()->user();
    $isNp = $user?->hasRole('national-president');
    $isAdmin = $user?->hasAnyRole(['national-president', 'club-president']);
@endphp

<aside
    x-data
    x-cloak
    :class="sidebarCollapsed ? 'hidden lg:block lg:w-16 lg:flex-shrink-0' : 'lg:block lg:w-64 lg:flex-shrink-0'"
    class="hidden lg:block lg:flex-shrink-0 transition-all duration-200"
>
    <div class="flex h-full flex-col border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
        <!-- Branding -->
        <div class="flex items-center gap-2 px-4 py-4 border-b border-gray-200 dark:border-gray-700">
            <img src="{{ asset('images/logo.png') }}" alt="" class="h-7 w-auto" :class="sidebarCollapsed ? 'mx-auto' : ''">
            <span x-show="!sidebarCollapsed" class="text-gray-500 dark:text-gray-400 text-xs font-medium">Admin</span>
        </div>

        <!-- Nav -->
        <nav class="px-2 pt-3 overflow-y-auto">
            <ul class="space-y-1">
                <!-- Dashboard -->
                <li>
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="group flex items-center rounded-md px-2 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 dark:bg-gray-800' : '' }}"
                        title="{{ __('Dashboard') }}"
                        :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                    >
                        <svg class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" class="ml-3">{{ __('Dashboard') }}</span>
                    </a>
                </li>

                @if($isNp)
                    <li x-show="!sidebarCollapsed" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <span class="px-2 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Admin') }}</span>
                    </li>

                    <li>
                        <a
                            href="{{ route('admin.regions.index') }}"
                            class="group flex items-center rounded-md px-2 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.regions.*') ? 'bg-gray-100 dark:bg-gray-800' : '' }}"
                            title="{{ __('Regions') }}"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                        >
                            <svg class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" class="ml-3">{{ __('Regions') }}</span>
                        </a>
                    </li>

                    <li>
                        <a
                            href="{{ route('admin.clubs.index') }}"
                            class="group flex items-center rounded-md px-2 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.clubs.*') ? 'bg-gray-100 dark:bg-gray-800' : '' }}"
                            title="{{ __('Clubs') }}"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                        >
                            <svg class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" class="ml-3">{{ __('Clubs') }}</span>
                        </a>
                    </li>

                    <li>
                        <a
                            href="{{ route('admin.positions.index') }}"
                            class="group flex items-center rounded-md px-2 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.positions.*') ? 'bg-gray-100 dark:bg-gray-800' : '' }}"
                            title="{{ __('Positions') }}"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                        >
                            <svg class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" class="ml-3">{{ __('Positions') }}</span>
                        </a>
                    </li>
                @endif

                <!-- Members Section -->
                @if($isAdmin)
                    @if(!$isNp)
                        <li x-show="!sidebarCollapsed" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <span class="px-2 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Club Management') }}</span>
                        </li>
                    @endif

                    <li>
                        <a
                            href="{{ route('admin.members.index') }}"
                            class="group flex items-center rounded-md px-2 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.members.index') ? 'bg-gray-100 dark:bg-gray-800' : '' }}"
                            title="{{ __('Members') }}"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                        >
                            <svg class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" class="ml-3">{{ __('Members') }}</span>
                        </a>
                    </li>

                    <li>
                        <a
                            href="{{ route('admin.members.directory') }}"
                            class="group flex items-center rounded-md px-2 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.members.directory') ? 'bg-gray-100 dark:bg-gray-800' : '' }}"
                            title="{{ __('Member Directory') }}"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                        >
                            <svg class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" class="ml-3">{{ __('Member Directory') }}</span>
                        </a>
                    </li>
                @endif

                <!-- Public -->
                <li x-show="!sidebarCollapsed" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <span class="px-2 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Public') }}</span>
                </li>

                <li>
                    <a
                        href="{{ route('member.directory') }}"
                        class="group flex items-center rounded-md px-2 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('member.directory') ? 'bg-gray-100 dark:bg-gray-800' : '' }}"
                        title="{{ __('Member Directory') }}"
                        :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                    >
                        <svg class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" class="ml-3">{{ __('Member Directory') }}</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Footer -->
        <div class="border-t border-gray-200 dark:border-gray-700 px-3 py-3">
            @if($user)
                <!-- Dark Mode Toggle -->
                <button
                    @click="darkMode = !darkMode"
                    class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-gray-200 transition mb-1"
                    :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                    title="{{ __('Toggle dark mode') }}"
                >
                    <svg x-show="!darkMode" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-2">{{ __('Dark Mode') }}</span>
                </button>

                <!-- User info and Sign Out -->
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <div x-show="!sidebarCollapsed" class="mb-2 text-xs text-gray-500 dark:text-gray-400 truncate px-1">
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $user->name }}</span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                            title="{{ __('Sign Out') }}"
                        >
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span x-show="!sidebarCollapsed">{{ __('Sign Out') }}</span>
                        </button>
                    </form>
                </div>
            @else
                <div class="text-xs text-gray-500 dark:text-gray-400 text-center">Not signed in</div>
            @endif
        </div>
    </div>
</aside>
