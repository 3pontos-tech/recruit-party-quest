@php
    use Filament\Support\Icons\Heroicon;
    use He4rt\Recruitment\Requisitions\Enums\RequisitionOverviewSort;
    use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
    use Illuminate\Support\Facades\Date;

    $requisitions = $this->requisitionsPage;
    $inputClasses =
        'bg-elevation-01dp border-outline-low/50 text-text-high placeholder:text-text-low focus:border-primary focus:ring-primary/30 rounded-lg border py-2 text-sm focus:ring-2 focus:outline-none';
    $pillIdle = 'bg-elevation-01dp text-text-medium hover:bg-elevation-02dp border-outline-low/40';
    $pillActive = 'bg-text-high text-text-light border-transparent';
    $pill = 'inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition';
@endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-text-high text-xl font-semibold tracking-tight">
                {{ __('panel-organization::workspace.overview.title') }}
            </h2>
            <p class="text-text-medium text-sm">{{ __('panel-organization::workspace.overview.subtitle') }}</p>
        </div>
        <p class="text-text-low font-mono text-xs tabular-nums">
            {{ trans_choice('panel-organization::workspace.overview.count', $requisitions->total(), ['count' => $requisitions->total()]) }}
        </p>
    </div>

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <label class="relative grow">
            <span class="sr-only">{{ __('panel-organization::workspace.overview.search_placeholder') }}</span>
            <x-he4rt::icon
                :icon="Heroicon::MagnifyingGlass"
                size="sm"
                class="text-icon-medium pointer-events-none absolute top-1/2 left-3 -translate-y-1/2"
            />
            <input
                type="search"
                wire:model.live.debounce.300ms="requisitionSearch"
                placeholder="{{ __('panel-organization::workspace.overview.search_placeholder') }}"
                class="{{ $inputClasses }} w-full pr-3 pl-9"
            />
        </label>
        <div class="flex flex-wrap gap-1.5">
            @foreach (RequisitionOverviewSort::cases() as $sortOption)
                <button
                    type="button"
                    wire:click="sortRequisitions('{{ $sortOption->value }}')"
                    class="{{ $pill }} {{ $this->requisitionSort === $sortOption ? $pillActive : $pillIdle }}"
                >
                    {{ $sortOption->getLabel() }}
                </button>
            @endforeach
        </div>
        <label class="text-text-medium inline-flex items-center gap-2 text-sm whitespace-nowrap">
            <input
                type="checkbox"
                wire:model.live="onlyPublished"
                class="border-outline-low/60 text-primary focus:ring-primary/30 rounded"
            />
            {{ __('panel-organization::workspace.overview.only_published') }}
        </label>
    </div>

    @if ($requisitions->isEmpty())
        <div
            class="border-outline-low/40 bg-elevation-01dp text-text-medium rounded-xl border border-dashed p-12 text-center text-sm"
        >
            {{ __('panel-organization::workspace.overview.empty') }}
        </div>
    @else
        <ul class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($requisitions as $requisition)
                @php
                    $funnel = $this->funnelByRequisition[$requisition->getKey()] ?? [];
                    $knockoutQuestions = $this->knockoutQuestionsByRequisition[$requisition->getKey()] ?? 0;
                    $oldest = $requisition->oldest_new_at ? Date::parse($requisition->oldest_new_at) : null;
                    $eyebrow = implode(' · ', array_filter([$requisition->department?->name, $requisition->recruiter?->user?->name]));
                    $unseen = (int) $requisition->unseen_applications_count;
                @endphp

                <li wire:key="card-{{ $requisition->getKey() }}">
                    <button
                        type="button"
                        wire:click="openRequisition('{{ $requisition->getKey() }}')"
                        class="border-outline-low/40 bg-elevation-01dp hover:border-outline-medium flex h-full w-full flex-col gap-3 rounded-2xl border p-4 text-left transition"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-text-low truncate text-[11px] font-semibold tracking-[0.18em] uppercase">
                                    {{ $eyebrow ?: __('panel-organization::workspace.overview.untitled') }}
                                </p>
                                <h3 class="text-text-high truncate text-base font-semibold tracking-tight">
                                    {{ $requisition->post?->title ?? __('panel-organization::workspace.overview.untitled') }}
                                </h3>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-1">
                                @if ($requisition->status !== RequisitionStatusEnum::Published)
                                    <span
                                        class="bg-outline-low/20 text-text-medium rounded-full px-1.5 py-0.5 text-[10px] font-semibold"
                                    >
                                        {{ $requisition->status->getLabel() }}
                                    </span>
                                @endif

                                @if ($unseen > 0)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-indigo-500/10 px-2 py-0.5 text-[11px] font-semibold text-indigo-600 ring-1 ring-indigo-500/30 ring-inset"
                                    >
                                        <span class="size-1.5 rounded-full bg-indigo-500"></span>
                                        {{ trans_choice('panel-organization::workspace.overview.unseen', $unseen, ['count' => $unseen]) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-end justify-between gap-3">
                            <div>
                                <span
                                    class="{{ $requisition->new_applications_count > 0 ? 'text-text-high' : 'text-text-low' }} text-3xl font-semibold tracking-tight tabular-nums"
                                >
                                    {{ $requisition->new_applications_count }}
                                </span>
                                <span class="text-text-medium ml-1 text-sm">
                                    {{ __('panel-organization::workspace.overview.new') }}
                                </span>
                                <p class="text-text-low text-[11px]">
                                    @if ($oldest)
                                        {{ __('panel-organization::workspace.overview.oldest_waiting', ['days' => (int) $oldest->diffInDays(now())]) }}
                                    @else
                                        {{ __('panel-organization::workspace.overview.none_waiting') }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-text-high font-mono text-sm tabular-nums">
                                    {{ $requisition->applications_count }}
                                    <span class="text-text-low font-sans text-xs">
                                        {{ __('panel-organization::workspace.overview.total') }}
                                    </span>
                                </p>
                                <p class="text-text-high font-mono text-sm tabular-nums">
                                    {{ $requisition->active_applications_count }}
                                    <span class="text-text-low font-sans text-xs">
                                        {{ __('panel-organization::workspace.overview.active') }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div>
                            <x-panel-organization::workspace.stage-bar
                                :stages="$requisition->stages"
                                :counts="$funnel"
                            />
                            <ol class="mt-1.5 grid grid-cols-3 gap-x-2 gap-y-0.5 text-[10px]">
                                @foreach ($requisition->stages as $stage)
                                    <li class="text-text-low flex items-center gap-1 truncate">
                                        <x-panel-organization::workspace.stage-dot :type="$stage->stage_type" />
                                        <span class="truncate">{{ $stage->name }}</span>
                                        <span class="text-text-medium font-mono tabular-nums">
                                            {{ $funnel[$stage->getKey()] ?? 0 }}
                                        </span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>

                        <div
                            class="border-outline-low/30 mt-auto flex items-center justify-between gap-2 border-t pt-3 text-xs"
                        >
                            @if ($knockoutQuestions === 0)
                                <span class="text-text-low">
                                    {{ __('panel-organization::workspace.overview.no_knockout') }}
                                </span>
                            @else
                                <span class="flex min-w-0 items-center gap-2">
                                    <span
                                        class="inline-flex shrink-0 items-center rounded-full bg-emerald-500/10 px-2 py-0.5 font-semibold text-emerald-600 ring-1 ring-emerald-500/30 ring-inset"
                                    >
                                        ✓
                                        {{ trans_choice('panel-organization::workspace.overview.knockout_passed', $requisition->knockout_passed_count, ['count' => $requisition->knockout_passed_count]) }}
                                    </span>
                                    <span class="text-text-low truncate">
                                        {{ trans_choice('panel-organization::workspace.overview.knockout_questions', $knockoutQuestions, ['count' => $knockoutQuestions]) }}
                                    </span>
                                </span>
                            @endif
                            <span class="text-text-medium shrink-0 font-mono tabular-nums">
                                {{ trans_choice('panel-organization::workspace.overview.hired', $requisition->hired_applications_count, ['count' => $requisition->hired_applications_count]) }}
                            </span>
                        </div>
                    </button>
                </li>
            @endforeach
        </ul>
    @endif

    <x-filament::pagination :paginator="$requisitions" />
</div>
