---
type: spec
title: 'Preservar a vaga após login/cadastro ao clicar em Candidatar-se'
module: panel-app
status: proposed
date: 2026-07-02
author: Clintonrocha98
related:
    issue: 3pontos-tech/recruit-party-quest#218
---

# Preservar a vaga após login/cadastro ao clicar em "Candidatar-se"

## Contexto e problema

Um candidato não autenticado navega pela listagem pública de vagas (paginada), abre uma
vaga em `/vagas/{slug}` e clica em **Candidatar-se**. Hoje ele é enviado para `/login` e,
após autenticar (e-mail/senha, cadastro ou auth social), cai no dashboard — perdendo a
vaga que tinha escolhido e precisando procurá-la de novo na listagem.

### Diagnóstico (difere da issue #218)

A issue culpa o `AppLoginPage::getRedirectUrl()`. Esse método é **código morto**: no
Filament v5, `Login::authenticate()` retorna `app(LoginResponse::class)` e nunca chama
`getRedirectUrl()`. Os três fluxos de autenticação já respeitam o mecanismo nativo de
destino do Laravel (`session('url.intended')`):

| Fluxo       | Response                                            | Comportamento                              |
| ----------- | --------------------------------------------------- | ------------------------------------------ |
| Login       | `Filament\Auth\Http\Responses\LoginResponse`        | `redirect()->intended(Filament::getUrl())` |
| Cadastro    | `Filament\Auth\Http\Responses\RegistrationResponse` | `redirect()->intended(Filament::getUrl())` |
| Auth social | `FilamentSocialitePlugin` (`Traits/Callbacks.php`)  | `redirect()->intended(...)`                |

**A causa raiz real:** ninguém grava `url.intended`. O Laravel só grava automaticamente
quando um guest é barrado por um middleware de autenticação (`redirect()->guest()`). A
página da vaga é pública — o painel `app` não declara `->authMiddleware()` — e o botão
guest é um link cru para `/login` (`job-description.blade.php`), então a request nunca é
barrada e a sessão fica sem destino.

Há ainda uma segunda perda no fluxo de cadastro: `RedirectIfOnboardingIncomplete` faz
bounce para `/onboarding` sem preservar o destino, e o `OnboardingWizard` finaliza com
redirect hardcoded para o dashboard.

## Objetivos

- Ao clicar em **Candidatar-se** como guest, preservar a vaga e retomá-la após login,
  cadastro ou auth social — inclusive atravessando o onboarding de usuários novos.
- Retomar a **intenção**, não só a página: reabrir o modal de screening quando a vaga
  tiver perguntas.
- Nenhuma criação de candidatura via GET (sem side effects por prefetch/refresh).
- Sem superfície de open redirect.

## Não-objetivos

- Generalizar "voltar para onde eu estava" para qualquer página/fluxo de login (só o
  fluxo de candidatura grava intenção).
- Auto-aplicar no retorno para vagas sem screening (o candidato dá o clique final).
- Alterar as regras de disponibilidade/publicação de vagas da página atual.

## Abordagens consideradas

**A) Rota de intenção protegida + `url.intended` nativo (escolhida)** — o botão guest
aponta para uma rota autenticada; o middleware grava o destino sozinho e os três fluxos
de auth retornam via `redirect()->intended()` sem nenhuma mudança neles. O destino nunca
vem do usuário → open redirect impossível por construção.

**B) Gravar `url.intended` no `mount()` do `AppLoginPage` via `url()->previous()`** —
zero rotas novas, mas muda o comportamento de todo login, perde a semântica de intenção
(sem auto-abrir modal) e depende do histórico de navegação. Descartada.

**C) Query param `?intended=` (proposta da issue)** — reinventa a sessão via query
string, cria superfície de open redirect que exige validação manual e obriga a propagar o
parâmetro por login, página de registro customizada e plugin social. Descartada.

## Design

### Visão do fluxo

```
USER (guest, /vagas/{slug})               SYSTEM
  │                                          │
  │  👆 "Candidatar-se"                      │
  │ ────────────────────────────────────►    │
  │                                          │  GET /vagas/{record}/candidatar
  │                                          │  Authenticate (Filament): guest barrado
  │                                          │  ⚙️ session: url.intended = /vagas/{record}/candidatar
  │     redirect → /login                    │
  │ ◄────────────────────────────────────    │
  │                                          │
  │  👆 login | cadastro | Google/GitHub     │
  │ ────────────────────────────────────►    │
  │                                          │  LoginResponse / RegistrationResponse /
  │                                          │  Socialite: redirect()->intended()   ← já existe
  │     redirect → /vagas/{record}/candidatar│
  │ ◄────────────────────────────────────    │
  │                                          │  (onboarding incompleto? middleware re-grava
  │                                          │   url.intended e manda p/ /onboarding;
  │                                          │   wizard finaliza com redirect()->intended())
  │                                          │
  │                                          │  JobApplyIntentController:
  │                                          │  posting existe? → /vagas/{slug}?apply=1
  │                                          │  posting sumiu?  → /vagas + notificação
  │                                          │
  │  📱 vaga aberta, ✓ modal de screening    │
  │     já aberto (quando houver perguntas)  │
```

### Componentes

**1. Rota de intenção** — registrada no `AppPanelProvider` via `$panel->routes(...)`
(herda o grupo/contexto do painel), com `Filament\Http\Middleware\Authenticate` explícito
(o painel não tem `authMiddleware` — é todo público por design):

