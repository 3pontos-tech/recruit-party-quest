# Notificar candidato ao receber a candidatura — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ao se candidatar, o candidato recebe uma confirmação "candidatura recebida" no painel (canal `database`) e por e-mail; de quebra, consolidar a criação da candidatura num único ponto, desacoplar a persistência/avaliação de screening via evento e mover o `JobApplicationForm` para o módulo `panel-app`.

**Architecture:** A criação da `Application` vira um chokepoint único (`StoreApplication`) que dispara o evento de domínio `ApplicationSubmitted`; um listener auto-descoberto envia uma `Notification` enfileirada multi-canal (`mail` + `database`). O fluxo de screening passa a reagir a um evento próprio (`ScreeningResponsesSubmitted`). O form Livewire migra de `screening` para `panel-app`.

**Tech Stack:** Laravel 12, Filament v5, Livewire v4, Pest v4, PostgreSQL, módulos `internachi/modular` (event/listener auto-discovery), namespaces `He4rt\Applications`, `He4rt\Screening`, `He4rt\App` (panel-app).

**Spec:** `docs/superpowers/specs/2026-06-17-notify-candidate-application-received-design.md`

---

## File Structure

**Módulo `applications` (`He4rt\Applications`)**

- Create `src/Events/ApplicationSubmitted.php` — evento de domínio (criação de candidatura).
- Create `src/Listeners/SendApplicationReceivedNotification.php` — envia a notificação.
- Create `src/Notifications/ApplicationReceivedNotification.php` — notificação `mail`+`database`, `ShouldQueue`.
- Create `src/Mail/ApplicationReceivedMail.php` — Mailable markdown, `ShouldQueue`.
- Create `resources/views/emails/application-received.blade.php` — template.
- Modify `src/Services/Applications/StoreApplication.php` — dispara o evento.
- Modify `src/Actions/ApplyToJobRequisitionAction.php` — delega a `StoreApplication`.
- Modify `lang/en/filament.php` e `lang/pt_BR/filament.php` — chaves novas.

**Módulo `screening` (`He4rt\Screening`)**

- Create `src/Events/ScreeningResponsesSubmitted.php` — evento de submissão de respostas.
- Create `src/Listeners/StoreAndEvaluateScreeningResponses.php` — persiste + avalia (best-effort).
- Modify `src/ScreeningServiceProvider.php` — remove o registro do form.

**Módulo `panel-app` (`He4rt\App`)**

- Move `JobApplicationForm` de `screening/src/Livewire/` → `panel-app/src/Livewire/` (namespace `He4rt\App\Livewire`).
- Move a view `screening/resources/views/livewire/job-application-form.blade.php` → `panel-app/resources/views/livewire/job-application-form.blade.php`.
- Modify `src/PanelAppServiceProvider.php` — registra `panel-app.job-application-form`.
- Modify `resources/views/components/jobs/job-description.blade.php` — atualiza o alias `<livewire:>`.
- Move os testes do form de `screening/tests/` → `panel-app/tests/`.

---

## Task 1: Evento `ApplicationSubmitted` + dispatch em `StoreApplication`

**Files:**

- Create: `app-modules/applications/src/Events/ApplicationSubmitted.php`
- Modify: `app-modules/applications/src/Services/Applications/StoreApplication.php`
- Test: `app-modules/applications/tests/Feature/Services/Applications/StoreApplicationTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use He4rt\Applications\DTOs\ApplicationDTO;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Events\ApplicationSubmitted;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Applications\StoreApplication;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;

it('creates an application and dispatches ApplicationSubmitted', function (): void {
    Event::fake([ApplicationSubmitted::class]);

    $candidate = Candidate::factory()->create();
    $requisition = JobRequisition::factory()->create();

    $application = resolve(StoreApplication::class)->execute(new ApplicationDTO(
        requisitionId: $requisition->getKey(),
        candidateId: $candidate->getKey(),
        teamId: $requisition->team_id,
        status: ApplicationStatusEnum::New,
        source: CandidateSourceEnum::CareerPage,
    ));

    assertDatabaseHas(Application::class, ['id' => $application->getKey(), 'status' => ApplicationStatusEnum::New->value]);

    Event::assertDispatched(
        ApplicationSubmitted::class,
        fn (ApplicationSubmitted $event): bool => $event->application->is($application),
    );
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=StoreApplicationTest`
Expected: FAIL — `Class "He4rt\Applications\Events\ApplicationSubmitted" not found`.

