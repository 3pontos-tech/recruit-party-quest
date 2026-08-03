<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Resources\Applications\Pages\ListApplications;
use He4rt\App\Livewire\ProfileCard;
use He4rt\Permissions\Roles;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
    $this->admin->assignRole(Roles::SuperAdmin);

    actingAs($this->admin);
    filament()->setCurrentPanel(FilamentPanel::App->value);
});

it('renders the applications list for an admin without a candidate profile', function (): void {
    livewire(ListApplications::class)->assertOk();
});

it('renders the profile card for an admin without a candidate profile', function (): void {
    livewire(ProfileCard::class)
        ->assertOk()
        ->assertSet('profileCompletionPercentage', 0);
});
