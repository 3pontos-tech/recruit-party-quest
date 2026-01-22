@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'label' => null,
    'checked' => false,
    'disabled' => false,
])

@php
    $inputId = $id ?? ($name ? $name . '-' . $value : 'hp-radio-' . \Illuminate\Support\Str::random(4));
    $isDisabled = $disabled;
    $isChecked = $checked;
    $labelAttributes = $attributes->only('class');
    $inputAttributes = $attributes->except('class');
@endphp

<label {{ $labelAttributes->class(['hp-radio-label', 'hp-radio-label--disabled' => $isDisabled]) }}>
    <input
        type="radio"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ $value }}"
        @if ($isChecked) checked @endif
        @if ($isDisabled) disabled @endif
        {{ $inputAttributes->class('hp-radio') }}
    />

    @if ($label)
        <x-he4rt::text>{{ $label }}</x-he4rt::text>
    @else
        {{ $slot }}
    @endif
</label>
