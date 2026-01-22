@props([
    'question',
    'disabled' => false,
])

@php
    $wrapperAttributes = $attributes->only('class');
    $inputAttributes = $attributes->except('class');
@endphp

<div {{ $wrapperAttributes->class('screening-question') }}>
    <div class="mb-2 flex items-center justify-between">
        <x-he4rt::heading size="2xs">
            {{ $question->question_text }}
            @if ($question->is_required)
                <span class="text-helper-error">*</span>
            @endif
        </x-he4rt::heading>
    </div>

    <div class="hp-radio-group">
        <x-he4rt::radio
            value="yes"
            :label="__('screening::question_types.yes_no.yes')"
            :disabled="$disabled"
            :required="$question->is_required && !$disabled"
            :attributes="$inputAttributes"
        />

        <x-he4rt::radio
            value="no"
            :label="__('screening::question_types.yes_no.no')"
            :disabled="$disabled"
            :attributes="$inputAttributes"
        />
    </div>
</div>
