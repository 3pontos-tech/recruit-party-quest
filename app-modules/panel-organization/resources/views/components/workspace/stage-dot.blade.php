@props([
    'type',
])

@php
    use He4rt\Recruitment\Stages\Enums\StageTypeEnum;

    $tone = match ($type) {
        StageTypeEnum::Screening => 'bg-yellow-500',
        StageTypeEnum::Assessment => 'bg-blue-500',
        StageTypeEnum::Interview, StageTypeEnum::Hired => 'bg-emerald-500',
        StageTypeEnum::Offer => 'bg-green-500',
        StageTypeEnum::HiddenStage => 'bg-red-500',
        default => 'bg-gray-500',
    };
@endphp

<span {{ $attributes->class([$tone, 'inline-block size-2 shrink-0 rounded-full']) }}></span>
