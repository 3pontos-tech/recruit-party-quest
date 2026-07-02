---
type: plan
title: 'Preservar a vaga após login/cadastro ao clicar em Candidatar-se'
module: panel-app
status: proposed
date: 2026-07-02
author: Clintonrocha98
related:
    spec: panel-app/2026-07-02-preserve-job-intent-after-auth
    issue: 3pontos-tech/recruit-party-quest#218
---

# Preservar vaga após login — Plano de implementação

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ao clicar em **Candidatar-se** como guest, preservar a vaga e retomá-la após login, cadastro ou auth social — reabrindo o modal de screening quando houver perguntas.

**Architecture:** Uma rota de intenção (`/vagas/{record}/candidatar`) protegida pelo `Authenticate` do Filament faz o Laravel gravar `session('url.intended')` sozinho; os três fluxos de auth (login, cadastro, social) já retornam via `redirect()->intended()` sem nenhuma mudança neles. A perna do onboarding re-grava o destino no bounce (`redirect()->guest()`) e o consome ao concluir o wizard (`redirect()->intended()`).

**Tech Stack:** Laravel 12, Filament v5, Livewire v4, Pest v4, módulo `panel-app` (`He4rt\App`).

**Spec:** `app-modules/panel-app/docs/specs/2026-07-02-preserve-job-intent-after-auth.md`

## Global Constraints

- **NÃO commitar nada** — o Clinton faz os commits manualmente. Nenhuma task tem passo de `git commit`.
- Toda string user-facing entra em `en` **e** `pt_BR` (`app-modules/panel-app/lang/{en,pt_BR}/filament.php`), sempre via `__()`.
- Após qualquer mudança em PHP: `vendor/bin/pint --dirty --format agent` (corrige, não usar `--test`).
- Testes: Pest v4, feature, em `app-modules/panel-app/tests/Feature/`; rodar com `php artisan test --compact --filter=...`.
- Não adicionar dependências.
- Contexto que os testes dependem: `UserObserver` auto-cria um `Candidate` (`is_onboarded => false`, role `user`) para todo `User` novo — para um usuário "onboarded", atualize o candidate auto-criado via relação query (`$user->candidate()->update(['is_onboarded' => true])`) **e chame `$user->refresh()`**, nunca crie um segundo. Atenção: o `if (! $user->candidate)` dentro do observer cacheia a relação como `null` na instância recém-criada — sem o `refresh()`, `$user->candidate` continua `null` no teste (em runtime real não afeta, pois cada request re-hidrata o user).
- O painel `app` tem `path('')` — as URLs finais são `/vagas/...`, `/login`, `/onboarding`, `/dashboard`.
- Testes `livewire()` **não** passam pelo middleware HTTP do painel; testes `get()` passam. Escolha conforme o que estiver testando.

## Estrutura de arquivos

| Ação      | Arquivo                                                                           | Responsabilidade                                                               |
| --------- | --------------------------------------------------------------------------------- | ------------------------------------------------------------------------------ |
| Criar     | `app-modules/panel-app/src/Http/Controllers/JobApplyIntentController.php`         | Destino autenticado da intenção: vaga com `?apply=1` ou listagem + notificação |
| Modificar | `app/Providers/Filament/AppPanelProvider.php`                                     | Registrar a rota de intenção no grupo do painel                                |
| Modificar | `app-modules/panel-app/lang/en/filament.php` e `lang/pt_BR/filament.php`          | Chave `job_unavailable`                                                        |
| Modificar | `app-modules/panel-app/resources/views/components/jobs/job-description.blade.php` | Botão guest → rota de intenção; auto-abrir modal com `?apply=1`                |
| Modificar | `app-modules/panel-app/src/RedirectIfOnboardingIncomplete.php:36`                 | `redirect()->guest()` para preservar destino no bounce                         |
| Modificar | `app-modules/panel-app/src/Filament/Pages/OnboardingWizard.php:206`               | `redirect()->intended()` na conclusão do wizard                                |
| Modificar | `app-modules/panel-app/src/Filament/Pages/AppLoginPage.php:32-35`                 | Remover `getRedirectUrl()` morto                                               |
| Criar     | `app-modules/panel-app/tests/Feature/JobApplyIntentTest.php`                      | Testes da rota de intenção + botão + modal                                     |
| Criar     | `app-modules/panel-app/tests/Feature/Filament/Pages/AppLoginPageTest.php`         | Testes de caracterização do redirect pós-login                                 |
| Modificar | `app-modules/panel-app/tests/Feature/RedirectIfOnboardingIncompleteTest.php`      | Novo caso: bounce grava `url.intended`                                         |
| Modificar | `app-modules/panel-app/tests/Feature/Filament/Pages/OnboardingWizardTest.php`     | Novo caso: conclusão retoma `url.intended`                                     |

