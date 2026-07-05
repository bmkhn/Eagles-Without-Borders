@props([
    'class' => '',
])

<div class="w-full overflow-x-auto">
    <table {{ $attributes->merge(['class' => $class ?: 'min-w-full divide-y divide-gray-200 dark:divide-gray-700']) }}>
        {{ $slot }}
    </table>
</div>
