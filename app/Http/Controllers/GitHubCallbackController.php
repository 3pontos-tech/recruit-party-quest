<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use DutchCodingCompany\FilamentSocialite\Exceptions\InvalidCallbackPayload;
use DutchCodingCompany\FilamentSocialite\Http\Controllers\SocialiteLoginController;
use DutchCodingCompany\FilamentSocialite\Http\Middleware\PanelFromUrlQuery;
use He4rt\App\Filament\Pages\RepoAnalysis\RepoAnalysisListPage;
use He4rt\Users\Models\UserGitHubConnection;
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

            UserGitHubConnection::query()->updateOrCreate(
                ['user_id' => $userId],
                [
                    'github_id' => (string) $oauthUser->getId(),
                    'github_username' => $oauthUser->getNickname() ?? $oauthUser->getName(),
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
