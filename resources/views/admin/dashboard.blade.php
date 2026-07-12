<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
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
                $isSuperAdmin = $user?->hasRole('super-admin');
                $isNationalAdmin = $user?->hasRole('national-admin');
                $isRegionalAdmin = $user?->hasRole('regional-admin');
                $isClubAdmin = $user?->hasRole('club-admin');
                $isNationLevel = $isSuperAdmin || $isNationalAdmin;

                if ($isNationLevel) {
                    $regionCount = \App\Models\Region::count();
                    $clubCount = \App\Models\Club::count();
                    $positionCount = \App\Models\Position::count();
                    $memberCount = \App\Models\Member::count();

                    $clubsWithStatus = \App\Models\Club::with('region')
                        ->withCount([
                            'members as active_count' => fn($q) => $q->where('status', 'active'),
                            'members as inactive_count' => fn($q) => $q->where('status', 'inactive'),
                        ])
                        ->orderBy('name')
                        ->get()
                        ->groupBy(fn($c) => $c->region?->name ?? 'Unassigned');

                    $positionsAll = \App\Models\Member::query()
                        ->select('position_id')
                        ->selectRaw('COUNT(*) as count')
                        ->with('position')
                        ->groupBy('position_id')
                        ->get()
                        ->map(fn($m) => [
                            'id' => $m->position_id,
                            'name' => $m->position?->name ?? 'Unassigned',
                            'count' => (int) $m->count,
                        ])
                        ->sortByDesc('count');
                } elseif ($isRegionalAdmin) {
                    // Regional admin: scoped to their region
                    $region = $user->region_id ? \App\Models\Region::find($user->region_id) : null;
                    $regionClubIds = $region?->clubs()->pluck('id') ?? collect();
                    $clubCount = $regionClubIds->count();
                    $memberCount = \App\Models\Member::whereIn('club_id', $regionClubIds)->count();
                } else {
                    // Club admin: scoped to their club
                    $clubId = $user->club_id;
                    $memberCount = $clubId ? \App\Models\Member::where('club_id', $clubId)->count() : 0;
                }
            @endphp

            <!-- Stats Cards -->
            @if($isNationLevel)
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
            @elseif($isRegionalAdmin)
                @php
                    $region = $user->region_id ? \App\Models\Region::withCount('clubs')->find($user->region_id) : null;
                @endphp

                @if($region)
                    <div class="mb-8">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center gap-4">
                                    <div class="shrink-0 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl p-3">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Your Region') }}</p>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $region->name }}</h3>
                                    </div>
                                    <div class="ml-auto text-right">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Clubs') }}</p>
                                        <p class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ $region->clubs_count }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                @php
                    $club = $user->club_id ? \App\Models\Club::with('region')->find($user->club_id) : null;
                @endphp

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
                @endif
            @endif

            <!-- Club Membership Status -->
            @if($isNationLevel && $clubsWithStatus->isNotEmpty())
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-5 flex items-center gap-2">
                        <svg class="size-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ __('Club Membership Status') }}
                    </h3>

                    @foreach($clubsWithStatus as $regionName => $regionClubs)
                        <div class="mb-6 last:mb-0">
                            <div class="flex items-center gap-2 mb-3 px-1">
                                <div class="size-2 rounded-full bg-amber-500"></div>
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">{{ $regionName }}</h4>
                                <span class="text-xs text-gray-400">({{ $regionClubs->count() }} {{ Str::plural('club', $regionClubs->count()) }})</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($regionClubs as $club)
                                    @php
                                        $total = $club->active_count + $club->inactive_count;
                                        $activePct = $total > 0 ? round(($club->active_count / $total) * 100) : 0;
                                        $inactivePct = $total > 0 ? round(($club->inactive_count / $total) * 100) : 0;
                                    @endphp
                                    <a
                                        href="{{ route('admin.members.index', ['club_id' => $club->id]) }}"
                                        class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:border-amber-400/50 dark:hover:border-amber-500/50 hover:shadow-md hover:shadow-amber-500/5 transition-all duration-200"
                                    >
                                        <div class="flex items-center justify-between mb-3">
                                            <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors truncate">
                                                {{ $club->name }}
                                            </h5>
                                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 ml-2 shrink-0">{{ $total }} {{ Str::plural('member', $total) }}</span>
                                        </div>

                                        <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden flex">
                                            <div class="h-full bg-emerald-500 rounded-l-full transition-all duration-500" style="width: {{ $activePct }}%"></div>
                                            <div class="h-full bg-red-500 rounded-r-full transition-all duration-500" style="width: {{ $inactivePct }}%"></div>
                                        </div>

                                        <div class="flex items-center justify-between mt-2.5">
                                            <div class="flex items-center gap-1.5">
                                                <span class="size-2 rounded-full bg-emerald-500"></span>
                                                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">{{ $club->active_count }} {{ __('Active') }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="size-2 rounded-full bg-red-500"></span>
                                                <span class="text-xs font-medium text-red-600 dark:text-red-400">{{ $club->inactive_count }} {{ __('Inactive') }}</span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Position Counts -->
                @if($positionsAll->isNotEmpty())
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-5 flex items-center gap-2">
                            <svg class="size-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ __('Position Counts') }}
                        </h3>

                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                            @foreach($positionsAll as $position)
                                <a
                                    href="{{ $position['id'] ? route('admin.members.index', ['position_id' => $position['id']]) : route('admin.members.index') }}"
                                    class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center hover:border-amber-400/50 dark:hover:border-amber-500/50 hover:shadow-md hover:shadow-amber-500/5 transition-all duration-200 group"
                                >
                                    <p class="text-2xl font-black text-gray-900 dark:text-gray-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ $position['count'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ $position['name'] }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @elseif($isRegionalAdmin && isset($region) && $region)
                @php
                    $regionClubIds = $region->clubs()->pluck('id');
                    $activeCount = \App\Models\Member::whereIn('club_id', $regionClubIds)->where('status', 'active')->count();
                    $inactiveCount = \App\Models\Member::whereIn('club_id', $regionClubIds)->where('status', 'inactive')->count();
                    $regionTotal = $activeCount + $inactiveCount;
                    $regionActivePct = $regionTotal > 0 ? round(($activeCount / $regionTotal) * 100) : 0;
                    $regionInactivePct = $regionTotal > 0 ? round(($inactiveCount / $regionTotal) * 100) : 0;

                    $raPositions = \App\Models\Member::whereIn('club_id', $regionClubIds)
                        ->select('position_id')
                        ->selectRaw('COUNT(*) as count')
                        ->with('position')
                        ->groupBy('position_id')
                        ->get()
                        ->map(fn($m) => [
                            'id' => $m->position_id,
                            'name' => $m->position?->name ?? 'Unassigned',
                            'count' => (int) $m->count,
                        ])
                        ->sortByDesc('count');
                @endphp

                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-5 flex items-center gap-2">
                        <svg class="size-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ __('Regional Membership Status') }}
                    </h3>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $region->name }}</h5>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $regionTotal }} {{ Str::plural('member', $regionTotal) }}</span>
                        </div>

                        <div class="h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden flex">
                            <div class="h-full bg-emerald-500 rounded-l-full transition-all duration-500" style="width: {{ $regionActivePct }}%"></div>
                            <div class="h-full bg-red-500 rounded-r-full transition-all duration-500" style="width: {{ $regionInactivePct }}%"></div>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full bg-emerald-500"></span>
                                <div>
                                    <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ $activeCount }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Active') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full bg-red-500"></span>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-red-600 dark:text-red-400">{{ $inactiveCount }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Inactive') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($raPositions->isNotEmpty())
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-5 flex items-center gap-2">
                            <svg class="size-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ __('Position Counts') }}
                        </h3>

                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                            @foreach($raPositions as $position)
                                <a
                                    href="{{ $position['id'] ? route('admin.members.index', ['position_id' => $position['id']]) : route('admin.members.index') }}"
                                    class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center hover:border-amber-400/50 dark:hover:border-amber-500/50 hover:shadow-md hover:shadow-amber-500/5 transition-all duration-200 group"
                                >
                                    <p class="text-2xl font-black text-gray-900 dark:text-gray-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ $position['count'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ $position['name'] }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @elseif($isClubAdmin && isset($club) && $club)
                @php
                    $activeCount = \App\Models\Member::where('club_id', $club->id)->where('status', 'active')->count();
                    $inactiveCount = \App\Models\Member::where('club_id', $club->id)->where('status', 'inactive')->count();
                    $clubTotal = $activeCount + $inactiveCount;
                    $clubActivePct = $clubTotal > 0 ? round(($activeCount / $clubTotal) * 100) : 0;
                    $clubInactivePct = $clubTotal > 0 ? round(($inactiveCount / $clubTotal) * 100) : 0;

                    $cpPositions = \App\Models\Member::where('club_id', $club->id)
                        ->select('position_id')
                        ->selectRaw('COUNT(*) as count')
                        ->with('position')
                        ->groupBy('position_id')
                        ->get()
                        ->map(fn($m) => [
                            'id' => $m->position_id,
                            'name' => $m->position?->name ?? 'Unassigned',
                            'count' => (int) $m->count,
                        ])
                        ->sortByDesc('count');
                @endphp

                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-5 flex items-center gap-2">
                        <svg class="size-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ __('Club Membership Status') }}
                    </h3>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $club->name }}</h5>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $clubTotal }} {{ Str::plural('member', $clubTotal) }}</span>
                        </div>

                        <div class="h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden flex">
                            <div class="h-full bg-emerald-500 rounded-l-full transition-all duration-500" style="width: {{ $clubActivePct }}%"></div>
                            <div class="h-full bg-red-500 rounded-r-full transition-all duration-500" style="width: {{ $clubInactivePct }}%"></div>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full bg-emerald-500"></span>
                                <div>
                                    <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ $activeCount }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Active') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full bg-red-500"></span>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-red-600 dark:text-red-400">{{ $inactiveCount }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Inactive') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($cpPositions->isNotEmpty())
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-5 flex items-center gap-2">
                            <svg class="size-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ __('Position Counts') }}
                        </h3>

                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                            @foreach($cpPositions as $position)
                                <a
                                    href="{{ $position['id'] ? route('admin.members.index', ['position_id' => $position['id']]) : route('admin.members.index') }}"
                                    class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center hover:border-amber-400/50 dark:hover:border-amber-500/50 hover:shadow-md hover:shadow-amber-500/5 transition-all duration-200 group"
                                >
                                    <p class="text-2xl font-black text-gray-900 dark:text-gray-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ $position['count'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ $position['name'] }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            @if($isNationLevel)
                <!-- Quick Actions -->
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

                                @if($isSuperAdmin)
                                    <a href="{{ route('admin.admins.create') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <span class="shrink-0 bg-red-50 dark:bg-red-900/30 rounded-md p-2">
                                            <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                        </span>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Create Admin') }}</span>
                                    </a>
                                @endif
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
            @elseif($isRegionalAdmin)
                <!-- Regional Admin quick links -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Quick Links') }}</h3>
                            <div class="space-y-3">
                                <a href="{{ route('admin.clubs.create') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <span class="shrink-0 bg-green-50 dark:bg-green-900/30 rounded-md p-2">
                                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Create Club') }}</span>
                                </a>
                                <a href="{{ route('admin.members.create') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <span class="shrink-0 bg-purple-50 dark:bg-purple-900/30 rounded-md p-2">
                                        <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Add Member') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($isClubAdmin)
                <!-- Club Admin quick links -->
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

                if ($isClubAdmin && $user->club_id) {
                    $recentMembersQuery->where('club_id', $user->club_id);
                } elseif ($isRegionalAdmin && $user->region_id) {
                    $regionClubIds = \App\Models\Club::where('region_id', $user->region_id)->pluck('id');
                    $recentMembersQuery->whereIn('club_id', $regionClubIds);
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
