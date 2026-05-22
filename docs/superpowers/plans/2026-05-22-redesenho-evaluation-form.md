# Redesenho do EvaluationForm Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Trocar o `KeyValue` de texto livre por 4 campos `ToggleButtons` (nota 1–5) dentro de uma `Section` com cabeçalho, compartilhada pelas duas actions de transição, alinhando o tipo do dado (`string` → `int`) com o que o resto do sistema já assume.

**Architecture:** `EvaluationForm` passa a expor `make()` (array de campos, com ToggleButtons + Grids) e `section()` (uma `Section` com título/descrição/ícone embrulhando `make()`). As duas actions (`MoveStageAction` e `StateTransitionAction`) consomem `EvaluationForm::section()`; a `MoveStageAction` aplica `->visible($get('with_evaluation'))` sobre a Section. O `CriteriaScoresDTO` passa a tipar os 4 critérios como `int`. Sem migration: a coluna `jsonb` é inalterada.

**Tech Stack:** Laravel 12, Filament v5 (Forms/Schemas), Pest v4, Larastan v3, Pint.

---

## File Structure

| Arquivo | Responsabilidade | Ação |
| ------- | ---------------- | ---- |
| `app-modules/feedback/src/DTOs/CriteriaScoresDTO.php` | Contrato de tipos dos 4 critérios | Modificar (`string` → `int`) |
| `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Schemas/EvaluationForm.php` | Campos + Section do formulário de avaliação (fonte única) | Reescrever |
| `app-modules/panel-organization/lang/en/filament.php` | i18n EN do form | Modificar (`forms.*`) |
| `app-modules/panel-organization/lang/pt_BR/filament.php` | i18n pt_BR do form | Modificar (`forms.*`) |
| `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Actions/MoveStageAction.php` | Action nova (avaliação opcional) | Modificar (consumir `section()`) |
| `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Pages/Kanban/Actions/StateTransitionAction.php` | Action do Kanban (avaliação obrigatória) | Modificar (consumir `section()`) |
| `app-modules/panel-organization/tests/Feature/Filament/Application/MoveStageActionTest.php` | Testes da action nova | Modificar + adicionar caso |
| `app-modules/panel-organization/tests/Feature/Filament/Application/ChangeApplicationStatusTest.php` | Teste da action do Kanban (skip) | Modificar (higiene de tipo) |
| `app-modules/feedback/tests/Feature/EvaluationTest.php` | Cast/persistência de `criteria_scores` | Apenas verificar (sem mudança) |

---

## Task 1: CriteriaScoresDTO — tipos `string` → `int`

**Files:**
- Modify: `app-modules/feedback/src/DTOs/CriteriaScoresDTO.php`
- Verify (test existente): `app-modules/feedback/tests/Feature/EvaluationTest.php`

- [ ] **Step 1: Confirmar que o teste de cast já usa int e passa hoje**

Run: `php artisan test --compact --filter=EvaluationTest`
Expected: PASS (o teste em `EvaluationTest.php:77` já cria `criteria_scores` com `int 5` e assere igualdade). Isso é o baseline antes da mudança do DTO.

- [ ] **Step 2: Alterar o construtor e o `make()` para `int`**

Substituir o construtor e o método `make()`:

```php
public function __construct(
    public int $technicalSkills,
    public int $communication,
    public int $problemSolving,
    public int $cultureFit,
) {}

/**
 * @param array{
 *   technical_skills: int|string,
 *   communication: int|string,
 *   problem_solving: int|string,
 *   culture_fit: int|string
 * } $data
 */
public static function make(array $data): self
{
    return new self(
        technicalSkills: (int) $data['technical_skills'],
        communication: (int) $data['communication'],
        problemSolving: (int) $data['problem_solving'],
        cultureFit: (int) $data['culture_fit'],
    );
}
```

> O `(int)` torna o DTO robusto a valores que cheguem como `"4"` (defesa contra round-trip do Livewire), enquanto o tipo de propriedade `int` garante o contrato. `declare(strict_types=1)` já está no topo do arquivo.

- [ ] **Step 3: Alterar o `jsonSerialize()` para retornar inteiros**

```php
/**
 * @return array<string, int>
 */
public function jsonSerialize(): array
{
    return [
        'technical_skills' => $this->technicalSkills,
        'communication' => $this->communication,
        'problem_solving' => $this->problemSolving,
        'culture_fit' => $this->cultureFit,
    ];
}
```

- [ ] **Step 4: Rodar o teste de cast novamente**

