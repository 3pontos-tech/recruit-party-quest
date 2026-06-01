@php
    use Filament\Support\Icons\Heroicon;
    use He4rt\Feedback\Enums\EvaluationRatingEnum;
    use He4rt\Recruitment\Stages\Enums\StageTypeEnum;

    $stagesCount = $this->stages->count();
    $progressPct = $stagesCount > 0 ? ($this->stageIndex / $stagesCount) * 100 : 0;
    $isCurrentRealStage = $this->application->currentStage?->id === $this->stage?->id;

    $stageTypeClasses = fn (StageTypeEnum $type): string => match ($type) {
        StageTypeEnum::New => 'bg-gray-500/10 text-gray-500 ring-gray-500/20',
        StageTypeEnum::Screening => 'bg-yellow-500/10 text-yellow-600 ring-yellow-500/20',
        StageTypeEnum::Assessment => 'bg-blue-500/10 text-blue-600 ring-blue-500/20',
        StageTypeEnum::Interview => 'bg-emerald-500/10 text-emerald-600 ring-emerald-500/20',
        StageTypeEnum::Offer => 'bg-green-500/10 text-green-600 ring-green-500/20',
        StageTypeEnum::Hired => 'bg-emerald-500/10 text-emerald-600 ring-emerald-500/20',
        StageTypeEnum::HiddenStage => 'bg-red-500/10 text-red-600 ring-red-500/20',
    };

    $ratingClasses = fn (EvaluationRatingEnum $rating): string => match ($rating) {
        EvaluationRatingEnum::StrongYes => 'bg-emerald-500/10 text-emerald-600 ring-emerald-500/20',
        EvaluationRatingEnum::Yes => 'bg-green-500/10 text-green-600 ring-green-500/20',
        EvaluationRatingEnum::Maybe => 'bg-amber-500/10 text-amber-600 ring-amber-500/20',
        EvaluationRatingEnum::No => 'bg-orange-500/10 text-orange-600 ring-orange-500/20',
        EvaluationRatingEnum::StrongNo => 'bg-red-500/10 text-red-600 ring-red-500/20',
    };
@endphp

