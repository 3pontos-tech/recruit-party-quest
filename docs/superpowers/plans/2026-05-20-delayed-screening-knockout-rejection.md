# Delayed Screening Knockout Rejection — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Adiar em 1 dia (fixo, `now()->addDay()`) a rejeição automática por _screening knockout_ — quando `JobRequisition::auto_screening_transition` está `true` e o candidato falhou em pergunta eliminatória, a transição para `Rejected` passa a ser feita por um job adiado em vez de acontecer dentro do request da submissão.

**Architecture:** Apenas o ramo de **rejeição** do listener `HandleScreeningKnockoutTransition` muda. O ramo de **avanço** (`InReview`) permanece síncrono. A rejeição é encapsulada num novo `RejectScreeningKnockoutJob` (`ShouldQueue`) que, ao executar +24h depois, revalida defensivamente que `flag` continua `true` e `status` ainda é `New`. Não reavalia respostas — é apenas atraso técnico; o próprio guard de status já garante idempotência quando o candidato re-submete na janela.

**Tech Stack:** Laravel 12 (queue `database`, `ShouldQueue` + `SerializesModels`), Pest 4 (Queue::fake, assertPushed com closure de delay), módulos `applications` e `screening`.

---

## Contexto

A feature de triagem automática (branch `feat/auto-screening-transition`) hoje é 100% síncrona: a submissão do questionário do candidato (`JobApplicationForm::submit()`) chama `EvaluateScreeningResponses::execute()`, que emite `ScreeningEvaluated`; o listener `HandleScreeningKnockoutTransition` reage em-processo e, quando há falha de knockout, executa a transição para `Rejected` imediatamente em `app-modules/applications/src/Listeners/HandleScreeningKnockoutTransition.php:27-39`.

A mudança transfere apenas esse trecho para um job com `delay(now()->addDay())`. O `QUEUE_CONNECTION=database` (driver persistente) garante que o job sobrevive a restart. Como o job revalida no momento da execução, é seguro re-disparar várias vezes (re-submissões) — o primeiro que encontrar `status == New` rejeita; os seguintes viram no-op.

### Diagrama — máquina de estados afetada

```
                              ┌─ acertou tudo ──► [InReview]  (IMEDIATO, inalterado)
                              │
   [New] ──ScreeningEvaluated─┤
                              │                       job +24h, revalida
                              └─ falhou knockout ──► [New] ─────────────────► [Rejected]
                                                        │                         ▲
                                                        │ RH/sistema mudou status │
                                                        └──► (outro) ──no-op──────┘
```

### Diagrama — fluxo de dados

```
 Candidato submete             Listener (síncrono)           Fila database (+24h)
       │                              │                              │
       │  EvaluateScreeningResponses  │                              │
       │ ───────────────────────────► │  event(ScreeningEvaluated)   │
       │                              │                              │
       │                              │  anyKnockoutFailed? sim      │
       │                              │  + status==New               │
       │                              │ ───dispatch->delay(+1d)────► │
       │                              │                              │  worker pega
       │                              │                              │  ► revalida
       │                              │                              │    flag,status
       │                              │                              │  ► current_step
       │                              │                              │    ->handle(Rejected)
       │                              │                              │  ► ApplicationStatusChanged
```

---

## Arquivos afetados

```
app-modules/applications/
├── src/
│   └── Jobs/
│       └── RejectScreeningKnockoutJob.php          ← CRIAR
│   └── Listeners/
│       └── HandleScreeningKnockoutTransition.php   ← MODIFICAR (l. 27-39)
└── tests/
    └── Feature/
        ├── Jobs/
        │   └── RejectScreeningKnockoutJobTest.php  ← CRIAR
        └── Listeners/
            └── HandleScreeningKnockoutTransitionTest.php ← MODIFICAR (teste de rejeição)
```

Memória do projeto (`triagem-automatica-candidatos.md`) é atualizada no fim para refletir que a rejeição agora é adiada.

