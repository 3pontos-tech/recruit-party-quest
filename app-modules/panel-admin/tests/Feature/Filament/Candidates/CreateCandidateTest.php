<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use He4rt\Admin\Filament\Resources\Recruitment\Candidates\Pages\CreateCandidate;
use He4rt\Permissions\Roles;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->create());

    // Sync permissions so we have some to work with
    artisan('sync:permissions');

    // Give the user SuperAdmin role to bypass all policies
    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

it('should render', function (): void {
    livewire(CreateCandidate::class)
        ->assertOk();
});

it('should be able to create a candidate', function (): void {
    $live = livewire(CreateCandidate::class)
        ->fillForm([
            'user_id' => User::factory()->create()->getKey(),
            'name' => '',
            'email' => '',
            'password' => '',
            'phone_number' => '',
            'headline' => '',
            'summary' => '',
            'availability_date' => '',
            'willing_to_relocate' => '',
            'is_open_to_remote' => '',
            'expected_salary' => '',
            'expected_salary_currency' => '',
            'linkedin_url' => '',
            'portfolio_url' => '',
        ])
        ->call('create');
});
