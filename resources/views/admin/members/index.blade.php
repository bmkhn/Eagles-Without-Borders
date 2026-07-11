<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Members') }}
            </h2>

            <a
                href="{{ route('admin.members.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{ __('Create Member') }}
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

            <div class="mt-6" x-data="{ showImport: false }">
                <x-card title="Search & Manage" class="mb-6">
                    <!-- Member Count + Import/Export -->
                    <div class="mb-5 flex items-center gap-3 flex-wrap">
                        @php $hasFilters = $q !== '' || $filterRegionId || $filterClubId || $filterStatus !== '' || $filterPositionId; @endphp

                        <div class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <svg class="size-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            @if($hasFilters)
                                <span class="text-sm">
                                    <span class="font-bold text-gray-900 dark:text-gray-100">{{ $totalCount }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">{{ Str::plural('member', $totalCount) }}</span>
                                    <span class="text-gray-400 dark:text-gray-500 mx-1">/</span>
                                    <span class="font-bold text-amber-600 dark:text-amber-400">{{ $unfilteredTotal }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">{{ Str::plural('member', $unfilteredTotal) }}</span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">{{ __('total') }}</span>
                                </span>
                            @else
                                <span class="text-sm">
                                    <span class="font-bold text-gray-900 dark:text-gray-100">{{ $totalCount }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">{{ Str::plural('member', $totalCount) }}</span>
                                </span>
                            @endif
                        </div>

                        <div class="ml-auto flex items-center gap-2">
                            <!-- Info tooltip (export & import) -->
                            <div class="relative" x-data="{ showTooltip: false }">
                                <svg
                                    @mouseenter="showTooltip = true"
                                    @mouseleave="showTooltip = false"
                                    class="size-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div
                                    x-show="showTooltip"
                                    x-cloak
                                    @mouseenter="showTooltip = true"
                                    @mouseleave="showTooltip = false"
                                    class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-3 py-2 rounded-lg bg-gray-900 dark:bg-gray-700 text-white text-xs leading-relaxed shadow-lg z-50"
                                >
                                    <p class="whitespace-nowrap">{{ __('Export: Download a CSV of the members shown on this page (applies your search and filters).') }}</p>
                                    <p class="whitespace-nowrap">{{ __('Import: Upload a CSV file to add members. Clubs are resolved from the CSV.') }}</p>
                                    <p class="whitespace-nowrap">{{ __('Duplicates are skipped. Scoped admins can only import within their scope.') }}</p>
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
                                </div>
                            </div>

                            <!-- Export Button -->
                            <a
                                href="{{ route('admin.members.export', request()->query()) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ __('Export CSV') }}
                            </a>

                            <!-- Import Button -->
                            <button
                                type="button"
                                @click="showImport = !showImport"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-lg text-xs font-semibold text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                                </svg>
                                {{ __('Import CSV') }}
                            </button>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.members.index') }}" class="mb-4">
                        <!-- Search row -->
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                            <div class="flex-1">
                                <label for="q" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Search by name, contact, or slug') }}
                                </label>
                                <input
                                    id="q"
                                    name="q"
                                    value="{{ $q }}"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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

                                @if($q !== '' || $filterRegionId || $filterClubId || $filterStatus !== '' || $filterPositionId)
                                    <a
                                        href="{{ route('admin.members.index') }}"
                                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 active:bg-gray-100"
                                    >
                                        {{ __('Clear All') }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Filters row -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            @if($isSuperAdmin || $isNationalAdmin)
                                <div>
                                    <label for="region_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Region') }}</label>
                                    <select
                                        id="region_id"
                                        name="region_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        onchange="this.form.submit()"
                                    >
                                        <option value="">{{ __('All Regions') }}</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}" @selected($filterRegionId === $region->id)>
                                                {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif($isRegionalAdmin)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Region') }}</label>
                                    <p class="mt-1.5 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $userRegionName ?? '—' }}</p>
                                </div>
                            @elseif($isClubAdmin)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Region') }}</label>
                                    <p class="mt-1.5 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $userRegionName ?? '—' }}</p>
                                </div>
                            @endif

                            <div>
                                <label for="club_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Club') }}</label>
                                @if($isClubAdmin)
                                    <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">{{ $clubs->first()?->name ?? '—' }}</p>
                                @else
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
                                @endif
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Status') }}</label>
                                <select
                                    id="status"
                                    name="status"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    onchange="this.form.submit()"
                                >
                                    <option value="">{{ __('All Statuses') }}</option>
                                    <option value="active" @selected($filterStatus === 'active')>{{ __('Active') }}</option>
                                    <option value="inactive" @selected($filterStatus === 'inactive')>{{ __('Inactive') }}</option>
                                </select>
                            </div>

                            <div>
                                <label for="position_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Position') }}</label>
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

                    <!-- Import Form (hidden by default) -->
                    <div
                        x-show="showImport"
                        x-cloak
                        x-transition
                        class="mb-6 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700"
                    >
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                            <svg class="size-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                            </svg>
                            {{ __('Import Members from CSV') }}
                        </h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            {{ __('CSV must match the export format: First Name, M.I., Last Name, Suffix, Contact Number, Club, Region, Position, Status, Paid Years.') }}
                            {{ __('Clubs are resolved from the CSV. Duplicates (same name + contact number) will be skipped.') }}
                            {{ __('The "Paid Years" column accepts comma-separated "year:date" entries (e.g. "2024:2024-01-15, 2025:2025-03-01" or just "2024" to use today\'s date).') }}
                        </p>

                        <form method="POST" action="{{ route('admin.members.import') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="flex flex-col sm:flex-row items-start sm:items-end gap-3">
                                <div class="flex-1 w-full sm:w-auto">
                                    <x-input-label for="import_file" :value="__('CSV File')" />
                                    <input
                                        id="import_file"
                                        name="file"
                                        type="file"
                                        accept=".csv,.txt"
                                        required
                                        class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50"
                                    />
                                </div>
                                <div class="flex gap-2 shrink-0">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-indigo-500 dark:hover:bg-indigo-400 transition"
                                    >
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                                        </svg>
                                        {{ __('Import') }}
                                    </button>
                                    <button
                                        type="button"
                                        @click="showImport = false"
                                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                                    >
                                        {{ __('Cancel') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <x-table class="min-w-full">
                        <x-table-head>
                            <tr>
                                <x-table-column>{{ __('Photo') }}</x-table-column>
                                <x-table-column>{{ __('Name') }}</x-table-column>
                                <x-table-column>{{ __('Status') }}</x-table-column>
                                <x-table-column>{{ __('Club') }}</x-table-column>
                                <x-table-column>{{ __('Position') }}</x-table-column>
                                <x-table-column>{{ __('Contact') }}</x-table-column>
                                <x-table-column>{{ __('Slug') }}</x-table-column>
                                <x-table-column class="text-right">{{ __('Actions') }}</x-table-column>
                            </tr>
                        </x-table-head>

                        <x-table-row>
                            @foreach($members as $member)
                                <tr class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                    <td class="px-3 py-3.5 text-sm text-gray-900 dark:text-gray-100">
                                        @if($member->profile_picture_url)
                                            <img
                                                src="{{ $member->profile_picture_url }}"
                                                alt="{{ $member->name }}"
                                                class="size-9 rounded-lg object-cover border border-gray-200 dark:border-gray-700"
                                            >
                                        @else
                                            <span class="inline-flex items-center justify-center size-9 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400 font-bold text-xs">
                                                {{ substr($member->name, 0, 1) }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-3.5 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $member->name }}
                                    </td>

                                    <td class="px-3 py-3.5 text-sm">
                                        @if($member->status === 'active')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
                                                <span class="size-1.5 rounded-full bg-green-500"></span>
                                                {{ __('Active') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                                <span class="size-1.5 rounded-full bg-gray-400"></span>
                                                {{ __('Inactive') }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $member->club?->name }}
                                    </td>

                                    <td class="px-3 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $member->position?->name }}
                                    </td>

                                    <td class="px-3 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $member->contact_number }}
                                    </td>

                                    <td class="px-3 py-3.5 text-sm text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $member->slug }}
                                    </td>

                                    <td class="px-3 py-3.5 text-sm text-right">
                                        <div class="inline-flex gap-2">
                                            <a
                                                href="{{ route('member.profile', $member->slug) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-semibold hover:bg-gray-50 dark:hover:bg-gray-600"
                                            >
                                                <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                {{ __('View') }}
                                            </a>

                                            <a
                                                href="{{ route('admin.members.edit', $member) }}"
                                                class="inline-flex items-center px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-md text-xs font-semibold hover:bg-indigo-100 dark:hover:bg-indigo-900/50"
                                            >
                                                {{ __('Edit') }}
                                            </a>

                                            <x-confirm-delete-modal
                                                action="{{ route('admin.members.destroy', $member) }}"
                                                method="DELETE"
                                                title="{{ __('Move to Trash') }}"
                                                message="{{ __('This member will be moved to the recycle bin. Their data can be restored later.') }}"
                                                confirm-text="DELETE"
                                                button-text="{{ __('Delete') }}"
                                                button-class="bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/50"
                                                type="soft"
                                            >
                                                {{ __('Delete') }}
                                            </x-confirm-delete-modal>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </x-table-row>
                    </x-table>

                    <div class="mt-6">
                        {{ $members->links() }}
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
