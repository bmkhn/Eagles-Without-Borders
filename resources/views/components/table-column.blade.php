@props([
    'class' => '',
])

<th scope="col" {{ $attributes->merge(['class' => $class ?: 'px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100']) }}>
    {{ $slot }}
</th>
