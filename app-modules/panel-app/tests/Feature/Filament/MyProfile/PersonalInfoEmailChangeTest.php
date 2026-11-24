<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use App\Filament\Shared\MyProfile\PersonalInfo;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::App->value);
});

it('requires the current password to change the email', function (): void {
    actingAs(User::factory()->create(['email' => 'valido@exemplo.com']));

    livewire(PersonalInfo::class)
        ->fillForm(['email' => 'novo@hotmail.com'])
        ->call('submit')
        ->assertHasFormErrors(['current_password' => 'required']);

    assertDatabaseHas(User::class, ['email' => 'valido@exemplo.com']);
});

it('rejects a wrong current password when changing the email', function (): void {
    actingAs(User::factory()->create(['email' => 'valido@exemplo.com']));

    livewire(PersonalInfo::class)
        ->fillForm([
            'email' => 'novo@hotmail.com',
            'current_password' => 'senha-errada',
        ])
        ->call('submit')
        ->assertHasFormErrors(['current_password']);

    assertDatabaseHas(User::class, ['email' => 'valido@exemplo.com']);
});

it('keeps the email editable for an account with a password', function (): void {
    actingAs(User::factory()->create());

    livewire(PersonalInfo::class)
        ->assertFormFieldEnabled('email');
});

it('locks the email of an account created by social login', function (): void {
    actingAs(User::factory()->create(['password' => null]));

    livewire(PersonalInfo::class)
        ->assertFormFieldDisabled('email');
});

it('lets an account without a password update its name', function (): void {
    actingAs(User::factory()->create([
        'name' => 'Pessoa Social',
        'email' => 'social@exemplo.com',
        'password' => null,
    ]));

    livewire(PersonalInfo::class)
        ->fillForm(['name' => 'Pessoa Renomeada'])
        ->call('submit')
        ->assertHasNoFormErrors();

    assertDatabaseHas(User::class, [
        'name' => 'Pessoa Renomeada',
        'email' => 'social@exemplo.com',
    ]);
});

it('keeps the email of an account without a password when the locked field is forged', function (): void {
    actingAs(User::factory()->create([
        'email' => 'social@exemplo.com',
        'password' => null,
    ]));

    livewire(PersonalInfo::class)
        ->fillForm(['email' => 'novo@hotmail.com'])
        ->call('submit')
        ->assertHasFormErrors(['current_password']);

    assertDatabaseHas(User::class, ['email' => 'social@exemplo.com']);
});
