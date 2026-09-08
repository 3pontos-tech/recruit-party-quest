@php
    use He4rt\Applications\Enums\ScreeningVerdictFilter;
    use He4rt\Applications\Enums\SeenFilter;

    $requisition = $this->requisition;
    $stats = $this->stats;
    $knockoutQuestions = $this->knockoutQuestionCount;
    $showScreening = $knockoutQuestions > 0;
    $stages = $this->stages;
    $eyebrow = implode(
        ' · ',
        array_filter([
            $requisition->department?->name,
            $requisition->recruiter?->user?->name,
            trans_choice('panel-organization::workspace.overview.positions', $requisition->positions_available, ['count' => $requisition->positions_available]),
            $requisition->experience_level?->getLabel(),
            $requisition->work_arrangement?->getLabel(),
        ]),
    );
@endphp

<header class="border-outline-low/40 bg-elevation-01dp rounded-xl border p-4">
    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto]">
        <div class="min-w-0">
            <p class="text-text-low text-[11px] font-semibold tracking-[0.18em] uppercase">
                {{ $eyebrow ?: __('panel-organization::workspace.overview.untitled') }}
            </p>
            <h2 class="text-text-high text-2xl font-semibold tracking-tight">
                {{ $requisition->post?->title ?? __('panel-organization::workspace.overview.untitled') }}
            </h2>
            <dl class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                <div>
                    <dt class="text-text-low text-[10px] font-semibold tracking-wider uppercase">
                        {{ __('panel-organization::workspace.header.total') }}
                    </dt>
                    <dd class="text-text-high font-mono font-semibold tabular-nums">{{ $stats->total }}</dd>
                </div>
                <div>
                    <dt class="text-text-low text-[10px] font-semibold tracking-wider uppercase">
                        {{ __('panel-organization::workspace.header.new') }}
                    </dt>
                    <dd class="text-text-high font-mono font-semibold tabular-nums">{{ $stats->new }}</dd>
                </div>
                <div>
                    <dt class="text-text-low text-[10px] font-semibold tracking-wider uppercase">
                        {{ __('panel-organization::workspace.header.unseen') }}
                    </dt>
                    <dd class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="filterSeen('{{ $this->seen === SeenFilter::Unseen ? 'all' : 'unseen' }}')"
                            class="{{ $stats->unseen > 0 ? 'text-indigo-600' : 'text-text-high' }} inline-flex items-center gap-1 font-mono font-semibold tabular-nums hover:underline"
                        >
                            @if ($stats->unseen > 0)
                                <span class="size-1.5 rounded-full bg-indigo-500"></span>
                            @endif

                            {{ $stats->unseen }}
                        </button>
                    </dd>
                </div>
                <div>
                    <dt class="text-text-low text-[10px] font-semibold tracking-wider uppercase">
                        {{ __('panel-organization::workspace.header.overdue') }}
                    </dt>
                    <dd
                        class="{{ $stats->overdue > 0 ? 'text-orange-600' : 'text-text-high' }} font-mono font-semibold tabular-nums"
                    >
                        {{ $stats->overdue }}
                    </dd>
                </div>
                <div>
                    <dt class="text-text-low text-[10px] font-semibold tracking-wider uppercase">
                        {{ __('panel-organization::workspace.header.closed') }}
                    </dt>
                    <dd class="text-text-high font-mono font-semibold tabular-nums">{{ $stats->closed }}</dd>
                </div>
            </dl>
        </div>

        <div
            class="{{ $showScreening ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-outline-low/40 border-dashed' }} rounded-lg border p-3 lg:min-w-72"
        >
            @if ($showScreening)
                <p class="text-[10px] font-semibold tracking-wider text-emerald-700 uppercase">
                    {{ __('panel-organization::workspace.header.knockout_title', ['count' => $knockoutQuestions]) }}
                </p>
                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                    <button
                        type="button"
                        wire:click="filterScreening('{{ $this->screening === ScreeningVerdictFilter::Passed ? 'all' : 'passed' }}')"
                        class="{{ $this->screening === ScreeningVerdictFilter::Passed ? 'underline' : '' }} font-semibold text-emerald-600 underline-offset-4 hover:underline"
                    >
                        ✓
                        {{ trans_choice('panel-organization::workspace.header.knockout_passed', $stats->knockoutPassed, ['count' => $stats->knockoutPassed]) }}
                    </button>
                    <button
                        type="button"
                        wire:click="filterScreening('{{ $this->screening === ScreeningVerdictFilter::Failed ? 'all' : 'failed' }}')"
                        class="{{ $this->screening === ScreeningVerdictFilter::Failed ? 'underline' : '' }} font-semibold text-red-600 underline-offset-4 hover:underline"
                    >
                        ✕
                        {{ trans_choice('panel-organization::workspace.header.knockout_failed', $stats->knockoutFailed, ['count' => $stats->knockoutFailed]) }}
                    </button>
                    <button
                        type="button"
                        wire:click="filterScreening('{{ $this->screening === ScreeningVerdictFilter::Unanswered ? 'all' : 'unanswered' }}')"
                        class="{{ $this->screening === ScreeningVerdictFilter::Unanswered ? 'underline' : '' }} text-text-low underline-offset-4 hover:underline"
                    >
                        {{ trans_choice('panel-organization::workspace.header.knockout_unanswered', $stats->knockoutUnanswered, ['count' => $stats->knockoutUnanswered]) }}
                    </button>
                </div>
                <p class="text-text-low mt-1 text-[11px]">
                    {{ __('panel-organization::workspace.header.knockout_hint') }}
                </p>
            @else
                <p class="text-text-low text-[10px] font-semibold tracking-wider uppercase">
                    {{ __('panel-organization::workspace.header.no_knockout_title') }}
                </p>
                <p class="text-text-medium mt-1 text-sm">
                    {{ __('panel-organization::workspace.header.no_knockout') }}
                </p>
            @endif
        </div>
    </div>

    <x-panel-organization::workspace.stage-bar
        :stages="$stages"
        :counts="$stats->byStage"
        :activeStageId="$this->stageId"
        :clickable="true"
        class="mt-4"
    />
    <ol class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs">
        <li>
            <button
                type="button"
                wire:click="filterStage('')"
                class="{{ $this->stageId === '' ? 'text-text-high font-semibold' : 'text-text-low hover:text-text-medium' }}"
            >
                {{ __('panel-organization::workspace.header.all_stages') }}
                <span class="font-mono tabular-nums">{{ $stats->openTotal() }}</span>
            </button>
        </li>
        @foreach ($stages as $stage)
            <li>
                <button
                    type="button"
                    wire:click="filterStage('{{ $stage->getKey() }}')"
                    class="{{ $this->stageId === $stage->getKey() ? 'text-text-high font-semibold' : 'text-text-low hover:text-text-medium' }} inline-flex items-center gap-1"
                >
                    <x-panel-organization::workspace.stage-dot :type="$stage->stage_type" />
                    {{ $stage->name }}
                    <span class="font-mono tabular-nums">{{ $stats->byStage[$stage->getKey()] ?? 0 }}</span>
                </button>
            </li>
        @endforeach
    </ol>
</header>