Ordem: Task 1 → Task 2 (dependente). Tasks 3 e 4 são independentes entre si e das demais. Task 5 fecha.

---

### Task 1: Rota de intenção + controller + i18n

**Files:**

- Create: `app-modules/panel-app/src/Http/Controllers/JobApplyIntentController.php`
- Create: `app-modules/panel-app/tests/Feature/JobApplyIntentTest.php`
- Modify: `app/Providers/Filament/AppPanelProvider.php` (cadeia do `$panel`, logo após `->registration()`, + imports)
- Modify: `app-modules/panel-app/lang/en/filament.php` (bloco `'pages' > 'job_description'`)
- Modify: `app-modules/panel-app/lang/pt_BR/filament.php` (mesmo bloco)

**Interfaces:**

- Consumes: rotas existentes `filament.app.resources.vagas.view` (param `record` = slug do `JobPosting`), `filament.app.resources.vagas.index`, `filament.app.auth.login`; `Filament\Http\Middleware\Authenticate`; `Filament\Notifications\Notification` (o `send()` fora de Livewire faz `session()->push('filament.notifications', ...)`).
- Produces: rota nomeada **`filament.app.jobs.apply-intent`** (`GET /vagas/{record}/candidatar`, `{record}` = slug do posting) — a Task 2 gera href para ela; chave i18n **`panel-app::filament.pages.job_description.job_unavailable`**.

- [x] **Step 1: Escrever os testes que falham**

Criar `app-modules/panel-app/tests/Feature/JobApplyIntentTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::App->value);

    $this->user = User::factory()->create();

    $team = Team::factory()->create();
    $department = Department::factory()->for($team)->create();
    $recruiter = Recruiter::factory()->for($team)->create();

    $this->jobRequisition = JobRequisition::factory()
        ->for($team)
        ->for($department)
        ->for($recruiter, 'recruiter')
        ->for($this->user, 'createdBy')
        ->create(['is_confidential' => false, 'is_internal_only' => false]);

    $this->posting = JobPosting::factory()
        ->for($this->jobRequisition, 'jobRequisition')
        ->create();
});

describe('Apply intent route', function (): void {
    it('redirects a guest to the login page and stores the intent as url.intended', function (): void {
        $intentUrl = route('filament.app.jobs.apply-intent', ['record' => $this->posting->slug]);

        get($intentUrl)
            ->assertRedirect(route('filament.app.auth.login'))
            ->assertSessionHas('url.intended', $intentUrl);
    });

    it('redirects an authenticated candidate to the job page with the apply flag', function (): void {
        $this->user->candidate()->update(['is_onboarded' => true]);
        $this->user->refresh();

        actingAs($this->user);

        get(route('filament.app.jobs.apply-intent', ['record' => $this->posting->slug]))
            ->assertRedirect(route('filament.app.resources.vagas.view', [
                'record' => $this->posting->slug,
                'apply' => 1,
            ]));
    });

    it('redirects to the jobs list with a notification when the posting no longer exists', function (): void {
        $this->user->candidate()->update(['is_onboarded' => true]);
        $this->user->refresh();

        actingAs($this->user);

        get(route('filament.app.jobs.apply-intent', ['record' => 'vaga-inexistente']))
            ->assertRedirect(route('filament.app.resources.vagas.index'))
            ->assertSessionHas('filament.notifications');
    });
});
```

