<?php

declare(strict_types=1);

namespace He4rt\Organization\Livewire;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Recruitment\Stages\Models\Stage;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * @property-read Application $application
 * @property-read Collection<int, Stage> $stages
 * @property-read Stage|null $stage
 * @property-read int $stageIndex
 * @property-read Collection<int, ApplicationStageHistory> $timeline
 * @property-read Collection<int, Recruiter> $interviewers
 * @property-read Collection<int, Evaluation> $evaluations
 * @property-read Stage|null $prevStage
 * @property-read Stage|null $nextStage
 * @property-read bool $isFutureStage
 */
class PipelineStageDetail extends Component
{
    #[Locked]
    public string $applicationId;

    public string $currentStageId = '';

    public function mount(Application $application): void
    {
        Gate::authorize('view', $application);

        $tenant = filament()->getTenant();
        abort_unless($tenant instanceof Model && $application->team_id === $tenant->getKey(), 403);

        $this->applicationId = $application->id;
        $this->currentStageId = $application->current_stage_id ?? '';
    }

    #[Computed]
    public function application(): Application
    {
        /** @var Application $application */
        $application = Application::query()
            ->with([
                'currentStage',
                'requisition.stages' => fn ($query) => $query
                    ->where('active', true)
                    ->orderBy('display_order'),
                'requisition.stages.interviewers.user',
                'requisition.stages.screeningQuestions',
                'stageHistory' => fn ($query) => $query->latest(),
                'stageHistory.fromStage',
                'stageHistory.toStage',
                'stageHistory.movedBy',
                'evaluations',
            ])
            ->findOrFail($this->applicationId);

        return $application;
    }

    /**
     * @return Collection<int, Stage>
     */
    #[Computed]
    public function stages(): Collection
    {
        if (! $this->application->requisition) {
            return collect();
        }

        return $this->application->requisition->stages
            ->sortBy('display_order')
            ->values();
    }

    #[Computed]
    public function stage(): ?Stage
    {
        return $this->stages->firstWhere('id', $this->currentStageId);
    }

    #[Computed]
    public function stageIndex(): int
    {
        $index = $this->stages->search(fn (Stage $stage) => $stage->id === $this->currentStageId);

        return $index === false ? 0 : $index + 1;
    }

    /**
     * @return Collection<int, ApplicationStageHistory>
     */
    #[Computed]
    public function timeline(): Collection
    {
        return $this->application->stageHistory
            ->where('to_stage_id', $this->currentStageId)
            ->values();
    }

    /**
     * @return Collection<int, Recruiter>
     */
    #[Computed]
    public function interviewers(): Collection
    {
        if (! $this->stage) {
            return collect();
        }

        return $this->stage->interviewers;
    }

    /**
     * @return Collection<int, Evaluation>
     */
    #[Computed]
    public function evaluations(): Collection
    {
        return $this->application->evaluations
            ->where('stage_id', $this->currentStageId)
            ->values();
    }

    #[Computed]
    public function prevStage(): ?Stage
    {
        $index = $this->stages->search(fn (Stage $stage) => $stage->id === $this->currentStageId);

        if ($index === false || $index === 0) {
            return null;
        }

        return $this->stages->get($index - 1);
    }

    #[Computed]
    public function nextStage(): ?Stage
    {
        $index = $this->stages->search(fn (Stage $stage) => $stage->id === $this->currentStageId);

        if ($index === false || $index === $this->stages->count() - 1) {
            return null;
        }

        $next = $this->stages->get($index + 1);

        return ($next instanceof Stage && $this->isReached($next)) ? $next : null;
    }

    #[Computed]
    public function isFutureStage(): bool
    {
        if (! $this->stage) {
            return false;
        }

        $currentApplicationStage = $this->application->currentStage;

        if (! $currentApplicationStage) {
            return true;
        }

        return $this->stage->display_order > $currentApplicationStage->display_order
            && $this->timeline->isEmpty();
    }

    public function goToStage(string $stageId): void
    {
        $target = $this->stages->firstWhere('id', $stageId);

        if ($target instanceof Stage && $this->isReached($target)) {
            $this->currentStageId = $stageId;
            $this->forgetStageComputeds();
        }
    }

    public function goToNextStage(): void
    {
        $next = $this->nextStage;

        if ($next instanceof Stage) {
            $this->currentStageId = $next->id;
            $this->forgetStageComputeds();
        }
    }

    public function goToPreviousStage(): void
    {
        $prev = $this->prevStage;

        if ($prev instanceof Stage) {
            $this->currentStageId = $prev->id;
            $this->forgetStageComputeds();
        }
    }

    /**
     * A stage is "reached" when it is at or before the application's current stage
     * (by display_order). Régua: o current_stage da candidatura — não depende de
     * stageHistory (candidato recém-inscrito já alcançou seu stage atual).
     */
    public function isReached(Stage $stage): bool
    {
        $current = $this->application->currentStage;

        return $current instanceof Stage && $stage->display_order <= $current->display_order;
    }

    public function isApplicationCurrent(Stage $stage): bool
    {
        $current = $this->application->currentStage;

        return $current instanceof Stage && $stage->id === $current->id;
    }

    public function isRejected(): bool
    {
        return $this->application->status === ApplicationStatusEnum::Rejected;
    }

    public function rejectionReason(): ?string
    {
        if (! $this->isRejected()) {
            return null;
        }

        return $this->application->rejection_reason_category?->getLabel();
    }

    /**
     * The terminal status that "freezes" the application on its current stage as
     * an overlay — rejection, withdrawal or a declined offer — or null when the
     * application is still progressing through the journey.
     */
    public function terminalStatus(): ?ApplicationStatusEnum
    {
        return $this->application->status->isTerminal()
            ? $this->application->status
            : null;
    }

    /**
     * Semantic color for the terminal marker: 'red' for negative exits (rejected
     * or offer declined) and 'orange' for a candidate-initiated withdrawal.
     */
    public function terminalColor(): ?string
    {
        return match ($this->terminalStatus()) {
            ApplicationStatusEnum::Rejected, ApplicationStatusEnum::OfferDeclined => 'red',
            ApplicationStatusEnum::Withdrawn => 'orange',
            default => null,
        };
    }

    public function render(): Factory|View
    {
        return view('panel-organization::livewire.pipeline-stage-detail');
    }

    /**
     * Invalidate the cached computeds that depend on the selected stage. Livewire
     * memoizes #[Computed] for the whole request; without this, reading nextStage
     * before mutating currentStageId re-renders the footer with a stale value.
     */
    private function forgetStageComputeds(): void
    {
        unset(
            $this->stage,
            $this->stageIndex,
            $this->timeline,
            $this->interviewers,
            $this->evaluations,
            $this->prevStage,
            $this->nextStage,
        );
    }
}
