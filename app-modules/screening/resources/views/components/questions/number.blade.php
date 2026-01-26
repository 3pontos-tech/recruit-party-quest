@props([
    'question',
    'disabled' => false,
])

@php
    $settings = $question->settings ?? [];
    $min = $settings['min'] ?? null;
    $max = $settings['max'] ?? null;
    $step = $settings['step'] ?? null;
    $prefix = $settings['prefix'] ?? null;
    $suffix = $settings['suffix'] ?? null;

    $wrapperAttributes = $attributes->only('class');
    $inputAttributes = $attributes->except('class');
@endphp

<div {{ $wrapperAttributes->class('screening-question') }}>
    <div class="mb-2 flex items-center justify-between">
        <x-he4rt::heading size="2xs">
            <x-screening::questions.question-base :$question />
        </x-he4rt::heading>
    </div>

    <div class="flex items-center gap-2">
        @if ($prefix)
            <x-he4rt::text class="text-text-medium">{{ $prefix }}</x-he4rt::text>
        @endif

        <x-he4rt::input
            type="number"
            :disabled="$disabled"
            min="{{ $min }}"
            max="{{ $max }}"
            step="{{ $step }}"
            :attributes="$inputAttributes"
        />

        @if ($suffix)
            <x-he4rt::text class="text-text-medium">{{ $suffix }}</x-he4rt::text>
        @endif
    </div>
</div>