- [x] **Step 2: Rodar e confirmar a falha**

Run: `php artisan test --compact --filter=JobApplyIntentTest`
Expected: FAIL — `Route [filament.app.jobs.apply-intent] not defined.`

- [x] **Step 3: Criar o controller**

Criar `app-modules/panel-app/src/Http/Controllers/JobApplyIntentController.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\App\Http\Controllers;

use Filament\Notifications\Notification;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use Illuminate\Http\RedirectResponse;

final class JobApplyIntentController
{
    public function __invoke(string $record): RedirectResponse
    {
        $postingExists = JobPosting::query()->where('slug', $record)->exists();

        if (! $postingExists) {
            Notification::make()
                ->title(__('panel-app::filament.pages.job_description.job_unavailable'))
                ->warning()
                ->send();

            return redirect()->route('filament.app.resources.vagas.index');
        }

        return redirect()->route('filament.app.resources.vagas.view', [
            'record' => $record,
            'apply' => 1,
        ]);
    }
}
```

- [x] **Step 4: Registrar a rota no painel**

Em `app/Providers/Filament/AppPanelProvider.php`, adicionar os imports (em ordem alfabética junto aos existentes):

```php
use Filament\Http\Middleware\Authenticate;
use He4rt\App\Http\Controllers\JobApplyIntentController;
use Illuminate\Support\Facades\Route;
```

E na cadeia do `$panel`, logo após `->registration()`:

```php
            ->registration()
            ->routes(function (): void {
                Route::get('/vagas/{record}/candidatar', JobApplyIntentController::class)
                    ->middleware(Authenticate::class)
                    ->name('jobs.apply-intent');
            })
```

O nome final fica `filament.app.jobs.apply-intent` (o grupo do painel prefixa `filament.app.`). O `Authenticate` do Filament barra guests com `AuthenticationException` → o handler faz `redirect()->guest(loginUrl)`, que grava `url.intended` automaticamente.

- [x] **Step 5: Adicionar a chave i18n nas duas línguas**

Em `app-modules/panel-app/lang/en/filament.php`, dentro de `'pages' => [ 'job_description' => [`, logo após `'no_posting' => ...,`:

```php
            'job_unavailable' => 'This job is no longer available.',
```

Em `app-modules/panel-app/lang/pt_BR/filament.php`, mesmo ponto:

```php
            'job_unavailable' => 'Esta vaga não está mais disponível.',
```

- [x] **Step 6: Rodar e confirmar que passam**

Run: `php artisan test --compact --filter=JobApplyIntentTest`
Expected: PASS (3 testes)

- [x] **Step 7: Formatar**

Run: `vendor/bin/pint --dirty --format agent`
Expected: sem erros restantes.

---

### Task 2: Botão guest → rota de intenção + auto-abrir modal

**Files:**

- Modify: `app-modules/panel-app/resources/views/components/jobs/job-description.blade.php` (bloco `@php` do topo, `x-data` e botão guest)
- Modify: `app-modules/panel-app/tests/Feature/JobApplyIntentTest.php` (novo `describe`)

**Interfaces:**

- Consumes: rota `filament.app.jobs.apply-intent` (Task 1); relação `$jobRequisition->screeningQuestions` (`MorphMany`, screenable); `ScreeningQuestion::factory()->for($requisition, 'screenable')->text()->required()->create()` (import `He4rt\Screening\Models\ScreeningQuestion`).
- Produces: comportamento de view — nenhum símbolo consumido por outras tasks.

