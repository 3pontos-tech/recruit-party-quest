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
        selected: @entangle('responses.' . $question->id).live,
        max: @js($maxSelections),
        isDisabled: @js($disabled),

        init() {
            if (! Array.isArray(this.selected)) {
                this.selected = []
            }
        },

        toggle(value) {
            const index = this.selected.indexOf(value)

            if (index === -1) {
                if (this.max === null || this.selected.length < this.max) {
                    this.selected = [...this.selected, value]
                }
            } else {
                this.selected = this.selected.filter((v) => v !== value)
            }
        },

        isChecked(value) {
            return Array.isArray(this.selected) && this.selected.includes(value)
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
                $escapedValue = $choice['value'];
            @endphp

            <label
                class="hp-checkbox-label"
                :class="{ 'hp-checkbox-label--disabled': shouldDisable(@js($escapedValue)) }"
            >
                <input
                    type="checkbox"
                    class="hp-checkbox"
                    :checked="isChecked(@js($escapedValue))"
                    @click="toggle(@js($escapedValue))"
                    :disabled="shouldDisable(@js($escapedValue))"
                    value="{{ $escapedValue }}"
                    {{ $inputAttributes->except(['x-model', 'wire:model']) }}
                />
                <x-he4rt::text>{{ $choice['label'] }}</x-he4rt::text>
            </label>
        @endforeach
    </div>
</div>
