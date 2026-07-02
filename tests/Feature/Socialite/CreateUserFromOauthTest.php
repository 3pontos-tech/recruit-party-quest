<?php

declare(strict_types=1);

use App\Exceptions\SocialiteEmailMissingException;
use App\Socialite\CreateUserFromOauth;
use He4rt\Users\User;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * @param  array<string, mixed>  $attributes
 */
function oauthUser(array $attributes): SocialiteUser
{
    return (new SocialiteUser)->map($attributes);
}

it('creates a user from the oauth payload', function (): void {
    $user = resolve(CreateUserFromOauth::class)->handle(oauthUser([
        'id' => '123',
        'name' => 'Gabriel R. Barbosa',
        'nickname' => 'gabriel',
        'email' => 'gabriel@gmail.com',
    ]));

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Gabriel R. Barbosa')
        ->and($user->email)->toBe('gabriel@gmail.com');

    $this->assertDatabaseHas('users', [
        'email' => 'gabriel@gmail.com',
        'name' => 'Gabriel R. Barbosa',
    ]);
});

it('throws and creates no user when the oauth email is missing', function (): void {
    $call = fn () => resolve(CreateUserFromOauth::class)->handle(oauthUser([
        'id' => '123',
        'name' => 'Gabriel R. Barbosa',
        'nickname' => 'gabriel',
        'email' => null,
    ]));

    expect($call)->toThrow(SocialiteEmailMissingException::class);

    $this->assertDatabaseCount('users', 0);
});

it('derives the name from the email local-part when name and nickname are absent', function (): void {
    $user = resolve(CreateUserFromOauth::class)->handle(oauthUser([
        'id' => '123',
        'name' => null,
        'nickname' => null,
        'email' => 'joao.silva@gmail.com',
    ]));

    expect($user->name)->toBe('joao.silva');
});
