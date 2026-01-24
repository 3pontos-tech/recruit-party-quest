@props([
    'question',
    'disabled' => false,
    'message' => '',
])

@php
    $settings = $question->settings ?? [];
    $minSelections = $settings['min_selections'] ?? 0;
    $maxSelections = $settings['max_selections'] ?? null;
    $choices = $settings['choices'] ?? [];

    $wrapperAttributes = $attributes->only('class');
    $inputAttributes = $attributes->except('class');
@endphp

<div
    {{ $wrapperAttributes->class('screening-question') }}
    x-data="{
        selected: [],
        max: @js($maxSelections),
        isDisabled: @js($disabled),
        toggle(value) {
            const index = this.selected.indexOf(value)
            if (index === -1) {
                this.selected.push(value)
            } else {
                this.selected.splice(index, 1)
            }
        },
        isChecked(value) {
            return this.selected.includes(value)
        },
        shouldDisable(value) {
            if (this.isDisabled) return true
            if (this.max === null) return false
            return this.selected.length >= this.max && ! this.isChecked(value)
        },
    }"
>
    <div class="mb-2 flex items-center justify-between">
        <x-he4rt::heading size="2xs">
            <x-screening::questions.question-base :$question />
        </x-he4rt::heading>
    </div>

    @if ($minSelections > 0 || $maxSelections !== null)
        <x-he4rt::text class="text-text-medium mb-2 text-sm">
            @if ($minSelections > 0 && $maxSelections !== null)
                {{ __('screening::question_types.multiple_choice.select_between', ['min' => $minSelections, 'max' => $maxSelections]) }}
            @elseif ($minSelections > 0)
                {{ __('screening::question_types.multiple_choice.select_min', ['min' => $minSelections]) }}
            @elseif ($maxSelections !== null)
                {{ __('screening::question_types.multiple_choice.select_max', ['max' => $maxSelections]) }}
            @endif
        </x-he4rt::text>
    @endif

    <div class="hp-checkbox-group">
        @foreach ($choices as $choice)
            @php
                $escapedValue = json_encode($choice['value']);
            @endphp

            <label
                class="hp-checkbox-label"
                :class="{ 'hp-checkbox-label--disabled': shouldDisable({{ $escapedValue }}) }"
            >
                <input
                    type="checkbox"
                    value="{{ $choice['value'] }}"
                    class="hp-checkbox"
                    x-model="selected"
                    :disabled="shouldDisable({{ $escapedValue }})"
                    {{ $inputAttributes }}
                />
                <x-he4rt::text>{{ $choice['label'] }}</x-he4rt::text>
            </label>
        @endforeach
    </div>
</div>