<div class="space-y-5">
    @if ($this->stage)
        {{-- =========================== HEADER =========================== --}}
        <div class="border-outline-low bg-elevation-01dp relative overflow-hidden rounded-xl border">
            <div class="from-primary via-primary/40 absolute inset-x-0 top-0 h-1 bg-gradient-to-r to-transparent"></div>

            <div class="space-y-4 p-4">
                <div class="flex items-start gap-3">
                    <div
                        class="bg-primary/10 ring-primary/20 flex h-12 w-12 shrink-0 items-center justify-center rounded-xl ring-1"
                    >
                        <x-he4rt::icon :icon="$this->stage->stage_type->getIcon()" size="md" class="text-primary" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-text-high text-xl leading-tight font-semibold">
                                {{ $this->stage->name }}
                            </h3>
                            <span class="text-text-low shrink-0 text-sm tabular-nums">
                                {{ $this->stageIndex }} / {{ $stagesCount }}
                            </span>
                        </div>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span
                                class="{{ $stageTypeClasses($this->stage->stage_type) }} inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                            >
                                {{ $this->stage->stage_type->getLabel() }}
                            </span>
                            @if ($isCurrentRealStage)
                                <span class="text-success inline-flex items-center gap-1.5 text-sm font-medium">
                                    <span class="bg-success relative flex h-1.5 w-1.5">
                                        <span
                                            class="bg-success absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                                        ></span>
                                        <span class="bg-success relative inline-flex h-1.5 w-1.5 rounded-full"></span>
                                    </span>
                                    {{ __('panel-organization::view.pipeline.stage_detail.current_stage') }}
                                </span>
                            @endif

                            @if ($this->application->tracking_code)
                                <span class="text-text-low font-mono text-xs tracking-wider uppercase">
                                    {{ $this->application->tracking_code }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- progresso no pipeline --}}
                <div class="space-y-1.5">
                    <div class="bg-elevation-03dp h-1.5 w-full overflow-hidden rounded-full">
                        <div
                            class="bg-primary h-full rounded-full transition-all duration-500"
                            style="width: {{ $progressPct }}%"
                        ></div>
                    </div>
                </div>

                @if ($this->stage->description)
                    <p class="text-text-medium text-base leading-relaxed">
                        {{ $this->stage->description }}
                    </p>
                @endif

                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="bg-elevation-02dp text-text-medium inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-sm"
                    >
                        <x-he4rt::icon :icon="Heroicon::Clock" size="xs" class="text-icon-medium" />
                        {{ trans_choice('panel-organization::view.pipeline.duration_days', $this->stage->expected_duration_days, ['count' => $this->stage->expected_duration_days]) }}
                    </span>
                    @if ($this->stage->screeningQuestions->isNotEmpty())
                        <span
                            class="bg-elevation-02dp text-text-medium inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-sm"
                        >
                            <x-he4rt::icon
                                :icon="Heroicon::ClipboardDocumentList"
                                size="xs"
                                class="text-icon-medium"
                            />
                            {{ trans_choice('panel-organization::view.pipeline.screening_questions', $this->stage->screeningQuestions->count(), ['count' => $this->stage->screeningQuestions->count()]) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- =========================== HIDDEN WARNING =========================== --}}
        @if ($this->stage->hidden)
            <div class="border-warning/40 bg-warning/10 flex items-start gap-2.5 rounded-xl border p-3">
                <x-he4rt::icon :icon="Heroicon::LockClosed" size="sm" class="text-warning mt-0.5 shrink-0" />
                <p class="text-warning text-sm leading-relaxed">
                    {{ __('panel-organization::view.pipeline.stage_detail.hidden_warning') }}
                </p>
            </div>
        @endif

        @if ($this->isFutureStage)
            {{-- =========================== FUTURE EMPTY STATE =========================== --}}
            <div
                class="border-outline-light bg-elevation-01dp/50 rounded-xl border border-dashed px-6 py-10 text-center"
            >
                <div
                    class="bg-elevation-02dp ring-outline-low mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full ring-1"
                >
                    <x-he4rt::icon :icon="Heroicon::Clock" size="md" class="text-icon-low" />
                </div>
                <p class="text-text-medium text-base">
                    {{ __('panel-organization::view.pipeline.stage_detail.empty_future') }}
                </p>
            </div>
        @else
            {{-- =========================== TIMELINE =========================== --}}
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-text-high flex items-center gap-2 text-base font-semibold">
                        <x-he4rt::icon :icon="Heroicon::ListBullet" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.pipeline.stage_detail.timeline') }}
                    </h4>
                    @if ($this->timeline->isNotEmpty())
                        <span
                            class="bg-elevation-02dp text-text-medium rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums"
                        >
                            {{ $this->timeline->count() }}
                        </span>
                    @endif
                </div>

                @if ($this->timeline->isEmpty())
                    <div class="border-outline-low bg-elevation-01dp/50 rounded-lg border px-3 py-4 text-center">
                        <p class="text-text-low text-sm italic">
                            {{ __('panel-organization::view.pipeline.stage_detail.empty_notes') }}
                        </p>
                    </div>
                @else
                    <ol class="relative space-y-4 pl-1">
                        @foreach ($this->timeline as $event)
                            <li class="relative pl-6">
                                <div
                                    class="bg-elevation-01dp ring-primary absolute top-1 left-0 flex h-[15px] w-[15px] items-center justify-center rounded-full ring-2"
                                >
                                    <div class="bg-primary h-1.5 w-1.5 rounded-full"></div>
                                </div>
                                <div class="space-y-1.5">
                                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                        <time class="text-text-high text-sm font-medium tabular-nums">
                                            {{ $event->created_at->translatedFormat('d M Y · H:i') }}
                                        </time>
                                        <span class="text-text-low text-xs">
                                            {{ $event->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    @if ($event->fromStage)
                                        <div class="text-text-medium flex items-center gap-1.5 text-sm">
                                            <span class="truncate">{{ $event->fromStage->name }}</span>
                                            <x-he4rt::icon
                                                :icon="Heroicon::ArrowLongRight"
                                                size="xs"
                                                class="text-icon-low shrink-0"
                                            />
                                            <span class="text-text-high truncate font-medium">
                                                {{ $event->toStage->name }}
                                            </span>
                                        </div>
                                    @endif

                                    @if ($event->movedBy)
                                        <div class="flex items-center gap-1.5">
                                            <div
                                                class="bg-primary/15 text-primary flex h-5 w-5 items-center justify-center rounded-full text-xs font-semibold"
                                            >
                                                {{ mb_strtoupper(mb_substr($event->movedBy->name, 0, 1)) }}
                                            </div>
                                            <span class="text-text-medium text-sm">{{ $event->movedBy->name }}</span>
                                        </div>
                                    @endif

                                    @if ($event->notes)
                                        <div
                                            class="border-outline-low bg-elevation-02dp/50 text-text-medium rounded-lg border p-2.5 text-sm leading-relaxed"
                                        >
                                            “{{ $event->notes }}”
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>

            {{-- =========================== INTERVIEWERS =========================== --}}
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-text-high flex items-center gap-2 text-base font-semibold">
                        <x-he4rt::icon :icon="Heroicon::UserGroup" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.pipeline.stage_detail.interviewers') }}
                    </h4>
                    @if ($this->interviewers->isNotEmpty())
                        <span
                            class="bg-elevation-02dp text-text-medium rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums"
                        >
                            {{ $this->interviewers->count() }}
                        </span>
                    @endif
                </div>

                @if ($this->interviewers->isEmpty())
                    <div class="border-outline-low bg-elevation-01dp/50 rounded-lg border px-3 py-4 text-center">
                        <p class="text-text-low text-sm italic">
                            {{ __('panel-organization::view.pipeline.stage_detail.empty_interviewers') }}
                        </p>
                    </div>
                @else
                    <ul class="grid grid-cols-1 gap-2">
                        @foreach ($this->interviewers as $interviewer)
                            <li
                                class="border-outline-low bg-elevation-01dp hover:bg-elevation-02dp/60 flex items-center gap-3 rounded-lg border p-2.5 transition-colors"
                            >
                                <div
                                    class="bg-primary/15 text-primary flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-base font-semibold"
                                >
                                    {{ mb_strtoupper(mb_substr($interviewer->user?->name ?? '?', 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-text-high truncate text-sm font-medium">
                                        {{ $interviewer->user?->name ?? $interviewer->id }}
                                    </div>
                                    @if ($interviewer->user?->email)
                                        <div class="text-text-low truncate text-xs">
                                            {{ $interviewer->user->email }}
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- =========================== EVALUATIONS =========================== --}}
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-text-high flex items-center gap-2 text-base font-semibold">
                        <x-he4rt::icon :icon="Heroicon::Star" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.pipeline.stage_detail.evaluations') }}
                    </h4>
                    @if ($this->evaluations->isNotEmpty())
                        <span
                            class="bg-elevation-02dp text-text-medium rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums"
                        >
                            {{ $this->evaluations->count() }}
                        </span>
                    @endif
                </div>

                @if ($this->evaluations->isEmpty())
                    <div class="border-outline-low bg-elevation-01dp/50 rounded-lg border px-3 py-4 text-center">
                        <p class="text-text-low text-sm italic">
                            {{ __('panel-organization::view.pipeline.stage_detail.empty_evaluations') }}
                        </p>
                    </div>
                @else
                    <ul class="space-y-3">
                        @foreach ($this->evaluations as $evaluation)
                            <li class="border-outline-low bg-elevation-01dp overflow-hidden rounded-xl border">
                                <div
                                    class="border-outline-low/60 bg-elevation-02dp/40 flex items-center justify-between gap-2 border-b p-3"
                                >
                                    @if ($evaluation->overall_rating)
                                        <span
                                            class="{{ $ratingClasses($evaluation->overall_rating) }} inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                                        >
                                            {{ $evaluation->overall_rating->getLabel() }}
                                        </span>
                                    @else
                                        <span class="text-text-low text-xs italic">—</span>
                                    @endif
                                    <time class="text-text-low text-xs tabular-nums">
                                        {{ ($evaluation->submitted_at ?? $evaluation->created_at)?->translatedFormat('d M Y') }}
                                    </time>
                                </div>

                                <div class="space-y-2.5 p-3">
                                    @if ($evaluation->recommendation)
                                        <div class="border-primary/40 bg-primary/5 rounded-lg border-l-2 p-2.5">
                                            <p class="text-text-high text-sm leading-relaxed">
                                                {{ $evaluation->recommendation }}
                                            </p>
                                        </div>
                                    @endif

                                    @if ($evaluation->strengths)
                                        <div class="flex gap-2">
                                            <x-he4rt::icon
                                                :icon="Heroicon::CheckCircle"
                                                size="xs"
                                                class="text-success mt-0.5 shrink-0"
                                            />
                                            <p class="text-text-medium text-sm leading-relaxed">
                                                {{ $evaluation->strengths }}
                                            </p>
                                        </div>
                                    @endif

                                    @if ($evaluation->concerns)
                                        <div class="flex gap-2">
                                            <x-he4rt::icon
                                                :icon="Heroicon::ExclamationTriangle"
                                                size="xs"
                                                class="text-warning mt-0.5 shrink-0"
                                            />
                                            <p class="text-text-medium text-sm leading-relaxed">
                                                {{ $evaluation->concerns }}
                                            </p>
                                        </div>
                                    @endif

                                    @if ($evaluation->notes)
                                        <div
                                            class="bg-elevation-02dp/50 text-text-medium rounded-lg p-2 text-sm leading-relaxed"
                                        >
                                            {{ $evaluation->notes }}
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif

        {{-- =========================== FOOTER NAV =========================== --}}
        @if ($this->prevStage || $this->nextStage)
            <div class="border-outline-low border-t pt-4">
                <div class="flex items-stretch gap-2">
                    @if ($this->prevStage)
                        <button
                            type="button"
                            wire:click="goToPreviousStage"
                            wire:loading.attr="disabled"
                            class="group border-outline-low bg-elevation-01dp hover:bg-elevation-02dp hover:border-primary/40 flex flex-1 items-center gap-2 rounded-xl border p-2.5 text-left transition-all"
                        >
                            <x-he4rt::icon
                                :icon="Heroicon::ArrowLeft"
                                size="sm"
                                class="text-icon-medium group-hover:text-primary shrink-0 transition-transform group-hover:-translate-x-0.5"
                            />
                            <div class="min-w-0 flex-1">
                                <div class="text-text-low text-xs font-semibold tracking-wider uppercase">
                                    {{ __('panel-organization::view.pipeline.stage_detail.previous') }}
                                </div>
                                <div class="text-text-high mt-0.5 flex items-center gap-1.5">
                                    <x-he4rt::icon
                                        :icon="$this->prevStage->stage_type->getIcon()"
                                        size="xs"
                                        class="text-icon-medium shrink-0"
                                    />
                                    <span class="truncate text-sm font-medium">{{ $this->prevStage->name }}</span>
                                </div>
                            </div>
                        </button>
                    @endif

                    @if ($this->nextStage)
                        <button
                            type="button"
                            wire:click="goToNextStage"
                            wire:loading.attr="disabled"
                            class="group border-outline-low bg-elevation-01dp hover:bg-elevation-02dp hover:border-primary/40 flex flex-1 items-center justify-between gap-2 rounded-xl border p-2.5 text-left transition-all"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="text-text-low text-right text-xs font-semibold tracking-wider uppercase">
                                    {{ __('panel-organization::view.pipeline.stage_detail.next') }}
                                </div>
                                <div class="text-text-high mt-0.5 flex items-center justify-end gap-1.5">
                                    <x-he4rt::icon
                                        :icon="$this->nextStage->stage_type->getIcon()"
                                        size="xs"
                                        class="text-icon-medium shrink-0"
                                    />
                                    <span class="truncate text-sm font-medium">{{ $this->nextStage->name }}</span>
                                </div>
                            </div>
                            <x-he4rt::icon
                                :icon="Heroicon::ArrowRight"
                                size="sm"
                                class="text-icon-medium group-hover:text-primary shrink-0 transition-transform group-hover:translate-x-0.5"
                            />
                        </button>
                    @endif
                </div>
            </div>
        @endif
    @else
        <p class="text-text-medium py-4 text-center text-base">
            {{ __('panel-organization::view.pipeline.stage_detail.empty_future') }}
        </p>
    @endif
</div>
