---
type: plan
title: 'Espaço de candidaturas do recrutador — plano de implementação'
module: panel-organization, applications, recruitment, screening, feedback
status: in_progress
date: 2026-09-02
author: Clintonrocha98
related:
    spec: specs/2026-09-02-espaco-de-candidaturas-do-recrutador
---

# Espaço de candidaturas do recrutador — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Substituir a tabela Filament de candidaturas do painel `organization` por um espaço Livewire: panorama de vagas em cartões → fila de candidatos com prévia, com veredito da pergunta eliminatória e "visto pelo time".

**Architecture:** Consultas e regras ficam nos módulos de domínio (`applications`, `recruitment`, `screening`, `feedback`) como scopes `#[Scope]`, Actions `execute()` e um DTO; o componente Livewire `ApplicationsWorkspace` (módulo `panel-organization`) só compõe scopes, guarda estado (`#[Url]` para a vaga) e renderiza Blade + Tailwind. `ViewApplication` registra o visto via Action. Referência visual: `docs.local/prototype/views/workspace/variant-e.blade.php` (fora do git; copiar markup, nunca o arquivo).

**Tech Stack:** PHP 8.4, Laravel 12, Filament v5, Livewire v4 (`WithPagination`, `#[Url]`, `#[Computed]`), Pest v4, Tailwind v4 (tema `resources/css/filament/organization/theme.css`), PostgreSQL.

**Spec:** `docs/specs/2026-09-02-espaco-de-candidaturas-do-recrutador.md`

## Global Constraints

- Toda string visível ao usuário via `__()` com chaves em **en e pt_BR** (`panel-organization/lang/{en,pt_BR}/workspace.php`; labels de enum em `enums.php` do módulo do enum). Nunca texto fixo em Blade ou PHP.
- **Sem comentários no código**; única exceção: docstring de uma linha em classe/método. PHPDoc de tipos (`@property`, `@return`) é permitido e obrigatório onde indicado.
- Domínio: Actions `final class X { public function execute(...) }`; DTO `final readonly class`; scopes Laravel 12 `#[Scope] protected function nome(Builder $query, ...): Builder`; nunca Repository/Service.
- Mudança de schema exige atualizar o bloco `@property` do Model no mesmo commit e declarar `#[UseFactory(...)]`.
- Apresentação não contém SQL nem regra de domínio; só compõe scopes/Actions e renderiza.
- Classes Tailwind aparecem apenas como literais em arquivos `.blade.php` sob `app-modules/panel-organization/resources/views/` (é o que o `@source` do tema compila).
- Status de candidatura em consultas sempre via `ApplicationStatusEnum`/`ApplicationStatusGroup`, nunca strings soltas.
- Testes: rodar só o arquivo/pasta tocado com `./vendor/bin/pest <caminho>` (sem `--parallel`). Formatar com `./vendor/bin/pint --dirty --format agent`. PHPStan por caminho: `./vendor/bin/phpstan analyse <arquivos ou pastas> --no-progress`.
- Commits em Conventional Commits, em inglês, **sem** `Co-Authored-By`. Nunca adicionar `docs.local/` ao git.
- Namespace por módulo: `He4rt\Applications`, `He4rt\Recruitment`, `He4rt\Screening`, `He4rt\Feedback`, `He4rt\Organization` (diretório `panel-organization`).

---

## Mapa de arquivos

| Arquivo                                                                                                                                                                                                                 | Responsabilidade                                    | Task |
| ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------- | ---- |
| `app-modules/applications/src/Enums/ApplicationStatusGroup.php`                                                                                                                                                         | agrupa `ApplicationStatusEnum` em 4 grupos          | 1    |
| `app-modules/applications/src/Enums/ScreeningVerdictFilter.php`, `SeenFilter.php`, `ApplicationListSort.php`                                                                                                            | filtros/ordenação da fila                           | 1    |
| `app-modules/feedback/src/Enums/EvaluationRatingEnum.php` (+ `score()`)                                                                                                                                                 | nota numérica 1–5                                   | 1    |
| `app-modules/applications/database/migrations/2026_09_02_000001_create_application_views_table.php`                                                                                                                     | tabela `application_views`                          | 2    |
| `app-modules/applications/src/Models/ApplicationView.php`, `database/factories/ApplicationViewFactory.php`                                                                                                              | visto pelo time                                     | 2    |
| `app-modules/applications/src/Actions/MarkApplicationAsViewed.php`                                                                                                                                                      | grava o primeiro visto (idempotente)                | 2    |
| `app-modules/applications/docs/adr/0001-visto-pelo-time-na-candidatura.md`                                                                                                                                              | decisão                                             | 2    |
| `app-modules/panel-organization/.../Pages/ViewApplication.php`                                                                                                                                                          | marca visto ao abrir                                | 3    |
| `app-modules/applications/src/Models/Application.php`                                                                                                                                                                   | scopes de listagem, tempo na etapa, veredito, média | 2, 4 |
| `app-modules/recruitment/src/Requisitions/Enums/RequisitionOverviewSort.php`                                                                                                                                            | ordenação do panorama                               | 5    |
| `app-modules/recruitment/src/Requisitions/Models/JobRequisition.php`                                                                                                                                                    | scopes do panorama                                  | 5    |
| `app-modules/applications/src/Actions/BuildRequisitionFunnels.php`, `BuildRequisitionApplicationStats.php`, `src/DTOs/RequisitionApplicationStats.php`                                                                  | agregados por vaga                                  | 5    |
| `app-modules/screening/src/Actions/CountKnockoutQuestionsByRequisition.php`                                                                                                                                             | perguntas eliminatórias por vaga (vaga + etapas)    | 5    |
| `app-modules/panel-organization/src/Livewire/Applications/ApplicationsWorkspace.php`                                                                                                                                    | estado + composição de consultas                    | 6    |
| `app-modules/panel-organization/resources/views/livewire/applications/workspace.blade.php` + `workspace/{overview,requisition}.blade.php` + `workspace/partials/{switcher,header}.blade.php`                            | panorama e cabeçalho da vaga                        | 6    |
| `app-modules/panel-organization/lang/{en,pt_BR}/workspace.php`                                                                                                                                                          | strings                                             | 6    |
| `app-modules/panel-organization/resources/views/livewire/applications/workspace/partials/{queue,preview}.blade.php` + `resources/views/components/workspace/{knockout-badge,rating-dots,stage-bar,stage-dot}.blade.php` | fila e prévia                                       | 7    |
| `app-modules/panel-organization/.../Pages/ListApplications.php`, `ApplicationResource.php`                                                                                                                              | troca do conteúdo; remoção da tabela                | 8    |

---

### Task 1: Enums de grupo de status, filtros, ordenação e nota da avaliação

**Files:**

- Create: `app-modules/applications/src/Enums/ApplicationStatusGroup.php`
- Create: `app-modules/applications/src/Enums/ScreeningVerdictFilter.php`
- Create: `app-modules/applications/src/Enums/SeenFilter.php`
- Create: `app-modules/applications/src/Enums/ApplicationListSort.php`
- Modify: `app-modules/applications/lang/en/enums.php`, `app-modules/applications/lang/pt_BR/enums.php` (novas chaves no fim do array)
- Modify: `app-modules/feedback/src/Enums/EvaluationRatingEnum.php` (método `score()`)
- Test: `app-modules/applications/tests/Unit/Enums/ApplicationStatusGroupTest.php`
- Test: `app-modules/feedback/tests/Unit/EvaluationRatingEnumScoreTest.php`

**Interfaces:**

- Produces: `ApplicationStatusGroup::{New,Active,Offer,Closed}` com `fromStatus(ApplicationStatusEnum): self`, `statuses(): array<int, ApplicationStatusEnum>`, `values(): array<int, string>`; `ScreeningVerdictFilter::{All,Passed,Failed,Unanswered}`; `SeenFilter::{All,Unseen,Seen}`; `ApplicationListSort::{Attention,DaysInStage,Applied,Name,Stage}`; todos `string`-backed e `implements HasLabel`; `EvaluationRatingEnum::score(): int` (StrongNo=1 … StrongYes=5).

- [ ] **Step 1: Escrever os testes**

`app-modules/applications/tests/Unit/Enums/ApplicationStatusGroupTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationListSort;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\ApplicationStatusGroup;
use He4rt\Applications\Enums\ScreeningVerdictFilter;
use He4rt\Applications\Enums\SeenFilter;

it('maps every application status to exactly one group', function (): void {
    foreach (ApplicationStatusEnum::cases() as $status) {
        expect(ApplicationStatusGroup::fromStatus($status))->toBeInstanceOf(ApplicationStatusGroup::class);
    }
});

it('exposes the status values of each group in enum order', function (): void {
    expect(ApplicationStatusGroup::New->values())->toBe(['new', 'in_review'])
        ->and(ApplicationStatusGroup::Active->values())->toBe(['in_progress'])
        ->and(ApplicationStatusGroup::Offer->values())->toBe(['offer_extended', 'offer_accepted', 'hired'])
        ->and(ApplicationStatusGroup::Closed->values())->toBe(['offer_declined', 'rejected', 'withdrawn']);
});

it('has translated labels for every new enum case', function (): void {
    $cases = [
        ...ApplicationStatusGroup::cases(),
        ...ScreeningVerdictFilter::cases(),
        ...SeenFilter::cases(),
        ...ApplicationListSort::cases(),
    ];

    foreach (['en', 'pt_BR'] as $locale) {
        app()->setLocale($locale);

        foreach ($cases as $case) {
            expect($case->getLabel())->not->toContain('::');
        }
    }
});
```

`app-modules/feedback/tests/Unit/EvaluationRatingEnumScoreTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Feedback\Enums\EvaluationRatingEnum;

it('scores ratings from 1 (strong no) to 5 (strong yes)', function (): void {
    expect(EvaluationRatingEnum::StrongNo->score())->toBe(1)
        ->and(EvaluationRatingEnum::No->score())->toBe(2)
        ->and(EvaluationRatingEnum::Maybe->score())->toBe(3)
        ->and(EvaluationRatingEnum::Yes->score())->toBe(4)
        ->and(EvaluationRatingEnum::StrongYes->score())->toBe(5);
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `./vendor/bin/pest app-modules/applications/tests/Unit/Enums app-modules/feedback/tests/Unit/EvaluationRatingEnumScoreTest.php`
Expected: FAIL (classes/métodos inexistentes).

- [ ] **Step 3: Implementar os enums**

`ApplicationStatusGroup.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Contracts\HasLabel;

enum ApplicationStatusGroup: string implements HasLabel
{
    use StringifyEnum;

    case New = 'new';
    case Active = 'active';
    case Offer = 'offer';
    case Closed = 'closed';

    public static function fromStatus(ApplicationStatusEnum $status): self
    {
        return match ($status) {
            ApplicationStatusEnum::New, ApplicationStatusEnum::InReview => self::New,
            ApplicationStatusEnum::InProgress => self::Active,
            ApplicationStatusEnum::OfferExtended, ApplicationStatusEnum::OfferAccepted, ApplicationStatusEnum::Hired => self::Offer,
            ApplicationStatusEnum::Rejected, ApplicationStatusEnum::Withdrawn, ApplicationStatusEnum::OfferDeclined => self::Closed,
        };
    }

    /**
     * @return array<int, ApplicationStatusEnum>
     */
    public function statuses(): array
    {
        return array_values(array_filter(
            ApplicationStatusEnum::cases(),
            fn (ApplicationStatusEnum $status): bool => self::fromStatus($status) === $this,
        ));
    }

    /**
     * @return array<int, string>
     */
    public function values(): array
    {
        return array_map(fn (ApplicationStatusEnum $status): string => $status->value, $this->statuses());
    }

    public function getLabel(): string
    {
        return __('applications::enums.application_status_group.'.$this->value.'.label');
    }
}
```

`ScreeningVerdictFilter.php`, `SeenFilter.php`, `ApplicationListSort.php` seguem o mesmo molde (mesmo `use`, `StringifyEnum`, `getLabel()` com a chave própria):

```php
enum ScreeningVerdictFilter: string implements HasLabel
{
    use StringifyEnum;

    case All = 'all';
    case Passed = 'passed';
    case Failed = 'failed';
    case Unanswered = 'unanswered';

    public function getLabel(): string
    {
        return __('applications::enums.screening_verdict_filter.'.$this->value.'.label');
    }
}

enum SeenFilter: string implements HasLabel
{
    use StringifyEnum;

    case All = 'all';
    case Unseen = 'unseen';
    case Seen = 'seen';

    public function getLabel(): string
    {
        return __('applications::enums.seen_filter.'.$this->value.'.label');
    }
}

enum ApplicationListSort: string implements HasLabel
{
    use StringifyEnum;

    case Attention = 'attention';
    case DaysInStage = 'days_in_stage';
    case Applied = 'applied';
    case Name = 'name';
    case Stage = 'stage';

    public function getLabel(): string
    {
        return __('applications::enums.application_list_sort.'.$this->value.'.label');
    }
}
```

Em `EvaluationRatingEnum` acrescentar:

```php
    public function score(): int
    {
        return match ($this) {
            self::StrongNo => 1,
            self::No => 2,
            self::Maybe => 3,
            self::Yes => 4,
            self::StrongYes => 5,
        };
    }
```

- [ ] **Step 4: Traduções**

Acrescentar ao fim do array em `applications/lang/en/enums.php`:

```php
    'application_status_group' => [
        'new' => ['label' => 'New'],
        'active' => ['label' => 'In progress'],
        'offer' => ['label' => 'Offer and hiring'],
        'closed' => ['label' => 'Closed'],
    ],
    'screening_verdict_filter' => [
        'all' => ['label' => 'All screening results'],
        'passed' => ['label' => 'Passed the knockout questions'],
        'failed' => ['label' => 'Failed the knockout questions'],
        'unanswered' => ['label' => 'No screening answers'],
    ],
    'seen_filter' => [
        'all' => ['label' => 'Seen and unseen'],
        'unseen' => ['label' => 'Unseen'],
        'seen' => ['label' => 'Already seen'],
    ],
    'application_list_sort' => [
        'attention' => ['label' => 'Attention'],
        'days_in_stage' => ['label' => 'Longest in stage'],
        'applied' => ['label' => 'Most recent application'],
        'name' => ['label' => 'Name'],
        'stage' => ['label' => 'Furthest stage'],
    ],
