@props([
    'question',
    'name' => null,
    'value' => null,
    'disabled' => false,
])

@php
    $settings = $question->settings ?? [];
    $min = $settings['min'] ?? null;
    $max = $settings['max'] ?? null;
    $step = $settings['step'] ?? null;
    $prefix = $settings['prefix'] ?? null;
    $suffix = $settings['suffix'] ?? null;
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

    <div class="flex items-center gap-2">
        @if ($prefix)
            <x-he4rt::text class="text-text-medium">{{ $prefix }}</x-he4rt::text>
        @endif

        <x-he4rt::input
            type="number"
            :name="$inputName"
            :id="$inputName"
            :disabled="$disabled"
            min="{{ $min }}"
            max="{{ $max }}"
            step="{{ $step }}"
            value="{{ $value }}"
        />

        @if ($suffix)
            <x-he4rt::text class="text-text-medium">{{ $suffix }}</x-he4rt::text>
        @endif
    </div>
</div>
