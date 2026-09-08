@props([
    'stages',
    'counts' => [],
    'activeStageId' => '',
    'clickable' => false,
    'height' => 'h-3',
])

@php
    use He4rt\Recruitment\Stages\Enums\StageTypeEnum;

    $tone = fn (StageTypeEnum $type): string => match ($type) {
        StageTypeEnum::Screening => 'bg-yellow-500',
        StageTypeEnum::Assessment => 'bg-blue-500',
        StageTypeEnum::Interview, StageTypeEnum::Hired => 'bg-emerald-500',
        StageTypeEnum::Offer => 'bg-green-500',
        StageTypeEnum::HiddenStage => 'bg-red-500',
        default => 'bg-gray-500',
    };
@endphp

<div {{ $attributes->class(['flex gap-px overflow-hidden rounded-full', $height]) }}>
    @foreach ($stages as $stage)
        @php
            $count = $counts[$stage->getKey()] ?? 0;
            $classes =
                ($count > 0 ? $tone($stage->stage_type) : 'bg-outline-low/20') .
                ($activeStageId !== '' && $activeStageId !== $stage->getKey() ? ' opacity-30' : '');
        @endphp

        @if ($clickable)
            <button
                type="button"
                wire:click="filterStage('{{ $stage->getKey() }}')"
                title="{{ $stage->name }}: {{ $count }}"
                class="{{ $classes }} transition hover:brightness-110"
                style="flex: {{ max($count, 0.15) }} 1 0"
            >
                <span class="sr-only">{{ $stage->name }}</span>
            </button>
        @else
            <span
                title="{{ $stage->name }}: {{ $count }}"
                class="{{ $classes }}"
                style="flex: {{ max($count, 0.15) }} 1 0"
            ></span>
        @endif
    @endforeach
</div>