```

E em `pt_BR/enums.php`:

```php
    'application_status_group' => [
        'new' => ['label' => 'Novas'],
        'active' => ['label' => 'Em processo'],
        'offer' => ['label' => 'Oferta e contratação'],
        'closed' => ['label' => 'Encerradas'],
    ],
    'screening_verdict_filter' => [
        'all' => ['label' => 'Toda a triagem'],
        'passed' => ['label' => 'Aprovados na eliminatória'],
        'failed' => ['label' => 'Reprovados na eliminatória'],
        'unanswered' => ['label' => 'Sem respostas'],
    ],
    'seen_filter' => [
        'all' => ['label' => 'Vistos e não vistos'],
        'unseen' => ['label' => 'Não vistos'],
        'seen' => ['label' => 'Já vistos'],
    ],
    'application_list_sort' => [
        'attention' => ['label' => 'Atenção'],
        'days_in_stage' => ['label' => 'Mais tempo na etapa'],
        'applied' => ['label' => 'Inscrição mais recente'],
        'name' => ['label' => 'Nome'],
        'stage' => ['label' => 'Etapa mais avançada'],
    ],
```

- [ ] **Step 5: Rodar e ver passar**

Run: `./vendor/bin/pest app-modules/applications/tests/Unit/Enums app-modules/feedback/tests/Unit/EvaluationRatingEnumScoreTest.php`
Expected: PASS. Depois `./vendor/bin/pint --dirty --format agent` e `./vendor/bin/phpstan analyse app-modules/applications/src/Enums app-modules/feedback/src/Enums --no-progress`.

- [ ] **Step 6: Commit**

```bash
git add app-modules/applications/src/Enums app-modules/applications/lang app-modules/applications/tests/Unit/Enums app-modules/feedback/src/Enums/EvaluationRatingEnum.php app-modules/feedback/tests/Unit/EvaluationRatingEnumScoreTest.php
git commit -m "feat(applications): add status group, screening verdict, seen and sort enums"
```

---

### Task 2: Tabela `application_views`, modelo, factory, Action e relação em `Application`

**Files:**

- Create: `app-modules/applications/database/migrations/2026_09_02_000001_create_application_views_table.php`
- Create: `app-modules/applications/src/Models/ApplicationView.php`
- Create: `app-modules/applications/database/factories/ApplicationViewFactory.php`
- Create: `app-modules/applications/src/Actions/MarkApplicationAsViewed.php`
- Create: `app-modules/applications/docs/adr/0001-visto-pelo-time-na-candidatura.md`
- Modify: `app-modules/applications/src/Models/Application.php` (relação `teamView`, scopes `unseenByTeam`/`seenByTeam`, método `isSeenByTeam()`, PHPDoc)
- Modify: `app-modules/applications/src/ApplicationsServiceProvider.php` (morphMap `'application_views' => ApplicationView::class`)
- Test: `app-modules/applications/tests/Feature/Actions/MarkApplicationAsViewedTest.php`

**Interfaces:**

- Consumes: nada de tasks anteriores.
- Produces: `ApplicationView` (`$table = 'application_views'`, props `id, team_id, application_id, viewed_by, viewed_at`); `Application::teamView(): HasOne<ApplicationView>`; scopes `Application::query()->unseenByTeam()` / `->seenByTeam()`; `Application::isSeenByTeam(): bool`; `MarkApplicationAsViewed::execute(Application $application, User $viewer): ApplicationView`.

- [ ] **Step 1: Escrever os testes**

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Actions\MarkApplicationAsViewed;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Users\User;

use function Pest\Laravel\assertDatabaseCount;

it('records the first view with the viewer and the application team', function (): void {
    $application = Application::factory()->create();
    $viewer = User::factory()->create();

    $view = resolve(MarkApplicationAsViewed::class)->execute($application, $viewer);

    expect($view)->toBeInstanceOf(ApplicationView::class)
        ->and($view->application_id)->toBe($application->getKey())
        ->and($view->team_id)->toBe($application->team_id)
        ->and($view->viewed_by)->toBe($viewer->getKey())
        ->and($view->viewed_at)->not->toBeNull();

    assertDatabaseCount(ApplicationView::class, 1);
});

it('is idempotent and keeps the first viewer', function (): void {
    $application = Application::factory()->create();
    $first = User::factory()->create();
    $second = User::factory()->create();

    resolve(MarkApplicationAsViewed::class)->execute($application, $first);
    $view = resolve(MarkApplicationAsViewed::class)->execute($application, $second);

    assertDatabaseCount(ApplicationView::class, 1);
    expect($view->viewed_by)->toBe($first->getKey());
});

it('exposes seen state through the relation and the scopes', function (): void {
    $seen = Application::factory()->create();
    $unseen = Application::factory()->create();
    resolve(MarkApplicationAsViewed::class)->execute($seen, User::factory()->create());

    expect($seen->fresh()->isSeenByTeam())->toBeTrue()
        ->and($unseen->fresh()->isSeenByTeam())->toBeFalse()
        ->and(Application::query()->seenByTeam()->pluck('id')->all())->toBe([$seen->getKey()])
        ->and(Application::query()->unseenByTeam()->pluck('id')->all())->toBe([$unseen->getKey()]);
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `./vendor/bin/pest app-modules/applications/tests/Feature/Actions/MarkApplicationAsViewedTest.php`
Expected: FAIL.

- [ ] **Step 3: Migration**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_views', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('application_id')->unique()->constrained('applications')->cascadeOnDelete();
            $table->foreignUuid('viewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('viewed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_views');
    }
};
```

Antes de escrever, confira em `app-modules/applications/database/migrations/2026_01_15_200001_create_applications_table.php` como a tabela `applications` declara `id` e as FKs, e siga exatamente o mesmo estilo (uuid / foreignUuid).

- [ ] **Step 4: Model, factory, Action, relação, morphMap**

`ApplicationView.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Models;

use App\Models\BaseModel;
use He4rt\Applications\Database\Factories\ApplicationViewFactory;
use He4rt\Teams\Concerns\BelongsToTeam;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $team_id
 * @property string $application_id
 * @property string|null $viewed_by
 * @property Carbon $viewed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Application $application
 * @property-read User|null $viewer
 *
 * @extends BaseModel<ApplicationViewFactory>
 */
#[UseFactory(ApplicationViewFactory::class)]
class ApplicationView extends BaseModel
{
    use BelongsToTeam;

    protected $table = 'application_views';

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewed_by');
    }

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }
}
```

`ApplicationViewFactory.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Database\Factories;

use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationView>
 */
class ApplicationViewFactory extends Factory
{
    protected $model = ApplicationView::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'application_id' => Application::factory(),
            'viewed_by' => User::factory(),
            'viewed_at' => now(),
        ];
    }

    public function forApplication(Application $application): static
    {
        return $this->state(fn (): array => [
            'application_id' => $application->getKey(),
            'team_id' => $application->team_id,
        ]);
    }
}
```

`MarkApplicationAsViewed.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Actions;

use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Users\User;

/** Registra a primeira visualização da candidatura pelo time; chamadas seguintes devolvem o registro existente. */
final class MarkApplicationAsViewed
{
    public function execute(Application $application, User $viewer): ApplicationView
    {
        return ApplicationView::query()->firstOrCreate(
            ['application_id' => $application->getKey()],
            [
                'team_id' => $application->team_id,
                'viewed_by' => $viewer->getKey(),
                'viewed_at' => now(),
            ],
        );
    }
}
```

Em `Application.php`: adicionar `use He4rt\Applications\Enums\ApplicationStatusGroup;`, `use Illuminate\Database\Eloquent\Attributes\Scope;`, `use Illuminate\Database\Eloquent\Builder;`, `use Illuminate\Database\Eloquent\Relations\HasOne;`; no PHPDoc `@property-read ApplicationView|null $teamView`; e:

```php
    /**
     * @return HasOne<ApplicationView, $this>
     */
    public function teamView(): HasOne
    {
        return $this->hasOne(ApplicationView::class);
    }

    public function isSeenByTeam(): bool
    {
        if ($this->relationLoaded('teamView')) {
            return $this->teamView !== null;
        }

        return $this->teamView()->exists();
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function unseenByTeam(Builder $query): Builder
    {
        return $query->whereDoesntHave('teamView');
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function seenByTeam(Builder $query): Builder
    {
        return $query->whereHas('teamView');
    }
```

`ApplicationsServiceProvider::boot()` — acrescentar `'application_views' => ApplicationView::class` ao `Relation::morphMap`.

- [ ] **Step 5: ADR**

`app-modules/applications/docs/adr/0001-visto-pelo-time-na-candidatura.md`:

```markdown
---
type: adr
title: 'Visto pelo time em candidaturas'
module: applications
status: accepted
date: 2026-09-02
author: Clintonrocha98
related:
    spec: ../../../../docs/specs/2026-09-02-espaco-de-candidaturas-do-recrutador
---

# 0001 — Visto pelo time em candidaturas

## Contexto

O espaço de candidaturas do recrutador precisa mostrar se alguém já olhou a candidatura. Não existia
registro de visualização.

## Decisão

Uma tabela `application_views` com **uma linha por candidatura** (`application_id` único), guardando
`viewed_by` (quem viu primeiro) e `viewed_at`. O único gatilho é abrir a candidatura completa
(`ViewApplication`), via `MarkApplicationAsViewed`, idempotente. A prévia na fila **não** marca.

## Consequências

- "Não visto" significa que ninguém do time abriu; não há "eu já vi".
- Contagens por vaga usam `whereDoesntHave('teamView')` sobre candidaturas não encerradas.
- Para evoluir para visto por usuário, basta trocar a unicidade para `application_id + viewed_by` e
  ajustar os scopes; o gatilho permanece.
```

- [ ] **Step 6: Migrar o banco de teste implicitamente pelo Pest e rodar**

Run: `./vendor/bin/pest app-modules/applications/tests/Feature/Actions/MarkApplicationAsViewedTest.php`
Expected: PASS. Depois `./vendor/bin/pint --dirty --format agent` e `./vendor/bin/phpstan analyse app-modules/applications/src --no-progress`.

- [ ] **Step 7: Commit**

```bash
git add app-modules/applications
git commit -m "feat(applications): record the first team view of an application"
```

---

### Task 3: `ViewApplication` marca a candidatura como vista

**Files:**

- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Pages/ViewApplication.php`
- Test: `app-modules/panel-organization/tests/Feature/Filament/Application/ViewApplicationMarksViewedTest.php`

**Interfaces:**

- Consumes: `MarkApplicationAsViewed::execute(Application, User): ApplicationView` (Task 2).

- [ ] **Step 1: Escrever o teste** (o `beforeEach` copia o de `ViewApplicationTest.php` do mesmo diretório — abra-o e reproduza a criação de requisição, time, recrutador `SuperAdmin`, `filament()->setCurrentPanel('organization')`, `filament()->setTenant($team)`)

```php
<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Candidates\Models\Candidate;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->requisition = JobRequisition::factory()->create();
    JobPosting::factory()->for($this->requisition, 'jobRequisition')->createOne();
    $this->team = $this->requisition->team;
    $this->recruiter = Recruiter::factory()->for($this->team, 'team')->create();
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);

    $this->application = Application::factory()
        ->recycle($this->team)
        ->for($this->requisition, 'requisition')
        ->for(Candidate::factory()->create(), 'candidate')
        ->create();

    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->team);
});

it('records the team view when an authorized recruiter opens the application', function (): void {
    actingAs($this->recruiter->user);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])->assertOk();
    livewire(ViewApplication::class, ['record' => $this->application->getKey()])->assertOk();

    assertDatabaseCount(ApplicationView::class, 1);
    expect(ApplicationView::query()->sole()->viewed_by)->toBe($this->recruiter->user->getKey());
});

it('does not record a view when the user is not authorized', function (): void {
    actingAs(User::factory()->create());

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])->assertForbidden();

    assertDatabaseCount(ApplicationView::class, 0);
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `./vendor/bin/pest app-modules/panel-organization/tests/Feature/Filament/Application/ViewApplicationMarksViewedTest.php`
Expected: o primeiro teste FALHA (0 registros).

- [ ] **Step 3: Implementar**

Em `ViewApplication.php`:

```php
use He4rt\Applications\Actions\MarkApplicationAsViewed;
use He4rt\Applications\Models\Application;
use He4rt\Users\User;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $application = $this->getRecord();
        $user = auth()->user();

        if ($application instanceof Application && $user instanceof User) {
            resolve(MarkApplicationAsViewed::class)->execute($application, $user);
        }
    }
```

- [ ] **Step 4: Rodar e ver passar**

Run: `./vendor/bin/pest app-modules/panel-organization/tests/Feature/Filament/Application/ViewApplicationMarksViewedTest.php app-modules/panel-organization/tests/Feature/Filament/Application/ViewApplicationTest.php`
Expected: PASS. `./vendor/bin/pint --dirty --format agent`; `./vendor/bin/phpstan analyse app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Pages/ViewApplication.php --no-progress`.

- [ ] **Step 5: Commit**

```bash
git add app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Pages/ViewApplication.php app-modules/panel-organization/tests/Feature/Filament/Application/ViewApplicationMarksViewedTest.php
git commit -m "feat(panel-organization): mark the application as viewed by the team when opened"
```

---

### Task 4: Scopes de listagem e sinais derivados em `Application`

**Files:**

- Modify: `app-modules/applications/src/Models/Application.php`
- Test: `app-modules/applications/tests/Feature/Models/ApplicationListingTest.php`

**Interfaces:**

