<?php

declare(strict_types=1);

namespace App\Exceptions;

use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Thrown when an OAuth provider completes the flow without a usable email.
 *
 * The `users.email` column is NOT NULL, so we cannot create an account without
 * one. Instead of letting the insert blow up with an HTTP 500, this exception
 * renders a friendly redirect back to the panel login page with a flashed
 * message — mirroring {@see \App\Http\Middleware\RedirectSocialiteCallbackErrors}.
 */
final class SocialiteEmailMissingException extends RuntimeException
{
    public function render(Request $request): RedirectResponse
    {
        session()->flash(
            'filament-socialite-login-error',
            __('panel-app::auth.social.email_missing'),
        );

        return to_route(FilamentSocialitePlugin::current()->getLoginRouteName());
    }
}