```php
// app/Providers/Filament/AppPanelProvider.php
->routes(function (): void {
    Route::get('/vagas/{record}/candidatar', JobApplyIntentController::class)
        ->middleware(Authenticate::class)
        ->name('jobs.apply-intent'); // nome final: filament.app.jobs.apply-intent
})
```

**2. Controller invokável** — `He4rt\App\Http\Controllers\JobApplyIntentController`
(módulo `panel-app`, camada de apresentação). Autenticado:

- Resolve o `JobPosting` pelo slug (mesma resolução da página atual).
- Posting existe → redirect para `filament.app.resources.vagas.view` com `?apply=1`.
- Posting não existe mais (vaga despublicada entre o clique e o retorno) → redirect para
  a listagem `filament.app.resources.vagas.index` com notificação Filament (flash de
  sessão) informando que a vaga não está mais disponível.

**3. Botão guest** — `job-description.blade.php`:

```text
{{-- antes --}}
<x-he4rt::button variant="solid" class="w-full sm:w-auto" href="/login">

{{-- depois --}}
<x-he4rt::button variant="solid" class="w-full sm:w-auto"
    :href="route('filament.app.jobs.apply-intent', ['record' => $posting->slug])">
```

**4. Auto-abrir o modal no retorno** — mesmo arquivo, estado Alpine:

```text
{{-- antes --}}
x-data="{ showApplicationModal: false, hasApplied: @js($hasApplied) }"

{{-- depois --}}
x-data="{ showApplicationModal: @js($autoOpenApplication), hasApplied: @js($hasApplied) }"
```

com `$autoOpenApplication = request()->boolean('apply') && ! $hasApplied &&
$jobRequisition->screeningQuestions->isNotEmpty()` no bloco `@php` do topo. Vagas sem
screening ignoram `?apply=1` — o candidato dá o clique final em "Candidatar-se" (que
aplica em 1 clique via `applyDirectly()`, como hoje).

**5. Perna do onboarding** — duas mudanças de uma palavra cada:

```php
// RedirectIfOnboardingIncomplete — antes
return redirect(OnboardingWizard::getUrl());
// depois: redirect()->guest() grava url.intended com a URL atual
return redirect()->guest(OnboardingWizard::getUrl());

// OnboardingWizard (conclusão do wizard) — antes
redirect(route('filament.app.pages.dashboard'));
// depois: retoma o destino salvo; dashboard continua como fallback
redirect()->intended(route('filament.app.pages.dashboard'));
```

**6. Limpeza** — remover o `getRedirectUrl()` morto de `AppLoginPage`.

Cadastro e auth social **não mudam**: já usam `redirect()->intended()`, e a sessão
sobrevive ao round-trip do OAuth.

### i18n

Nova chave para a notificação de vaga indisponível, em `en` e `pt_BR`
(`app-modules/panel-app/lang/{en,pt_BR}/filament.php`), ex.:
`panel-app::filament.pages.job_description.job_unavailable`.

## Comportamento esperado (BDD)

- **Happy path (login):** Dado um guest em `/vagas/x` que clica em Candidatar-se, Então
  vai para `/login`; após autenticar, volta para `/vagas/x` com o modal de screening
  aberto (ou pronto para o clique final, se a vaga não tem perguntas).
- **Cadastro + onboarding:** Dado que ele cria conta em vez de logar, Então após o
  registro é levado ao onboarding; ao concluir o wizard, volta para `/vagas/x?apply=1`.
- **Auth social:** mesmo destino, sem mudança de código.
- **Já candidatado:** Dado que ele já aplicou, Então o `mount()` existente de
  `ViewJobRequisition` redireciona para a candidatura dele (comportamento preservado).
- **Sem intenção:** Dado um login direto em `/login`, Então cai na home do painel
  (`Filament::getUrl()` → `/`, landing page) como hoje — fallback do `intended()`
  inalterado. Na conclusão do onboarding o fallback é o dashboard, como hoje.
- **Vaga despublicada no retorno:** Dado que o posting sumiu entre o clique e o retorno,
  Então o candidato cai na listagem `/vagas` com notificação de vaga indisponível.
- **Open redirect:** Dado qualquer tentativa de manipular o destino, Então nada acontece —
  o destino nunca sai da sessão server-side.

## Testes (Pest, feature — `app-modules/panel-app/tests/Feature/`)

1. Guest em `/vagas/{slug}/candidatar` → redirect para login + `url.intended` gravado.
2. Autenticado na rota de intenção → redirect para a vaga com `?apply=1`.
3. Autenticado na rota de intenção com posting inexistente → redirect para `/vagas` +
   notificação.
4. Login com `url.intended` na sessão → volta ao destino; sem intended → home do painel
   (comportamento atual).
5. Onboarding: middleware grava intended no bounce; wizard conclui → destino salvo em vez
   de dashboard (ajustar `RedirectIfOnboardingIncompleteTest` e `OnboardingWizardTest`
   que asseguram o redirect atual).
6. Botão guest renderiza o href da rota de intenção.

## Riscos e observações

- **GET sem side effects:** a rota de intenção só redireciona; a candidatura continua
  exigindo ação explícita do candidato (submit do modal ou clique em aplicar).
- **`url.intended` é consumido no primeiro uso** (`redirect()->intended()` faz pull da
  chave) — por isso o middleware de onboarding precisa re-gravar o destino no bounce.
- O `RedirectIfOnboardingIncomplete` roda no grupo do painel e não intercepta a rota de
  intenção de guests (só autenticados sem onboarding), então a ordem
  middleware-do-painel → middleware-da-rota não causa conflito.
