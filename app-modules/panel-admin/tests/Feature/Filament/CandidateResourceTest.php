<?php

declare(strict_types=1);

namespace He4rt\Admin\Tests\Feature\Filament;

use Filament\Facades\Filament;
use He4rt\Admin\Filament\Resources\Candidates\CandidateResource;
use He4rt\Admin\Filament\Resources\Candidates\Pages\CreateCandidate;
use He4rt\Admin\Filament\Resources\Candidates\Pages\EditCandidate;
use He4rt\Admin\Filament\Resources\Candidates\Pages\ListCandidates;
use He4rt\Candidates\Models\Candidate;
use He4rt\Permissions\Roles;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->admin()->create());

    // Sync permissions so we have some to work with
    artisan('sync:permissions');

    // Give the user SuperAdmin role to bypass all policies
    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

it('can list candidates', function (): void {
    $candidates = Candidate::factory()->count(3)->create();

    livewire(ListCandidates::class)
        ->assertCanSeeTableRecords($candidates->load('user'));
});

it('can render create candidate page', function (): void {
    $this->get(CandidateResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render edit candidate page', function (): void {
    $candidate = Candidate::factory()->create();

    $this->get(CandidateResource::getUrl('edit', ['record' => $candidate]))
        ->assertSuccessful();
});

it('can create a candidate', function (): void {
    $user = User::factory()->create();

    livewire(CreateCandidate::class)
        ->fillForm([
            'user_id' => $user->id,
            'phone_number' => '+1234567890',
            'is_open_to_remote' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('candidates', [
        'user_id' => $user->id,
        'phone_number' => '+1234567890',
        'is_open_to_remote' => true,
    ]);
});

it('can update a candidate', function (): void {
    $candidate = Candidate::factory()->create();

    livewire(EditCandidate::class, ['record' => $candidate->id])
        ->fillForm([
            'headline' => 'Senior Developer',
            'willing_to_relocate' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('candidates', [
        'id' => $candidate->id,
        'headline' => 'Senior Developer',
        'willing_to_relocate' => true,
    ]);
});
