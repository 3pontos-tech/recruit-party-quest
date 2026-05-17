# Triagem Automática de Candidatos — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Avaliar automaticamente as respostas do questionário de triagem no momento da candidatura, marcando reprovações, e — quando a vaga opta-in via flag — reprovar (`Rejected`) ou avançar (`InReview`) o candidato automaticamente.

**Architecture:** O módulo `screening` ganha um contrato de avaliação por tipo de pergunta (Strategy via `QuestionTypeRegistry`), uma Action que sempre marca `is_knockout_fail` e emite o evento `ScreeningEvaluated`. O módulo `applications` escuta esse evento e, gated pela flag `auto_screening_transition` da `JobRequisition`, dispara a transição existente `NewTransition` com ator nulo (`byUserId = null` = sistema). A UI livre `KeyValue` do critério é substituída por um schema Filament tipado por tipo de pergunta.

**Tech Stack:** Laravel 12, Filament v5, Livewire v4, Pest v4, PostgreSQL, módulos `internachi/modular` (`He4rt\Screening`, `He4rt\Applications`, `He4rt\Recruitment`).

---

## Decisões consolidadas (do grill-me)

1. Dupla semântica: falhou critério → `Rejected`; passou em todos e há ≥1 knockout → avança stage.
2. Escopo: só perguntas da `JobRequisition` (no ato da candidatura).
3. `screening` emite evento, `applications` escuta (Observer).
4. UX: form tipado por tipo (substitui `KeyValue`), semântica "aprovar se".
5. Ator: `byUserId` nullable; `null` = automático; reusa `NewTransition`.
6. MultipleChoice: aprova se marcou ≥1 das aceitas.
7. Desacoplado: avaliação/`is_knockout_fail` sempre; flag só automatiza a transição.
8. Novo case `ScreeningKnockout` no `RejectionReasonCategoryEnum` + notes.
9. Toggle único "Triagem automática de candidatos" na aba Settings, `default(false)`.
10. Notificação fora de escopo (issue #158) — apenas garantir que o evento `ApplicationStatusChanged` dispara.
11. Point-in-time: editar critério não retroage; avaliação só na submissão. Critérios legados/malformados nunca reprovam (avaliação defensiva).

## Fluxo

```
JobApplicationForm::submit()
  → StoreApplication            (status=New, current_stage_id = 1º stage)
  → StoreScreeningResponse      (persiste respostas)
  → EvaluateScreeningResponses  (screening)  ── SEMPRE
        p/ cada pergunta is_knockout c/ criteria:
          QuestionTypeRegistry::get(type)::evaluateKnockout(criteria, answer)
          falhou → ScreeningResponse.is_knockout_fail = true
        dispatch ScreeningEvaluated(application, anyFailed, hadKnockout)
            │
            ▼
  HandleScreeningKnockoutTransition (applications, listener)
        requisition.auto_screening_transition == false → return
        application.status != New → return
        anyFailed            → NewTransition → Rejected (ScreeningKnockout, byUserId=null)
        !anyFailed && hadKnockout → NewTransition → InReview (forwardToReview avança stage)
        else → return
```

## File Structure

- `app-modules/applications/src/Enums/RejectionReasonCategoryEnum.php` — +case `ScreeningKnockout`
- `app-modules/applications/lang/{en,pt_BR}/enums.php` — +label
- `app-modules/applications/src/Services/Transitions/TransitionData.php` — `byUserId` nullable
- `app-modules/applications/src/Services/Transitions/AbstractApplicationTransition.php` — ator nulo
- `app-modules/applications/src/Events/ApplicationStatusChanged.php` — `?User $by`
- `app-modules/screening/src/Contracts/QuestionTypeContract.php` — +2 métodos
- `app-modules/screening/src/QuestionTypes/{YesNo,Number,SingleChoice,MultipleChoice,Text}Type.php` — implementam knockout
- `app-modules/screening/src/Events/ScreeningEvaluated.php` — novo
- `app-modules/screening/src/Actions/ScreeningResponse/EvaluateScreeningResponses.php` — novo
- `app-modules/applications/src/Listeners/HandleScreeningKnockoutTransition.php` — novo
- `app-modules/applications/src/ApplicationsServiceProvider.php` — registra listener
- `app-modules/screening/src/Livewire/JobApplicationForm.php` — chama a Action
- `app-modules/screening/src/Filament/Schemas/ScreeningQuestionsFormSchema.php` — schema tipado
- `app-modules/screening/src/Filament/RelationManagers/ScreeningQuestionsRelationManager.php` — schema tipado
- `app-modules/screening/lang/{en,pt_BR}/filament.php` — i18n critério
- `app-modules/recruitment/database/migrations/2026_05_17_000000_add_auto_screening_transition_to_recruitment_job_requisitions.php` — novo
- `app-modules/recruitment/src/Requisitions/Models/JobRequisition.php` — cast + @property
- `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Schemas/JobRequisitionForm.php` — toggle
- `app-modules/recruitment/lang/{en,pt_BR}/filament.php` — i18n flag

---

### Task 1: Adicionar `ScreeningKnockout` ao `RejectionReasonCategoryEnum`

**Files:**

- Modify: `app-modules/applications/src/Enums/RejectionReasonCategoryEnum.php:20`
- Modify: `app-modules/applications/lang/en/enums.php:83`
- Modify: `app-modules/applications/lang/pt_BR/enums.php:83`
- Test: `app-modules/applications/tests/Unit/RejectionReasonCategoryEnumTest.php`

- [ ] **Step 1: Escrever o teste que falha**

Criar `app-modules/applications/tests/Unit/RejectionReasonCategoryEnumTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Enums\RejectionReasonCategoryEnum;

it('has a ScreeningKnockout case', function (): void {
    expect(RejectionReasonCategoryEnum::ScreeningKnockout->value)->toBe('screening_knockout');
});

it('resolves a non-empty label for ScreeningKnockout', function (): void {
    expect(RejectionReasonCategoryEnum::ScreeningKnockout->getLabel())
        ->toBeString()
        ->not->toBeEmpty()
        ->not->toContain('rejection_reason_category');
});
```

- [ ] **Step 2: Rodar o teste e ver falhar**

Run: `php artisan test --compact --filter=RejectionReasonCategoryEnumTest`
Expected: FAIL — `Undefined constant ... ScreeningKnockout`

- [ ] **Step 3: Adicionar o case no enum**

Em `RejectionReasonCategoryEnum.php`, após a linha `case Other = 'other';` (linha 20), adicionar:

```php
    case ScreeningKnockout = 'screening_knockout';
```

- [ ] **Step 4: Adicionar labels i18n**

Em `app-modules/applications/lang/en/enums.php`, dentro do array `'rejection_reason_category'`, após o bloco `'other' => ['label' => 'Other'],`:

```php
        'screening_knockout' => [
            'label' => 'Automatic Screening',
        ],
```

Em `app-modules/applications/lang/pt_BR/enums.php`, mesma posição:

```php
        'screening_knockout' => [
            'label' => 'Triagem Automática',
        ],
```

- [ ] **Step 5: Rodar o teste e ver passar**

Run: `php artisan test --compact --filter=RejectionReasonCategoryEnumTest`
Expected: PASS

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/applications/src/Enums/RejectionReasonCategoryEnum.php app-modules/applications/lang app-modules/applications/tests/Unit/RejectionReasonCategoryEnumTest.php
git commit -m "feat(applications): add ScreeningKnockout rejection reason"
```

---

### Task 2: Tornar o ator da transição nulo (`byUserId` nullable + evento)

**Contexto:** `TransitionData::byUserId` é `string` não-nullable e `AbstractApplicationTransition::handle()` faz `User::query()->findOrFail($data->byUserId)`. O banco já aceita `moved_by`/`rejected_by` nulos. Tornamos a camada de aplicação capaz de expressar "transição feita pelo sistema" (`null`) sem usuário fake. O evento `ApplicationStatusChanged` continua disparando (Decisão 10) com `by` possivelmente nulo.

**Files:**

- Modify: `app-modules/applications/src/Services/Transitions/TransitionData.php:14,52`
- Modify: `app-modules/applications/src/Services/Transitions/AbstractApplicationTransition.php:46-79`
- Modify: `app-modules/applications/src/Events/ApplicationStatusChanged.php:21`
- Test: `app-modules/applications/tests/Feature/Transitions/AutomaticActorTransitionTest.php`

- [ ] **Step 1: Escrever o teste que falha**

Criar `app-modules/applications/tests/Feature/Transitions/AutomaticActorTransitionTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Events\ApplicationStatusChanged;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\TransitionData;
use Illuminate\Support\Facades\Event;

it('performs a New → Rejected transition with a null actor (system)', function (): void {
    $application = Application::factory()->withStatus(ApplicationStatusEnum::New)->create();

    $data = TransitionData::fromArray([
        'to_status' => ApplicationStatusEnum::Rejected,
        'rejection_reason_category' => RejectionReasonCategoryEnum::ScreeningKnockout->value,
        'rejection_reason_details' => 'Failed: Q1',
    ], byUserId: null);

    $application->current_step->handle($data);

    $application->refresh();

    expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($application->rejected_by)->toBeNull()
        ->and($application->rejection_reason_category)->toBe(RejectionReasonCategoryEnum::ScreeningKnockout);

    $movement = $application->getLastMovement();
    expect($movement->moved_by)->toBeNull();
});

it('still dispatches ApplicationStatusChanged with a null actor', function (): void {
    Event::fake([ApplicationStatusChanged::class]);

    $application = Application::factory()->withStatus(ApplicationStatusEnum::New)->create();

    $data = TransitionData::fromArray([
        'to_status' => ApplicationStatusEnum::Withdrawn,
    ], byUserId: null);

    $application->current_step->handle($data);

    Event::assertDispatched(ApplicationStatusChanged::class, function (ApplicationStatusChanged $event): bool {
        return $event->by === null
            && $event->toStatus === ApplicationStatusEnum::Withdrawn->value;
    });
});
```

- [ ] **Step 2: Rodar o teste e ver falhar**

Run: `php artisan test --compact --filter=AutomaticActorTransitionTest`
Expected: FAIL — `TransitionData::fromArray(): Argument #2 ($byUserId) must be of type string, null given` ou `findOrFail(null)`

- [ ] **Step 3: Tornar `byUserId` nullable em `TransitionData`**

Em `TransitionData.php` linha 14, alterar a propriedade do construtor:

```php
        public ?string $byUserId,
```

Em `TransitionData.php`, alterar a assinatura de `fromArray` (linha ~52) de `string $byUserId` para:

```php
    public static function fromArray(array $data, ?string $byUserId = null): self
```

(O corpo de `fromArray` e o `toArray()` permanecem inalterados — `'by_user_id' => $this->byUserId` já aceita null.)

- [ ] **Step 4: Tornar o ator nulo em `ApplicationStatusChanged`**

Em `ApplicationStatusChanged.php` linha 21, alterar:

```php
        public ?User $by,
```

- [ ] **Step 5: Guardar o `findOrFail` em `AbstractApplicationTransition::handle()`**

Em `AbstractApplicationTransition.php`, substituir o bloco final do método `handle()` (a partir de `$toStatus = $this->application->refresh()->status->value;`) por:

```php
        $toStatus = $this->application->refresh()->status->value;

        // TODO (issue #158): ApplicationStatusChanged ainda não possui listeners de
        // notificação. by === null indica transição automática (sistema).
        if ($fromStatus !== $toStatus) {
            $by = $data->byUserId !== null
                ? User::query()->findOrFail($data->byUserId)
                : null;

            event(new ApplicationStatusChanged(
                $this->application,
                $fromStatus,
                $toStatus,
                $by,
                $data->toArray()
            ));
        }
```

- [ ] **Step 6: Rodar a suíte de transições inteira (regressão)**

Run: `php artisan test --compact app-modules/applications/tests/Feature/Transitions`
Expected: PASS — todos os testes existentes (`NewTransitionTest`, `InProgressTransitionTest`, `TransitionDataTest`, etc.) continuam verdes + os 2 novos passam.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/applications/src/Services/Transitions/TransitionData.php app-modules/applications/src/Services/Transitions/AbstractApplicationTransition.php app-modules/applications/src/Events/ApplicationStatusChanged.php app-modules/applications/tests/Feature/Transitions/AutomaticActorTransitionTest.php
git commit -m "feat(applications): allow null actor for system-driven transitions"
```

---

### Task 3: Estender `QuestionTypeContract` com knockout + implementar por tipo

**Contexto:** Cada tipo de pergunta passa a definir seu próprio schema Filament de critério e sua própria lógica de avaliação (Strategy). A avaliação é **defensiva**: critério ausente/malformado/legado nunca reprova (`true` = passou). `$answer` recebido é o valor já extraído de `response_value['value']`.

**Files:**

- Modify: `app-modules/screening/src/Contracts/QuestionTypeContract.php:50`
- Modify: `app-modules/screening/src/QuestionTypes/YesNoType.php`
- Modify: `app-modules/screening/src/QuestionTypes/NumberType.php`
- Modify: `app-modules/screening/src/QuestionTypes/SingleChoiceType.php`
- Modify: `app-modules/screening/src/QuestionTypes/MultipleChoiceType.php`
- Modify: `app-modules/screening/src/QuestionTypes/TextType.php`
- Test: `app-modules/screening/tests/Unit/QuestionTypes/EvaluateKnockoutTest.php`

- [ ] **Step 1: Escrever o teste que falha**

Criar `app-modules/screening/tests/Unit/QuestionTypes/EvaluateKnockoutTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Screening\QuestionTypes\MultipleChoiceType;
use He4rt\Screening\QuestionTypes\NumberType;
use He4rt\Screening\QuestionTypes\SingleChoiceType;
use He4rt\Screening\QuestionTypes\TextType;
use He4rt\Screening\QuestionTypes\YesNoType;

describe('YesNoType::evaluateKnockout', function (): void {
    it('passes when answer equals expected', function (): void {
        expect(YesNoType::evaluateKnockout(['expected' => 'yes'], 'yes'))->toBeTrue();
    });
    it('fails when answer differs from expected', function (): void {
        expect(YesNoType::evaluateKnockout(['expected' => 'yes'], 'no'))->toBeFalse();
    });
    it('passes (defensive) when criteria is incomplete', function (): void {
        expect(YesNoType::evaluateKnockout([], 'no'))->toBeTrue();
    });
});

describe('NumberType::evaluateKnockout', function (): void {
    it('passes when answer >= threshold', function (): void {
        expect(NumberType::evaluateKnockout(['operator' => '>=', 'value' => 3], '3'))->toBeTrue();
    });
    it('fails when answer < threshold for >=', function (): void {
        expect(NumberType::evaluateKnockout(['operator' => '>=', 'value' => 3], '2'))->toBeFalse();
    });
    it('handles =, >, <, <= operators', function (): void {
        expect(NumberType::evaluateKnockout(['operator' => '=', 'value' => 5], '5'))->toBeTrue();
        expect(NumberType::evaluateKnockout(['operator' => '>', 'value' => 5], '5'))->toBeFalse();
        expect(NumberType::evaluateKnockout(['operator' => '<', 'value' => 5], '4'))->toBeTrue();
        expect(NumberType::evaluateKnockout(['operator' => '<=', 'value' => 5], '5'))->toBeTrue();
    });
    it('passes (defensive) when criteria/value missing or non-numeric', function (): void {
        expect(NumberType::evaluateKnockout(['operator' => '>='], 'abc'))->toBeTrue();
        expect(NumberType::evaluateKnockout(['minimum' => '3'], '2'))->toBeTrue();
    });
});

describe('SingleChoiceType::evaluateKnockout', function (): void {
    it('passes when answer is in accepted set', function (): void {
        expect(SingleChoiceType::evaluateKnockout(['accepted' => ['python', 'go']], 'go'))->toBeTrue();
    });
    it('fails when answer is not accepted', function (): void {
        expect(SingleChoiceType::evaluateKnockout(['accepted' => ['python']], 'java'))->toBeFalse();
    });
    it('passes (defensive) when accepted list is missing', function (): void {
        expect(SingleChoiceType::evaluateKnockout([], 'java'))->toBeTrue();
    });
});

describe('MultipleChoiceType::evaluateKnockout', function (): void {
    it('passes when at least one selected is accepted', function (): void {
        expect(MultipleChoiceType::evaluateKnockout(['accepted' => ['react', 'vue']], ['vue', 'angular']))->toBeTrue();
    });
    it('fails when none selected is accepted', function (): void {
        expect(MultipleChoiceType::evaluateKnockout(['accepted' => ['react']], ['vue', 'angular']))->toBeFalse();
    });
    it('passes (defensive) when accepted list missing', function (): void {
        expect(MultipleChoiceType::evaluateKnockout([], ['vue']))->toBeTrue();
    });
});

describe('TextType::evaluateKnockout', function (): void {
    it('never knocks out (always passes)', function (): void {
        expect(TextType::evaluateKnockout(['anything' => 'x'], 'whatever'))->toBeTrue();
    });
    it('exposes an empty knockout criteria schema', function (): void {
        expect(TextType::knockoutCriteriaSchema())->toBe([]);
    });
});
```

- [ ] **Step 2: Rodar o teste e ver falhar**

Run: `php artisan test --compact --filter=EvaluateKnockoutTest`
Expected: FAIL — `Call to undefined method ...::evaluateKnockout()`

- [ ] **Step 3: Adicionar os métodos ao contrato**

Em `QuestionTypeContract.php`, antes do fechamento da interface (após o método `component()`), adicionar:

```php

    /**
     * Filament form components for the typed knockout criteria.
     * Returns [] for types that do not support knockout.
     *
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    public static function knockoutCriteriaSchema(): array;

    /**
     * Whether the candidate's answer PASSES the knockout criteria.
     * true = approved (no knockout), false = failed (knockout).
     * Must be defensive: incomplete/legacy criteria return true.
     *
     * @param  array<string, mixed>  $criteria
     */
    public static function evaluateKnockout(array $criteria, mixed $answer): bool;
```

- [ ] **Step 4: Implementar em `YesNoType`**

Em `YesNoType.php`, adicionar os `use` no topo:

```php
use Filament\Forms\Components\Select;
```

E antes do fechamento da classe (após `component()`):

```php
    public static function knockoutCriteriaSchema(): array
    {
        return [
            Select::make('knockout_criteria.expected')
                ->label(__('screening::filament.question.fields.knockout_expected'))
                ->options([
                    'yes' => __('screening::question_types.yes_no.yes'),
                    'no' => __('screening::question_types.yes_no.no'),
                ])
                ->required(),
        ];
    }

    public static function evaluateKnockout(array $criteria, mixed $answer): bool
    {
        if (! isset($criteria['expected'])) {
            return true;
        }

        return $criteria['expected'] === $answer;
    }
```

- [ ] **Step 5: Implementar em `NumberType`**

Em `NumberType.php`, adicionar o `use`:

```php
use Filament\Forms\Components\Select;
```

E antes do fechamento da classe:

```php
    public static function knockoutCriteriaSchema(): array
    {
        return [
            Select::make('knockout_criteria.operator')
                ->label(__('screening::filament.question.fields.knockout_operator'))
                ->options([
                    '>=' => '≥',
                    '<=' => '≤',
                    '=' => '=',
                    '>' => '>',
                    '<' => '<',
                ])
                ->default('>=')
                ->required(),
            TextInput::make('knockout_criteria.value')
                ->label(__('screening::filament.question.fields.knockout_value'))
                ->numeric()
                ->required(),
        ];
    }

    public static function evaluateKnockout(array $criteria, mixed $answer): bool
    {
        $operator = $criteria['operator'] ?? null;

        if ($operator === null || ! array_key_exists('value', $criteria)) {
            return true;
        }

        if (! is_numeric($answer) || ! is_numeric($criteria['value'])) {
            return true;
        }

        $a = (float) $answer;
        $t = (float) $criteria['value'];

        return match ($operator) {
            '>=' => $a >= $t,
            '<=' => $a <= $t,
            '=' => $a === $t,
            '>' => $a > $t,
            '<' => $a < $t,
            default => true,
        };
    }
```

- [ ] **Step 6: Implementar em `SingleChoiceType`**

Em `SingleChoiceType.php`, adicionar os `use`:

```php
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Utilities\Get;
```

E antes do fechamento da classe:

```php
    public static function knockoutCriteriaSchema(): array
    {
        return [
            CheckboxList::make('knockout_criteria.accepted')
                ->label(__('screening::filament.question.fields.knockout_accepted'))
                ->options(fn (Get $get): array => collect($get('settings.choices') ?? [])
                    ->mapWithKeys(fn (array $choice): array => [
                        (string) ($choice['value'] ?? '') => (string) ($choice['label'] ?? ''),
                    ])
                    ->all())
                ->columns(2)
                ->required(),
        ];
    }

    public static function evaluateKnockout(array $criteria, mixed $answer): bool
    {
        $accepted = $criteria['accepted'] ?? null;

        if (! is_array($accepted) || $accepted === []) {
            return true;
        }

        return in_array($answer, $accepted, true);
    }
```

- [ ] **Step 7: Implementar em `MultipleChoiceType`**

Em `MultipleChoiceType.php`, adicionar os `use`:

```php
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Utilities\Get;
```

E antes do fechamento da classe:

```php
    public static function knockoutCriteriaSchema(): array
    {
        return [
            CheckboxList::make('knockout_criteria.accepted')
                ->label(__('screening::filament.question.fields.knockout_accepted'))
                ->helperText(__('screening::filament.question.fields.knockout_accepted_multi_help'))
                ->options(fn (Get $get): array => collect($get('settings.choices') ?? [])
                    ->mapWithKeys(fn (array $choice): array => [
                        (string) ($choice['value'] ?? '') => (string) ($choice['label'] ?? ''),
                    ])
                    ->all())
                ->columns(2)
                ->required(),
        ];
    }

    public static function evaluateKnockout(array $criteria, mixed $answer): bool
    {
        $accepted = $criteria['accepted'] ?? null;

        if (! is_array($accepted) || $accepted === []) {
            return true;
        }

        $selected = is_array($answer) ? $answer : [$answer];

        return array_intersect($selected, $accepted) !== [];
    }
```

- [ ] **Step 8: Implementar em `TextType` (sem knockout)**

Em `TextType.php`, antes do fechamento da classe:

```php
    public static function knockoutCriteriaSchema(): array
    {
        return [];
    }

    public static function evaluateKnockout(array $criteria, mixed $answer): bool
    {
        return true;
    }
```

- [ ] **Step 9: Adicionar chaves i18n usadas pelos schemas**

Em `app-modules/screening/lang/en/filament.php`, dentro de `'question' => ['fields' => [ ... ]]`, adicionar:

```php
            'knockout_expected' => 'Approve the candidate if the answer is',
            'knockout_operator' => 'Approve if the number is',
            'knockout_value' => 'Reference value',
            'knockout_accepted' => 'Answers that approve',
            'knockout_accepted_multi_help' => 'The candidate passes if they select at least one of these.',
```

Em `app-modules/screening/lang/pt_BR/filament.php`, mesmo bloco:

```php
            'knockout_expected' => 'Aprovar o candidato se a resposta for',
            'knockout_operator' => 'Aprovar se o número for',
            'knockout_value' => 'Valor de referência',
            'knockout_accepted' => 'Respostas que aprovam',
            'knockout_accepted_multi_help' => 'O candidato passa se marcar pelo menos uma destas.',
```

- [ ] **Step 10: Rodar o teste e ver passar**

Run: `php artisan test --compact --filter=EvaluateKnockoutTest`
Expected: PASS

- [ ] **Step 11: Rodar a suíte unit de QuestionTypes (regressão)**

Run: `php artisan test --compact app-modules/screening/tests/Unit/QuestionTypes`
Expected: PASS — testes existentes (`YesNoTypeTest`, etc.) continuam verdes.

- [ ] **Step 12: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/screening/src/Contracts/QuestionTypeContract.php app-modules/screening/src/QuestionTypes app-modules/screening/lang app-modules/screening/tests/Unit/QuestionTypes/EvaluateKnockoutTest.php
git commit -m "feat(screening): add typed knockout criteria schema and evaluation per question type"
```

---

### Task 4: Criar o evento `ScreeningEvaluated`

**Files:**

- Create: `app-modules/screening/src/Events/ScreeningEvaluated.php`
- Test: `app-modules/screening/tests/Unit/Events/ScreeningEvaluatedTest.php`

- [ ] **Step 1: Escrever o teste que falha**

Criar `app-modules/screening/tests/Unit/Events/ScreeningEvaluatedTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Screening\Events\ScreeningEvaluated;

it('carries the application and the evaluation flags', function (): void {
    $application = Application::factory()->create();

    $event = new ScreeningEvaluated($application, anyKnockoutFailed: true, hadKnockoutCriteria: true);

    expect($event->application->is($application))->toBeTrue()
        ->and($event->anyKnockoutFailed)->toBeTrue()
        ->and($event->hadKnockoutCriteria)->toBeTrue();
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `php artisan test --compact --filter=ScreeningEvaluatedTest`
Expected: FAIL — class `ScreeningEvaluated` not found

- [ ] **Step 3: Criar o evento**

Criar `app-modules/screening/src/Events/ScreeningEvaluated.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Screening\Events;

use He4rt\Applications\Models\Application;

final class ScreeningEvaluated
{
    public function __construct(
        public Application $application,
        public bool $anyKnockoutFailed,
        public bool $hadKnockoutCriteria,
    ) {}
}
```

- [ ] **Step 4: Rodar e ver passar**

Run: `php artisan test --compact --filter=ScreeningEvaluatedTest`
Expected: PASS

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/screening/src/Events/ScreeningEvaluated.php app-modules/screening/tests/Unit/Events/ScreeningEvaluatedTest.php
git commit -m "feat(screening): add ScreeningEvaluated event"
```

---

### Task 5: Criar a Action `EvaluateScreeningResponses`

**Contexto:** Sempre marca `is_knockout_fail` nas respostas que falham (Decisão 7, independente da flag) e emite `ScreeningEvaluated`. Considera apenas perguntas `is_knockout === true` com `knockout_criteria` não-vazio. Resposta ausente/branca para pergunta knockout = passa (Decisão 11).

**Files:**

- Create: `app-modules/screening/src/Actions/ScreeningResponse/EvaluateScreeningResponses.php`
- Test: `app-modules/screening/tests/Feature/Actions/EvaluateScreeningResponsesTest.php`

- [ ] **Step 1: Escrever o teste que falha**

Criar `app-modules/screening/tests/Feature/Actions/EvaluateScreeningResponsesTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Actions\ScreeningResponse\EvaluateScreeningResponses;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Events\ScreeningEvaluated;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    $this->requisition = JobRequisition::factory()->create();
    $this->application = Application::factory()->create([
        'requisition_id' => $this->requisition->id,
        'team_id' => $this->requisition->team_id,
    ]);
});

function knockoutQuestion(JobRequisition $req, array $criteria): ScreeningQuestion
{
    return ScreeningQuestion::factory()
        ->for($req, 'screenable')
        ->state([
            'team_id' => $req->team_id,
            'question_type' => QuestionTypeEnum::YesNo,
            'settings' => [],
            'is_required' => true,
            'is_knockout' => true,
            'knockout_criteria' => $criteria,
        ])
        ->create();
}

it('marks is_knockout_fail true when the answer fails the criteria', function (): void {
    Event::fake([ScreeningEvaluated::class]);
    $question = knockoutQuestion($this->requisition, ['expected' => 'yes']);

    $response = ScreeningResponse::query()->create([
        'team_id' => $this->requisition->team_id,
        'application_id' => $this->application->id,
        'question_id' => $question->id,
        'response_value' => ['value' => 'no'],
    ]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    expect($response->refresh()->is_knockout_fail)->toBeTrue();

    Event::assertDispatched(ScreeningEvaluated::class, fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === true && $e->hadKnockoutCriteria === true);
});

it('keeps is_knockout_fail false when the answer passes', function (): void {
    Event::fake([ScreeningEvaluated::class]);
    $question = knockoutQuestion($this->requisition, ['expected' => 'yes']);

    $response = ScreeningResponse::query()->create([
        'team_id' => $this->requisition->team_id,
        'application_id' => $this->application->id,
        'question_id' => $question->id,
        'response_value' => ['value' => 'yes'],
    ]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    expect($response->refresh()->is_knockout_fail)->toBeFalse();

    Event::assertDispatched(ScreeningEvaluated::class, fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === false && $e->hadKnockoutCriteria === true);
});

it('reports hadKnockoutCriteria false when there are no knockout questions', function (): void {
    Event::fake([ScreeningEvaluated::class]);

    ScreeningQuestion::factory()
        ->for($this->requisition, 'screenable')
        ->state([
            'team_id' => $this->requisition->team_id,
            'question_type' => QuestionTypeEnum::Text,
            'settings' => [],
            'is_knockout' => false,
            'knockout_criteria' => null,
        ])
        ->create();

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    Event::assertDispatched(ScreeningEvaluated::class, fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === false && $e->hadKnockoutCriteria === false);
});

it('treats a missing answer to a knockout question as a pass', function (): void {
    Event::fake([ScreeningEvaluated::class]);
    knockoutQuestion($this->requisition, ['expected' => 'yes']);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    Event::assertDispatched(ScreeningEvaluated::class, fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === false && $e->hadKnockoutCriteria === true);
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `php artisan test --compact --filter=EvaluateScreeningResponsesTest`
Expected: FAIL — class `EvaluateScreeningResponses` not found

- [ ] **Step 3: Criar a Action**

Criar `app-modules/screening/src/Actions/ScreeningResponse/EvaluateScreeningResponses.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Screening\Actions\ScreeningResponse;

use He4rt\Applications\Models\Application;
use He4rt\Screening\Events\ScreeningEvaluated;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;
use He4rt\Screening\QuestionTypes\QuestionTypeRegistry;

final class EvaluateScreeningResponses
{
    public function execute(Application $application): void
    {
        $application->loadMissing(['requisition.screeningQuestions', 'screeningResponses']);

        $questions = $application->requisition?->screeningQuestions ?? collect();

        $responsesByQuestion = $application->screeningResponses->keyBy('question_id');

        $hadKnockoutCriteria = false;
        $anyKnockoutFailed = false;

        foreach ($questions as $question) {
            /** @var ScreeningQuestion $question */
            if (! $question->is_knockout) {
                continue;
            }

            $criteria = $question->knockout_criteria ?? [];

            if ($criteria === []) {
                continue;
            }

            $hadKnockoutCriteria = true;

            /** @var ScreeningResponse|null $response */
            $response = $responsesByQuestion->get($question->getKey());

            if ($response === null) {
                continue;
            }

            $answer = $response->response_value['value'] ?? null;

            if ($answer === null || $answer === '' || $answer === []) {
                continue;
            }

            $typeClass = QuestionTypeRegistry::get($question->question_type);

            $passed = $typeClass::evaluateKnockout($criteria, $answer);

            if (! $passed) {
                $anyKnockoutFailed = true;
                $response->update(['is_knockout_fail' => true]);
            }
        }

        event(new ScreeningEvaluated(
            $application,
            anyKnockoutFailed: $anyKnockoutFailed,
            hadKnockoutCriteria: $hadKnockoutCriteria,
        ));
    }
}
```

- [ ] **Step 4: Rodar e ver passar**

Run: `php artisan test --compact --filter=EvaluateScreeningResponsesTest`
Expected: PASS

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/screening/src/Actions/ScreeningResponse/EvaluateScreeningResponses.php app-modules/screening/tests/Feature/Actions/EvaluateScreeningResponsesTest.php
git commit -m "feat(screening): add EvaluateScreeningResponses action marking knockout failures"
```

---

### Task 6: Adicionar a flag `auto_screening_transition` na `JobRequisition`

**Contexto:** Coluna boolean `default(false)` (opt-in, deploy inerte). Cast + @property + toggle na aba Settings do form da organização (Decisão 9).

**Files:**

- Create: `app-modules/recruitment/database/migrations/2026_05_17_000000_add_auto_screening_transition_to_recruitment_job_requisitions.php`
- Modify: `app-modules/recruitment/src/Requisitions/Models/JobRequisition.php:62,197`
- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Schemas/JobRequisitionForm.php:295`
- Modify: `app-modules/recruitment/lang/en/filament.php`
- Modify: `app-modules/recruitment/lang/pt_BR/filament.php`
- Test: `app-modules/recruitment/tests/Feature/AutoScreeningTransitionFlagTest.php`

- [ ] **Step 1: Escrever o teste que falha**

Criar `app-modules/recruitment/tests/Feature/AutoScreeningTransitionFlagTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Models\JobRequisition;

it('defaults auto_screening_transition to false', function (): void {
    $requisition = JobRequisition::factory()->create();

    // default(false) é do banco — verificar o valor persistido via fresh().
    expect($requisition->fresh()->auto_screening_transition)->toBeFalse();
});

it('casts auto_screening_transition to boolean', function (): void {
    $requisition = JobRequisition::factory()->create(['auto_screening_transition' => true]);

    expect($requisition->fresh()->auto_screening_transition)->toBeTrue();
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `php artisan test --compact --filter=AutoScreeningTransitionFlagTest`
Expected: FAIL — column `auto_screening_transition` does not exist

- [ ] **Step 3: Criar a migration**

Criar `app-modules/recruitment/database/migrations/2026_05_17_000000_add_auto_screening_transition_to_recruitment_job_requisitions.php`:

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
        Schema::table('recruitment_job_requisitions', function (Blueprint $table): void {
            $table->boolean('auto_screening_transition')->default(false)->after('is_confidential');
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_job_requisitions', function (Blueprint $table): void {
            $table->dropColumn('auto_screening_transition');
        });
    }
};
```

- [ ] **Step 4: Adicionar cast + @property no model**

Em `JobRequisition.php`, no PHPDoc, após a linha `* @property bool $is_confidential` (linha 62), adicionar:

```php
 * @property bool $auto_screening_transition
```

No método `casts()` (linha ~197), após `'is_confidential' => 'boolean',`, adicionar:

```php
            'auto_screening_transition' => 'boolean',
```

- [ ] **Step 5: Adicionar o toggle no form (aba Settings)**

Em `JobRequisitionForm.php`, na `Section` de settings (linha ~292), após o `Toggle::make('is_confidential')...->default(false),` (linha 300), adicionar:

```php
                                        Toggle::make('auto_screening_transition')
                                            ->label(__('recruitment::filament.requisition.fields.auto_screening_transition'))
                                            ->helperText(__('recruitment::filament.requisition.fields.auto_screening_transition_help'))
                                            ->default(false),
```

- [ ] **Step 6: Adicionar i18n da flag**

Em `app-modules/recruitment/lang/en/filament.php`, dentro de `requisition.fields` (mesma seção de `is_internal_only`), adicionar:

```php
            'auto_screening_transition' => 'Automatic candidate screening',
            'auto_screening_transition_help' => 'Rejects candidates who fail the screening criteria and advances those who pass, based on their questionnaire answers.',
```

Em `app-modules/recruitment/lang/pt_BR/filament.php`, mesma seção:

```php
            'auto_screening_transition' => 'Triagem automática de candidatos',
            'auto_screening_transition_help' => 'Reprova candidatos que não atendem aos critérios da triagem e avança quem atende, com base nas respostas do questionário.',
```

(Se a chave exata `requisition.fields.is_internal_only` não existir nesse arquivo, localizar onde `is_internal_only`/`is_confidential` estão definidas com `grep -n "is_internal_only" app-modules/recruitment/lang/en/filament.php` e inserir as duas chaves no mesmo array.)

- [ ] **Step 7: Rodar e ver passar**

Run: `php artisan test --compact --filter=AutoScreeningTransitionFlagTest`
Expected: PASS

- [ ] **Step 8: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/recruitment/database/migrations app-modules/recruitment/src/Requisitions/Models/JobRequisition.php app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Schemas/JobRequisitionForm.php app-modules/recruitment/lang app-modules/recruitment/tests/Feature/AutoScreeningTransitionFlagTest.php
git commit -m "feat(recruitment): add auto_screening_transition flag to job requisitions"
```

---

### Task 7: Listener `HandleScreeningKnockoutTransition` + registro

**Contexto:** Escuta `ScreeningEvaluated`. Gated pela flag e por `status === New`. Reprova (`Rejected` + `ScreeningKnockout`) ou avança (`InReview`, que via `forwardToReview()` move o stage) — sempre com `byUserId = null`.

**Files:**

- Create: `app-modules/applications/src/Listeners/HandleScreeningKnockoutTransition.php` (auto-descoberto pelo internachi/modular — sem mudança no ServiceProvider)
- Test: `app-modules/applications/tests/Feature/Listeners/HandleScreeningKnockoutTransitionTest.php`

- [ ] **Step 1: Escrever o teste que falha**

Criar `app-modules/applications/tests/Feature/Listeners/HandleScreeningKnockoutTransitionTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Events\ScreeningEvaluated;

// JobRequisitionObserver::created() já cria 8 stages ordenados (display_order 1..8).
// Não fabricar stages manualmente — usar os do observer evita display_order colidente.
function newApplicationFor(JobRequisition $req): Application
{
    $first = $req->stages()->orderBy('display_order')->first();

    return Application::factory()->create([
        'requisition_id' => $req->id,
        'team_id' => $req->team_id,
        'status' => ApplicationStatusEnum::New,
        'current_stage_id' => $first->id,
    ]);
}

it('does nothing when the flag is off', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => false]);
    $application = newApplicationFor($req);

    event(new ScreeningEvaluated($application, anyKnockoutFailed: true, hadKnockoutCriteria: true));

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::New);
});

it('rejects the candidate when the flag is on and a knockout failed', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationFor($req);

    event(new ScreeningEvaluated($application, anyKnockoutFailed: true, hadKnockoutCriteria: true));

    $application->refresh();

    expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($application->rejection_reason_category)->toBe(RejectionReasonCategoryEnum::ScreeningKnockout)
        ->and($application->rejected_by)->toBeNull();
});

it('advances the candidate when the flag is on, has knockout questions and none failed', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationFor($req);
    $secondStageId = $req->stages()->orderBy('display_order')->skip(1)->first()->id;

    event(new ScreeningEvaluated($application, anyKnockoutFailed: false, hadKnockoutCriteria: true));

    $application->refresh();

    expect($application->status)->toBe(ApplicationStatusEnum::InReview)
        ->and($application->current_stage_id)->toBe($secondStageId);
});

it('does nothing when there were no knockout questions', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationFor($req);

    event(new ScreeningEvaluated($application, anyKnockoutFailed: false, hadKnockoutCriteria: false));

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::New);
});

it('ignores applications not in New status', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationFor($req);
    $application->update(['status' => ApplicationStatusEnum::InProgress]);

    event(new ScreeningEvaluated($application, anyKnockoutFailed: true, hadKnockoutCriteria: true));

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::InProgress);
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `php artisan test --compact --filter=HandleScreeningKnockoutTransitionTest`
Expected: FAIL — status continua `New` (sem listener registrado)

- [ ] **Step 3: Criar o listener**

Criar `app-modules/applications/src/Listeners/HandleScreeningKnockoutTransition.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Listeners;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
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
            $data = TransitionData::fromArray([
                'to_status' => ApplicationStatusEnum::Rejected,
                'rejection_reason_category' => RejectionReasonCategoryEnum::ScreeningKnockout->value,
                'rejection_reason_details' => __('screening::messages.knockout_auto_rejected'),
                'notes' => __('screening::messages.knockout_auto_rejected'),
            ], byUserId: null);

            $application->current_step->handle($data);

            return;
        }

        if ($event->hadKnockoutCriteria) {
            $data = TransitionData::fromArray([
                'to_status' => ApplicationStatusEnum::InReview,
                'notes' => __('screening::messages.knockout_auto_advanced'),
            ], byUserId: null);

            $application->current_step->handle($data);
        }
    }
}
```

- [ ] **Step 4: Adicionar as mensagens i18n**

Criar `app-modules/screening/lang/en/messages.php`:

```php
<?php

