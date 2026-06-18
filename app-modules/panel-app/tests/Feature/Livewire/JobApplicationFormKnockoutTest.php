<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Livewire\JobApplicationForm;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Events\ScreeningEvaluated;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->candidate = Candidate::factory()->create();
    actingAs($this->candidate->user->refresh());
    filament()->setCurrentPanel(FilamentPanel::App->value);

    // JobRequisitionObserver::created() já cria os 8 stages ordenados.
    $this->requisition = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    JobPosting::factory()->for($this->requisition, 'jobRequisition')->create();

    $this->question = ScreeningQuestion::factory()
        ->yesNo()
        ->required()
        ->knockout(['expected' => 'yes'])
        ->for($this->requisition, 'screenable')
        ->create(['team_id' => $this->requisition->team_id]);
});

it('auto-rejects the candidate who fails the knockout on submit', function (): void {
    livewire(JobApplicationForm::class, ['requisition' => $this->requisition])
        ->set('source', CandidateSourceEnum::LinkedIn)
        ->set('responses.'.$this->question->getKey(), 'no')
        ->call('submit')
        ->assertHasNoErrors();

    $application = Application::query()->first();

    expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($application->rejection_reason_category)->toBe(RejectionReasonCategoryEnum::ScreeningKnockout);

    $response = ScreeningResponse::query()->where('application_id', $application->id)->first();
    expect($response->is_knockout_fail)->toBeTrue();
});

it('auto-advances the candidate who passes the knockout on submit', function (): void {
    $secondStageId = $this->requisition->stages()->orderBy('display_order')->skip(1)->first()->id;

    livewire(JobApplicationForm::class, ['requisition' => $this->requisition])
        ->set('source', CandidateSourceEnum::LinkedIn)
        ->set('responses.'.$this->question->getKey(), 'yes')
        ->call('submit')
        ->assertHasNoErrors();

    $application = Application::query()->first();

    expect($application->status)->toBe(ApplicationStatusEnum::InReview)
        ->and($application->current_stage_id)->toBe($secondStageId);
});

it('does not break the candidate submission when a screening side-effect listener throws', function (): void {
    // A failing recruiter-automation side-effect must NOT 500 a candidate
    // whose application was actually saved successfully.
    Event::listen(ScreeningEvaluated::class, function (): void {
        throw new RuntimeException('downstream side-effect blew up');
    });

    livewire(JobApplicationForm::class, ['requisition' => $this->requisition])
        ->set('source', CandidateSourceEnum::LinkedIn)
        ->set('responses.'.$this->question->getKey(), 'yes')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertNotified(__('screening::messages.application_submitted'));

    assertDatabaseHas(Application::class, [
        'requisition_id' => $this->requisition->id,
    ]);

    assertDatabaseHas(ScreeningResponse::class, [
        'question_id' => $this->question->getKey(),
    ]);
});
