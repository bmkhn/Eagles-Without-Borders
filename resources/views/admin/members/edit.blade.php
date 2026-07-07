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
                                <x-input-label for="status" :value="__('Status')" />
                                <label class="mt-2 relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="status" value="inactive">
                                    <input
                                        id="status"
                                        name="status"
                                        type="checkbox"
                                        value="active"
                                        class="sr-only peer"
                                        {{ old('status', $member->status) === 'active' ? 'checked' : '' }}
                                        onchange="updateStatusLabel(this)"
                                    >
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-500 peer-checked:bg-green-600"></div>
                                    <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <span class="status-label-active {{ old('status', $member->status) !== 'active' ? 'hidden' : '' }}">Active</span>
                                        <span class="status-label-inactive {{ old('status', $member->status) === 'active' ? 'hidden' : '' }}">Inactive</span>
                                    </span>
                                </label>
                                @error('status')
                                    <x-input-error class="mt-1" :messages="[$message]" />
                                @enderror
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

                        <script>
                            function updateStatusLabel(checkbox) {
                                const container = checkbox.closest('label');
                                if (container) {
                                    const activeLabel = container.querySelector('.status-label-active');
                                    const inactiveLabel = container.querySelector('.status-label-inactive');
                                    if (activeLabel && inactiveLabel) {
                                        activeLabel.classList.toggle('hidden', !checkbox.checked);
                                        inactiveLabel.classList.toggle('hidden', checkbox.checked);
                                    }
                                }
                                const hiddenInput = checkbox.previousElementSibling;
                                if (hiddenInput && hiddenInput.type === 'hidden') {
                                    hiddenInput.value = checkbox.checked ? 'active' : 'inactive';
                                }
                            }
                        </script>

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
            </x-card>
        </div>
    </div>
</x-app-layout>
