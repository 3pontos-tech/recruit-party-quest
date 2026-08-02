<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Pages\AppDashboard;
use He4rt\App\Filament\Pages\LandingPage;
use He4rt\Candidates\Actions\EnsureCandidateProfile;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $candidate = resolve(EnsureCandidateProfile::class)->execute($this->user);
    $this->user->setRelation('candidate', $candidate);
    $candidate->update([
        'is_onboarded' => true,
        'onboarding_completed_at' => now(),
    ]);

    filament()->setCurrentPanel(FilamentPanel::App->value);
});

describe('Unauthenticated guest access', function (): void {
    it('redirects guest to the landing page when accessing the dashboard', function (): void {
        get(AppDashboard::getUrl())
            ->assertRedirect(LandingPage::getUrl());
    });
});

describe('Authenticated candidate access', function (): void {
    it('renders the dashboard successfully for an authenticated candidate', function (): void {
        actingAs($this->user);

        get(AppDashboard::getUrl())
            ->assertOk();
    });

    it('renders the dashboard Livewire component without errors', function (): void {
        actingAs($this->user);

        livewire(AppDashboard::class)
            ->assertOk();
    });
});
