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

        @if ($question->is_knockout)
            <x-he4rt::text class="text-helper-warning font-family-secondary shrink-0 self-start text-sm">
                (pergunta eliminatória)
            </x-he4rt::text>
        @endif
    </div>

    @if ($isMultiline)
        <x-he4rt::textarea
            :disabled="$disabled"
            :placeholder="$placeholder"
            :resizable="true"
            maxlength="{{ $maxLength }}"
            rows="4"
            {{ $inputAttributes }}
        />
    @else
        <x-he4rt::input
            type="text"
            :disabled="$disabled"
            :placeholder="$placeholder"
            maxlength="{{ $maxLength }}"
            {{ $inputAttributes }}
        />
    @endif
</div>
