@props([
    'record',
])

@php
    /** @var \He4rt\Applications\Models\Application $record */
    $evaluations = $record
        ->evaluations()
        ->with(['evaluator', 'stage'])
        ->orderBy('created_at')
        ->get();

    $hasEvaluations = $evaluations->isNotEmpty();
    $evaluationsByStage = $evaluations->groupBy('stage_id');

    $criteriaLabels = [
        'technical_skills' => __('panel-organization::view.tabs.feedbacks.criteria.technical_skills'),
        'communication' => __('panel-organization::view.tabs.feedbacks.criteria.communication'),
        'problem_solving' => __('panel-organization::view.tabs.feedbacks.criteria.problem_solving'),
        'culture_fit' => __('panel-organization::view.tabs.feedbacks.criteria.culture_fit'),
    ];

    $criteriaColors = [
        1 => 'bg-red-500',
        2 => 'bg-yellow-500',
        3 => 'bg-blue-500',
        4 => 'bg-green-500',
        5 => 'bg-primary-500',
    ];
@endphp

<x-filament::section icon="heroicon-o-clipboard-document-list" icon-color="info">
    <x-slot name="heading">
        {{ __('panel-organization::view.tabs.feedbacks.title') }}
    </x-slot>

    <x-slot name="description">
        {{ __('panel-organization::view.tabs.feedbacks.subtitle') }}
    </x-slot>

    @if ($hasEvaluations)
        <x-slot name="afterHeader">
            <x-he4rt::tag size="sm">
                {{ trans_choice('panel-organization::view.tabs.feedbacks.count', $evaluations->count(), ['count' => $evaluations->count()]) }}
            </x-he4rt::tag>
        </x-slot>

        @foreach ($evaluationsByStage as $stageId => $stageEvaluations)
            @php
                $stage = $stageEvaluations->first()->stage;
            @endphp

            <div class="mb-6">
                <h4 class="text-text-high mb-4 text-base font-semibold">
                    {{ $stage?->name ?? __('panel-organization::view.tabs.feedbacks.unknown_stage') }}
                </h4>

                @foreach ($stageEvaluations as $evaluation)
                    <x-filament::section
                        :icon="\Filament\Support\Icons\Heroicon::ClipboardDocumentCheck"
                        icon-color="info"
                        class="mb-4"
                    >
                        <x-slot name="heading">
                            {{ $evaluation->evaluator?->name ?? __('panel-organization::view.tabs.feedbacks.unknown_evaluator') }}
                        </x-slot>

                        <x-slot name="afterHeader">
                            <div class="flex items-center gap-2">
                                <x-filament::badge :color="$evaluation->overall_rating->getColor()">
                                    {{ $evaluation->overall_rating->getLabel() }}
                                </x-filament::badge>
                                <span class="text-text-medium text-xs">
                                    {{ $evaluation->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                        </x-slot>

                        {{-- Criteria Scores --}}
                        @if (! empty($evaluation->criteria_scores))
                            <div class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-2">
                                @foreach ($criteriaLabels as $key => $label)
                                    @php
                                        $score = (int) ($evaluation->criteria_scores[$key] ?? 0);
                                        $score = max(1, min(5, $score));
                                        $color = $criteriaColors[$score];
                                        $width = ($score / 5) * 100;
                                    @endphp

                                    <div class="bg-elevation-02dp border-outline-low rounded-lg border p-3">
                                        <div class="mb-1 flex items-center justify-between">
                                            <span class="text-text-medium text-xs font-medium">{{ $label }}</span>
                                            <span class="text-text-high text-xs font-bold">{{ $score }}/5</span>
                                        </div>
                                        <div class="bg-elevation-01dp h-2 w-full overflow-hidden rounded-full">
                                            <div
                                                class="{{ $color }} h-full transition-all duration-500"
                                                style="width: {{ $width }}%"
                                            ></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Text fields: strengths, concerns, recommendation, notes --}}
                        @foreach (['strengths', 'concerns', 'recommendation', 'notes'] as $field)
                            @if ($evaluation->$field)
                                <div class="border-outline-low mb-3 border-t pt-3 first:border-t-0 first:pt-0">
                                    <p class="text-text-medium mb-1 text-xs font-semibold tracking-wider uppercase">
                                        {{ __('panel-organization::view.tabs.feedbacks.fields.' . $field) }}
                                    </p>
                                    <p class="text-text-high text-sm leading-relaxed">
                                        {{ $evaluation->$field }}
                                    </p>
                                </div>
                            @endif
                        @endforeach
                    </x-filament::section>
                @endforeach
            </div>
        @endforeach
    @else
        <x-filament::section :secondary="true">
            <div class="py-4 text-center">
                <x-he4rt::icon
                    :icon="\Filament\Support\Icons\Heroicon::ClipboardDocumentList"
                    size="lg"
                    class="text-text-low mx-auto"
                />
                <h4 class="text-text-high mt-4 text-lg font-medium">
                    {{ __('panel-organization::view.tabs.feedbacks.empty_title') }}
                </h4>
                <p class="text-text-medium mt-1 text-sm">
                    {{ __('panel-organization::view.tabs.feedbacks.empty_text') }}
                </p>
            </div>
        </x-filament::section>
    @endif
</x-filament::section>
