@props([
    'type' => 'info', // info|success|warning|danger
    'dismissible' => false,
])

@php
    $classes = match ($type) {
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'danger' => 'bg-red-50 border-red-200 text-red-800',
        default => 'bg-blue-50 border-blue-200 text-blue-800',
    };
@endphp

<div
    @if($dismissible)
        x-data="{ show: true }"
        x-show="show"
        x-cloak
        class="rounded-lg border px-4 py-3 {{ $classes }}"
        role="alert"
    @else
        class="rounded-lg border px-4 py-3 {{ $classes }}"
        role="alert"
    @endif
    {{ $attributes }}
>
    <div class="flex items-start gap-2.5">
        <div class="mt-1 shrink-0">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
        </div>

        <div class="text-sm leading-5">
            {{ $slot }}
        </div>

        @if($dismissible)
            <button
                type="button"
                class="ml-auto inline-flex rounded-md p-1.5 text-current hover:bg-black/5 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                @click="show = false"
                aria-label="Dismiss"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path
                        fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
        @endif
    </div>
</div>