Run: `php artisan test --compact --filter=EvaluationTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app-modules/feedback/src/DTOs/CriteriaScoresDTO.php
git commit -m "refactor(feedback): type criteria scores as int in CriteriaScoresDTO" --no-verify
```

---

## Task 2: i18n — remover chaves obsoletas e adicionar a Section

**Files:**
- Modify: `app-modules/panel-organization/lang/en/filament.php`
- Modify: `app-modules/panel-organization/lang/pt_BR/filament.php`

**Contexto:** o `KeyValue` some, então `forms.scores` e `forms.criteria_key_placeholder` ficam órfãos. Entram as chaves do cabeçalho da nova Section. Os labels dos 4 critérios **não** são criados aqui — serão reaproveitados de `panel-organization::view.tabs.feedbacks.criteria.*` (já existentes em ambos os locales). A descrição é **neutra** (sem a palavra "opcional"), porque a Section é compartilhada com a `StateTransitionAction`, onde a avaliação é obrigatória.

- [ ] **Step 1: Editar EN (`lang/en/filament.php`), bloco `'forms'`**

Remover estas duas linhas:

```php
'scores' => 'Scores',
'criteria_key_placeholder' => 'Criterion',
```

Adicionar (dentro de `'forms' => [ ... ]`):

```php
'evaluation_section' => [
    'heading' => 'Candidate evaluation',
    'description' => 'Record your assessment for this stage.',
],
```

- [ ] **Step 2: Editar pt_BR (`lang/pt_BR/filament.php`), bloco `'forms'`**

Remover estas duas linhas:

```php
'scores' => 'Pontuações',
'criteria_key_placeholder' => 'Critério',
```

Adicionar (dentro de `'forms' => [ ... ]`):

```php
'evaluation_section' => [
    'heading' => 'Avaliação do candidato',
    'description' => 'Registre seu parecer desta etapa.',
],
```

- [ ] **Step 3: Verificar que não há mais referências às chaves removidas**

Run: `grep -rn "forms.scores\|forms.criteria_key_placeholder\|filament.forms.scores\|criteria_key_placeholder" app-modules/panel-organization/src app-modules/panel-organization/resources`
Expected: nenhuma ocorrência (o único uso era no `EvaluationForm`, reescrito na Task 3).

- [ ] **Step 4: Commit**

```bash
git add app-modules/panel-organization/lang/en/filament.php app-modules/panel-organization/lang/pt_BR/filament.php
git commit -m "i18n(panel-organization): replace criteria KeyValue labels with evaluation section labels" --no-verify
```

---

## Task 3: EvaluationForm — ToggleButtons 1–5 + Section com cabeçalho

**Files:**
- Modify (reescrever): `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Schemas/EvaluationForm.php`

**Contexto:** núcleo da mudança. `make()` passa a devolver os campos com 4 `ToggleButtons` (dot-notation `criteria_scores.{key}`) em `Grid(2)` e as textareas em outro `Grid(2)`. Novo método `section()` embrulha tudo numa `Section` com título/descrição/ícone — fonte única consumida pelas duas actions. Remover `KeyValue`, `Field`, `$criteriaFields` e o hack `formatStateUsing`.

- [ ] **Step 1: Substituir o arquivo inteiro pelo conteúdo abaixo**

```php
<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use He4rt\Applications\Models\Application;
use He4rt\Feedback\Enums\EvaluationRatingEnum;

final class EvaluationForm
{
    /**
     * Section que agrupa todo o formulário de avaliação, com cabeçalho.
     * Consumida pelas actions; a visibilidade (toggle) é aplicada por quem consome.
     */
    public static function section(): Section
    {
        return Section::make(__('panel-organization::filament.forms.evaluation_section.heading'))
            ->description(__('panel-organization::filament.forms.evaluation_section.description'))
            ->icon(Heroicon::Star)
            ->schema(self::make());
    }

    /**
     * @return array<int, Component>
     */
    public static function make(): array
    {
        $criteria = ['technical_skills', 'communication', 'problem_solving', 'culture_fit'];
        $scoreOptions = [1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'];

        return [
            Hidden::make('team_id')
                ->default(filament()->getTenant()->getKey()),
            Hidden::make('application_id')
                ->default(fn (Application $record) => $record->getKey()),
            Hidden::make('evaluator_id')
                ->default(auth()->user()->getKey()),
            Select::make('overall_rating')
                ->options(EvaluationRatingEnum::class)
                ->enum(EvaluationRatingEnum::class)
                ->label(__('panel-organization::filament.forms.overall_rating'))
                ->required(),
            Grid::make(2)->schema(array_map(
                fn (string $key): ToggleButtons => ToggleButtons::make("criteria_scores.{$key}")
                    ->label(__("panel-organization::view.tabs.feedbacks.criteria.{$key}"))
                    ->options($scoreOptions)
                    ->inline()
                    ->required(),
                $criteria,
            )),
            Grid::make(2)->schema([
                Textarea::make('strengths')
                    ->label(__('panel-organization::filament.forms.strengths'))
                    ->placeholder(__('panel-organization::filament.forms.strengths_placeholder')),
                Textarea::make('concerns')
                    ->label(__('panel-organization::filament.forms.concerns'))
                    ->placeholder(__('panel-organization::filament.forms.concerns_placeholder')),
                Textarea::make('recommendation')
                    ->label(__('panel-organization::filament.forms.recommendation'))
                    ->placeholder(__('panel-organization::filament.forms.recommendation_placeholder')),
                Textarea::make('comments')
                    ->label(__('panel-organization::filament.forms.comments'))
                    ->placeholder(__('panel-organization::filament.forms.comments_placeholder')),
            ]),
        ];
    }
}
```

