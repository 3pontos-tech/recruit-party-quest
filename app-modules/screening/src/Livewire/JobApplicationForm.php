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
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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

    public function submit(): Redirector|RedirectResponse
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

            $settings = $question->question_type->settings($question->settings ?? []);

            $rules[$key] = $settings->rules(
                $key,
                required: $question->is_required,
            );
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
            $fieldKey = 'responses.'.$question->id;
            $questionMessages = $question->question_type
                ->settings($question->settings ?? [])
                ->messages($fieldKey);

            $messages = array_merge($messages, $questionMessages);
        }

        return $messages;
    }

    private function initializeQuestions(JobRequisition $requisition): void
    {
        foreach ($requisition->screeningQuestions as $question) {

            $this->responses[$question->getKey()] = $question->question_type
                ->settings($question->settings ?? [])
                ->initialValue();
        }
    }
}
