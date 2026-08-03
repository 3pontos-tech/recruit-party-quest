---
type: plan
title: 'Criação explícita do perfil de candidato'
module: users, candidates, panel-app
status: proposed
date: 2026-08-02
author: Clintonrocha98
related:
    spec: 2026-08-02-criacao-explicita-do-perfil-de-candidato
    issue: 3pontos-tech/recruit-party-quest#261
---

# Criação explícita do perfil de candidato — Plano de Implementação

> **Para agentes:** use `superpowers:subagent-driven-development` ou
> `superpowers:executing-plans` para executar tarefa a tarefa. Os passos usam checkbox
> (`- [ ]`) para acompanhamento.

**Goal:** tirar a criação do `Candidate` do `UserObserver` e passá-la para uma Action
explícita chamada no onboarding, fechando a issue #261 e viabilizando o índice único em
`candidates.user_id`.

**Architecture:** o observer fica só com `assignRole(Roles::User)`. Uma Action idempotente
em `candidates` cria o perfil, e o `OnboardingWizard` a chama no `mount()`. As tarefas são
ordenadas em _strangler_: cada uma deixa a suíte verde tanto com o observer antigo quanto
sem ele, então nenhuma tarefa depende da seguinte para o CI passar.

**Tech Stack:** PHP 8.4, Laravel 12, Filament v5, Livewire v4, PostgreSQL, Pest v4,
Larastan v3, Pint, Rector.

**Spec:** `docs/specs/2026-08-02-criacao-explicita-do-perfil-de-candidato.md`

## Global Constraints

- Módulos seguem `internachi/modular`: `candidates` → `He4rt\Candidates`, `users` →
  `He4rt\Users`, `panel-app` → `He4rt\App`.
- Domínio nunca importa de apresentação. O `UserObserver` **não pode** importar
  `He4rt\Candidates` — esse é um dos objetivos do trabalho. A relação `User::candidate()`
  permanece: `getFilamentAvatarUrl()` e `preferredLocale()` dependem dela, e desacoplá-la
  está fora do escopo (ver spec).
- Actions usam o método `execute()`, seguindo `StoreCandidateEducation`,
  `UpdateCandidateAction` e as demais em `app-modules/candidates/src/Actions/Onboarding/`.
- Toda string de UI passa por `__()`. Este plano não introduz string nova de UI.
- `Model::unguard()` está ativo (`app/Providers/AppServiceProvider.php:84`) — mass
  assignment é permitido.
- Rodar Pest **sempre** com `nice -n 19 ./vendor/bin/pest --parallel --processes=10 --compact`.
  Nunca `--parallel` sem `--processes`.
- Commits em Conventional Commits, sem linha de co-autoria.
- Branch: `refactor/explicit-candidate-profile`, a partir de `develop`.

## Estrutura de arquivos

**Criar**

- `app-modules/candidates/src/Actions/EnsureCandidateProfile.php` — Action idempotente,
  única responsável por materializar o perfil.
- `app-modules/candidates/tests/Feature/EnsureCandidateProfileTest.php`
- `app-modules/candidates/database/migrations/2026_08_02_000000_add_unique_index_to_candidates_user_id.php`
- `app-modules/users/tests/Feature/UserObserverTest.php`

**Modificar**

- `app-modules/users/src/UserObserver.php` — perde a criação do Candidate.
- `app-modules/panel-app/src/Filament/Pages/OnboardingWizard.php:108` — passa a chamar a Action.
- 10 arquivos de apresentação que assumem `candidate` não-nulo (Tarefa 3).
- 11 arquivos de teste cujos fixtures dependem da criação implícita (Tarefa 2).
- `app-modules/panel-app/tests/Feature/Filament/AfterRegisterTest.php` — contrato muda.

---

### Task 1: Action `EnsureCandidateProfile`

Cria a Action e seus testes. Nada mais no sistema a consome ainda, então a suíte
permanece verde.

**Files:**

- Create: `app-modules/candidates/src/Actions/EnsureCandidateProfile.php`
- Test: `app-modules/candidates/tests/Feature/EnsureCandidateProfileTest.php`

**Interfaces:**

- Consumes: `He4rt\Users\User`, `He4rt\Candidates\Models\Candidate`.
- Produces: `EnsureCandidateProfile::execute(User $user): Candidate` — idempotente,
  devolve o perfil existente quando já houver um. Tarefas 2, 4 e 5 dependem dessa
  assinatura exata.

- [ ] **Step 1: Escrever o teste que falha**

