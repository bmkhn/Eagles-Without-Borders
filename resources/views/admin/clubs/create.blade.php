<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Create Club') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-card title="New Club">
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

                <form method="POST" action="{{ route('admin.clubs.store') }}" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf

                    <div class="space-y-6" x-data="{
                        password: '',
                        confirmPassword: '',
                        get passwordsMatch() {
                            return this.password !== '' && this.password === this.confirmPassword;
                        }
                    }">
                        {{-- Club Details --}}
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Club Details') }}</h3>
                            <div class="space-y-2">
                                <div>
                                    <x-input-label for="region_id" :value="__('Region')" />
                                    <select
                                        id="region_id"
                                        name="region_id"
                                        required
                                        class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">{{ __('Select region') }}</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}" @selected(old('region_id') == $region->id)>
                                                {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('region_id')
                                        <x-input-error class="mt-1" :messages="[$message]" />
                                    @enderror
                                </div>

                                <div>
                                    <x-input-label for="name" :value="__('Club Name')" />
                                    <input
                                        id="name"
                                        name="name"
                                        type="text"
                                        value="{{ old('name') }}"
                                        required
                                        class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    @error('name')
                                        <x-input-error class="mt-1" :messages="[$message]" />
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Club President Account --}}
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ __('Club President Account') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('Create a login account for the club president.') }}</p>
                            <div class="space-y-2">
                                <div>
                                    <x-input-label for="cp_name" :value="__('Name')" />
                                    <input
                                        id="cp_name"
                                        name="cp_name"
                                        type="text"
                                        value="{{ old('cp_name') }}"
                                        required
                                        class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    @error('cp_name')
                                        <x-input-error class="mt-1" :messages="[$message]" />
                                    @enderror
                                </div>

                                <div>
                                    <x-input-label for="cp_email" :value="__('Email')" />
                                    <input
                                        id="cp_email"
                                        name="cp_email"
                                        type="email"
                                        value="{{ old('cp_email') }}"
                                        required
                                        class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    @error('cp_email')
                                        <x-input-error class="mt-1" :messages="[$message]" />
                                    @enderror
                                </div>

                                <div>
                                    <x-input-label for="cp_password" :value="__('Password')" />
                                    <input
                                        id="cp_password"
                                        name="cp_password"
                                        type="password"
                                        x-model="password"
                                        required
                                        class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    @error('cp_password')
                                        <x-input-error class="mt-1" :messages="[$message]" />
                                    @enderror
                                </div>

                                <div>
                                    <x-input-label for="cp_password_confirmation" :value="__('Confirm Password')" />
                                    <input
                                        id="cp_password_confirmation"
                                        name="cp_password_confirmation"
                                        type="password"
                                        x-model="confirmPassword"
                                        required
                                        class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <template x-if="confirmPassword !== '' && !passwordsMatch">
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ __('Passwords do not match.') }}</p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="!passwordsMatch || submitting"
                                :class="!passwordsMatch || submitting
                                    ? 'inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed'
                                    : 'inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150'
                                "
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
                                href="{{ route('admin.clubs.index') }}"
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
</x-app-layout>
