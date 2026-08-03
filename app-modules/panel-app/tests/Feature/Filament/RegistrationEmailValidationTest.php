<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Users\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

/*
 * Regressão de produção (2026-07-31): `contatomyke@hotmail` foi aceito no cadastro
 * porque a regra `email` do Laravel usa RFCValidation, que não exige TLD. O endereço
 * ficou salvo e a API do Resend passou a recusar todo envio para esse usuário,
 * derrubando o job da ApplicationReceivedNotification.
 */

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::App->value);
});

it('rejects a registration email whose domain has no TLD', function (): void {
    livewire(filament()->getCurrentPanel()->getRegistrationRouteAction())
        ->fillForm([
            'name' => 'Myke Douglas',
            'email' => 'contatomyke@hotmail',
            'password' => 'password',
            'passwordConfirmation' => 'password',
        ])
        ->call('register')
        ->assertHasFormErrors(['email']);

    assertDatabaseMissing(User::class, ['email' => 'contatomyke@hotmail']);
});

it('accepts a registration email with a deliverable domain', function (): void {
    livewire(filament()->getCurrentPanel()->getRegistrationRouteAction())
        ->fillForm([
            'name' => 'Myke Douglas',
            'email' => 'contatomyke@hotmail.com',
            'password' => 'password',
            'passwordConfirmation' => 'password',
        ])
        ->call('register')
        ->assertHasNoFormErrors();

    assertDatabaseHas(User::class, ['email' => 'contatomyke@hotmail.com']);
});
