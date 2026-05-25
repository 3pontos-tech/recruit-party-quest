@php
    use App\Enums\FilamentPanel;
    use Filament\Support\Icons\Heroicon;
    use He4rt\Applications\Enums\ApplicationStatusEnum;
    use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
    use He4rt\Applications\Models\Application;
    use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
    use He4rt\Recruitment\Stages\Models\Stage;
    use Illuminate\Support\Collection;
@endphp

@props([
    'record',
])

@php
    /** @var Application $record */
    $currentStage = $record->currentStage;
    $organizationPanel =
        filament()
            ->getCurrentPanel()
            ?->getId() === FilamentPanel::Organization->value;
    /** @var Collection<int, Stage>  $stages */
    $pipelineStages = $record->getPipelineStages();

    $terminalStageTypes = [StageTypeEnum::Rejected, StageTypeEnum::Declined];
    $currentStageType = $currentStage?->stage_type;
    $isCurrentTerminal = $currentStageType !== null && in_array($currentStageType, $terminalStageTypes, true);

    $keepStage = function (Stage $stage) use ($currentStageType, $isCurrentTerminal, $terminalStageTypes): bool {
        $stageIsTerminal = in_array($stage->stage_type, $terminalStageTypes, true);

        if (! $stageIsTerminal) {
            return true;
        }

        return $isCurrentTerminal && $stage->stage_type === $currentStageType;
    };

    if ($organizationPanel) {
        $pipelineStages->loadMissing('screeningQuestions');
        $stages = $pipelineStages
            ->where('active', true)
            ->filter($keepStage)
            ->values();
    } else {
        $stages = $pipelineStages
            ->where('hidden', false)
            ->where('active', true)
            ->filter($keepStage)
            ->values();
    }

    $isRejected = ! $organizationPanel && $record->status === ApplicationStatusEnum::Rejected;

    $isScreeningKnockout = $record->rejection_reason_category === RejectionReasonCategoryEnum::ScreeningKnockout;

    $currentStageIndex = 0;

    $showRejectionInfo = $record->status === ApplicationStatusEnum::Rejected || ($currentStage !== null && in_array($currentStage->stage_type, $terminalStageTypes, true));

    if ($currentStage) {
        $index = $stages->search(fn ($stage) => $stage->id === $currentStage->id);

        if ($index !== false) {
            $currentStageIndex = $index;
        } elseif (! $organizationPanel) {
            $lastVisible = $stages->filter(fn ($s) => $s->display_order <= $currentStage->display_order)->last();
            if ($lastVisible) {
                $fallbackIndex = $stages->search(fn ($s) => $s->id === $lastVisible->id);
                $currentStageIndex = $fallbackIndex !== false ? $fallbackIndex : 0;
            }
        }
    }
@endphp

