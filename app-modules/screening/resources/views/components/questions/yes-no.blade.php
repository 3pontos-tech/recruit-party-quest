@props([
    'question',
    'name' => null,
    'value' => null,
    'disabled' => false,
])

@php
    $inputName = $name ?? "answers[{$question->id}]";
@endphp

<div {{ $attributes->class('screening-question') }}>
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
            :name="$inputName"
            value="yes"
            :label="__('screening::question_types.yes_no.yes')"
            :checked="$value === 'yes' || $value === true || $value === 1"
            :disabled="$disabled"
        />

        <x-he4rt::radio
            :name="$inputName"
            value="no"
            :label="__('screening::question_types.yes_no.no')"
            :checked="$value === 'no' || $value === false || $value === 0"
            :disabled="$disabled"
        />
    </div>
</div>
