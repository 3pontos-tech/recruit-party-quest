@props([
    'question',
    'name' => null,
    'value' => null,
    'disabled' => false,
])

@php
    $settings = $question->settings ?? [];
    $layout = $settings['layout'] ?? 'radio';
    $choices = $settings['choices'] ?? [];
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

    @if ($layout === 'dropdown')
        <select name="{{ $inputName }}" id="{{ $inputName }}" @if ($disabled) disabled @endif class="hp-input">
            <option value="">{{ __('screening::question_types.single_choice.select_placeholder') }}</option>
            @foreach ($choices as $choice)
                <option value="{{ $choice['value'] }}" @if ($value === $choice['value']) selected @endif>
                    {{ $choice['label'] }}
                </option>
            @endforeach
        </select>
    @else
        <div class="hp-radio-group hp-radio-group--vertical">
            @foreach ($choices as $choice)
                <x-he4rt::radio
                    :name="$inputName"
                    :value="$choice['value']"
                    :label="$choice['label']"
                    :checked="$value === $choice['value']"
                    :disabled="$disabled"
                />
            @endforeach
        </div>
    @endif
</div>
