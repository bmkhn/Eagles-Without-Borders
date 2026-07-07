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
                                    placeholder="{{ __('e.g. Lagos') }}"
                                >
                            </div>

                            <div class="flex gap-2">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    {{ __('Search') }}
                                </button>

                                @if($q !== '')                                        <a
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
                                <x-table-column class="text-right">{{ __('Actions') }}</x-table-column>
                            </tr>
                        </x-table-head>

                        @foreach($regions as $region)
                            <tr class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                <td class="px-3 py-3.5 text-sm text-gray-900 dark:text-gray-100">
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
                                                title="{{ __('Cannot delete: :count :club(s) are assigned to this region.', ['count' => $region->clubs_count, 'club' => Str::plural('club', $region->clubs_count)]) }}"
                                            >
                                                {{ __('Delete') }}
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('admin.regions.destroy', $region) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-md text-xs font-semibold hover:bg-red-100 dark:hover:bg-red-900/50"
                                                    onclick="return confirm('{{ __('Are you sure you want to delete this region?') }}')"
                                                >
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
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