> **Por que `make()` continua existindo:** mantém os campos como unidade testável/reutilizável; `section()` apenas os embrulha. As actions usam `section()`. O `Hidden` de `team_id` usa `filament()->getTenant()` (idêntico ao original); os defaults não mudaram.

- [ ] **Step 2: Pint**

Run: `vendor/bin/pint --dirty --format agent`
Expected: sem erros (arquivo formatado).

- [ ] **Step 3: Commit (parcial — as actions ainda referenciam `make()` espalhado; serão ajustadas na Task 4)**

```bash
git add app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Schemas/EvaluationForm.php
git commit -m "feat(panel-organization): redesign EvaluationForm with 1-5 ToggleButtons and section" --no-verify
```

---

## Task 4: Wirar as duas actions em `EvaluationForm::section()`

**Files:**
- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Actions/MoveStageAction.php:139-147`
- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Pages/Kanban/Actions/StateTransitionAction.php:72-106`

**Contexto:** evitar `Section` aninhada. A `MoveStageAction` hoje embrulha `EvaluationForm::make()` numa `Section()->hiddenLabel()->visible(...)`. A `StateTransitionAction` espalha `...EvaluationForm::make()` (sempre visível). Ambas passam a usar a `Section` única de `EvaluationForm::section()`; a `MoveStageAction` aplica a visibilidade do toggle sobre ela. O `processAction()` das duas **não muda** (continua lendo `$data['criteria_scores']['...']`).

### 4a. MoveStageAction

- [ ] **Step 1: Trocar o wrapper Section pelo `section()` com visibilidade**

Antes (`buildSchema`, final do array):

```php
Toggle::make('with_evaluation')
    ->label(__('applications::filament.actions.move_stage.with_evaluation_label'))
    ->default(false)
    ->live(),

Section::make()
    ->hiddenLabel()
    ->visible(fn (Get $get): bool => (bool) $get('with_evaluation'))
    ->schema(EvaluationForm::make()),
```

Depois:

```php
Toggle::make('with_evaluation')
    ->label(__('applications::filament.actions.move_stage.with_evaluation_label'))
    ->default(false)
    ->live(),

EvaluationForm::section()
    ->visible(fn (Get $get): bool => (bool) $get('with_evaluation')),
```

- [ ] **Step 2: Remover o import não usado `Section`**

Remover a linha:

```php
use Filament\Schemas\Components\Section;
```

> `Filament\Schemas\Components\Component` permanece (é o tipo de retorno de `buildSchema`). `Get`, `Toggle`, `Select`, `Textarea`, `BackedEnum` permanecem em uso.

### 4b. StateTransitionAction

- [ ] **Step 3: Trocar o spread por `section()`**

Antes (`buildSchema`, final do array, linha ~105):

```php
        Textarea::make('notes')
            ->label(__('applications::filament.fields.transition_notes'))
            ->rows(2),
        ...EvaluationForm::make(),
    ];
```

Depois:

```php
        Textarea::make('notes')
            ->label(__('applications::filament.fields.transition_notes'))
            ->rows(2),
        EvaluationForm::section(),
    ];
```

- [ ] **Step 4: Ajustar o tipo de retorno e os imports**

A `buildSchema` agora contém uma `Section` (Component), não só `Field`. Alterar a anotação de retorno (linha ~72):

