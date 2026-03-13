<?php

declare(strict_types=1);

use He4rt\App\Livewire\Jobs\SavedJobsWidget;
use He4rt\Candidates\Models\CandidateJobSaved;
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
