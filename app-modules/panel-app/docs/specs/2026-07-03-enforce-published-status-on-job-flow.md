---
type: spec
title: 'Enforce published status on job view and application flow'
module: panel-app
status: proposed
date: 2026-07-03
author: Clintonrocha98
related:
    issue: 226
    spec: panel-app/2026-07-02-preserve-job-intent-after-auth
---

# Enforce published status on job view and application flow

## Contexto

Só vagas com `status = RequisitionStatusEnum::Published` deveriam ser visíveis e
aceitar candidatura. Hoje esse filtro existe **apenas** na listagem pública
(`SearchJobs::jobs()`), que combina três condições: `publicJobs()` +
`status = Published` + `hasStages()`.

Duas camadas do fluxo de candidatura **não** checam o status, então quem tem o
link direto de uma vaga fora do ar (rascunho, fechada, cancelada) consegue abrir
a página e se candidatar normalmente:

- **`ViewJobRequisition::mount()`**
  (`app-modules/panel-app/src/Filament/Resources/JobRequisitions/Pages/ViewJobRequisition.php`)
  — resolve a vaga pelo slug com `firstOrFail()` e não verifica status.
- **`ApplyToJobRequisitionAction::execute()`**
  (`app-modules/applications/src/Actions/ApplyToJobRequisitionAction.php`)
  — cria a `Application` sem validar o estado da requisition. É o _single creation
  chokepoint_ (a própria docblock diz isso), chamado por dois lugares:
  `ViewJobRequisition::applyDirectly()` (vagas sem screening) e
  `JobApplicationForm::submit()` (vagas com screening).

> Esta falha **precede** o PR #225. Naquele PR o `JobApplyIntentController`
> (rota de intenção do botão "Candidatar-se" para visitantes) já passou a exigir
> `Published` — mas as camadas de view e ação de domínio continuam sem trava, então
> o furo permanece por acesso direto ao link.

### Onde "vaga disponível" é verificado hoje

