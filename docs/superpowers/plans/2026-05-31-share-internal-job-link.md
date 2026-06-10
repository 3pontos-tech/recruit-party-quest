# Share Internal Job Link — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Dar ao RH (painel da org) uma action que copia o link público da vaga para o clipboard, e esconder o botão de compartilhar do candidato em vagas internas.

**Architecture:** Uma `Action` do Filament v5 que usa `actionJs()` (JS client-side, sem round-trip) para escrever a URL absoluta de detalhe da vaga (painel `app`) no clipboard via `navigator.clipboard.writeText`. A geração da URL fica isolada num método estático `shareUrlFor()` (unit-testável). Em paralelo, um guard `@if (! $job->is_internal_only)` no componente Blade `share-job-button` remove o botão do lado do candidato para vagas internas.

**Tech Stack:** Laravel 12, Filament v5, Livewire 4, Pest 4, módulos `panel-organization` e `panel-app`.

**Spec:** `docs/superpowers/specs/2026-05-31-share-internal-job-link-design.md`

---

## Mapa de arquivos

**Criar:**
- `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Actions/CopyJobShareLinkAction.php` — a action (label/ícone/authorize/disabled/tooltip/actionJs) + `shareUrlFor()` estático.
- `app-modules/panel-organization/tests/Feature/JobRequisition/CopyJobShareLinkTest.php` — testes da org (URL, presença, estado).

**Modificar:**
- `app-modules/panel-organization/lang/en/filament.php` — chave `actions.copy_share_link`.
- `app-modules/panel-organization/lang/pt_BR/filament.php` — chave `actions.copy_share_link`.
- `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Tables/JobRequisitionsTable.php:118-128` — registrar action no `ActionGroup`.
- `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Pages/ViewJobRequisition.php:32-37` — registrar action no header.
- `app-modules/panel-app/resources/views/components/jobs/share-job-button.blade.php` — guard `is_internal_only`.
- `app-modules/panel-app/tests/Feature/Filament/JobRequisitions/JobRequisitionPagesTest.php` — describe block para visibilidade do share button.

---

## Task 0: Baseline verde

- [ ] **Step 1: Rodar os testes que serão tocados, para confirmar baseline limpo**

Run:
```bash
php artisan test --compact \
  app-modules/panel-organization/tests/Feature/JobRequisition/ViewJobRequisitionTest.php \
  app-modules/panel-organization/tests/Feature/JobRequisition/DuplicateJobRequisitionTest.php \
  app-modules/panel-app/tests/Feature/Filament/JobRequisitions/JobRequisitionPagesTest.php
```
Expected: PASS (todos verdes). Se algo falhar antes de qualquer mudança, **parar e reportar**.

---

## Task 1: `CopyJobShareLinkAction` + `shareUrlFor()` + traduções

**Files:**
- Create: `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Actions/CopyJobShareLinkAction.php`
- Modify: `app-modules/panel-organization/lang/en/filament.php`, `app-modules/panel-organization/lang/pt_BR/filament.php`
- Test: `app-modules/panel-organization/tests/Feature/JobRequisition/CopyJobShareLinkTest.php`

- [ ] **Step 1: Escrever o teste falho do `shareUrlFor`**

Criar `app-modules/panel-organization/tests/Feature/JobRequisition/CopyJobShareLinkTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\CopyJobShareLinkAction;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    $this->recruiter = Recruiter::factory()->createOne();
    actingAs($this->recruiter->user);
    $this->team = $this->recruiter->team;
    $this->department = Department::factory()->forRecruiter($this->recruiter)->createOne();
    filament()->setTenant($this->team);

    $this->makeRequisition = fn (array $attributes = []): JobRequisition => JobRequisition::factory()
        ->for($this->team)
        ->for($this->department)
        ->for($this->recruiter, 'recruiter')
        ->for($this->recruiter->user, 'createdBy')
        ->create($attributes);
});

it('builds the candidate detail URL from the job posting slug', function (): void {
    $requisition = ($this->makeRequisition)();
    $posting = JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    $url = CopyJobShareLinkAction::shareUrlFor($requisition->fresh());

    expect($url)
        ->toBeString()
        ->toContain('/vagas/'.$posting->slug)
        ->toStartWith('http');
});

it('returns null when the requisition has no job posting', function (): void {
    $requisition = ($this->makeRequisition)();

    expect(CopyJobShareLinkAction::shareUrlFor($requisition->fresh()))->toBeNull();
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `php artisan test --compact --filter='builds the candidate detail URL|returns null when the requisition has no job posting'`
Expected: FAIL — `Class "...CopyJobShareLinkAction" not found`.

- [ ] **Step 3: Adicionar traduções**

Em `app-modules/panel-organization/lang/en/filament.php`, dentro do array `'actions' => [ ... ]`, adicionar:

```php
'copy_share_link' => [
    'label' => 'Copy share link',
    'tooltip_unavailable' => 'Publish a posting before sharing this job',
    'notification_copied' => 'Link copied to clipboard',
],
```

Em `app-modules/panel-organization/lang/pt_BR/filament.php`, dentro do array `'actions' => [ ... ]`, adicionar:

```php
'copy_share_link' => [
    'label' => 'Copiar link de compartilhamento',
    'tooltip_unavailable' => 'Publique um anúncio antes de compartilhar esta vaga',
    'notification_copied' => 'Link copiado para a área de transferência',
],
```

- [ ] **Step 4: Implementar a action**

Criar `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Actions/CopyJobShareLinkAction.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions;

