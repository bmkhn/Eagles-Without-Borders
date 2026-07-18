<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Create Member') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-card title="New Member">
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

                <form method="POST" action="{{ route('admin.members.store') }}" enctype="multipart/form-data"
                      x-data="{
                        submitting: false,
                        showNoPaymentWarning: false,
                        payments: [],
                        addPayment() {
                            this.payments.push({ year_paid: {{ (int) now()->year }}, date_paid: '{{ now()->format('Y-m-d') }}' });
                        },
                        removePayment(index) {
                            this.payments.splice(index, 1);
                        },
                        confirmSubmit() {
                            if (this.payments.length === 0) {
                                this.showNoPaymentWarning = true;
                            } else {
                                this.submitting = true;
                                this.$el.submit();
                            }
                        },
                        proceedWithoutPayment() {
                            this.showNoPaymentWarning = false;
                            this.submitting = true;
                            this.$el.submit();
                        }
                      }"
                      @submit.prevent="confirmSubmit()">
                    @csrf

                    {{-- Member Details Container --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                        <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-transparent dark:from-indigo-950/30 dark:to-transparent border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <div class="size-2 rounded-full bg-indigo-500"></div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-base">{{ __('Member Details') }}</h3>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <x-input-label for="club_id" :value="__('Club')" />
                                @if($clubs->count() === 1)
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $clubs->first()->name }}
                                    </p>
                                    <input type="hidden" name="club_id" value="{{ $clubs->first()->id }}">
                                @else
                                    <select
                                        id="club_id"
                                        name="club_id"
                                        required
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">{{ __('Select club') }}</option>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" @selected(old('club_id') == $club->id)>
                                                {{ $club->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('club_id')
                                    <x-input-error class="mt-1" :messages="[$message]" />
                                @enderror
                            </div>

                            <div>
                                <x-input-label for="position_id" :value="__('Position')" />
                                <select
                                    id="position_id"
                                    name="position_id"
                                    required
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">{{ __('Select position') }}</option>
                                    @foreach($positions as $position)
                                        <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>
                                            {{ $position->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('position_id')
                                    <x-input-error class="mt-1" :messages="[$message]" />
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                <div class="sm:col-span-2">
                                    <x-input-label for="first_name" :value="__('First Name')" />
                                    <input
                                        id="first_name"
                                        name="first_name"
                                        type="text"
                                        value="{{ old('first_name') }}"
                                        required
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    @error('first_name')
                                        <x-input-error class="mt-1" :messages="[$message]" />
                                    @enderror
                                </div>

                                <div>
                                    <x-input-label for="middle_initial" :value="__('M.I.')" />
                                    <input
                                        id="middle_initial"
                                        name="middle_initial"
                                        type="text"
                                        value="{{ old('middle_initial') }}"
                                        maxlength="10"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="{{ __('(opt)') }}"
                                    />
                                    @error('middle_initial')
                                        <x-input-error class="mt-1" :messages="[$message]" />
                                    @enderror
                                </div>

                                <div>
                                    <x-input-label for="suffix" :value="__('Suffix')" />
                                    <input
                                        id="suffix"
                                        name="suffix"
                                        type="text"
                                        value="{{ old('suffix') }}"
                                        maxlength="50"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="{{ __('Jr., III, etc.') }}"
                                    />
                                    @error('suffix')
                                        <x-input-error class="mt-1" :messages="[$message]" />
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <x-input-label for="last_name" :value="__('Last Name')" />
                                <input
                                    id="last_name"
                                    name="last_name"
                                    type="text"
                                    value="{{ old('last_name') }}"
                                    required
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                @error('last_name')
                                    <x-input-error class="mt-1" :messages="[$message]" />
                                @enderror
                            </div>

                            <div>
                                <x-input-label for="contact_number" :value="__('Contact Number')" />
                                <input
                                    id="contact_number"
                                    name="contact_number"
                                    type="text"
                                    value="{{ old('contact_number') }}"
                                    required
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                @error('contact_number')
                                    <x-input-error class="mt-1" :messages="[$message]" />
                                @enderror
                            </div>

                            <div>
                                <x-input-label for="profile_picture" :value="__('Profile Picture')" />
                                <input
                                    id="profile_picture"
                                    name="profile_picture"
                                    type="file"
                                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                    class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50"
                                />
                                <p class="mt-1 text-xs text-gray-500">{{ __('Optional. JPEG, PNG, GIF, WebP. Max 2MB.') }}</p>
                                @error('profile_picture')
                                    <x-input-error class="mt-1" :messages="[$message]" />
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Certificates Container --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6"
                         x-data="{
                            submitting: false,
                            certificates: [],
                            addCertificate() {
                                this.certificates.push({ name: '', file: null, issued_at: '' });
                            },
                            removeCertificate(index) {
                                this.certificates.splice(index, 1);
                            }
                         }">
                        <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-transparent dark:from-amber-950/30 dark:to-transparent border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="size-2 rounded-full bg-amber-500"></div>
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-base">{{ __('Certificates') }}</h3>
                                </div>
                                <button
                                    type="button"
                                    @click="addCertificate()"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-md text-xs font-semibold hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('Add Certificate') }}
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mt-1 ml-4">{{ __('Add certificates, awards, and recognitions.') }}</p>
                        </div>
                        <div class="p-6">
                            <template x-for="(cert, index) in certificates" :key="index">
                                <div class="p-4 mb-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="'Certificate #' + (index + 1)"></span>
                                        <button
                                            type="button"
                                            @click="removeCertificate(index)"
                                            class="text-red-500 hover:text-red-700 transition"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <x-input-label x-bind:for="'certificates_' + index + '_name'" :value="__('Certificate Name')" />
                                            <input
                                                :id="'certificates_' + index + '_name'"
                                                :name="'certificates[' + index + '][name]'"
                                                type="text"
                                                x-model="cert.name"
                                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                placeholder="{{ __('e.g. Leadership Award') }}"
                                            />
                                        </div>

                                        <div>
                                            <x-input-label x-bind:for="'certificates_' + index + '_issued_at'" :value="__('Date Issued')" />
                                            <input
                                                :id="'certificates_' + index + '_issued_at'"
                                                :name="'certificates[' + index + '][issued_at]'"
                                                type="date"
                                                x-model="cert.issued_at"
                                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            />
                                        </div>

                                        <div class="sm:col-span-2">
                                            <x-input-label x-bind:for="'certificates_' + index + '_file'" :value="__('Certificate File')" />
                                            <input
                                                :id="'certificates_' + index + '_file'"
                                                :name="'certificates[' + index + '][file]'"
                                                type="file"
                                                accept=".pdf,image/jpeg,image/png,image/jpg,image/gif,image.webp"
                                                class="mt-1.5 block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50"
                                            />
                                            <p class="mt-1 text-xs text-gray-500">{{ __('PDF, JPEG, PNG, GIF, WebP. Max 5MB.') }}</p>
                                            @error('certificates.*.file')
                                                <x-input-error class="mt-1" :messages="[$message]" />
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <p x-show="certificates.length === 0" class="text-sm text-gray-400 dark:text-gray-500 text-center py-4 italic">
                                {{ __('No certificates added yet. Click "Add Certificate" above.') }}
                            </p>
                        </div>
                    </div>

                    {{-- Payments Container --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                        <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-transparent dark:from-green-950/30 dark:to-transparent border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="size-2 rounded-full bg-green-500"></div>
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-base">{{ __('Payments') }}</h3>
                                </div>
                                <button
                                    type="button"
                                    @click="addPayment()"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-md text-xs font-semibold hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('Add Payment') }}
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mt-1 ml-4">{{ __('Record membership payments.') }}</p>
                        </div>
                        <div class="p-6">
                            <template x-for="(payment, index) in payments" :key="index">
                                <div class="p-4 mb-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="'Payment #' + (index + 1)"></span>
                                        <button
                                            type="button"
                                            @click="removePayment(index)"
                                            class="text-red-500 hover:text-red-700 transition"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <x-input-label x-bind:for="'payments_' + index + '_year_paid'" :value="__('Year')" />
                                            <input
                                                :id="'payments_' + index + '_year_paid'"
                                                :name="'payments[' + index + '][year_paid]'"
                                                type="number"
                                                x-model.number="payment.year_paid"
                                                min="2000"
                                                max="2099"
                                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                placeholder="{{ __('e.g. 2024') }}"
                                            />
                                        </div>

                                        <div>
                                            <x-input-label x-bind:for="'payments_' + index + '_date_paid'" :value="__('Date of Payment')" />
                                            <input
                                                :id="'payments_' + index + '_date_paid'"
                                                :name="'payments[' + index + '][date_paid]'"
                                                type="date"
                                                x-model="payment.date_paid"
                                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <p x-show="payments.length === 0" class="text-sm text-gray-400 dark:text-gray-500 text-center py-4 italic">
                                {{ __('No payments added yet. Click "Add Payment" above.') }}
                            </p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-3 pt-2">
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 dark:hover:bg-indigo-400 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <span x-show="!submitting">{{ __('Save') }}</span>
                            <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                                <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span>{{ __('Saving...') }}</span>
                            </span>
                        </button>

                        <a
                            href="{{ route('admin.members.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 active:bg-gray-100"
                        >
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>

                {{-- No Payment Warning Modal --}}
                <template x-teleport="body">
                    <div
                        x-show="showNoPaymentWarning"
                        x-transition.opacity.duration.200ms
                        class="fixed inset-0 z-50 flex items-center justify-center"
                        @keydown.escape.window="showNoPaymentWarning = false"
                    >
                        <div class="absolute inset-0 bg-black/40" @click="showNoPaymentWarning = false"></div>
                        <div class="relative w-full max-w-md mx-4 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden" @click.stop>
                            <div class="px-6 pt-5 pb-4">
                                <div class="flex items-start gap-4">
                                    <div class="shrink-0 size-10 rounded-full bg-amber-100 dark:bg-amber-500/10 flex items-center justify-center">
                                        <svg class="size-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ __('No Payment Recorded') }}</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                            {{ __("You haven't added any payments for this member. The member will be created with an 'Inactive' status until a payment is recorded.") }}
                                        </p>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                            {{ __('Would you like to continue anyway?') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="px-6 pb-5 pt-3 flex items-center justify-end gap-2 border-t border-gray-100 dark:border-gray-700">
                                <button
                                    type="button"
                                    @click="showNoPaymentWarning = false"
                                    class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                                >
                                    {{ __('Add Payment') }}
                                </button>
                                <button
                                    type="button"
                                    @click="proceedWithoutPayment"
                                    class="inline-flex items-center px-3 py-2 border border-transparent rounded-lg text-sm font-semibold text-white bg-amber-600 hover:bg-amber-500 transition"
                                >
                                    {{ __('Save Without Payment') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </x-card>
        </div>
    </div>
</x-app-layout>
