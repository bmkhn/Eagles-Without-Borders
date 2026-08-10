<!-- Admin Accounts by Region & Club -->
<div class="mt-8">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-2">
        <svg class="size-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        {{ ($showRegionCard ?? true) ? __('Admin Accounts by Region & Club') : __('Admin Accounts by Club') }}
    </h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
        @if($showRegionCard ?? true)
            {{ __('Admin accounts connected to each region and club. Regions or clubs with no account are marked as None.') }}
        @else
            {{ __('Admin accounts connected to your club. Clubs with no account are marked as None.') }}
        @endif
    </p>

    @php
        $roleColors = [
            'super-admin' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
            'national-admin' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
            'regional-admin' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
            'club-admin' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
        ];
    @endphp

    <div class="grid grid-cols-1 gap-6 {{ ($showRegionCard ?? true) ? 'lg:grid-cols-2' : '' }}">
        {{-- By Region --}}
        @if($showRegionCard ?? true)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-transparent dark:from-indigo-950/30 dark:to-transparent border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <div class="size-2 rounded-full bg-indigo-500"></div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-base">{{ __('By Region') }}</h4>
                </div>
            </div>
            <div class="p-6 space-y-5 max-h-96 overflow-y-auto">
                @forelse($regionsWithAdmins as $region)
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $region->name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 shrink-0">
                                {{ $region->adminUsers->count() }} {{ Str::plural('admin', $region->adminUsers->count()) }}
                            </span>
                        </div>
                        @if($region->adminUsers->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach($region->adminUsers as $admin)
                                    @php
                                        $role = $admin->roles->first()?->name ?? 'none';
                                        $colorClass = $roleColors[$role] ?? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400';
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-xs">                                                        <span class="size-5 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white font-bold text-[9px] shrink-0">
                                                            {{ substr($admin->name, 0, 1) }}
                                                        </span>
                                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $admin->name }}</span>
                                                        @if($admin->id === auth()->id())
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">{{ __('You') }}</span>
                                                        @endif
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $colorClass }}">
                                            {{ Str::headline($role) }}
                                        </span>
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500 italic">{{ __('None') }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-400 dark:text-gray-500 italic">{{ __('No regions yet.') }}</p>
                @endforelse
            </div>
        </div>
        @endif

        {{-- By Club --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-transparent dark:from-green-950/30 dark:to-transparent border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <div class="size-2 rounded-full bg-green-500"></div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-base">{{ __('By Club') }}</h4>
                </div>
            </div>
            <div class="p-6 space-y-5 max-h-96 overflow-y-auto">
                @forelse($clubsWithAdmins as $club)
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $club->name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 shrink-0">
                                {{ $club->adminUsers->count() }} {{ Str::plural('admin', $club->adminUsers->count()) }}
                            </span>
                        </div>
                        @if($club->adminUsers->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach($club->adminUsers as $admin)
                                    @php
                                        $role = $admin->roles->first()?->name ?? 'none';
                                        $colorClass = $roleColors[$role] ?? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400';
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-xs">                                                        <span class="size-5 rounded-full bg-gradient-to-br from-green-500 to-green-700 flex items-center justify-center text-white font-bold text-[9px] shrink-0">
                                                            {{ substr($admin->name, 0, 1) }}
                                                        </span>
                                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $admin->name }}</span>
                                                        @if($admin->id === auth()->id())
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">{{ __('You') }}</span>
                                                        @endif
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $colorClass }}">
                                            {{ Str::headline($role) }}
                                        </span>
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500 italic">{{ __('None') }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-400 dark:text-gray-500 italic">{{ __('No clubs yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
