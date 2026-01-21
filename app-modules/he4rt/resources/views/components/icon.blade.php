@props([
    'icon',
    'size' => 'md',
])

@php
    $iconSizeCls = 'hp-icon-size-' . $size;
@endphp

<x-dynamic-component :component="$icon" {{
    $attributes->class([
        'hp-icon',
        $iconSizeCls,
    ])
}} />