---

## Task 1: Criar `RejectScreeningKnockoutJob` (TDD)

**Files:**

- Create: `app-modules/applications/src/Jobs/RejectScreeningKnockoutJob.php`
- Test: `app-modules/applications/tests/Feature/Jobs/RejectScreeningKnockoutJobTest.php`

### Comportamento esperado (BDD)

- **Dado** application `status=New`, `requisition.auto_screening_transition=true`, **Quando** o job executa, **Então** status vira `Rejected` com `rejection_reason_category = ScreeningKnockout`, `rejected_by = null`, e uma entrada de stage history é criada.
- **Dado** application `status=InReview` (RH/candidato moveu na janela), **Quando** o job executa, **Então** status permanece `InReview` (no-op).
- **Dado** application `status=Rejected` (já rejeitada por outro motivo), **Quando** o job executa, **Então** o registro não é alterado (no-op).
- **Dado** `requisition.auto_screening_transition=false` (flag desligada durante a janela), **Quando** o job executa, **Então** status permanece `New` (no-op).

### Antes / depois

Antes: rejeição inline em `HandleScreeningKnockoutTransition.php:27-39` chamando `$application->current_step->handle($data)` sincronamente.

Depois: a montagem do `TransitionData` e a chamada de `current_step->handle()` migram para `RejectScreeningKnockoutJob::handle()`, com guards defensivos.

- [ ] **Step 1.1: Criar o arquivo de teste com os 4 cenários**

Conteúdo de `app-modules/applications/tests/Feature/Jobs/RejectScreeningKnockoutJobTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Jobs\RejectScreeningKnockoutJob;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

function newApplicationForRejectJob(JobRequisition $req): Application
{
    $first = $req->stages()->orderBy('display_order')->first();

    return Application::factory()->create([
        'requisition_id' => $req->id,
        'team_id' => $req->team_id,
        'status' => ApplicationStatusEnum::New,
        'current_stage_id' => $first->id,
    ]);
}

it('rejects a New application when the flag is still on', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationForRejectJob($req);

    (new RejectScreeningKnockoutJob($application))->handle();

    $application->refresh();

    expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($application->rejection_reason_category)->toBe(RejectionReasonCategoryEnum::ScreeningKnockout)
        ->and($application->rejected_by)->toBeNull();
});

it('is a no-op when status is no longer New', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationForRejectJob($req);
    $application->update(['status' => ApplicationStatusEnum::InReview]);

    (new RejectScreeningKnockoutJob($application))->handle();

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::InReview);
});

it('is a no-op when the requisition flag has been turned off', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationForRejectJob($req);

    $req->update(['auto_screening_transition' => false]);

    (new RejectScreeningKnockoutJob($application))->handle();

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::New);
});

it('is idempotent: a second job execution does not change anything', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationForRejectJob($req);

    (new RejectScreeningKnockoutJob($application))->handle();
    $rejectedAt = $application->fresh()->rejected_at;

    (new RejectScreeningKnockoutJob($application->fresh()))->handle();

    $application->refresh();

    expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($application->rejected_at?->equalTo($rejectedAt))->toBeTrue();
});
```

- [ ] **Step 1.2: Rodar o teste e verificar que falha**

Run:

```bash
php artisan test --compact --filter=RejectScreeningKnockoutJobTest
```

Expected: falha com `Class "He4rt\Applications\Jobs\RejectScreeningKnockoutJob" not found`.

- [ ] **Step 1.3: Criar o job mínimo que satisfaz os testes**

Conteúdo de `app-modules/applications/src/Jobs/RejectScreeningKnockoutJob.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Jobs;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\TransitionData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class RejectScreeningKnockoutJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Application $application) {}

    public function handle(): void
    {
        $application = $this->application->fresh(['requisition']);

        if ($application === null) {
            return;
        }

        if ($application->requisition?->auto_screening_transition !== true) {
            return;
        }

        if ($application->status !== ApplicationStatusEnum::New) {
            return;
        }

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::ScreeningKnockout->value,
            'rejection_reason_details' => __('screening::messages.knockout_auto_rejected'),
            'notes' => __('screening::messages.knockout_auto_rejected'),
        ]);

        $application->current_step->handle($data);
    }
}
```

