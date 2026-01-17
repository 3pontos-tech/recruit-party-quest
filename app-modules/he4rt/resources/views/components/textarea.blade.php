@props([
    'name' => null,
    'id' => null,
    'label' => null,
    'disabled' => false,
    'rows' => 3,
])

@php
    $inputId = $id ?? ($name ?? 'hp-input-' . \Illuminate\Support\Str::random(4));
    $isDisabled = (bool) $disabled;
@endphp

<div {{ $attributes->class('hp-input-field') }}>
    @if ($label)
        <x-he4rt::heading size="3xs" for="{{ $inputId }}" class="hp-input-label">
            {{ $label }}
        </x-he4rt::heading>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $inputId }}"
        rows="{{ $rows }}"
        @if ($isDisabled) disabled @endif
        {{ $attributes->class('hp-input resize-none') }}
    ></textarea>
</div>
