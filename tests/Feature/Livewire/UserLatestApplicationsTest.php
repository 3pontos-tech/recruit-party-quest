<?php

declare(strict_types=1);

use He4rt\App\Livewire\UserLatestApplications;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;

use function Pest\Livewire\livewire;

it('renders successfully', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertStatus(200);
});

it('displays user applications', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);

    Application::factory()->count(3)->for($candidate)->create();

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertStatus(200);
});

it('paginates applications', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    Application::factory()->count(10)->for($candidate)->create();

    $this->actingAs($user);

    $component = livewire(UserLatestApplications::class);
    $applications = $component->get('applications');

    expect($applications->count())->toBe(4);
});

it('filters by search query', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);

    Application::factory()->count(3)->for($candidate)->create();

    $this->actingAs($user);

    $component = livewire(UserLatestApplications::class)
        ->set('search', 'test');

    $applications = $component->get('applications');

    expect($applications->count())->toBe(0)
        ->and($component->get('search'))->toBe('test');
});

it('filters by status', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    $app1 = Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::New]);
    $app2 = Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::OfferExtended]);

    $this->actingAs($user);

    $component = livewire(UserLatestApplications::class)
        ->call('filterByStatus', 'in_review');

    $applications = $component->get('applications');

    expect($applications->contains($app1))->toBeTrue()
        ->and($applications->contains($app2))->toBeFalse();
});

it('resets page on search', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    Application::factory()->count(10)->for($candidate)->create();

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->set('search', '')
        ->set('paginators.page', 2)
        ->set('search', 'test')
        ->assertSet('paginators.page', 1);
});

it('eager loads relationships to prevent N+1 queries', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);

    Application::factory()->count(5)->for($candidate)->create();

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertStatus(200);
});

it('displays empty state when no applications', function (): void {
    $user = User::factory()->create();
    Candidate::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertSee('No applications found');
});

it('shows status badge for rejected application', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->rejected()->for($candidate)->create();

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertSee($application->status->getLabel());
});

it('shows status badge for withdrawn application', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::Withdrawn]);

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertSee($application->status->getLabel());
});

it('shows status badge when application has no visible stages', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::InReview]);

    $application->requisition->stages()->update(['hidden' => true]);

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertSee($application->status->getLabel());
});

it('shows pipeline progress bar for active application', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::InReview]);

    $stage = Stage::factory()->create([
        'job_requisition_id' => $application->requisition_id,
        'hidden' => false,
        'active' => true,
        'display_order' => 1,
    ]);
    $application->update(['current_stage_id' => $stage->id]);

    $application->requisition->stages()->where('id', '!=', $stage->id)->update(['hidden' => true]);

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertSee($stage->name);
});

it('shows correct stage position in pipeline progress', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::InReview]);

    $stage1 = Stage::factory()->create(['job_requisition_id' => $application->requisition_id, 'hidden' => false, 'active' => true, 'display_order' => 1]);
    $stage2 = Stage::factory()->create(['job_requisition_id' => $application->requisition_id, 'hidden' => false, 'active' => true, 'display_order' => 2]);
    $stage3 = Stage::factory()->create(['job_requisition_id' => $application->requisition_id, 'hidden' => false, 'active' => true, 'display_order' => 3]);

    $application->update(['current_stage_id' => $stage2->id]);

    $application->requisition->stages()
        ->whereNotIn('id', [$stage1->id, $stage2->id, $stage3->id])
        ->update(['hidden' => true]);

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertSee('Stage 2/3');
});

it('applies opacity to rejected application card', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    Application::factory()->rejected()->for($candidate)->create();

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertSeeHtml('opacity-60');
});

it('applies opacity to withdrawn application card', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::Withdrawn]);

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertSeeHtml('opacity-60');
});

it('does not apply opacity to active application card', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::New]);

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertDontSeeHtml('opacity-60');
});

it('toggles off status filter when same filter is called again', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->call('filterByStatus', 'in_review')
        ->assertSet('statusFilter', 'in_review')
        ->call('filterByStatus', 'in_review')
        ->assertSet('statusFilter', null);
});

it('filters by interview status group', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    $inProgressApp = Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::InProgress]);
    $newApp = Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::New]);

    $this->actingAs($user);

    $component = livewire(UserLatestApplications::class)
        ->call('filterByStatus', 'interview');

    $applications = $component->get('applications');

    expect($applications->contains($inProgressApp))->toBeTrue()
        ->and($applications->contains($newApp))->toBeFalse();
});

it('filters by offer status group', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    $offerApp = Application::factory()->withOffer()->for($candidate)->create();
    $newApp = Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::New]);

    $this->actingAs($user);

    $component = livewire(UserLatestApplications::class)
        ->call('filterByStatus', 'offer');

    $applications = $component->get('applications');

    expect($applications->contains($offerApp))->toBeTrue()
        ->and($applications->contains($newApp))->toBeFalse();
});

it('applies search and status filter together', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);

    $requisition = JobRequisition::factory()->create();
    JobPosting::factory()->create([
        'job_requisition_id' => $requisition->id,
        'title' => 'Unique RPQ Test Position',
    ]);

    $matchingApp = Application::factory()->for($candidate)->create([
        'status' => ApplicationStatusEnum::InReview,
        'requisition_id' => $requisition->id,
    ]);
    $nonMatchingApp = Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::InReview]);

    $this->actingAs($user);

    $component = livewire(UserLatestApplications::class)
        ->set('search', 'Unique RPQ Test Position')
        ->call('filterByStatus', 'in_review');

    $applications = $component->get('applications');

    expect($applications->contains($matchingApp))->toBeTrue()
        ->and($applications->contains($nonMatchingApp))->toBeFalse();
});
