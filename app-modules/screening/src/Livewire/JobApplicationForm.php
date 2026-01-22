<?php

declare(strict_types=1);

namespace He4rt\Screening\Livewire;

use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Models\ScreeningResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class JobApplicationForm extends Component
{
    public JobRequisition $requisition;

    public ?Application $application = null;

    public array $responses = [];

    public function mount(JobRequisition $requisition): void
    {
        $this->requisition = $requisition;

        foreach ($requisition->screeningQuestions as $question) {
            $this->responses[$question->id] = null;
        }
    }

    public function submit(): void
    {
        if (! $this->application instanceof Application) {
            // TODO: Create application or handle logic when application is provided
            return;
        }

        foreach ($this->responses as $questionId => $value) {
            ScreeningResponse::query()->create([
                'application_id' => $this->application->id,
                'question_id' => $questionId,
                'response_value' => ['value' => $value],
            ]);
        }
    }

    public function render(): View|Factory|\Illuminate\View\View
    {
        return view('screening::livewire.job-application-form');
    }
}
