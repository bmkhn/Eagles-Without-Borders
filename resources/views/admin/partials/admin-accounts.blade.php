<!-- Admin Accounts by Region & Club -->
@php
    $unstaffedRegions = ($regionsWithAdmins ?? collect())->filter(fn ($r) => $r->adminUsers->isEmpty())->count();
    $unstaffedClubs = ($clubsWithAdmins ?? collect())->filter(fn ($c) => $c->adminUsers->isEmpty())->count();
@endphp
<div class="mb-8">
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

    <div class="grid grid-cols-1 gap-6 {{ ($showRegionCard ?? true) ? 'lg:grid-cols-2' : '' }}">
        {{-- By Region --}}
        @if($showRegionCard ?? true)
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
            x-data="{
                page: 1,
                perPage: 8,
                total: {{ $regionsWithAdmins->count() }},
                get totalPages() {
                    return Math.max(1, Math.ceil(this.total / this.perPage));
                },
                isOnPage(index) {
                    return index >= (this.page - 1) * this.perPage && index < this.page * this.perPage;
                },
                goToPage(newPage) {
                    if (newPage < 1 || newPage > this.totalPages) return;
                    this.page = newPage;
                    this.$refs.list.scrollTop = 0;
                }
            }"
        >
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-transparent dark:from-indigo-950/30 dark:to-transparent border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <div class="size-2 rounded-full bg-indigo-500"></div>
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-base">{{ __('By Region') }}</h4>
                    </div>
                    @if($unstaffedRegions > 0)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 shrink-0">
                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            {{ $unstaffedRegions }} {{ __('unstaffed') }}
                        </span>
                    @endif
                </div>
            </div>

            <div x-ref="list" class="px-6 py-4 max-h-96 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                @if($regionsWithAdmins->isEmpty())
                    <p class="text-sm text-gray-400 dark:text-gray-500 italic">{{ __('No regions yet.') }}</p>
                @else
                    @foreach($regionsWithAdmins as $index => $region)
                        <div class="py-2.5 first:pt-0 last:pb-0" x-show="isOnPage({{ $index }})">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $region->name }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 shrink-0">
                                    {{ $region->adminUsers->count() }} {{ Str::plural('admin', $region->adminUsers->count()) }}
                                </span>
                            </div>
                            @if($region->adminUsers->isNotEmpty())
                                <ul class="mt-2 space-y-1">
                                    @foreach($region->adminUsers as $admin)
                                        <li class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                                            <span class="truncate">{{ $admin->name }}</span>
                                            @if($admin->id === auth()->id())
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 shrink-0">{{ __('You') }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500 italic">{{ __('None') }}</p>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            <div
                class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between"
                x-show="total > perPage"
            >
                <button
                    type="button"
                    @click="goToPage(page - 1)"
                    :disabled="page === 1"
                    :class="page === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-50 dark:hover:bg-gray-700'"
                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 transition"
                >
                    {{ __('Previous') }}
                </button>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Page') }} <span x-text="page"></span> {{ __('of') }} <span x-text="totalPages"></span>
                </span>
                <button
                    type="button"
                    @click="goToPage(page + 1)"
                    :disabled="page === totalPages"
                    :class="page === totalPages ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-50 dark:hover:bg-gray-700'"
                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 transition"
                >
                    {{ __('Next') }}
                </button>
            </div>
        </div>
        @endif

        {{-- By Club --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
            x-data="{
                page: 1,
                perPage: 8,
                total: {{ $clubsWithAdmins->count() }},
                get totalPages() {
                    return Math.max(1, Math.ceil(this.total / this.perPage));
                },
                isOnPage(index) {
                    return index >= (this.page - 1) * this.perPage && index < this.page * this.perPage;
                },
                goToPage(newPage) {
                    if (newPage < 1 || newPage > this.totalPages) return;
                    this.page = newPage;
                    this.$refs.list.scrollTop = 0;
                }
            }"
        >
            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-transparent dark:from-green-950/30 dark:to-transparent border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <div class="size-2 rounded-full bg-green-500"></div>
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-base">{{ __('By Club') }}</h4>
                    </div>
                    @if($unstaffedClubs > 0)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 shrink-0">
                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            {{ $unstaffedClubs }} {{ __('unstaffed') }}
                        </span>
                    @endif
                </div>
            </div>

            <div x-ref="list" class="px-6 py-4 max-h-96 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                @if($clubsWithAdmins->isEmpty())
                    <p class="text-sm text-gray-400 dark:text-gray-500 italic">{{ __('No clubs yet.') }}</p>
                @else
                    @foreach($clubsWithAdmins as $index => $club)
                        <div class="py-2.5 first:pt-0 last:pb-0" x-show="isOnPage({{ $index }})">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $club->name }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 shrink-0">
                                    {{ $club->adminUsers->count() }} {{ Str::plural('admin', $club->adminUsers->count()) }}
                                </span>
                            </div>
                            @if($club->adminUsers->isNotEmpty())
                                <ul class="mt-2 space-y-1">
                                    @foreach($club->adminUsers as $admin)
                                        <li class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                                            <span class="truncate">{{ $admin->name }}</span>
                                            @if($admin->id === auth()->id())
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 shrink-0">{{ __('You') }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500 italic">{{ __('None') }}</p>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            <div
                class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between"
                x-show="total > perPage"
            >
                <button
                    type="button"
                    @click="goToPage(page - 1)"
                    :disabled="page === 1"
                    :class="page === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-50 dark:hover:bg-gray-700'"
                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 transition"
                >
                    {{ __('Previous') }}
                </button>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Page') }} <span x-text="page"></span> {{ __('of') }} <span x-text="totalPages"></span>
                </span>
                <button
                    type="button"
                    @click="goToPage(page + 1)"
                    :disabled="page === totalPages"
                    :class="page === totalPages ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-50 dark:hover:bg-gray-700'"
                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 transition"
                >
                    {{ __('Next') }}
                </button>
            </div>
        </div>
    </div>
</div>