- Consumes: enums da Task 1; `teamView`/scopes da Task 2; `EvaluationRatingEnum::score()`.
- Produces (todos em `Application`): `public const string STAGE_SINCE_SQL`; scopes `withStageSince()`, `searchCandidate(string $term)`, `inStatusGroup(ApplicationStatusGroup $group)`, `withScreeningVerdict(ScreeningVerdictFilter $filter)`, `withSeenState(SeenFilter $filter)`, `withListingCounts()`, `orderForListing(ApplicationListSort $sort)`; métodos `stageSince(): Carbon`, `daysInStage(): int`, `isOverdueInStage(): bool`, `knockoutFailsCount(): int`, `screeningAnswersCount(): int`, `hasFailedKnockout(): bool`, `knockoutResponses(): Collection<int, ScreeningResponse>`, `averageEvaluationScore(): ?float`, `statusGroup(): ApplicationStatusGroup`.

- [ ] **Step 1: Escrever os testes**

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationListSort;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\ApplicationStatusGroup;
use He4rt\Applications\Enums\ScreeningVerdictFilter;
use He4rt\Applications\Enums\SeenFilter;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Candidates\Models\Candidate;
use He4rt\Feedback\Enums\EvaluationRatingEnum;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;
use He4rt\Users\User;

beforeEach(function (): void {
    $this->requisition = JobRequisition::factory()->create();
    $this->team = $this->requisition->team;
    $this->stages = $this->requisition->stages()->orderBy('display_order')->get();

    $this->make = function (ApplicationStatusEnum $status, array $attributes = []): Application {
        $user = User::factory()->create(['name' => $attributes['name'] ?? fake()->name()]);
        unset($attributes['name']);

        return Application::factory()
            ->recycle($this->team)
            ->for($this->requisition, 'requisition')
            ->for(Candidate::factory()->for($user, 'user')->create(), 'candidate')
            ->create(['status' => $status, ...$attributes]);
    };
});

it('filters by status group', function (): void {
    $new = ($this->make)(ApplicationStatusEnum::New);
    $inReview = ($this->make)(ApplicationStatusEnum::InReview);
    ($this->make)(ApplicationStatusEnum::Rejected);

    expect(Application::query()->inStatusGroup(ApplicationStatusGroup::New)->pluck('id')->sort()->values()->all())
        ->toBe(collect([$new->getKey(), $inReview->getKey()])->sort()->values()->all());
});

it('filters by screening verdict', function (): void {
    $question = ScreeningQuestion::factory()->yesNo()->knockout()->create([
        'team_id' => $this->team->getKey(),
        'screenable_type' => $this->requisition->getMorphClass(),
        'screenable_id' => $this->requisition->getKey(),
    ]);
    $passed = ($this->make)(ApplicationStatusEnum::New);
    $failed = ($this->make)(ApplicationStatusEnum::New);
    $unanswered = ($this->make)(ApplicationStatusEnum::New);

    ScreeningResponse::factory()->yesNoResponse(true)->create(['team_id' => $this->team->getKey(), 'application_id' => $passed->getKey(), 'question_id' => $question->getKey()]);
    ScreeningResponse::factory()->yesNoResponse(false)->knockoutFailed()->create(['team_id' => $this->team->getKey(), 'application_id' => $failed->getKey(), 'question_id' => $question->getKey()]);

    expect(Application::query()->withScreeningVerdict(ScreeningVerdictFilter::Passed)->pluck('id')->all())->toBe([$passed->getKey()])
        ->and(Application::query()->withScreeningVerdict(ScreeningVerdictFilter::Failed)->pluck('id')->all())->toBe([$failed->getKey()])
        ->and(Application::query()->withScreeningVerdict(ScreeningVerdictFilter::Unanswered)->pluck('id')->all())->toBe([$unanswered->getKey()])
        ->and(Application::query()->withScreeningVerdict(ScreeningVerdictFilter::All)->count())->toBe(3);

    $loaded = Application::query()->withListingCounts()->with('screeningResponses.question')->findOrFail($failed->getKey());

    expect($loaded->knockoutFailsCount())->toBe(1)
        ->and($loaded->screeningAnswersCount())->toBe(1)
        ->and($loaded->hasFailedKnockout())->toBeTrue()
        ->and($loaded->knockoutResponses()->first()?->question_id)->toBe($question->getKey());
});

it('filters by seen state', function (): void {
    $seen = ($this->make)(ApplicationStatusEnum::New);
    $unseen = ($this->make)(ApplicationStatusEnum::New);
    ApplicationView::factory()->forApplication($seen)->create();

    expect(Application::query()->withSeenState(SeenFilter::Seen)->pluck('id')->all())->toBe([$seen->getKey()])
        ->and(Application::query()->withSeenState(SeenFilter::Unseen)->pluck('id')->all())->toBe([$unseen->getKey()])
        ->and(Application::query()->withSeenState(SeenFilter::All)->count())->toBe(2);
});

it('searches by candidate name, email, headline and tracking code, case-insensitively', function (): void {
    $ana = ($this->make)(ApplicationStatusEnum::New, ['name' => 'Ana Beatriz Souza', 'tracking_code' => 'APP-1234-ZZZZ']);
    ($this->make)(ApplicationStatusEnum::New, ['name' => 'Carlos Lima', 'tracking_code' => 'APP-9999-AAAA']);

    expect(Application::query()->searchCandidate('ana beatriz')->pluck('id')->all())->toBe([$ana->getKey()])
        ->and(Application::query()->searchCandidate('1234-zzzz')->pluck('id')->all())->toBe([$ana->getKey()])
        ->and(Application::query()->searchCandidate($ana->candidate->user->email)->pluck('id')->all())->toBe([$ana->getKey()]);
});

it('orders by attention then by longest time in stage', function (): void {
    $inProgress = ($this->make)(ApplicationStatusEnum::InProgress, ['created_at' => now()->subDays(30)]);
    $newRecent = ($this->make)(ApplicationStatusEnum::New, ['created_at' => now()->subDays(2)]);
    $newOld = ($this->make)(ApplicationStatusEnum::New, ['created_at' => now()->subDays(10)]);

    expect(Application::query()->withStageSince()->orderForListing(ApplicationListSort::Attention)->pluck('id')->all())
        ->toBe([$newOld->getKey(), $newRecent->getKey(), $inProgress->getKey()]);
});

it('measures time in stage from the last movement or the application date', function (): void {
    $moved = ($this->make)(ApplicationStatusEnum::InProgress, ['created_at' => now()->subDays(20)]);
    $fresh = ($this->make)(ApplicationStatusEnum::New, ['created_at' => now()->subDays(20)]);

    ApplicationStageHistory::factory()->create([
        'team_id' => $this->team->getKey(),
        'application_id' => $moved->getKey(),
        'from_stage_id' => $this->stages[0]->getKey(),
        'to_stage_id' => $this->stages[1]->getKey(),
        'moved_by' => User::factory()->create()->getKey(),
        'created_at' => now()->subDays(3),
    ]);
    $moved->update(['current_stage_id' => $this->stages[1]->getKey()]);

    $rows = Application::query()->withStageSince()->orderForListing(ApplicationListSort::DaysInStage)->get();

    expect($rows->first()?->getKey())->toBe($fresh->getKey())
        ->and($rows->firstWhere('id', $moved->getKey())?->daysInStage())->toBe(3)
        ->and($rows->firstWhere('id', $fresh->getKey())?->daysInStage())->toBe(20)
        ->and($moved->fresh()->daysInStage())->toBe(3);
});

it('flags overdue applications against the expected stage duration', function (): void {
    $stage = $this->stages[1];
    $stage->update(['expected_duration_days' => 3]);

    $late = ($this->make)(ApplicationStatusEnum::InProgress, ['created_at' => now()->subDays(10), 'current_stage_id' => $stage->getKey()]);
    $onTime = ($this->make)(ApplicationStatusEnum::InProgress, ['created_at' => now()->subDay(), 'current_stage_id' => $stage->getKey()]);
    $closed = ($this->make)(ApplicationStatusEnum::Rejected, ['created_at' => now()->subDays(10), 'current_stage_id' => $stage->getKey()]);

    expect($late->fresh()->isOverdueInStage())->toBeTrue()
        ->and($onTime->fresh()->isOverdueInStage())->toBeFalse()
        ->and($closed->fresh()->isOverdueInStage())->toBeFalse()
        ->and($late->statusGroup())->toBe(ApplicationStatusGroup::Active);
});

