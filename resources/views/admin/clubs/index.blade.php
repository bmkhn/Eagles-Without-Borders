<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Clubs') }}
            </h2>

            <a
                href="{{ route('admin.clubs.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{ __('Create Club') }}
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
                        <form method="GET" action="{{ route('admin.clubs.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div class="flex-1">
                                <label for="q" class="block text-sm font-medium text-gray-700">
                                    {{ __('Search by name') }}
                                </label>
                                <input
                                    id="q"
                                    name="q"
                                    value="{{ $q }}"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="{{ __('e.g. Falcons') }}"
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
                                        href="{{ route('admin.clubs.index') }}"
                                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100"
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
                                <x-table-column>{{ __('Region') }}</x-table-column>
                                <x-table-column>{{ __('Members') }}</x-table-column>
                                <x-table-column class="text-right">{{ __('Actions') }}</x-table-column>
                            </tr>
                        </x-table-head>

                        <x-table-row>
                            @foreach($clubs as $club)
                                <tr class="bg-white border-b border-gray-200">
                                    <td class="px-3 py-3.5 text-sm text-gray-900">
                                        {{ $club->name }}
                                    </td>

                                    <td class="px-3 py-3.5 text-sm text-gray-700">
                                        {{ $club->region?->name }}
                                    </td>

                                    <td class="px-3 py-3.5 text-sm text-gray-700">
                                        @if($club->members_count > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">
                                                {{ $club->members_count }} {{ Str::plural('member', $club->members_count) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-3.5 text-sm text-right">
                                        <div class="inline-flex gap-2">
                                            <a
                                                href="{{ route('admin.clubs.edit', $club) }}"
                                                class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-md text-xs font-semibold hover:bg-indigo-100"
                                            >
                                                {{ __('Edit') }}
                                            </a>

                                            @if($club->members_count > 0)
                                                <span
                                                    class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-400 border border-gray-200 rounded-md text-xs font-semibold cursor-not-allowed"
                                                    title="{{ __('Cannot delete: :count :member(s) belong to this club.', ['count' => $club->members_count, 'member' => Str::plural('member', $club->members_count)]) }}"
                                                >
                                                    {{ __('Delete') }}
                                                </span>
                                            @else
                                                <form method="POST" action="{{ route('admin.clubs.destroy', $club) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 border border-red-200 rounded-md text-xs font-semibold hover:bg-red-100"
                                                        onclick="return confirm('{{ __('Are you sure you want to delete this club?') }}')"
                                                    >
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </x-table-row>
                    </x-table>

                    <div class="mt-6">
                        {{ $clubs->links() }}
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
