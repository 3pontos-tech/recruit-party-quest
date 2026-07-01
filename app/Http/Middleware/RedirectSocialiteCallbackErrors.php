<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the Socialite OAuth callback against error responses.
 *
 * When a provider redirects back with an `error` (e.g. the user cancels the
 * authorization, or the requested scope is rejected) there is no `code` to
 * exchange for a token. Without this guard, filament-socialite would still call
 * `->user()` and the missing-code token exchange blows up with an HTTP 500.
 *
 * Runs after `SetUpPanel`/`StartSession`, so the current panel and session are
 * already resolved and we can flash a message and bounce back to the login page.
 */
final class RedirectSocialiteCallbackErrors
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->filled('error')) {
            session()->flash(
                'filament-socialite-login-error',
                __('panel-app::auth.social.callback_error'),
            );

            return to_route(FilamentSocialitePlugin::current()->getLoginRouteName());
        }

        return $next($request);
    }
}
