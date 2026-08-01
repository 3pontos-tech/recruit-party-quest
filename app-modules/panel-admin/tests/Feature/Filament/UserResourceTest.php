<?php

declare(strict_types=1);

namespace He4rt\Admin\Tests\Feature\Filament;

use App\Enums\FilamentPanel;
use He4rt\Admin\Filament\Resources\Users\Pages\CreateUser;
use He4rt\Admin\Filament\Resources\Users\Pages\EditUser;
use He4rt\Admin\Filament\Resources\Users\Pages\ListUsers;
use He4rt\Admin\Filament\Resources\Users\UserResource;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Admin->value);

    actingAs(User::factory()->admin()->create());
});

it('can list users', function (): void {
    $users = User::factory()->count(5)->create();

    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->assertCountTableRecords(6);
});

it('can render create user page', function (): void {
    get(UserResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create user', function (): void {
    $newData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'email_verified_at' => null,
        'password' => 'password',
    ];

    livewire(CreateUser::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

it('can render edit user page', function (): void {
    $user = User::factory()->create();

    get(UserResource::getUrl('edit', ['record' => $user]))
        ->assertSuccessful();
});

it('can update user', function (): void {
    $user = User::factory()->create();
    $newData = [
        'name' => 'Updated User Name',
        'email' => $user->email,
        'email_verified_at' => $user->email_verified_at,
        'password' => 'password',
    ];

    livewire(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh())
        ->name->toBe('Updated User Name');
});

it('rejects a user email whose domain has no TLD', function (): void {
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Myke Douglas',
            'email' => 'contatomyke@hotmail',
            'email_verified_at' => null,
            'password' => 'password',
        ])
        ->call('create')
        ->assertHasFormErrors(['email']);

    assertDatabaseMissing('users', ['email' => 'contatomyke@hotmail']);
});
