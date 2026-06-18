# Design — Notificar candidato ao receber a candidatura (confirmação)

- **Issue:** #189 (sub-issue do épico #191 — política de comunicação com o candidato)
- **Branch:** `feat/notify-candidate-application-received-189`
- **Data:** 2026-06-17
- **Status:** aprovado para implementação

Além da notificação da #189, esta branch carrega três melhorias de arquitetura no fluxo de submissão
de candidatura (decididas durante o brainstorm — ver §2): **consolidar a criação** num único ponto,
**desacoplar a persistência/avaliação de screening** do componente Livewire via evento, e **mover o
`JobApplicationForm`** do módulo `screening` para o `panel-app` (módulo correto para UI candidato-facing).

---

## 1. Contexto

Hoje **nenhuma** transição da candidatura notifica o candidato — todos os `notify()` das transições
(`AbstractApplicationTransition` e subclasses) estão vazios. O RH decidiu (respostas de 01/06/2026) que,
**por ora**, o único momento que gera aviso ao candidato é a **confirmação de candidatura recebida**
(decisão 3.1), entregue em **dois canais** — painel in-app (`database`) **e** e-mail (decisão 3.2).
Reprovações **nunca** são notificadas (decisão 3.3), nem as manuais nem a auto-reprovação por knockout.

