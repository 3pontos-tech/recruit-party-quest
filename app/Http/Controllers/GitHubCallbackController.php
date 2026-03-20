<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use DutchCodingCompany\FilamentSocialite\Exceptions\InvalidCallbackPayload;
use DutchCodingCompany\FilamentSocialite\Http\Controllers\SocialiteLoginController;
use DutchCodingCompany\FilamentSocialite\Http\Middleware\PanelFromUrlQuery;
use He4rt\App\Filament\Pages\RepoAnalysis\RepoAnalysisListPage;
use He4rt\Users\Enums\OAuthProvider;
use He4rt\Users\Models\SocialAccount;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteOAuthTwoUser;
use Symfony\Component\HttpFoundation\Response;

final class GitHubCallbackController extends Controller
{
    public function handle(): Response
    {
        if ($userId = session()->pull('github_connect_for_user')) {
            /** @var SocialiteOAuthTwoUser $oauthUser */
            $oauthUser = Socialite::driver('github')->user();

            $conflict = SocialAccount::query()
                ->where('provider', OAuthProvider::GitHub)
                ->where('provider_id', (string) $oauthUser->getId())
                ->where('user_id', '!=', $userId)
                ->exists();

            if ($conflict) {
                session()->flash('filament-socialite-login-error', __('users::labels.github.already_linked'));

                return redirect(RepoAnalysisListPage::getUrl());
            }

            SocialAccount::query()->updateOrCreate(
                ['user_id' => $userId, 'provider' => OAuthProvider::GitHub],
                [
                    'provider_id' => (string) $oauthUser->getId(),
                    'provider_username' => $oauthUser->getNickname() ?? $oauthUser->getName(),
                    'access_token' => $oauthUser->token,
                ]
            );

            return redirect(RepoAnalysisListPage::getUrl());
        }

        try {
            $panelId = PanelFromUrlQuery::decrypt(request());
        } catch (InvalidCallbackPayload) {
            $panelId = 'app';
        }

        filament()->setCurrentPanel($panelId);

        return resolve(SocialiteLoginController::class)->processCallback('github');
    }
}
