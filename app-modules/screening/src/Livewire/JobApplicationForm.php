<?php

declare(strict_types=1);

namespace He4rt\Screening\Livewire;

use He4rt\App\Filament\Resources\Applications\ApplicationResource;
use He4rt\Applications\Actions\ApplyToJobRequisitionAction;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Models\ScreeningResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class JobApplicationForm extends Component
{
    public JobRequisition $requisition;

    public ?Application $application = null;

    /** @var array<string, mixed> */
    public array $responses = [];

    public function mount(JobRequisition $requisition): void
    {
        $this->requisition = $requisition;

        foreach ($requisition->screeningQuestions as $question) {
            $this->responses[$question->id] = null;
        }
    }

    public function submit(ApplyToJobRequisitionAction $applyAction): void
    {
        if (! $this->application instanceof Application) {
            /** @var Candidate $candidate */
            $candidate = auth()->user()->candidate;

            $this->application = $applyAction->execute($this->requisition, $candidate);
        }

        foreach ($this->responses as $questionId => $value) {
            if ($value === null) {
                continue;
            }

            ScreeningResponse::query()->create([
                'team_id' => $this->requisition->team_id,
                'application_id' => $this->application->id,
                'question_id' => $questionId,
                'response_value' => is_array($value) ? $value : ['value' => $value],
            ]);
        }

        $this->redirect(ApplicationResource::getUrl('view', ['record' => $this->application]));
    }

    public function render(): View|Factory|\Illuminate\View\View
    {
        $requiredQuestionIds = $this->requisition->screeningQuestions
            ->where('is_required', true)
            ->pluck('id')
            ->toArray();

        return view('screening::livewire.job-application-form', [
            'requiredQuestionIds' => $requiredQuestionIds,
        ]);
    }
}