it('averages only submitted evaluations', function (): void {
    $application = ($this->make)(ApplicationStatusEnum::InProgress);

    Evaluation::factory()->submitted()->create(['team_id' => $this->team->getKey(), 'application_id' => $application->getKey(), 'stage_id' => $this->stages[1]->getKey(), 'overall_rating' => EvaluationRatingEnum::Yes]);
    Evaluation::factory()->submitted()->create(['team_id' => $this->team->getKey(), 'application_id' => $application->getKey(), 'stage_id' => $this->stages[1]->getKey(), 'overall_rating' => EvaluationRatingEnum::StrongYes]);
    Evaluation::factory()->draft()->create(['team_id' => $this->team->getKey(), 'application_id' => $application->getKey(), 'stage_id' => $this->stages[1]->getKey(), 'overall_rating' => EvaluationRatingEnum::StrongNo]);

    expect($application->fresh()->averageEvaluationScore())->toBe(4.5)
        ->and(($this->make)(ApplicationStatusEnum::New)->averageEvaluationScore())->toBeNull();
});
```

Observação para o teste de `created_at`: a factory de `Application` sobrescreve `current_stage_id` no `afterCreating` apenas quando ele vem nulo; passar `current_stage_id` explícito é respeitado.

- [ ] **Step 2: Rodar e ver falhar**

Run: `./vendor/bin/pest app-modules/applications/tests/Feature/Models/ApplicationListingTest.php`
Expected: FAIL.

- [ ] **Step 3: Implementar em `Application.php`**

Imports adicionais: `He4rt\Applications\Enums\ApplicationListSort`, `ScreeningVerdictFilter`, `SeenFilter`, `He4rt\Recruitment\Stages\Models\Stage` (já existe), `He4rt\Users\User` (já existe), `Illuminate\Support\Carbon` (já existe).

```php
    public const string STAGE_SINCE_SQL = 'COALESCE((SELECT MAX(h.created_at) FROM application_stage_history h WHERE h.application_id = applications.id), applications.created_at)';

    private const string ATTENTION_ORDER_SQL = "CASE applications.status WHEN 'new' THEN 0 WHEN 'in_review' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'offer_extended' THEN 3 WHEN 'offer_accepted' THEN 4 WHEN 'hired' THEN 5 ELSE 6 END";

    public function statusGroup(): ApplicationStatusGroup
    {
        return ApplicationStatusGroup::fromStatus($this->status);
    }

    public function stageSince(): Carbon
    {
        $selected = $this->getAttribute('stage_since');

        if (filled($selected)) {
            return Carbon::parse((string) $selected);
        }

        return $this->getLastMovement()?->created_at ?? $this->created_at;
    }

    public function daysInStage(): int
    {
        return (int) $this->stageSince()->diffInDays(now());
    }

    public function isOverdueInStage(): bool
    {
        $expected = $this->currentStage->expected_duration_days ?? 0;

        return $expected > 0
            && $this->statusGroup() !== ApplicationStatusGroup::Closed
            && $this->daysInStage() > $expected;
    }

    public function knockoutFailsCount(): int
    {
        $loaded = $this->getAttribute('knockout_fails_count');

        return $loaded !== null
            ? (int) $loaded
            : $this->screeningResponses()->where('is_knockout_fail', true)->count();
    }

    public function screeningAnswersCount(): int
    {
        $loaded = $this->getAttribute('screening_responses_count');

        return $loaded !== null ? (int) $loaded : $this->screeningResponses()->count();
    }

    public function hasFailedKnockout(): bool
    {
        return $this->knockoutFailsCount() > 0;
    }

    /**
     * @return Collection<int, ScreeningResponse>
     */
    public function knockoutResponses(): Collection
    {
        return $this->screeningResponses
            ->filter(fn (ScreeningResponse $response): bool => $response->question->is_knockout)
            ->sortByDesc('is_knockout_fail')
            ->values();
    }

    public function averageEvaluationScore(): ?float
    {
        $submitted = $this->evaluations->whereNotNull('submitted_at');

        if ($submitted->isEmpty()) {
            return null;
        }

        return round((float) $submitted->avg(fn (Evaluation $evaluation): int => $evaluation->overall_rating->score()), 1);
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function withStageSince(Builder $query): Builder
    {
        return $query->select('applications.*')->selectRaw(self::STAGE_SINCE_SQL.' AS stage_since');
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function searchCandidate(Builder $query, string $term): Builder
    {
        $like = '%'.mb_trim($term).'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query
                ->where('applications.tracking_code', 'ilike', $like)
                ->orWhereHas('candidate', fn (Builder $candidate) => $candidate
                    ->where('headline', 'ilike', $like)
                    ->orWhereHas('user', fn (Builder $user) => $user
                        ->where('name', 'ilike', $like)
                        ->orWhere('email', 'ilike', $like)));
        });
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function inStatusGroup(Builder $query, ApplicationStatusGroup $group): Builder
    {
        return $query->whereIn('applications.status', $group->values());
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function withScreeningVerdict(Builder $query, ScreeningVerdictFilter $filter): Builder
    {
        $failed = fn (Builder $responses): Builder => $responses->where('is_knockout_fail', true);

        return match ($filter) {
            ScreeningVerdictFilter::Passed => $query->whereHas('screeningResponses')->whereDoesntHave('screeningResponses', $failed),
            ScreeningVerdictFilter::Failed => $query->whereHas('screeningResponses', $failed),
            ScreeningVerdictFilter::Unanswered => $query->whereDoesntHave('screeningResponses'),
            ScreeningVerdictFilter::All => $query,
        };
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function withSeenState(Builder $query, SeenFilter $filter): Builder
    {
        return match ($filter) {
            SeenFilter::Unseen => $query->whereDoesntHave('teamView'),
            SeenFilter::Seen => $query->whereHas('teamView'),
            SeenFilter::All => $query,
        };
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function withListingCounts(Builder $query): Builder
    {
        return $query->withCount([
            'comments',
            'screeningResponses',
            'screeningResponses as knockout_fails_count' => fn (Builder $responses) => $responses->where('is_knockout_fail', true),
        ]);
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function orderForListing(Builder $query, ApplicationListSort $sort): Builder
    {
        $ordered = match ($sort) {
            ApplicationListSort::Name => $query->orderBy(
                User::query()
                    ->select('users.name')
                    ->join('candidates', 'candidates.user_id', '=', 'users.id')
                    ->whereColumn('candidates.id', 'applications.candidate_id')
                    ->limit(1)
            ),
            ApplicationListSort::Applied => $query->orderByDesc('applications.created_at'),
            ApplicationListSort::DaysInStage => $query->orderByRaw(self::STAGE_SINCE_SQL.' ASC'),
            ApplicationListSort::Stage => $query->orderByDesc(
                Stage::query()->select('display_order')->whereColumn('id', 'applications.current_stage_id')->limit(1)
            ),
            ApplicationListSort::Attention => $query->orderByRaw(self::ATTENTION_ORDER_SQL)->orderByRaw(self::STAGE_SINCE_SQL.' ASC'),
        };

        return $ordered->orderBy('applications.id');
    }
```

`Collection` aqui é `Illuminate\Database\Eloquent\Collection` (já importada no modelo). Se o PHPStan reclamar de `$this->currentStage->expected_duration_days ?? 0`, mantenha `->` (não `?->`): o `??` já cobre o nulo.

- [ ] **Step 4: Rodar e ver passar**

Run: `./vendor/bin/pest app-modules/applications/tests/Feature/Models/ApplicationListingTest.php`
Expected: PASS. `./vendor/bin/pint --dirty --format agent`; `./vendor/bin/phpstan analyse app-modules/applications/src --no-progress`.

- [ ] **Step 5: Commit**

```bash
git add app-modules/applications/src/Models/Application.php app-modules/applications/tests/Feature/Models/ApplicationListingTest.php
git commit -m "feat(applications): add recruiter listing scopes and stage timing signals"
```

---

### Task 5: Agregados do panorama — scopes em `JobRequisition`, funil, estatísticas e perguntas eliminatórias

**Files:**

- Create: `app-modules/recruitment/src/Requisitions/Enums/RequisitionOverviewSort.php`
- Modify: `app-modules/recruitment/src/Requisitions/Models/JobRequisition.php` (3 scopes)
- Modify: `app-modules/recruitment/lang/en/enums.php`, `app-modules/recruitment/lang/pt_BR/enums.php`
- Create: `app-modules/applications/src/DTOs/RequisitionApplicationStats.php`
- Create: `app-modules/applications/src/Actions/BuildRequisitionFunnels.php`
- Create: `app-modules/applications/src/Actions/BuildRequisitionApplicationStats.php`
- Create: `app-modules/screening/src/Actions/CountKnockoutQuestionsByRequisition.php`
- Test: `app-modules/recruitment/tests/Feature/Requisitions/RecruiterOverviewScopesTest.php`
- Test: `app-modules/applications/tests/Feature/Actions/RequisitionAggregatesTest.php`
- Test: `app-modules/screening/tests/Feature/Actions/CountKnockoutQuestionsByRequisitionTest.php`

**Interfaces:**

- Consumes: `ApplicationStatusGroup` (Task 1), `teamView` (Task 2), `Application::STAGE_SINCE_SQL` (Task 4).
- Produces: `RequisitionOverviewSort::{New,Unseen,KnockoutPassed,Oldest,Total,Title}`; `JobRequisition::query()->withRecruiterOverviewCounts()` (atributos `applications_count`, `new_applications_count`, `active_applications_count`, `hired_applications_count`, `unseen_applications_count`, `knockout_passed_count`, `oldest_new_at`), `->searchTitle(string)`, `->orderForRecruiterOverview(RequisitionOverviewSort)`; `BuildRequisitionFunnels::execute(array<int, string> $requisitionIds): array<string, array<string, int>>`; `BuildRequisitionApplicationStats::execute(JobRequisition): RequisitionApplicationStats` (props `total, new, active, closed, unseen, overdue, knockoutPassed, knockoutFailed, knockoutUnanswered, byStage` e método `openTotal(): int`); `CountKnockoutQuestionsByRequisition::execute(Collection<int, JobRequisition> $requisitions): array<string, int>` (exige `stages` carregadas).

- [ ] **Step 1: Escrever os testes**

`recruitment/tests/Feature/Requisitions/RecruiterOverviewScopesTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Recruitment\Requisitions\Enums\RequisitionOverviewSort;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Teams\Team;

beforeEach(function (): void {
    $this->team = Team::factory()->create();
    $this->busy = JobRequisition::factory()->recycle($this->team)->create();
    JobPosting::factory()->for($this->busy, 'jobRequisition')->createOne(['title' => 'Backend Engineer']);
    $this->quiet = JobRequisition::factory()->recycle($this->team)->create();
    JobPosting::factory()->for($this->quiet, 'jobRequisition')->createOne(['title' => 'Product Designer']);

    Application::factory()->recycle($this->team)->for($this->busy, 'requisition')->count(2)->create(['status' => ApplicationStatusEnum::New]);
    Application::factory()->recycle($this->team)->for($this->busy, 'requisition')->create(['status' => ApplicationStatusEnum::InProgress]);
    Application::factory()->recycle($this->team)->for($this->busy, 'requisition')->create(['status' => ApplicationStatusEnum::Hired]);
    $seen = Application::factory()->recycle($this->team)->for($this->quiet, 'requisition')->create(['status' => ApplicationStatusEnum::New]);
    ApplicationView::factory()->forApplication($seen)->create();
});

it('counts applications per requisition for the recruiter overview', function (): void {
    $busy = JobRequisition::query()->withRecruiterOverviewCounts()->findOrFail($this->busy->getKey());
    $quiet = JobRequisition::query()->withRecruiterOverviewCounts()->findOrFail($this->quiet->getKey());

    expect((int) $busy->getAttribute('applications_count'))->toBe(4)
        ->and((int) $busy->getAttribute('new_applications_count'))->toBe(2)
        ->and((int) $busy->getAttribute('active_applications_count'))->toBe(1)
        ->and((int) $busy->getAttribute('hired_applications_count'))->toBe(1)
        ->and((int) $busy->getAttribute('unseen_applications_count'))->toBe(4)
        ->and($busy->getAttribute('oldest_new_at'))->not->toBeNull()
        ->and((int) $quiet->getAttribute('unseen_applications_count'))->toBe(0);
});

it('orders and searches the overview', function (): void {
    expect(JobRequisition::query()->withRecruiterOverviewCounts()->orderForRecruiterOverview(RequisitionOverviewSort::New)->pluck('id')->first())->toBe($this->busy->getKey())
        ->and(JobRequisition::query()->withRecruiterOverviewCounts()->orderForRecruiterOverview(RequisitionOverviewSort::Title)->pluck('id')->first())->toBe($this->busy->getKey())
        ->and(JobRequisition::query()->searchTitle('designer')->pluck('id')->all())->toBe([$this->quiet->getKey()]);
});

it('has translated labels for the overview sort', function (): void {
    foreach (['en', 'pt_BR'] as $locale) {
        app()->setLocale($locale);

        foreach (RequisitionOverviewSort::cases() as $case) {
            expect($case->getLabel())->not->toContain('::');
        }
    }
});
```

`applications/tests/Feature/Actions/RequisitionAggregatesTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Actions\BuildRequisitionApplicationStats;
use He4rt\Applications\Actions\BuildRequisitionFunnels;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;

beforeEach(function (): void {
    $this->requisition = JobRequisition::factory()->create();
    $this->team = $this->requisition->team;
    $this->stages = $this->requisition->stages()->orderBy('display_order')->get();
    $this->stages[1]->update(['expected_duration_days' => 2]);

    $make = fn (ApplicationStatusEnum $status, int $stageIndex, int $daysAgo): Application => Application::factory()
        ->recycle($this->team)
        ->for($this->requisition, 'requisition')
        ->create(['status' => $status, 'current_stage_id' => $this->stages[$stageIndex]->getKey(), 'created_at' => now()->subDays($daysAgo)]);

    $this->newOne = $make(ApplicationStatusEnum::New, 0, 0);
    $this->lateOne = $make(ApplicationStatusEnum::InProgress, 1, 10);
    $this->closedOne = $make(ApplicationStatusEnum::Rejected, 1, 10);

    $question = ScreeningQuestion::factory()->yesNo()->knockout()->create([
        'team_id' => $this->team->getKey(),
        'screenable_type' => $this->requisition->getMorphClass(),
        'screenable_id' => $this->requisition->getKey(),
    ]);
    ScreeningResponse::factory()->yesNoResponse(true)->create(['team_id' => $this->team->getKey(), 'application_id' => $this->newOne->getKey(), 'question_id' => $question->getKey()]);
    ScreeningResponse::factory()->yesNoResponse(false)->knockoutFailed()->create(['team_id' => $this->team->getKey(), 'application_id' => $this->lateOne->getKey(), 'question_id' => $question->getKey()]);
});

it('builds the open funnel per requisition and stage', function (): void {
    $funnels = resolve(BuildRequisitionFunnels::class)->execute([$this->requisition->getKey()]);

    expect($funnels[$this->requisition->getKey()][$this->stages[0]->getKey()])->toBe(1)
        ->and($funnels[$this->requisition->getKey()][$this->stages[1]->getKey()])->toBe(1);
});

it('builds the application stats of a requisition', function (): void {
    $stats = resolve(BuildRequisitionApplicationStats::class)->execute($this->requisition);

    expect($stats->total)->toBe(3)
        ->and($stats->new)->toBe(1)
        ->and($stats->active)->toBe(1)
        ->and($stats->closed)->toBe(1)
        ->and($stats->unseen)->toBe(2)
        ->and($stats->overdue)->toBe(1)
        ->and($stats->knockoutPassed)->toBe(1)
        ->and($stats->knockoutFailed)->toBe(1)
        ->and($stats->knockoutUnanswered)->toBe(1)
        ->and($stats->openTotal())->toBe(2)
        ->and($stats->byStage[$this->stages[1]->getKey()])->toBe(1);
});
```

`screening/tests/Feature/Actions/CountKnockoutQuestionsByRequisitionTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Actions\CountKnockoutQuestionsByRequisition;
use He4rt\Screening\Models\ScreeningQuestion;

it('counts knockout questions attached to the requisition and to its stages', function (): void {
    $withQuestions = JobRequisition::factory()->create();
    $team = $withQuestions->team;
    $stage = $withQuestions->stages()->orderBy('display_order')->first();
    $without = JobRequisition::factory()->recycle($team)->create();

    ScreeningQuestion::factory()->yesNo()->knockout()->create(['team_id' => $team->getKey(), 'screenable_type' => $withQuestions->getMorphClass(), 'screenable_id' => $withQuestions->getKey()]);
    ScreeningQuestion::factory()->yesNo()->knockout()->create(['team_id' => $team->getKey(), 'screenable_type' => $stage->getMorphClass(), 'screenable_id' => $stage->getKey()]);
    ScreeningQuestion::factory()->yesNo()->create(['team_id' => $team->getKey(), 'screenable_type' => $withQuestions->getMorphClass(), 'screenable_id' => $withQuestions->getKey(), 'is_knockout' => false, 'knockout_criteria' => null]);

    $requisitions = JobRequisition::query()->with('stages')->whereKey([$withQuestions->getKey(), $without->getKey()])->get();

    $counts = resolve(CountKnockoutQuestionsByRequisition::class)->execute($requisitions);

    expect($counts[$withQuestions->getKey()])->toBe(2)
        ->and($counts)->not->toHaveKey($without->getKey());
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `./vendor/bin/pest app-modules/recruitment/tests/Feature/Requisitions/RecruiterOverviewScopesTest.php app-modules/applications/tests/Feature/Actions/RequisitionAggregatesTest.php app-modules/screening/tests/Feature/Actions/CountKnockoutQuestionsByRequisitionTest.php`
Expected: FAIL.

- [ ] **Step 3: Enum e scopes em `recruitment`**

`RequisitionOverviewSort.php` (namespace `He4rt\Recruitment\Requisitions\Enums`, `use App\Enums\Concerns\StringifyEnum; use Filament\Support\Contracts\HasLabel;`):

```php
enum RequisitionOverviewSort: string implements HasLabel
{
    use StringifyEnum;

    case New = 'new';
    case Unseen = 'unseen';
    case KnockoutPassed = 'knockout_passed';
    case Oldest = 'oldest';
    case Total = 'total';
    case Title = 'title';

    public function getLabel(): string
    {
        return __('recruitment::enums.requisition_overview_sort.'.$this->value.'.label');
    }
}
```

Traduções (`recruitment/lang/en/enums.php`, no fim do array):

```php
    'requisition_overview_sort' => [
        'new' => ['label' => 'Most new pending'],
        'unseen' => ['label' => 'Most unseen'],
        'knockout_passed' => ['label' => 'Most passed knockout'],
        'oldest' => ['label' => 'Longest pending'],
        'total' => ['label' => 'Most applications'],
        'title' => ['label' => 'Title'],
    ],
```

`pt_BR`:

```php
    'requisition_overview_sort' => [
        'new' => ['label' => 'Mais novas pendentes'],
        'unseen' => ['label' => 'Mais não vistos'],
        'knockout_passed' => ['label' => 'Mais aprovados na eliminatória'],
        'oldest' => ['label' => 'Pendente há mais tempo'],
        'total' => ['label' => 'Mais candidaturas'],
        'title' => ['label' => 'Título'],
    ],
```

Scopes em `JobRequisition.php` (imports: `He4rt\Applications\Enums\ApplicationStatusEnum`, `He4rt\Applications\Enums\ApplicationStatusGroup`, `He4rt\Recruitment\Requisitions\Enums\RequisitionOverviewSort`; `Builder` e `Scope` já importados):

```php
    /**
     * @param  Builder<JobRequisition>  $query
     * @return Builder<JobRequisition>
     */
    #[Scope]
    protected function withRecruiterOverviewCounts(Builder $query): Builder
    {
        $failed = fn (Builder $responses): Builder => $responses->where('is_knockout_fail', true);

        return $query
            ->withCount([
                'applications',
                'applications as new_applications_count' => fn (Builder $applications) => $applications->whereIn('status', ApplicationStatusGroup::New->values()),
                'applications as active_applications_count' => fn (Builder $applications) => $applications->whereIn('status', ApplicationStatusGroup::Active->values()),
                'applications as hired_applications_count' => fn (Builder $applications) => $applications->where('status', ApplicationStatusEnum::Hired->value),
                'applications as unseen_applications_count' => fn (Builder $applications) => $applications
                    ->whereNotIn('status', ApplicationStatusGroup::Closed->values())
                    ->whereDoesntHave('teamView'),
                'applications as knockout_passed_count' => fn (Builder $applications) => $applications
                    ->whereHas('screeningResponses')
                    ->whereDoesntHave('screeningResponses', $failed),
            ])
            ->withMin(['applications as oldest_new_at' => fn (Builder $applications) => $applications->whereIn('status', ApplicationStatusGroup::New->values())], 'created_at');
    }

    /**
     * @param  Builder<JobRequisition>  $query
     * @return Builder<JobRequisition>
     */
    #[Scope]
    protected function searchTitle(Builder $query, string $term): Builder
    {
        return $query->whereHas('post', fn (Builder $post) => $post->where('title', 'ilike', '%'.mb_trim($term).'%'));
    }

    /**
     * @param  Builder<JobRequisition>  $query
     * @return Builder<JobRequisition>
     */
    #[Scope]
    protected function orderForRecruiterOverview(Builder $query, RequisitionOverviewSort $sort): Builder
    {
        $ordered = match ($sort) {
            RequisitionOverviewSort::Unseen => $query->orderByDesc('unseen_applications_count'),
            RequisitionOverviewSort::KnockoutPassed => $query->orderByDesc('knockout_passed_count'),
            RequisitionOverviewSort::Oldest => $query->orderByRaw('oldest_new_at ASC NULLS LAST'),
            RequisitionOverviewSort::Total => $query->orderByDesc('applications_count'),
            RequisitionOverviewSort::Title => $query->orderBy(
                JobPosting::query()->select('title')->whereColumn('job_requisition_id', 'recruitment_job_requisitions.id')->limit(1)
            ),
            RequisitionOverviewSort::New => $query->orderByDesc('new_applications_count'),
        };

        return $ordered->orderByDesc('applications_count')->orderBy('recruitment_job_requisitions.id');
    }
```

- [ ] **Step 4: DTO e Actions em `applications`**

`DTOs/RequisitionApplicationStats.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\DTOs;

/** Agregados de candidaturas de uma vaga para o espaço do recrutador. */
final readonly class RequisitionApplicationStats
{
    /**
     * @param  array<string, int>  $byStage
     */
    public function __construct(
        public int $total,
        public int $new,
        public int $active,
        public int $closed,
        public int $unseen,
        public int $overdue,
        public int $knockoutPassed,
        public int $knockoutFailed,
        public int $knockoutUnanswered,
        public array $byStage,
    ) {}

    public function openTotal(): int
    {
        return array_sum($this->byStage);
    }
}
```

`Actions/BuildRequisitionFunnels.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Actions;

use He4rt\Applications\Enums\ApplicationStatusGroup;
use He4rt\Applications\Models\Application;
use Illuminate\Support\Facades\DB;

/** Conta candidaturas abertas por etapa para um conjunto de vagas: [requisition_id => [stage_id => total]]. */
final class BuildRequisitionFunnels
{
    /**
     * @param  array<int, string>  $requisitionIds
     * @return array<string, array<string, int>>
     */
    public function execute(array $requisitionIds): array
    {
        if ($requisitionIds === []) {
            return [];
        }

        $rows = Application::query()
            ->whereIn('requisition_id', $requisitionIds)
            ->whereNotNull('current_stage_id')
            ->whereNotIn('status', ApplicationStatusGroup::Closed->values())
            ->select('requisition_id', 'current_stage_id', DB::raw('COUNT(*) AS total'))
            ->groupBy('requisition_id', 'current_stage_id')
            ->get();

        $funnels = [];

        foreach ($rows as $row) {
            $funnels[(string) $row->requisition_id][(string) $row->current_stage_id] = (int) $row->getAttribute('total');
        }

        return $funnels;
    }
}
```

`Actions/BuildRequisitionApplicationStats.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Actions;

use He4rt\Applications\DTOs\RequisitionApplicationStats;
use He4rt\Applications\Enums\ApplicationStatusGroup;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class BuildRequisitionApplicationStats
{
    public function execute(JobRequisition $requisition): RequisitionApplicationStats
    {
        $all = fn (): Builder => Application::query()->where('requisition_id', $requisition->getKey());
        $open = fn (): Builder => $all()->whereNotIn('status', ApplicationStatusGroup::Closed->values());
        $failed = fn (Builder $responses): Builder => $responses->where('is_knockout_fail', true);

        $byStage = $open()
            ->whereNotNull('current_stage_id')
            ->select('current_stage_id', DB::raw('COUNT(*) AS total'))
            ->groupBy('current_stage_id')
            ->pluck('total', 'current_stage_id')
            ->map(fn ($total): int => (int) $total)
            ->all();

        $overdue = $open()
            ->whereNotNull('current_stage_id')
            ->whereRaw(Application::STAGE_SINCE_SQL.' < NOW() - MAKE_INTERVAL(days => (SELECT s.expected_duration_days FROM recruitment_pipeline_stages s WHERE s.id = applications.current_stage_id))')
            ->count();

        return new RequisitionApplicationStats(
            total: $all()->count(),
            new: $all()->inStatusGroup(ApplicationStatusGroup::New)->count(),
            active: $all()->inStatusGroup(ApplicationStatusGroup::Active)->count(),
            closed: $all()->inStatusGroup(ApplicationStatusGroup::Closed)->count(),
            unseen: $open()->unseenByTeam()->count(),
            overdue: $overdue,
            knockoutPassed: $all()->whereHas('screeningResponses')->whereDoesntHave('screeningResponses', $failed)->count(),
            knockoutFailed: $all()->whereHas('screeningResponses', $failed)->count(),
            knockoutUnanswered: $all()->whereDoesntHave('screeningResponses')->count(),
            byStage: $byStage,
        );
    }
}
```

`screening/src/Actions/CountKnockoutQuestionsByRequisition.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Screening\Actions;

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningQuestion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

/** Conta perguntas eliminatórias por vaga, somando as da própria vaga e as de suas etapas (exige `stages` carregadas). */
final class CountKnockoutQuestionsByRequisition
{
    /**
     * @param  Collection<int, JobRequisition>  $requisitions
     * @return array<string, int>
     */
    public function execute(Collection $requisitions): array
    {
        if ($requisitions->isEmpty()) {
            return [];
        }

        $stageToRequisition = [];

        foreach ($requisitions as $requisition) {
            foreach ($requisition->stages as $stage) {
                $stageToRequisition[(string) $stage->getKey()] = (string) $requisition->getKey();
            }
        }

        $requisitionAlias = Relation::getMorphAlias(JobRequisition::class);
        $stageAlias = Relation::getMorphAlias(Stage::class);
        $requisitionIds = $requisitions->map(fn (JobRequisition $requisition): string => (string) $requisition->getKey())->all();

        $questions = ScreeningQuestion::query()
            ->where('is_knockout', true)
            ->where(function (Builder $query) use ($requisitionAlias, $stageAlias, $requisitionIds, $stageToRequisition): void {
                $query
                    ->where(fn (Builder $query) => $query->where('screenable_type', $requisitionAlias)->whereIn('screenable_id', $requisitionIds))
                    ->orWhere(fn (Builder $query) => $query->where('screenable_type', $stageAlias)->whereIn('screenable_id', array_keys($stageToRequisition)));
            })
            ->get(['screenable_type', 'screenable_id']);

        $counts = [];

        foreach ($questions as $question) {
            $requisitionId = $question->screenable_type === $stageAlias
                ? ($stageToRequisition[(string) $question->screenable_id] ?? null)
                : (string) $question->screenable_id;

            if ($requisitionId !== null) {
                $counts[$requisitionId] = ($counts[$requisitionId] ?? 0) + 1;
            }
        }

        return $counts;
    }
}
```

- [ ] **Step 5: Rodar e ver passar**

Run: mesmo comando do Step 2. Expected: PASS. `./vendor/bin/pint --dirty --format agent`; `./vendor/bin/phpstan analyse app-modules/applications/src app-modules/recruitment/src/Requisitions app-modules/screening/src/Actions --no-progress`.

- [ ] **Step 6: Commit**

```bash
git add app-modules/recruitment app-modules/applications app-modules/screening
git commit -m "feat(recruitment): aggregate recruiter overview counts, funnels and knockout questions"
```

---

### Task 6: Componente Livewire `ApplicationsWorkspace` — panorama, seletor de vaga e cabeçalho da vaga

**Files:**

- Create: `app-modules/panel-organization/src/Livewire/Applications/ApplicationsWorkspace.php`
- Create: `app-modules/panel-organization/resources/views/livewire/applications/workspace.blade.php`
- Create: `app-modules/panel-organization/resources/views/livewire/applications/workspace/overview.blade.php`
- Create: `app-modules/panel-organization/resources/views/livewire/applications/workspace/requisition.blade.php`
- Create: `app-modules/panel-organization/resources/views/livewire/applications/workspace/partials/switcher.blade.php`
- Create: `app-modules/panel-organization/resources/views/livewire/applications/workspace/partials/header.blade.php`
- Create: `app-modules/panel-organization/resources/views/components/workspace/stage-dot.blade.php`
- Create: `app-modules/panel-organization/resources/views/components/workspace/stage-bar.blade.php`
- Create: `app-modules/panel-organization/lang/en/workspace.php`, `app-modules/panel-organization/lang/pt_BR/workspace.php`
- Modify: `app-modules/panel-organization/src/PanelOrganizationServiceProvider.php` (`Livewire::component('panel-organization.applications-workspace', ApplicationsWorkspace::class)`)
- Test: `app-modules/panel-organization/tests/Feature/Livewire/ApplicationsWorkspaceOverviewTest.php`

**Interfaces:**

- Consumes: Tasks 1, 2, 4, 5 (enums, scopes, Actions, DTO).
- Produces: `ApplicationsWorkspace` com propriedades públicas `string $requisitionId` (`#[Url(as: 'job', except: '')]`), `string $requisitionSearch`, `RequisitionOverviewSort $requisitionSort`, `bool $onlyPublished = true`, `string $search`, `string $stageId`, `?ApplicationStatusGroup $statusGroup = null`, `ScreeningVerdictFilter $screening`, `SeenFilter $seen`, `ApplicationListSort $sort`, `string $selectedId`; computeds `requisitionsPage`, `funnelByRequisition`, `knockoutQuestionsByRequisition`, `requisition`, `stages`, `knockoutQuestionCount`, `stats`, `applicationsPage`, `selectedApplication`, `requisitionResults`; métodos `openRequisition(string)`, `closeRequisition()`, `sortRequisitions(string)`, `filterStage(string)`, `filterStatusGroup(string)`, `filterScreening(string)`, `filterSeen(string)`, `sortBy(string)`, `select(string)`, `clearFilters()`, `viewUrl(Application): string`. Views `partials/queue.blade.php` e `partials/preview.blade.php` são criadas na Task 7; nesta task `requisition.blade.php` inclui só `switcher` e `header`.

- [ ] **Step 1: Escrever o teste**

```php
<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Candidates\Models\Candidate;
use He4rt\Organization\Livewire\Applications\ApplicationsWorkspace;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->requisition = JobRequisition::factory()->create(['status' => RequisitionStatusEnum::Published]);
    JobPosting::factory()->for($this->requisition, 'jobRequisition')->createOne(['title' => 'Engenharia de Plataforma']);
    $this->team = $this->requisition->team;

    $this->draft = JobRequisition::factory()->recycle($this->team)->create(['status' => RequisitionStatusEnum::Draft]);
    JobPosting::factory()->for($this->draft, 'jobRequisition')->createOne(['title' => 'Vaga Rascunho']);

    $this->plain = JobRequisition::factory()->recycle($this->team)->create(['status' => RequisitionStatusEnum::Published]);
    JobPosting::factory()->for($this->plain, 'jobRequisition')->createOne(['title' => 'Vaga Sem Eliminatória']);

    $recruiter = Recruiter::factory()->for($this->team, 'team')->create();
    $recruiter->user->assignRole(Roles::SuperAdmin->value);
    actingAs($recruiter->user);
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->team);

    $question = ScreeningQuestion::factory()->yesNo()->knockout()->create([
        'team_id' => $this->team->getKey(),
        'screenable_type' => $this->requisition->getMorphClass(),
        'screenable_id' => $this->requisition->getKey(),
    ]);

    $make = fn (JobRequisition $requisition, ApplicationStatusEnum $status): Application => Application::factory()
        ->recycle($this->team)
        ->for($requisition, 'requisition')
        ->for(Candidate::factory()->create(), 'candidate')
        ->create(['status' => $status]);

    $this->passed = $make($this->requisition, ApplicationStatusEnum::New);
    $this->failed = $make($this->requisition, ApplicationStatusEnum::New);
    $this->seen = $make($this->requisition, ApplicationStatusEnum::InProgress);
    ApplicationView::factory()->forApplication($this->seen)->create();

    ScreeningResponse::factory()->yesNoResponse(true)->create(['team_id' => $this->team->getKey(), 'application_id' => $this->passed->getKey(), 'question_id' => $question->getKey()]);
    ScreeningResponse::factory()->yesNoResponse(false)->knockoutFailed()->create(['team_id' => $this->team->getKey(), 'application_id' => $this->failed->getKey(), 'question_id' => $question->getKey()]);
});

it('opens on the overview with only published requisitions and their signals', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->assertOk()
        ->assertSee(__('panel-organization::workspace.overview.title'))
        ->assertSee('Engenharia de Plataforma')
        ->assertSee('Vaga Sem Eliminatória')
        ->assertDontSee('Vaga Rascunho')
        ->assertSee(trans_choice('panel-organization::workspace.overview.knockout_passed', 1, ['count' => 1]))
        ->assertSee(__('panel-organization::workspace.overview.no_knockout'))
        ->assertSee(trans_choice('panel-organization::workspace.overview.unseen', 2, ['count' => 2]));
});

it('includes non published requisitions when asked', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->set('onlyPublished', false)
        ->assertSee('Vaga Rascunho');
});

it('searches requisitions by title', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->set('requisitionSearch', 'plataforma')
        ->assertSee('Engenharia de Plataforma')
        ->assertDontSee('Vaga Sem Eliminatória');
});

it('opens a requisition and shows its header with knockout totals', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->requisition->getKey())
        ->assertSet('requisitionId', $this->requisition->getKey())
        ->assertSee(__('panel-organization::workspace.header.knockout_title', ['count' => 1]))
        ->assertSee(trans_choice('panel-organization::workspace.header.knockout_passed', 1, ['count' => 1]))
        ->assertSee(trans_choice('panel-organization::workspace.header.knockout_failed', 1, ['count' => 1]))
        ->assertSee(trans_choice('panel-organization::workspace.header.knockout_unanswered', 1, ['count' => 1]))
        ->call('closeRequisition')
        ->assertSet('requisitionId', '')
        ->assertSee(__('panel-organization::workspace.overview.title'));
});

it('falls back to the overview when the requisition belongs to another team', function (): void {
    $foreign = JobRequisition::factory()->create(['status' => RequisitionStatusEnum::Published]);

    livewire(ApplicationsWorkspace::class, ['requisitionId' => $foreign->getKey()])
        ->assertOk()
        ->assertSee(__('panel-organization::workspace.overview.title'));
});

it('finds requisitions through the switcher search', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->requisition->getKey())
        ->set('requisitionSearch', 'Sem Elimin')
        ->assertSee('Vaga Sem Eliminatória');
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `./vendor/bin/pest app-modules/panel-organization/tests/Feature/Livewire/ApplicationsWorkspaceOverviewTest.php`
Expected: FAIL (classe inexistente).

- [ ] **Step 3: Traduções** (`lang/en/workspace.php`; o `pt_BR` espelha cada chave com o texto entre parênteses)

```php
<?php

declare(strict_types=1);

return [
    'overview' => [
        'title' => 'Job overview',                                        // Panorama de vagas
        'subtitle' => 'One card per job: new applications waiting, unseen by the team, the funnel and who passed the knockout questions. Click a job to open its candidates.', // Um cartão por vaga: novas esperando, não vistas pelo time, o funil e quem passou na eliminatória. Clique na vaga para abrir os candidatos.
        'count' => '{1} :count job|[2,*] :count jobs',                    // {1} :count vaga|[2,*] :count vagas
        'search_placeholder' => 'Search job by title',                    // Buscar vaga pelo título
        'only_published' => 'Published only',                             // Só publicadas
        'new' => 'new',                                                   // novas
        'oldest_waiting' => 'the oldest has been waiting :days d',        // a mais antiga espera há :days d
        'none_waiting' => 'none waiting for screening',                   // nenhuma esperando triagem
        'total' => 'in total',                                            // no total
        'active' => 'in progress',                                        // em processo
        'unseen' => '{1} :count unseen|[2,*] :count unseen',              // {1} :count não vista|[2,*] :count não vistas
        'knockout_passed' => '{1} :count passed the knockout|[2,*] :count passed the knockout', // {1} :count aprovada na eliminatória|[2,*] :count aprovadas na eliminatória
        'knockout_questions' => '{1} :count question|[2,*] :count questions', // {1} :count pergunta|[2,*] :count perguntas
        'no_knockout' => 'No knockout question',                          // Sem pergunta eliminatória
        'hired' => '{1} :count hired|[2,*] :count hired',                 // {1} :count contratada|[2,*] :count contratadas
        'empty' => 'No job matches these filters.',                       // Nenhuma vaga encontrada com esses filtros.
        'untitled' => 'Untitled job',                                     // Vaga sem título
        'positions' => '{1} :count position|[2,*] :count positions',      // {1} :count posição|[2,*] :count posições
    ],
    'switcher' => [
        'back' => 'Overview',                                             // Panorama
        'placeholder' => 'Choose a job',                                  // Escolher vaga
        'label' => 'Switch job',                                          // Trocar de vaga
        'new_count' => '{1} :count new|[2,*] :count new',                 // {1} :count nova|[2,*] :count novas
        'empty' => 'No job found.',                                       // Nenhuma vaga encontrada.
    ],
    'header' => [
        'total' => 'Total',                                               // Total
        'new' => 'New',                                                   // Novas
        'unseen' => 'Unseen by the team',                                 // Não vistas pelo time
        'overdue' => 'Overdue in stage',                                  // Atrasadas na etapa
        'closed' => 'Closed',                                             // Encerradas
        'knockout_title' => 'Job with knockout questions · :count',       // Vaga com pergunta eliminatória · :count
        'knockout_passed' => '{1} :count passed|[2,*] :count passed',     // {1} :count aprovada|[2,*] :count aprovadas
        'knockout_failed' => '{1} :count failed|[2,*] :count failed',     // {1} :count reprovada|[2,*] :count reprovadas
        'knockout_unanswered' => '{1} :count unanswered|[2,*] :count unanswered', // {1} :count sem resposta|[2,*] :count sem resposta
        'knockout_hint' => 'Click to filter the queue by result.',        // Clique para filtrar a fila pelo resultado.
        'no_knockout_title' => 'Knockout questions',                      // Eliminatória
        'no_knockout' => 'This job has no knockout question.',            // Esta vaga não tem pergunta eliminatória.
        'all_stages' => 'All stages',                                     // Todas as etapas
    ],
    'queue' => [
        'search_placeholder' => 'Name, email, headline or tracking code', // Nome, e-mail, título profissional ou código
        'sort_prefix' => 'Sort: :label',                                  // Ordenar: :label
        'all_statuses' => 'All',                                          // Todas
        'seen_unseen' => 'Unseen',                                        // Não vistas
        'seen_seen' => 'Already seen',                                    // Já vistas
        'screening_passed' => '✓ Passed the knockout',                    // ✓ Aprovadas na eliminatória
        'screening_failed' => '✕ Failed',                                 // ✕ Reprovadas
        'verdict_failed' => '✕ Failed the knockout :fails/:total',        // ✕ Reprovado na eliminatória :fails/:total
        'verdict_passed' => '✓ Passed the knockout',                      // ✓ Aprovado na eliminatória
        'verdict_unanswered' => 'Knockout unanswered',                    // Eliminatória sem resposta
        'seen_title' => 'Already seen by the team',                       // Já visto pelo time
        'unseen_title' => 'Not seen by the team yet',                     // Ainda não visto pelo time
        'no_headline' => 'No professional headline',                      // Sem título profissional
        'no_location' => 'Location not provided',                         // Local não informado
        'no_stage' => 'No stage',                                         // Sem etapa
        'days_in_stage' => ':days d in stage',                            // :days d na etapa
        'overdue' => 'overdue',                                           // atrasada
        'no_evaluations' => 'No evaluations',                             // Sem avaliações
        'average' => 'Average :score of 5',                               // Média :score de 5
        'empty' => 'No application matches these filters.',               // Nenhuma candidatura corresponde aos filtros.
        'clear_filters' => 'Clear filters',                               // Limpar filtros
    ],
    'preview' => [
        'empty' => 'Select a candidate in the queue to see the preview.', // Selecione um candidato na fila para ver a prévia.
        'open' => 'Open application',                                     // Abrir candidatura
        'applied_on' => 'applied on :date',                               // inscrição em :date
        'no_experience' => 'No work experience provided',                 // Sem experiência informada
        'seen_by' => 'Seen by :name on :date',                            // Visto por :name em :date
        'unseen' => 'Not seen by the team yet',                           // Ainda não visto pelo time
        'knockout_unanswered' => 'Knockout questions not answered yet.',  // Eliminatória ainda sem resposta.
        'knockout_failed_title' => '✕ Failed the knockout · :fails of :total', // ✕ Reprovado na eliminatória · :fails de :total
        'knockout_passed_title' => '{1} ✓ Passed the :count knockout question|[2,*] ✓ Passed the :count knockout questions', // {1} ✓ Aprovado na :count pergunta eliminatória|[2,*] ✓ Aprovado nas :count perguntas eliminatórias
        'answered' => 'answered “:answer”',                               // respondeu “:answer”
        'question_removed' => 'Question removed',                         // Pergunta removida
        'answer_yes' => 'Yes',                                            // Sim
        'answer_no' => 'No',                                              // Não
        'current_stage' => 'Current stage',                               // Etapa atual
        'days' => '{1} :count day|[2,*] :count days',                     // {1} :count dia|[2,*] :count dias
        'expected' => 'expected :days',                                   // esperado :days
        'moved_by' => 'Moved by :name on :date',                          // Movida por :name em :date
        'system' => 'system',                                             // sistema
        'no_movement' => 'No movements: in the initial stage since applying.', // Sem movimentações: está na etapa inicial desde a inscrição.
        'experience' => 'Experience',                                     // Experiência
        'location' => 'Location',                                         // Localização
        'remote' => 'Open to remote',                                     // Aceita remoto
        'relocate' => 'Willing to relocate',                              // Aceita mudar de cidade
        'salary' => 'Expected salary',                                    // Pretensão
        'availability' => 'Availability',                                 // Disponibilidade
        'immediate' => 'Immediate',                                       // Imediata
        'evaluations' => 'Evaluations',                                   // Avaliações
        'evaluations_count' => '{0} none recorded|{1} :count recorded|[2,*] :count recorded', // {0} nenhuma registrada|{1} :count registrada|[2,*] :count registradas
        'comments' => 'Comments',                                         // Comentários
        'cover_letter' => 'Cover letter',                                 // Carta
        'sent' => 'Sent',                                                 // Enviada
        'not_sent' => 'Not sent',                                         // Não enviada
        'source' => 'Source',                                             // Origem
        'tracking_code' => 'Code',                                        // Código
        'skills' => 'Top skills',                                         // Principais habilidades
        'skill_level' => 'level :level of 5',                             // nível :level de 5
    ],
];
```

Remova os comentários ao gravar os arquivos (eles existem só neste plano para dar o texto pt_BR). Grave `pt_BR/workspace.php` com a mesma estrutura e os textos indicados.

- [ ] **Step 4: Componente Livewire**

```php
<?php

declare(strict_types=1);

namespace He4rt\Organization\Livewire\Applications;

use He4rt\Applications\Actions\BuildRequisitionApplicationStats;
use He4rt\Applications\Actions\BuildRequisitionFunnels;
use He4rt\Applications\DTOs\RequisitionApplicationStats;
use He4rt\Applications\Enums\ApplicationListSort;
use He4rt\Applications\Enums\ApplicationStatusGroup;
use He4rt\Applications\Enums\ScreeningVerdictFilter;
use He4rt\Applications\Enums\SeenFilter;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\ApplicationResource;
use He4rt\Recruitment\Requisitions\Enums\RequisitionOverviewSort;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Actions\CountKnockoutQuestionsByRequisition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Espaço do recrutador: panorama de vagas e, dentro de uma vaga, fila de candidaturas com prévia.
 *
 * @property-read LengthAwarePaginator<int, JobRequisition> $requisitionsPage
 * @property-read array<string, array<string, int>> $funnelByRequisition
 * @property-read array<string, int> $knockoutQuestionsByRequisition
 * @property-read JobRequisition|null $requisition
 * @property-read Collection<int, Stage> $stages
 * @property-read int $knockoutQuestionCount
 * @property-read RequisitionApplicationStats $stats
 * @property-read LengthAwarePaginator<int, Application> $applicationsPage
 * @property-read Application|null $selectedApplication
 * @property-read EloquentCollection<int, JobRequisition> $requisitionResults
 */
class ApplicationsWorkspace extends Component
{
    use WithPagination;

    public const int APPLICATIONS_PER_PAGE = 25;

    public const int REQUISITIONS_PER_PAGE = 20;

    public const string REQUISITIONS_PAGE_NAME = 'jobsPage';

    #[Locked]
    public string $teamId = '';

    #[Url(as: 'job', except: '')]
    public string $requisitionId = '';

    public string $requisitionSearch = '';

    public RequisitionOverviewSort $requisitionSort = RequisitionOverviewSort::New;

    public bool $onlyPublished = true;

    public string $search = '';

    public string $stageId = '';

    public ?ApplicationStatusGroup $statusGroup = null;

    public ScreeningVerdictFilter $screening = ScreeningVerdictFilter::All;

    public SeenFilter $seen = SeenFilter::All;

    public ApplicationListSort $sort = ApplicationListSort::Attention;

    public string $selectedId = '';

    public function mount(): void
    {
        $tenant = filament()->getTenant();
        abort_unless($tenant instanceof Model, 403);

        $this->teamId = (string) $tenant->getKey();
    }

    /**
     * @return LengthAwarePaginator<int, JobRequisition>
     */
    #[Computed]
    public function requisitionsPage(): LengthAwarePaginator
    {
        $term = mb_trim($this->requisitionSearch);

        return JobRequisition::query()
            ->where('team_id', $this->teamId)
            ->with(['post', 'department', 'recruiter.user', 'stages' => fn ($stages) => $stages->where('active', true)->orderBy('display_order')])
            ->withRecruiterOverviewCounts()
            ->when($this->onlyPublished, fn (Builder $query) => $query->where('status', RequisitionStatusEnum::Published->value))
            ->when($term !== '', fn (Builder $query) => $query->searchTitle($term))
            ->orderForRecruiterOverview($this->requisitionSort)
            ->paginate(self::REQUISITIONS_PER_PAGE, pageName: self::REQUISITIONS_PAGE_NAME);
    }

    /**
     * @return array<string, array<string, int>>
     */
    #[Computed]
    public function funnelByRequisition(): array
    {
        $ids = collect($this->requisitionsPage->items())->map(fn (JobRequisition $requisition): string => (string) $requisition->getKey())->all();

        return resolve(BuildRequisitionFunnels::class)->execute($ids);
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function knockoutQuestionsByRequisition(): array
    {
        return resolve(CountKnockoutQuestionsByRequisition::class)->execute(collect($this->requisitionsPage->items()));
    }

    #[Computed]
    public function requisition(): ?JobRequisition
    {
        if ($this->requisitionId === '') {
            return null;
        }

        return JobRequisition::query()
            ->where('team_id', $this->teamId)
            ->with(['post', 'department', 'recruiter.user', 'stages' => fn ($stages) => $stages->where('active', true)->orderBy('display_order')])
            ->find($this->requisitionId);
    }

    /**
     * @return Collection<int, Stage>
     */
    #[Computed]
    public function stages(): Collection
    {
        return $this->requisition->stages ?? new EloquentCollection();
    }

    #[Computed]
    public function knockoutQuestionCount(): int
    {
        $requisition = $this->requisition;

        if ($requisition === null) {
            return 0;
        }

        return resolve(CountKnockoutQuestionsByRequisition::class)->execute(collect([$requisition]))[(string) $requisition->getKey()] ?? 0;
    }

    #[Computed]
    public function stats(): RequisitionApplicationStats
    {
        $requisition = $this->requisition;

        if ($requisition === null) {
            return new RequisitionApplicationStats(0, 0, 0, 0, 0, 0, 0, 0, 0, []);
        }

        return resolve(BuildRequisitionApplicationStats::class)->execute($requisition);
    }

    /**
     * @return LengthAwarePaginator<int, Application>
     */
    #[Computed]
    public function applicationsPage(): LengthAwarePaginator
    {
        $term = mb_trim($this->search);

        $page = Application::query()
            ->withStageSince()
            ->where('applications.team_id', $this->teamId)
            ->where('applications.requisition_id', $this->requisitionId)
            ->with([
                'candidate.media',
                'candidate.address',
                'candidate.skills',
                'candidate.degrees',
                'candidate.workExperiences',
                'candidate.user.links',
                'currentStage',
                'teamView.viewer',
                'stageHistory' => fn ($history) => $history->latest()->limit(1)->with(['toStage', 'movedBy']),
                'evaluations',
                'screeningResponses.question',
            ])
            ->withListingCounts()
            ->when($term !== '', fn (Builder $query) => $query->searchCandidate($term))
            ->when($this->stageId !== '', fn (Builder $query) => $query->where('applications.current_stage_id', $this->stageId))
            ->when($this->statusGroup !== null, fn (Builder $query) => $query->inStatusGroup($this->statusGroup))
            ->withScreeningVerdict($this->screening)
            ->withSeenState($this->seen)
            ->orderForListing($this->sort)
            ->paginate(self::APPLICATIONS_PER_PAGE);

        foreach ($page->items() as $application) {
            $application->candidate?->user->setRelation('candidate', $application->candidate);
        }

        return $page;
    }

    #[Computed]
    public function selectedApplication(): ?Application
    {
        if ($this->selectedId === '') {
            return null;
        }

        return collect($this->applicationsPage->items())->first(fn (Application $application): bool => (string) $application->getKey() === $this->selectedId);
    }

    /**
     * @return EloquentCollection<int, JobRequisition>
     */
    #[Computed]
    public function requisitionResults(): EloquentCollection
    {
        $term = mb_trim($this->requisitionSearch);

        return JobRequisition::query()
            ->where('team_id', $this->teamId)
            ->with('post')
            ->withRecruiterOverviewCounts()
            ->when($this->onlyPublished, fn (Builder $query) => $query->where('status', RequisitionStatusEnum::Published->value))
            ->when($term !== '', fn (Builder $query) => $query->searchTitle($term))
            ->orderForRecruiterOverview(RequisitionOverviewSort::New)
            ->limit(8)
            ->get();
    }

    public function viewUrl(Application $application): string
    {
        return ApplicationResource::getUrl('view', ['record' => $application]);
    }

    public function openRequisition(string $requisitionId): void
    {
        $this->requisitionId = $requisitionId;
        $this->requisitionSearch = '';
        $this->selectedId = '';
        $this->reset('search', 'stageId', 'statusGroup', 'screening', 'seen', 'sort');
        $this->resetPage();
    }

    public function closeRequisition(): void
    {
        $this->requisitionId = '';
        $this->requisitionSearch = '';
        $this->selectedId = '';
        $this->resetPage();
    }

    public function updatedRequisitionSearch(): void
    {
        $this->resetPage(self::REQUISITIONS_PAGE_NAME);
    }

    public function updatedRequisitionSort(): void
    {
        $this->resetPage(self::REQUISITIONS_PAGE_NAME);
    }

    public function updatedOnlyPublished(): void
    {
        $this->resetPage(self::REQUISITIONS_PAGE_NAME);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function sortRequisitions(string $sort): void
    {
        $this->requisitionSort = RequisitionOverviewSort::tryFrom($sort) ?? RequisitionOverviewSort::New;
        $this->resetPage(self::REQUISITIONS_PAGE_NAME);
    }

    public function filterStage(string $stageId): void
    {
        $this->stageId = $this->stageId === $stageId ? '' : $stageId;
        $this->resetPage();
    }

    public function filterStatusGroup(string $group): void
    {
        $this->statusGroup = ApplicationStatusGroup::tryFrom($group);
        $this->resetPage();
    }

    public function filterScreening(string $filter): void
    {
        $this->screening = ScreeningVerdictFilter::tryFrom($filter) ?? ScreeningVerdictFilter::All;
        $this->resetPage();
    }

    public function filterSeen(string $filter): void
    {
        $this->seen = SeenFilter::tryFrom($filter) ?? SeenFilter::All;
        $this->resetPage();
    }

    public function sortBy(string $sort): void
    {
        $this->sort = ApplicationListSort::tryFrom($sort) ?? ApplicationListSort::Attention;
        $this->resetPage();
    }

    public function select(string $applicationId): void
    {
        $this->selectedId = $applicationId;
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'stageId', 'statusGroup', 'screening', 'seen');
        $this->resetPage();
    }

    public function render(): View
    {
        return view('panel-organization::livewire.applications.workspace');
    }
}
```

Registrar no `PanelOrganizationServiceProvider::boot()`: `Livewire::component('panel-organization.applications-workspace', ApplicationsWorkspace::class);` (com o `use`).

- [ ] **Step 5: Views do panorama, seletor e cabeçalho**

Fonte visual: `docs.local/prototype/views/workspace/variant-e.blade.php` (bloco `@if ($job === null)` para o panorama; `<header>` para o cabeçalho) e `docs.local/prototype/views/workspace/partials/job-switcher.blade.php`. Adapte **todos** os textos para `__()`/`trans_choice()` com as chaves da Step 3, troque `$this->job` por `$this->requisition`, `$this->jobsPage` por `$this->requisitionsPage`, `openJob`/`closeJob` por `openRequisition`/`closeRequisition`, `filterStatus('new')` por `filterStatusGroup('new')`, `$stats['x']` por `$stats->x`, e no rodapé do cartão mostre `knockout_passed_count` (aprovados), nunca reprovados.

`workspace.blade.php` (raiz, um único elemento):

```blade
<div class="space-y-4">
    @if ($this->requisition === null)
        @include('panel-organization::livewire.applications.workspace.overview')
    @else
        @include('panel-organization::livewire.applications.workspace.requisition')
    @endif
</div>
```

`workspace/requisition.blade.php` nesta task:

```blade
@include('panel-organization::livewire.applications.workspace.partials.switcher')
@include('panel-organization::livewire.applications.workspace.partials.header')
```

Componentes anônimos (namespace já registrado como `panel-organization`, uso `<x-panel-organization::workspace.stage-dot :type="$stage->stage_type" />`):

`components/workspace/stage-dot.blade.php`:

```blade
@props([
    'type',
])

@php
    use He4rt\Recruitment\Stages\Enums\StageTypeEnum;

    $tone = match ($type) {
        StageTypeEnum::Screening => 'bg-yellow-500',
        StageTypeEnum::Assessment => 'bg-blue-500',
        StageTypeEnum::Interview, StageTypeEnum::Hired => 'bg-emerald-500',
        StageTypeEnum::Offer => 'bg-green-500',
        StageTypeEnum::HiddenStage => 'bg-red-500',
        default => 'bg-gray-500',
    };
@endphp

<span {{ $attributes->class([$tone, 'inline-block size-2 shrink-0 rounded-full']) }}></span>
```

`components/workspace/stage-bar.blade.php` (barra segmentada; `counts` = `[stage_id => total]`, `activeStageId` opcional para esmaecer as outras, `clickable` chama `filterStage`):

```blade
@props([
    'stages',
    'counts' => [],
    'activeStageId' => '',
    'clickable' => false,
    'height' => 'h-3',
])

@php
    use He4rt\Recruitment\Stages\Enums\StageTypeEnum;

    $tone = fn (StageTypeEnum $type): string => match ($type) {
        StageTypeEnum::Screening => 'bg-yellow-500',
        StageTypeEnum::Assessment => 'bg-blue-500',
        StageTypeEnum::Interview, StageTypeEnum::Hired => 'bg-emerald-500',
        StageTypeEnum::Offer => 'bg-green-500',
        StageTypeEnum::HiddenStage => 'bg-red-500',
        default => 'bg-gray-500',
    };
@endphp

<div {{ $attributes->class(['flex gap-px overflow-hidden rounded-full', $height]) }}>
    @foreach ($stages as $stage)
        @php
            $count = $counts[$stage->getKey()] ?? 0;
            $classes =
                ($count > 0 ? $tone($stage->stage_type) : 'bg-outline-low/20') .
                ($activeStageId !== '' && $activeStageId !== $stage->getKey() ? ' opacity-30' : '');
        @endphp

        @if ($clickable)
            <button
                type="button"
                wire:click="filterStage('{{ $stage->getKey() }}')"
                title="{{ $stage->name }}: {{ $count }}"
                class="{{ $classes }} transition hover:brightness-110"
                style="flex: {{ max($count, 0.15) }} 1 0"
            >
                <span class="sr-only">{{ $stage->name }}</span>
            </button>
        @else
            <span
                title="{{ $stage->name }}: {{ $count }}"
                class="{{ $classes }}"
                style="flex: {{ max($count, 0.15) }} 1 0"
            ></span>
        @endif
    @endforeach
</div>
```

Nos cartões e no cabeçalho, use `<x-panel-organization::workspace.stage-bar :stages="$requisition->stages" :counts="$funnel" />` e `<x-filament::pagination :paginator="$requisitions" />` para paginar. Os KPIs do cabeçalho (`total`, `new`, `unseen`, `overdue`, `closed`) vêm de `$this->stats`; o painel da eliminatória usa `knockout_passed`/`knockout_failed`/`knockout_unanswered` com botões `wire:click="filterScreening('passed'|'failed'|'unanswered')"` e o funil clicável com `activeStageId="$this->stageId"` e lista de etapas com `wire:click="filterStage(...)"`.

- [ ] **Step 6: Rodar e ver passar**

Run: `./vendor/bin/pest app-modules/panel-organization/tests/Feature/Livewire/ApplicationsWorkspaceOverviewTest.php`
Expected: PASS. `./vendor/bin/pint --dirty --format agent`; `./vendor/bin/phpstan analyse app-modules/panel-organization/src --no-progress`.

- [ ] **Step 7: Commit**

```bash
git add app-modules/panel-organization/src/Livewire/Applications app-modules/panel-organization/src/PanelOrganizationServiceProvider.php app-modules/panel-organization/resources/views/livewire/applications app-modules/panel-organization/resources/views/components/workspace app-modules/panel-organization/lang app-modules/panel-organization/tests/Feature/Livewire/ApplicationsWorkspaceOverviewTest.php
git commit -m "feat(panel-organization): add the recruiter applications workspace overview"
```

---

### Task 7: Fila com prévia — filtros, linhas, veredito da eliminatória, visto pelo time

**Files:**

- Create: `app-modules/panel-organization/resources/views/livewire/applications/workspace/partials/queue.blade.php`
- Create: `app-modules/panel-organization/resources/views/livewire/applications/workspace/partials/preview.blade.php`
- Create: `app-modules/panel-organization/resources/views/components/workspace/knockout-badge.blade.php`
- Create: `app-modules/panel-organization/resources/views/components/workspace/rating-dots.blade.php`
- Modify: `app-modules/panel-organization/resources/views/livewire/applications/workspace/requisition.blade.php` (incluir `queue`)
- Test: `app-modules/panel-organization/tests/Feature/Livewire/ApplicationsWorkspaceQueueTest.php`

**Interfaces:**

- Consumes: componente e chaves de tradução da Task 6; métodos de `Application` da Task 4 (`daysInStage`, `isOverdueInStage`, `knockoutFailsCount`, `screeningAnswersCount`, `knockoutResponses`, `averageEvaluationScore`, `isSeenByTeam`, relação `teamView`).

- [ ] **Step 1: Escrever o teste** (mesmo `beforeEach` da Task 6 — copie-o integralmente)

```php
it('lists the candidates of the requisition with knockout verdict and seen state', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->requisition->getKey())
        ->assertSee($this->passed->candidate->user->name)
        ->assertSee($this->failed->candidate->user->name)
        ->assertSee(__('panel-organization::workspace.queue.verdict_failed', ['fails' => 1, 'total' => 1]))
        ->assertSee(__('panel-organization::workspace.queue.verdict_passed'))
        ->assertSee(__('panel-organization::workspace.queue.unseen_title'))
        ->assertSee(__('panel-organization::workspace.queue.seen_title'));
});

it('hides knockout signals for a requisition without knockout questions', function (): void {
    $application = Application::factory()->recycle($this->team)->for($this->plain, 'requisition')->for(Candidate::factory()->create(), 'candidate')->create();

    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->plain->getKey())
        ->assertSee($application->candidate->user->name)
        ->assertSee(__('panel-organization::workspace.header.no_knockout'))
        ->assertDontSee(__('panel-organization::workspace.queue.verdict_passed'))
        ->assertDontSee(__('panel-organization::workspace.queue.verdict_unanswered'));
});

it('filters by screening verdict, seen state, status group, stage and search', function (): void {
    $firstStage = $this->requisition->stages()->orderBy('display_order')->first();

    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->requisition->getKey())
        ->call('filterScreening', 'failed')
        ->assertSee($this->failed->candidate->user->name)
        ->assertDontSee($this->passed->candidate->user->name)
        ->call('filterScreening', 'passed')
        ->assertSee($this->passed->candidate->user->name)
        ->assertDontSee($this->failed->candidate->user->name)
        ->call('filterScreening', 'all')
        ->call('filterSeen', 'seen')
        ->assertSee($this->seen->candidate->user->name)
        ->assertDontSee($this->passed->candidate->user->name)
        ->call('filterSeen', 'unseen')
        ->assertSee($this->passed->candidate->user->name)
        ->assertDontSee($this->seen->candidate->user->name)
        ->call('clearFilters')
        ->call('filterStatusGroup', 'active')
        ->assertSee($this->seen->candidate->user->name)
        ->assertDontSee($this->passed->candidate->user->name)
        ->call('clearFilters')
        ->call('filterStage', $firstStage->getKey())
        ->assertSet('stageId', $firstStage->getKey())
        ->call('sortBy', 'name')
        ->assertSet('sort', ApplicationListSort::Name)
        ->set('search', 'zzz-nao-existe')
        ->assertSee(__('panel-organization::workspace.queue.empty'));
});

it('previews the selected candidate without recording a team view', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->requisition->getKey())
        ->assertSee(__('panel-organization::workspace.preview.empty'))
        ->call('select', $this->failed->getKey())
        ->assertSet('selectedId', $this->failed->getKey())
        ->assertSee(__('panel-organization::workspace.preview.knockout_failed_title', ['fails' => 1, 'total' => 1]))
        ->assertSee($this->failed->tracking_code)
        ->assertSee(__('panel-organization::workspace.preview.open'));

    expect(ApplicationView::query()->where('application_id', $this->failed->getKey())->exists())->toBeFalse();
});

it('shows who saw the application in the preview', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->requisition->getKey())
        ->call('select', $this->seen->getKey())
        ->assertSee($this->seen->teamView->viewer->name);
});
```

Acrescente ao topo do arquivo `use He4rt\Applications\Enums\ApplicationListSort;`.

- [ ] **Step 2: Rodar e ver falhar**

Run: `./vendor/bin/pest app-modules/panel-organization/tests/Feature/Livewire/ApplicationsWorkspaceQueueTest.php`
Expected: FAIL (fila/prévia ausentes).

- [ ] **Step 3: Componentes Blade**

`components/workspace/knockout-badge.blade.php`:

```blade
@props(['fails' => 0, 'total' => 0, 'answers' => 0])

