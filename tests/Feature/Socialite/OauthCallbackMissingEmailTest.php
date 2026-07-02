<?php

declare(strict_types=1);

use DutchCodingCompany\FilamentSocialite\Http\Middleware\PanelFromUrlQuery;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function (): void {
    config()->set('services.github', [
        'client_id' => 'fake-client-id',
        'client_secret' => 'fake-client-secret',
        'redirect' => 'https://recrutamento.test/oauth/callback/github',
    ]);
});

it('redirects to login without creating a user when github returns no usable email', function (): void {
    $oauthUser = (new SocialiteUser)->map([
        'id' => '123',
        'name' => 'Gabriel R. Barbosa',
        'nickname' => 'gabriel',
        'email' => null,
    ]);

    $driver = Mockery::mock(GithubProvider::class);
    $driver->shouldReceive('stateless')->andReturnSelf();
    $driver->shouldReceive('user')->andReturn($oauthUser);
    Socialite::shouldReceive('driver')->with('github')->andReturn($driver);

    $response = $this->get('oauth/callback/github?'.http_build_query([
        'code' => 'valid-authorization-code',
        'state' => PanelFromUrlQuery::encrypt('app'),
    ]));

    $response->assertRedirectToRoute('filament.app.auth.login');

    expect(session('filament-socialite-login-error'))
        ->toBe(__('panel-app::auth.social.email_missing'));

    $this->assertDatabaseCount('users', 0);
});
