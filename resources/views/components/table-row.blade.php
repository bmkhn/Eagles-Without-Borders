@props([
    'class' => '',
])

<tr {{ $attributes->merge(['class' => $class ?: 'bg-white dark:bg-gray-800']) }}>
    {{ $slot }}
</tr>
