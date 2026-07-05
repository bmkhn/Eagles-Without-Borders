@props([
    'class' => '',
])

<thead {{ $attributes->merge(['class' => $class ?: 'bg-gray-50 dark:bg-gray-800/50']) }}>
    {{ $slot }}
</thead>
