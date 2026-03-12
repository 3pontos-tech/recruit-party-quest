<?php

declare(strict_types=1);

use He4rt\App\Livewire\Jobs\SavedJobsWidget;
use He4rt\Candidates\Models\SavedJob;
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

it('displays saved jobs for the authenticated candidate', function (): void {
    $jobs = JobRequisition::factory()->available()->count(2)->create();

    $jobs->each(fn (JobRequisition $job) => SavedJob::factory()->create([
        'candidate_id' => $this->candidate->id,
        'job_requisition_id' => $job->id,
    ]));

    $component = livewire(SavedJobsWidget::class);

    expect($component->get('savedJobs'))->toHaveCount(2);
});

it('removes a saved job when remove is called', function (): void {
    $job = JobRequisition::factory()->available()->create();

    SavedJob::factory()->create([
        'candidate_id' => $this->candidate->id,
        'job_requisition_id' => $job->id,
    ]);

    livewire(SavedJobsWidget::class)
        ->call('remove', $job->id);

    $this->assertDatabaseMissing('candidate_saved_jobs', [
        'candidate_id' => $this->candidate->id,
        'job_requisition_id' => $job->id,
    ]);
});

it('refreshes saved jobs list when saved-job-toggled event is dispatched', function (): void {
    $component = livewire(SavedJobsWidget::class);

    expect($component->get('savedJobs'))->toHaveCount(0);

    $job = JobRequisition::factory()->available()->create();

    SavedJob::factory()->create([
        'candidate_id' => $this->candidate->id,
        'job_requisition_id' => $job->id,
    ]);

    $component->dispatch('saved-job-toggled');

    expect($component->get('savedJobs'))->toHaveCount(1);
});