- [x] **Step 1: Escrever os testes que falham**

Adicionar ao final de `app-modules/panel-app/tests/Feature/JobApplyIntentTest.php` (novo import no topo: `use He4rt\Screening\Models\ScreeningQuestion;`):

```php
describe('Job page apply intent UI', function (): void {
    it('renders the guest apply button pointing to the intent route', function (): void {
        get(route('filament.app.resources.vagas.view', ['record' => $this->posting->slug]))
            ->assertOk()
            ->assertSee(route('filament.app.jobs.apply-intent', ['record' => $this->posting->slug]));
    });

    it('auto-opens the application modal when returning with the apply flag', function (): void {
        ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->text()
            ->required()
            ->create();

        $this->user->candidate()->update(['is_onboarded' => true]);
        $this->user->refresh();

        actingAs($this->user);

        get(route('filament.app.resources.vagas.view', [
            'record' => $this->posting->slug,
            'apply' => 1,
        ]))
            ->assertOk()
            ->assertSee('showApplicationModal: true', escape: false);
    });

    it('keeps the application modal closed without the apply flag', function (): void {
        ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->text()
            ->required()
            ->create();

        $this->user->candidate()->update(['is_onboarded' => true]);
        $this->user->refresh();

        actingAs($this->user);

        get(route('filament.app.resources.vagas.view', ['record' => $this->posting->slug]))
            ->assertOk()
            ->assertSee('showApplicationModal: false', escape: false);
    });
});
```

- [x] **Step 2: Rodar e confirmar a falha**

Run: `php artisan test --compact --filter="Job page apply intent UI"`
Expected: FAIL — o primeiro teste não encontra o href da rota de intenção; o segundo não encontra `showApplicationModal: true`.

- [x] **Step 3: Editar o blade**

Em `app-modules/panel-app/resources/views/components/jobs/job-description.blade.php`:

**(a)** No bloco `@php` do topo, logo após o `if` que calcula `$hasApplied`, adicionar:

```text
    $autoOpenApplication = request()->boolean('apply')
        && ! $hasApplied
        && $jobRequisition->screeningQuestions->isNotEmpty();
```

**(b)** No `x-data` do container (hoje `showApplicationModal: false`), trocar para:

```text
x-data="{
    showApplicationModal: @js($autoOpenApplication),
    hasApplied: @js($hasApplied),
}"
```

**(c)** No botão guest (hoje `href="/login"`), trocar para:

```text
@guest
    <x-he4rt::button
        variant="solid"
        class="w-full sm:w-auto"
        :href="route('filament.app.jobs.apply-intent', ['record' => $posting->slug])"
    >
        {{ __('panel-app::filament.pages.job_description.apply_button') }}
    </x-he4rt::button>
@endguest
```

- [x] **Step 4: Rodar e confirmar que passam**

Run: `php artisan test --compact --filter=JobApplyIntentTest`
Expected: PASS (6 testes — os 3 da Task 1 continuam verdes)

---

### Task 3: Perna do onboarding preserva o destino

**Files:**

- Modify: `app-modules/panel-app/src/RedirectIfOnboardingIncomplete.php:36`
- Modify: `app-modules/panel-app/src/Filament/Pages/OnboardingWizard.php:206`
- Modify: `app-modules/panel-app/tests/Feature/RedirectIfOnboardingIncompleteTest.php`
- Modify: `app-modules/panel-app/tests/Feature/Filament/Pages/OnboardingWizardTest.php` (dentro do `describe('Complete Registration Flow')`)

**Interfaces:**

- Consumes: `session('url.intended')` (mecanismo nativo); rota `filament.app.pages.dashboard`.
- Produces: comportamento — bounce de onboarding grava `url.intended`; conclusão do wizard consome via `redirect()->intended()`. Os testes existentes que asseguram dashboard **continuam passando** (sem intended na sessão, o fallback é o dashboard).