declare(strict_types=1);

return [
    'knockout_auto_rejected' => 'Automatically rejected: did not meet the screening criteria.',
    'knockout_auto_advanced' => 'Automatically advanced: met all screening criteria.',
];
```

Criar `app-modules/screening/lang/pt_BR/messages.php`:

```php
<?php

declare(strict_types=1);

return [
    'knockout_auto_rejected' => 'Reprovado automaticamente: não atendeu aos critérios da triagem.',
    'knockout_auto_advanced' => 'Avançado automaticamente: atendeu a todos os critérios da triagem.',
];
```

- [ ] **Step 5: (sem registro manual — discovery do internachi/modular)**

NÃO registrar o listener no `ApplicationsServiceProvider`. O `internachi/modular`
auto-descobre qualquer classe em `app-modules/<mod>/src/Listeners/` com um método
`handle(EventType $e)` e a registra no dispatcher (mesmo padrão dos listeners do
módulo `ai`, que não têm registro manual nem `EventServiceProvider`).

Verificado empiricamente: a suíte do listener passa sem nenhuma linha
`Event::listen`. Criar o arquivo em `src/Listeners/` **é** o wiring.

- [ ] **Step 6: Rodar e ver passar**

Run: `php artisan test --compact --filter=HandleScreeningKnockoutTransitionTest`
Expected: PASS

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/applications/src/Listeners/HandleScreeningKnockoutTransition.php app-modules/screening/lang app-modules/applications/tests/Feature/Listeners/HandleScreeningKnockoutTransitionTest.php
git commit -m "feat(applications): auto-transition on screening evaluation gated by requisition flag"
```

