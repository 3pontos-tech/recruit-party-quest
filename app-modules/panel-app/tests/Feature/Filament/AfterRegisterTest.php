<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Auth\Pages\Register;
use He4rt\Candidates\Models\Candidate;
use He4rt\Permissions\Permission;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Users\User;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

test('after registration user should be able to apply to jobs', function (): void {
    filament()->setCurrentPanel(FilamentPanel::App->value);
    Permission::factory()
        ->create([
            'name' => 'view_applications',
            'guard_name' => 'web',
            'action' => PermissionsEnum::View,
        ]);

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
    assertDatabaseCount(Candidate::class, 1);
});
