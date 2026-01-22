@props([
    'question',
    'disabled' => false,
])

@php
    $settings = $question->settings ?? [];
    $isMultiline = $settings['multiline'] ?? false;
    $maxLength = $settings['max_length'] ?? null;
    $placeholder = $settings['placeholder'] ?? null;

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

    @if ($isMultiline)
        <x-he4rt::textarea
            :disabled="$disabled"
            :placeholder="$placeholder"
            :resizable="true"
            maxlength="{{ $maxLength }}"
            rows="4"
            :required="$question->is_required && !$disabled"
            :attributes="$inputAttributes"
        />
    @else
        <x-he4rt::input
            type="text"
            :disabled="$disabled"
            :placeholder="$placeholder"
            maxlength="{{ $maxLength }}"
            :required="$question->is_required && !$disabled"
            :attributes="$inputAttributes"
        />
    @endif
</div>
