<?php

declare(strict_types=1);

use He4rt\App\Livewire\Jobs\SavedJobsWidget;
use He4rt\Candidates\Models\CandidateJobSaved;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->user->refresh();

    $this->candidate = $this->user->candidate;

    actingAs($this->user);
});

it('renders successfully', function (): void {
    livewire(SavedJobsWidget::class)
        ->assertOk();
});

it('loads only the count without fetching the full list when event is received', function (): void {
    $jobs = JobRequisition::factory()->available()->count(3)->create();

    $jobs->each(fn (JobRequisition $job) => CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]));

    $component = livewire(SavedJobsWidget::class)
        ->dispatch('saved-job-toggled');

    expect($component->get('savedJobsCount'))->toBe(3)
        ->and($component->get('savedJobs'))->toBeEmpty()
        ->and($component->get('loaded'))->toBeFalse();
});

it('loads the full list and sets loaded to true when loadJobs is called', function (): void {
    $jobs = JobRequisition::factory()->available()->count(2)->create();

    $jobs->each(fn (JobRequisition $job) => CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]));

    $component = livewire(SavedJobsWidget::class)
        ->call('loadJobs');

    expect($component->get('savedJobs'))->toHaveCount(2)
        ->and($component->get('loaded'))->toBeTrue();
});

it('removes a saved job when remove is called', function (): void {
    $job = JobRequisition::factory()->available()->create();

    CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]);

    livewire(SavedJobsWidget::class)
        ->call('loadJobs')
        ->call('remove', $job->getKey());

    $this->assertDatabaseMissing('job_requisition_bookmarks', [
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]);
});

it('updates the count after removing a saved job', function (): void {
    $job = JobRequisition::factory()->available()->create();

    CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]);

    $component = livewire(SavedJobsWidget::class)
        ->call('loadJobs');

    expect($component->get('savedJobsCount'))->toBe(1);

    $component->call('remove', $job->getKey());

    expect($component->get('savedJobsCount'))->toBe(0)
        ->and($component->get('savedJobs'))->toBeEmpty();
});

it('updates the count when saved-job-toggled event is dispatched', function (): void {
    $component = livewire(SavedJobsWidget::class);

    expect($component->get('savedJobsCount'))->toBe(0);

    $job = JobRequisition::factory()->available()->create();

    CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]);

    $component->dispatch('saved-job-toggled');

    expect($component->get('savedJobsCount'))->toBe(1)
        ->and($component->get('savedJobs'))->toBeEmpty();
});

it('refreshes the full list when saved-job-toggled is dispatched and the widget is already loaded', function (): void {
    $component = livewire(SavedJobsWidget::class)
        ->call('loadJobs');

    expect($component->get('savedJobs'))->toBeEmpty();

    $job = JobRequisition::factory()->available()->create();

    CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]);

    $component->dispatch('saved-job-toggled');

    expect($component->get('savedJobs'))->toHaveCount(1)
        ->and($component->get('savedJobsCount'))->toBe(1);
});

it('does not show a closed job in the saved list and does not count it', function (): void {
    $job = JobRequisition::factory()->available()->create();
    $job->update(['status' => RequisitionStatusEnum::Closed]);

    CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]);

    $component = livewire(SavedJobsWidget::class)
        ->call('loadJobs');

    expect($component->get('savedJobs'))->toBeEmpty()
        ->and($component->get('savedJobsCount'))->toBe(0);
});

it('shows only published jobs when mix of published and closed jobs are saved', function (): void {
    $publishedJob = JobRequisition::factory()->available()->create();
    $closedJob = JobRequisition::factory()->available()->create();
    $closedJob->update(['status' => RequisitionStatusEnum::Closed]);

    CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $publishedJob->getKey(),
    ]);

    CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $closedJob->getKey(),
    ]);

    $component = livewire(SavedJobsWidget::class)
        ->call('loadJobs');

    expect($component->get('savedJobs'))->toHaveCount(1)
        ->and($component->get('savedJobsCount'))->toBe(1);
});

it('does not show a soft-deleted job in the saved list and does not count it', function (): void {
    $job = JobRequisition::factory()->available()->create();

    CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]);

    $job->delete();

    $component = livewire(SavedJobsWidget::class)
        ->call('loadJobs');

    expect($component->get('savedJobs'))->toBeEmpty()
        ->and($component->get('savedJobsCount'))->toBe(0);
});

it('renders saved jobs when employment_type and work_schedule are null', function (): void {
    $job = JobRequisition::factory()
        ->available()
        ->create([
            'employment_type' => null,
            'work_schedule' => null,
        ]);

    CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]);

    $component = livewire(SavedJobsWidget::class)
        ->call('loadJobs')
        ->assertOk();

    expect($component->get('savedJobs'))->toHaveCount(1);

    $jobData = $component->get('savedJobs')[0];

    expect($jobData['employmentType'])->toBeNull()
        ->and($jobData['workSchedule'])->toBeNull()
        ->and($jobData['workArrangement'])->not->toBeNull()
        ->and($jobData['experienceLevel'])->not->toBeNull();
});

it('exposes the work_schedule label when present', function (): void {
    $job = JobRequisition::factory()
        ->available()
        ->create([
            'work_schedule' => WorkScheduleEnum::FullTime,
        ]);

    CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]);

    $component = livewire(SavedJobsWidget::class)
        ->call('loadJobs');

    $jobData = $component->get('savedJobs')[0];

    expect($jobData['workSchedule'])->toBe(WorkScheduleEnum::FullTime->getLabel());
});