- [ ] **Step 1.4: Rodar os testes e verificar que passam**

Run:

```bash
php artisan test --compact --filter=RejectScreeningKnockoutJobTest
```

Expected: 4 passed.

- [ ] **Step 1.5: Rodar o Pint para formatação**

Run:

```bash
vendor/bin/pint --dirty --format agent
```

Expected: arquivos do job e teste formatados sem erros.

- [ ] **Step 1.6: Commit**

```bash
git add app-modules/applications/src/Jobs/RejectScreeningKnockoutJob.php \
        app-modules/applications/tests/Feature/Jobs/RejectScreeningKnockoutJobTest.php
git commit -m "feat(applications): add delayed screening knockout rejection job"
```

---

## Task 2: Adiar a rejeição no listener (TDD)

**Files:**

- Modify: `app-modules/applications/src/Listeners/HandleScreeningKnockoutTransition.php` (linhas 27-39)
- Modify: `app-modules/applications/tests/Feature/Listeners/HandleScreeningKnockoutTransitionTest.php` (teste "rejects the candidate when the flag is on and a knockout failed", l. 34-45)

### Comportamento esperado (BDD)

- **Dado** flag `true` e `anyKnockoutFailed=true` (status `New`), **Quando** o evento é disparado, **Então** o listener despacha `RejectScreeningKnockoutJob` com delay de aproximadamente 1 dia, **e** o status da application **não muda imediatamente**.
- **Dado** flag `true` e `anyKnockoutFailed=false, hadKnockoutCriteria=true`, **Quando** o evento é disparado, **Então** o avanço para `InReview` continua acontecendo síncronamente (inalterado).
- **Dado** flag `false`, **Quando** o evento é disparado, **Então** nenhum job é despachado (inalterado).
- **Dado** flag `true` mas application com status `!= New`, **Quando** o evento é disparado, **Então** nenhum job é despachado.

### Antes / depois

Antes (`HandleScreeningKnockoutTransition.php:27-39`):

```php
if ($event->anyKnockoutFailed) {
    $data = TransitionData::fromArray([
        'to_status' => ApplicationStatusEnum::Rejected,
        'rejection_reason_category' => RejectionReasonCategoryEnum::ScreeningKnockout->value,
        'rejection_reason_details' => __('screening::messages.knockout_auto_rejected'),
        'notes' => __('screening::messages.knockout_auto_rejected'),
    ]);

    $application->current_step->handle($data);

    return;
}
```

Depois:

```php
if ($event->anyKnockoutFailed) {
    RejectScreeningKnockoutJob::dispatch($application)->delay(now()->addDay());

    return;
}
```

- [ ] **Step 2.1: Atualizar o teste existente para esperar job adiado**

Substituir o bloco inteiro do teste "rejects the candidate when the flag is on and a knockout failed" em `app-modules/applications/tests/Feature/Listeners/HandleScreeningKnockoutTransitionTest.php` (linhas 34-45).

Antes:

```php
it('rejects the candidate when the flag is on and a knockout failed', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationFor($req);

    event(new ScreeningEvaluated($application, anyKnockoutFailed: true, hadKnockoutCriteria: true));

    $application->refresh();

    expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($application->rejection_reason_category)->toBe(RejectionReasonCategoryEnum::ScreeningKnockout)
        ->and($application->rejected_by)->toBeNull();
});
```

Depois:

