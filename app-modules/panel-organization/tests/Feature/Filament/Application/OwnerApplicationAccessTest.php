<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\ApplicationResource;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\CreateApplication;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\EditApplication;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ListApplications;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);

    $this->owner = User::factory()->create();
    $this->owner->assignRole(Roles::Owner->value);

    actingAs($this->owner);

    $this->team = Team::factory()->create(['owner_id' => $this->owner->id]);
    $this->application = Application::factory()->create(['team_id' => $this->team->id]);

    JobPosting::factory()->for($this->application->requisition)->create();

    filament()->setTenant($this->team);
});

it('owner cannot create an application in the organization panel', function (): void {
    expect(ApplicationResource::canCreate())->toBeFalse();

    livewire(CreateApplication::class, ['tenant' => $this->team])
        ->assertForbidden();
});

it('owner cannot edit an application in the organization panel', function (): void {
    expect(ApplicationResource::canEdit($this->application))->toBeFalse();

    livewire(EditApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])->assertForbidden();
});

it('owner does not see the edit button in the applications table', function (): void {
    livewire(ListApplications::class, ['tenant' => $this->team])
        ->assertOk()
        ->assertTableActionHidden('edit', $this->application);
});

it('owner cannot see quick actions when viewing an application', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->assertOk()
        ->assertActionHidden('state-transition-action')
        ->assertActionHidden('comment_application-action')
        ->assertActionHidden('reject_application-action');
})->skip();
