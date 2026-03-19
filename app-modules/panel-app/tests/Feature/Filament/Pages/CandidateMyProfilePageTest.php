<?php

declare(strict_types=1);

use He4rt\App\Filament\Pages\CandidateMyProfilePage;
use He4rt\Users\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('prevents unauthenticated users from accessing the page', function (): void {
    get(CandidateMyProfilePage::getUrl())
        ->assertForbidden();
});

it('allows authenticated users with completed onboarding to access my profile page', function (): void {
    $user = User::factory()->create();
    $user->refresh();

    // Mark onboarding as complete
    $user->candidate->update([
        'is_onboarded' => true,
        'onboarding_completed_at' => now(),
    ]);

    actingAs($user);

    get(CandidateMyProfilePage::getUrl())
        ->assertOk();
});

it('renders the my profile page successfully for authenticated users', function (): void {
    $user = User::factory()->create();
    $user->refresh();

    // Mark onboarding as complete
    $user->candidate->update([
        'is_onboarded' => true,
        'onboarding_completed_at' => now(),
    ]);

    actingAs($user);

    Livewire::test(CandidateMyProfilePage::class)
        ->assertOk();
});

it('prevents guests from accessing the Livewire component', function (): void {
    Livewire::test(CandidateMyProfilePage::class)
        ->assertForbidden();
});

it('ensures the route is protected', function (): void {
    expect(CandidateMyProfilePage::canAccess())
        ->toBeFalse();

    actingAs(User::factory()->create());

    expect(CandidateMyProfilePage::canAccess())
        ->toBeTrue();
});
