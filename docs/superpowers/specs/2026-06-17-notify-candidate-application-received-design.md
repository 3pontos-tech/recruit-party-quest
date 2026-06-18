# Design — Notificar candidato ao receber a candidatura (confirmação)

- **Issue:** #189 (sub-issue do épico #191 — política de comunicação com o candidato)
- **Branch:** `feat/notify-candidate-application-received-189`
- **Data:** 2026-06-17
- **Status:** aprovado para implementação

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

| Premissa da issue                                                   | Realidade no código                                                                                                                                                                           | Efeito no design                                                                                       |
| ------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| "Canal `database` nunca foi usado"                                  | **Falso.** `He4rt\Organization\Notifications\MentionedInCommentNotification` já usa `['mail','database']` com `toDatabase()` via `FilamentNotification::make()->...->getDatabaseMessage()`.   | **Reusamos** esse padrão; não estreamos nada. Menos risco.                                             |
| "Candidato é `Notifiable`"                                          | Quem é `Notifiable` é o `User` (`He4rt\Users\User`), não o `Candidate`.                                                                                                                       | Alvo da notificação é `$application->candidate->user`.                                                 |
| "Ao criar a `Application`… disparar via `ApplicationStatusChanged`" | `ApplicationStatusChanged` só dispara quando `fromStatus !== toStatus` (`AbstractApplicationTransition.php:73`). A candidatura **nasce** em `New` — esse evento **nunca** dispara na criação. | O gatilho é um **evento de domínio próprio** (`ApplicationSubmitted`), não `ApplicationStatusChanged`. |

---

## 2. Decisões de design

1. **Gatilho = evento de domínio próprio** (`ApplicationSubmitted`), disparado apenas pelos fluxos reais de
   candidatura. Criações não interativas (factory/seeder/import) **não** notificam. (Decisão do usuário.)
2. **Consolidar a criação em um único ponto.** Hoje há duas classes que duplicam a criação da `Application`:
    - `ApplyToJobRequisitionAction::execute(JobRequisition, Candidate, source)` — usada pelo apply direto
      (vaga **sem** triagem), em `panel-app` `ViewJobRequisition::applyDirectly()`.
    - `StoreApplication::execute(ApplicationDTO)` — usada pelo formulário com triagem
      (`screening` `JobApplicationForm::submit()`).

    `ApplyToJobRequisitionAction` passa a **montar o `ApplicationDTO` e delegar** a `StoreApplication`,
    que vira o **chokepoint único** de criação e o único lugar que dispara `ApplicationSubmitted`.
    Assinaturas públicas permanecem inalteradas (sem regressão em `ViewJobRequisition` nem nos testes).

3. **Entrega = uma única `Notification` multi-canal** (`via = ['mail','database']`), `implements ShouldQueue`,
   espelhando `MentionedInCommentNotification`. (Aprovado — abordagem A.)

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
   ApplyToJobRequisitionAction              JobApplicationForm::submit()
     ::execute() monta DTO ─────┐                     │
                                ▼                      ▼
                       ┌──────────────────────────────────────┐
                       │ StoreApplication::execute(DTO)        │  ← CHOKEPOINT ÚNICO
                       │  • Application::create(status=New)    │
                       │  • update(current_stage_id)           │
                       │  • event(new ApplicationSubmitted)────┼──┐
                       └────────────────────────────────────────┘ │
                                                                   ▼
                       SendApplicationReceivedNotification (auto-discovery, síncrono)
                         guard: $app->candidate?->user
                         $user->notify(new ApplicationReceivedNotification($app))
                                                                   │ (ShouldQueue) enfileira
                                                                   ▼
                                              ┌──────────── FILA (worker) ────────────┐
                                              │ via = ['mail','database']             │
                                              │  toMail()     → ApplicationReceivedMail│
                                              │  toDatabase() → 🔔 card no panel-app    │
                                              └────────────────────────────────────────┘
