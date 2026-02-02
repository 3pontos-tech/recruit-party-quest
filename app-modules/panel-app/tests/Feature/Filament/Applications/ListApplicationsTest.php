<?php

declare(strict_types=1);

use He4rt\App\Filament\Resources\Applications\Pages\ListApplications;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;
use Illuminate\Support\Collection;

use function Pest\Laravel\actingAs;
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

    $this->applications = Application::factory()->count(3)
        ->for($this->candidate, 'candidate')
        ->create();
});

it('renders the applications list page successfully', function (): void {
    livewire(ListApplications::class)
        ->assertOk();
});

it('shows applications table with correct structure', function (): void {
    livewire(ListApplications::class)
        ->assertOk()
        ->assertSee('Post')
        ->assertSee('Current stage')
        ->assertSee('Status');
});
