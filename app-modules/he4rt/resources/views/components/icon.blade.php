@props([
    'icon',
    'size' => 'md',
])

@php
    $componentName = $icon;

    if ($icon instanceof \Filament\Support\Icons\Heroicon) {
        $componentName = 'heroicon-o-' . $icon->value;
    } elseif ($icon instanceof \BackedEnum) {
        $componentName = $icon->value;
    }

    $iconSizeCls = 'hp-icon-size-' . $size;
@endphp

<x-dynamic-component
    :component="$componentName"
    {{
    $attributes->class([
        'hp-icon',
        $iconSizeCls,
    ])
}}
/>