@if ($stages->isNotEmpty())
    <div
        class="bg-elevation-01dp/64 border-outline-light dark:border-outline-dark space-y-4 rounded-lg border p-4 backdrop-blur-md"
    >
        <div class="flex items-center justify-between gap-2">
            <div class="flex min-w-0 items-center gap-2">
                <x-he4rt::icon
                    :icon="$isRejected ? Heroicon::InformationCircle : Heroicon::ChartBar"
                    size="sm"
                    class="text-icon-medium shrink-0"
                />
                <h3 class="text-text-high text-sm font-semibold">
                    {{ __('panel-organization::view.pipeline.' . ($isRejected ? 'rejected_title' : 'title')) }}
                </h3>
            </div>
            @if (! $isRejected && $currentStage)
                <x-he4rt::tag size="sm" class="shrink-0 whitespace-nowrap">
                    {{ $currentStageIndex + 1 }} / {{ max($stages->count(), 1) }}
                </x-he4rt::tag>
            @endif
        </div>

        @if ($isRejected)
            {{-- Rejection Card (candidate panel only) --}}
            <div class="space-y-3">
                @if ($isScreeningKnockout)
                    {{-- Screening knockout: show a neutral message and hide the internal reason/details --}}
                    <p class="text-text-medium text-sm leading-relaxed">
                        {{ __('panel-organization::view.pipeline.rejected_screening_message') }}
                    </p>
                @else
                    @if ($record->rejection_reason_category)
                        <div class="space-y-1">
                            <span class="text-text-medium text-xs">
                                {{ __('panel-organization::view.pipeline.rejected_reason') }}
                            </span>
                            <p class="text-text-high text-sm font-medium">
                                {{ $record->rejection_reason_category->getLabel() }}
                            </p>
                        </div>
                    @endif

                    @if ($record->rejection_reason_details)
                        <div class="space-y-1">
                            <span class="text-text-medium text-xs">
                                {{ __('panel-organization::view.pipeline.rejected_details') }}
                            </span>
                            <p class="text-text-medium text-sm leading-relaxed">
                                {{ $record->rejection_reason_details }}
                            </p>
                        </div>
                    @endif
                @endif
            </div>
        @else
            {{-- Active timeline --}}
            <div class="space-y-4 rounded-lg">
                {{-- Progress Bar Overview --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-text-medium">
                            {{ __('panel-organization::view.pipeline.overall_progress') }}
                        </span>
                        <span class="text-text-high font-semibold">
                            {{ round((($currentStageIndex + 1) / max($stages->count(), 1)) * 100) }}%
                        </span>
                    </div>
                    <div class="border-outline-low h-3 w-full overflow-hidden rounded-full border">
                        <div
                            class="bg-outline-medium h-full transition-all duration-500"
                            style="width: {{ round((($currentStageIndex + 1) / max($stages->count(), 1)) * 100) }}%"
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
                                    class="{{ $isCompleted ? 'bg-primary' : 'bg-outline-low/30' }} absolute top-8 left-4 z-10 h-23 w-0.5 -translate-x-1/2"
                                    aria-hidden="true"
                                ></div>
                            @endif

                            {{-- Indicator Dot --}}
                            <div
                                class="{{ $isCompleted || $isCurrent ? 'bg-outline-medium border-outline-medium' : 'bg-elevation-surface border-outline-low' }} relative z-20 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 transition-colors duration-300"
                            >
                                @if ($isCompleted)
                                    <x-he4rt::icon
                                        :icon="$organizationPanel ? $stage->stage_type->getIcon() : Heroicon::Check"
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
                            <div class="flex-1 pb-6">
                                <div class="flex items-center justify-between">
                                    <h4
                                        class="{{ $isCompleted || $isCurrent ? 'text-text-high' : 'text-text-low' }} text-sm font-medium transition-colors duration-300"
                                    >
                                        {{ $stage->name }}
                                    </h4>

                                    {{-- Stage Status Badge --}}
                                    @if ($isCurrent)
                                        <x-he4rt::tag size="xs">
                                            {{ __('panel-organization::view.pipeline.current') }}
                                        </x-he4rt::tag>
                                    @elseif ($isCompleted)
                                        <x-he4rt::tag size="xs">
                                            {{ __('panel-organization::view.pipeline.done') }}
                                        </x-he4rt::tag>
                                    @endif
                                </div>

                                @if ($stage->description)
                                    <p
                                        class="{{ $isCompleted || $isCurrent ? 'text-text-medium' : 'text-text-low/60' }} mt-1 text-xs leading-relaxed transition-colors duration-300"
                                    >
                                        {{ $stage->description }}
                                    </p>
                                @endif

                                @if ($organizationPanel)
                                    <div class="text-text-low mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                                        <span class="flex items-center gap-1">
                                            <x-he4rt::icon :icon="Heroicon::Clock" size="xs" />
                                            {{ trans_choice('panel-organization::view.pipeline.duration_days', $stage->expected_duration_days, ['count' => $stage->expected_duration_days]) }}
                                        </span>
                                        @if ($stage->screeningQuestions->isNotEmpty())
                                            <span class="flex items-center gap-1">
                                                <x-he4rt::icon :icon="Heroicon::ClipboardDocumentList" size="xs" />
                                                {{ trans_choice('panel-organization::view.pipeline.screening_questions', $stage->screeningQuestions->count(), ['count' => $stage->screeningQuestions->count()]) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Current Stage Extra Info --}}
                                @if ($isCurrent)
                                    <div class="mt-2 space-y-1">
                                        <div class="text-text-medium flex items-center gap-2 text-xs">
                                            <x-he4rt::tag :icon="Heroicon::Clock" size="xs" variant="ghost">
                                                {{ __('panel-organization::view.pipeline.active_since', ['date' => $record->updated_at->translatedFormat('M j, Y')]) }}
                                            </x-he4rt::tag>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($showRejectionInfo && ($record->rejection_reason_category || $record->rejection_reason_details))
                    {{-- Rejection Info (shown when status=Rejected or current stage is a Rejected type) --}}
                    <div class="border-outline-low/60 bg-elevation-02dp/40 space-y-1.5 rounded-lg border p-3">
                        <div class="text-text-medium text-xs font-semibold tracking-wider uppercase">
                            {{ __('panel-organization::view.pipeline.rejected_reason') }}
                        </div>
                        @if ($record->rejection_reason_category)
                            <p class="text-text-high text-sm font-medium">
                                {{ $record->rejection_reason_category->getLabel() }}
                            </p>
                        @endif

                        @if ($record->rejection_reason_details)
                            <p class="text-text-medium text-sm leading-relaxed">
                                {{ $record->rejection_reason_details }}
                            </p>
                        @endif
                    </div>
                @endif

                {{-- Stage History Summary --}}
                <div class="border-outline-low border-t pt-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-text-medium">
                            {{ __('panel-organization::view.pipeline.application_submitted') }}
                        </span>
                        <span class="text-text-high font-medium">
                            {{ $record->created_at->translatedFormat('M j, Y') }}
                        </span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-xs">
                        <span class="text-text-medium">
                            {{ __('panel-organization::view.pipeline.last_updated') }}
                        </span>
                        <span class="text-text-high font-medium">{{ $record->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
