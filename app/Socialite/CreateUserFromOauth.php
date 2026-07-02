<?php

declare(strict_types=1);

namespace App\Socialite;

use App\Exceptions\SocialiteEmailMissingException;
use He4rt\Users\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

/**
 * Creates the local {@see User} for a first-time OAuth login.
 *
 * OAuth providers may legitimately return a `null` email (e.g. a GitHub account
 * with no verified email at all). Since `users.email` is NOT NULL, we abort with
 * {@see SocialiteEmailMissingException} instead of hitting a database violation.
 */
final class CreateUserFromOauth
{
    public function handle(SocialiteUser $oauthUser): User
    {
        $email = $oauthUser->getEmail();

        throw_if(blank($email), SocialiteEmailMissingException::class);

        return User::query()->create([
            'name' => $oauthUser->getName() ?? $oauthUser->getNickname() ?? Str::before($email, '@'),
            'email' => $email,
        ]);
    }
}
