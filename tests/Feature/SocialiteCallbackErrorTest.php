<?php

declare(strict_types=1);

use App\Http\Middleware\RedirectSocialiteCallbackErrors;
use DutchCodingCompany\FilamentSocialite\Http\Middleware\PanelFromUrlQuery;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    config()->set('services.linkedin-openid', [
        'client_id' => 'fake-client-id',
        'client_secret' => 'fake-client-secret',
        'redirect' => 'https://recrutamento.test/oauth/callback/linkedin-openid',
    ]);
});

it('redirects back to login when the provider returns an error on the callback', function (): void {
    // Quando o provedor devolve `error` (ex.: o usuário cancela a autorização) não há
    // `code` para trocar por token — a troca NUNCA deve ser tentada. É essa tentativa
    // que hoje estoura um HTTP 500.
    Socialite::shouldReceive('driver')->never();

    $response = $this->get('oauth/callback/linkedin-openid?'.http_build_query([
        'error' => 'user_cancelled_authorize',
        'error_description' => 'The user cancelled the authorization',
        'state' => PanelFromUrlQuery::encrypt('app'),
    ]));

    $response->assertRedirectToRoute('filament.app.auth.login');

    // A mensagem é resolvida via chave de tradução (não pode vazar a chave literal).
    expect(session('filament-socialite-login-error'))
        ->not->toBeNull()
        ->toBe(__('panel-app::auth.social.callback_error'))
        ->not->toBe('panel-app::auth.social.callback_error');
});

it('lets the callback through untouched when there is no error', function (): void {
    $request = Request::create('oauth/callback/linkedin-openid', 'GET', [
        'code' => 'valid-authorization-code',
        'state' => PanelFromUrlQuery::encrypt('app'),
    ]);

    $reached = false;
    $response = new RedirectSocialiteCallbackErrors()->handle($request, function () use (&$reached): Response {
        $reached = true;

        return new Response('ok');
    });

    expect($reached)->toBeTrue()
        ->and($response->getContent())->toBe('ok');
});
