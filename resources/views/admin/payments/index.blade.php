<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Payments') }}
            </h2>
            <a
                href="{{ route('admin.payments.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{ __('Create Payment') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <x-alert type="success">{{ session('success') }}</x-alert>
            @endif
            @if (session('error'))
                <x-alert type="danger">{{ session('error') }}</x-alert>
            @endif

            <div class="mt-6">
            <x-card title="All Payments">
                {{-- Filters --}}
                <form method="GET" action="{{ route('admin.payments.index') }}" class="mb-4 flex items-end gap-3 flex-wrap" x-data="{ submitting: false }" @submit="submitting = true">
                    <div>
                        <x-input-label for="q" :value="__('Search Member')" />
                        <input
                            id="q"
                            name="q"
                            type="text"
                            value="{{ $filterMemberName }}"
                            placeholder="{{ __('Name...') }}"
                            class="mt-1 w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                    </div>

                    <div>
                        <x-input-label for="year" :value="__('Year')" />
                        <select
                            id="year"
                            name="year"
                            class="mt-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                            <option value="">{{ __('All Years') }}</option>
                            @foreach($years as $y)
                                <option value="{{ $y }}" @selected($filterYear == $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($clubs->isNotEmpty())
                        <div>
                            <x-input-label for="club_id" :value="__('Club')" />
                            <select
                                id="club_id"
                                name="club_id"
                                class="mt-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            >
                                <option value="">{{ __('All Clubs') }}</option>
                                @foreach($clubs as $club)
                                    <option value="{{ $club->id }}" @selected($filterClubId == $club->id)>
                                        {{ $club->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <button
                        type="submit"
                        x-bind:disabled="submitting"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold hover:bg-indigo-500 transition disabled:opacity-70 disabled:cursor-wait"
                    >
                        <span x-show="!submitting">{{ __('Filter') }}</span>
                        <svg x-show="submitting" x-cloak class="animate-spin size-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-show="submitting" x-cloak>{{ __('Filtering...') }}</span>
                    </button>

                    <a
                        href="{{ route('admin.payments.index') }}"
                        class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                    >
                        {{ __('Clear') }}
                    </a>
                </form>

                {{-- Payments Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <x-table-head>{{ __('Member') }}</x-table-head>
                                <x-table-head>{{ __('Club') }}</x-table-head>
                                <x-table-head>{{ __('Year Paid') }}</x-table-head>
                                <x-table-head>{{ __('Date of Payment') }}</x-table-head>
                                <x-table-head class="text-right">{{ __('Actions') }}</x-table-head>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($payments as $payment)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <x-table-column>
                                        <a
                                            href="{{ route('admin.members.edit', $payment->member) }}"
                                            class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline"
                                        >
                                            {{ $payment->member?->name ?? '—' }}
                                        </a>
                                    </x-table-column>
                                    <x-table-column class="text-gray-700 dark:text-gray-300">
                                        {{ $payment->member?->club?->name ?? '—' }}
                                    </x-table-column>
                                    <x-table-column>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
                                            {{ $payment->year_paid }}
                                        </span>
                                    </x-table-column>
                                    <x-table-column class="text-gray-600 dark:text-gray-400">
                                        {{ $payment->date_paid instanceof \Carbon\Carbon ? $payment->date_paid->format('M d, Y') : \Carbon\Carbon::parse($payment->date_paid)->format('M d, Y') }}
                                    </x-table-column>
                                    <x-table-column class="text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a
                                                href="{{ route('admin.payments.show', $payment) }}"
                                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition"
                                            >
                                                <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                {{ __('View') }}
                                            </a>

                                            <a
                                                href="{{ route('admin.payments.edit', $payment) }}"
                                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition"
                                            >
                                                <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                {{ __('Edit') }}
                                            </a>
                                        </div>
                                    </x-table-column>
                                </tr>
                            @empty
                                <tr>
                                    <x-table-column colspan="5">
                                        <p class="text-center text-gray-400 dark:text-gray-500 italic py-8">
                                            {{ __('No payments found.') }}
                                        </p>
                                    </x-table-column>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $payments->links() }}
                </div>
            </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
