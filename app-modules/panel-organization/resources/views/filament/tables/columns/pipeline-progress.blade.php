@php
    /** @var Application $record */
    use He4rt\Applications\Models\Application;
    use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
    $stages = $record->getPipelineStages();
    $statusLabel = $record->currentStage->stage_type->getLabel();

    if ($stages->isEmpty()) {
        $stages = collect([]);
    }
@endphp

<div class="flex w-full flex-col gap-1.5 px-5">
    <span class="truncate text-xs font-medium text-gray-700 dark:text-gray-300">
        {{ $statusLabel }}
    </span>
    @if ($stages->isNotEmpty())
        <div class="grid auto-cols-fr grid-flow-col gap-1">
            @foreach ($stages as $stage)
                @php
                    $isCompleted = $record->isStageCompleted($stage);
                    $isCurrent = $record->isCurrentStage($stage);

                    if ($isCompleted) {
                        $colorClass = StageTypeEnum::getColorClassFromString($stage->stage_type->value);
                        $extraClasses = 'opacity-100';
                    } elseif ($isCurrent) {
                        $colorClass = StageTypeEnum::getColorClassFromString($stage->stage_type->value);
                        $extraClasses = 'opacity-100';
                    } else {
                        $colorClass = 'bg-gray-200';
                        $extraClasses = 'opacity-10';
                    }
                @endphp

                <div
                    class="{{ $colorClass }} {{ $extraClasses }} h-1.5 flex-1 rounded-full"
                    title="{{ $stage->stage_type->getLabel() }}"
                ></div>
            @endforeach
        </div>
    @else
        <div class="flex items-center gap-1">
            <div class="bg-black-300 h-1.5 flex-1 rounded-full opacity-60"></div>
        </div>
    @endif
</div>