- [x] **Step 1: Teste do middleware (falha primeiro)**

Adicionar ao final de `app-modules/panel-app/tests/Feature/RedirectIfOnboardingIncompleteTest.php`:

```php
it('stores the blocked url as url.intended when bouncing to the onboarding wizard', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    get(AppDashboard::getUrl())
        ->assertRedirect(OnboardingWizard::getUrl())
        ->assertSessionHas('url.intended', AppDashboard::getUrl());
});
```

- [x] **Step 2: Rodar e confirmar a falha**

Run: `php artisan test --compact --filter=RedirectIfOnboardingIncompleteTest`
Expected: FAIL — `Session missing expected key [url.intended]` no teste novo; os 5 existentes passam.

- [x] **Step 3: Mudar o middleware**

Em `app-modules/panel-app/src/RedirectIfOnboardingIncomplete.php`, linha 36:

```php
// antes
return redirect(OnboardingWizard::getUrl());

// depois — redirect()->guest() grava url.intended com a URL atual
return redirect()->guest(OnboardingWizard::getUrl());
```

- [x] **Step 4: Rodar e confirmar que passam**

Run: `php artisan test --compact --filter=RedirectIfOnboardingIncompleteTest`
Expected: PASS (6 testes)

- [x] **Step 5: Teste da conclusão do wizard (falha primeiro)**

Adicionar dentro do `describe('Complete Registration Flow', ...)` de `app-modules/panel-app/tests/Feature/Filament/Pages/OnboardingWizardTest.php`:

```php
    it('redirects to the stored intent after completing onboarding', function (): void {
        $intentUrl = url('/vagas/alguma-vaga/candidatar');
        session(['url.intended' => $intentUrl]);

        livewire(OnboardingWizard::class)
            ->set('wizardVisible', true)
            ->set('data.expected_salary', '75000')
            ->set('data.expected_salary_currency', 'USD')
            ->set('data.availability_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.willing_to_relocate', true)
            ->set('data.is_open_to_remote', true)
            ->set('data.experience_level', ExperienceLevelEnum::MidLevel->value)
            ->set('data.timezone', 'America/New_York')
            ->set('data.preferred_language', 'en_US')
            ->set('data.phone', '+5511987654321')
            ->set('data.confirm_submission', true)
            ->set('data.data_consent_given', true)
            ->set('data.work_experiences', [])
            ->set('data.education', [])
            ->call('handleRegistration')
            ->assertHasNoFormErrors()
            ->assertRedirect($intentUrl);
    });
```

- [x] **Step 6: Rodar e confirmar a falha**

Run: `php artisan test --compact --filter="redirects to the stored intent after completing onboarding"`
Expected: FAIL — redirect para `filament.app.pages.dashboard` em vez do intent.

- [x] **Step 7: Mudar o wizard**

Em `app-modules/panel-app/src/Filament/Pages/OnboardingWizard.php`, linha 206 (última linha do método de conclusão, após a `Notification`):

```php
// antes
redirect(route('filament.app.pages.dashboard'));

// depois — retoma o destino salvo; dashboard continua como fallback
redirect()->intended(route('filament.app.pages.dashboard'));
```

Não tocar no redirect do `mount()` (linha 100) — "onboarding já completo" continua indo para o dashboard.

- [x] **Step 8: Rodar o arquivo inteiro (novo caso + regressões)**

Run: `php artisan test --compact app-modules/panel-app/tests/Feature/Filament/Pages/OnboardingWizardTest.php`
Expected: PASS — os casos existentes `should complete full onboarding successfully` e `should save phone number from onboarding data` continuam assertando dashboard (sessão sem intended → fallback).

- [x] **Step 9: Formatar**

Run: `vendor/bin/pint --dirty --format agent`
Expected: sem erros restantes.

---

### Task 4: Testes de caracterização do login + remover `getRedirectUrl()` morto