---

### Task 8: Plugar `EvaluateScreeningResponses` no `JobApplicationForm::submit()`

**Contexto:** Após persistir as respostas, a avaliação roda sempre (Decisão 7). Mantém a orquestração na mesma camada que já chama `StoreApplication`/`StoreScreeningResponse`.

**Files:**

- Modify: `app-modules/screening/src/Livewire/JobApplicationForm.php:96`
- Test: `app-modules/screening/tests/Feature/Livewire/JobApplicationFormKnockoutTest.php`

- [ ] **Step 1: Escrever o teste que falha (end-to-end via Livewire)**

Criar `app-modules/screening/tests/Feature/Livewire/JobApplicationFormKnockoutTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Livewire\JobApplicationForm;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->candidate = Candidate::factory()->create();
    actingAs($this->candidate->user->refresh());
    filament()->setCurrentPanel(FilamentPanel::App->value);

    // JobRequisitionObserver::created() já cria os 8 stages ordenados.
    $this->requisition = JobRequisition::factory()->create(['auto_screening_transition' => true]);

    $this->question = ScreeningQuestion::factory()
        ->for($this->requisition, 'screenable')
        ->state([
            'team_id' => $this->requisition->team_id,
            'question_type' => QuestionTypeEnum::YesNo,
            'settings' => [],
            'is_required' => true,
            'is_knockout' => true,
            'knockout_criteria' => ['expected' => 'yes'],
        ])
        ->create();
});

it('auto-rejects the candidate who fails the knockout on submit', function (): void {
    livewire(JobApplicationForm::class, ['requisition' => $this->requisition])
        ->set('source', CandidateSourceEnum::LinkedIn)
        ->set('responses.'.$this->question->getKey(), 'no')
        ->call('submit')
        ->assertHasNoErrors();

    $application = Application::query()->first();

    expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($application->rejection_reason_category)->toBe(RejectionReasonCategoryEnum::ScreeningKnockout);

    $response = ScreeningResponse::query()->where('application_id', $application->id)->first();
    expect($response->is_knockout_fail)->toBeTrue();
});

it('auto-advances the candidate who passes the knockout on submit', function (): void {
    $secondStageId = $this->requisition->stages()->orderBy('display_order')->skip(1)->first()->id;

    livewire(JobApplicationForm::class, ['requisition' => $this->requisition])
        ->set('source', CandidateSourceEnum::LinkedIn)
        ->set('responses.'.$this->question->getKey(), 'yes')
        ->call('submit')
        ->assertHasNoErrors();

    $application = Application::query()->first();

    expect($application->status)->toBe(ApplicationStatusEnum::InReview)
        ->and($application->current_stage_id)->toBe($secondStageId);
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `php artisan test --compact --filter=JobApplicationFormKnockoutTest`
Expected: FAIL — status continua `New` (avaliação não é chamada)

- [ ] **Step 3: Chamar a Action no submit**

Em `JobApplicationForm.php`, adicionar o import:

```php
use He4rt\Screening\Actions\ScreeningResponse\EvaluateScreeningResponses;
```

Em `submit()`, logo após a linha `resolve(StoreScreeningResponse::class)->execute($screeningCollection);` (linha 96), adicionar:

```php
        resolve(EvaluateScreeningResponses::class)->execute($this->application);
