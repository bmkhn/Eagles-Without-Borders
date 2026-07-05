<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Member Directory') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search -->
            <x-card title="Search Directory" class="mb-6">
                <form method="GET" action="{{ route('admin.members.directory') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="flex-1">
                        <label for="q" class="block text-sm font-medium text-gray-700">
                            {{ __('Search by name or contact number') }}
                        </label>
                        <input
                            id="q"
                            name="q"
                            value="{{ $q }}"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="{{ __('e.g. John Doe') }}"
                        >
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ __('Search') }}
                        </button>

                        @if($q !== '')
                            <a
                                href="{{ route('admin.members.directory') }}"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100"
                            >
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </div>
                </form>
            </x-card>

            <!-- Directory Content -->
            @forelse($regions as $region)
                <div class="mt-8 mb-10 last:mb-0">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="size-3 rounded-full bg-indigo-500"></div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            {{ $region->name }}
                        </h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            ({{ $region->clubs->sum(fn($c) => $c->members->count()) }} {{ Str::plural('member', $region->clubs->sum(fn($c) => $c->members->count())) }})
                        </span>
                    </div>

                    @forelse($region->clubs as $club)
                        <div class="ml-6 mb-6 last:mb-0 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="bg-gray-50 dark:bg-gray-800/50 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-2">
                                    <svg class="size-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-sm">
                                        {{ $club->name }}
                                    </h4>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        ({{ $club->members->count() }} {{ Str::plural('member', $club->members->count()) }})
                                    </span>
                                </div>
                            </div>

                            @if($club->members->count() > 0)
                                <div class="divide-y divide-gray-100">
                                    @foreach($club->members as $member)
                                        <div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <div class="shrink-0">
                                                @if($member->profile_picture_url)
                                                    <img
                                                        src="{{ $member->profile_picture_url }}"
                                                        alt="{{ $member->name }}"
                                                        class="size-9 rounded-lg object-cover border border-gray-200"
                                                    >
                                                @else
                                                    <span class="inline-flex items-center justify-center size-9 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400 font-bold text-xs">
                                                        {{ substr($member->name, 0, 1) }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ $member->name }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    @if($member->position)
                                                        {{ $member->position->name }}
                                                    @endif
                                                    @if($member->position && $member->contact_number)
                                                        &middot;
                                                    @endif
                                                    @if($member->contact_number)
                                                        {{ $member->contact_number }}
                                                    @endif
                                                </p>
                                            </div>

                                            <a
                                                href="{{ route('member.profile', $member->slug) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-md hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
                                            >
                                                {{ __('View') }}
                                                <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="px-4 py-6 text-center">
                                    <p class="text-sm text-gray-500 italic">{{ __('No members in this club.') }}</p>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 ml-6">{{ __('No clubs found in this region.') }}</p>
                    @endforelse
                </div>
            @empty
                <x-card>
                    <div class="text-center py-10">
                        <svg class="size-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-gray-500 text-lg font-medium">{{ __('No members found') }}</p>
                        @if($q)
                            <p class="text-gray-400 text-sm mt-1">{{ __('Try a different search term.') }}</p>
                            <a href="{{ route('admin.members.directory') }}" class="inline-flex items-center gap-1 mt-3 text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                {{ __('Clear search') }}
                            </a>
                        @endif
                    </div>
                </x-card>
            @endforelse
        </div>
    </div>
</x-app-layout>
