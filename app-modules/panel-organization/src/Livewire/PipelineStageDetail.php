<?php

declare(strict_types=1);

namespace He4rt\Organization\Livewire;

use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
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

        $terminalTypes = [StageTypeEnum::Rejected, StageTypeEnum::Declined];
        $currentStageType = $this->application->currentStage?->stage_type;
        $isCurrentTerminal = $currentStageType !== null
            && in_array($currentStageType, $terminalTypes, true);

        return $this->application->requisition->stages
            ->reject(function (Stage $stage) use ($currentStageType, $isCurrentTerminal, $terminalTypes): bool {
                $stageIsTerminal = in_array($stage->stage_type, $terminalTypes, true);

                if (! $stageIsTerminal) {
                    return false;
                }

                return ! $isCurrentTerminal || $stage->stage_type !== $currentStageType;
            })
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

        return $this->stages->get($index + 1);
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
        if ($this->stages->contains(fn (Stage $stage) => $stage->id === $stageId)) {
            $this->currentStageId = $stageId;
        }
    }

    public function goToNextStage(): void
    {
        if ($this->nextStage) {
            $this->currentStageId = $this->nextStage->id;
        }
    }

    public function goToPreviousStage(): void
    {
        if ($this->prevStage) {
            $this->currentStageId = $this->prevStage->id;
        }
    }

    public function render(): Factory|View
    {
        return view('panel-organization::livewire.pipeline-stage-detail');
    }
}
