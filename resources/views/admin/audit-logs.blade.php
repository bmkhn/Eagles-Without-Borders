<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Audit Logs') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Activity Log') }}</h3>

                    <!-- Search & Filter -->
                    <form method="GET" action="{{ route('admin.audit-logs') }}" class="mb-4" x-data="{ submitting: false }" @submit="submitting = true">
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $q }}"
                                    placeholder="{{ __('Search by details or admin...') }}"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                />
                            </div>
                            <select
                                name="log_name"
                                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                onchange="this.form.submit()"
                            >
                                <option value="">{{ __('All Log Types') }}</option>
                                @foreach($logNames as $logName)
                                    <option value="{{ $logName }}" @selected($filterLogName === $logName)>
                                        {{ $logName === 'default' ? __('General') : Str::headline($logName) }}
                                    </option>
                                @endforeach
                            </select>
                            <select
                                name="event"
                                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                onchange="this.form.submit()"
                            >
                                <option value="">{{ __('All Events') }}</option>
                                @foreach($eventTypes as $eventType)
                                    <option value="{{ $eventType }}" @selected($filterEvent === $eventType)>
                                        {{ Str::headline($eventType) }}
                                    </option>
                                @endforeach
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

                    <!-- Logs Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Date/Time') }}</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Admin') }}</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Event') }}</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Details') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($logs as $log)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                        <td class="px-3 py-3.5 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                            {{ $log->created_at->format('M d, Y h:i A') }}
                                        </td>
                                        <td class="px-3 py-3.5 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            @if($log->causer)
                                                {{ $log->causer->name }}
                                                <span class="text-xs text-gray-500">({{ $log->causer->email }})</span>
                                            @else
                                                <span class="text-gray-400 italic">{{ __('System') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3.5">
                                            @php
                                                $eventColors = [
                                                    'created_admin' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                                                    'updated_admin' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                                                    'deleted_admin' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                                    'created' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                                                    'updated' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                                                    'deleted' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                                ];
                                                $colorClass = $eventColors[$log->description] ?? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400';
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $colorClass }}">
                                                {{ Str::headline($log->description) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3.5 text-sm text-gray-600 dark:text-gray-400">
                                            @if($log->properties && $log->properties->isNotEmpty())
                                                @foreach($log->properties as $key => $value)
                                                    <span class="text-xs text-gray-500">{{ $key }}: </span>
                                                    <span class="text-xs">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                                    @if(!$loop->last), @endif
                                                @endforeach
                                            @else
                                                <span class="text-gray-400 italic">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-10 text-center text-gray-500 dark:text-gray-400">
                                            {{ __('No audit logs found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
