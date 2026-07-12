<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Create Admin Account') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-card title="New Admin Account">
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

                <form method="POST" action="{{ route('admin.admins.store') }}" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf

                    <div class="space-y-4" x-data="{
                        role: '{{ old('role') }}',
                        get isRegional() { return this.role === 'regional-admin'; },
                        get isClub() { return this.role === 'club-admin'; }
                    }">
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
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

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            @error('email')
                                <x-input-error class="mt-1" :messages="[$message]" />
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="role" :value="__('Role')" />
                            <select
                                id="role"
                                name="role"
                                required
                                x-model="role"
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">{{ __('Select role') }}</option>
                                @if(!isset($isNationalAdmin) || !$isNationalAdmin)
                                    <option value="super-admin" @selected(old('role') === 'super-admin')>{{ __('Super Admin') }}</option>
                                @endif
                                <option value="national-admin" @selected(old('role') === 'national-admin')>{{ __('National Admin') }}</option>
                                <option value="regional-admin" @selected(old('role') === 'regional-admin')>{{ __('Regional Admin') }}</option>
                                <option value="club-admin" @selected(old('role') === 'club-admin')>{{ __('Club Admin') }}</option>
                            </select>
                            @error('role')
                                <x-input-error class="mt-1" :messages="[$message]" />
                            @enderror
                        </div>

                        <div x-show="isRegional" x-cloak>
                            <x-input-label for="region_id" :value="__('Region')" />
                            <select
                                id="region_id"
                                name="region_id"
                                :required="isRegional"
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

                        <div x-show="isClub" x-cloak>
                            <x-input-label for="club_id" :value="__('Club')" />
                            <select
                                id="club_id"
                                name="club_id"
                                :required="isClub"
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">{{ __('Select club') }}</option>
                                @foreach($clubs as $club)
                                    <option value="{{ $club->id }}" @selected(old('club_id') == $club->id)>
                                        {{ $club->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('club_id')
                                <x-input-error class="mt-1" :messages="[$message]" />
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="password" :value="__('Password')" />
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            @error('password')
                                <x-input-error class="mt-1" :messages="[$message]" />
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <div class="flex items-center gap-3 pt-4">
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 dark:hover:bg-indigo-400 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                <span x-show="!submitting">{{ __('Create Admin') }}</span>
                                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                                    <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <span>{{ __('Saving...') }}</span>
                                </span>
                            </button>

                            <a
                                href="{{ route('admin.admins.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 active:bg-gray-100 transition"
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
