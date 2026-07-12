@props([
    'title' => 'Confirm Deletion',
    'message' => 'Are you sure you want to delete this? This action cannot be undone.',
    'action' => '#',
    'method' => 'DELETE',
    'confirmText' => 'DELETE',
    'buttonText' => 'Delete',
    'buttonClass' => 'bg-red-600 hover:bg-red-500 text-white',
    'type' => 'soft', // 'soft' or 'permanent'
])

<div
    x-data="{
        open: false,
        confirmInput: '',
        get isValid() {
            return this.confirmInput === '{{ $confirmText }}';
        },
        reset() {
            this.confirmInput = '';
            this.open = false;
        },
        submit() {
            if (this.isValid) {
                $el.querySelector('form').submit();
            }
        }
    }"
    x-cloak
>
    {{-- Trigger Button --}}
    <button
        type="button"
        @click="open = true"
        {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-semibold border transition ' . $buttonClass]) }}
    >
        {{ $slot }}
    </button>

    {{-- Modal Backdrop --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-50 flex items-center justify-center"
            @keydown.escape.window="reset()"
        >
            {{-- Overlay --}}
            <div class="absolute inset-0 bg-black/40" @click="reset()"></div>

            {{-- Panel --}}
            <div
                class="relative w-full max-w-md mx-4 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                @click.stop
            >
                {{-- Header --}}
                <div class="px-6 pt-5 pb-4">
                    <div class="flex items-start gap-4">
                        {{-- Icon --}}
                        <div class="shrink-0">
                            @if($type === 'permanent')
                                <div class="size-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                    <svg class="size-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                </div>
                            @else
                                <div class="size-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                    <svg class="size-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ $message }}</p>
                        </div>
                    </div>
                </div>

                {{-- Confirmation Input --}}
                <div class="px-6 pb-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        {{ __('Type :confirm to confirm', ['confirm' => $confirmText]) }}
                    </label>
                    <input
                        type="text"
                        x-model="confirmInput"
                        @keydown.enter="submit()"
                        placeholder="{{ $confirmText }}"
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                        autocomplete="off"
                    >
                </div>

                {{-- Form --}}
                <form
                    method="POST"
                    action="{{ $action }}"
                    class="px-6 pb-5 pt-3 flex items-center justify-end gap-2 border-t border-gray-100 dark:border-gray-700"
                >
                    @csrf
                    @method($method)
                    <input type="hidden" name="confirm_delete" value="1">
                    <input type="hidden" name="confirm_text" x-model="confirmInput">

                    <button
                        type="button"
                        @click="reset()"
                        class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="!isValid"
                        :class="isValid ? 'opacity-100' : 'opacity-50 cursor-not-allowed'"
                        class="inline-flex items-center px-3 py-2 border border-transparent rounded-lg text-sm font-semibold text-white transition {{ $type === 'permanent' ? 'bg-red-600 hover:bg-red-500' : 'bg-red-600 hover:bg-red-500' }}"
                    >
                        {{ $buttonText }}
                    </button>
                </form>
            </div>
        </div>
    </template>
</div>
