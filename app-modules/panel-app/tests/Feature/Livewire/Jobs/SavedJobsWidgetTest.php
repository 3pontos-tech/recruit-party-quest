<?php

declare(strict_types=1);

use He4rt\App\Livewire\Jobs\SavedJobsWidget;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\CandidateJobSaved;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Users\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->user->refresh();

    $this->candidate = $this->user->candidate;
    actingAs($this->user);

    Livewire::withoutLazyLoading();
});

function saveJobForCandidate(JobRequisition $job, Candidate $candidate): void
{
    CandidateJobSaved::factory()->create([
        'candidate_id' => $candidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]);
}

it('renders successfully', function (): void {
    livewire(SavedJobsWidget::class)->assertOk();
});

it('loads the saved jobs count on mount', function (): void {
    JobRequisition::factory()->available()->count(3)->create()
        ->each(fn (JobRequisition $job) => saveJobForCandidate($job, $this->candidate));

    expect(livewire(SavedJobsWidget::class)->get('savedJobsCount'))->toBe(3);
});

it('updates the count when saved-job-toggled is dispatched', function (): void {
    $component = livewire(SavedJobsWidget::class);
    expect($component->get('savedJobsCount'))->toBe(0);

    $job = JobRequisition::factory()->available()->create();
    saveJobForCandidate($job, $this->candidate);

    $component->dispatch('saved-job-toggled');
    expect($component->get('savedJobsCount'))->toBe(1);
});

it('exposes the viewSavedJobs action', function (): void {
    livewire(SavedJobsWidget::class)->assertActionExists('viewSavedJobs');
});

it('shows saved published jobs inside the slideover', function (): void {
    $job = JobRequisition::factory()->available()->create();
    saveJobForCandidate($job, $this->candidate);

    livewire(SavedJobsWidget::class)
        ->mountAction('viewSavedJobs')
        ->assertMountedActionModalSee($job->post->title);
});

it('removes a saved job when removeSavedJob is called', function (): void {
    $job = JobRequisition::factory()->available()->create();
    saveJobForCandidate($job, $this->candidate);

    livewire(SavedJobsWidget::class)
        ->call('removeSavedJob', $job->getKey())
        ->assertNotNotified();

    assertDatabaseMissing('job_requisition_bookmarks', [
        'candidate_id' => $this->candidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]);
});

it('decrements the count after removing a saved job', function (): void {
    $job = JobRequisition::factory()->available()->create();
    saveJobForCandidate($job, $this->candidate);

    $component = livewire(SavedJobsWidget::class);
    expect($component->get('savedJobsCount'))->toBe(1);

    $component->call('removeSavedJob', $job->getKey());

    expect($component->get('savedJobsCount'))->toBe(0);
});

it('does not count nor show a closed job', function (): void {
    $job = JobRequisition::factory()->available()->create();
    $job->update(['status' => RequisitionStatusEnum::Closed]);
    saveJobForCandidate($job, $this->candidate);

    $component = livewire(SavedJobsWidget::class);

    expect($component->get('savedJobsCount'))->toBe(0);
    $component->mountAction('viewSavedJobs')->assertMountedActionModalDontSee($job->post->title);
});

it('does not count nor show a soft-deleted job', function (): void {
    $job = JobRequisition::factory()->available()->create();
    saveJobForCandidate($job, $this->candidate);
    $job->delete();

    expect(livewire(SavedJobsWidget::class)->get('savedJobsCount'))->toBe(0);
});

it('shows only published jobs when mixing published and closed', function (): void {
    $published = JobRequisition::factory()->available()->create();
    $published->post()->update(['title' => 'Visible Published Role']);

    $closed = JobRequisition::factory()->available()->create();
    $closed->post()->update(['title' => 'Hidden Closed Role']);
    $closed->update(['status' => RequisitionStatusEnum::Closed]);

    saveJobForCandidate($published, $this->candidate);
    saveJobForCandidate($closed, $this->candidate);

    $component = livewire(SavedJobsWidget::class);

    expect($component->get('savedJobsCount'))->toBe(1);
    $component->mountAction('viewSavedJobs')
        ->assertMountedActionModalSee('Visible Published Role')
        ->assertMountedActionModalDontSee('Hidden Closed Role');
});

it('shows the empty state when there are no saved jobs', function (): void {
    livewire(SavedJobsWidget::class)
        ->mountAction('viewSavedJobs')
        ->assertMountedActionModalSee(__('panel-app::filament.components.saved_jobs_widget.empty_title'));
});

it('does not remove a saved job that belongs to another candidate', function (): void {
    $otherUser = User::factory()->create();
    $otherUser->refresh();

    $otherCandidate = $otherUser->candidate;

    $job = JobRequisition::factory()->available()->create();
    saveJobForCandidate($job, $otherCandidate);

    livewire(SavedJobsWidget::class)
        ->call('removeSavedJob', $job->getKey());

    $this->assertDatabaseHas('job_requisition_bookmarks', [
        'candidate_id' => $otherCandidate->getKey(),
        'job_requisition_id' => $job->getKey(),
    ]);
});

it('masks the company name for confidential saved jobs', function (): void {
    $job = JobRequisition::factory()->available()->create(['is_confidential' => true]);
    $job->team->update(['name' => 'Strictly Confidential Employer']);
    saveJobForCandidate($job, $this->candidate);

    livewire(SavedJobsWidget::class)
        ->mountAction('viewSavedJobs')
        ->assertMountedActionModalSee(__('panel-app::filament.confidential.company_name'))
        ->assertMountedActionModalDontSee('Strictly Confidential Employer');
});

it('renders saved jobs when employment_type and work_schedule are null', function (): void {
    $job = JobRequisition::factory()->available()->create([
        'employment_type' => null,
        'work_schedule' => null,
    ]);
    saveJobForCandidate($job, $this->candidate);

    livewire(SavedJobsWidget::class)
        ->mountAction('viewSavedJobs')
        ->assertOk()
        ->assertMountedActionModalSee($job->post->title);
});
