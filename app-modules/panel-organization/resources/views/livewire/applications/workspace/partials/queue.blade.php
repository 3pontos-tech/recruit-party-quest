@php
    use Filament\Support\Icons\Heroicon;
    use He4rt\Applications\Enums\ApplicationListSort;
    use He4rt\Applications\Enums\ApplicationStatusGroup;
    use He4rt\Applications\Enums\ScreeningVerdictFilter;
    use He4rt\Applications\Enums\SeenFilter;

    $applications = $this->applicationsPage;
    $knockoutQuestions = $this->knockoutQuestionCount;
    $showScreening = $knockoutQuestions > 0;
    $inputClasses =
        'bg-elevation-01dp border-outline-low/50 text-text-high placeholder:text-text-low focus:border-primary focus:ring-primary/30 rounded-lg border py-2 text-sm focus:ring-2 focus:outline-none';
    $pill = 'inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition';
    $pillIdle = 'bg-elevation-01dp text-text-medium hover:bg-elevation-02dp border-outline-low/40';
    $pillActive = 'bg-text-high text-text-light border-transparent';
@endphp

<div class="grid gap-4 xl:grid-cols-[minmax(0,7fr)_minmax(0,5fr)]">
    <section class="min-w-0 space-y-3">
        <div class="flex flex-col gap-2 sm:flex-row">
            <label class="relative grow">
                <span class="sr-only">{{ __('panel-organization::workspace.queue.search_placeholder') }}</span>
                <x-he4rt::icon
                    :icon="Heroicon::MagnifyingGlass"
                    size="sm"
                    class="text-icon-medium pointer-events-none absolute top-1/2 left-3 -translate-y-1/2"
                />
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('panel-organization::workspace.queue.search_placeholder') }}"
                    class="{{ $inputClasses }} w-full pr-3 pl-9"
                />
            </label>
            <select wire:model.live="sort" class="{{ $inputClasses }} pr-8 pl-3 sm:w-52">
                @foreach (ApplicationListSort::cases() as $sortOption)
                    <option wire:key="sort-{{ $sortOption->value }}" value="{{ $sortOption->value }}">
                        {{ __('panel-organization::workspace.queue.sort_prefix', ['label' => $sortOption->getLabel()]) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-wrap items-center gap-1.5" role="tablist">
            <button
                type="button"
                wire:click="filterStatusGroup('')"
                role="tab"
                aria-selected="{{ $this->statusGroup === null ? 'true' : 'false' }}"
                class="{{ $pill }} {{ $this->statusGroup === null ? $pillActive : $pillIdle }}"
            >
                {{ __('panel-organization::workspace.queue.all_statuses') }}
            </button>
            @foreach (ApplicationStatusGroup::cases() as $group)
                <button
                    type="button"
                    wire:key="status-group-{{ $group->value }}"
                    wire:click="filterStatusGroup('{{ $this->statusGroup === $group ? '' : $group->value }}')"
                    role="tab"
                    aria-selected="{{ $this->statusGroup === $group ? 'true' : 'false' }}"
                    class="{{ $pill }} {{ $this->statusGroup === $group ? $pillActive : $pillIdle }}"
                >
                    {{ $group->getLabel() }}
                </button>
            @endforeach

            <span class="bg-outline-low/40 mx-1 hidden h-4 w-px sm:block"></span>
            <button
                type="button"
                wire:click="filterSeen('{{ $this->seen === SeenFilter::Unseen ? 'all' : 'unseen' }}')"
                class="{{ $pill }} {{ $this->seen === SeenFilter::Unseen ? 'border-transparent bg-indigo-600 text-white' : 'border-indigo-500/40 bg-indigo-500/10 text-indigo-600 hover:bg-indigo-500/15' }}"
            >
                <span
                    class="{{ $this->seen === SeenFilter::Unseen ? 'bg-white' : 'bg-indigo-500' }} size-1.5 rounded-full"
                ></span>
                {{ __('panel-organization::workspace.queue.seen_unseen') }}
            </button>
            <button
                type="button"
                wire:click="filterSeen('{{ $this->seen === SeenFilter::Seen ? 'all' : 'seen' }}')"
                class="{{ $pill }} {{ $this->seen === SeenFilter::Seen ? $pillActive : $pillIdle }}"
            >
                {{ __('panel-organization::workspace.queue.seen_seen') }}
            </button>
            @if ($showScreening)
                <span class="bg-outline-low/40 mx-1 hidden h-4 w-px sm:block"></span>
                <button
                    type="button"
                    wire:click="filterScreening('{{ $this->screening === ScreeningVerdictFilter::Passed ? 'all' : 'passed' }}')"
                    class="{{ $pill }} {{ $this->screening === ScreeningVerdictFilter::Passed ? 'border-transparent bg-emerald-600 text-white' : 'border-emerald-500/40 bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500/15' }}"
                >
                    {{ __('panel-organization::workspace.queue.screening_passed') }}
                </button>
                <button
                    type="button"
                    wire:click="filterScreening('{{ $this->screening === ScreeningVerdictFilter::Failed ? 'all' : 'failed' }}')"
                    class="{{ $pill }} {{ $this->screening === ScreeningVerdictFilter::Failed ? 'border-transparent bg-red-600 text-white' : 'border-red-500/40 bg-red-500/10 text-red-600 hover:bg-red-500/15' }}"
                >
                    {{ __('panel-organization::workspace.queue.screening_failed') }}
                </button>
            @endif
        </div>

        <ol
            class="border-outline-low/40 bg-elevation-01dp divide-outline-low/30 divide-y overflow-hidden rounded-xl border"
        >
            @forelse ($applications as $application)
                @php
                    $candidate = $application->candidate;
                    $user = $candidate?->user;
                    $isSelected = $this->selectedApplication?->getKey() === $application->getKey();
                    $isSeen = $application->isSeenByTeam();
                    $days = $application->daysInStage();
                    $overdue = $application->isOverdueInStage();
                    $score = $application->averageEvaluationScore();
                    $fails = $application->knockoutFailsCount();
                    $answers = $application->screeningAnswersCount();
                    $location = implode(', ', array_filter([$candidate?->address?->city, $candidate?->address?->state]));
                    $latestExperience = $candidate?->workExperiences->sortByDesc('start_date')->first();
                    $currentPosition = $latestExperience !== null ? implode(' · ', array_filter([$latestExperience->position, $latestExperience->company_name])) : null;
                @endphp

                <li wire:key="row-{{ $application->getKey() }}">
                    <button
                        type="button"
                        wire:click="select('{{ $application->getKey() }}')"
                        aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                        class="{{ $isSelected ? 'bg-elevation-02dp shadow-[inset_3px_0_0_0_var(--color-primary)]' : ($application->hasFailedKnockout() ? 'hover:bg-elevation-02dp/70 shadow-[inset_3px_0_0_0_var(--color-red-500)]' : 'hover:bg-elevation-02dp/70') }} grid w-full grid-cols-[auto_auto_minmax(0,1fr)_auto] items-center gap-3 px-4 py-3 text-left transition"
                    >
                        <span
                            class="{{ $isSeen ? 'ring-outline-low/50 bg-transparent ring-1' : 'bg-indigo-500' }} size-2 rounded-full"
                            title="{{ $isSeen ? __('panel-organization::workspace.queue.seen_title') : __('panel-organization::workspace.queue.unseen_title') }}"
                        ></span>
                        <img
                            src="{{ $user?->getFilamentAvatarUrl() }}"
                            alt=""
                            class="ring-outline-low/40 size-10 rounded-full object-cover ring-1"
                        />
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span
                                    class="{{ $isSeen ? 'text-text-medium font-medium' : 'text-text-high font-semibold' }} truncate text-sm"
                                >
                                    {{ $user?->name }}
                                </span>
                                <span
                                    @class([$application->status->getTailwindBadgeClass(), 'shrink-0 rounded-full px-1.5 py-0.5 text-[10px] font-semibold'])
                                >
                                    {{ $application->status->getLabel() }}
                                </span>
                            </div>
                            <p class="text-text-medium truncate text-xs">
                                {{ $candidate?->headline ?? ($currentPosition ?? __('panel-organization::workspace.queue.no_headline')) }}
                            </p>
                            <p class="text-text-low truncate text-[11px]">
                                {{ $location !== '' ? $location : __('panel-organization::workspace.queue.no_location') }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1 text-right">
                            @if ($showScreening)
                                <x-panel-organization::workspace.knockout-badge
                                    :fails="$fails"
                                    :total="$knockoutQuestions"
                                    :answers="$answers"
                                />
                            @endif

                            <span class="text-text-high inline-flex items-center gap-1.5 text-xs font-medium">
                                <x-panel-organization::workspace.stage-dot
                                    :type="$application->currentStage?->stage_type"
                                />
                                {{ $application->currentStage?->name ?? __('panel-organization::workspace.queue.no_stage') }}
                            </span>
                            <span
                                class="{{ $overdue ? 'font-semibold text-red-600' : 'text-text-low' }} font-mono text-[11px] tabular-nums"
                            >
                                {{ __('panel-organization::workspace.queue.days_in_stage', ['days' => $days]) }}
                                @if ($overdue)
                                        · {{ __('panel-organization::workspace.queue.overdue') }}
                                @endif
                            </span>
                            <x-panel-organization::workspace.rating-dots :score="$score" />
                        </div>
                    </button>
                </li>
            @empty
                <li class="text-text-medium px-4 py-12 text-center text-sm">
                    {{ __('panel-organization::workspace.queue.empty') }}
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="text-text-high ml-1 font-medium underline-offset-2 hover:underline"
                    >
                        {{ __('panel-organization::workspace.queue.clear_filters') }}
                    </button>
                </li>
            @endforelse
        </ol>

        <x-filament::pagination :paginator="$applications" />
    </section>

    @include('panel-organization::livewire.applications.workspace.partials.preview')
</div>
