@props([
    'icon' => null,
    'size' => 'lg',
])

@php
    $classes = collect([
        'hp-badge',
        'hp-badge-size-' . $size,
    ])
        ->filter()
        ->implode(' ');
@endphp

<div {{ $attributes->class($classes) }}>
    <x-he4rt::icon
        size="lg"
        :icon="$icon"
        class="text-text-medium group-hover:text-text-high transition duration-500"
    />
</div>
