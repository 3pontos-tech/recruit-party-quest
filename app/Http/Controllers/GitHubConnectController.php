<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

final class GitHubConnectController extends Controller
{
    public function redirect(): RedirectResponse
    {
        session(['github_connect_for_user' => auth()->id()]);

        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('github');

        return $driver
            ->scopes(['read:user', 'public_repo'])
            ->redirect();
    }
}