Criar `app-modules/candidates/tests/Feature/EnsureCandidateProfileTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Candidates\Actions\EnsureCandidateProfile;
use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;

it('creates a candidate profile with the onboarding defaults', function (): void {
    $user = User::factory()->create();

    // Enquanto o UserObserver ainda criar o perfil (até a Tarefa 5), o registro precisa
    // sair da frente para que este teste exercite o caminho de criação da Action.
    Candidate::query()->where('user_id', $user->getKey())->forceDelete();

    $candidate = resolve(EnsureCandidateProfile::class)->execute($user);

    expect($candidate->user_id)->toBe($user->getKey())
        ->and($candidate->is_onboarded)->toBeFalse()
        ->and($candidate->preferred_language)->toBe('pt_BR')
        ->and($candidate->expected_salary_currency)->toBe('BRL')
        ->and($candidate->is_open_to_remote)->toBeTrue();
});

it('returns the existing profile instead of creating a second one', function (): void {
    $user = User::factory()->create();
    $action = resolve(EnsureCandidateProfile::class);

    $first = $action->execute($user);
    $second = $action->execute($user);

    expect($second->getKey())->toBe($first->getKey())
        ->and(Candidate::query()->where('user_id', $user->getKey())->count())->toBe(1);
});
```

- [ ] **Step 2: Rodar e confirmar a falha**

```bash
nice -n 19 ./vendor/bin/pest app-modules/candidates/tests/Feature/EnsureCandidateProfileTest.php --compact
```

Esperado: FAIL com `Class "He4rt\Candidates\Actions\EnsureCandidateProfile" not found`.

- [ ] **Step 3: Implementar a Action**

Criar `app-modules/candidates/src/Actions/EnsureCandidateProfile.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions;

use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;

/**
 * Materializa o perfil de candidato de um usuário.
 *
 * Idempotente: devolve o perfil existente quando já houver um. Os defaults repetem os
 * `default()` das colunas em `create_candidates_table`, mantendo a intenção legível sem
 * depender do schema.
 */
final class EnsureCandidateProfile
{
    public function execute(User $user): Candidate
    {
        return Candidate::query()->firstOrCreate(
            ['user_id' => $user->getKey()],
            [
                'is_onboarded' => false,
                'preferred_language' => 'pt_BR',
                'expected_salary_currency' => 'BRL',
                'is_open_to_remote' => true,
            ],
        );
    }
}
```

- [ ] **Step 4: Rodar e confirmar que passa**

```bash
nice -n 19 ./vendor/bin/pest app-modules/candidates/tests/Feature/EnsureCandidateProfileTest.php --compact
```

Esperado: 2 passed.

- [ ] **Step 5: Commit**

```bash
git add app-modules/candidates/src/Actions/EnsureCandidateProfile.php \
        app-modules/candidates/tests/Feature/EnsureCandidateProfileTest.php
git commit -m "feat(candidates): add EnsureCandidateProfile action"
```

---

### Task 2: Fixtures deixam de depender do observer

Onze arquivos hoje fazem `User::factory()->create()`, chamam `refresh()` para furar o
cache `null` da issue #261 e pegam o `Candidate` que o observer criou. Cada um passa a
materializar o perfil pela Action e a injetá-lo na instância com `setRelation()`, o que
elimina a dança do `refresh()`.

O padrão funciona **antes e depois** da Tarefa 5: com o observer ativo, `firstOrCreate`
encontra o perfil existente e nada é duplicado; sem ele, cria.

Três arquivos que quebraram na medição do spec ficam de fora de propósito —
`JobApplicationFormTest`, `JobApplicationFormKnockoutTest` e `QuestionValidationsTest` usam
`Candidate::factory()->create()` e falharam só no cenário em que o observer também perdia
o `assignRole`. Como a role continua no observer, eles não são afetados.

**Files:**

- Modify: `app-modules/panel-app/tests/Feature/Filament/Pages/OnboardingWizardTest.php:18-28`
- Modify: `app-modules/panel-app/tests/Feature/Livewire/Jobs/SavedJobsWidgetTest.php:17-21`
- Modify: `app-modules/panel-app/tests/Feature/Filament/MyProfile/CandidateProfileInfoTest.php:13-17,53-57`
- Modify: `app-modules/panel-app/tests/Feature/Filament/Widgets/UserTotalApplicationsTest.php:14-18`
- Modify: `app-modules/panel-app/tests/Feature/Filament/MyProfile/CandidateWorkExperienceTest.php:13-21`
- Modify: `app-modules/panel-app/tests/Feature/Filament/Pages/AppDashboardTest.php:14-17`
- Modify: `app-modules/panel-app/tests/Feature/Livewire/Jobs/BookmarkJobButtonTest.php:13-17`
- Modify: `app-modules/panel-app/tests/Feature/Filament/Pages/CandidateMyProfilePageTest.php:18-22,34-38`
- Modify: `app-modules/panel-app/tests/Feature/Filament/JobRequisitions/ViewJobRequisitionStatusTest.php:73-74,92-93`
- Modify: `app-modules/panel-app/tests/Feature/JobApplyIntentTest.php:21`
- Modify: `app-modules/users/tests/Feature/UserPreferredLocaleTest.php:9-10,17-18`