```php
it('queues a delayed rejection job when the flag is on and a knockout failed', function (): void {
    Queue::fake();

    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationFor($req);

    event(new ScreeningEvaluated($application, anyKnockoutFailed: true, hadKnockoutCriteria: true));

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::New);

    Queue::assertPushed(
        RejectScreeningKnockoutJob::class,
        function (RejectScreeningKnockoutJob $job) use ($application): bool {
            return $job->application->is($application)
                && $job->delay instanceof DateTimeInterface
                && $job->delay->getTimestamp() >= now()->addDay()->subMinute()->getTimestamp()
                && $job->delay->getTimestamp() <= now()->addDay()->addMinute()->getTimestamp();
        }
    );
});
```

Adicionar imports no topo do arquivo (logo após os `use` existentes, mantendo ordem alfabética):

```php
use DateTimeInterface;
use He4rt\Applications\Jobs\RejectScreeningKnockoutJob;
use Illuminate\Support\Facades\Queue;
```

- [ ] **Step 2.2: Rodar o teste e verificar que falha**

Run:

```bash
php artisan test --compact --filter="HandleScreeningKnockoutTransitionTest"
```

Expected: o teste novo falha (o listener ainda chama `current_step->handle()` síncrono, então `Queue::assertPushed` falha com "The expected [RejectScreeningKnockoutJob] job was not dispatched.").

- [ ] **Step 2.3: Modificar o listener para despachar o job adiado**

Em `app-modules/applications/src/Listeners/HandleScreeningKnockoutTransition.php`, substituir o ramo de rejeição.

Antes (linhas 27-39):

```php
if ($event->anyKnockoutFailed) {
    $data = TransitionData::fromArray([
        'to_status' => ApplicationStatusEnum::Rejected,
        'rejection_reason_category' => RejectionReasonCategoryEnum::ScreeningKnockout->value,
        // TODO: verificar qual dos dois campos ficam visiveis para o usuario final e editar para algo mais agradavel.
        'rejection_reason_details' => __('screening::messages.knockout_auto_rejected'),
        'notes' => __('screening::messages.knockout_auto_rejected'),
    ]);

    $application->current_step->handle($data);

    return;
}
```

Depois:

```php
if ($event->anyKnockoutFailed) {
    RejectScreeningKnockoutJob::dispatch($application)->delay(now()->addDay());

    return;
}
```

Atualizar os imports no topo do arquivo: remover `He4rt\Applications\Enums\RejectionReasonCategoryEnum` e `He4rt\Applications\Services\Transitions\TransitionData` se não forem mais usados pelo ramo de avanço (eles **continuam** sendo usados pelo ramo de `InReview` — apenas `TransitionData` é usado lá; `RejectionReasonCategoryEnum` pode ser removido). Adicionar:

```php
use He4rt\Applications\Jobs\RejectScreeningKnockoutJob;
```

Estado final completo de `app-modules/applications/src/Listeners/HandleScreeningKnockoutTransition.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Listeners;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Jobs\RejectScreeningKnockoutJob;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Screening\Events\ScreeningEvaluated;

final class HandleScreeningKnockoutTransition
{
    public function handle(ScreeningEvaluated $event): void
    {
        $application = $event->application;
        $application->loadMissing('requisition');

        if ($application->requisition?->auto_screening_transition !== true) {
            return;
        }

        if ($application->status !== ApplicationStatusEnum::New) {
            return;
        }

        if ($event->anyKnockoutFailed) {
            RejectScreeningKnockoutJob::dispatch($application)->delay(now()->addDay());

            return;
        }

        if ($event->hadKnockoutCriteria) {
            $data = TransitionData::fromArray([
                'to_status' => ApplicationStatusEnum::InReview,
                'notes' => __('screening::messages.knockout_auto_advanced'),
            ]);

            $application->current_step->handle($data);
        }
    }
}
```

- [ ] **Step 2.4: Rodar os testes do listener e do job**

Run:

```bash
php artisan test --compact --filter="HandleScreeningKnockoutTransitionTest|RejectScreeningKnockoutJobTest"
```

