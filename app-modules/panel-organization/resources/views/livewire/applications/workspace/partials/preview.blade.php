@php
    use Filament\Support\Icons\Heroicon;
    use He4rt\Screening\Presenters\ScreeningResponsePresenter;

    $selected = $this->selectedApplication;
    $knockoutQuestions = $this->knockoutQuestionCount;
    $showScreening = $knockoutQuestions > 0;
@endphp

<aside class="min-w-0 self-start xl:sticky xl:top-4">
    @if ($selected)
        @php
            $candidate = $selected->candidate;
            $user = $candidate?->user;
            $address = $candidate?->address;
            $location = implode(', ', array_filter([$address?->city, $address?->state]));
            $preferences = implode(' · ', array_filter([$candidate?->is_open_to_remote ? __('panel-organization::workspace.preview.remote') : null, $candidate?->willing_to_relocate ? __('panel-organization::workspace.preview.relocate') : null]));
            $latestExperience = $candidate?->workExperiences->sortByDesc('start_date')->first();
            $currentPosition = $latestExperience !== null ? implode(' · ', array_filter([$latestExperience->position, $latestExperience->company_name])) : null;
            $expectedSalary = $candidate !== null ? sprintf('%s %s', $candidate->expected_salary_currency, number_format((float) $candidate->expected_salary, 0, ',', '.')) : null;
            $availability = $candidate?->availability_date === null || $candidate->availability_date->isPast() ? __('panel-organization::workspace.preview.immediate') : $candidate->availability_date->format('d/m/Y');
            $score = $selected->averageEvaluationScore();
            $skills = $candidate?->skills->sortByDesc(fn ($skill) => $skill->pivot?->proficiency_level ?? 0)->take(6) ?? collect();
            $links = $user?->links ?? collect();
            $fails = $selected->knockoutFailsCount();
            $knockoutResponses = $selected->knockoutResponses();
            $lastMovement = $selected->stageHistory->first();
            $expectedDuration = $selected->currentStage?->expected_duration_days;
            $signals = [
                [
                    'label' => __('panel-organization::workspace.preview.experience'),
                    'value' => $candidate?->total_experience_formatted,
                    'hint' => $candidate?->experience_level?->getLabel(),
                ],
                [
                    'label' => __('panel-organization::workspace.preview.location'),
                    'value' => $location !== '' ? $location : __('panel-organization::workspace.queue.no_location'),
                    'hint' => $preferences !== '' ? $preferences : null,
                ],
                [
                    'label' => __('panel-organization::workspace.preview.salary'),
                    'value' => $expectedSalary,
                    'hint' => null,
                ],
                [
                    'label' => __('panel-organization::workspace.preview.availability'),
                    'value' => $availability,
                    'hint' => null,
                ],
                [
                    'label' => __('panel-organization::workspace.preview.evaluations'),
                    'value' => $score === null ? __('panel-organization::workspace.queue.no_evaluations') : __('panel-organization::workspace.queue.average', ['score' => $score]),
                    'hint' => trans_choice('panel-organization::workspace.preview.evaluations_count', $selected->submittedEvaluationsCount(), ['count' => $selected->submittedEvaluationsCount()]),
                ],
                [
                    'label' => __('panel-organization::workspace.preview.comments'),
                    'value' => (string) $selected->comments_count,
                    'hint' => null,
                ],
                [
                    'label' => __('panel-organization::workspace.preview.cover_letter'),
                    'value' => filled($selected->cover_letter) ? __('panel-organization::workspace.preview.sent') : __('panel-organization::workspace.preview.not_sent'),
                    'hint' => null,
                ],
                [
                    'label' => __('panel-organization::workspace.preview.source'),
                    'value' => $selected->source->getLabel(),
                    'hint' => null,
                ],
                [
                    'label' => __('panel-organization::workspace.preview.tracking_code'),
                    'value' => (string) $selected->tracking_code,
                    'hint' => null,
                ],
            ];
        @endphp

        <article
            wire:key="preview-{{ $selected->getKey() }}"
            class="border-outline-low/40 bg-elevation-01dp overflow-hidden rounded-xl border"
        >
            <div class="space-y-5 p-5">
                <header class="flex items-start gap-4">
                    <img
                        src="{{ $user?->getFilamentAvatarUrl() }}"
                        alt=""
                        class="ring-outline-low/40 size-16 shrink-0 rounded-full object-cover ring-1"
                    />
                    <div class="min-w-0 grow">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-text-high text-lg font-semibold tracking-tight">{{ $user?->name }}</h3>
                            <span
                                @class([$selected->status->getTailwindBadgeClass(), 'rounded-full px-2 py-0.5 text-[10px] font-semibold'])
                            >
                                {{ $selected->status->getLabel() }}
                            </span>
                        </div>
                        <p class="text-text-medium text-sm">
                            {{ $candidate?->headline ?? __('panel-organization::workspace.queue.no_headline') }}
                        </p>
                        <p class="text-text-low text-xs">
                            {{ $currentPosition ?? __('panel-organization::workspace.preview.no_experience') }}
                            ·
                            {{ __('panel-organization::workspace.preview.applied_on', ['date' => $selected->created_at->format('d/m/Y')]) }}
                        </p>
                        <p class="text-text-low mt-1 text-[11px]">
                            @if ($selected->teamView !== null)
                                {{ __('panel-organization::workspace.preview.seen_by', ['name' => $selected->teamView->viewer?->name, 'date' => $selected->teamView->viewed_at->format('d/m/Y')]) }}
                            @else
                                {{ __('panel-organization::workspace.preview.unseen') }}
                            @endif
                        </p>
                    </div>
                    <a
                        href="{{ $this->viewUrl($selected) }}"
                        class="bg-text-high text-text-light shrink-0 rounded-lg px-3 py-2 text-xs font-semibold transition hover:opacity-90"
                    >
                        {{ __('panel-organization::workspace.preview.open') }}
                    </a>
                </header>

                @if ($showScreening)
                    @if ($knockoutResponses->isEmpty())
                        <p
                            class="border-outline-low/40 text-text-low rounded-lg border border-dashed px-3 py-2 text-sm"
                        >
                            {{ __('panel-organization::workspace.preview.knockout_unanswered') }}
                        </p>
                    @else
                        <section
                            class="{{ $fails > 0 ? 'border-red-500/30 bg-red-500/5' : 'border-emerald-500/30 bg-emerald-500/5' }} rounded-lg border p-3"
                        >
                            <p
                                class="{{ $fails > 0 ? 'text-red-600' : 'text-emerald-700' }} text-xs font-semibold tracking-wider uppercase"
                            >
                                @if ($fails > 0)
                                    {{ __('panel-organization::workspace.preview.knockout_failed_title', ['fails' => $fails, 'total' => $knockoutQuestions]) }}
                                @else
                                    {{ trans_choice('panel-organization::workspace.preview.knockout_passed_title', $knockoutQuestions, ['count' => $knockoutQuestions]) }}
                                @endif
                            </p>
                            <ul class="mt-2 space-y-1.5 text-sm">
                                @foreach ($knockoutResponses as $response)
                                    @php
                                        $responseText = (new ScreeningResponsePresenter($response))->displayValue();
                                    @endphp

                                    <li wire:key="knockout-{{ $response->getKey() }}" class="flex items-start gap-2">
                                        <span
                                            class="{{ $response->is_knockout_fail ? 'text-red-600' : 'text-emerald-600' }} shrink-0 font-semibold"
                                        >
                                            {{ $response->is_knockout_fail ? '✕' : '✓' }}
                                        </span>
                                        <span class="text-text-high min-w-0">
                                            {{ $response->question?->question_text ?? __('panel-organization::workspace.preview.question_removed') }}
                                            <span class="text-text-low">
                                                {{ __('panel-organization::workspace.preview.answered', ['answer' => $responseText]) }}
                                            </span>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                @endif

                <section class="border-outline-low/30 rounded-lg border p-3">
                    <p class="text-text-low text-xs font-semibold tracking-wider uppercase">
                        {{ __('panel-organization::workspace.preview.current_stage') }}
                    </p>
                    <div class="mt-1 flex items-baseline justify-between gap-3">
                        <span class="text-text-high text-base font-semibold">
                            {{ $selected->currentStage?->name ?? __('panel-organization::workspace.queue.no_stage') }}
                        </span>
                        <span
                            class="{{ $selected->isOverdueInStage() ? 'font-semibold text-red-600' : 'text-text-medium' }} text-xs"
                        >
                            {{ trans_choice('panel-organization::workspace.preview.days', $selected->daysInStage(), ['count' => $selected->daysInStage()]) }}
                            @if ($expectedDuration)
                                ·
                                {{ __('panel-organization::workspace.preview.expected', ['days' => trans_choice('panel-organization::workspace.preview.days', $expectedDuration, ['count' => $expectedDuration])]) }}
                            @endif
                        </span>
                    </div>
                    @if ($lastMovement)
                        <p class="text-text-low mt-2 text-xs">
                            {{ __('panel-organization::workspace.preview.moved_by', ['name' => $lastMovement->movedBy?->name ?? __('panel-organization::workspace.preview.system'), 'date' => $lastMovement->created_at->format('d/m/Y')]) }}
                        </p>
                    @else
                        <p class="text-text-low mt-2 text-xs">
                            {{ __('panel-organization::workspace.preview.no_movement') }}
                        </p>
                    @endif
                </section>

                <dl class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-3">
                    @foreach ($signals as $signal)
                        <div wire:key="signal-{{ $loop->index }}" class="bg-elevation-02dp/60 rounded-lg px-3 py-2">
                            <dt class="text-text-low text-[10px] font-semibold tracking-wider uppercase">
                                {{ $signal['label'] }}
                            </dt>
                            <dd class="text-text-high truncate font-medium">{{ $signal['value'] }}</dd>
                            @if ($signal['hint'])
                                <dd class="text-text-low truncate text-xs">{{ $signal['hint'] }}</dd>
                            @endif
                        </div>
                    @endforeach
                </dl>

                @if ($skills->isNotEmpty())
                    <section>
                        <h4 class="text-text-low text-[10px] font-semibold tracking-wider uppercase">
                            {{ __('panel-organization::workspace.preview.skills') }}
                        </h4>
                        <ul class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($skills as $skill)
                                <li
                                    wire:key="skill-{{ $skill->getKey() }}"
                                    class="border-outline-low/40 text-text-high inline-flex items-center gap-1.5 rounded-md border px-2 py-1 text-xs"
                                >
                                    {{ $skill->name }}
                                    <span
                                        class="flex gap-px"
                                        aria-label="{{ __('panel-organization::workspace.preview.skill_level', ['level' => $skill->pivot?->proficiency_level ?? 0]) }}"
                                    >
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span
                                                class="{{ $i <= ($skill->pivot?->proficiency_level ?? 0) ? 'bg-text-high' : 'bg-outline-low/40' }} h-3 w-0.5 rounded-full"
                                            ></span>
                                        @endfor
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($links->isNotEmpty())
                    <section class="flex flex-wrap gap-3">
                        @foreach ($links as $link)
                            <a
                                wire:key="link-{{ $link->getKey() }}"
                                href="{{ $link->url }}"
                                target="_blank"
                                rel="noopener"
                                class="text-text-medium hover:text-text-high inline-flex items-center gap-1 text-xs underline-offset-2 hover:underline"
                            >
                                <x-he4rt::icon :icon="Heroicon::ArrowTopRightOnSquare" size="xs" />
                                {{ $link->type?->getLabel() ?? $link->name }}
                            </a>
                        @endforeach
                    </section>
                @endif
            </div>
        </article>
    @else
        <div
            class="border-outline-low/40 bg-elevation-01dp text-text-medium rounded-xl border border-dashed p-8 text-center text-sm"
        >
            {{ __('panel-organization::workspace.preview.empty') }}
        </div>
    @endif
</aside>
