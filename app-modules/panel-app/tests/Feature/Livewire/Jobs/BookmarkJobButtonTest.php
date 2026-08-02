<?php

declare(strict_types=1);

use He4rt\App\Livewire\Jobs\BookmarkJobButton;
use He4rt\Candidates\Actions\EnsureCandidateProfile;
use He4rt\Candidates\Models\CandidateJobSaved;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    $this->candidate = resolve(EnsureCandidateProfile::class)->execute($this->user);
    $this->user->setRelation('candidate', $this->candidate);
    $this->job = JobRequisition::factory()->create();

    actingAs($this->user);
});

it('renders successfully', function (): void {
    livewire(BookmarkJobButton::class, ['job' => $this->job])
        ->assertOk();
});

it('starts with isSaved false when job is not bookmarked', function (): void {
    livewire(BookmarkJobButton::class, ['job' => $this->job])
        ->assertSet('isSaved', false);
});

it('starts with isSaved true when job is already bookmarked', function (): void {
    CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->id,
        'job_requisition_id' => $this->job->id,
    ]);

    livewire(BookmarkJobButton::class, ['job' => $this->job])
        ->assertSet('isSaved', true);
});

it('saves the job and notifies when toggled and not yet bookmarked', function (): void {
    livewire(BookmarkJobButton::class, ['job' => $this->job])
        ->call('toggle')
        ->assertSet('isSaved', true)
        ->assertNotified();

    $this->assertDatabaseHas('job_requisition_bookmarks', [
        'candidate_id' => $this->candidate->id,
        'job_requisition_id' => $this->job->id,
    ]);
});

it('removes the bookmark without notifying when toggled and already saved', function (): void {
    CandidateJobSaved::factory()->create([
        'candidate_id' => $this->candidate->id,
        'job_requisition_id' => $this->job->id,
    ]);

    livewire(BookmarkJobButton::class, ['job' => $this->job])
        ->call('toggle')
        ->assertSet('isSaved', false)
        ->assertNotNotified();

    $this->assertDatabaseMissing('job_requisition_bookmarks', [
        'candidate_id' => $this->candidate->id,
        'job_requisition_id' => $this->job->id,
    ]);
});

it('dispatches saved-job-toggled event on toggle', function (): void {
    livewire(BookmarkJobButton::class, ['job' => $this->job])
        ->call('toggle')
        ->assertDispatched('saved-job-toggled');
});
