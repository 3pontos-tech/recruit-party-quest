@props([
    'variant' => 'outline',
    'size' => 'md',
    'rounded' => 'md',
    'icon' => null,
    'iconTrailing' => null,
    'iconLeading' => null,
])
@php
    $iconTrailing ??= $attributes->get('icon:trailing');
    $iconLeading ??= $icon ??= $iconLeading;

    $classes = Arr::toCssClasses([
        'hp-tag',
        "hp-tag-$variant",
        "hp-tag-size-$size",
        "hp-tag-rounded-$rounded",
    ]);
@endphp

<x-he4rt::interactive-as {{ $attributes->class($classes) }}>
    @if ($iconLeading)
        <x-he4rt::icon :icon="$iconLeading" class="hp-tag-icon" />
    @elseif (isset($leading))
        <div class="hp-tag-icon">
            {{ $leading }}
        </div>
    @endif
    {{ $slot }}

    @if ($iconTrailing)
        <x-he4rt::icon :icon="$iconTrailing" class="hp-tag-icon" />
    @elseif (isset($trailing))
        <div class="hp-tag-icon">
            {{ $trailing }}
        </div>
    @endif
</x-he4rt::interactive-as>
