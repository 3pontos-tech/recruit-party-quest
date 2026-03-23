@php
    /** @var Application $record */
    use He4rt\Applications\Models\Application;

    $stages = $record->getPipelineStages();

    // Safe access with fallback to Application status
    $statusLabel = $record->currentStage?->stage_type?->getLabel() ?? 'N/A';
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

                    if ($isCompleted || $isCurrent) {
                        $colorClass = $stage->stage_type->getTailwindColorClass();
                        $extraClasses = 'opacity-100';
                    } else {
                        $colorClass = 'bg-gray-200 dark:bg-gray-700';
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
        <div class="flex items-center gap-1.5">
            <div class="h-1.5 flex-1 rounded-full bg-gray-300 opacity-40 dark:bg-gray-600"></div>
            <span class="text-xs text-gray-400 dark:text-gray-500">
                {{ __('panel-organization::view.pipeline.no_stages') }}
            </span>
        </div>
    @endif
</div>
