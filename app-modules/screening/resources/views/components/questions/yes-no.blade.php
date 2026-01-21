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

        @if ($question->is_knockout)
            <x-he4rt::text class="text-helper-warning font-family-secondary shrink-0 self-start text-sm">
                (pergunta eliminatória)
            </x-he4rt::text>
        @endif
    </div>

    <div class="hp-radio-group">
        <x-he4rt::radio
            value="yes"
            :label="__('screening::question_types.yes_no.yes')"
            :disabled="$disabled"
            {{ $inputAttributes }}
        />

        <x-he4rt::radio
            value="no"
            :label="__('screening::question_types.yes_no.no')"
            :disabled="$disabled"
            {{ $inputAttributes }}
        />
    </div>
</div>
