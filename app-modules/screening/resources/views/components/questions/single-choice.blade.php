@props([
    'question',
    'disabled' => false,
])

@php
    $settings = $question->settings ?? [];
    $layout = $settings['layout'] ?? 'radio';
    $choices = $settings['choices'] ?? [];

    $wrapperAttributes = $attributes->only('class');
    $inputAttributes = $attributes->except('class');
@endphp

<div {{ $wrapperAttributes->class('screening-question') }}>
    <div class="mb-2 flex items-center justify-between">
        <x-he4rt::heading size="2xs">
            <x-screening::questions.question-base :$question />
        </x-he4rt::heading>
    </div>

    @if ($layout === 'dropdown')
        <select
            {{ $inputAttributes->class('hp-input') }}
            @if ($disabled) disabled @endif
            @if ($question->is_required && !$disabled) required @endif
        >
            <option value="">{{ __('screening::question_types.single_choice.select_placeholder') }}</option>
            @foreach ($choices as $choice)
                <option value="{{ $choice['value'] }}">
                    {{ $choice['label'] }}
                </option>
            @endforeach
        </select>
    @else
        <div class="hp-radio-group hp-radio-group--vertical">
            @foreach ($choices as $choice)
                <x-he4rt::radio
                    :value="$choice['value']"
                    :label="$choice['label']"
                    :disabled="$disabled"
                    :required="$question->is_required && !$disabled && $loop->first"
                    :attributes="$inputAttributes"
                />
            @endforeach
        </div>
    @endif
</div>
