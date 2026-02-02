<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use He4rt\Admin\Filament\Resources\Recruitment\Candidates\Pages\CreateCandidate;
use He4rt\Candidates\Models\Candidate;
use He4rt\Permissions\Roles;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $this->authUser = User::factory()->createQuietly();
    actingAs($this->authUser);

    artisan('sync:permissions');

    $this->authUser->assignRole(Roles::SuperAdmin->value);

    $this->team = Team::factory()->createQuietly();
    $this->team->members()->attach($this->authUser);

    Filament::setTenant($this->team);
});

it('should render', function (): void {
    livewire(CreateCandidate::class)
        ->assertOk();
});

it('should be able to create a candidate', function (): void {
    $user = User::factory()->createQuietly();
    $this->team->members()->attach($user);

    $availabilityDate = now()->addDays(30)->format('Y-m-d');

    livewire(CreateCandidate::class)
        ->fillForm([
            'user_id' => $user->getKey(),
            'phone_number' => '+5511999999999',
            'headline' => 'Desenvolvedor Full Stack',
            'summary' => 'Desenvolvedor com 5 anos de experiência',
            'availability_date' => $availabilityDate,
            'willing_to_relocate' => true,
            'is_open_to_remote' => true,
            'expected_salary' => 10000,
            'expected_salary_currency' => 'BRL',
            'linkedin_url' => 'https://linkedin.com/in/joao',
            'portfolio_url' => 'https://joao.dev',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Candidate::class, 1);
});

it('should not allow users without permission to access', function (): void {
    auth()->user()->removeRole(Roles::SuperAdmin->value);

    livewire(CreateCandidate::class)
        ->assertForbidden();
});

it('should redirect after successful creation', function (): void {
    $user = User::factory()->createQuietly();
    $this->team->members()->attach($user);

    $availabilityDate = now()->addDays(30)->format('Y-m-d');

    livewire(CreateCandidate::class)
        ->fillForm([
            'user_id' => $user->getKey(),
            'phone_number' => '+5511999999999',
            'headline' => 'Desenvolvedor Full Stack',
            'summary' => 'Desenvolvedor com 5 anos de experiência',
            'availability_date' => $availabilityDate,
            'willing_to_relocate' => true,
            'is_open_to_remote' => true,
            'expected_salary' => 10000,
            'expected_salary_currency' => 'BRL',
            'linkedin_url' => 'https://linkedin.com/in/joao',
            'portfolio_url' => 'https://joao.dev',
        ])
        ->call('create')
        ->assertRedirect();
});
