@props([
    'type' => 'info', // info|success|warning|danger
    'autoDismiss' => false,
])

@php
    $classes = match ($type) {
        'success' => 'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-800 text-green-800 dark:text-green-300',
        'warning' => 'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-300',
        'danger' => 'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800 text-red-800 dark:text-red-300',
        default => 'bg-blue-50 dark:bg-blue-900/30 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300',
    };

    $icon = match ($type) {
        'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z',
        'danger' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    };
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-cloak
    x-transition:leave.duration.500ms
    x-init="
        @if($autoDismiss)
            setTimeout(() => { show = false }, 4000);
        @endif
    "
    class="rounded-lg border px-4 py-3 {{ $classes }}"
    role="alert"
    {{ $attributes }}
>
    <div class="flex items-start gap-2.5">
        <div class="mt-0.5 shrink-0">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
        </div>

        <div class="flex-1 text-sm leading-5">
            {{ $slot }}
        </div>

        <button
            type="button"
            class="shrink-0 inline-flex rounded-md p-1 text-current hover:bg-black/10 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
            @click="show = false"
            aria-label="Dismiss"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path
                    fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                />
            </svg>
        </button>
    </div>
</div>
