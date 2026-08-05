<?php

declare(strict_types=1);

use DutchCodingCompany\FilamentSocialite\Exceptions\InvalidCallbackPayload;
use DutchCodingCompany\FilamentSocialite\Exceptions\ProviderNotConfigured;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function (): void {
            Broadcast::routes(['middleware' => ['web', 'auth']]);
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->replace(
            TrustProxies::class,
            Monicahq\Cloudflare\Http\Middleware\TrustProxies::class
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (ProviderNotConfigured $e): void {
            Log::warning('Unknown OAuth provider accessed.', [
                'message' => $e->getMessage(),
            ]);
        })->stop();

        $exceptions->render(function (ProviderNotConfigured $e): void {
            abort(404);
        });

        // O `state` do callback OAuth carrega o id do painel encriptado com a APP_KEY, e o
        // `PanelFromUrlQuery` do filament-socialite o decripta como PRIMEIRO middleware da
        // rota `oauth.callback` — antes de `config('filament-socialite.middleware')`, onde
        // vive o nosso `RedirectSocialiteCallbackErrors`. Um `state` ilegível (URL de callback
        // reaproveitada, truncada ou varredura sem parâmetro algum) portanto nunca chega ao
        // guard e virava HTTP 500. Tratar aqui é o único ponto acima desse middleware.
        $exceptions->report(function (InvalidCallbackPayload $e): void {
            Log::warning('OAuth callback with an undecryptable panel state.', [
                'reason' => $e->getPrevious()?->getMessage(),
            ]);
        })->stop();

        // Sem mensagem para o visitante: `StartSession` também roda depois do
        // `PanelFromUrlQuery`, então não há sessão onde flashar o aviso.
        $exceptions->render(fn (InvalidCallbackPayload $e): RedirectResponse => to_route('filament.app.auth.login'));
    })
    ->create();
