<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Edit Admin Account') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-card title="Edit Admin: {{ $admin->name }}">
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

                <form method="POST" action="{{ route('admin.admins.update', $admin) }}" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-4" x-data="{
                        role: '{{ $currentRole }}',
                        original: {
                            name: '{{ addslashes(old('name', $admin->name)) }}',
                            email: '{{ addslashes(old('email', $admin->email)) }}',
                            role: '{{ $currentRole }}',
                            region_id: '{{ old('region_id', $admin->region_id) }}',
                            club_id: '{{ old('club_id', $admin->club_id) }}',
                        },
                        form: {
                            name: '{{ addslashes(old('name', $admin->name)) }}',
                            email: '{{ addslashes(old('email', $admin->email)) }}',
                            role: '{{ $currentRole }}',
                            region_id: '{{ old('region_id', $admin->region_id) }}',
                            club_id: '{{ old('club_id', $admin->club_id) }}',
                        },
                        get isRegional() { return this.form.role === 'regional-admin'; },
                        get isClub() { return this.form.role === 'club-admin'; },
                        password: '',
                        get isDirty() {
                            return this.form.name !== this.original.name
                                || this.form.email !== this.original.email
                                || this.form.role !== this.original.role
                                || this.form.region_id !== this.original.region_id
                                || this.form.club_id !== this.original.club_id
                                || this.password !== '';
                        }
                    }">
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
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

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <input
                                id="email"
                                name="email"
                                type="email"
                                x-model="form.email"
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
                                x-model="form.role"
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                @if(!isset($isNationalAdmin) || !$isNationalAdmin)
                                    <option value="super-admin" @selected($currentRole === 'super-admin')>{{ __('Super Admin') }}</option>
                                @endif
                                <option value="national-admin" @selected($currentRole === 'national-admin')>{{ __('National Admin') }}</option>
                                <option value="regional-admin" @selected($currentRole === 'regional-admin')>{{ __('Regional Admin') }}</option>
                                <option value="club-admin" @selected($currentRole === 'club-admin')>{{ __('Club Admin') }}</option>
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
                                x-model="form.region_id"
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">{{ __('Select region') }}</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}" @selected(old('region_id', $admin->region_id) == $region->id)>
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
                                x-model="form.club_id"
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">{{ __('Select club') }}</option>
                                @foreach($clubs as $club)
                                    <option value="{{ $club->id }}" @selected(old('club_id', $admin->club_id) == $club->id)>
                                        {{ $club->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('club_id')
                                <x-input-error class="mt-1" :messages="[$message]" />
                            @enderror
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <x-input-label for="password" :value="__('New Password')" />
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="{{ __('Leave blank to keep current') }}"
                            />
                            @error('password')
                                <x-input-error class="mt-1" :messages="[$message]" />
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="mt-1.5 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="{{ __('Leave blank to keep current') }}"
                            />
                        </div>

                        <div class="flex items-center gap-3 pt-4">
                            <button
                                type="submit"
                                :disabled="!isDirty || submitting"
                                :class="!isDirty || submitting
                                    ? 'inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed'
                                    : 'inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500 dark:hover:bg-indigo-400 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150'
                                "
                            >
                                <span x-show="!submitting">{{ __('Update Admin') }}</span>
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

                @if($admin->id !== auth()->id())
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                        <h3 class="text-sm font-semibold text-red-600 dark:text-red-400 mb-3">{{ __('Danger Zone') }}</h3>
                        <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this admin account?') }}')">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-red-500 transition"
                                onclick="this.disabled=true; this.classList.add('opacity-50','cursor-not-allowed'); this.form.submit();"
                            >
                                {{ __('Delete This Admin') }}
                            </button>
                        </form>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