**Interfaces:**

- Consumes: `EnsureCandidateProfile::execute(User $user): Candidate` da Tarefa 1.
- Produces: nada — só fixtures.

- [ ] **Step 1: Converter `OnboardingWizardTest` (Pages)**

Em `app-modules/panel-app/tests/Feature/Filament/Pages/OnboardingWizardTest.php`, trocar o
início do `beforeEach`:

```php
// antes
beforeEach(function (): void {
    $this->user = User::factory()->create();

    // O UserObserver já cria um Candidate por User, mas o acesso a `$user->candidate`
    // dentro do observer deixa a relação cacheada como null nesta instância. Sem o
    // refresh, `auth()->user()->candidate` continuaria null durante todo o teste.
    $this->user->refresh();

    $this->candidate = $this->user->candidate;
    $this->candidate->update(['is_onboarded' => false]);

// depois
beforeEach(function (): void {
    $this->user = User::factory()->create();

    $this->candidate = resolve(EnsureCandidateProfile::class)->execute($this->user);
    $this->user->setRelation('candidate', $this->candidate);
    $this->candidate->update(['is_onboarded' => false]);
```

Adicionar o import `use He4rt\Candidates\Actions\EnsureCandidateProfile;` junto aos demais.

- [ ] **Step 2: Rodar o arquivo convertido**

```bash
nice -n 19 ./vendor/bin/pest app-modules/panel-app/tests/Feature/Filament/Pages/OnboardingWizardTest.php --compact
```

Esperado: PASS — o observer ainda existe, e o `firstOrCreate` devolve o perfil dele.

- [ ] **Step 3: Converter `SavedJobsWidgetTest`**

```php
// antes
    $this->user = User::factory()->create();
    $this->user->refresh();

    $this->candidate = $this->user->candidate;

// depois
    $this->user = User::factory()->create();

    $this->candidate = resolve(EnsureCandidateProfile::class)->execute($this->user);
    $this->user->setRelation('candidate', $this->candidate);
```

- [ ] **Step 4: Converter `UserTotalApplicationsTest`, `BookmarkJobButtonTest` e `AppDashboardTest`**

Os três seguem a mesma forma. Em `UserTotalApplicationsTest` e `BookmarkJobButtonTest`:

```php
// antes
    $this->user = User::factory()->create();
    $this->user->refresh();

    $this->candidate = $this->user->candidate;

// depois
    $this->user = User::factory()->create();

    $this->candidate = resolve(EnsureCandidateProfile::class)->execute($this->user);
    $this->user->setRelation('candidate', $this->candidate);
```

Em `AppDashboardTest`, que não guarda o `$this->candidate`:

```php
// antes
    $this->user = User::factory()->create();
    $this->user->refresh();
    $this->user->candidate->update([

// depois
    $this->user = User::factory()->create();
    $candidate = resolve(EnsureCandidateProfile::class)->execute($this->user);
    $this->user->setRelation('candidate', $candidate);
    $candidate->update([
```

- [ ] **Step 5: Converter `CandidateProfileInfoTest`, incluindo o caso "sem candidato"**

No `beforeEach`, aplicar o mesmo padrão dos passos anteriores. Além disso, o teste da
linha 53 fica mais direto — hoje ele precisa apagar o perfil que o observer criou:

```php
// antes
it('returns ui-avatars url when user has no candidate', function (): void {
    $user = User::factory()->create();

    $user->candidate?->forceDelete();
    $user->unsetRelation('candidate');

// depois
it('returns ui-avatars url when user has no candidate', function (): void {
    $user = User::factory()->create();

    // Até a Tarefa 5 o observer ainda cria o perfil; depois dela estas duas linhas somem.
    $user->candidate?->forceDelete();
    $user->unsetRelation('candidate');
```

Não altere este bloco agora — ele é removido na Tarefa 5, quando o observer parar de criar.
O comentário sinaliza a dívida para quem executar aquela tarefa.

- [ ] **Step 6: Converter `CandidateWorkExperienceTest`**

Aplicar o mesmo padrão no `beforeEach` e apagar o comentário das linhas 17-20, que descreve
o contorno do PR #260 (dois registros para o mesmo `user_id`) — ele deixa de valer.

- [ ] **Step 7: Converter `CandidateMyProfilePageTest`**

Este arquivo cria o usuário dentro de cada teste, em dois pontos:

```php
// antes (linhas 18-22 e 34-38, mesmo formato nos dois)
    $user = User::factory()->create();
    ...
    $user->candidate->update([

// depois
    $user = User::factory()->create();
    $candidate = resolve(EnsureCandidateProfile::class)->execute($user);
    $user->setRelation('candidate', $candidate);
    ...
    $candidate->update([
```

A linha 58 (`actingAs(User::factory()->create());`) não muda: esse teste exercita
justamente o usuário sem perfil.

