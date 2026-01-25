@props([
    'type' => 'text',
    'name' => null,
    'id' => null,
    'label' => null,
    'disabled' => false,
])

@php
    $inputId = $id ?? $name;
    $isDisabled = (bool) $disabled;
@endphp

<div @class(['hp-input-field', $attributes->get('class')])>
    @if ($label)
        <x-he4rt::heading size="2xs" :for="$inputId">
            {{ $label }}
        </x-he4rt::heading>
    @endif

    <input
        type="{{ $type }}"
        @if($name) name="{{ $name }}" @endif
        @if($inputId) id="{{ $inputId }}" @endif
        @disabled($isDisabled)
        {{ $attributes->except('class')->class('hp-input') }}
    />
</div>
