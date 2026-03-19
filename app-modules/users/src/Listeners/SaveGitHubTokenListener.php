<?php

declare(strict_types=1);

namespace He4rt\Users\Listeners;

use DutchCodingCompany\FilamentSocialite\Events\Login;
use DutchCodingCompany\FilamentSocialite\Events\Registered;
use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser;
use He4rt\Users\Models\UserGitHubConnection;
use He4rt\Users\User;
use Laravel\Socialite\Two\User as SocialiteOAuthTwoUser;

final class SaveGitHubTokenListener
{
    public function handle(Login|Registered $event): void
    {
        /** @var SocialiteUser $socialiteUser */
        $socialiteUser = $event->socialiteUser;

        if ($socialiteUser->provider !== 'github') {
            return;
        }

        /** @var User $user */
        $user = $socialiteUser->getUser();

        /** @var SocialiteOAuthTwoUser $oauthUser */
        $oauthUser = $event->oauthUser;

        UserGitHubConnection::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'github_id' => $oauthUser->getId(),
                'github_username' => $oauthUser->getNickname() ?? $oauthUser->getName(),
                'access_token' => $oauthUser->token,
            ]
        );
    }
}