```

- [ ] **Step 4: Rodar e ver passar**

Run: `php artisan test --compact --filter=JobApplicationFormKnockoutTest`
Expected: PASS

- [ ] **Step 5: Regressão do fluxo de submissão existente**

Run: `php artisan test --compact app-modules/screening/tests/Feature/Livewire`
Expected: PASS — `JobApplicationFormTest` e `QuestionValidationsTest` continuam verdes (sem flag, `auto_screening_transition` default false → nenhum efeito).

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/screening/src/Livewire/JobApplicationForm.php app-modules/screening/tests/Feature/Livewire/JobApplicationFormKnockoutTest.php
git commit -m "feat(screening): evaluate screening responses on application submit"
```

---

### Task 9: Substituir o `KeyValue` do critério por schema tipado (Filament)

**Contexto:** Dois pontos renderizam `knockout_criteria` como `KeyValue` livre. Trocar por um `Group` dinâmico que injeta `knockoutCriteriaSchema()` do tipo selecionado, espelhando o padrão já usado para `settings` (Decisão 4).

**Files:**

- Modify: `app-modules/screening/src/Filament/Schemas/ScreeningQuestionsFormSchema.php:8,101-105`
- Modify: `app-modules/screening/src/Filament/RelationManagers/ScreeningQuestionsRelationManager.php:12,99-102`
- Test: `app-modules/screening/tests/Feature/Filament/ScreeningKnockoutSchemaTest.php`

