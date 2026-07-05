<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <x-alert type="success">{{ session('success') }}</x-alert>
            @endif

            @if(session('error'))
                <x-alert type="danger">{{ session('error') }}</x-alert>
            @endif

            @php
                $user = auth()->user();
                $isNationalPresident = $user?->hasRole('national-president');

                if ($isNationalPresident) {
                    $regionCount = \App\Models\Region::count();
                    $clubCount = \App\Models\Club::count();
                    $positionCount = \App\Models\Position::count();
                    $memberCount = \App\Models\Member::count();
                } else {
                    // Club president — scoped to their club
                    $clubId = $user->club_id;
                    $memberCount = $clubId ? \App\Models\Member::where('club_id', $clubId)->count() : 0;
                }
            @endphp

            <!-- Stats Cards -->
            @if($isNationalPresident)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="shrink-0 bg-indigo-100 dark:bg-indigo-900/50 rounded-md p-3">
                                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Regions') }}</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $regionCount }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="shrink-0 bg-green-100 dark:bg-green-900/50 rounded-md p-3">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Clubs') }}</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $clubCount }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="shrink-0 bg-amber-100 dark:bg-amber-900/50 rounded-md p-3">
                                    <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Positions') }}</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $positionCount }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="shrink-0 bg-purple-100 dark:bg-purple-900/50 rounded-md p-3">
                                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Members') }}</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $memberCount }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @php
                    $club = $user->club_id ? \App\Models\Club::with('region')->find($user->club_id) : null;
                    $positionsByClub = $club ? \App\Models\Member::where('club_id', $club->id)
                        ->with('position')
                        ->get()
                        ->groupBy(fn($m) => $m->position?->name ?? 'Unassigned')
                        ->map(fn($g) => $g->count()) : collect();
                @endphp

                <!-- Club Info Header -->
                @if($club)
                    <div class="mb-8">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center gap-4">
                                    <div class="shrink-0 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl p-3">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Your Club') }}</p>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $club->name }}</h3>
                                        @if($club->region)
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $club->region->name }}</p>
                                        @endif
                                    </div>
                                    <div class="ml-auto text-right">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Members') }}</p>
                                        <p class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ $memberCount }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Position Breakdown -->
                    @if($positionsByClub->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                            @foreach($positionsByClub as $positionName => $count)
                                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">{{ $positionName }}</span>
                                            <span class="inline-flex items-center justify-center size-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400 font-bold text-sm">{{ $count }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            @endif

            @if($isNationalPresident)
                <!-- Quick Actions (NP only) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Quick Actions') }}</h3>
                            <div class="space-y-3">
                                <a href="{{ route('admin.regions.create') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <span class="shrink-0 bg-indigo-50 dark:bg-indigo-900/30 rounded-md p-2">
                                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Create Region') }}</span>
                                </a>

                                <a href="{{ route('admin.clubs.create') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <span class="shrink-0 bg-green-50 dark:bg-green-900/30 rounded-md p-2">
                                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Create Club') }}</span>
                                </a>

                                <a href="{{ route('admin.positions.create') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <span class="shrink-0 bg-amber-50 dark:bg-amber-900/30 rounded-md p-2">
                                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Create Position') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Manage') }}</h3>
                            <div class="space-y-3">
                                <a href="{{ route('admin.regions.index') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <span class="shrink-0 bg-indigo-50 dark:bg-indigo-900/30 rounded-md p-2">
                                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Manage Regions') }}</span>
                                </a>

                                <a href="{{ route('admin.clubs.index') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <span class="shrink-0 bg-green-50 dark:bg-green-900/30 rounded-md p-2">
                                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Manage Clubs') }}</span>
                                </a>

                                <a href="{{ route('admin.positions.index') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <span class="shrink-0 bg-amber-50 dark:bg-amber-900/30 rounded-md p-2">
                                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Manage Positions') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Club President quick links -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Quick Links') }}</h3>
                            <div class="space-y-3">
                                <a href="{{ route('admin.members.index') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <span class="shrink-0 bg-purple-50 dark:bg-purple-900/30 rounded-md p-2">
                                        <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Manage Members') }}</span>
                                </a>
                                <a href="{{ route('admin.members.directory') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <span class="shrink-0 bg-indigo-50 dark:bg-indigo-900/30 rounded-md p-2">
                                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Member Directory') }}</span>
                                </a>
                                <a href="{{ route('admin.members.create') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <span class="shrink-0 bg-green-50 dark:bg-green-900/30 rounded-md p-2">
                                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Add Member') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recent Members -->
            @php
                $recentMembersQuery = \App\Models\Member::with(['club', 'position']);

                if (!$isNationalPresident && $user->club_id) {
                    $recentMembersQuery->where('club_id', $user->club_id);
                }

                $recentMembers = $recentMembersQuery->latest()->take(5)->get();
            @endphp

            @if($recentMembers->isNotEmpty())
                <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Recent Members') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800/50">
                                    <tr>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Name') }}</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Club') }}</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Position') }}</th>
                                        <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Profile') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($recentMembers as $member)
                                        <tr>
                                            <td class="px-3 py-3.5 text-sm text-gray-900 dark:text-gray-100">{{ $member->name }}</td>
                                            <td class="px-3 py-3.5 text-sm text-gray-700 dark:text-gray-300">{{ optional($member->club)->name }}</td>
                                            <td class="px-3 py-3.5 text-sm text-gray-700 dark:text-gray-300">{{ optional($member->position)->name }}</td>
                                            <td class="px-3 py-3.5 text-sm text-right">
                                                <a href="{{ route('member.profile', $member->slug) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-md text-xs font-semibold hover:bg-indigo-100 dark:hover:bg-indigo-900/50">
                                                    {{ __('View') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
