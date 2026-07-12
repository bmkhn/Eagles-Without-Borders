<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Edit Region') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-card title="Update Region: {{ $region->name }}">
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

                <form method="POST" action="{{ route('admin.regions.update', $region) }}" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4" x-data="{
                        original: {
                            name: '{{ addslashes(old('name', $region->name)) }}',
                            ra_name: '{{ addslashes(old('ra_name', optional($region->regionalAdmin)->name)) }}',
                            ra_email: '{{ addslashes(old('ra_email', optional($region->regionalAdmin)->email)) }}',
                        },
                        form: {
                            name: '{{ addslashes(old('name', $region->name)) }}',
                            ra_name: '{{ addslashes(old('ra_name', optional($region->regionalAdmin)->name)) }}',
                            ra_email: '{{ addslashes(old('ra_email', optional($region->regionalAdmin)->email)) }}',
                        },
                        get isDirty() {
                            return this.form.name !== this.original.name
                                || this.form.ra_name !== this.original.ra_name
                                || this.form.ra_email !== this.original.ra_email
                                || (document.getElementById('ra_password')?.value ?? '') !== '';
                        }
                    }">
                        <div>
                            <x-input-label for="name" :value="__('Region Name')" />
                            <input
                                id="name"
                                name="name"
                                type="text"
                                x-model="form.name"
                                required
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            @error('name')
                                <x-input-error class="mt-1" :messages="[$message]" />
                            @enderror
                        </div>

                        <!-- Regional Admin Account Section -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ __('Regional Admin Account') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('Leave password blank to keep the current one.') }}</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="ra_name" :value="__('Admin Name')" />
                                    <input
                                        id="ra_name"
                                        name="ra_name"
                                        type="text"
                                        x-model="form.ra_name"
                                        class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    @error('ra_name')
                                        <x-input-error class="mt-1" :messages="[$message]" />
                                    @enderror
                                </div>

                                <div>
                                    <x-input-label for="ra_email" :value="__('Admin Email')" />
                                    <input
                                        id="ra_email"
                                        name="ra_email"
                                        type="email"
                                        x-model="form.ra_email"
                                        class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    @error('ra_email')
                                        <x-input-error class="mt-1" :messages="[$message]" />
                                    @enderror
                                </div>

                                <div>
                                    <x-input-label for="ra_password" :value="__('New Password')" />
                                    <input
                                        id="ra_password"
                                        name="ra_password"
                                        type="password"
                                        class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="{{ __('Leave blank to keep current') }}"
                                    />
                                    @error('ra_password')
                                        <x-input-error class="mt-1" :messages="[$message]" />
                                    @enderror
                                </div>

                                <div>
                                    <x-input-label for="ra_password_confirmation" :value="__('Confirm Password')" />
                                    <input
                                        id="ra_password_confirmation"
                                        name="ra_password_confirmation"
                                        type="password"
                                        class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="!isDirty || submitting"
                                :class="!isDirty || submitting
                                    ? 'inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed'
                                    : 'inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 dark:hover:bg-indigo-400 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150'
                                "
                            >
                                <span x-show="!submitting">{{ __('Update Region') }}</span>
                                <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                                    <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <span>{{ __('Saving...') }}</span>
                                </span>
                            </button>

                            <a
                                href="{{ route('admin.regions.index') }}"
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