- [ ] **Step 1: Escrever o smoke test que falha**

Criar `app-modules/screening/tests/Feature/Filament/ScreeningKnockoutSchemaTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Screening\Filament\Schemas\ScreeningQuestionsFormSchema;
use He4rt\Screening\QuestionTypes\YesNoType;

it('builds the questions repeater without referencing the legacy KeyValue field', function (): void {
    $repeater = ScreeningQuestionsFormSchema::make();

    expect($repeater)->toBeInstanceOf(\Filament\Forms\Components\Repeater::class);

    $componentClasses = collect($repeater->getChildComponents())
        ->map(fn ($component): string => $component::class);

    expect($componentClasses)->not->toContain(\Filament\Forms\Components\KeyValue::class);
});

it('YesNoType exposes a knockout criteria schema with the expected field', function (): void {
    $schema = YesNoType::knockoutCriteriaSchema();

    expect($schema)->toHaveCount(1)
        ->and($schema[0]->getName())->toBe('knockout_criteria.expected');
});
```

- [ ] **Step 2: Rodar e ver falhar**

Run: `php artisan test --compact --filter=ScreeningKnockoutSchemaTest`
Expected: FAIL — o repeater ainda contém `KeyValue`

- [ ] **Step 3: Trocar no `ScreeningQuestionsFormSchema`**

