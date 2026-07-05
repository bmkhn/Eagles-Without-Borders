@props([
    'title' => null,
    'subtitle' => null,
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm']) }}>

    @if($title)
        <div class="flex items-start justify-between gap-3 border-b border-gray-200 dark:border-gray-700 px-5 py-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
                @if($subtitle)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="shrink-0">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    <div class="px-5 py-4">
        {{ $slot }}
    </div>
</div>