O redirect pós-login real é o `LoginResponse` do Filament (`redirect()->intended(Filament::getUrl())`); o override `getRedirectUrl()` em `AppLoginPage` nunca é chamado. Primeiro os testes provam o comportamento atual (verdes antes da mudança), depois removemos o método morto e os testes continuam verdes — prova de que era morto.

**Files:**

- Create: `app-modules/panel-app/tests/Feature/Filament/Pages/AppLoginPageTest.php`
- Modify: `app-modules/panel-app/src/Filament/Pages/AppLoginPage.php` (remover linhas 32-35)

**Interfaces:**

- Consumes: `AppLoginPage` (form Filament: campos `email`/`password`, action `authenticate`); senha default da `UserFactory` é `password`; `Filament::getUrl()` do painel app resolve para `/` (landing page).
- Produces: nada consumido por outras tasks.

- [x] **Step 1: Escrever os testes (devem passar JÁ — caracterização)**

Criar `app-modules/panel-app/tests/Feature/Filament/Pages/AppLoginPageTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Facades\Filament;
use He4rt\App\Filament\Pages\AppLoginPage;
use He4rt\Users\User;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::App->value);

    $this->user = User::factory()->create();
});

it('sends the user to the stored intent after logging in', function (): void {
    $intentUrl = url('/vagas/alguma-vaga/candidatar');
    session(['url.intended' => $intentUrl]);

    livewire(AppLoginPage::class)
        ->fillForm([
            'email' => $this->user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertRedirect($intentUrl);
});

it('falls back to the panel home when no intent is stored', function (): void {
    livewire(AppLoginPage::class)
        ->fillForm([
            'email' => $this->user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertRedirect(Filament::getUrl());
});
```

- [x] **Step 2: Rodar e confirmar que JÁ passam (comportamento pré-existente)**

Run: `php artisan test --compact --filter=AppLoginPageTest`
Expected: PASS (2 testes) — sem nenhuma mudança de código. Se falhar, PARE e investigue antes de prosseguir (a premissa do design estaria errada).

- [x] **Step 3: Remover o método morto**

Em `app-modules/panel-app/src/Filament/Pages/AppLoginPage.php`, remover o método inteiro (linhas 32-35):

```php
    protected function getRedirectUrl(): string
    {
        return route('filament.app.pages.dashboard');
    }
```

- [x] **Step 4: Rodar e confirmar que continuam passando**

Run: `php artisan test --compact --filter=AppLoginPageTest`
Expected: PASS (2 testes) — confirma que o método era código morto.

- [x] **Step 5: Formatar**

Run: `vendor/bin/pint --dirty --format agent`
Expected: sem erros restantes.

---

### Task 5: Verificação final

**Files:** nenhum novo — só verificação.

- [x] **Step 1: Suite completa do módulo**

Run: `php artisan test --compact app-modules/panel-app`
Expected: PASS — nenhuma regressão no módulo.

- [x] **Step 2: PHPStan**

Run: `vendor/bin/phpstan analyse`
Expected: `[OK] No errors` (se estourar memória, adicionar `--memory-limit=1G`). Se surgir erro novo no controller ou nos arquivos tocados, corrigir o código — não adicionar `ignoreErrors`.

- [x] **Step 3: Pint geral**

Run: `vendor/bin/pint --dirty --format agent`
Expected: sem pendências.

- [x] **Step 4: Checagem manual dos critérios de aceite da issue #218**

Com `composer run dev` (ou ambiente local ativo), validar o happy path no browser: guest em `/vagas/{slug}` → Candidatar-se → login → volta à vaga com modal aberto (vaga com screening). Auth social não tem teste automatizado (exigiria mock de OAuth); o mecanismo é o mesmo `url.intended` coberto pelos testes de login — validar manualmente se houver provider configurado no `.env` local.

**NÃO commitar** — entregar o diff para revisão do Clinton.
