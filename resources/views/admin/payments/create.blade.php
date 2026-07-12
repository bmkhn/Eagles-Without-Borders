<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Create Payment') }}
        </h2>
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
            <x-card title="New Payment Record">
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

                <form method="POST" action="{{ route('admin.payments.store') }}" @submit="submitting = true" x-data="{
                        submitting: false,
                    memberId: '{{ old('member_id') }}',
                    yearPaid: '{{ old('year_paid', $currentYear) }}',
                    existingPayments: @js($existingPayments),
                    get isYearAlreadyPaid() {
                        if (!this.memberId || !this.yearPaid) return false;
                        const years = this.existingPayments[this.memberId];
                        return years && years.includes(parseInt(this.yearPaid));
                    }
                }">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="member_id" :value="__('Member')" />
                            <select
                                id="member_id"
                                name="member_id"
                                required
                                x-model="memberId"
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">{{ __('Select a member...') }}</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>
                                        {{ $member->name }} — {{ $member->club?->name ?? 'No Club' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id')
                                <x-input-error class="mt-1" :messages="[$message]" />
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="year_paid" :value="__('Year')" />
                                <input
                                    id="year_paid"
                                    name="year_paid"
                                    type="number"
                                    x-model="yearPaid"
                                    value="{{ old('year_paid', $currentYear) }}"
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
                                    value="{{ old('date_paid', now()->format('Y-m-d')) }}"
                                    required
                                    class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                @error('date_paid')
                                    <x-input-error class="mt-1" :messages="[$message]" />
                                @enderror
                            </div>
                        </div>

                        <!-- Duplicate warning message -->
                        <template x-if="isYearAlreadyPaid">
                            <div class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/30 px-4 py-3">
                                <div class="flex items-start gap-2.5">
                                    <svg class="mt-0.5 size-4 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                    <p class="text-sm text-amber-800 dark:text-amber-300">
                                        {{ __('This member already has a payment recorded for Year') }} <strong x-text="yearPaid"></strong>.
                                    </p>
                                </div>
                            </div>
                        </template>

                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="isYearAlreadyPaid || submitting"
                                :class="isYearAlreadyPaid || submitting
                                    ? 'inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed'
                                    : 'inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 dark:hover:bg-indigo-400 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150'
                                "
                            >
                                <span x-show="!submitting">{{ __('Save Payment') }}</span>
                                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                                    <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <span>{{ __('Saving...') }}</span>
                                </span>
                            </button>

                            <a
                                href="{{ route('admin.payments.index') }}"
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