```

**Robustez:** `event()` é disparado **fora de qualquer `DB::transaction`** (as Actions de criação não abrem
transação) e a `Notification` é `ShouldQueue` — e-mail e card rodam no worker, desacoplados do request.
Falha de e-mail não toca a candidatura já persistida.

---

## 4. Componentes (todos no módulo `applications`, namespace `He4rt\Applications`)

| Peça         | Caminho                                                 | Papel                                                                                                                   |
| ------------ | ------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| Evento       | `src/Events/ApplicationSubmitted.php`                   | `final` que carrega `public Application $application`.                                                                  |
| Listener     | `src/Listeners/SendApplicationReceivedNotification.php` | `handle(ApplicationSubmitted $event)`. Auto-discovery via `EventsPlugin` do `internachi/modular` (sem registro manual). |
| Notification | `src/Notifications/ApplicationReceivedNotification.php` | `extends Notification implements ShouldQueue`; `via = ['mail','database']`.                                             |
| Mailable     | `src/Mail/ApplicationReceivedMail.php`                  | `extends Mailable implements ShouldQueue`; markdown `applications::emails.application-received`.                        |
| Template     | `resources/views/emails/application-received.blade.php` | Corpo do e-mail (saudação + cargo + CTA).                                                                               |
| i18n         | `lang/{en,pt_BR}/filament.php`                          | chaves novas (ver §7).                                                                                                  |

Reaproveita o já existente: `ApplicationsServiceProvider` já registra `applications::` para views e traduções;
`panel-app` já tem `DatabaseNotifications` ativo (renderiza o card); `Candidate::user()` (BelongsTo) já existe.

---

## 5. Comportamento esperado (BDD)

**Happy path**

- **Dado** um candidato que se candidata (qualquer um dos dois fluxos), **Quando** a `Application` é criada,
  **Então** dispara `ApplicationSubmitted` e o `User` do candidato recebe `ApplicationReceivedNotification`
  no painel (card com botão "Ver candidatura") **e** por e-mail (assunto + cargo + link), **enfileirada**.

**Tolerância a falha**

- **Dado** que o envio de e-mail falha no worker, **Quando** isso ocorre, **Então** a candidatura
  **permanece** criada (notificação fora da transação e na fila).

**Não-regressão / respeito ao RH**

- **Dado** uma `Application` criada por seeder/factory/import (sem passar pelos fluxos de candidatura),
  **Quando** ela é gravada, **Então** **nenhuma** notificação é enviada (não há hook no model `created`).
- **Dado** qualquer reprovação ou transição posterior, **Então** **nenhum** aviso é enviado ao candidato
  (decisão 3.3 intacta).

**Borda**

- **Dado** um `Application` cujo `candidate->user` é nulo, **Quando** o listener roda, **Então** sai sem erro
  (guarda defensiva).

---

## 6. Antes / depois (código)

### 6.1 `StoreApplication` — vira o chokepoint e dispara o evento

**Antes:**

```php
public function execute(ApplicationDTO $dto): Application
{
    $application = Application::query()->create([/* ... */]);
    $application->update(['current_stage_id' => $application->first_stage?->getKey()]);

    return $application;
}
```

**Depois:**

```php
public function execute(ApplicationDTO $dto): Application
{
    $application = Application::query()->create([/* ... */]);
    $application->update(['current_stage_id' => $application->first_stage?->getKey()]);

    event(new ApplicationSubmitted($application));

    return $application;
}
```

### 6.2 `ApplyToJobRequisitionAction` — delega a `StoreApplication`

**Antes:** cria a `Application` por conta própria (lógica duplicada).

**Depois:**

```php
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

// hasApplied() permanece inalterado.
```

> `ViewJobRequisition::applyDirectly(ApplyToJobRequisitionAction $action)` resolve a Action pelo container,
> então a injeção de `StoreApplication` no construtor funciona sem mudar o chamador.

### 6.3 `ApplicationReceivedNotification` (novo — esqueleto, espelha `MentionedInCommentNotification`)

```php
final class ApplicationReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

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
                Action::make('view')
                    ->button()
                    ->url(ApplicationResource::getUrl('view', ['record' => $this->application->getKey()], panel: 'app'))
                    ->label(__('applications::filament.notifications.application_received.view_button'))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
```

> O `ApplicationResource` referenciado é o do `panel-app` (`He4rt\App\Filament\Resources\Applications\ApplicationResource`).
> O panel id é `'app'` (enum `FilamentPanel::App`) — confirmado.

### 6.4 `SendApplicationReceivedNotification` (novo)

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

```php
// emails.application_received.subject  → assunto do e-mail (com :job)
// emails.application_received.*         → linhas do corpo (markdown)
// notifications.application_received.title
// notifications.application_received.body        (com :job)
// notifications.application_received.view_button
```

Copy (a redigir na implementação, tom de confirmação cordial):

- **title:** "Candidatura recebida" / "Application received"
- **body:** "Recebemos sua candidatura para :job." / "We received your application for :job."
- **subject:** "Recebemos sua candidatura — :job" / "We received your application — :job"

---

## 8. Testes (Pest — feature, módulo `applications` e/ou `screening`)

1. **Fluxo direto:** `ApplyToJobRequisitionAction::execute()` → `Notification::fake()` + `assertSentTo($user, ApplicationReceivedNotification::class)`.
2. **Fluxo triagem:** `StoreApplication::execute()` (ou `JobApplicationForm` via `livewire()`) → mesma asserção.
3. **Enfileiramento + canais:** notificação implementa `ShouldQueue`; `via()` retorna `['mail','database']`.
4. **Conteúdo:** card/e-mail contêm o **cargo** (`requisition->post->title`) e o **link** para a `ViewApplication` do `panel-app`.
5. **Negativo (codifica a decisão de produto):** `Application::factory()->create()` **não** dispara o evento / **não** notifica.
6. **i18n:** chaves novas existem em `en` e `pt_BR`.
7. **Não-regressão:** `applyDirectly` e o submit do formulário continuam criando a candidatura e redirecionando normalmente.

---

## 9. Fora de escopo

- Notificar qualquer outro momento (avanço de etapa, proposta, contratação) — depende de aval do RH (ver #198).
- Notificar reprovação/desistência (decisão 3.3).
- Preferências de canal por candidato / opt-out de e-mail (item "a discutir" do épico, sem decisão).
- Mesclar/deletar uma das classes de criação (escolhemos delegar, não remover).