use App\Enums\FilamentPanel;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource as CandidateJobRequisitionResource;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

class CopyJobShareLinkAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('panel-organization::filament.actions.copy_share_link.label'))
            ->icon(Heroicon::OutlinedLink)
            ->color('gray')
            ->authorize('view')
            ->disabled(fn (JobRequisition $record): bool => blank(self::shareUrlFor($record)))
            ->tooltip(fn (JobRequisition $record): ?string => blank(self::shareUrlFor($record))
                ? __('panel-organization::filament.actions.copy_share_link.tooltip_unavailable')
                : null)
            ->actionJs(fn (JobRequisition $record): string => self::clipboardJs($record));
    }

    public static function shareUrlFor(JobRequisition $record): ?string
    {
        $slug = $record->post?->slug;

        if (blank($slug)) {
            return null;
        }

        return CandidateJobRequisitionResource::getUrl(
            name: 'view',
            parameters: ['record' => $slug],
            panel: FilamentPanel::App->value,
        );
    }

    private static function clipboardJs(JobRequisition $record): string
    {
        $url = json_encode(self::shareUrlFor($record), JSON_THROW_ON_ERROR);
        $message = json_encode(
            __('panel-organization::filament.actions.copy_share_link.notification_copied'),
            JSON_THROW_ON_ERROR,
        );

        return <<<JS
            if ({$url}) {
                window.navigator.clipboard.writeText({$url});
                new FilamentNotification().title({$message}).success().send();
            }
            JS;
    }

    public static function getDefaultName(): ?string
    {
        return 'copyShareLink';
    }
}
```

- [ ] **Step 5: Rodar e ver passar**

Run: `php artisan test --compact --filter='builds the candidate detail URL|returns null when the requisition has no job posting'`
Expected: PASS (2 passed).

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Actions/CopyJobShareLinkAction.php \
        app-modules/panel-organization/lang/en/filament.php \
        app-modules/panel-organization/lang/pt_BR/filament.php \
        app-modules/panel-organization/tests/Feature/JobRequisition/CopyJobShareLinkTest.php
git commit -m "feat(recruitment): action de copiar link de vaga com geração de URL isolada (#179)"
```

---

## Task 2: Registrar a action na tabela + testes de presença/estado

**Files:**
- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Tables/JobRequisitionsTable.php`
- Test: `app-modules/panel-organization/tests/Feature/JobRequisition/CopyJobShareLinkTest.php`

- [ ] **Step 1: Escrever os testes falhos da tabela**

Acrescentar ao final de `CopyJobShareLinkTest.php`:

```php
it('shows the copy share link action enabled when the job has a posting', function (): void {
    $requisition = ($this->makeRequisition)();
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    Livewire\Livewire::test(
        He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\ListJobRequisitions::class
    )
        ->assertActionEnabled(
            Filament\Actions\Testing\TestAction::make('copyShareLink')->table($requisition)
        );
});

it('keeps the copy share link action enabled for internal jobs that have a posting', function (): void {
    $requisition = ($this->makeRequisition)(['is_internal_only' => true]);
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    Livewire\Livewire::test(
        He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\ListJobRequisitions::class
    )
        ->assertActionEnabled(
            Filament\Actions\Testing\TestAction::make('copyShareLink')->table($requisition)
        );
});

