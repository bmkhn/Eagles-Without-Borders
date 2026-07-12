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

                {{-- Main Member Update Form --}}
                <form
                    method="POST"
                    action="{{ route('admin.members.update', $member) }}"
                    enctype="multipart/form-data"
                    x-data="{
                        submitting: false,
                        originalClubId: '{{ old('club_id', $member->club_id) }}',
                        originalPositionId: '{{ old('position_id', $member->position_id) }}',
                        originalFirstName: '{{ old('first_name', $member->first_name) }}',
                        originalMiddleInitial: '{{ old('middle_initial', $member->middle_initial) }}',
                        originalLastName: '{{ old('last_name', $member->last_name) }}',
                        originalSuffix: '{{ old('suffix', $member->suffix) }}',
                        originalContactNumber: '{{ old('contact_number', $member->contact_number) }}',
                        clubId: '{{ old('club_id', $member->club_id) }}',
                        positionId: '{{ old('position_id', $member->position_id) }}',
                        firstName: '{{ old('first_name', $member->first_name) }}',
                        middleInitial: '{{ old('middle_initial', $member->middle_initial) }}',
                        lastName: '{{ old('last_name', $member->last_name) }}',
                        suffix: '{{ old('suffix', $member->suffix) }}',
                        contactNumber: '{{ old('contact_number', $member->contact_number) }}',
                        get isDirty() {
                            return this.firstName !== this.originalFirstName
                                || this.lastName !== this.originalLastName
                                || this.middleInitial !== this.originalMiddleInitial
                                || this.suffix !== this.originalSuffix
                                || this.contactNumber !== this.originalContactNumber
                                || String(this.clubId) !== String(this.originalClubId)
                                || String(this.positionId) !== String(this.originalPositionId);
                        }
                    }"
                    @submit="submitting = true"
                >
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="club_id" :value="__('Club')" />
                            @if($clubs->count() === 1)
                                <p class="mt-1.5 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $clubs->first()->name }}
                                </p>
                                <input type="hidden" name="club_id" value="{{ $clubs->first()->id }}" x-model.number="clubId">
                            @else
                                <select
                                    id="club_id"
                                    name="club_id"
                                    x-model.number="clubId"
                                    required
                                    class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">{{ __('Select club') }}</option>
                                    @foreach($clubs as $club)
                                        <option value="{{ $club->id }}">{{ $club->name }}</option>
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
                                x-model.number="positionId"
                                required
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">{{ __('Select position') }}</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}">{{ $position->name }}</option>
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
                                    x-model="firstName"
                                    required
                                    class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    x-model="middleInitial"
                                    maxlength="10"
                                    class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    x-model="suffix"
                                    maxlength="50"
                                    class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    x-model="lastName"
                                    required
                                    class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                x-model="contactNumber"
                                required
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                class="mt-1.5 block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50"
                            />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Optional. Leave empty to keep current. JPEG, PNG, GIF, WebP. Max 2MB.') }}</p>
                            @error('profile_picture')
                                <x-input-error class="mt-1" :messages="[$message]" />
                            @enderror
                        </div>

                        {{-- Update & Cancel Buttons (moved BEFORE certificates) --}}
                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                x-bind:disabled="!isDirty || submitting"
                                x-bind:class="!isDirty || submitting ? 'opacity-50 cursor-not-allowed' : 'hover:bg-indigo-500 dark:hover:bg-indigo-400 active:bg-indigo-700'"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                <span x-show="!submitting">{{ __('Update') }}</span>
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
                    </div>
                </form>

                {{-- Certificates Section (independent inline CRUD, outside main form) --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <svg class="size-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                {{ __('Certificates') }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Add, edit, or remove certificates, awards, and recognitions.') }}</p>
                        </div>
                    </div>

                    {{-- Add Certificate Form --}}
                    <div
                        class="mb-4 p-4 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/30"
                        x-data="{ showForm: false }"
                    >
                        <button
                            type="button"
                            @click="showForm = !showForm"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-md text-xs font-semibold hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span x-show="!showForm">{{ __('Add Certificate') }}</span>
                            <span x-show="showForm" x-cloak>{{ __('Cancel') }}</span>
                        </button>

                        <form
                            method="POST"
                            action="{{ route('admin.certificates.store') }}"
                            enctype="multipart/form-data"
                            x-show="showForm"
                            x-cloak
                            x-transition
                            class="mt-4"
                        >
                            @csrf
                            <input type="hidden" name="member_id" value="{{ $member->id }}">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <x-input-label for="new_cert_name" :value="__('Certificate Name')" />
                                    <input
                                        id="new_cert_name"
                                        name="name"
                                        type="text"
                                        required
                                        class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        placeholder="{{ __('e.g. Leadership Award') }}"
                                    />
                                </div>
                                <div>
                                    <x-input-label for="new_cert_issued_at" :value="__('Date Issued')" />
                                    <input
                                        id="new_cert_issued_at"
                                        name="issued_at"
                                        type="date"
                                        class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="new_cert_file" :value="__('Certificate File')" />
                                    <input
                                        id="new_cert_file"
                                        name="file"
                                        type="file"
                                        accept=".pdf,image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                        class="mt-1.5 block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">{{ __('PDF, JPEG, PNG, GIF, WebP. Max 5MB.') }}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs font-semibold hover:bg-indigo-500 transition"
                                >
                                    {{ __('Save Certificate') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Existing Certificates List --}}
                    @php $certificates = $member->certificates; @endphp

                    @if($certificates->count() > 0)
                        <div class="space-y-2">
                            @foreach($certificates as $cert)
                                <div
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-sm"
                                    x-data="{ editing: false }"
                                >
                                    <svg class="size-4 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>

                                    {{-- Display mode --}}
                                    <template x-if="!editing">
                                        <div class="flex-1 flex items-center gap-2 flex-wrap">
                                            <span class="font-semibold text-amber-800 dark:text-amber-300">{{ $cert->name }}</span>
                                            @if($cert->issued_at)
                                                <span class="text-xs text-amber-600 dark:text-amber-400">
                                                    {{ \Carbon\Carbon::parse($cert->issued_at)->format('M d, Y') }}
                                                </span>
                                            @endif
                                            @if($cert->file_url)
                                                <a
                                                    href="{{ $cert->file_url }}"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1 text-xs text-indigo-600 dark:text-indigo-400 hover:underline"
                                                >
                                                    <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                    {{ __('View File') }}
                                                </a>
                                            @endif
                                        </div>
                                    </template>

                                    {{-- Edit mode --}}
                                    <form
                                        method="POST"
                                        action="{{ route('admin.certificates.update', $cert->id) }}"
                                        enctype="multipart/form-data"
                                        x-show="editing"
                                        @submit="editing = false"
                                        class="flex-1"
                                    >
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                            <input
                                                type="text"
                                                name="name"
                                                value="{{ $cert->name }}"
                                                required
                                                class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                            <input
                                                type="date"
                                                name="issued_at"
                                                value="{{ $cert->issued_at ? \Carbon\Carbon::parse($cert->issued_at)->format('Y-m-d') : '' }}"
                                                class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                            <input
                                                type="file"
                                                name="file"
                                                accept=".pdf,image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                                class="block w-full text-xs text-gray-700 dark:text-gray-300 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-400"
                                            >
                                        </div>
                                        <div class="flex items-center gap-2 mt-2">
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
                                        </div>
                                    </form>

                                    {{-- Actions --}}
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

                                        {{-- Delete button --}}
                                        <div x-data="{ open: false, confirmInput: '' }">
                                            <button
                                                type="button"
                                                @click="open = true"
                                                class="size-7 flex items-center justify-center rounded-md text-red-400 dark:text-red-500 hover:text-red-700 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition"
                                                title="{{ __('Delete') }}"
                                            >
                                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>

                                            {{-- Delete Confirmation Modal --}}
                                            <template x-teleport="body">
                                                <div
                                                    x-show="open"
                                                    x-transition.opacity.duration.200ms
                                                    class="fixed inset-0 z-50 flex items-center justify-center"
                                                    @keydown.escape.window="open = false; confirmInput = ''"
                                                >
                                                    <div class="absolute inset-0 bg-black/40" @click="open = false; confirmInput = ''"></div>
                                                    <div class="relative w-full max-w-md mx-4 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden" @click.stop>
                                                        <div class="px-6 pt-5 pb-4">
                                                            <div class="flex items-start gap-4">
                                                                <div class="shrink-0 size-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                                                    <svg class="size-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                    </svg>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ __('Remove Certificate') }}</h3>
                                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                                                        {{ __('Remove the certificate') }}
                                                                        "<span class="font-semibold">{{ $cert->name }}</span>"
                                                                        {{ __('from this member. Any uploaded file will be permanently deleted.') }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="px-6 pb-2">
                                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                                                {{ __('Type DELETE to confirm') }}
                                                            </label>
                                                            <input
                                                                type="text"
                                                                x-model="confirmInput"
                                                                @keydown.enter="if (confirmInput === 'DELETE') $el.closest('[x-data]').querySelector('form').submit()"
                                                                placeholder="DELETE"
                                                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                                                autocomplete="off"
                                                            >
                                                        </div>
                                                        <form
                                                            method="POST"
                                                            action="{{ route('admin.certificates.destroy', $cert->id) }}"
                                                            class="px-6 pb-5 pt-3 flex items-center justify-end gap-2 border-t border-gray-100 dark:border-gray-700"
                                                        >
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="confirm_delete" value="1">
                                                            <input type="hidden" name="confirm_text" x-bind:value="confirmInput">
                                                            <button
                                                                type="button"
                                                                @click="open = false; confirmInput = ''"
                                                                class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                                                            >
                                                                {{ __('Cancel') }}
                                                            </button>
                                                            <button
                                                                type="submit"
                                                                :disabled="confirmInput !== 'DELETE'"
                                                                :class="confirmInput === 'DELETE' ? 'opacity-100' : 'opacity-50 cursor-not-allowed'"
                                                                class="inline-flex items-center px-3 py-2 border border-transparent rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-500 transition"
                                                            >
                                                                {{ __('Delete') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400 dark:text-gray-500 italic mb-4">{{ __('No certificates added yet.') }}</p>
                    @endif
                </div>

                {{-- Payments Section (independent inline CRUD, outside main form) --}}
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
                                            <div x-data="{ open: false, confirmInput: '' }">
                                                <button
                                                    type="button"
                                                    @click="open = true"
                                                    class="size-7 flex items-center justify-center rounded-md text-red-400 dark:text-red-500 hover:text-red-700 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition"
                                                    title="{{ __('Delete') }}"
                                                >
                                                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>

                                                {{-- Delete Confirmation Modal --}}
                                                <template x-teleport="body">
                                                    <div
                                                        x-show="open"
                                                        x-transition.opacity.duration.200ms
                                                        class="fixed inset-0 z-50 flex items-center justify-center"
                                                        @keydown.escape.window="open = false; confirmInput = ''"
                                                    >
                                                        <div class="absolute inset-0 bg-black/40" @click="open = false; confirmInput = ''"></div>
                                                        <div class="relative w-full max-w-md mx-4 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden" @click.stop>
                                                            <div class="px-6 pt-5 pb-4">
                                                                <div class="flex items-start gap-4">
                                                                    <div class="shrink-0 size-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                                                        <svg class="size-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="flex-1 min-w-0">
                                                                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ __('Delete Payment Record') }}</h3>
                                                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                                                            {{ __('This will delete the payment record for Year :year. The member\'s status will be re-evaluated after deletion.', ['year' => $year]) }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="px-6 pb-2">
                                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                                                    {{ __('Type DELETE to confirm') }}
                                                                </label>
                                                                <input
                                                                    type="text"
                                                                    x-model="confirmInput"
                                                                    @keydown.enter="if (confirmInput === 'DELETE') $el.closest('[x-data]').querySelector('form').submit()"
                                                                    placeholder="DELETE"
                                                                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                                                    autocomplete="off"
                                                                >
                                                            </div>
                                                            <form
                                                                method="POST"
                                                                action="{{ route('admin.payments.destroy', $paymentForYear->id) }}"
                                                                class="px-6 pb-5 pt-3 flex items-center justify-end gap-2 border-t border-gray-100 dark:border-gray-700"
                                                            >
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="confirm_delete" value="1">
                                                                <input type="hidden" name="confirm_text" x-bind:value="confirmInput">
                                                                <button
                                                                    type="button"
                                                                    @click="open = false; confirmInput = ''"
                                                                    class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                                                                >
                                                                    {{ __('Cancel') }}
                                                                </button>
                                                                <button
                                                                    type="submit"
                                                                    :disabled="confirmInput !== 'DELETE'"
                                                                    :class="confirmInput === 'DELETE' ? 'opacity-100' : 'opacity-50 cursor-not-allowed'"
                                                                    class="inline-flex items-center px-3 py-2 border border-transparent rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-500 transition"
                                                                >
                                                                    {{ __('Delete') }}
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400 dark:text-gray-500 italic mb-4">{{ __('No payments recorded yet.') }}</p>
                    @endif

                    <!-- Add Payment Form -->
                    <div
                        class="mt-4 p-4 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/30"
                        x-data="{
                            paidYears: @js($paidYears),
                            selectedYear: {{ old('year_paid', $currentYear) }},
                            get isYearPaid() {
                                return this.paidYears.includes(this.selectedYear);
                            }
                        }"
                    >
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ __('Record a Payment') }}</h4>

                        <template x-if="isYearPaid">
                            <div class="mb-3 flex items-center gap-2 text-sm text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg px-3 py-2">
                                <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                <span x-text="'Year ' + selectedYear + ' has already been paid for this member.'"></span>
                            </div>
                        </template>

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
                                    x-model.number="selectedYear"
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
                                :disabled="isYearPaid"
                                :class="isYearPaid ? 'opacity-50 cursor-not-allowed bg-gray-400' : 'bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700'"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
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