- [ ] **Step 3: Create the event**

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Events;

use He4rt\Applications\Models\Application;

final class ApplicationSubmitted
{
    public function __construct(public Application $application) {}
}
```

- [ ] **Step 4: Dispatch from `StoreApplication`**

Adicione o import `use He4rt\Applications\Events\ApplicationSubmitted;` e dispare o evento antes do `return`:

```php
public function execute(ApplicationDTO $dto): Application
{
    $application = Application::query()->create([
        'requisition_id' => $dto->requisitionId,
        'candidate_id' => $dto->candidateId,
        'team_id' => $dto->teamId,
        'status' => $dto->status,
        'source' => $dto->source,
    ]);

    $application->update([
        'current_stage_id' => $application->first_stage?->getKey(),
    ]);

    event(new ApplicationSubmitted($application));

    return $application;
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --compact --filter=StoreApplicationTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app-modules/applications/src/Events/ApplicationSubmitted.php app-modules/applications/src/Services/Applications/StoreApplication.php app-modules/applications/tests/Feature/Services/Applications/StoreApplicationTest.php
git commit -m "feat(applications): dispara ApplicationSubmitted ao criar candidatura"
```

---

## Task 2: `ApplyToJobRequisitionAction` delega a `StoreApplication`

**Files:**

- Modify: `app-modules/applications/src/Actions/ApplyToJobRequisitionAction.php`
- Test: `app-modules/applications/tests/Feature/Actions/ApplyToJobRequisitionActionTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Actions\ApplyToJobRequisitionAction;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Events\ApplicationSubmitted;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;

it('applies a candidate by delegating to StoreApplication and dispatches the event', function (): void {
    Event::fake([ApplicationSubmitted::class]);

    $candidate = Candidate::factory()->create();
    $requisition = JobRequisition::factory()->create();

    $application = resolve(ApplyToJobRequisitionAction::class)->execute($requisition, $candidate);

    assertDatabaseHas(Application::class, [
        'id' => $application->getKey(),
        'requisition_id' => $requisition->getKey(),
        'candidate_id' => $candidate->getKey(),
        'status' => ApplicationStatusEnum::New->value,
    ]);

    Event::assertDispatched(ApplicationSubmitted::class);
});

it('reports whether a candidate already applied', function (): void {
    $candidate = Candidate::factory()->create();
    $requisition = JobRequisition::factory()->create();
    $action = resolve(ApplyToJobRequisitionAction::class);

    expect($action->hasApplied($requisition, $candidate))->toBeFalse();

    $action->execute($requisition, $candidate);

    expect($action->hasApplied($requisition, $candidate))->toBeTrue();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=ApplyToJobRequisitionActionTest`
Expected: FAIL — o `execute()` atual cria direto e **não** dispara `ApplicationSubmitted`, então `Event::assertDispatched` falha.

- [ ] **Step 3: Implement delegation**

Substitua o corpo do arquivo por (mantendo `hasApplied()`):

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Actions;

use He4rt\Applications\DTOs\ApplicationDTO;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Applications\StoreApplication;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

class ApplyToJobRequisitionAction
{
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

    public function hasApplied(JobRequisition $requisition, Candidate $candidate): bool
    {
        return $requisition->applications()
            ->where('candidate_id', $candidate->id)
            ->exists();
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=ApplyToJobRequisitionActionTest`
Expected: PASS.

- [ ] **Step 5: Run the panel-app apply flow test (no regression)**

Run: `php artisan test --compact --filter=JobRequisitionPagesTest`
Expected: PASS (o `applyDirectly` segue criando e redirecionando).

- [ ] **Step 6: Commit**

```bash
git add app-modules/applications/src/Actions/ApplyToJobRequisitionAction.php app-modules/applications/tests/Feature/Actions/ApplyToJobRequisitionActionTest.php
git commit -m "refactor(applications): ApplyToJobRequisitionAction delega criacao a StoreApplication"
```

---

## Task 3: i18n + `ApplicationReceivedMail` (Mailable + template)

**Files:**

- Modify: `app-modules/applications/lang/en/filament.php`
- Modify: `app-modules/applications/lang/pt_BR/filament.php`
- Create: `app-modules/applications/src/Mail/ApplicationReceivedMail.php`
- Create: `app-modules/applications/resources/views/emails/application-received.blade.php`
- Test: `app-modules/applications/tests/Feature/Mail/ApplicationReceivedMailTest.php`

- [ ] **Step 1: Add i18n keys (en)**

Em `app-modules/applications/lang/en/filament.php`, adicione estas duas chaves de 1º nível **antes** do `];` final (irmãs de `resource`, `sections`, `actions`):

```php
    'emails' => [
        'application_received' => [
            'subject' => 'We received your application — :job',
            'greeting' => 'Hi :name,',
            'line' => 'We received your application for :job. You can follow its progress in your panel.',
            'action' => 'View application',
        ],
    ],
    'notifications' => [
        'application_received' => [
            'title' => 'Application received',
            'body' => 'We received your application for :job.',
            'view_button' => 'View application',
        ],
    ],
```

- [ ] **Step 2: Add i18n keys (pt_BR)**

Em `app-modules/applications/lang/pt_BR/filament.php`, adicione antes do `];` final:

```php
    'emails' => [
        'application_received' => [
            'subject' => 'Recebemos sua candidatura — :job',
            'greeting' => 'Olá :name,',
            'line' => 'Recebemos sua candidatura para :job. Você pode acompanhar o andamento no seu painel.',
            'action' => 'Ver candidatura',
        ],
    ],
    'notifications' => [
        'application_received' => [
            'title' => 'Candidatura recebida',
            'body' => 'Recebemos sua candidatura para :job.',
            'view_button' => 'Ver candidatura',
        ],
    ],
```

- [ ] **Step 3: Write the failing test**

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Mail\ApplicationReceivedMail;
use He4rt\Applications\Models\Application;

it('builds the application received mail with subject and job title', function (): void {
    $application = Application::factory()->create();
    $jobTitle = $application->requisition->post->title;

    $mailable = new ApplicationReceivedMail($application);

    $mailable->assertHasSubject(__('applications::filament.emails.application_received.subject', ['job' => $jobTitle]));
    $mailable->assertSeeInHtml($jobTitle);
});
```

- [ ] **Step 4: Run test to verify it fails**

Run: `php artisan test --compact --filter=ApplicationReceivedMailTest`
Expected: FAIL — `Class "He4rt\Applications\Mail\ApplicationReceivedMail" not found`.

- [ ] **Step 5: Create the Mailable**

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Mail;

use He4rt\Applications\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Application $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('applications::filament.emails.application_received.subject', [
                'job' => $this->application->requisition->post->title,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'applications::emails.application-received',
            with: [
                'candidateName' => $this->application->candidate->user->name,
                'jobTitle' => $this->application->requisition->post->title,
                // Rota nomeada (string) em vez de panel-app::ApplicationResource::getUrl()
                // para não criar dependência reversa applications -> panel-app.
                'url' => route('filament.app.resources.applications.view', ['record' => $this->application->getKey()]),
            ],
        );
    }
}
```

- [ ] **Step 6: Create the markdown template**

`app-modules/applications/resources/views/emails/application-received.blade.php`:

```blade
<x-mail::message>
    # {{ __('applications::filament.emails.application_received.greeting', ['name' => $candidateName]) }}

    {{ __('applications::filament.emails.application_received.line', ['job' => $jobTitle]) }}

    <x-mail::button :url="$url">
        {{ __('applications::filament.emails.application_received.action') }}
    </x-mail::button>
</x-mail::message>
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --compact --filter=ApplicationReceivedMailTest`
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add app-modules/applications/lang app-modules/applications/src/Mail/ApplicationReceivedMail.php app-modules/applications/resources/views/emails/application-received.blade.php app-modules/applications/tests/Feature/Mail/ApplicationReceivedMailTest.php
git commit -m "feat(applications): adiciona ApplicationReceivedMail e traducoes"
```

---

## Task 4: `ApplicationReceivedNotification` (mail + database, enfileirada)

**Files:**

- Create: `app-modules/applications/src/Notifications/ApplicationReceivedNotification.php`
- Test: `app-modules/applications/tests/Feature/Notifications/ApplicationReceivedNotificationContentTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Mail\ApplicationReceivedMail;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Notifications\ApplicationReceivedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

it('targets mail and database channels and is queued', function (): void {
    $application = Application::factory()->create();
    $user = $application->candidate->user;

    $notification = new ApplicationReceivedNotification($application);

    expect($notification)->toBeInstanceOf(ShouldQueue::class)
        ->and($notification->via($user))->toBe(['mail', 'database'])
        ->and($notification->toMail($user))->toBeInstanceOf(ApplicationReceivedMail::class);

    $database = $notification->toDatabase($user);
    expect($database)->toBeArray()
        ->and(json_encode($database))->toContain($application->requisition->post->title);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=ApplicationReceivedNotificationContentTest`
Expected: FAIL — classe não existe.

- [ ] **Step 3: Create the notification**

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Notifications;

use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Support\Icons\Heroicon;
use He4rt\Applications\Mail\ApplicationReceivedMail;
use He4rt\Applications\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class ApplicationReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): ApplicationReceivedMail
    {
        return new ApplicationReceivedMail($this->application)
            ->to($this->application->candidate->user->email);
    }

    /**
     * @return array<string, mixed>
     */
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
                    ->url(route('filament.app.resources.applications.view', [
                        'record' => $this->application->getKey(),
                    ]))
                    ->label(__('applications::filament.notifications.application_received.view_button'))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=ApplicationReceivedNotificationContentTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app-modules/applications/src/Notifications/ApplicationReceivedNotification.php app-modules/applications/tests/Feature/Notifications/ApplicationReceivedNotificationContentTest.php
git commit -m "feat(applications): adiciona ApplicationReceivedNotification (mail+database)"
```

---

## Task 5: Listener `SendApplicationReceivedNotification` + integração

**Files:**

- Create: `app-modules/applications/src/Listeners/SendApplicationReceivedNotification.php`
- Test: `app-modules/applications/tests/Feature/Listeners/SendApplicationReceivedNotificationTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use He4rt\Applications\DTOs\ApplicationDTO;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Notifications\ApplicationReceivedNotification;
use He4rt\Applications\Services\Applications\StoreApplication;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Support\Facades\Notification;

it('notifies the candidate user when an application is submitted', function (): void {
    Notification::fake();

    $candidate = Candidate::factory()->create();
    $requisition = JobRequisition::factory()->create();

    $application = resolve(StoreApplication::class)->execute(new ApplicationDTO(
        requisitionId: $requisition->getKey(),
        candidateId: $candidate->getKey(),
        teamId: $requisition->team_id,
        status: ApplicationStatusEnum::New,
        source: CandidateSourceEnum::CareerPage,
    ));

    Notification::assertSentTo($candidate->user, ApplicationReceivedNotification::class);
    expect($application->exists)->toBeTrue();
});

it('does not notify when an application is created without the submission flow', function (): void {
    Notification::fake();

    Application::factory()->create();

    Notification::assertNothingSent();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=SendApplicationReceivedNotificationTest`
Expected: FAIL no primeiro caso — nenhum listener registrado, nada é enviado.

- [ ] **Step 3: Create the listener (auto-discovered)**

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Listeners;

use He4rt\Applications\Events\ApplicationSubmitted;
use He4rt\Applications\Notifications\ApplicationReceivedNotification;

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

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=SendApplicationReceivedNotificationTest`
Expected: PASS — o `EventsPlugin` do `internachi/modular` auto-descobre o listener; o caso negativo passa porque `Application::factory()->create()` não dispara `ApplicationSubmitted`.

- [ ] **Step 5: Commit**

```bash
git add app-modules/applications/src/Listeners/SendApplicationReceivedNotification.php app-modules/applications/tests/Feature/Listeners/SendApplicationReceivedNotificationTest.php
git commit -m "feat(applications): notifica candidato ao receber candidatura (#189)"
```

---

## Task 6: Evento + listener de screening (`ScreeningResponsesSubmitted`)

**Files:**

- Create: `app-modules/screening/src/Events/ScreeningResponsesSubmitted.php`
- Create: `app-modules/screening/src/Listeners/StoreAndEvaluateScreeningResponses.php`
- Test: `app-modules/screening/tests/Feature/Listeners/StoreAndEvaluateScreeningResponsesTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Screening\Collections\ScreeningResponseCollection;
use He4rt\Screening\DTOs\ScreeningResponseDTO;
use He4rt\Screening\Events\ScreeningEvaluated;
use He4rt\Screening\Events\ScreeningResponsesSubmitted;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;

it('persists responses and evaluates when responses are submitted', function (): void {
    Event::fake([ScreeningEvaluated::class]);

    $application = Application::factory()->create();
    $question = ScreeningQuestion::factory()
        ->for($application->requisition, 'screenable')
        ->create();

    $responses = new ScreeningResponseCollection();
    $responses->add(new ScreeningResponseDTO(
        teamId: $application->team_id,
        applicationId: $application->getKey(),
        questionId: $question->getKey(),
        response_value: ['value' => 'yes'],
    ));

    event(new ScreeningResponsesSubmitted($application, $responses));

    assertDatabaseHas(ScreeningResponse::class, [
        'application_id' => $application->getKey(),
        'question_id' => $question->getKey(),
    ]);
    Event::assertDispatched(ScreeningEvaluated::class);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=StoreAndEvaluateScreeningResponsesTest`
Expected: FAIL — `ScreeningResponsesSubmitted` não existe.

- [ ] **Step 3: Create the event**

```php
<?php

declare(strict_types=1);

namespace He4rt\Screening\Events;

use He4rt\Applications\Models\Application;
use He4rt\Screening\Collections\ScreeningResponseCollection;

final class ScreeningResponsesSubmitted
{
    public function __construct(
        public Application $application,
        public ScreeningResponseCollection $responses,
    ) {}
}
```

- [ ] **Step 4: Create the listener**

```php
<?php

declare(strict_types=1);

namespace He4rt\Screening\Listeners;

use He4rt\Screening\Actions\ScreeningResponse\EvaluateScreeningResponses;
use He4rt\Screening\Actions\ScreeningResponse\StoreScreeningResponse;
use He4rt\Screening\Events\ScreeningResponsesSubmitted;
use Illuminate\Support\Facades\Log;
use Throwable;

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

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --compact --filter=StoreAndEvaluateScreeningResponsesTest`
Expected: PASS (listener auto-descoberto).

- [ ] **Step 6: Commit**

```bash
git add app-modules/screening/src/Events/ScreeningResponsesSubmitted.php app-modules/screening/src/Listeners/StoreAndEvaluateScreeningResponses.php app-modules/screening/tests/Feature/Listeners/StoreAndEvaluateScreeningResponsesTest.php
git commit -m "feat(screening): evento ScreeningResponsesSubmitted + listener de persistencia/avaliacao"
```

---

## Task 7: `JobApplicationForm::submit()` fino — emite o evento

> Ainda em `He4rt\Screening\Livewire\JobApplicationForm` (a mudança de módulo é a Task 8). Aqui só removemos a orquestração inline.

**Files:**

- Modify: `app-modules/screening/src/Livewire/JobApplicationForm.php`

- [ ] **Step 1: Run the existing form tests (baseline verde)**

Run: `php artisan test --compact --filter=JobApplicationFormTest`
Expected: PASS (estado atual). Anote que devem continuar passando após a mudança.

- [ ] **Step 2: Replace `submit()` and extract the collection builder**

Troque o método `submit()` e adicione o helper privado. Remova os imports não usados (`StoreScreeningResponse`, `EvaluateScreeningResponses`, `Log`, `Throwable`, `Notification` permanece) e adicione `use He4rt\Screening\Events\ScreeningResponsesSubmitted;`:

```php
public function submit(): Redirector|RedirectResponse
{
    $this->validate();

    if (! $this->application instanceof Application) {
        /** @var Candidate $candidate */
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

    Notification::make()
        ->title(__('screening::messages.application_submitted'))
        ->success()
        ->send();

    return redirect(route('filament.app.resources.applications.view', ['record' => $this->application->getKey()]));
}

private function buildResponseCollection(): ScreeningResponseCollection
{
    $collection = new ScreeningResponseCollection();

    foreach ($this->responses as $questionId => $value) {
        if ($value === null) {
            continue;
        }

        $isStructuredResponse = is_array($value)
            && (array_key_exists('value', $value) || array_key_exists('files', $value));

        $collection->add(new ScreeningResponseDTO(
            teamId: $this->requisition->team_id,
            applicationId: $this->application->getKey(),
            questionId: $questionId,
            response_value: $isStructuredResponse ? $value : ['value' => $value],
        ));
    }

    return $collection;
}
```

> Mantém o import `use He4rt\Applications\Services\Applications\StoreApplication;` (continua sendo o ponto de criação). `StoreScreeningResponse`/`EvaluateScreeningResponses` saem do componente.

- [ ] **Step 3: Run the form tests to verify no regression**

Run: `php artisan test --compact --filter=JobApplicationForm`
Expected: PASS — respostas persistidas e avaliação rodam agora via listener (Task 6). Inclui `JobApplicationFormTest` e `JobApplicationFormKnockoutTest`.

- [ ] **Step 4: Commit**

```bash
git add app-modules/screening/src/Livewire/JobApplicationForm.php
git commit -m "refactor(screening): JobApplicationForm emite ScreeningResponsesSubmitted"
```

---

## Task 8: Mover `JobApplicationForm` para `panel-app`

**Files:**

- Create: `app-modules/panel-app/src/Livewire/JobApplicationForm.php` (movido)
- Delete: `app-modules/screening/src/Livewire/JobApplicationForm.php`
- Create: `app-modules/panel-app/resources/views/livewire/job-application-form.blade.php` (movido)
- Delete: `app-modules/screening/resources/views/livewire/job-application-form.blade.php`
- Modify: `app-modules/panel-app/src/PanelAppServiceProvider.php`
- Modify: `app-modules/screening/src/ScreeningServiceProvider.php`
- Modify: `app-modules/panel-app/resources/views/components/jobs/job-description.blade.php`
- Move: testes do form para `app-modules/panel-app/tests/Feature/Livewire/`

- [ ] **Step 1: Move the component class and change its namespace**

```bash
git mv app-modules/screening/src/Livewire/JobApplicationForm.php app-modules/panel-app/src/Livewire/JobApplicationForm.php
```

No arquivo movido, troque a primeira linha de namespace:

```php
// de:
namespace He4rt\Screening\Livewire;
// para:
namespace He4rt\App\Livewire;
```

E no método `render()`, troque a view namespace:

```php
return view('panel-app::livewire.job-application-form', [
    'requiredQuestionIds' => $requiredQuestionIds,
]);
```

> Os imports `He4rt\Screening\...` (DTOs, Collection, evento) permanecem — `panel-app` depende de `screening`.

- [ ] **Step 2: Move the view**

```bash
git mv app-modules/screening/resources/views/livewire/job-application-form.blade.php app-modules/panel-app/resources/views/livewire/job-application-form.blade.php
```

> Não altere o conteúdo: as refs internas `<livewire:screening.source-question>` e `screening::question_validations.*` continuam válidas (widgets permanecem no `screening`).

- [ ] **Step 3: Register the component in `PanelAppServiceProvider`**

Adicione o import `use He4rt\App\Livewire\JobApplicationForm;` e, no `boot()`, junto aos outros `Livewire::component(...)`:

```php
Livewire::component('panel-app.job-application-form', JobApplicationForm::class);
```

- [ ] **Step 4: Remove the registration from `ScreeningServiceProvider`**

Remova a linha:

```php
Livewire::component('screening.job-application-form', JobApplicationForm::class);
```

E o import `use He4rt\Screening\Livewire\JobApplicationForm;`.

- [ ] **Step 5: Update the blade reference**

Em `app-modules/panel-app/resources/views/components/jobs/job-description.blade.php` (linha ~229):

```blade
<livewire:panel-app.job-application-form :requisition="$jobRequisition" />
```

- [ ] **Step 6: Move and fix the form tests**

```bash
git mv app-modules/screening/tests/Feature/Livewire/JobApplicationFormTest.php app-modules/panel-app/tests/Feature/Livewire/JobApplicationFormTest.php
git mv app-modules/screening/tests/Feature/Livewire/JobApplicationFormKnockoutTest.php app-modules/panel-app/tests/Feature/Livewire/JobApplicationFormKnockoutTest.php
```

Nos dois arquivos, troque o import:

```php
// de:
use He4rt\Screening\Livewire\JobApplicationForm;
// para:
use He4rt\App\Livewire\JobApplicationForm;
```

Depois, verifique se algum outro teste ainda referencia o caminho antigo e ajuste o import do mesmo jeito:

Run: `grep -rln "He4rt\\\\Screening\\\\Livewire\\\\JobApplicationForm\|screening.job-application-form" app-modules`
Expected: nenhum resultado após os ajustes (ex.: `QuestionValidationsTest.php` — se aparecer, atualize o import).

- [ ] **Step 7: Run the moved tests + both modules' suites**

Run: `php artisan test --compact app-modules/panel-app/tests/Feature/Livewire app-modules/screening/tests`
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add -A
git commit -m "refactor(panel-app): move JobApplicationForm de screening para panel-app"
```

---

## Task 9: Verificação final

- [ ] **Step 1: Pint (estilo)**

Run: `vendor/bin/pint --dirty --format agent`
Expected: sem erros (corrige formatação automaticamente).

- [ ] **Step 2: Suítes dos módulos afetados**

Run: `php artisan test --compact app-modules/applications app-modules/screening app-modules/panel-app`
Expected: PASS em tudo.

- [ ] **Step 3: Larastan (análise estática)**

Run: `vendor/bin/phpstan analyse --memory-limit=2G`
Expected: sem erros novos introduzidos.

- [ ] **Step 4: Commit final (se Pint alterou algo)**

```bash
git add -A
git commit -m "style: pint nos modulos afetados"
```

---

## Notas de execução

- **Auto-discovery:** listeners em `src/Listeners/` (assinatura `handle(EventType $event)`) são registrados pelo `EventsPlugin` do `internachi/modular` — **sem** registro manual.
- **i18n:** sempre `en` + `pt_BR`. As chaves usam `:job` e `:name` como placeholders.
- **Fila:** a `Notification` é `ShouldQueue`; nos testes use `Notification::fake()` (não exige worker).
- **Não-regressão crítica:** a auto-transição por knockout (`ScreeningEvaluated` → `HandleScreeningKnockoutTransition`) deve continuar verde após a Task 7 — é o que prova que a avaliação migrou para o listener sem quebrar o comportamento do recrutador.
