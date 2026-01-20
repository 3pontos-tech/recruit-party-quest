@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'label' => null,
    'checked' => false,
    'disabled' => false,
])

@php
    $inputId = $id ?? ($name ? $name . '-' . $value : 'hp-checkbox-' . \Illuminate\Support\Str::random(4));
    $isDisabled = (bool) $disabled;
    $isChecked = (bool) $checked;
@endphp

<label {{ $attributes->class(['hp-checkbox-label', 'hp-checkbox-label--disabled' => $isDisabled]) }}>
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ $value }}"
        @if ($isChecked) checked @endif
        @if ($isDisabled) disabled @endif
        class="hp-checkbox"
    />

    @if ($label)
        <x-he4rt::text>{{ $label }}</x-he4rt::text>
    @else
        {{ $slot }}
    @endif
</label>
