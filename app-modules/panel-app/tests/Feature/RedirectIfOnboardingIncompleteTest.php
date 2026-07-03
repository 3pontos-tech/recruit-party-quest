<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Pages\AppDashboard;
use He4rt\App\Filament\Pages\LandingPage;
use He4rt\App\Filament\Pages\OnboardingWizard;
use He4rt\Permissions\Roles;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::App->value);
});

it('redirects an authenticated candidate with incomplete onboarding to the wizard', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    get(AppDashboard::getUrl())
        ->assertRedirect(OnboardingWizard::getUrl());
});

it('redirects a guest to the landing page instead of the onboarding wizard', function (): void {
    get(AppDashboard::getUrl())
        ->assertRedirect(LandingPage::getUrl());
});

it('does not force onboarding for a super admin', function (): void {
    $user = User::factory()->admin()->create();

    actingAs($user);

    get(AppDashboard::getUrl())
        ->assertOk();
});

it('does not force onboarding for an admin', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::Admin);

    actingAs($user);

    get(AppDashboard::getUrl())
        ->assertOk();
});

it('still forces onboarding for a plain user role', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    expect($user->fresh()->hasAnyRole([Roles::SuperAdmin, Roles::Admin]))->toBeFalse();

    get(AppDashboard::getUrl())
        ->assertRedirect(OnboardingWizard::getUrl());
});

it('stores the blocked url as url.intended when bouncing to the onboarding wizard', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    get(AppDashboard::getUrl())
        ->assertRedirect(OnboardingWizard::getUrl())
        ->assertSessionHas('url.intended', AppDashboard::getUrl());
});
