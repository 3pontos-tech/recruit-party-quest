@php
    use He4rt\Applications\Models\Application;
    use He4rt\Recruitment\Stages\Models\Stage;
    use Illuminate\Support\Collection;
@endphp

@props([
    'record',
])

@php
    /** @var Application $record */
    $currentStage = $record->currentStage;

    /** @var Collection<int, Stage>  $stages */
    $stages = $record->requisition
        ->stages()
        ->orderBy('display_order')
        ->get();

    $currentStageIndex = $currentStage ? $stages->search(fn ($stage) => $stage->name === $currentStage->name) : 0;

    if ($currentStageIndex === false) {
        $currentStageIndex = 0;
    }
@endphp

<div class="bg-surface-01dp border-outline-low space-y-4 rounded-lg border p-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <x-he4rt::icon :icon="\Filament\Support\Icons\Heroicon::ChartBar" size="sm" class="text-icon-medium" />
            <h3 class="text-text-high text-sm font-semibold">{{ __('panel-organization::view.pipeline.title') }}</h3>
        </div>
        @if ($currentStage)
            <x-he4rt::tag size="sm">{{ $currentStageIndex + 1 }} / {{ $stages->count() }}</x-he4rt::tag>
        @endif
    </div>
    <div class="space-y-4 rounded-lg">
        {{-- Progress Bar Overview --}}
        <div class="space-y-2">
            <div class="flex justify-between text-xs">
                <span class="text-text-medium">{{ __('panel-organization::view.pipeline.overall_progress') }}</span>
                <span class="text-text-high font-semibold">
                    {{ round((($currentStageIndex + 1) / $stages->count()) * 100) }}%
                </span>
            </div>
            <div class="border-outline-low h-3 w-full overflow-hidden rounded-full border">
                <div
                    class="h-full bg-gray-500 transition-all duration-500"
                    style="width: {{ round((($currentStageIndex + 1) / $stages->count()) * 100) }}%"
                ></div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="relative space-y-1">
            @foreach ($stages as $index => $stage)
                @php
                    $isCompleted = $index < $currentStageIndex;
                    $isCurrent = $index === $currentStageIndex;
                    $isFuture = $index > $currentStageIndex;
                @endphp

                <div class="group relative flex items-start gap-3">
                    {{-- Timeline Line Segment --}}
                    @if (! $loop->last)
                        <div
                            class="{{ $isCompleted ? 'bg-primary' : 'bg-outline-low/30' }} absolute top-8 left-4 z-10 h-15 w-0.5"
                            aria-hidden="true"
                        ></div>
                    @endif

                    {{-- Indicator Dot --}}
                    <div
                        class="{{ $isCompleted || $isCurrent ? 'bg-primary border-primary' : 'bg-elevation-surface border-outline-low' }} relative z-20 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 transition-colors duration-300"
                    >
                        @if ($isCompleted)
                            <x-he4rt::icon
                                :icon="\Filament\Support\Icons\Heroicon::Check"
                                size="sm"
                                class="text-white"
                            />
                        @elseif ($isCurrent)
                            <div class="h-2.5 w-2.5 animate-pulse rounded-full bg-white"></div>
                        @else
                            <div class="bg-outline-low h-2 w-2 rounded-full"></div>
                        @endif
                    </div>

                    {{-- Stage Content --}}
                    <div class="flex-1 pb-3">
                        <div class="flex items-center justify-between">
                            <h4
                                class="{{ $isCompleted || $isCurrent ? 'text-text-high' : 'text-text-low' }} text-sm font-medium transition-colors duration-300"
                            >
                                {{ $stage->name }}
                            </h4>

                            {{-- Stage Status Badge --}}
                            @if ($isCurrent)
                                <x-he4rt::tag size="xs" class="p-1">
                                    {{ __('panel-organization::view.pipeline.current') }}
                                </x-he4rt::tag>
                            @elseif ($isCompleted)
                                <x-he4rt::tag size="xs" class="p-1">
                                    {{ __('panel-organization::view.pipeline.done') }}
                                </x-he4rt::tag>
                            @endif
                        </div>

                        @if ($stage->description)
                            <p
                                class="{{ $isCompleted || $isCurrent ? 'text-text-medium' : 'text-text-low/60' }} mt-1 text-xs transition-colors duration-300"
                            >
                                {{ $stage->description }}
                            </p>
                        @endif

                        {{-- Current Stage Extra Info --}}
                        @if ($isCurrent)
                            <div class="mt-2 space-y-1">
                                <div class="text-text-medium flex items-center gap-2 text-xs">
                                    <x-he4rt::icon
                                        :icon="\Filament\Support\Icons\Heroicon::Clock"
                                        size="sm"
                                        class="text-primary"
                                    />
                                    <span>
                                        {{ __('panel-organization::view.pipeline.active_since', ['date' => $record->updated_at->format('M j, Y')]) }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Stage History Summary --}}
        <div class="border-outline-low border-t pt-3">
            <div class="flex items-center justify-between text-xs">
                <span class="text-text-medium">
                    {{ __('panel-organization::view.pipeline.application_submitted') }}
                </span>
                <span class="text-text-high font-medium">{{ $record->created_at->format('M j, Y') }}</span>
            </div>
            <div class="mt-1 flex items-center justify-between text-xs">
                <span class="text-text-medium">{{ __('panel-organization::view.pipeline.last_updated') }}</span>
                <span class="text-text-high font-medium">{{ $record->updated_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