@if ($answers === 0)
    <span
        {{ $attributes->class(['bg-outline-low/20 text-text-low inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium']) }}
    >
        {{ __('panel-organization::workspace.queue.verdict_unanswered') }}
    </span>
@elseif ($fails > 0)
    <span
        {{ $attributes->class(['inline-flex items-center rounded-full bg-red-500/10 px-2 py-0.5 text-[11px] font-semibold text-red-600 ring-1 ring-red-500/30 ring-inset']) }}
    >
        {{ __('panel-organization::workspace.queue.verdict_failed', ['fails' => $fails, 'total' => $total]) }}
    </span>
@else
    <span
        {{ $attributes->class(['inline-flex items-center rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-semibold text-emerald-600 ring-1 ring-emerald-500/30 ring-inset']) }}
    >
        {{ __('panel-organization::workspace.queue.verdict_passed') }}
    </span>
@endif
```

`components/workspace/rating-dots.blade.php`:

```blade
@props(['score' => null, 'count' => null])

<span
    {{ $attributes->class(['inline-flex items-center gap-2']) }}
    title="{{ $score === null ? __('panel-organization::workspace.queue.no_evaluations') : __('panel-organization::workspace.queue.average', ['score' => $score]) }}"
>
    <span class="flex gap-0.5">
        @for ($i = 1; $i <= 5; $i++)
            <span
                class="{{ $score !== null && $i <= round($score) ? 'bg-emerald-500' : 'bg-outline-low/40' }} h-1.5 w-2.5 rounded-sm"
            ></span>
        @endfor
    </span>
    @if ($count !== null)
        <span class="text-text-low font-mono text-xs tabular-nums">{{ $count }}</span>
    @endif
