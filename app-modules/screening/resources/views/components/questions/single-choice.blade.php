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
            {{ $question->question_text }}
            @if ($question->is_required)
                <span class="text-helper-error">*</span>
            @endif
        </x-he4rt::heading>

        @if ($question->is_knockout)
            <x-he4rt::text class="text-helper-warning font-family-secondary shrink-0 self-start text-sm">
                {{ __('screening::question_types.knockout_helper') }}
            </x-he4rt::text>
        @endif
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
