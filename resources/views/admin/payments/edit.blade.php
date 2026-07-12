<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Edit Payment') }}
            </h2>
            <a
                href="{{ route('admin.members.edit', $payment->member) }}"
                class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
            >
                {{ __('Back to Member') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <x-alert type="success">{{ session('success') }}</x-alert>
            @endif
            @if (session('error'))
                <x-alert type="danger">{{ session('error') }}</x-alert>
            @endif

            <div class="mt-6">
            <x-card title="Edit Payment Record">
                <div class="mb-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-semibold">{{ __('Member:') }}</span>
                        <a href="{{ route('admin.members.edit', $payment->member) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                            {{ $payment->member?->name ?? '—' }}
                        </a>
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ $payment->member?->club?->name ?? '—' }}
                        @if($payment->member?->club?->region)
                            &middot; {{ $payment->member->club->region->name }}
                        @endif
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-4">
                        <x-alert type="danger">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-alert>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.payments.update', $payment) }}" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="year_paid" :value="__('Year Paid')" />
                            <input
                                id="year_paid"
                                name="year_paid"
                                type="number"
                                value="{{ old('year_paid', $payment->year_paid) }}"
                                min="2000"
                                max="2099"
                                required
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            @error('year_paid')
                                <x-input-error class="mt-1" :messages="[$message]" />
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="date_paid" :value="__('Date of Payment')" />
                            <input
                                id="date_paid"
                                name="date_paid"
                                type="date"
                                value="{{ old('date_paid', $payment->date_paid instanceof \Carbon\Carbon ? $payment->date_paid->format('Y-m-d') : \Carbon\Carbon::parse($payment->date_paid)->format('Y-m-d')) }}"
                                required
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            @error('date_paid')
                                <x-input-error class="mt-1" :messages="[$message]" />
                            @enderror
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 dark:hover:bg-indigo-400 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                <span x-show="!submitting">{{ __('Update Payment') }}</span>
                                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                                    <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <span>{{ __('Saving...') }}</span>
                                </span>
                            </button>

                            <a
                                href="{{ route('admin.members.edit', $payment->member) }}"
                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 active:bg-gray-100"
                            >
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </div>
                </form>
            </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
