<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Auth\Pages\Register;
use He4rt\App\Filament\Pages\OnboardingWizard;
use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

test('after registration user should be able to apply to jobs', function (): void {
    filament()->setCurrentPanel(FilamentPanel::App->value);
    assertDatabaseCount(Candidate::class, 0);
    livewire(Register::class)
        ->assertOk()
        ->fillForm([
            'name' => 'joe doe',
            'email' => 'joe@doe.com',
            'password' => 'password',
            'passwordConfirmation' => 'password',
        ])
        ->call('register')
        ->assertHasNoFormErrors()
        ->assertSuccessful();

    assertAuthenticated();

    assertDatabaseHas(User::class, [
        'name' => 'joe doe',
        'email' => 'joe@doe.com',
    ]);

    // O registro não cria mais o perfil — quem materializa é o onboarding.
    assertDatabaseCount(Candidate::class, 0);

    livewire(OnboardingWizard::class)->assertOk();

    assertDatabaseCount(Candidate::class, 1);
});