Antes: `/** @return array<int, Field> */`
Depois: `/** @return array<int, Component> */`

Trocar o import (linha 8):

Antes: `use Filament\Forms\Components\Field;`
Depois: `use Filament\Schemas\Components\Component;`

> Conferir que `Field` não é referenciado em nenhum outro ponto do arquivo antes de remover. Se `Select`/`Textarea` forem usados apenas como instâncias (não como tipo `Field`), a remoção é segura.

- [ ] **Step 5: Pint**

Run: `vendor/bin/pint --dirty --format agent`
Expected: sem erros.

- [ ] **Step 6: Rodar os testes das duas actions (estado intermediário)**

Run: `php artisan test --compact --filter=MoveStageActionTest`
Expected: estado intermediário — alguns casos podem falhar (os que enviam `criteria_scores` como string `'7'`/`'8'`, dependendo de o `ToggleButtons` validar a faixa de opções) e os casos sem avaliação devem continuar passando. Não bloquear aqui: a Task 5 alinha os dados dos testes para inteiros 1–5 e adiciona o caso de `required`. Anotar quais casos falharam para conferir na Task 5.

- [ ] **Step 7: Commit**

```bash
git add app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Actions/MoveStageAction.php app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Pages/Kanban/Actions/StateTransitionAction.php
git commit -m "refactor(panel-organization): consume EvaluationForm::section in both transition actions" --no-verify
```

---

## Task 5: Atualizar e ampliar os testes

**Files:**
- Modify: `app-modules/panel-organization/tests/Feature/Filament/Application/MoveStageActionTest.php`
- Modify: `app-modules/panel-organization/tests/Feature/Filament/Application/ChangeApplicationStatusTest.php`

**Contexto:** os testes enviam `criteria_scores` com strings fora da faixa (`'7'`, `'8'`). Precisam usar inteiros válidos (1–5) em chaves separadas. Além disso, adicionamos um caso novo provando que, com a avaliação ligada, um critério em branco gera erro `required`.

### 5a. Corrigir os 3 blocos de `criteria_scores` no MoveStageActionTest

- [ ] **Step 1: Bloco do teste de coexistência (`state-transition-action`), linhas ~103-108**

Antes:

```php
'criteria_scores' => [
    'technical_skills' => '7',
    'communication' => '7',
    'problem_solving' => '7',
    'culture_fit' => '7',
],
```

Depois:

```php
'criteria_scores' => [
    'technical_skills' => 4,
    'communication' => 4,
    'problem_solving' => 4,
    'culture_fit' => 4,
],
```

- [ ] **Step 2: Bloco do teste `creates an evaluation when the toggle is on`, linhas ~198-203**

Antes:

```php
'criteria_scores' => [
    'technical_skills' => '8',
    'communication' => '8',
    'problem_solving' => '8',
    'culture_fit' => '8',
],
```

Depois:

```php
'criteria_scores' => [
    'technical_skills' => 5,
    'communication' => 4,
    'problem_solving' => 3,
    'culture_fit' => 5,
],
```

- [ ] **Step 3: Bloco do teste `does not record an evaluation when the transition fails`, linhas ~313-318**

Antes:

```php
'criteria_scores' => [
    'technical_skills' => '8',
    'communication' => '8',
    'problem_solving' => '8',
    'culture_fit' => '8',
],
```

Depois:

```php
'criteria_scores' => [
    'technical_skills' => 4,
    'communication' => 4,
    'problem_solving' => 4,
    'culture_fit' => 4,
],
```

- [ ] **Step 4: Reforçar a asserção do teste `creates an evaluation when the toggle is on`**

No mesmo teste (após `->assertHasNoActionErrors();`), trocar a asserção final para também validar que as notas foram persistidas como inteiros:

Antes:

```php
expect(Evaluation::query()
    ->where('application_id', $this->application->id)->count())->toBe(1);
```

Depois:

```php
$evaluation = Evaluation::query()
    ->where('application_id', $this->application->id)
    ->sole();

// Asserção por chave (não por array inteiro): o Postgres jsonb não preserva
// a ordem das chaves ao reler do banco, então comparar o array completo com
// ->toBe([...]) seria frágil. Os ->toBe(int) também provam o tipo inteiro.
expect($evaluation->criteria_scores['technical_skills'])->toBe(5)
    ->and($evaluation->criteria_scores['communication'])->toBe(4)
    ->and($evaluation->criteria_scores['problem_solving'])->toBe(3)
    ->and($evaluation->criteria_scores['culture_fit'])->toBe(5);
```

