<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Pages\LandingPage;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::App->value);
});

describe('Public access to the landing page', function (): void {
    it('renders the landing page for unauthenticated visitors', function (): void {
        get(LandingPage::getUrl())
            ->assertOk();
    });

    it('renders the landing page for authenticated users', function (): void {
        actingAs(User::factory()->create());

        get(LandingPage::getUrl())
            ->assertOk();
    });

    it('renders the landing page Livewire component without errors', function (): void {
        livewire(LandingPage::class)
            ->assertOk();
    });

    it('does not register the landing page in the navigation', function (): void {
        expect(LandingPage::shouldRegisterNavigation())->toBeFalse();
    });
});