</span>
```

- [ ] **Step 4: Fila e prévia**

Fonte visual: bloco `<div class="grid gap-4 xl:grid-cols-[minmax(0,7fr)_minmax(0,5fr)]">` de `docs.local/prototype/views/workspace/variant-e.blade.php`. Regras de adaptação:

- Linha: ponto de visto (`$application->isSeenByTeam()` → vazio com `ring-1 ring-outline-low/50`; não visto → `bg-indigo-500`), nome em `font-semibold text-text-high` quando não visto e `font-medium text-text-medium` quando visto; `<x-panel-organization::workspace.knockout-badge :fails="$application->knockoutFailsCount()" :total="$this->knockoutQuestionCount" :answers="$application->screeningAnswersCount()" />` só quando `$this->knockoutQuestionCount > 0`; borda vermelha (`shadow-[inset_3px_0_0_0_var(--color-red-500)]`) quando `hasFailedKnockout()`; `<x-panel-organization::workspace.stage-dot :type="$application->currentStage?->stage_type" />`; `<x-panel-organization::workspace.rating-dots :score="$application->averageEvaluationScore()" />`; `$application->daysInStage()` e `isOverdueInStage()`.
- Pills: grupos de status via `ApplicationStatusGroup::cases()` (`filterStatusGroup`), "Todas" via `filterStatusGroup('')`; vistos via `filterSeen('unseen'|'seen')`; eliminatória via `filterScreening('passed'|'failed')` apenas se `knockoutQuestionCount > 0`. Select de ordenação: `wire:model.live="sort"` com `ApplicationListSort::cases()` (valor `->value`, texto `__('...queue.sort_prefix', ['label' => $case->getLabel()])`).
- Sem seleção automática: `$this->selectedApplication` nulo mostra `preview.empty`.
- Prévia: cabeçalho (avatar, nome, status, headline, cargo atual, inscrição, `seen_by` com `$application->teamView?->viewer?->name` e `viewed_at->format('d/m/Y')` ou `preview.unseen`), botão `preview.open` → `$this->viewUrl($application)`; seção da eliminatória com `$application->knockoutResponses()` (cada item: `✕`/`✓`, `question->question_text` ou `preview.question_removed`, resposta traduzida: `yes`→`answer_yes`, `no`→`answer_no`, senão o valor); etapa atual; grade de sinais (experiência, localização + remoto/mudança, pretensão, disponibilidade, avaliações, comentários, carta, origem, código); habilidades top 6 com barras de nível; links.
- Paginação: `<x-filament::pagination :paginator="$this->applicationsPage" />` abaixo da fila.
- Localização: `implode(', ', array_filter([$address->city, $address->state]))`; pretensão: `sprintf('%s %s', $candidate->expected_salary_currency, number_format((float) $candidate->expected_salary, 0, ',', '.'))`; disponibilidade: `preview.immediate` quando nula ou passada, senão `format('d/m/Y')`; cargo atual: última `workExperiences` por `start_date` (`position · company_name`).

Em `requisition.blade.php` acrescente ao fim: `@include('panel-organization::livewire.applications.workspace.partials.queue')` (a fila inclui a prévia).

- [ ] **Step 5: Rodar e ver passar**

Run: `./vendor/bin/pest app-modules/panel-organization/tests/Feature/Livewire`
Expected: PASS (overview + queue). `./vendor/bin/pint --dirty --format agent`.

- [ ] **Step 6: Commit**

```bash
git add app-modules/panel-organization/resources/views app-modules/panel-organization/tests/Feature/Livewire/ApplicationsWorkspaceQueueTest.php
git commit -m "feat(panel-organization): add the candidate queue with preview to the workspace"
```

---

### Task 8: Trocar o conteúdo da página, remover a tabela antiga e reescrever os testes dela

**Files:**

- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Pages/ListApplications.php`
- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/ApplicationResource.php` (remover `table()` e o `use` de `ApplicationsTable`/`Table`)
- Delete: `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Tables/ApplicationsTable.php`
- Delete (se `rg -n "tables.columns.last-movement|tables.columns.pipeline-progress" app-modules` só apontar para a tabela removida): `app-modules/panel-organization/resources/views/filament/tables/columns/last-movement.blade.php`, `pipeline-progress.blade.php`
- Delete: `app-modules/panel-organization/tests/Feature/Filament/Application/ApplicationTableNullSafetyTest.php`, `RequisitionFilterPublishedOnlyTest.php`
- Modify: `app-modules/panel-organization/tests/Feature/Filament/Application/OwnerApplicationAccessTest.php` (segundo teste)
- Create: `app-modules/panel-organization/tests/Feature/Filament/Application/ListApplicationsWorkspaceTest.php`

**Interfaces:**

- Consumes: `ApplicationsWorkspace` (Task 6/7).

- [ ] **Step 1: Escrever o teste da página** (`beforeEach` igual ao de `ApplicationTableNullSafetyTest.php` atual — leia-o antes de apagar e reproduza a criação de recrutador `SuperAdmin`, painel e tenant)

```php
it('renders the workspace instead of the table', function (): void {
    livewire(ListApplications::class)
        ->assertOk()
        ->assertSeeLivewire(ApplicationsWorkspace::class)
        ->assertSee(__('panel-organization::workspace.overview.title'));
});