it('disables the copy share link action when the job has no posting', function (): void {
    $requisition = ($this->makeRequisition)();

    Livewire\Livewire::test(
        He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\ListJobRequisitions::class
    )
        ->assertActionDisabled(
            Filament\Actions\Testing\TestAction::make('copyShareLink')->table($requisition)
        );
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `php artisan test --compact app-modules/panel-organization/tests/Feature/JobRequisition/CopyJobShareLinkTest.php`
Expected: FAIL nos 3 novos casos — action `copyShareLink` não existe na tabela (`assertActionExists`-style failure).

- [ ] **Step 3: Registrar a action no `ActionGroup` da tabela**

Em `JobRequisitionsTable.php`, adicionar o import no topo (junto dos outros `use He4rt\Organization\...\Actions\...`):

```php
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\CopyJobShareLinkAction;
```

E no `->recordActions([ ActionGroup::make([ ... ]) ])` (linhas 118-128), inserir a action antes de `DuplicateJobRequisitionAction::make()`:

```php
->recordActions([
    ActionGroup::make([
        EditAction::make(),
        Action::make('kanban')
            ->label(__('panel-organization::filament.tables.kanban'))
            ->icon(Heroicon::OutlinedViewColumns)
            ->url(fn (JobRequisition $record): string => JobRequisitionResource::getUrl('kanban',
                ['record' => $record->id])),
        CopyJobShareLinkAction::make(),
        DuplicateJobRequisitionAction::make(),
    ]),
])
```

- [ ] **Step 4: Rodar e ver passar**

Run: `php artisan test --compact app-modules/panel-organization/tests/Feature/JobRequisition/CopyJobShareLinkTest.php`
Expected: PASS (todos os casos do arquivo).

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Tables/JobRequisitionsTable.php \
        app-modules/panel-organization/tests/Feature/JobRequisition/CopyJobShareLinkTest.php
git commit -m "feat(recruitment): registra action de copiar link na tabela de vagas da org (#179)"
```

---

## Task 3: Registrar a action no header da View + teste

**Files:**
- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Pages/ViewJobRequisition.php`
- Test: `app-modules/panel-organization/tests/Feature/JobRequisition/CopyJobShareLinkTest.php`

- [ ] **Step 1: Escrever o teste falho do header da View**

Acrescentar ao final de `CopyJobShareLinkTest.php`:

```php
it('shows the copy share link action in the view page header', function (): void {
    $requisition = ($this->makeRequisition)();
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    Livewire\Livewire::test(
        He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\ViewJobRequisition::class,
        ['record' => $requisition->getKey()]
    )
        ->assertActionExists('copyShareLink')
        ->assertActionEnabled('copyShareLink');
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `php artisan test --compact --filter='shows the copy share link action in the view page header'`
Expected: FAIL — action `copyShareLink` não existe no header da View.

- [ ] **Step 3: Registrar a action no header**

Em `ViewJobRequisition.php`, adicionar o import:

```php
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\CopyJobShareLinkAction;
```

E ajustar `getHeaderActions()` (linhas 32-37):

```php
protected function getHeaderActions(): array
{
    return [
        CopyJobShareLinkAction::make(),
        DuplicateJobRequisitionAction::make(),
    ];
}
```

- [ ] **Step 4: Rodar e ver passar**

Run: `php artisan test --compact app-modules/panel-organization/tests/Feature/JobRequisition/CopyJobShareLinkTest.php`
Expected: PASS (todos).

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Pages/ViewJobRequisition.php \
        app-modules/panel-organization/tests/Feature/JobRequisition/CopyJobShareLinkTest.php
git commit -m "feat(recruitment): adiciona action de copiar link no header da view da vaga (#179)"
```

---

## Task 4: Parte B — esconder o share button do candidato em vaga interna

**Files:**
- Modify: `app-modules/panel-app/resources/views/components/jobs/share-job-button.blade.php`
- Test: `app-modules/panel-app/tests/Feature/Filament/JobRequisitions/JobRequisitionPagesTest.php`

- [ ] **Step 1: Escrever os testes falhos de visibilidade do share button**

Em `JobRequisitionPagesTest.php`, adicionar um novo `describe` block após o bloco `ViewJobRequisition Page` (antes do fechamento do arquivo). O aria-label do botão é `__('panel-app::filament.components.share_button.share_job')`:

```php
describe('ViewJobRequisition Page — Share button visibility', function (): void {
    it('shows the share button for public jobs', function (): void {
        livewire(ViewJobRequisition::class, ['record' => $this->jobPosting->slug])
            ->assertOk()
            ->assertSee(__('panel-app::filament.components.share_button.share_job'));
    });

    it('hides the share button for internal-only jobs', function (): void {
        $internalRequisition = JobRequisition::factory()
            ->for($this->team)
            ->for($this->department)
            ->for($this->recruiter, 'recruiter')
            ->for($this->user, 'createdBy')
            ->create(['is_confidential' => false, 'is_internal_only' => true]);

        $internalPosting = JobPosting::factory()
            ->for($internalRequisition, 'jobRequisition')
            ->create(['slug' => 'internal-share-'.str()->uuid()]);

        livewire(ViewJobRequisition::class, ['record' => $internalPosting->slug])
            ->assertOk()
            ->assertDontSee(__('panel-app::filament.components.share_button.share_job'));
    });
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `php artisan test --compact --filter='hides the share button for internal-only jobs'`
Expected: FAIL — o aria-label aparece mesmo para vaga interna (botão renderizado).

- [ ] **Step 3: Adicionar o guard no componente Blade**

Em `share-job-button.blade.php`, envolver TODO o markup (do bloco `@php ... @endphp` que monta `$jobUrl`/`$jobTitle` até o `</div>` final) com `@if (! $job->is_internal_only) ... @endif`. O cabeçalho `@php use ...; @endphp` e `@props([...])` permanecem no topo, fora do `@if`. Resultado:

```blade
@php
    use Filament\Support\Icons\Heroicon;
    use Illuminate\Support\Js;
@endphp

@props([
    'job',
    'size' => 'sm',
])

@if (! $job->is_internal_only)
    @php
        /** @var \He4rt\Recruitment\Requisitions\Models\JobRequisition $job */
        $jobUrl = $job->post ? He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource::getUrl('view', ['record' => $job->post->slug]) : '#';
        $jobTitle = $job->post?->title ?? 'Vaga';
    @endphp

    <div
        x-data="{ copied: false }"
        x-on:link-copied.stop="
            copied = true
            setTimeout(() => (copied = false), 2000)
        "
        class="relative"
    >
        <x-he4rt::button
            variant="outline"
            icon="heroicon-o-clipboard-document-check"
            :size="$size"
            class="flex-1 px-4 py-2"
            x-on:click.stop.prevent="
                    const url = {{ Js::from($jobUrl) }};
                    const title = {{ Js::from($jobTitle) }};
                    if (navigator.share) {
                        await navigator.share({ title, url });
                    } else {
                        await navigator.clipboard.writeText(url);
                        $dispatch('link-copied');
                    }
                "
            aria-label="{{ __('panel-app::filament.components.share_button.share_job') }}"
        />

        {{-- Tooltip (only for icon-only variant) --}}
        <span
            x-show="copied"
            x-cloak
            x-transition.opacity
            class="bg-surface-primary dark:bg-surface-primary-dark border-outline-light dark:border-outline-dark text-text-high pointer-events-none absolute -bottom-8 left-1/2 -translate-x-1/2 rounded-md border px-2 py-1 text-xs whitespace-nowrap shadow"
        >
            {{ __('panel-app::filament.components.share_button.copied') }}
        </span>
    </div>
@endif
```

- [ ] **Step 4: Rodar e ver passar**

Run: `php artisan test --compact app-modules/panel-app/tests/Feature/Filament/JobRequisitions/JobRequisitionPagesTest.php`
Expected: PASS (incluindo os 2 novos casos).

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/panel-app/resources/views/components/jobs/share-job-button.blade.php \
        app-modules/panel-app/tests/Feature/Filament/JobRequisitions/JobRequisitionPagesTest.php
git commit -m "feat(recruitment): esconde botão de compartilhar do candidato em vaga interna (#179)"
```

---

## Task 5: Suíte completa dos módulos tocados + verificação manual

- [ ] **Step 1: Rodar a suíte dos dois módulos**

Run:
```bash
php artisan test --compact app-modules/panel-organization/tests app-modules/panel-app/tests
```
Expected: PASS (sem regressões).

- [ ] **Step 2: Verificação manual da cópia (não coberta por teste automatizado)**

Como `actionJs()` roda no browser, a escrita no clipboard e a notificação **não** são observáveis em teste server-side. Verificar manualmente (ou via skill `/run`/browser): no painel da org, abrir uma vaga com anúncio, acionar "Copiar link de compartilhamento" e confirmar (a) notificação de sucesso e (b) que o conteúdo colado é a URL `/vagas/{slug}` que abre a página de detalhe do candidato. Confirmar também que `FilamentNotification` está disponível no escopo JS do painel; se não estiver, a cópia ainda ocorre (writeText vem antes), mas a notificação não — nesse caso, trocar por `window.dispatchEvent` + listener, ou `$tooltip`/`alert` mínimo.

- [ ] **Step 3: Confirmar tooltip de item desabilitado no dropdown**

Para uma vaga **sem anúncio** na listagem da org, abrir o menu de ações e confirmar que o item "Copiar link de compartilhamento" aparece desabilitado com o tooltip explicativo. Se o tooltip não renderizar bem dentro do `ActionGroup` (dropdown), o header da View já garante a descoberta com tooltip — registrar a limitação no PR.

---

## Notas de execução

- **Worktree:** já criada em `.claude/worktrees/feat+share-internal-job-link-179` (branch `feat/share-internal-job-link-179`).
- **Pint:** o hook `pre-commit` do Husky está sem permissão de execução nesta worktree, então rodar `vendor/bin/pint --dirty --format agent` manualmente antes de cada commit (já incluído nos passos).
- **Sem co-autoria** nos commits (regra do projeto).