| Camada                                           | Checa status? | Predicado                                             |
| ------------------------------------------------ | :-----------: | ----------------------------------------------------- |
| `SearchJobs::jobs()` (listagem)                  |      ✅       | `publicJobs()` + `status = Published` + `hasStages()` |
| `JobApplyIntentController` (#225, rota do botão) |      ✅       | `status === Published`                                |
| `ViewJobRequisition::mount()`                    |      ❌       | `firstOrFail()` por slug, sem status                  |
| `ApplyToJobRequisitionAction::execute()`         |      ❌       | cria a candidatura sem checar                         |

## Objetivos

- Garantir que uma vaga não publicada **não exponha** a página de candidatura por
  acesso direto ao link.
- Garantir que **nenhuma** `Application` seja criada para uma vaga não publicada,
  mesmo chamando a ação diretamente (garantia de domínio).
- Manter o fluxo de vagas publicadas **inalterado**.
- Centralizar o predicado de "vaga publicada", hoje duplicado, numa fonte única.

## Não-objetivos

- **Não** ampliar o predicado para o triplo do `SearchJobs`. Usamos **apenas**
  `status === Published` — o mesmo do `JobApplyIntentController` (#225). As
  condições `publicJobs()` (`is_internal_only = false`) e `hasStages()` são sobre
  _listabilidade/busca_, não sobre "a vaga está no ar". Incluí-las no guard
  **quebraria as vagas internas** (ver seção abaixo).
- **Não** criar um scope de query `available()` no model (YAGNI — `SearchJobs` já
  encadeia os scopes que precisa). Só o helper de instância `isPublished()` é
  introduzido.
- **Não** alterar o comportamento de vagas internas nem de confidenciais.

## Vagas internas (`is_internal_only`) — restrição crítica de predicado

`is_internal_only` é o mecanismo de **vaga interna**: o RH marca a vaga como interna
(some da listagem pública via `publicJobs()`) e distribui **apenas pelo link direto**
— quem tem a URL abre e se candidata. Não há trava de acesso por link; a única
"proteção" é não aparecer na busca. Isso é **ortogonal ao status**: o enum de status
é compartilhado, então uma vaga interna que aceita candidatura também está em
`Published`.

Por isso o guard verifica **somente `status === Published`** e nunca `publicJobs()`:

```
Vaga interna + Published    → isPublished() = true  → link direto ABRE e candidata  ✅
Vaga interna + Draft/Closed  → isPublished() = false → bloqueada                     ✅
```

Se o guard usasse o predicado completo do `SearchJobs` (com `publicJobs()`), uma vaga
interna publicada seria redirecionada/negada mesmo estando no ar — regressão da
feature de vaga interna. `hasStages()` fica igualmente fora: é pré-condição de
_listagem_, não de "estar publicada".

## Decisão de UX (view) — DECIDIDO: redirect + aviso

Quando alguém acessa `/vagas/{slug}` de uma vaga não publicada e **não** tem
candidatura própria àquela vaga: **redirect para a listagem com notificação de aviso**,
reusando a chave `panel-app::filament.pages.job_description.job_unavailable`.

Escolhido em vez de `abort(404)` por consistência: é o mesmo comportamento que o
`JobApplyIntentController` (#225) já adota e que a própria `mount()` já usa no caso
"já candidatado".

> As vagas internas **não** dependem desta escolha — uma vaga interna publicada nunca
> chega no ramo de bloqueio (o guard checa só status).

## Arquitetura / componentes

### 1. `JobRequisition::isPublished(): bool` — recruitment (fonte única de verdade)

```php
public function isPublished(): bool
{
    return $this->status === RequisitionStatusEnum::Published;
}
```

Reusado pela view, pela action e pelo `JobApplyIntentController` (refatorado),
eliminando as cópias soltas de `=== Published`.

### 2. `RequisitionNotPublishedException` — applications/src/Exceptions

Segue o padrão de `InvalidTransitionException` (classe `final`, `extends Exception`,
construtores nomeados estáticos):

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Exceptions;

use Exception;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

final class RequisitionNotPublishedException extends Exception
{
    public static function cannotApplyToRequisition(JobRequisition $requisition): self
    {
        return new self(sprintf(
            'Cannot apply to requisition %s: status is %s, not Published.',
            $requisition->getKey(),
            $requisition->status->value,
        ), 422);
    }
}
```

### 3. Guard de domínio — `ApplyToJobRequisitionAction::execute()`

```php
// ANTES
$application = Application::query()->create([...]);

// DEPOIS
if (! $requisition->isPublished()) {
    throw RequisitionNotPublishedException::cannotApplyToRequisition($requisition);
}

$application = Application::query()->create([...]);
```

### 4. Guard de UX — `ViewJobRequisition::mount()`

Ordem de precedência importa: **"já candidatado" vem antes de "não publicada"**,
para não tirar do candidato o acesso à própria candidatura de uma vaga que fechou
depois.

```php
// DEPOIS
public function mount(int|string $record): void
{
    $requisitionId = JobPosting::query()->where('slug', $record)->firstOrFail()->job_requisition_id;

    parent::mount($requisitionId);

    /** @var User|null $user */
    $user = auth()->user();

    if ($user?->candidate) {
        $application = $this->record->applicationFrom($user->candidate);

        if ($application) {
            $this->redirect(ApplicationResource::getUrl('view', ['record' => $application]));

            return;
        }
    }

    if (! $this->record->isPublished()) {
        Notification::make()
            ->title(__('panel-app::filament.pages.job_description.job_unavailable'))
            ->warning()
            ->send();

        $this->redirect(JobRequisitionResource::getUrl('index'));

        return;
    }
}
```

### 5. Consistência — `JobApplyIntentController` (#225)

```php
// ANTES
$isAvailable = $posting?->jobRequisition?->status === RequisitionStatusEnum::Published;

// DEPOIS
$isAvailable = $posting?->jobRequisition?->isPublished() ?? false;
```

## Comportamento esperado (BDD)

```gherkin
Cenário: vaga publicada é acessível
  Given uma vaga com status Published
  When acesso /vagas/{slug}
  Then vejo a página da vaga normalmente

Cenário: vaga interna publicada segue acessível por link direto (regressão)
  Given uma vaga com status Published e is_internal_only = true
  When acesso /vagas/{slug} pelo link direto
  Then vejo a página e consigo me candidatar normalmente
    And NÃO sou redirecionado (o guard checa só status, não is_internal_only)

Cenário: link direto de vaga não publicada é bloqueado
  Given uma vaga com status Closed/Draft/Cancelled
    And eu não tenho candidatura para ela
  When acesso /vagas/{slug} pelo link direto
  Then sou redirecionado à listagem de vagas
    And vejo o aviso "Esta vaga não está mais disponível."

Cenário: candidatura própria tem precedência sobre o status (compat)
  Given uma vaga que fechou depois de eu ter me candidatado
  When acesso /vagas/{slug}
  Then sou redirecionado à minha candidatura (não à listagem)

Cenário: ação de domínio barra vaga não publicada
  Given uma vaga com status Closed
  When ApplyToJobRequisitionAction::execute() é chamada diretamente
  Then lança RequisitionNotPublishedException
    And nenhuma Application é criada

Cenário: ação de domínio segue funcionando para vaga publicada (compat)
  Given uma vaga com status Published
  When execute() é chamada
  Then a Application é criada normalmente
```

## Estratégia de testes (por camada)

- **recruitment (unit):** `JobRequisition::isPublished()` retorna `true` para
  `Published` e `false` para os demais casos do enum.
- **applications (feature):** `ApplyToJobRequisitionActionTest`
    - lança `RequisitionNotPublishedException` para vaga não publicada **e** não cria
      `Application` (`assertDatabaseCount`/`assertDatabaseMissing`);
    - segue criando para `Published`.
    - ⚠️ **Corrigir o teste existente**: ele hoje cria a requisition sem fixar status;
      a `JobRequisitionFactory` gera `status` **aleatório** por default
      (`fake()->randomElement(RequisitionStatusEnum::cases())`), então o teste passaria
      a falhar de forma intermitente ao ganhar o guard. Fixar `->available()` (ou
      `['status' => RequisitionStatusEnum::Published]`).
- **panel-app (feature):** `ViewJobRequisition`
    - vaga `Published` → página carrega (`assertSuccessful`);
    - vaga `Published` + `is_internal_only = true` → página carrega (regressão de vaga
      interna: não pode redirecionar);
    - vaga `Closed` sem candidatura → redirect para a listagem + notificação;
    - vaga `Closed` **com** candidatura própria → redirect para a candidatura (compat).

### Armadilhas conhecidas

- **`UserObserver` / candidate stale:** `User::factory()->create()` devolve
  `$user->candidate` como `null` (cache do observer). Nos testes, usar
  `$user->candidate()->update([...])` + `$user->refresh()`. Nunca criar um segundo
  Candidate para o mesmo user.
- **Status aleatório da factory:** sempre fixar o status desejado (`available()` ou
  explícito) no setup — ver acima.