Em `ScreeningQuestionsFormSchema.php`, remover o import `use Filament\Forms\Components\KeyValue;` (linha 8).

Substituir o bloco `KeyValue::make('knockout_criteria')...->columnSpanFull(),` (linhas 101-105) por:

```php
                Group::make()
                    ->schema(function ($get): array {
                        if ($get('is_knockout') !== true) {
                            return [];
                        }

                        $typeValue = $get('question_type');

                        if ($typeValue === null) {
                            return [];
                        }

                        $type = $typeValue instanceof QuestionTypeEnum
                            ? $typeValue
                            : QuestionTypeEnum::tryFrom($typeValue);

                        if ($type === null) {
                            return [];
                        }

                        return QuestionTypeRegistry::get($type)::knockoutCriteriaSchema();
                    })
                    ->visible(fn ($get): bool => $get('is_knockout') === true)
                    ->columnSpanFull(),
```

- [ ] **Step 4: Trocar no `ScreeningQuestionsRelationManager`**

Em `ScreeningQuestionsRelationManager.php`, remover o import `use Filament\Forms\Components\KeyValue;` (linha 12).

Substituir o bloco `KeyValue::make('knockout_criteria')...->columnSpanFull(),` (linhas 99-102) por:

```php
                Group::make()
                    ->schema(function ($get): array {
                        if ($get('is_knockout') !== true) {
                            return [];
                        }

                        $typeValue = $get('question_type');

                        if ($typeValue === null) {
                            return [];
                        }

                        $type = $typeValue instanceof QuestionTypeEnum
                            ? $typeValue
                            : QuestionTypeEnum::tryFrom($typeValue);

                        if ($type === null) {
                            return [];
                        }

                        return QuestionTypeRegistry::get($type)::knockoutCriteriaSchema();
                    })
                    ->visible(fn ($get): bool => $get('is_knockout') === true)
                    ->columnSpanFull(),
```

