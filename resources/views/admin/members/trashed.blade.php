<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Recycle Bin') }}
                @if($trashedCount > 0)
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                        {{ $trashedCount }}
                    </span>
                @endif
            </h2>

            <a
                href="{{ route('admin.members.index') }}"
                class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
            >
                <svg class="size-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                </svg>
                {{ __('Back to Members') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <x-alert type="success">{{ session('success') }}</x-alert>
            @endif

            @if(session('error'))
                <x-alert type="danger">{{ session('error') }}</x-alert>
            @endif

            <x-card title="Deleted Members">
                {{-- Filters --}}
                <form method="GET" action="{{ route('admin.members.trashed') }}" class="mb-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                        <div class="flex-1">
                            <x-input-label for="q" :value="__('Search trashed members')" />
                            <input
                                id="q"
                                name="q"
                                value="{{ $q }}"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="{{ __('Name, contact, or slug...') }}"
                            >
                        </div>

                        <div class="flex gap-2">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 transition"
                            >
                                {{ __('Search') }}
                            </button>

                            @if($q !== '' || $filterClubId || $filterPositionId)
                                <a
                                    href="{{ route('admin.members.trashed') }}"
                                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                                >
                                    {{ __('Clear') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="club_id" :value="__('Club')" />
                            <select
                                id="club_id"
                                name="club_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                onchange="this.form.submit()"
                            >
                                <option value="">{{ __('All Clubs') }}</option>
                                @foreach($clubs as $club)
                                    <option value="{{ $club->id }}" @selected($filterClubId === $club->id)>
                                        {{ $club->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="position_id" :value="__('Position')" />
                            <select
                                id="position_id"
                                name="position_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                onchange="this.form.submit()"
                            >
                                <option value="">{{ __('All Positions') }}</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" @selected($filterPositionId === $position->id)>
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

                {{-- Trashed Members Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <x-table-head>{{ __('Name') }}</x-table-head>
                                <x-table-head>{{ __('Club') }}</x-table-head>
                                <x-table-head>{{ __('Position') }}</x-table-head>
                                <x-table-head>{{ __('Deleted At') }}</x-table-head>
                                <x-table-head class="text-right">{{ __('Actions') }}</x-table-head>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($trashedMembers as $member)
                                <tr class="bg-red-50/30 dark:bg-red-900/5 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors">
                                    <x-table-column>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $member->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $member->contact_number }}</p>
                                        </div>
                                    </x-table-column>
                                    <x-table-column class="text-gray-700 dark:text-gray-300">
                                        {{ $member->club?->name ?? '—' }}
                                    </x-table-column>
                                    <x-table-column class="text-gray-700 dark:text-gray-300">
                                        {{ $member->position?->name ?? '—' }}
                                    </x-table-column>
                                    <x-table-column class="text-gray-500 dark:text-gray-400 text-sm">
                                        {{ $member->deleted_at instanceof \Carbon\Carbon ? $member->deleted_at->format('M d, Y h:i A') : \Carbon\Carbon::parse($member->deleted_at)->format('M d, Y h:i A') }}
                                    </x-table-column>
                                    <x-table-column class="text-right">
                                        <div class="inline-flex gap-2">
                                            {{-- Restore --}}
                                            <form method="POST" action="{{ route('admin.members.restore', $member->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800 rounded-md text-xs font-semibold hover:bg-green-100 dark:hover:bg-green-900/50 transition"
                                                    onclick="return confirm('{{ __('Restore this member?') }}')"
                                                >
                                                    <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                    {{ __('Restore') }}
                                                </button>
                                            </form>

                                            {{-- Permanent Delete --}}
                                            <form
                                                method="POST"
                                                action="{{ route('admin.members.force-destroy', $member->id) }}"
                                                onsubmit="return confirm('{{ __('Permanently delete this member? This action cannot be undone.') }}')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-md text-xs font-semibold hover:bg-red-100 dark:hover:bg-red-900/50 transition"
                                                >
                                                    <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    {{ __('Delete Forever') }}
                                                </button>
                                            </form>
                                        </div>
                                    </x-table-column>
                                </tr>
                            @empty
                                <tr>
                                    <x-table-column colspan="5">
                                        <div class="flex flex-col items-center justify-center py-12 text-center">
                                            <svg class="size-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            <p class="text-gray-400 dark:text-gray-500 italic">
                                                {{ __('The recycle bin is empty.') }}
                                            </p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                {{ __('Deleted members will appear here.') }}
                                            </p>
                                        </div>
                                    </x-table-column>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $trashedMembers->links() }}
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
