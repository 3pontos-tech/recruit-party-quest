<?php

declare(strict_types=1);

use App\Exceptions\SocialiteEmailMissingException;
use Illuminate\Http\RedirectResponse;

it('renders a redirect back to the app login with a flashed message', function (): void {
    filament()->setCurrentPanel('app');

    $response = (new SocialiteEmailMissingException)->render(request());

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe(route('filament.app.auth.login'));

    // The message is resolved through the translation key (never the literal key).
    expect(session('filament-socialite-login-error'))
        ->toBe(__('panel-app::auth.social.email_missing'))
        ->not->toBe('panel-app::auth.social.email_missing');
});