- [ ] **Step 8: Converter `ViewJobRequisitionStatusTest` e `JobApplyIntentTest`**

Ambos usam `$user->candidate()->update([...])` — um update via query, que não depende do
cache da relação, mas depende de o perfil existir:

```php
// antes
    $user = User::factory()->create();
    $user->candidate()->update(['is_onboarded' => true]);

// depois
    $user = User::factory()->create();
    resolve(EnsureCandidateProfile::class)->execute($user)->update(['is_onboarded' => true]);
```

Em `JobApplyIntentTest` o usuário nasce no `beforeEach` (linha 21) e os `update` aparecem
em cinco testes (linhas 53, 66, 79, 104, 124). Materialize o perfil uma vez no
`beforeEach` e guarde-o:

```php
// beforeEach, depois de criar $this->user
    $this->candidate = resolve(EnsureCandidateProfile::class)->execute($this->user);
    $this->user->setRelation('candidate', $this->candidate);

// nos cinco testes
    $this->candidate->update(['is_onboarded' => true]);
```

- [ ] **Step 9: Converter `UserPreferredLocaleTest`**

Este teste vive no módulo `users`, que não pode importar `He4rt\Candidates`. Use a relação,
que não exige import:

```php
// antes
it('exposes the candidate preferred language as the locale preference', function (): void {
    $user = User::factory()->create();
    $user->candidate()->update(['preferred_language' => 'pt_BR']);

// depois
it('exposes the candidate preferred language as the locale preference', function (): void {
    $user = User::factory()->create();
    $user->candidate()->firstOrCreate([])->update(['preferred_language' => 'pt_BR']);
    $user->unsetRelation('candidate');
```

O segundo teste (linha 17-18) faz `$user->candidate()->delete()` para provar que o locale
some sem perfil. Ele passa a não precisar de nada — deixe como está por enquanto; a
Tarefa 5 simplifica.

- [ ] **Step 10: Rodar a suíte completa**

```bash
nice -n 19 ./vendor/bin/pest --parallel --processes=10 --compact
```

Esperado: 1201 tests, 0 failed. O observer continua intacto; só os fixtures mudaram.

- [ ] **Step 11: Commit**

```bash
git add app-modules/panel-app/tests app-modules/users/tests
git commit -m "test: materialize candidate profiles through EnsureCandidateProfile"
```

---

### Task 3: Null-safety onde o perfil pode faltar

Com a Tarefa 5, SuperAdmin e Admin passam a chegar ao painel app sem `Candidate` — o
`RedirectIfOnboardingIncomplete:26` os libera do onboarding. Estes pontos assumem a relação
não-nula e precisam se comportar como "sem perfil".

**Files:**

- Modify: `app-modules/panel-app/src/Filament/Resources/Applications/Tables/ApplicationsTable.php:18`
- Modify: `app-modules/panel-app/src/Livewire/JobApplicationForm.php:64`
- Modify: `app-modules/panel-app/src/Livewire/Jobs/BookmarkJobButton.php:37`
- Modify: `app-modules/panel-app/src/Livewire/ProfileCard.php:17-23`
- Modify: `app-modules/panel-app/src/Livewire/MyProfile/CandidateSkills.php:30,95`
- Modify: `app-modules/panel-app/src/Livewire/MyProfile/CandidateProfileInfo.php:31,43,83`
- Modify: `app-modules/panel-app/src/Livewire/MyProfile/CandidateEducation.php:32,109`
- Modify: `app-modules/panel-app/src/Livewire/MyProfile/CandidatePreferences.php:33,129`
- Modify: `app-modules/panel-app/src/Livewire/MyProfile/CandidateWorkExperience.php:36,125`
- Modify: `app-modules/panel-app/src/Livewire/MyProfile/CandidateResumeUpload.php:41,70`
- Test: `app-modules/panel-app/tests/Feature/Filament/AdminWithoutCandidateTest.php` (criar)

**Interfaces:**

- Consumes: nada das tarefas anteriores.
- Produces: nada consumido adiante.

- [ ] **Step 1: Escrever o teste que falha**

Criar `app-modules/panel-app/tests/Feature/Filament/AdminWithoutCandidateTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Resources\Applications\Pages\ListApplications;
use He4rt\App\Livewire\ProfileCard;
use He4rt\Permissions\Roles;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
    $this->admin->assignRole(Roles::SuperAdmin);
    $this->admin->candidate?->forceDelete();
    $this->admin->unsetRelation('candidate');

    actingAs($this->admin);
    filament()->setCurrentPanel(FilamentPanel::App->value);
});

it('renders the applications list for an admin without a candidate profile', function (): void {
    livewire(ListApplications::class)->assertOk();
});

it('renders the profile card for an admin without a candidate profile', function (): void {
    livewire(ProfileCard::class)
        ->assertOk()
        ->assertSet('profileCompletionPercentage', 0);
});
```

Confirme o FQCN de `ListApplications` antes de rodar:

```bash
grep -rn "class ListApplications" app-modules/panel-app/src
```

- [ ] **Step 2: Rodar e confirmar a falha**

```bash
nice -n 19 ./vendor/bin/pest app-modules/panel-app/tests/Feature/Filament/AdminWithoutCandidateTest.php --compact
```

Esperado: FAIL com `Call to a member function getKey() on null`, vindo de
`ApplicationsTable:18`.

- [ ] **Step 3: Corrigir `ApplicationsTable`**

```php
// antes
->modifyQueryUsing(fn (Builder $query) => $query->where('candidate_id', auth()->user()->candidate->getKey()))

// depois
->modifyQueryUsing(fn (Builder $query) => $query->where(
    'candidate_id',
    auth()->user()?->candidate?->getKey(),
))
```

Com `candidate_id = null` a query não devolve linhas, que é o resultado correto para quem
não tem perfil.

- [ ] **Step 4: Corrigir `ProfileCard`**

```php
// antes
        $candidate = $user->candidate;

        return view('panel-app::livewire.profile-card', [
            'links' => $links,
            'candidate' => $candidate,
            'profileCompletionPercentage' => $candidate->profile_completion_percentage ?? 0,
            'missingSections' => $candidate ? $candidate->getMissingProfileSections() : [],
        ]);

// depois
        $candidate = $user->candidate;

        return view('panel-app::livewire.profile-card', [
            'links' => $links,
            'candidate' => $candidate,
            'profileCompletionPercentage' => $candidate?->profile_completion_percentage ?? 0,
            'missingSections' => $candidate?->getMissingProfileSections() ?? [],
        ]);
```

- [ ] **Step 5: Corrigir os componentes de `MyProfile`, `JobApplicationForm` e `BookmarkJobButton`**

Todos leem `auth()->user()->candidate` e usam o resultado adiante. O conserto é um early
return antes do primeiro uso. Os dois formatos que aparecem:

**`mount(): void` que preenche o formulário** — sem perfil, não há o que preencher.
`CandidateSkills.php:30`:

```php
// antes
    public function mount(): void
    {
        $candidate = auth()->user()->candidate;

        $this->form->fill([
            'skills' => $candidate->skills->map(fn (Skill $skill) => [

// depois
    public function mount(): void
    {
        $candidate = auth()->user()?->candidate;

        if ($candidate === null) {
            return;
        }

        $this->form->fill([
            'skills' => $candidate->skills->map(fn (Skill $skill) => [
```

**`submit(): void` que grava** — sem perfil, não há o que gravar.
`CandidatePreferences.php:126`:

```php
// antes
    public function submit(): void
    {
        $data = $this->form->getState();

        auth()->user()->candidate->update($data);

// depois
    public function submit(): void
    {
        $data = $this->form->getState();
        $candidate = auth()->user()?->candidate;

        if ($candidate === null) {
            return;
        }

        $candidate->update($data);
```

Aplique um dos dois formatos em cada ponto, conforme o método preencha ou grave. Onde o
método não for `void`, devolva o vazio do tipo declarado (`[]` para array, `null` para
nulável) em vez de `return;`. Os métodos e linhas:

| Arquivo                                 | Linhas     |
| --------------------------------------- | ---------- |
| `MyProfile/CandidateSkills.php`         | 30, 95     |
| `MyProfile/CandidateProfileInfo.php`    | 31, 43, 83 |
| `MyProfile/CandidateEducation.php`      | 32, 109    |
| `MyProfile/CandidatePreferences.php`    | 33, 129    |
| `MyProfile/CandidateWorkExperience.php` | 36, 125    |
| `MyProfile/CandidateResumeUpload.php`   | 41, 70     |
| `JobApplicationForm.php`                | 64         |
| `Jobs/BookmarkJobButton.php`            | 37         |

- [ ] **Step 6: Rodar o teste novo e os testes de `MyProfile`**

```bash
nice -n 19 ./vendor/bin/pest app-modules/panel-app/tests/Feature/Filament/AdminWithoutCandidateTest.php \
    app-modules/panel-app/tests/Feature/Filament/MyProfile --compact
```

Esperado: todos passam.

- [ ] **Step 7: Rodar a suíte completa**

```bash
nice -n 19 ./vendor/bin/pest --parallel --processes=10 --compact
```

Esperado: 0 failed.

- [ ] **Step 8: Commit**

```bash
git add app-modules/panel-app/src app-modules/panel-app/tests/Feature/Filament/AdminWithoutCandidateTest.php
git commit -m "fix(panel-app): tolerate users without a candidate profile"
```

---

### Task 4: Onboarding materializa o perfil

**Files:**

