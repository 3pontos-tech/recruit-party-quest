# Design — Notificar candidato ao receber a candidatura (confirmação)

- **Issue:** #189 (sub-issue do épico #191 — política de comunicação com o candidato)
- **Branch:** `feat/notify-candidate-application-received-189`
- **Data:** 2026-06-17
- **Status:** aprovado para implementação

Além da notificação da #189, esta branch carrega duas melhorias de arquitetura no fluxo de
submissão de candidatura (decididas durante o brainstorm — ver §2): **consolidar a criação** num
único ponto e **desacoplar a persistência/avaliação de screening** do componente Livewire via evento.

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

A issue foi escrita antes do estado atual do projeto. Validação:

| Premissa da issue                         | Realidade no código                                                                                                                                                                                                                                                                       | Efeito no design                                                                            |
| ----------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------- |
| "Canal `database` nunca foi usado"        | **Falso.** `He4rt\Organization\Notifications\MentionedInCommentNotification` já usa `['mail','database']` com `toDatabase()` via `FilamentNotification::make()->...->getDatabaseMessage()`.                                                                                               | **Reusamos** esse padrão; não estreamos nada.                                               |
| "Candidato é `Notifiable`"                | Quem é `Notifiable` é o `User` (`He4rt\Users\User`), não o `Candidate`.                                                                                                                                                                                                                   | Alvo da notificação é `$application->candidate->user`.                                      |
| "Disparar via `ApplicationStatusChanged`" | `ApplicationStatusChanged` só dispara quando `fromStatus !== toStatus` (`AbstractApplicationTransition.php:73`). A candidatura **nasce** em `New` — esse evento **nunca** dispara na criação. `NewTransition` modela a **saída** de `New` (avanço/reprovação/desistência), não a entrada. | O gatilho é um **evento de domínio próprio** (`ApplicationSubmitted`), não a state machine. |

---

## 2. Decisões de design

1. **Gatilho da notificação = evento de domínio `ApplicationSubmitted`**, disparado apenas pelos fluxos reais
   de candidatura. Criações não interativas (factory/seeder/import) **não** notificam.
2. **Consolidar a criação em um único ponto.** Há duas classes que duplicam a criação da `Application`:
    - `ApplyToJobRequisitionAction::execute(JobRequisition, Candidate, source)` — apply direto (vaga **sem**
      triagem), em `panel-app` `ViewJobRequisition::applyDirectly()`.
    - `StoreApplication::execute(ApplicationDTO)` — formulário com triagem (`screening` `JobApplicationForm`).

    `ApplyToJobRequisitionAction` passa a **montar o `ApplicationDTO` e delegar** a `StoreApplication`, que vira
    o **chokepoint único** de criação e o único lugar que dispara `ApplicationSubmitted`. Assinaturas públicas
    inalteradas (sem regressão em `ViewJobRequisition` nem nos testes).

3. **Entrega da notificação = uma única `Notification` multi-canal** (`via = ['mail','database']`),
   `implements ShouldQueue`, espelhando `MentionedInCommentNotification`.
4. **Desacoplar a persistência/avaliação de screening do Livewire via evento.** Princípio: evento serve à
   direção **provedor → consumidor**. Hoje o `JobApplicationForm` orquestra inline 3 passos (criar candidatura,
   salvar respostas, avaliar). A criação permanece chamada direta (consumidor `screening` → provedor
   `applications`, dependência natural; o form precisa do `Application` **síncrono** para a FK das respostas e
   o redirect). Mas a **persistência + avaliação** das respostas saem do componente e passam a ser disparadas
   por um evento do próprio módulo `screening` (`ScreeningResponsesSubmitted`), tratado por um listener.
   O `EvaluateScreeningResponses` já lê as respostas do banco, então roda desacoplado do estado do Livewire.

---

## 3. Arquitetura / fluxo

