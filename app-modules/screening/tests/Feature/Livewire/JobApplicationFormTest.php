<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Livewire\JobApplicationForm;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->candidate = Candidate::factory()->create();
    $user = $this->candidate->user->refresh();
    actingAs($user);
    filament()->setCurrentPanel(FilamentPanel::App->value);
    $this->jobRequisition = JobRequisition::factory()->create();

    $this->question = ScreeningQuestion::factory()
        ->for($this->jobRequisition, 'screenable')
        ->state([
            'question_text' => 'fuedase?',
        ])
        ->fileUpload()
        ->required()
        ->create();
    $this->stage = Stage::factory()->recycle($this->jobRequisition)->create();

    Storage::fake('public');
    $this->file = UploadedFile::fake()->create('curriculum.pdf');

});

it('should render', function (): void {
    livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
        ->assertOk();
});

it('should answer screening questions that are file upload', function (): void {
    $questionId = $this->question->getKey();
    $filePayload = ['questionId' => $questionId, 'files' => $this->file->getFilename()];

    $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
        ->assertOk()
        ->call('handleFileUploaded', $filePayload)
        ->assertSet(sprintf('responses.%s.files', $questionId), $this->file->getfilename())
        ->call('submit')
        ->assertSessionHasNoErrors()
        ->assertDontSeeText('This question is required.')
        ->assertNotified('Your application has been submitted');

    $application = Application::query()->first();
    $livewire->assertRedirect(route('filament.app.resources.applications.view', ['record' => $application->getKey()]));

    assertDatabaseCount(Application::class, 1);
    assertDatabaseHas(Application::class, [
        'requisition_id' => $this->jobRequisition->getKey(),
        'candidate_id' => auth()->user()->candidate->getKey(),
        'team_id' => $this->jobRequisition->team_id,
        'status' => ApplicationStatusEnum::New,
        'source' => CandidateSourceEnum::CareerPage,
    ]);
    assertDatabaseHas(ScreeningResponse::class, [
        'team_id' => $this->jobRequisition->team_id,
        'application_id' => $application->getKey(),
        'question_id' => $this->question->getKey(),
        'response_value' => json_encode(['files' => $this->file->getFilename()]),
    ]);
});

// TODO: Separate for each type of question
test('required validation', function (): void {
    $questionId = $this->question->getKey();
    $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
        ->assertOk()
        ->call('submit');
    $livewire->assertHasErrors(['responses.'.$questionId => 'This question is required.']);
});

describe('multiple questions', function (): void {
    test('min', function (): void {
        $min = 1;
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->state([
                'question_text' => 'wich options fuedase?',
            ])
            ->multipleChoice($min)
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => sprintf('Selecione pelo menos %d opção(ões).', $min)]);

    });
    test('max', function (): void {
        $min = 1;
        $max = 1;
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->state([
                'question_text' => 'wich options fuedase?',
            ])
            ->multipleChoice($min, $max)
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->set('responses.'.$question->getKey(), [0 => 'oi', 1 => 'iai'])
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => sprintf('Selecione no máximo %d opção(ões).', $max)]);

    });
    test('null should be considere empty', function (): void {
        $min = 1;
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->state([
                'question_text' => 'wich options fuedase?',
            ])
            ->multipleChoice($min)
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->set('responses.'.$question->getKey(), [0 => null, 1 => null])
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => sprintf('Selecione pelo menos %d opção(ões).', $min)]);

    });
});