- Modify: `app-modules/panel-app/src/Filament/Pages/OnboardingWizard.php:108`
- Modify: `app-modules/candidates/src/Actions/Onboarding/StoreCandidateEducation.php:15`
- Modify: `app-modules/candidates/src/Actions/Onboarding/StoreCandidateWorkExperiences.php:16`
- Test: `app-modules/panel-app/tests/Feature/Filament/OnboardingWizardTest.php`

**Interfaces:**

- Consumes: `EnsureCandidateProfile::execute(User $user): Candidate` da Tarefa 1.
- Produces: garantia de que qualquer usuário que abra o wizard tem perfil — a Tarefa 5
  depende disso.

- [ ] **Step 1: Escrever o teste que falha**

Acrescentar ao final de
`app-modules/panel-app/tests/Feature/Filament/OnboardingWizardTest.php`:

```php
it('creates the candidate profile when a user without one opens the wizard', function (): void {
    $user = User::factory()->create();
    $user->candidate?->forceDelete();
    $user->unsetRelation('candidate');

    actingAs($user);

    livewire(OnboardingWizard::class)->assertOk();

    expect(Candidate::query()->where('user_id', $user->getKey())->count())->toBe(1);
});
```

- [ ] **Step 2: Rodar e confirmar a falha**

```bash
nice -n 19 ./vendor/bin/pest app-modules/panel-app/tests/Feature/Filament/OnboardingWizardTest.php --compact
```

Esperado: FAIL — nenhum `Candidate` é criado (contagem 0), ou erro ao montar o schema com
`record` nulo.

- [ ] **Step 3: Trocar a leitura pela Action**

Em `app-modules/panel-app/src/Filament/Pages/OnboardingWizard.php`, adicionar o import
`use He4rt\Candidates\Actions\EnsureCandidateProfile;` e alterar o `mount()`:

```php
// antes (linha 108)
        $this->user = $user;
        $this->record = $user->candidate;
        $this->content->fill();

// depois
        $this->user = $user;
        $this->record = resolve(EnsureCandidateProfile::class)->execute($user);
        $user->setRelation('candidate', $this->record);
        $this->content->fill();
```

O early return da linha 101 (`$user->candidate?->hasCompletedOnboarding()`) fica como está:
quem já concluiu o onboarding é redirecionado antes de chegar aqui.

- [ ] **Step 4: Blindar as Actions de onboarding**

`StoreCandidateEducation:15` e `StoreCandidateWorkExperiences:16` leem
`auth()->user()->candidate` e usam o resultado sem checagem. Hoje o `mount()` garante o
perfil antes de qualquer submit, mas as duas são públicas e nada impede outra chamada.
Como são do próprio módulo `candidates`, podem usar a Action diretamente:

```php
// antes — StoreCandidateEducation.php
use He4rt\Candidates\DTOs\Collections\CandidateEducationCollection;
use He4rt\Candidates\Models\Candidate;

    public function execute(CandidateEducationCollection $degree): void
    {
        /** @var Candidate $candidate */
        $candidate = auth()->user()->candidate;

// depois
use He4rt\Candidates\Actions\EnsureCandidateProfile;
use He4rt\Candidates\DTOs\Collections\CandidateEducationCollection;

    public function execute(CandidateEducationCollection $degree): void
    {
        $candidate = resolve(EnsureCandidateProfile::class)->execute(auth()->user());
```

O import de `Candidate` e o `@var` deixam de ser necessários — a Action já devolve o tipo.
Aplicar a mesma troca em `StoreCandidateWorkExperiences.php:16`, preservando o corpo de
cada método.

- [ ] **Step 5: Rodar e confirmar que passa**

```bash
nice -n 19 ./vendor/bin/pest app-modules/panel-app/tests/Feature/Filament/OnboardingWizardTest.php \
    app-modules/panel-app/tests/Feature/Filament/Pages/OnboardingWizardTest.php \
    app-modules/candidates/tests/Feature/StoreCandidateActionsTest.php --compact
```

Esperado: todos passam.

- [ ] **Step 6: Commit**

```bash
git add app-modules/panel-app/src/Filament/Pages/OnboardingWizard.php \
        app-modules/panel-app/tests/Feature/Filament/OnboardingWizardTest.php \
        app-modules/candidates/src/Actions/Onboarding
git commit -m "feat(panel-app): create the candidate profile on onboarding"
```

---

### Task 5: Observer para de criar o perfil

O passo que fecha a issue #261. A partir daqui `users` não importa mais `He4rt\Candidates`.

**Files:**

- Modify: `app-modules/users/src/UserObserver.php`
- Create: `app-modules/users/tests/Feature/UserObserverTest.php`
- Modify: `app-modules/panel-app/tests/Feature/Filament/AfterRegisterTest.php`
- Modify: `app-modules/panel-app/tests/Feature/Filament/MyProfile/CandidateProfileInfoTest.php:53-57`
- Modify: `app-modules/candidates/tests/Feature/EnsureCandidateProfileTest.php`
- Modify: `app-modules/panel-app/tests/Feature/Filament/AdminWithoutCandidateTest.php`
- Modify: `app-modules/panel-app/tests/Feature/Filament/OnboardingWizardTest.php`

