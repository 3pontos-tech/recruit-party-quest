@props([
    'as' => null,
    'href' => null,
    'type' => 'button',
    'current' => null,
])

@php
    if ($as) {
        $tag = $as;
    } elseif ($href) {
        $tag = 'a';
    } else {
        $tag = 'div';
    }

    if ($current === null && $href) {
        $current = request()->fullUrlIs(url($href));
    }

    $dataCurrent = $current ? '' : false;
@endphp

@if ($tag === 'a')
    <a href="{{ $href }}" {{ $attributes->merge(['data-current' => $dataCurrent]) }}>
        {{ $slot }}
    </a>
@elseif ($tag === 'button')
    <button type="{{ $type }}" {{ $attributes->merge(['data-current' => $dataCurrent]) }}>
        {{ $slot }}
    </button>
@else
    <{{ $tag }} {{ $attributes->merge(['data-current' => $dataCurrent]) }}>
        {{ $slot }}
    </{{ $tag }}>
@endif
