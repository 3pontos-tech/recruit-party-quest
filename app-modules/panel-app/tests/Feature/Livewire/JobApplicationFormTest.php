<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Livewire\JobApplicationForm;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
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
    JobPosting::factory()->for($this->jobRequisition, 'jobRequisition')->create();

    $this->question = ScreeningQuestion::factory()
        ->for($this->jobRequisition, 'screenable')
        ->state([
            'question_text' => 'fuedase?',
        ])
        // ->fileUpload()
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
        ->set('source', CandidateSourceEnum::LinkedIn)
        ->call('submit')
        ->assertSessionHasNoErrors()
        ->assertDontSeeText('This question is required.')
        ->assertNotified(__('screening::messages.application_submitted'));

    $application = Application::query()->first();
    $livewire->assertRedirect(route('filament.app.resources.applications.view', ['record' => $application->getKey()]));

    assertDatabaseCount(Application::class, 1);
    assertDatabaseHas(Application::class, [
        'requisition_id' => $this->jobRequisition->getKey(),
        'candidate_id' => auth()->user()->candidate->getKey(),
        'team_id' => $this->jobRequisition->team_id,
        'status' => ApplicationStatusEnum::New,
        'source' => CandidateSourceEnum::LinkedIn,
    ]);
    assertDatabaseHas(ScreeningResponse::class, [
        'team_id' => $this->jobRequisition->team_id,
        'application_id' => $application->getKey(),
        'question_id' => $this->question->getKey(),
        'response_value' => json_encode(['files' => $this->file->getFilename()]),
    ]);
})->todo(note: <<<'NOTE'
        For now we disabled file upload question
NOTE
);

test('source question are required', function (): void {
    livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
        ->assertOk()
        ->call('submit')
        ->assertHasErrors(['source' => 'required']);
});

it('should submit successfully when requisition has no screening questions', function (): void {
    $requisition = JobRequisition::factory()->create();
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    $livewire = livewire(JobApplicationForm::class, ['requisition' => $requisition])
        ->set('source', CandidateSourceEnum::LinkedIn)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertNotified(__('screening::messages.application_submitted'));

    $application = Application::query()->first();
    $livewire->assertRedirect(route('filament.app.resources.applications.view', ['record' => $application->getKey()]));

    assertDatabaseCount(Application::class, 1);
    assertDatabaseHas(Application::class, [
        'requisition_id' => $requisition->getKey(),
        'candidate_id' => auth()->user()->candidate->getKey(),
        'team_id' => $requisition->team_id,
        'status' => ApplicationStatusEnum::New,
        'source' => CandidateSourceEnum::LinkedIn,
    ]);
    assertDatabaseCount(ScreeningResponse::class, 0);
})->group('screening');

it('should submit successfully and save screening responses', function (): void {
    $requisition = JobRequisition::factory()->create();
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();
    $question = ScreeningQuestion::factory()
        ->for($requisition, 'screenable')
        ->text()
        ->required()
        ->create();

    $livewire = livewire(JobApplicationForm::class, ['requisition' => $requisition])
        ->set('source', CandidateSourceEnum::LinkedIn)
        ->set('responses.'.$question->getKey(), 'Minha resposta')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertNotified(__('screening::messages.application_submitted'));

    $application = Application::query()->first();
    $livewire->assertRedirect(route('filament.app.resources.applications.view', ['record' => $application->getKey()]));

    assertDatabaseCount(Application::class, 1);
    assertDatabaseHas(ScreeningResponse::class, [
        'team_id' => $requisition->team_id,
        'application_id' => $application->getKey(),
        'question_id' => $question->getKey(),
    ]);

    $screeningResponse = ScreeningResponse::query()
        ->where('application_id', $application->getKey())
        ->where('question_id', $question->getKey())
        ->first();

    expect($screeningResponse->response_value)->toBe(['value' => 'Minha resposta']);
})->group('screening');

it('should assign first stage to application when stages exist', function (): void {
    $requisition = JobRequisition::factory()->create();
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();
    Stage::factory()->create([
        'job_requisition_id' => $requisition->id,
        'team_id' => $requisition->team_id,
    ]);

    livewire(JobApplicationForm::class, ['requisition' => $requisition])
        ->set('source', CandidateSourceEnum::LinkedIn)
        ->call('submit')
        ->assertHasNoErrors();

    $application = Application::query()->where('requisition_id', $requisition->id)->first();
    $firstStage = $requisition->fresh()->stages->sortBy('display_order')->first();

    expect($application->current_stage_id)->toBe($firstStage->getKey());
})->group('screening');
