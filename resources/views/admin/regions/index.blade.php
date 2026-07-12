<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Regions') }}
            </h2>

            <a
                href="{{ route('admin.regions.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{ __('Create Region') }}
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

            <div class="mt-6">
                <x-card title="Search & Manage" class="mb-6">
                    <div class="mb-4">
                        <form method="GET" action="{{ route('admin.regions.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div class="flex-1">
                                <label for="q" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Search by name') }}
                                </label>
                                <input
                                    id="q"
                                    name="q"
                                    value="{{ $q }}"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="{{ __('e.g. Palawan') }}"
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
                                        href="{{ route('admin.regions.index') }}"
                                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 active:bg-gray-100"
                                    >
                                        {{ __('Clear') }}
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <x-table class="min-w-full">
                        <x-table-head>
                            <tr>
                                <x-table-column>{{ __('Name') }}</x-table-column>
                                <x-table-column>{{ __('Clubs') }}</x-table-column>
                                <x-table-column>{{ __('Regional Admin') }}</x-table-column>
                                <x-table-column class="text-right">{{ __('Actions') }}</x-table-column>
                            </tr>
                        </x-table-head>

                        @foreach($regions as $region)
                            <tr class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                <td class="px-3 py-3.5 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $region->name }}
                                </td>

                                <td class="px-3 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                                    @if($region->clubs_count > 0)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400">
                                            {{ $region->clubs_count }} {{ Str::plural('club', $region->clubs_count) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>

                                <td class="px-3 py-3.5 text-sm text-gray-600 dark:text-gray-400">
                                    @if($region->regionalAdmin)
                                        <div class="flex items-center gap-2">
                                            <div class="size-6 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-700 dark:text-blue-400 font-bold text-xs">
                                                {{ substr($region->regionalAdmin->name, 0, 1) }}
                                            </div>
                                            <span class="text-xs">{{ $region->regionalAdmin->email }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 italic text-xs">{{ __('No admin') }}</span>
                                    @endif
                                </td>

                                <td class="px-3 py-3.5 text-sm text-right">
                                    <div class="inline-flex gap-2">
                                        <a
                                            href="{{ route('admin.regions.edit', $region) }}"
                                            class="inline-flex items-center px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-md text-xs font-semibold hover:bg-indigo-100 dark:hover:bg-indigo-900/50"
                                        >
                                            {{ __('Edit') }}
                                        </a>

                                        @if($region->clubs_count > 0)
                                            <span
                                                class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-600 rounded-md text-xs font-semibold cursor-not-allowed"
                                                title="{{ __('Cannot delete: :count club(s) are assigned to this region.', ['count' => $region->clubs_count]) }}"
                                            >
                                                {{ __('Delete') }}
                                            </span>
                                        @else
                                            <x-confirm-delete-modal
                                                title="{{ __('Delete Region') }}"
                                                message="{{ __('Are you sure you want to delete this region? The regional admin login account will also be removed. This action cannot be undone.') }}"
                                                :action="route('admin.regions.destroy', $region)"
                                                confirmText="DELETE"
                                                buttonText="{{ __('Delete') }}"
                                                button-class="bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/50"
                                                type="soft"
                                            >
                                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                {{ __('Delete') }}
                                            </x-confirm-delete-modal>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-table>

                    <div class="mt-6">
                        {{ $regions->links() }}
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
