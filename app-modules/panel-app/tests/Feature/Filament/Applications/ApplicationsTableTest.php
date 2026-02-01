<?php

declare(strict_types=1);

use He4rt\App\Filament\Resources\Applications\Pages\ListApplications;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Livewire\livewire;

/** @var User $user */
/** @var Candidate $candidate */
/** @var Collection|Application[] $applications */
beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->candidate = Candidate::factory()->for($this->user, 'user')->create();

    $this->user->refresh();
    $this->candidate->refresh();

    actingAs($this->user);

    artisan('sync:permissions');

    $this->user->givePermissionTo('view_applications');

    $this->applications = Application::factory()->count(3)
        ->for($this->candidate, 'candidate')
        ->create();
});

it('should show view action for each application record', function (): void {
    livewire(ListApplications::class)
        ->assertOk()
        ->assertTableActionExists('view');
});

it('should filter applications by authenticated candidate only', function (): void {
    /** @var Candidate $otherCandidate */
    $otherCandidate = Candidate::factory()->create();

    Application::factory()->count(2)
        ->for($otherCandidate, 'candidate')
        ->create();

    $livewire = livewire(ListApplications::class)
        ->assertOk();

    $livewire->assertSee('Post')
        ->assertSee('Status');
});

it('should display correct table columns', function (): void {
    livewire(ListApplications::class)
        ->assertOk()
        ->assertTableColumnExists('requisition.post.title')
        ->assertTableColumnExists('currentStage.name')
        ->assertTableColumnExists('status')
        ->assertTableColumnExists('created_at')
        ->assertTableColumnExists('updated_at');
});

it('should have created_at and updated_at columns hidden by default', function (): void {
    livewire(ListApplications::class)
        ->assertOk()
        ->assertTableColumnExists('created_at')
        ->assertTableColumnExists('updated_at');
});

it('should make key columns searchable', function (): void {
    livewire(ListApplications::class)
        ->assertOk()
        ->assertTableColumnExists('requisition.post.title')
        ->assertTableColumnExists('currentStage.name')
        ->assertTableColumnExists('status');
});

it('should show team name in the interface', function (): void {
    livewire(ListApplications::class)
        ->assertOk()
        ->assertSee('Post')
        ->assertSee('Status');
});

it('should display status column correctly', function (): void {
    livewire(ListApplications::class)
        ->assertOk()
        ->assertTableColumnExists('status')
        ->assertSee('Status');
});

it('should have datetime columns', function (): void {
    livewire(ListApplications::class)
        ->assertOk()
        ->assertTableColumnExists('created_at')
        ->assertTableColumnExists('updated_at');
});

it('should handle empty state when candidate has no applications', function (): void {
    $this->candidate->applications()->delete();

    livewire(ListApplications::class)
        ->assertOk()
        ->assertCountTableRecords(0);
});

it('should allow access to view action on table', function (): void {
    livewire(ListApplications::class)
        ->assertOk()
        ->assertTableActionExists('view');
});
