<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

beforeEach(function (): void {
    config()->set('services.linkedin-openid', [
        'client_id' => 'fake-client-id',
        'client_secret' => 'fake-client-secret',
        'redirect' => 'https://recrutamento.test/oauth/callback/linkedin-openid',
    ]);
});

it('redirects to login when the callback state cannot be decrypted', function (): void {
    // O `state` carrega o id do painel encriptado com a nossa APP_KEY. Um valor que não
    // decripta é URL de callback reaproveitada, truncada ou forjada: não há `code` legítimo
    // para trocar por token, e o visitante deve voltar ao login em vez de ver um HTTP 500.
    Socialite::shouldReceive('driver')->never();

    $response = $this->get('oauth/callback/linkedin-openid?'.http_build_query([
        'code' => 'authorization-code',
        'state' => 'nao-decripta',
    ]));

    $response->assertRedirectToRoute('filament.app.auth.login');
});

it('redirects to login when the callback arrives without any state', function (): void {
    // Varreduras e links soltos batem na rota sem nenhum parâmetro.
    Socialite::shouldReceive('driver')->never();

    $response = $this->get('oauth/callback/linkedin-openid?code=authorization-code');

    $response->assertRedirectToRoute('filament.app.auth.login');
});

it('logs the decryption reason that the package buries in the previous exception', function (): void {
    // `InvalidCallbackPayload` só diz que o painel não pôde ser decriptado; o motivo real
    // vive no `DecryptException` anterior, e sem ele o monitoramento não distingue payload
    // corrompido de APP_KEY trocada.
    Log::spy();

    $this->get('oauth/callback/linkedin-openid?state=nao-decripta');

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message, array $context): bool => $context['reason'] === 'The payload is invalid.');
});
