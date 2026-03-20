<?php

declare(strict_types=1);

namespace He4rt\Users\Listeners;

use DutchCodingCompany\FilamentSocialite\Events\Login;
use DutchCodingCompany\FilamentSocialite\Events\Registered;
use He4rt\Users\Enums\OAuthProvider;
use He4rt\Users\Models\SocialAccount;
use Laravel\Socialite\Two\User as SocialiteOAuthTwoUser;

final class SaveGitHubTokenListener
{
    public function handle(Login|Registered $event): void
    {
        $socialAccount = $event->socialiteUser;

        if (! $socialAccount instanceof SocialAccount) {
            return;
        }

        if ($socialAccount->provider !== OAuthProvider::GitHub) {
            return;
        }

        /** @var SocialiteOAuthTwoUser $oauthUser */
        $oauthUser = $event->oauthUser;

        $socialAccount->update([
            'access_token' => $oauthUser->token,
        ]);
    }
}
