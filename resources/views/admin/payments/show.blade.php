<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Payment Details') }}
            </h2>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.payments.index') }}"
                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                >
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                    </svg>
                    {{ __('Back to Payments') }}
                </a>
                <a
                    href="{{ route('admin.payments.edit', $payment) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md text-xs font-semibold text-white hover:bg-indigo-500 transition"
                >
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('Edit') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <x-alert type="success">{{ session('success') }}</x-alert>
            @endif
            @if (session('error'))
                <x-alert type="danger">{{ session('error') }}</x-alert>
            @endif

            <div class="mt-6">
            <x-card title="Payment Record">
                <div class="space-y-6">
                    {{-- Member Info --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">{{ __('Member Information') }}</h3>
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-4">
                                <div class="shrink-0">
                                    @if($payment->member?->profile_picture_url)
                                        <img src="{{ $payment->member->profile_picture_url }}" alt="" class="size-14 rounded-lg object-cover border border-gray-200 dark:border-gray-600">
                                    @else
                                        <span class="inline-flex items-center justify-center size-14 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400 font-bold text-lg">
                                            {{ substr($payment->member?->name ?? '?', 0, 1) }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('admin.members.edit', $payment->member) }}" class="text-lg font-bold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        {{ $payment->member?->name ?? '—' }}
                                    </a>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $payment->member?->club?->name ?? '—' }}</span>
                                        @if($payment->member?->club?->region)
                                            <span class="text-xs text-gray-400 dark:text-gray-500">&middot;</span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $payment->member->club->region->name }}</span>
                                        @endif
                                    </div>
                                    @if($payment->member?->position)
                                        <span class="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                            {{ $payment->member->position->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Details --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">{{ __('Payment Details') }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider mb-1">{{ __('Year Paid') }}</p>
                                <p class="text-2xl font-black text-gray-900 dark:text-gray-100">{{ $payment->year_paid }}</p>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider mb-1">{{ __('Date of Payment') }}</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $payment->date_paid instanceof \Carbon\Carbon ? $payment->date_paid->format('F d, Y') : \Carbon\Carbon::parse($payment->date_paid)->format('F d, Y') }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    {{ $payment->date_paid instanceof \Carbon\Carbon ? $payment->date_paid->format('l, h:i A') : \Carbon\Carbon::parse($payment->date_paid)->format('l, h:i A') }}
                                </p>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider mb-1">{{ __('Member Status') }}</p>
                                @if($payment->member && $payment->member->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
                                        <span class="size-2 rounded-full bg-green-500"></span>
                                        {{ __('Active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                        <span class="size-2 rounded-full bg-gray-400"></span>
                                        {{ __('Inactive') }}
                                    </span>
                                @endif
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider mb-1">{{ __('Record Created') }}</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $payment->created_at instanceof \Carbon\Carbon ? $payment->created_at->format('M d, Y h:i A') : \Carbon\Carbon::parse($payment->created_at)->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- All Payments for this Member --}}
                    @php
                        $memberPayments = $payment->member?->payments()->orderByDesc('year_paid')->get() ?? collect();
                    @endphp
                    @if($memberPayments->count() > 1)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">{{ __('All Payments — :name', ['name' => $payment->member?->name ?? '']) }}</h3>
                            <div class="space-y-2">
                                @foreach($memberPayments as $mp)
                                    <div class="flex items-center justify-between px-4 py-2.5 rounded-lg border {{ $mp->id === $payment->id ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800' : 'bg-gray-50 dark:bg-gray-800/30 border-gray-200 dark:border-gray-700' }}">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center justify-center size-8 rounded-lg {{ $mp->id === $payment->id ? 'bg-indigo-200 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-300' : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' }} font-bold text-sm">
                                                {{ $mp->year_paid }}
                                            </span>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $mp->year_paid }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $mp->date_paid instanceof \Carbon\Carbon ? $mp->date_paid->format('M d, Y') : \Carbon\Carbon::parse($mp->date_paid)->format('M d, Y') }}</p>
                                            </div>
                                        </div>
                                        @if($mp->id !== $payment->id)
                                            <a href="{{ route('admin.payments.show', $mp) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('View') }}</a>
                                        @else
                                            <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Current') }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex items-center justify-between">
                        <a
                            href="{{ route('admin.payments.edit', $payment) }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 transition"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            {{ __('Edit Payment') }}
                        </a>

                        <a
                            href="{{ route('admin.members.edit', $payment->member) }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ __('View Member') }}
                        </a>
                    </div>
                </div>
            </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
