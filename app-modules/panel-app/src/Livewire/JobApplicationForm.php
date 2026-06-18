<?php

declare(strict_types=1);

namespace He4rt\App\Livewire;

use Filament\Notifications\Notification;
use He4rt\Applications\Actions\ApplyToJobRequisitionAction;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Collections\ScreeningResponseCollection;
use He4rt\Screening\DTOs\ScreeningResponseDTO;
use He4rt\Screening\Events\ScreeningResponsesSubmitted;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class JobApplicationForm extends Component
{
    public JobRequisition $requisition;

    public ?Application $application = null;

    #[Rule('required')]
    public CandidateSourceEnum|string $source = '';

    /** @var array<string, mixed> */
    public array $responses = [];

    public function mount(JobRequisition $requisition): void
    {
        if (auth()->check()) {
            $this->authorize('create', Application::class);
        }

        $this->requisition = $requisition;

        $this->initializeQuestions($requisition);
    }

    /**
     * @param  array<string<string,string>,string>  $data
     */
    #[On('file-uploaded')]
    public function handleFileUploaded(array $data): void
    {
        $this->responses[$data['questionId']] = ['files' => $data['files']];
    }

    public function submit(): Redirector|RedirectResponse
    {
        $this->validate();

        if (! $this->application instanceof Application) {
            /** @var Candidate $candidate */
            $candidate = auth()->user()->candidate;

            $source = $this->source instanceof CandidateSourceEnum
                ? $this->source
                : CandidateSourceEnum::from($this->source);

            $this->application = resolve(ApplyToJobRequisitionAction::class)
                ->execute($this->requisition, $candidate, $source);
        }

        event(new ScreeningResponsesSubmitted($this->application, $this->buildResponseCollection()));

        Notification::make()
            ->title(__('screening::messages.application_submitted'))
            ->success()
            ->send();

        return redirect(route('filament.app.resources.applications.view', ['record' => $this->application->getKey()]));
    }

    public function render(): View|Factory|\Illuminate\View\View
    {
        $requiredQuestionIds = $this->requisition->screeningQuestions
            ->where('is_required', true)
            ->pluck('id')
            ->toArray();

        return view('panel-app::livewire.job-application-form', [
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

    /**
     * Build the screening response collection from the current form state.
     */
    private function buildResponseCollection(): ScreeningResponseCollection
    {
        $collection = new ScreeningResponseCollection();

        foreach ($this->responses as $questionId => $value) {
            if ($value === null) {
                continue;
            }

            $isStructuredResponse = is_array($value)
                && (array_key_exists('value', $value) || array_key_exists('files', $value));

            $collection->add(new ScreeningResponseDTO(
                teamId: $this->requisition->team_id,
                applicationId: $this->application->getKey(),
                questionId: $questionId,
                response_value: $isStructuredResponse ? $value : ['value' => $value],
            ));
        }

        return $collection;
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