Expected: todos os testes passam (5 do listener + 4 do job = 9).

- [ ] **Step 2.5: Rodar a suíte completa de applications e screening para garantir que nada quebrou**

Run:

```bash
php artisan test --compact app-modules/applications app-modules/screening
```

Expected: tudo verde. Atenção a:

- `AutomaticActorTransitionTest` (não toca em jobs — deve continuar passando)
- `EvaluateScreeningResponsesTest` (não toca em jobs — deve continuar passando)
- `JobApplicationFormKnockoutTest` (livewire submetendo o formulário — agora o "rejected" sob a flag deveria virar "pendente", mas se houver assertion direta de status `Rejected`, ela vai quebrar)

Se algum teste do `JobApplicationFormKnockoutTest` quebrar com expectativa de `status = Rejected` imediato, ajustar para `status = New` + `Queue::assertPushed(RejectScreeningKnockoutJob::class)` (mesmo padrão do listener). Verificar antes de quebrar; pode ser que esses testes já validem indiretamente sem checar status.

- [ ] **Step 2.6: Rodar o Pint**

Run:

```bash
vendor/bin/pint --dirty --format agent
```

Expected: sem erros.

- [ ] **Step 2.7: Commit**

```bash
git add app-modules/applications/src/Listeners/HandleScreeningKnockoutTransition.php \
        app-modules/applications/tests/Feature/Listeners/HandleScreeningKnockoutTransitionTest.php
git commit -m "feat(applications): defer screening knockout rejection by 1 day"
```

Se algum teste de Livewire foi ajustado no Step 2.5, incluir aquele(s) arquivo(s) no `git add` deste commit.

---

## Task 3: Verificação final + atualização de memória

- [ ] **Step 3.1: Rodar a suíte inteira de teste rapidamente**

Run:

```bash
php artisan test --compact
```

Expected: tudo verde. Se algo fora dos módulos `applications`/`screening` quebrou, é regressão inesperada — investigar antes de prosseguir.

- [ ] **Step 3.2: Atualizar a memória do projeto**

Em `/home/clinton/.claude-pessoal/projects/-home-clinton-www-3pontos-recruit-party-quest/memory/triagem-automatica-candidatos.md`, adicionar ao final do corpo, antes de `[[filament-dynamic-schema-stateful-fields]]`:

```
**Atraso de 24h na rejeição (branch feat/auto-screening-transition, 2026-05-20):** A rejeição automática deixou de ser síncrona — `HandleScreeningKnockoutTransition` agora despacha `RejectScreeningKnockoutJob` com `delay(now()->addDay())`. Apenas "atraso técnico": o job revalida `flag==true` e `status==New` no momento da execução, **não reavalia respostas**. O guard de status protege re-submissões na janela (jobs adiados redundantes viram no-op). Ramo de avanço (InReview) permanece síncrono. Delay é fixo no código (decisão explícita). Plano: `docs/superpowers/plans/2026-05-20-delayed-screening-knockout-rejection.md`.
```

- [ ] **Step 3.3: Commit final**

```bash
git add docs/superpowers/plans/2026-05-20-delayed-screening-knockout-rejection.md
git commit -m "docs(screening): add plan for delayed knockout rejection"
```

(A memória mora fora do repositório — não entra no commit.)

---

## Self-review checklist

- ✅ Cobre o requisito único do spec (atrasar rejeição em ≥1 dia, mantendo avanço síncrono e delay fixo).
- ✅ Sem placeholders, sem "TBD", todos os snippets completos.
- ✅ Nomes consistentes: `RejectScreeningKnockoutJob` em todas as 4 ocorrências (job, teste do job, listener atualizado, teste do listener).
- ✅ Assinatura do job (`public Application $application`) usada igual em dispatch (`::dispatch($application)`) e teste (`new RejectScreeningKnockoutJob($application)`).
- ✅ Cada task termina com commit; commits separados por responsabilidade (job, listener+ajuste de teste, docs).
