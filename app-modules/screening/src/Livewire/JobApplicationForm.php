<?php

declare(strict_types=1);

namespace He4rt\Screening\Livewire;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Models\ScreeningResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
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

            if ($question->question_type === QuestionTypeEnum::FileUpload) {
                $this->responses[$question->id] = ['files' => []];
            }
        }
    }

    /**
     * @param  array<string<string,string>,string>  $data
     */
    #[On('file-uploaded')]
    public function handleFileUploaded(array $data): void
    {
        // we got the name of the file that will be saved
        $this->responses[$data['questionId']] = ['files' => $data['files']];
    }

    public function submit(): void
    {
        $this->validate();
        if (! $this->application instanceof Application) {
            /** @var Candidate $candidate */
            $candidate = auth()->user()->candidate;

            $this->application = Application::query()->create([
                'requisition_id' => $this->requisition->id,
                'candidate_id' => $candidate->getKey(),
                'team_id' => $this->requisition->team_id,
                'status' => ApplicationStatusEnum::New,
                'source' => CandidateSourceEnum::CareerPage,
            ]);

            $this->application->update([
                'current_stage_id' => $this->application->first_stage->getKey(),
            ]);
            // TODO: refactor dispatch an event because this belongs to Application module,
            // we could use some dto and then extract to an action
        }

        // TODO: extract this to an exclusive action, maybe create a custom collection
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

    /**
     * @return array<string, array<int, string>|string>
     */
    protected function rules(): array
    {
        $rules = [];

        foreach ($this->requisition->screeningQuestions as $question) {
            $rules['responses.'.$question->id] = $question->is_required
                ? ['required']
                : ['nullable'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        $messages = [];

        foreach ($this->requisition->screeningQuestions as $question) {
            if ($question->is_required) {
                $messages[sprintf('responses.%s.required', $question->id)]
                    = 'This question is required.';
            }
        }

        return $messages;
    }
}