(O import `use He4rt\Screening\QuestionTypes\QuestionTypeRegistry;` já existe na linha 25 do RelationManager; `Group` já está importado na linha 18.)

- [ ] **Step 5: Rodar e ver passar**

Run: `php artisan test --compact --filter=ScreeningKnockoutSchemaTest`
Expected: PASS

- [ ] **Step 6: Smoke test dos Resources que usam o RelationManager/Schema**

Run: `php artisan test --compact app-modules/screening/tests/Feature/Filament`
Expected: PASS — nenhuma referência quebrada a `KeyValue`.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/screening/src/Filament app-modules/screening/tests/Feature/Filament/ScreeningKnockoutSchemaTest.php
git commit -m "feat(screening): replace free-form knockout KeyValue with typed criteria schema"
```

---

### Task 10: Verificação final integrada

**Files:** nenhuma alteração — apenas verificação.

- [ ] **Step 1: Rodar a suíte completa dos módulos afetados**

Run: `php artisan test --compact app-modules/screening app-modules/applications app-modules/recruitment`
Expected: PASS — todos verdes, incluindo regressões.

- [ ] **Step 2: Larastan nos módulos tocados**

Run: `vendor/bin/phpstan analyse app-modules/screening app-modules/applications --no-progress`
Expected: sem novos erros introduzidos pelas mudanças (linha-base mantida).

- [ ] **Step 3: Pint final**

Run: `vendor/bin/pint --dirty --format agent`
Expected: nenhuma alteração pendente.

- [ ] **Step 4: Commit de fechamento (se houver ajustes)**

```bash
git add -A
git commit -m "test: integrated verification for automatic screening transition"
```

---

## Self-Review

**Spec coverage:**

- Decisão 1 (reprova/avança) → Task 7. Decisão 2 (só requisição) → Task 8 (chamada no submit, escopo `requisition->screeningQuestions`). Decisão 3 (evento/listener) → Tasks 4/5/7. Decisão 4 (form tipado) → Tasks 3/9. Decisão 5 (byUserId nullable) → Task 2. Decisão 6 (MultipleChoice ≥1) → Task 3 Step 7. Decisão 7 (desacoplado) → Task 5 (marca `is_knockout_fail` sem flag) + Task 7 (flag só na transição). Decisão 8 (`ScreeningKnockout`) → Task 1. Decisão 9 (toggle Settings) → Task 6. Decisão 10 (evento dispara) → Task 2 Step 5. Decisão 11 (point-in-time/defensivo) → Task 3 (evaluate defensivo) + Task 5 (resposta ausente = passa). Issue #158 referenciada no TODO da Task 2.

**Placeholder scan:** Sem TBD/TODO de implementação; todo passo com código tem o código completo. O único `TODO` textual é um comentário de domínio intencional citando a issue #158.

**Type consistency:** `evaluateKnockout(array $criteria, mixed $answer): bool` e `knockoutCriteriaSchema(): array` idênticos em contrato (Task 3 Step 3) e implementações (Steps 4-8). `ScreeningEvaluated(Application, bool $anyKnockoutFailed, bool $hadKnockoutCriteria)` consistente entre Task 4 (criação), Task 5 (dispatch), Task 7 (consumo). `TransitionData::fromArray(array, ?string $byUserId = null)` consistente entre Task 2 e os usos nas Tasks 7. `auto_screening_transition` (boolean) consistente entre Task 6 (migração/cast/form) e Task 7 (guard do listener).

Observação de risco residual: a chave i18n exata de `is_internal_only` no `recruitment/lang` deve ser confirmada no Step 6 da Task 6 (instrução de `grep` incluída) — os arquivos não foram inspecionados nesse ponto exato.
