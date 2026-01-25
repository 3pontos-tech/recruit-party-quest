<?php

declare(strict_types=1);

namespace He4rt\Screening\Livewire;

use Filament\Notifications\Notification;
use He4rt\Applications\DTOs\ApplicationDTO;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Events\JobAppliedEvent;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Actions\ScreeningResponse\StoreScreeningResponse;
use He4rt\Screening\Collections\ScreeningResponseCollection;
use He4rt\Screening\DTOs\ScreeningResponseDTO;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Rules\MultipleChoiceRule;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Ramsey\Uuid\Uuid;

class JobApplicationForm extends Component
{
    public JobRequisition $requisition;

    public ?Application $application = null;

    /** @var array<string, mixed> */
    public array $responses = [];

    public function mount(JobRequisition $requisition): void
    {
        $this->requisition = $requisition;

        $this->initializeQuestions($requisition);
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

    public function submit(): Redirector
    {
        $this->validate();
        $applicationId = Uuid::uuid4();
        if (! $this->application instanceof Application) {
            /** @var Candidate $candidate */
            $candidate = auth()->user()->candidate;

            event(new JobAppliedEvent(ApplicationDTO::make([
                'application_id' => (string) $applicationId,
                'requisition_id' => $this->requisition->getKey(),
                'candidate_id' => $candidate->getKey(),
                'team_id' => $this->requisition->team_id,
                'status' => ApplicationStatusEnum::New->value,
                'source' => CandidateSourceEnum::CareerPage->value,
            ])));

        }

        $screeningCollection = new ScreeningResponseCollection();
        foreach ($this->responses as $questionId => $value) {
            if ($value === null) {
                continue;
            }

            $value = is_array($value) ? $value : ['value' => $value];
            $screeningCollection->add(new ScreeningResponseDTO(
                teamId: $this->requisition->team_id,
                applicationId: $applicationId->toString(),
                questionId: $questionId,
                response_value: $value,
            ));
        }

        resolve(StoreScreeningResponse::class)->execute($screeningCollection);

        Notification::make()
            ->title('Your application has been submitted')
            ->success()
            ->send();

        return redirect(route('filament.app.resources.applications.view', ['record' => $applicationId]));
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

            $key = 'responses.'.$question->id;

            if ($question->question_type === QuestionTypeEnum::MultipleChoice) {

                $min = $question->settings['min_selections'] ?? 0;

                $max = $question->settings['max_selections'] ?? null;

                $rules[$key] = [
                    'array',
                    new MultipleChoiceRule($min, $max),
                ];

                continue;
            }

            $rules[$key] = $question->is_required
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
            if ($question->is_required && $question->question_type !== QuestionTypeEnum::MultipleChoice) {
                $messages[sprintf('responses.%s.required', $question->id)]
                    = 'This question is required.';
            }
        }

        return $messages;
    }

    private function initializeQuestions(JobRequisition $requisition): void
    {
        foreach ($requisition->screeningQuestions as $question) {

            $this->responses[$question->id] = null;

            if ($question->question_type === QuestionTypeEnum::MultipleChoice) {
                $this->responses[$question->id] = [];

                foreach ($question->settings['choices'] ?? [] as $choice) {
                    $this->responses[$question->id][$choice['value']] = null;
                }
            }
        }
    }
}