```
                      Candidato clica "Candidatar-se"  (job-description.blade.php:121)
                                    │
                   ┌────────────────┴─────────────────┐
       vaga SEM triagem                        vaga COM triagem
                   │                                  │
        $wire.applyDirectly()              modal → <livewire:screening.job-application-form>
                   │                                  │
   ApplyToJobRequisitionAction              JobApplicationForm::submit()  (FINO)
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

**Robustez da notificação:** `ApplicationSubmitted` é disparado **fora de transação** e a `Notification` é
`ShouldQueue` — e-mail/card rodam no worker, desacoplados do request. Falha de e-mail não toca a candidatura.

**Robustez da avaliação:** o listener de screening salva as respostas (síncrono, dentro do request — igual a
hoje) e roda a avaliação em `try/catch` com log — preservando a garantia atual de que falha de avaliação
**não** quebra a submissão do candidato.

---

## 4. Componentes

### Módulo `applications` (namespace `He4rt\Applications`)

| Peça         | Caminho                                                 | Papel                                                                                            |
| ------------ | ------------------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| Evento       | `src/Events/ApplicationSubmitted.php`                   | `final`, carrega `public Application $application`.                                              |
| Listener     | `src/Listeners/SendApplicationReceivedNotification.php` | `handle(ApplicationSubmitted $event)`. Auto-discovery (`EventsPlugin` do `internachi/modular`).  |
| Notification | `src/Notifications/ApplicationReceivedNotification.php` | `extends Notification implements ShouldQueue`; `via = ['mail','database']`.                      |
| Mailable     | `src/Mail/ApplicationReceivedMail.php`                  | `extends Mailable implements ShouldQueue`; markdown `applications::emails.application-received`. |
| Template     | `resources/views/emails/application-received.blade.php` | Corpo do e-mail (saudação + cargo + CTA).                                                        |
| Alterado     | `src/Services/Applications/StoreApplication.php`        | Dispara `ApplicationSubmitted` após criar.                                                       |
| Alterado     | `src/Actions/ApplyToJobRequisitionAction.php`           | Delega a `StoreApplication` (injeção no construtor).                                             |
| i18n         | `lang/{en,pt_BR}/filament.php`                          | chaves novas (ver §7).                                                                           |

### Módulo `screening` (namespace `He4rt\Screening`)

| Peça     | Caminho                                                | Papel                                                                                                 |
| -------- | ------------------------------------------------------ | ----------------------------------------------------------------------------------------------------- |
| Evento   | `src/Events/ScreeningResponsesSubmitted.php`           | `final`, carrega `Application $application` + `ScreeningResponseCollection $responses`.               |
| Listener | `src/Listeners/StoreAndEvaluateScreeningResponses.php` | `handle(...)`: `StoreScreeningResponse` + `EvaluateScreeningResponses` (best-effort). Auto-discovery. |
| Alterado | `src/Livewire/JobApplicationForm.php`                  | `submit()` fica fino: cria, monta DTOs, dispara `ScreeningResponsesSubmitted`.                        |

Reaproveita: `ApplicationsServiceProvider` já registra `applications::` (views+traduções); `panel-app` já tem
`DatabaseNotifications` ativo; `Candidate::user()` (BelongsTo) já existe; `EvaluateScreeningResponses` já lê
as respostas do banco.

---

## 5. Comportamento esperado (BDD)

**Happy path — notificação**

- **Dado** um candidato que se candidata (qualquer um dos dois fluxos), **Quando** a `Application` é criada,
  **Então** dispara `ApplicationSubmitted` e o `User` do candidato recebe `ApplicationReceivedNotification` no
  painel (card com botão "Ver candidatura") **e** por e-mail (assunto + cargo + link), **enfileirada**.

**Happy path — screening**

- **Dado** uma candidatura com triagem, **Quando** o form é submetido, **Então** a candidatura é criada,
  `ScreeningResponsesSubmitted` é disparado, o listener persiste as respostas e roda a avaliação
  (disparando `ScreeningEvaluated` → auto-transição quando aplicável).

**Tolerância a falha**

- **Dado** que o envio de e-mail falha no worker, **Então** a candidatura **permanece** criada.
- **Dado** que a avaliação de screening lança exceção, **Quando** o listener roda, **Então** as respostas
  **continuam** salvas, o erro é logado e a submissão do candidato **não** quebra.

**Não-regressão / respeito ao RH**

- **Dado** uma `Application` criada por seeder/factory/import, **Então** **nenhuma** notificação é enviada
  (não há hook no model `created`).
- **Dado** qualquer reprovação/transição posterior, **Então** **nenhum** aviso é enviado ao candidato (3.3).

**Borda**

- **Dado** um `Application` cujo `candidate->user` é nulo, **Quando** o listener de notificação roda, **Então**
  sai sem erro (guarda defensiva).

---

## 6. Antes / depois (código)

### 6.1 `StoreApplication` — chokepoint que dispara o evento

```php
// depois
public function execute(ApplicationDTO $dto): Application
{
    $application = Application::query()->create([/* ... */]);
    $application->update(['current_stage_id' => $application->first_stage?->getKey()]);

    event(new ApplicationSubmitted($application));

    return $application;
}
```

### 6.2 `ApplyToJobRequisitionAction` — delega a `StoreApplication`

```php
// depois — sem lógica de criação própria
public function __construct(private StoreApplication $storeApplication) {}

