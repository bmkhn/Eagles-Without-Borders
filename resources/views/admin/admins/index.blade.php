<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Admin Accounts') }}
            </h2>
            <a
                href="{{ route('admin.admins.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{ __('Create Admin') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <x-alert type="success" auto-dismiss>{{ session('success') }}</x-alert>
            @endif

            @if(session('error'))
                <x-alert type="danger">{{ session('error') }}</x-alert>
            @endif

            <div class="mt-6">
                <x-card title="Search & Manage">

                    <!-- Search & Filter -->
                    <form method="GET" action="{{ route('admin.admins.index') }}" class="mb-4" x-data="{ submitting: false }" @submit="submitting = true">
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $q }}"
                                    placeholder="{{ __('Search by name or email...') }}"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                />
                            </div>
                            <select
                                name="role"
                                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                onchange="this.form.submit()"
                            >
                                <option value="">{{ __('All Roles') }}</option>
                                @if(!isset($isNationalAdmin) || !$isNationalAdmin)
                                    <option value="super-admin" @selected($filterRole === 'super-admin')>{{ __('Super Admin') }}</option>
                                @endif
                                <option value="national-admin" @selected($filterRole === 'national-admin')>{{ __('National Admin') }}</option>
                                <option value="regional-admin" @selected($filterRole === 'regional-admin')>{{ __('Regional Admin') }}</option>
                                <option value="club-admin" @selected($filterRole === 'club-admin')>{{ __('Club Admin') }}</option>
                            </select>
                            <button type="submit" x-bind:disabled="submitting" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition disabled:opacity-70 disabled:cursor-wait">
                                <span x-show="!submitting">{{ __('Search') }}</span>
                                <svg x-show="submitting" x-cloak class="animate-spin size-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-show="submitting" x-cloak>{{ __('Searching...') }}</span>
                            </button>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Name') }}</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Email') }}</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Role') }}</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Scope') }}</th>
                                    <th class="px-3 py-3.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($admins as $admin)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                        <td class="px-3 py-3.5 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <div class="flex items-center gap-2">
                                                <div class="size-8 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white font-bold text-xs">
                                                    {{ substr($admin->name, 0, 1) }}
                                                </div>
                                                <span>{{ $admin->name }}</span>
                                                @if($admin->id === auth()->id())
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">{{ __('You') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-3.5 text-sm text-gray-600 dark:text-gray-400">{{ $admin->email }}</td>
                                        <td class="px-3 py-3.5">
                                            @php
                                                $roleColors = [
                                                    'super-admin' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                                    'national-admin' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
                                                    'regional-admin' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                                                    'club-admin' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                                                ];
                                                $role = $admin->roles->first()?->name ?? 'none';
                                                $colorClass = $roleColors[$role] ?? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400';
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $colorClass }}">
                                                {{ Str::headline($role) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3.5 text-sm text-gray-600 dark:text-gray-400">
                                            @if($role === 'regional-admin' && $admin->region)
                                                <span>{{ $admin->region->name }}</span>
                                            @elseif($role === 'club-admin' && $admin->club)
                                                <span>{{ $admin->club->name }}</span>
                                            @elseif(in_array($role, ['super-admin', 'national-admin']))
                                                <span class="text-gray-400">{{ __('Global') }}</span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3.5 text-right">
                                            <a
                                                href="{{ route('admin.admins.edit', $admin) }}"
                                                class="inline-flex items-center px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-md text-xs font-semibold hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition"
                                            >
                                                {{ __('Edit') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-10 text-center text-gray-500 dark:text-gray-400">
                                            {{ __('No admin accounts found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $admins->links() }}
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