**Interfaces:**

- Consumes: tudo das Tarefas 1 a 4.
- Produces: `UserObserver::created()` passa a fazer só `assignRole(Roles::User)`.

- [ ] **Step 1: Escrever o teste do novo contrato**

Criar `app-modules/users/tests/Feature/UserObserverTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Permissions\Roles;
use He4rt\Users\User;

it('assigns the base user role on creation', function (): void {
    $user = User::factory()->create();

    expect($user->hasRole(Roles::User))->toBeTrue();
});

it('does not create a candidate profile', function (): void {
    $user = User::factory()->create();

    expect($user->candidate()->exists())->toBeFalse();
});

it('leaves the candidate relation resolvable after creation', function (): void {
    $user = User::factory()->create();
    $user->candidate()->create([]);

    expect($user->candidate)->not->toBeNull();
});
```

O terceiro teste é a regressão da issue #261: antes, o acesso dentro do observer deixava a
relação cacheada como `null` nesta mesma instância.

- [ ] **Step 2: Rodar e confirmar a falha**

```bash
nice -n 19 ./vendor/bin/pest app-modules/users/tests/Feature/UserObserverTest.php --compact
```

Esperado: FAIL nos dois últimos testes — o observer ainda cria o perfil, e a relação está
cacheada como `null`.

- [ ] **Step 3: Enxugar o observer**

`app-modules/users/src/UserObserver.php`:

```php
// antes
use He4rt\Candidates\Models\Candidate;

    public function created(User $user): void
    {
        if (! $user->candidate) {
            Candidate::query()->create([
                'user_id' => $user->id,
                'is_onboarded' => false,
                'preferred_language' => 'en',
                'expected_salary_currency' => 'USD',
                'is_open_to_remote' => true,
            ]);

            $user->assignRole('user');
        }
    }

// depois
use He4rt\Permissions\Roles;

    public function created(User $user): void
    {
        $user->assignRole(Roles::User);
    }
```

O import de `Candidate` sai; entra o de `Roles`.

- [ ] **Step 4: Rodar e confirmar que passa**

```bash
nice -n 19 ./vendor/bin/pest app-modules/users/tests/Feature/UserObserverTest.php --compact
```

Esperado: 3 passed.

- [ ] **Step 5: Atualizar `AfterRegisterTest`**

O contrato muda: o registro não cria mais o perfil; o onboarding cria.

```php
// antes (final do teste)
    assertDatabaseCount(Candidate::class, 1);

// depois
    assertDatabaseCount(Candidate::class, 0);

    livewire(OnboardingWizard::class)->assertOk();

    assertDatabaseCount(Candidate::class, 1);
```

Adicionar `use He4rt\App\Filament\Pages\OnboardingWizard;` aos imports. A asserção inicial
`assertDatabaseCount(Candidate::class, 0)` permanece.

- [ ] **Step 6: Remover os contornos que ficaram obsoletos**

Três lugares deixam de precisar apagar o perfil que o observer criava:

Em `CandidateProfileInfoTest.php:53-57`:

```php
// antes
    $user = User::factory()->create();

    // Até a Tarefa 5 o observer ainda cria o perfil; depois dela estas duas linhas somem.
    $user->candidate?->forceDelete();
    $user->unsetRelation('candidate');

// depois
    $user = User::factory()->create();
```

Em `EnsureCandidateProfileTest.php`, remover as duas linhas do primeiro teste:

```php
// antes
    // Enquanto o UserObserver ainda criar o perfil (até a Tarefa 5), o registro precisa
    // sair da frente para que este teste exercite o caminho de criação da Action.
    Candidate::query()->where('user_id', $user->getKey())->forceDelete();

// depois
    (bloco removido)
```

Em `AdminWithoutCandidateTest.php` e no teste novo de `OnboardingWizardTest.php`, remover
as linhas `$user->candidate?->forceDelete();` e `$user->unsetRelation('candidate');`.

- [ ] **Step 7: Rodar a suíte completa**

```bash
nice -n 19 ./vendor/bin/pest --parallel --processes=10 --compact
```

Esperado: 0 failed. Se algum teste ainda cair, ele depende da criação implícita e não foi
coberto pela Tarefa 2 — converta-o com o mesmo padrão.

- [ ] **Step 8: Commit**

```bash
git add app-modules/users app-modules/panel-app/tests app-modules/candidates/tests
git commit -m "refactor(users): stop creating the candidate profile in UserObserver"
```

---

### Task 6: Índice único em `candidates.user_id`

**Files:**

- Create: `app-modules/candidates/database/migrations/2026_08_02_000000_add_unique_index_to_candidates_user_id.php`
- Test: `app-modules/candidates/tests/Feature/EnsureCandidateProfileTest.php` (acrescentar caso)

