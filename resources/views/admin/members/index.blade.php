<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
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

            <div class="mt-6">
                <x-card title="Search & Manage" class="mb-6">
                    <div class="mb-4">
                        <form method="GET" action="{{ route('admin.members.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div class="flex-1">
                                <label for="q" class="block text-sm font-medium text-gray-700">
                                    {{ __('Search by name, contact, or slug') }}
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
                                        href="{{ route('admin.members.index') }}"
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
                                <tr class="bg-white border-b border-gray-200">
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

                                            <form method="POST" action="{{ route('admin.members.destroy', $member) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-md text-xs font-semibold hover:bg-red-100 dark:hover:bg-red-900/50"
                                                    onclick="return confirm('{{ __('Are you sure you want to delete this member?') }}')"
                                                >
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
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
