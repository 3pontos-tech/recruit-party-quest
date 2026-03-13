<?php

declare(strict_types=1);

use He4rt\App\Filament\Pages\OnboardingWizard;
use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('redirects unauthenticated users to login', function (): void {
    get(route('filament.app.pages.onboarding'))
        ->assertRedirect(route('filament.app.auth.login'));
});

it('renders the onboarding page for authenticated users without a candidate', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    livewire(OnboardingWizard::class)
        ->assertOk();
});

it('renders the onboarding page for authenticated users with an incomplete onboarding', function (): void {
    $user = User::factory()->create();
    Candidate::factory()->for($user, 'user')->create([
        'is_onboarded' => false,
    ]);

    $user->refresh();

    actingAs($user);

    livewire(OnboardingWizard::class)
        ->assertOk();
});

it('redirects non-onboarding pages to onboarding when candidate has not completed onboarding', function (): void {
    $user = User::factory()->create();
    Candidate::factory()->for($user, 'user')->create([
        'is_onboarded' => false,
    ]);

    $user->refresh();

    actingAs($user);

    get(route('filament.app.pages.dashboard'))
        ->assertRedirect(route('filament.app.pages.onboarding'));
});

it('redirects non-onboarding pages to onboarding when user has no candidate', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    get(route('filament.app.pages.dashboard'))
        ->assertRedirect(route('filament.app.pages.onboarding'));
});