it('renders without error when a requisition has no stages', function (): void {
    $requisition = JobRequisition::factory()->recycle($this->team)->create(['status' => RequisitionStatusEnum::Published]);
    $requisition->stages()->delete();
    Application::factory()->recycle($this->team)->for($requisition, 'requisition')->withoutCurrentStage()->create();

    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $requisition->getKey())
        ->assertOk();
});

it('shows only published requisitions by default', function (): void {
    $published = JobRequisition::factory()->recycle($this->team)->create(['status' => RequisitionStatusEnum::Published]);
    JobPosting::factory()->for($published, 'jobRequisition')->createOne(['title' => 'PUBLISHED_VACANCY_OPTION']);
    $draft = JobRequisition::factory()->recycle($this->team)->create(['status' => RequisitionStatusEnum::Draft]);
    JobPosting::factory()->for($draft, 'jobRequisition')->createOne(['title' => 'DRAFT_VACANCY_OPTION']);

    livewire(ListApplications::class)
        ->assertSee('PUBLISHED_VACANCY_OPTION')
        ->assertDontSee('DRAFT_VACANCY_OPTION');
});
```

Em `OwnerApplicationAccessTest.php`, substitua o teste "does not show an edit button in the applications table" por:

```php
it('lets the team owner open the workspace', function (): void {
    livewire(ListApplications::class)
        ->assertOk()
        ->assertSeeLivewire(ApplicationsWorkspace::class);
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `./vendor/bin/pest app-modules/panel-organization/tests/Feature/Filament/Application/ListApplicationsWorkspaceTest.php`
Expected: FAIL (`assertSeeLivewire`).

- [ ] **Step 3: Implementar**

`ListApplications.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\ApplicationResource;
use He4rt\Organization\Livewire\Applications\ApplicationsWorkspace;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Livewire::make(ApplicationsWorkspace::class),
        ]);
    }
}
```

Em `ApplicationResource.php` remova o método `table()` e os imports que ficarem sem uso. Apague `ApplicationsTable.php`, as duas views de coluna (após confirmar com `rg` que ninguém mais as usa) e os dois testes antigos. Confira com `rg -n "last_movement|pipeline_progress|group\.job" app-modules/panel-organization` se alguma chave de tradução ficou órfã; remova só as órfãs de `lang/{en,pt_BR}/filament.php`.

- [ ] **Step 4: Rodar e ver passar**

Run: `./vendor/bin/pest app-modules/panel-organization/tests`
Expected: PASS. `./vendor/bin/pint --dirty --format agent`; `./vendor/bin/phpstan analyse app-modules/panel-organization/src --no-progress`; `npm run build` (tem de compilar sem erro).

- [ ] **Step 5: Commit**

```bash
git add -A app-modules/panel-organization
git commit -m "feat(panel-organization): replace the applications table with the recruiter workspace"
```

---

## Self-review (feito ao escrever o plano)

- Cobertura da spec: panorama (T5, T6), fila/prévia/filtros/ordenação/paginação (T4, T7), eliminatória em cartão/cabeçalho/fila/prévia (T5, T6, T7), visto pelo time e gatilho único (T2, T3), substituição da tabela (T8), i18n (T1, T5, T6), testes antigos preservados em intenção (T8), `?job` na URL (T6), vaga de outro time volta ao panorama (T6).
- Tipos consistentes: `ApplicationStatusGroup::values()` usado em T4/T5; `Application::STAGE_SINCE_SQL` público usado em T5; `RequisitionApplicationStats` com props `total,new,active,closed,unseen,overdue,knockoutPassed,knockoutFailed,knockoutUnanswered,byStage` em T5/T6/T7; `CountKnockoutQuestionsByRequisition::execute(Collection)` em T5/T6; `ApplicationViewFactory::forApplication()` em T4/T5/T6.
- Sem placeholders: cada step traz o código ou a fonte exata (arquivo do protótipo + regras de adaptação).