Esta feature implementa esse único momento, construindo o "encanamento" mínimo de notificação ao candidato
(evento de domínio → listener → notificação multi-canal enfileirada) que servirá de base a momentos futuros
quando o RH liberar (ex.: #198 proposta).

### Correções de premissas da issue (validadas contra o código atual)

| Premissa da issue                         | Realidade no código                                                                                                                                                            | Efeito no design                                            |
| ----------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ----------------------------------------------------------- |
| "Canal `database` nunca foi usado"        | **Falso.** `MentionedInCommentNotification` já usa `['mail','database']` com `toDatabase()` via `FilamentNotification::make()->...->getDatabaseMessage()`.                     | **Reusamos** o padrão.                                      |
| "Candidato é `Notifiable`"                | Quem é `Notifiable` é o `User`, não o `Candidate`.                                                                                                                             | Alvo = `$application->candidate->user`.                     |
| "Disparar via `ApplicationStatusChanged`" | Esse evento só dispara com `fromStatus !== toStatus`. A candidatura **nasce** em `New` — nunca dispara na criação. `NewTransition` modela a **saída** de `New`, não a entrada. | Gatilho = evento de domínio próprio `ApplicationSubmitted`. |

---

## 2. Decisões de design

1. **Gatilho da notificação = evento de domínio `ApplicationSubmitted`**, disparado apenas pelos fluxos reais
   de candidatura. Criações não interativas (factory/seeder/import) **não** notificam.
2. **Consolidar a criação em um único ponto.** `ApplyToJobRequisitionAction` passa a montar o `ApplicationDTO`
   e **delegar** a `StoreApplication`, que vira o **chokepoint único** de criação e o único lugar que dispara
   `ApplicationSubmitted`. Assinaturas públicas inalteradas.
3. **Entrega da notificação = uma única `Notification` multi-canal** (`via = ['mail','database']`),
   `implements ShouldQueue`, espelhando `MentionedInCommentNotification`.
4. **Desacoplar a persistência/avaliação de screening do Livewire via evento.** A criação permanece chamada
   direta (consumidor → provedor; o form precisa do `Application` síncrono para a FK e o redirect). Mas a
   **persistência + avaliação** das respostas saem do componente e passam a ser disparadas por um evento do
   módulo `screening` (`ScreeningResponsesSubmitted`), tratado por um listener. O `EvaluateScreeningResponses`
   já lê as respostas do banco, então roda desacoplado do estado do Livewire.
5. **Mover o `JobApplicationForm` `screening` → `panel-app`.** É UI candidato-facing e deve viver onde mora todo
   o resto da UI do candidato (e o outro fluxo de candidatura, `ViewJobRequisition::applyDirectly()`).
   `recruitment` seria o contexto errado (lado empregador, sem UI); `screening` o hospeda só por acaso histórico
   (#9). Os **widgets de pergunta** (`SourceQuestion`, `FileUploadQuestion`) **ficam** no `screening` e seguem
   sendo renderizados dentro do form via nesting Livewire cross-module. O desacoplamento (decisão 4) é o que
   torna a mudança de baixo risco: pós-evento, o form quase não depende mais do `screening`.

---

## 3. Arquitetura / fluxo

```
                      Candidato clica "Candidatar-se"  (job-description.blade.php:121)
                                    │
                   ┌────────────────┴─────────────────┐
       vaga SEM triagem                        vaga COM triagem
                   │                                  │
        $wire.applyDirectly()              modal → <livewire:panel-app.job-application-form>
                   │                                  │            (movido de screening)
   ApplyToJobRequisitionAction              JobApplicationForm::submit()  (FINO, em panel-app)
     ::execute() monta DTO ─────┐                     │  • cria via StoreApplication
                                ▼                      │  • monta DTOs das respostas
                       ┌────────────────────────┐     │  • event(ScreeningResponsesSubmitted)──┐
                       │ StoreApplication        │◄────┘                                        │
                       │  ::execute(DTO)         │ ← CHOKEPOINT ÚNICO de criação                │
                       │  • Application::create  │                                              │
                       │  • update(stage)        │                                              │
                       │  • event(ApplicationSubmitted)─┐                                       │
                       └────────────────────────┘       │                                       │
                                                         ▼                                       ▼
                       SendApplicationReceivedNotification          StoreAndEvaluateScreeningResponses
                         (applications, auto-discovery)               (screening, auto-discovery)
                         guard candidate?->user                       • StoreScreeningResponse (salva)
                         $user->notify(ApplicationReceived)           • try { EvaluateScreeningResponses }
                                  │ (ShouldQueue)                              │   (best-effort + log)
                                  ▼                                            ▼
                     ┌──────── FILA (worker) ────────┐            event(ScreeningEvaluated)
                     │ via = ['mail','database']      │                    │
                     │  toMail()  → ApplicationRecMail │                    ▼
                     │  toDatabase() → 🔔 panel-app    │       HandleScreeningKnockoutTransition
                     └────────────────────────────────┘          (applications — auto-transição)
```

**Robustez da notificação:** `ApplicationSubmitted` é disparado fora de transação e a `Notification` é
`ShouldQueue` — falha de e-mail não toca a candidatura. **Robustez da avaliação:** o listener salva as
respostas (síncrono, no request — igual a hoje) e roda a avaliação em `try/catch` com log.

---

## 4. Componentes

### Módulo `applications` (`He4rt\Applications`)

| Peça                | Caminho                                                 | Papel                                                                |
| ------------------- | ------------------------------------------------------- | -------------------------------------------------------------------- |
| Evento (novo)       | `src/Events/ApplicationSubmitted.php`                   | carrega `public Application $application`.                           |
| Listener (novo)     | `src/Listeners/SendApplicationReceivedNotification.php` | `handle(ApplicationSubmitted)`; auto-discovery.                      |
| Notification (novo) | `src/Notifications/ApplicationReceivedNotification.php` | `ShouldQueue`; `via=['mail','database']`.                            |
| Mailable (novo)     | `src/Mail/ApplicationReceivedMail.php`                  | `ShouldQueue`; markdown `applications::emails.application-received`. |
| Template (novo)     | `resources/views/emails/application-received.blade.php` | corpo do e-mail.                                                     |
| Alterado            | `src/Services/Applications/StoreApplication.php`        | dispara `ApplicationSubmitted` após criar.                           |
| Alterado            | `src/Actions/ApplyToJobRequisitionAction.php`           | delega a `StoreApplication`.                                         |
| i18n (novo)         | `lang/{en,pt_BR}/filament.php`                          | chaves de §7.                                                        |

### Módulo `screening` (`He4rt\Screening`)

| Peça            | Caminho                                                     | Papel                                                                         |
| --------------- | ----------------------------------------------------------- | ----------------------------------------------------------------------------- |
| Evento (novo)   | `src/Events/ScreeningResponsesSubmitted.php`                | `Application $application` + `ScreeningResponseCollection $responses`.        |
| Listener (novo) | `src/Listeners/StoreAndEvaluateScreeningResponses.php`      | persiste + avalia (best-effort); auto-discovery.                              |
| Removido        | `src/Livewire/JobApplicationForm.php`                       | movido para `panel-app` (ver §4 panel-app).                                   |
| Alterado        | `src/ScreeningServiceProvider.php`                          | remove o registro `Livewire::component('screening.job-application-form', …)`. |
| Mantido         | `src/Livewire/SourceQuestion.php`, `FileUploadQuestion.php` | widgets de pergunta — permanecem.                                             |

### Módulo `panel-app` (`He4rt\App`)

| Peça     | Caminho                                                                | Papel                                                                                                        |
| -------- | ---------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| Movido   | `src/Livewire/JobApplicationForm.php` (era `He4rt\Screening\Livewire`) | agora `He4rt\App\Livewire\JobApplicationForm`; `submit()` fino.                                              |
| Movido   | `resources/views/livewire/job-application-form.blade.php`              | view `panel-app::livewire.job-application-form` (mantém refs a `screening.source-question` e `screening::`). |
| Alterado | `src/PanelAppServiceProvider.php`                                      | registra `Livewire::component('panel-app.job-application-form', JobApplicationForm::class)`.                 |
| Alterado | `resources/views/components/jobs/job-description.blade.php:229`        | `<livewire:screening.job-application-form>` → `<livewire:panel-app.job-application-form>`.                   |

Reaproveita: `panel-app` já tem `DatabaseNotifications` ativo, view namespace `panel-app::` e registro de
Livewire; `Candidate::user()` existe; `EvaluateScreeningResponses` lê as respostas do banco.

---

## 5. Comportamento esperado (BDD)

**Notificação** — **Dado** um candidato que se candidata (qualquer fluxo), **Quando** a `Application` é criada,
**Então** dispara `ApplicationSubmitted` e o `User` recebe `ApplicationReceivedNotification` no painel (card
com botão "Ver candidatura") **e** por e-mail (assunto + cargo + link), **enfileirada**.

**Screening** — **Dado** uma candidatura com triagem, **Quando** o form é submetido, **Então** a candidatura é
criada, `ScreeningResponsesSubmitted` dispara, o listener persiste as respostas e avalia (→ `ScreeningEvaluated`
→ auto-transição quando aplicável).

**Tolerância a falha** — falha de e-mail no worker **não** reverte a candidatura; exceção na avaliação mantém
as respostas salvas, loga e **não** quebra a submissão.

**Não-regressão / RH** — `Application::factory()->create()` **não** notifica; nenhuma reprovação/transição
posterior notifica (3.3); os dois fluxos de candidatura continuam criando e redirecionando normalmente
(form agora sob o alias `panel-app.job-application-form`).

**Borda** — `candidate->user` nulo: listener de notificação sai sem erro (guarda).

---

## 6. Antes / depois (código)

### 6.1 `StoreApplication` — chokepoint que dispara o evento

```php
$application = Application::query()->create([/* ... */]);
$application->update(['current_stage_id' => $application->first_stage?->getKey()]);
event(new ApplicationSubmitted($application));   // novo
return $application;
```

### 6.2 `ApplyToJobRequisitionAction` — delega a `StoreApplication`

```php
public function __construct(private StoreApplication $storeApplication) {}

public function execute(JobRequisition $requisition, Candidate $candidate, CandidateSourceEnum $source = CandidateSourceEnum::CareerPage): Application
{
    return $this->storeApplication->execute(new ApplicationDTO(
        requisitionId: $requisition->id, candidateId: $candidate->id, teamId: $requisition->team_id,
        status: ApplicationStatusEnum::New, source: $source,
    ));
} // hasApplied() inalterado
```

### 6.3 `JobApplicationForm::submit()` — fino, no `panel-app`

```php
// namespace He4rt\App\Livewire;  (movido de He4rt\Screening\Livewire)
public function submit(): Redirector|RedirectResponse
{
    $this->validate();

    if (! $this->application instanceof Application) {
        $candidate = auth()->user()->candidate;
        $source = $this->source instanceof CandidateSourceEnum ? $this->source : CandidateSourceEnum::from($this->source);
        $this->application = resolve(StoreApplication::class)->execute(new ApplicationDTO(
            requisitionId: $this->requisition->getKey(), candidateId: $candidate->getKey(),
            teamId: $this->requisition->team_id, status: ApplicationStatusEnum::New, source: $source,
        ));
    }

    event(new ScreeningResponsesSubmitted($this->application, $this->buildResponseCollection()));

    Notification::make()->title(__('screening::messages.application_submitted'))->success()->send();

    return redirect(route('filament.app.resources.applications.view', ['record' => $this->application->getKey()]));
}
// render() retorna view('panel-app::livewire.job-application-form', …)
// buildResponseCollection() extrai o loop atual que monta os ScreeningResponseDTO.
```

### 6.4 `StoreAndEvaluateScreeningResponses` (novo listener — screening)

```php
public function handle(ScreeningResponsesSubmitted $event): void
{
    $this->store->execute($event->responses);
    try {
        $this->evaluate->execute($event->application);
    } catch (Throwable $e) {
        Log::error('Screening evaluation failed after application submission', ['application_id' => $event->application->getKey(), 'exception' => $e]);
    }
}
```

### 6.5 `ApplicationReceivedNotification` (novo — espelha `MentionedInCommentNotification`)

```php
public function via(object $notifiable): array { return ['mail', 'database']; }

public function toMail(object $notifiable): ApplicationReceivedMail
{ return new ApplicationReceivedMail($this->application)->to($this->application->candidate->user->email); }

public function toDatabase(object $notifiable): array
{
    return FilamentNotification::make()
        ->title(__('applications::filament.notifications.application_received.title'))
        ->body(__('applications::filament.notifications.application_received.body', ['job' => $this->application->requisition->post->title]))
        ->icon(Heroicon::OutlinedCheckCircle)
        ->actions([Action::make('view')->button()
            ->url(ApplicationResource::getUrl('view', ['record' => $this->application->getKey()], panel: 'app'))
            ->label(__('applications::filament.notifications.application_received.view_button'))->markAsRead()])
        ->getDatabaseMessage();
}
```

> `ApplicationResource` do `panel-app`; panel id `'app'` (`FilamentPanel::App`) — confirmado.

### 6.6 `SendApplicationReceivedNotification` (novo listener — applications)

```php
public function handle(ApplicationSubmitted $event): void
{
    $user = $event->application->candidate?->user;
    if ($user === null) { return; }
    $user->notify(new ApplicationReceivedNotification($event->application));
}
```

---

## 7. i18n (en + pt_BR, em `applications::filament`)

```
emails.application_received.subject              assunto (com :job)
emails.application_received.*                     corpo (markdown)
notifications.application_received.title
notifications.application_received.body           (com :job)
notifications.application_received.view_button
```

Copy sugerida: **title** "Candidatura recebida" / "Application received"; **body** "Recebemos sua candidatura
para :job." / "We received your application for :job."; **subject** "Recebemos sua candidatura — :job".

---

## 8. Testes (Pest — feature)

**Notificação (#189)**

1. `ApplyToJobRequisitionAction::execute()` → `Notification::fake()` + `assertSentTo($user, ApplicationReceivedNotification::class)`.
2. Fluxo de triagem (form via `livewire('panel-app.job-application-form')` ou `JobApplicationForm::class`) → mesma asserção.
3. Notificação `ShouldQueue`; `via()` = `['mail','database']`.
4. Conteúdo contém o **cargo** e o **link** para a `ViewApplication` do `panel-app`.
5. **Negativo:** `Application::factory()->create()` **não** dispara o evento / **não** notifica.
6. i18n: chaves novas em `en` e `pt_BR`.

**Screening + move** 7. `submit()` dispara `ScreeningResponsesSubmitted`. 8. `StoreAndEvaluateScreeningResponses` persiste as respostas e dispara `ScreeningEvaluated`. 9. **Best-effort:** avaliação que lança mantém respostas salvas, loga e não propaga. 10. **Não-regressão:** auto-transição por knockout segue ponta a ponta; o form responde sob o novo alias
`panel-app.job-application-form` (atualizar testes que usavam `He4rt\Screening\Livewire\JobApplicationForm`
ou `screening.job-application-form`).

---

## 9. Fora de escopo

- Notificar outros momentos (avanço, proposta, contratação) — depende do RH (#198).
- Notificar reprovação/desistência (3.3).
- Preferências de canal por candidato / opt-out de e-mail.
- Mesclar/deletar uma das classes de criação (delegamos, não removemos).
- Corrigir re-submit do form gerando respostas duplicadas (pré-existente).
- Mover os widgets `SourceQuestion`/`FileUploadQuestion` (permanecem no `screening`).