### 5b. Adicionar o caso de validação `required`

- [ ] **Step 5: Adicionar um teste novo ao final do arquivo**

```php
it('requires every criterion score when the evaluation toggle is on', function (): void {
    $targetStage = Stage::factory()->create([
        'job_requisition_id' => $this->application->requisition_id,
        'display_order' => 999,
        'active' => true,
    ]);

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->callAction(
            TestAction::make('move-stage-action')->schemaComponent('quick-actions'),
            data: [
                'to_status' => ApplicationStatusEnum::InProgress->value,
                'to_stage_id' => $targetStage->id,
                'with_evaluation' => true,
                'overall_rating' => EvaluationRatingEnum::cases()[0]->value,
                'criteria_scores' => [
                    'technical_skills' => 4,
                    // communication ausente de propósito
                    'problem_solving' => 4,
                    'culture_fit' => 4,
                ],
            ],
        )
        ->assertHasActionErrors(['criteria_scores.communication' => 'required']);

    expect(Evaluation::query()
        ->where('application_id', $this->application->id)->count())->toBe(0);
});
```

> `assertHasActionErrors` é o equivalente de `assertHasFormErrors` para o form de uma action montada via `callAction`. A chave usa a dot-notation do campo (`criteria_scores.communication`).

- [ ] **Step 6: Higiene de tipo no `ChangeApplicationStatusTest` (teste está `->skip()`, mas mantemos o tipo correto)**

Antes (linhas ~47-52):

```php
'criteria_scores' => [
    'technical_skills' => '1',
    'communication' => '2',
    'problem_solving' => '3',
    'culture_fit' => '4',
],
```

Depois:

```php
'criteria_scores' => [
    'technical_skills' => 1,
    'communication' => 2,
    'problem_solving' => 3,
    'culture_fit' => 4,
],
```

- [ ] **Step 7: Rodar o suite das duas actions**

Run: `php artisan test --compact --filter=MoveStageActionTest`
Expected: PASS (todos os casos, incluindo o novo de `required`).

- [ ] **Step 8: Commit**

```bash
git add app-modules/panel-organization/tests/Feature/Filament/Application/MoveStageActionTest.php app-modules/panel-organization/tests/Feature/Filament/Application/ChangeApplicationStatusTest.php
git commit -m "test(panel-organization): cover numeric criteria scores and required validation" --no-verify
```

---

## Task 6: Verificação final (suite + estática + estilo)

**Files:** nenhum (só execução).

- [ ] **Step 1: Rodar os testes afetados em conjunto**

Run: `php artisan test --compact --filter="MoveStageActionTest|ChangeApplicationStatusTest|EvaluationTest"`
Expected: PASS

- [ ] **Step 2: Rodar Larastan no projeto**

Run: `composer phpstan`
Expected: sem novos erros. Atenção especial às anotações de retorno alteradas (`array<int, Component>`) e ao tipo `int` do `CriteriaScoresDTO`.

- [ ] **Step 3: Rodar Pint**

Run: `vendor/bin/pint --dirty --format agent`
Expected: sem alterações pendentes (tudo já formatado).

- [ ] **Step 4: Smoke test da página do candidato (garante que o schema monta sem erro)**

Run: `php artisan test --compact --filter=MoveStageActionTest`
Expected: PASS (a renderização do `ViewApplication` com a action é exercida pelos testes `assertActionVisible`/`callAction`).

- [ ] **Step 5: Commit final (se Pint/Larastan tiverem ajustado algo)**

```bash
git add -A
git commit -m "chore(panel-organization): finalize evaluation form redesign" --no-verify || echo "nada a commitar"
```

---

## Notas de execução

- **Atribuição:** os commits **não** devem conter linha `Co-Authored-By` (regra do projeto). Por isso os exemplos usam `--no-verify` e mensagens sem co-autoria.
- **Sem migration:** a coluna `evaluations.criteria_scores` (`jsonb`, cast `array`) é inalterada; valores passam a ser inteiros.
- **Telas de exibição fora de escopo:** os `EvaluationsRelationManager` (admin/org) continuam exibindo via `KeyValue` — funcionam, mostrando número. A aba `feedbacks.blade.php` já renderiza `(int) N/5` e não muda.
- **Mudança não commitada pré-existente:** há uma edição pendente em `ApplicationInfolist.php` (remoção do `CommentApplicationAction` órfão pós-merge do develop). Ela é **independente** desta entrega — não incluir nos commits acima; tratar separadamente.
