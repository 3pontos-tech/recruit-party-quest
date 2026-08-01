<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use App\Filament\Shared\MyProfile\PersonalInfo;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

/*
 * A edição de perfil é a segunda porta de entrada para o mesmo endereço
 * não entregável que derrubou o envio em produção — ver
 * RegistrationEmailValidationTest.
 */

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::App->value);

    actingAs(User::factory()->create(['email' => 'valido@exemplo.com']));
});

it('rejects a profile email whose domain has no TLD', function (): void {
    livewire(PersonalInfo::class)
        ->fillForm([
            'name' => 'Myke Douglas',
            'email' => 'contatomyke@hotmail',
        ])
        ->call('submit')
        ->assertHasFormErrors(['email']);

    assertDatabaseHas(User::class, ['email' => 'valido@exemplo.com']);
});

it('accepts a profile email with a deliverable domain', function (): void {
    livewire(PersonalInfo::class)
        ->fillForm([
            'name' => 'Myke Douglas',
            'email' => 'contatomyke@hotmail.com',
        ])
        ->call('submit')
        ->assertHasNoFormErrors();

    assertDatabaseHas(User::class, ['email' => 'contatomyke@hotmail.com']);
});