**Interfaces:**

- Consumes: a garantia da Tarefa 5 de que nada mais cria perfis implicitamente.
- Produces: nada consumido adiante.

- [ ] **Step 1: Escrever o teste que falha**

Acrescentar a `app-modules/candidates/tests/Feature/EnsureCandidateProfileTest.php`:

```php
it('rejects a second profile for the same user at the database level', function (): void {
    $user = User::factory()->create();
    resolve(EnsureCandidateProfile::class)->execute($user);

    Candidate::query()->create(['user_id' => $user->getKey()]);
})->throws(QueryException::class);

it('allows a new profile after the previous one was soft deleted', function (): void {
    $user = User::factory()->create();
    $first = resolve(EnsureCandidateProfile::class)->execute($user);

    $first->delete();

    $second = Candidate::query()->create(['user_id' => $user->getKey()]);

    expect($second->getKey())->not->toBe($first->getKey());
});
```

Adicionar `use Illuminate\Database\QueryException;` aos imports.

- [ ] **Step 2: Rodar e confirmar a falha**

```bash
nice -n 19 ./vendor/bin/pest app-modules/candidates/tests/Feature/EnsureCandidateProfileTest.php --compact
```

Esperado: FAIL no primeiro dos dois — nenhuma exceção é lançada, porque a constraint não
existe.

- [ ] **Step 3: Escrever a migration**

Criar
`app-modules/candidates/database/migrations/2026_08_02_000000_add_unique_index_to_candidates_user_id.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Índice parcial: `candidates` usa SoftDeletes, então um perfil apagado não pode bloquear
 * a criação de um novo para o mesmo usuário.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'CREATE UNIQUE INDEX candidates_user_id_unique ON candidates (user_id) WHERE deleted_at IS NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS candidates_user_id_unique');
    }
};
```

- [ ] **Step 4: Rodar e confirmar que passa**

```bash
nice -n 19 ./vendor/bin/pest app-modules/candidates/tests/Feature/EnsureCandidateProfileTest.php --compact
```

Esperado: 4 passed.

- [ ] **Step 5: Conferir a migration num banco limpo**

```bash
php artisan migrate:fresh --seed
```

Esperado: termina sem violação de constraint. O `DevelopmentSeeder` usa
`Candidate::factory()`, que agora produz exatamente um perfil por usuário.

- [ ] **Step 6: Commit**

```bash
git add app-modules/candidates/database/migrations app-modules/candidates/tests
git commit -m "feat(candidates): add partial unique index on candidates.user_id"
```

---

### Task 7: Fechamento — qualidade e issue

**Files:**

- Modify: nenhum arquivo novo; só correções apontadas pelas ferramentas.

**Interfaces:**

- Consumes: todas as tarefas anteriores.
- Produces: branch pronta para PR.

- [ ] **Step 1: Procurar menções obsoletas ao observer**

```bash
grep -rn "UserObserver" --include="*.php" app-modules tests
```

Esperado: só `User.php` (o atributo `#[ObservedBy]`), `UserObserver.php` e
`UserObserverTest.php`. Qualquer comentário remanescente descrevendo o contorno do PR #260
deve ser removido.

- [ ] **Step 2: Rector**

```bash
./vendor/bin/rector process --dry-run --ansi
```

Se houver mudanças propostas, aplicar com `./vendor/bin/rector process --ansi` e revisar.

- [ ] **Step 3: Pint**

```bash
./vendor/bin/pint --test --ansi
```

Se falhar, rodar `./vendor/bin/pint --ansi`.

- [ ] **Step 4: PHPStan**

```bash
./vendor/bin/phpstan analyse --ansi
```

Esperado: sem erros. Se o null-safety da Tarefa 3 gerar avisos de tipo, corrija a
assinatura em vez de suprimir — só use `ignoreErrors` no formato de bloco indentado
descrito na guideline `phpstan`.

- [ ] **Step 5: Suíte completa**

```bash
nice -n 19 ./vendor/bin/pest --parallel --processes=10 --compact
```

Esperado: 1201 tests (mais os novos), 0 failed.

- [ ] **Step 6: Commit final e push**

```bash
git add -A
git commit -m "chore: apply rector, pint and phpstan fixes"
git push --no-verify -u origin refactor/explicit-candidate-profile
```

O `--no-verify` evita que o hook do husky dispare `pest --parallel` sem `--processes` e
trave a máquina; a bateria equivalente já foi rodada nos passos 2 a 5.

- [ ] **Step 7: Abrir o PR referenciando a issue**

```bash
gh pr create --base develop \
  --title "refactor(users): create the candidate profile explicitly at onboarding" \
  --body "Closes #261

Spec: docs/specs/2026-08-02-criacao-explicita-do-perfil-de-candidato.md
Plano: docs/plans/2026-08-02-criacao-explicita-do-perfil-de-candidato.md"
```
