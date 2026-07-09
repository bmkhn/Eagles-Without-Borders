<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Edit Member') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-card title="Update Member">
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

                <form method="POST" action="{{ route('admin.members.update', $member) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <div>
                            <x-input-label for="club_id" :value="__('Club')" />
                            @if($clubs->count() === 1)
                                <p class="mt-1.5 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $clubs->first()->name }}
                                </p>
                                <input type="hidden" name="club_id" value="{{ $clubs->first()->id }}">
                            @else
                                <select
                                    id="club_id"
                                    name="club_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">{{ __('Select club') }}</option>
                                    @foreach($clubs as $club)
                                        <option value="{{ $club->id }}" @selected(old('club_id', $member->club_id) == $club->id)>
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
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">{{ __('Select position') }}</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" @selected(old('position_id', $member->position_id) == $position->id)>
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
                                    value="{{ old('first_name', $member->first_name) }}"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    value="{{ old('middle_initial', $member->middle_initial) }}"
                                    maxlength="10"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    value="{{ old('suffix', $member->suffix) }}"
                                    maxlength="50"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="{{ __('Jr., III, etc.') }}"
                                />
                                @error('suffix')
                                    <x-input-error class="mt-1" :messages="[$message]" />
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="last_name" :value="__('Last Name')" />
                                <input
                                    id="last_name"
                                    name="last_name"
                                    type="text"
                                    value="{{ old('last_name', $member->last_name) }}"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                @error('last_name')
                                    <x-input-error class="mt-1" :messages="[$message]" />
                                @enderror
                            </div>

                            <div>
                                <x-input-label :value="__('Status')" />
                                @php
                                    $currentYear = (int) now()->year;
                                    $autoStatus = $member->hasPaidForYear($currentYear) ? 'active' : 'inactive';
                                @endphp
                                <div class="mt-2">
                                    @if($autoStatus === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-sm font-semibold text-green-700 dark:text-green-400">
                                            <span class="size-2 rounded-full bg-green-500"></span>
                                            {{ __('Active — Paid for :year', ['year' => $currentYear]) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-500 dark:text-gray-400">
                                            <span class="size-2 rounded-full bg-gray-400"></span>
                                            {{ __('Inactive — No payment for :year', ['year' => $currentYear]) }}
                                        </span>
                                    @endif
                                    <p class="mt-1 text-xs text-gray-400">{{ __('Status is auto-managed based on yearly payment.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="contact_number" :value="__('Contact Number')" />
                            <input
                                id="contact_number"
                                name="contact_number"
                                type="text"
                                value="{{ old('contact_number', $member->contact_number) }}"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            @error('contact_number')
                                <x-input-error class="mt-1" :messages="[$message]" />
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="profile_picture" :value="__('Profile Picture')" />

                            @if($member->profile_picture_url)
                                <div class="mb-2 flex items-start gap-3">
                                    <img
                                        src="{{ $member->profile_picture_url }}"
                                        alt="{{ $member->name }}"
                                        class="size-20 rounded-lg object-cover border border-gray-200 shadow-sm"
                                    >
                                    <label class="inline-flex items-center gap-2 mt-1 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            name="remove_photo"
                                            value="1"
                                            class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500"
                                        >
                                        <span class="text-sm text-red-600 font-medium">{{ __('Remove photo') }}</span>
                                    </label>
                                </div>
                            @endif

                            <input
                                id="profile_picture"
                                name="profile_picture"
                                type="file"
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50"
                            />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Optional. Leave empty to keep current. JPEG, PNG, GIF, WebP. Max 2MB.') }}</p>
                            @error('profile_picture')
                                <x-input-error class="mt-1" :messages="[$message]" />
                            @enderror
                        </div>

                        {{-- Certificates Section --}}
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6"
                             x-data="{
                                certificates: @js($member->certificates->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'file' => null, 'issued_at' => $c->issued_at ? \Carbon\Carbon::parse($c->issued_at)->format('Y-m-d') : '', 'has_file' => !is_null($c->file), 'file_url' => $c->file_url])->values()->all()),
                                addCertificate() {
                                    this.certificates.push({ id: null, name: '', file: null, issued_at: '', has_file: false, file_url: null });
                                },
                                removeCertificate(index) {
                                    if (this.certificates[index].id) {
                                        if (!confirm('{{ __('Remove this certificate?') }}')) return;
                                    }
                                    this.certificates.splice(index, 1);
                                }
                             }">
                            <input type="hidden" name="certificates_managed" value="1">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Certificates') }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Manage certificates, awards, and recognitions.') }}</p>
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

                                    {{-- Existing file link --}}
                                    <template x-if="cert.has_file && cert.file_url">
                                        <div class="mb-3 flex items-center gap-2 text-sm">
                                            <svg class="h-4 w-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <a :href="cert.file_url" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">{{ __('View current file') }}</a>
                                        </div>
                                    </template>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <x-input-label x-bind:for="'certificates_' + index + '_name'" :value="__('Certificate Name')" />
                                            <input
                                                :id="'certificates_' + index + '_name'"
                                                :name="'certificates[' + index + '][name]'"
                                                type="text"
                                                x-model="cert.name"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
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
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            />
                                        </div>

                                        <div class="sm:col-span-2">
                                            <x-input-label x-bind:for="'certificates_' + index + '_file'" :value="__('Certificate File')" />
                                            <input
                                                :id="'certificates_' + index + '_file'"
                                                :name="'certificates[' + index + '][file]'"
                                                type="file"
                                                accept=".pdf,image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                                class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50"
                                            />
                                            <p class="mt-1 text-xs text-gray-500">{{ __('PDF, JPEG, PNG, GIF, WebP. Max 5MB. Leave empty to keep current.') }}</p>
                                            @error('certificates.*.file')
                                                <x-input-error class="mt-1" :messages="[$message]" />
                                            @enderror
                                        </div>

                                        {{-- Hidden ID field --}}
                                        <input type="hidden" :name="'certificates[' + index + '][id]'" :value="cert.id">
                                    </div>
                                </div>
                            </template>

                            <p x-show="certificates.length === 0" class="text-sm text-gray-400 dark:text-gray-500 text-center py-4 italic">
                                {{ __('No certificates added yet. Click "Add Certificate" above.') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 dark:hover:bg-indigo-400 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                {{ __('Update') }}
                            </button>

                            <a
                                href="{{ route('admin.members.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 active:bg-gray-100"
                            >
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </div>
                </form>

                {{-- Payments Section (outside main form to avoid nested form issue) --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <svg class="size-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ __('Payments') }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Record and manage yearly membership payments.') }}</p>
                        </div>
                        <a
                            href="{{ route('admin.payments.index', ['q' => $member->name]) }}"
                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
                        >
                            {{ __('View All Payments') }}
                        </a>
                    </div>

                    @php
                        $currentYear = (int) now()->year;
                        $paidYears = $member->payments->pluck('year_paid')->sort()->values()->toArray();
                        $hasPaidCurrentYear = in_array($currentYear, $paidYears);
                    @endphp

                    <!-- Payment History -->
                    @if(!empty($paidYears))
                        <div class="mb-4 space-y-2">
                            @foreach($paidYears as $year)
                                @php
                                    $paymentForYear = $member->payments->firstWhere('year_paid', $year);
                                @endphp
                                @if($paymentForYear)
                                    <div
                                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-sm"
                                        x-data="{ editing: false }"
                                    >
                                        @if($year === $currentYear)
                                            <svg class="size-4 shrink-0 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @else
                                            <svg class="size-4 shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @endif

                                        <div class="flex-1 flex items-center gap-3 flex-wrap">
                                            {{-- Display mode --}}
                                            <template x-if="!editing">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-semibold text-green-700 dark:text-green-400">{{ $year }}</span>
                                                    <span class="text-xs text-green-600 dark:text-green-500">
                                                        {{ $paymentForYear->date_paid instanceof \Carbon\Carbon ? $paymentForYear->date_paid->format('M d, Y') : \Carbon\Carbon::parse($paymentForYear->date_paid)->format('M d, Y') }}
                                                    </span>
                                                </div>
                                            </template>

                                            {{-- Edit mode --}}
                                            <form
                                                method="POST"
                                                action="{{ route('admin.payments.update', $paymentForYear->id) }}"
                                                x-show="editing"
                                                @submit="editing = false"
                                                class="flex items-center gap-2 flex-wrap"
                                            >
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="_redirect" value="{{ route('admin.members.edit', $member) }}">
                                                <input
                                                    type="number"
                                                    name="year_paid"
                                                    value="{{ $year }}"
                                                    min="2000"
                                                    max="2099"
                                                    class="w-20 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                >
                                                <input
                                                    type="date"
                                                    name="date_paid"
                                                    value="{{ $paymentForYear->date_paid instanceof \Carbon\Carbon ? $paymentForYear->date_paid->format('Y-m-d') : \Carbon\Carbon::parse($paymentForYear->date_paid)->format('Y-m-d') }}"
                                                    class="w-36 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                >
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center px-2 py-1 bg-indigo-600 text-white rounded text-[10px] font-semibold hover:bg-indigo-500 transition"
                                                >
                                                    {{ __('Save') }}
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="editing = false"
                                                    class="text-[10px] text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                                                >
                                                    {{ __('Cancel') }}
                                                </button>
                                            </form>
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0">
                                            {{-- Edit button --}}
                                            <button
                                                type="button"
                                                @click="editing = !editing"
                                                class="size-7 flex items-center justify-center rounded-md text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition"
                                                title="{{ __('Edit') }}"
                                            >
                                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>

                                            {{-- Delete form --}}
                                            <form
                                                method="POST"
                                                action="{{ route('admin.payments.destroy', $paymentForYear->id) }}"
                                                onsubmit="return confirm('⚠️ DELETE PAYMENT RECORD for Year {{ $year }}?\\n\\nType OK to proceed to the second confirmation.')"
                                                class="inline-flex"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="confirm_delete" value="1">
                                                <label class="inline-flex items-center gap-1 text-xs text-red-600 cursor-pointer" title="{{ __('Delete') }}">
                                                    <input
                                                        type="checkbox"
                                                        name="confirm_text"
                                                        value="DELETE"
                                                        required
                                                        class="sr-only peer"
                                                        onchange="this.closest('form').querySelector('button[type=submit]').disabled = !this.checked"
                                                    >
                                                    <svg class="size-3.5 peer-checked:text-red-700 dark:peer-checked:text-red-600 text-red-400 dark:text-red-500 hover:text-red-700 dark:hover:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </label>
                                                <button
                                                    type="submit"
                                                    disabled
                                                    class="text-[10px] font-semibold text-red-600 hover:text-red-800 transition disabled:opacity-30 disabled:cursor-not-allowed"
                                                >
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400 dark:text-gray-500 italic mb-4">{{ __('No payments recorded yet.') }}</p>
                    @endif

                    <!-- Add Payment Form -->
                    <div class="mt-4 p-4 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/30">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ __('Record a Payment') }}</h4>
                        <form
                            method="POST"
                            action="{{ route('admin.payments.store') }}"
                            class="flex items-end gap-3 flex-wrap"
                        >
                            @csrf
                            <input type="hidden" name="member_id" value="{{ $member->id }}">
                            <input type="hidden" name="_redirect" value="{{ route('admin.members.edit', $member) }}">

                            <div>
                                <x-input-label for="new_payment_year" :value="__('Year')" />
                                <input
                                    id="new_payment_year"
                                    name="year_paid"
                                    type="number"
                                    value="{{ old('year_paid', $currentYear) }}"
                                    min="2000"
                                    max="2099"
                                    required
                                    class="mt-1 w-24 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                >
                            </div>

                            <div>
                                <x-input-label for="new_payment_date" :value="__('Date of Payment')" />
                                <input
                                    id="new_payment_date"
                                    name="date_paid"
                                    type="date"
                                    value="{{ old('date_paid', now()->format('Y-m-d')) }}"
                                    required
                                    class="mt-1 w-40 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                >
                            </div>

                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-green-500 transition"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('Record Payment') }}
                            </button>
                        </form>
                        @error('year_paid')
                            <x-input-error class="mt-1" :messages="[$message]" />
                        @enderror
                        @error('date_paid')
                            <x-input-error class="mt-1" :messages="[$message]" />
                        @enderror
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