public function execute(
    JobRequisition $requisition,
    Candidate $candidate,
    CandidateSourceEnum $source = CandidateSourceEnum::CareerPage,
): Application {
    return $this->storeApplication->execute(new ApplicationDTO(
        requisitionId: $requisition->id,
        candidateId: $candidate->id,
        teamId: $requisition->team_id,
        status: ApplicationStatusEnum::New,
        source: $source,
    ));
}
// hasApplied() inalterado.
```

### 6.3 `JobApplicationForm::submit()` — fino, dispara evento do screening

```php
// antes: criava + StoreScreeningResponse + try{EvaluateScreeningResponses} inline.
// depois:
public function submit(): Redirector|RedirectResponse
{
    $this->validate();

    if (! $this->application instanceof Application) {
        $candidate = auth()->user()->candidate;
        $source = $this->source instanceof CandidateSourceEnum
            ? $this->source
            : CandidateSourceEnum::from($this->source);

        $this->application = resolve(StoreApplication::class)->execute(new ApplicationDTO(
            requisitionId: $this->requisition->getKey(),
            candidateId: $candidate->getKey(),
            teamId: $this->requisition->team_id,
            status: ApplicationStatusEnum::New,
            source: $source,
        ));
    }

    event(new ScreeningResponsesSubmitted($this->application, $this->buildResponseCollection()));

    Notification::make()->title(__('screening::messages.application_submitted'))->success()->send();

    return redirect(route('filament.app.resources.applications.view', ['record' => $this->application->getKey()]));
}
```

> `buildResponseCollection()` extrai o loop atual que monta os `ScreeningResponseDTO` a partir de `$this->responses`.

### 6.4 `StoreAndEvaluateScreeningResponses` (novo listener — screening)

```php
final class StoreAndEvaluateScreeningResponses
{
    public function __construct(
        private StoreScreeningResponse $store,
        private EvaluateScreeningResponses $evaluate,
    ) {}

    public function handle(ScreeningResponsesSubmitted $event): void
    {
        $this->store->execute($event->responses);

        // Best-effort: falha de avaliação nunca pode quebrar a submissão do candidato.
        try {
            $this->evaluate->execute($event->application);
        } catch (Throwable $throwable) {
            Log::error('Screening evaluation failed after application submission', [
                'application_id' => $event->application->getKey(),
                'exception' => $throwable,
            ]);
        }
    }
}
```

### 6.5 `ApplicationReceivedNotification` (novo — espelha `MentionedInCommentNotification`)

```php
final class ApplicationReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): ApplicationReceivedMail
    {
        return new ApplicationReceivedMail($this->application)
            ->to($this->application->candidate->user->email);
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('applications::filament.notifications.application_received.title'))
            ->body(__('applications::filament.notifications.application_received.body', [
                'job' => $this->application->requisition->post->title,
            ]))
            ->icon(Heroicon::OutlinedCheckCircle)
            ->actions([
                Action::make('view')->button()
                    ->url(ApplicationResource::getUrl('view', ['record' => $this->application->getKey()], panel: 'app'))
                    ->label(__('applications::filament.notifications.application_received.view_button'))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
```

> `ApplicationResource` é o do `panel-app`; panel id `'app'` (`FilamentPanel::App`) — confirmado.

### 6.6 `SendApplicationReceivedNotification` (novo listener — applications)

```php
final class SendApplicationReceivedNotification
{
    public function handle(ApplicationSubmitted $event): void
    {
        $user = $event->application->candidate?->user;

        if ($user === null) {
            return;
        }

        $user->notify(new ApplicationReceivedNotification($event->application));
    }
}
```

---

## 7. i18n (en + pt_BR, em `applications::filament`)

```
emails.application_received.subject              assunto do e-mail (com :job)
emails.application_received.*                    linhas do corpo (markdown)
notifications.application_received.title
notifications.application_received.body          (com :job)
notifications.application_received.view_button
```

Copy sugerida (tom de confirmação cordial; redigir na implementação):

- **title:** "Candidatura recebida" / "Application received"
- **body:** "Recebemos sua candidatura para :job." / "We received your application for :job."
- **subject:** "Recebemos sua candidatura — :job" / "We received your application — :job"

---

## 8. Testes (Pest — feature)

**Notificação (#189)**

1. `ApplyToJobRequisitionAction::execute()` → `Notification::fake()` + `assertSentTo($user, ApplicationReceivedNotification::class)`.
2. Fluxo de triagem (`StoreApplication` / `JobApplicationForm` via `livewire()`) → mesma asserção.
3. Notificação implementa `ShouldQueue`; `via()` retorna `['mail','database']`.
4. Conteúdo contém o **cargo** (`requisition->post->title`) e o **link** para a `ViewApplication` do `panel-app`.
5. **Negativo (codifica a decisão de produto):** `Application::factory()->create()` **não** dispara o evento / **não** notifica.
6. i18n: chaves novas existem em `en` e `pt_BR`.

**Screening (desacoplamento)** 7. `JobApplicationForm::submit()` dispara `ScreeningResponsesSubmitted` (`Event::fake()` na asserção do form). 8. `StoreAndEvaluateScreeningResponses` persiste as respostas e dispara `ScreeningEvaluated` (sem `Event::fake` global, ou faking seletivo). 9. **Best-effort:** se `EvaluateScreeningResponses` lança, as respostas seguem persistidas e a exceção é logada (não propaga). 10. **Não-regressão:** auto-transição por knockout continua funcionando ponta a ponta (`ScreeningEvaluated` → `HandleScreeningKnockoutTransition`).

---

## 9. Fora de escopo

- Notificar qualquer outro momento (avanço, proposta, contratação) — depende do RH (ver #198).
- Notificar reprovação/desistência (decisão 3.3).
- Preferências de canal por candidato / opt-out de e-mail.
- Mesclar/deletar uma das classes de criação (escolhemos delegar, não remover).
- Corrigir eventual re-submit do form gerando respostas duplicadas (comportamento pré-existente, não introduzido aqui).

```

```
