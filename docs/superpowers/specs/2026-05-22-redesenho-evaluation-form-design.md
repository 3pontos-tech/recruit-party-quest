# Design — Redesenho do EvaluationForm (notas 1–5 + container)

- **Data:** 2026-05-22
- **Branch:** `feat/move-stage-action`
- **Módulos afetados:** `panel-organization` (form + lang), `feedback` (DTO + lang)
- **Origem:** dores de UX no modal de avaliação da `MoveStageAction` — `EvaluationForm` "solto" dentro de uma `Section` sem título e `KeyValue` de texto livre sem clareza sobre o que preencher.

## 1. Problema

O `EvaluationForm::make()` (compartilhado pela `MoveStageAction` e pela `StateTransitionAction` do Kanban) renderiza os critérios com um `KeyValue` de **texto livre**:

```php
KeyValue::make('criteria_scores')
    ->default($criteriaFields)                                   // 4 chaves com '' (string)
    ->formatStateUsing(fn ($state) => blank($state) ? $criteriaFields : $state)
    ->keyPlaceholder(__('panel-organization::filament.forms.criteria_key_placeholder'))
    ->addable(false)->deletable(false)->editableKeys(false)
    ->label(__('panel-organization::filament.forms.scores')),
```

Dois problemas:

1. **Ambiguidade de entrada.** O avaliador não sabe se deve digitar texto ou número; nada valida o conteúdo. Pode escrever `"bom"`, `"8"`, vazio.
2. **Container pobre.** Quando o toggle "Registrar avaliação" liga, o form inteiro aparece dentro de uma `Section` com `hiddenLabel()` — sem identidade visual, competindo com os campos principais do modal.

**Inconsistência-raiz:** o restante do sistema **já trata os critérios como nota numérica 1–5**:

- `feedbacks.blade.php` faz `(int) $evaluation->criteria_scores[$key]`, renderiza `N/5`, barra de progresso e cor por nota (1🔴 2🟡 3🔵 4🟢 5🟣).
- `EvaluationFactory` gera `fake()->numberBetween(1, 5)` para cada critério.

Ou seja: a coluna, os seeds e a exibição já são numéricos. **Só o formulário de entrada estava fora da curva.**

## 2. Objetivo / escopo

Realinhar a entrada com o resto do sistema e melhorar o container.

### Em escopo

- Substituir o `KeyValue` por **4 campos `ToggleButtons` (1–5)**, um por critério, com label amigável, **obrigatórios** quando a avaliação está ligada.
- Envolver o `EvaluationForm` em uma **`Section` com cabeçalho** (título + descrição + ícone), em vez da `Section` sem título atual.
- Ajustar `CriteriaScoresDTO` de `string` → `int`.
- Limpar i18n obsoleto e reaproveitar labels de critério já existentes.
- Atualizar testes.

### Fora de escopo (YAGNI)

- **Migration / mudança de schema** — a coluna `jsonb` aceita `{"technical_skills": 4}` sem alteração.
- **Telas de exibição** (`EvaluationsRelationManager` de `panel-admin` e `panel-organization`) — continuam funcionando, exibindo a chave técnica + número. Refinamento de labels fica de fora.
- **Lógica de transição** da `StateTransitionAction` — inalterada (só o form compartilhado muda de UI).
- A aba `feedbacks.blade.php` — já correta, não toca.

## 3. Decisões (registro)

| # | Decisão | Escolha |
| - | ------- | ------- |
| 1 | Tipo do valor do critério | **Número 1–5** (realinha com factory/blade) |
| 2 | Widget | **ToggleButtons** inline (5 botões sempre visíveis) |
| 3 | Container do bloco | **Section com cabeçalho** (título + descrição + ícone) |
| 4 | Alcance da nova UI | **Form compartilhado** → `MoveStageAction` **e** `StateTransitionAction` |
| 5 | Critérios obrigatórios? | **Sim**, quando o toggle de avaliação está ligado |
| 6 | Telas de exibição | **Fora de escopo** |

## 4. Arquitetura

```
  EvaluationForm::make()  (panel-organization, compartilhado)
        │ usado por  (...EvaluationForm::make() espalhado no schema)
        ├──► StateTransitionAction  (Kanban)          ◄ ganha nova UI
        └──► MoveStageAction        (página + Kanban)  ◄ ganha nova UI
        │
        ▼ processAction() de AMBAS lê $data['criteria_scores']['technical_skills'] ...
        ▼  → INALTERADO (mesma forma de array; agora int)
   CriteriaScoresDTO::make([...])   (string → int)
        ▼
   StoreEvaluationAction → Evaluation.criteria_scores  (jsonb, cast 'array')  ← coluna inalterada
        ▼
   feedbacks.blade.php  → (int) já existente: barra + N/5 + cor   ← inalterado
```

## 5. Wireframe — modal com avaliação ligada

```
  ┌─────────────────────────────────────────────────────┐
  │  Mover etapa — {candidato}                           │
  ├─────────────────────────────────────────────────────┤
  │  Status destino    [ Em andamento            ▾ ]     │
  │  Etapa destino     [ Entrevista Técnica      ▾ ]     │
  │  Observações       [_____________________________]   │
  │  [✔] Registrar avaliação                             │
  │  ┌─ ⭐ Avaliação do candidato ──────────────────────┐│
  │  │ Opcional — registre seu parecer desta etapa      ││
  │  │ Nota geral        [ Yes                      ▾ ] ││
  │  │ ┌── Grid 2 col ───────────────────────────────┐ ││
  │  │ │ Habilidades técnicas*  [1][2][3][▣4][5]     │ ││
  │  │ │ Comunicação*           [1][2][▣3][4][5]     │ ││
  │  │ │ Resolução de problemas*[1][2][3][4][▣5]     │ ││
  │  │ │ Fit cultural*          [1][2][3][▣4][5]     │ ││
  │  │ └─────────────────────────────────────────────┘ ││
  │  │ ┌── Grid 2 col ───────────────────────────────┐ ││
  │  │ │ Pontos fortes      │ Preocupações           │ ││
  │  │ │ Recomendação       │ Comentários            │ ││
  │  │ └─────────────────────────────────────────────┘ ││
  │  └──────────────────────────────────────────────────┘│
  ├─────────────────────────────────────────────────────┤
  │                       [ Cancelar ]  [ Confirmar ]    │
  └─────────────────────────────────────────────────────┘
```

## 6. Mudanças por arquivo

### 6.1 `EvaluationForm.php`

**Contexto:** núcleo da mudança. Trocar o `KeyValue` por 4 `ToggleButtons` com dot-notation (`criteria_scores.technical_skills`), agrupados em `Grid(2)`, e envolver tudo numa `Section` com cabeçalho. As textareas vão para um `Grid(2)`. Remover `$criteriaFields` e o hack `formatStateUsing`.

**Antes:**

```php
KeyValue::make('criteria_scores')
    ->default($criteriaFields)
    ->formatStateUsing(fn ($state) => blank($state) ? $criteriaFields : $state)
    ->keyPlaceholder(__('panel-organization::filament.forms.criteria_key_placeholder'))
    ->columnSpanFull()->addable(false)->deletable(false)->editableKeys(false)
    ->label(__('panel-organization::filament.forms.scores')),
```

**Depois (esboço):**

```php
$criteria = ['technical_skills', 'communication', 'problem_solving', 'culture_fit'];
$scoreOptions = array_combine(range(1, 5), range(1, 5)); // [1=>1, ..., 5=>5]

Section::make(__('panel-organization::filament.forms.evaluation_section.heading'))
    ->description(__('panel-organization::filament.forms.evaluation_section.description'))
    ->icon(Heroicon::Star)
    ->schema([
        Select::make('overall_rating')
            ->options(EvaluationRatingEnum::class)
            ->enum(EvaluationRatingEnum::class)
            ->label(__('panel-organization::filament.forms.overall_rating'))
            ->required(),
        Grid::make(2)->schema(
            array_map(fn (string $key) => ToggleButtons::make("criteria_scores.{$key}")
                ->label(__("panel-organization::view.tabs.feedbacks.criteria.{$key}"))
                ->options($scoreOptions)
                ->inline()
                ->required(), $criteria)
        ),
        Grid::make(2)->schema([
            Textarea::make('strengths')->label(...)->placeholder(...),
            Textarea::make('concerns')->label(...)->placeholder(...),
            Textarea::make('recommendation')->label(...)->placeholder(...),
            Textarea::make('comments')->label(...)->placeholder(...),
        ]),
    ]),
// Hidden team_id / application_id / evaluator_id permanecem.
```

> Nota: como `EvaluationForm::make()` retorna `array<int, Field>` e hoje é espalhado (`...EvaluationForm::make()`), a `Section` raiz vira o item agrupador. As actions continuam fazendo `->schema([... ->schema(EvaluationForm::make())])` / `...EvaluationForm::make()` sem mudança de contrato.

**Comportamento esperado (BDD):**

- **Happy path** — Dado o toggle "Registrar avaliação" ligado, Então o bloco aparece dentro da Section "Avaliação do candidato" com 4 ToggleButtons 1–5 e os textos em 2 colunas.
- **Borda (required)** — Dado o toggle ligado e um critério sem nota, Quando confirmo, Então recebo erro de validação `required` naquele critério.
- **Compatibilidade** — Dado o toggle desligado, Então nenhum campo de avaliação é exigido nem enviado (comportamento atual preservado).

### 6.2 `CriteriaScoresDTO.php`

**Contexto:** alinhar o tipo ao dado real (numérico). Os 4 campos passam de `string` para `int`.

**Antes:** `public string $technicalSkills, ...` · phpdoc `technical_skills: string` · `jsonSerialize(): array<string, string>`
**Depois:** `public int $technicalSkills, ...` · phpdoc `technical_skills: int` · `jsonSerialize(): array<string, int>`

**BDD:**
- **Happy path** — Dado `make(['technical_skills' => 4, ...])`, Então o DTO expõe `int 4` e `jsonSerialize()` retorna inteiros.
- **Compatibilidade** — `StoreEvaluationAction` e `EvaluationDTO` não mudam (só recebem int onde antes recebiam string).

### 6.3 i18n

**Contexto:** o `KeyValue` some, então `forms.scores` e `forms.criteria_key_placeholder` ficam órfãos. Os labels dos 4 critérios já existem em `panel-organization::view.tabs.feedbacks.criteria.*` e são reaproveitados.

- **Remover** (`panel-organization/lang/{en,pt_BR}/filament.php`): `forms.scores`, `forms.criteria_key_placeholder`.
- **Adicionar** (`panel-organization/lang/{en,pt_BR}/filament.php`): `forms.evaluation_section.heading` e `forms.evaluation_section.description`.
- **Reaproveitar:** `panel-organization::view.tabs.feedbacks.criteria.{technical_skills,communication,problem_solving,culture_fit}`.

### 6.4 Actions (`MoveStageAction`, `StateTransitionAction`)

**Contexto:** ambas leem `$data['criteria_scores']['technical_skills']` etc. Com os ToggleButtons em dot-notation, `$data['criteria_scores']` continua um array com as 4 chaves. **Nenhuma mudança de código** nas actions — só herdam a nova UI via `EvaluationForm`.

## 7. Fluxo de dados (sem migration)

```
  [ToggleButtons]      [Form state]                 [DTO]            [Persistência]      [Exibição]
   nota int 1..5  ──►  criteria_scores:        ──► CriteriaScores ─► Evaluation       ─► feedbacks.blade
   (dot-notation)      { technical_skills: 4,      DTO (int)          .criteria_scores    (int) N/5 + barra
                         communication: 3, ... }                      jsonb / cast array  (já existente)
```

## 8. Tratamento de erros e testes

**Erros:** validação `required` por critério é nativa do Filament; sem nota → `assertHasFormErrors`. A transição em si segue tratada como antes (try/catch nas actions).

**Plano de testes:**

| Cenário | Arquivo | Esperado |
| ------- | ------- | -------- |
| Mover com avaliação (4 notas int + nota geral) | `MoveStageActionTest` | `Evaluation` criada com `criteria_scores` numérico |
| Toggle ligado, critério faltando | `MoveStageActionTest` | `assertHasFormErrors(['criteria_scores.technical_skills'])` |
| Toggle desligado | `MoveStageActionTest` | só transição, nenhum `Evaluation` |
| Transição via Kanban com avaliação (form novo) | `ChangeApplicationStatusTest` | passa enviando `criteria_scores.* => int` |
| DTO/persistência numérica | `EvaluationTest` (feedback) | criteria_scores como inteiros |

> Testes existentes que preenchem `criteria_scores` como string/KeyValue precisam migrar para chaves separadas com int (`'criteria_scores.technical_skills' => 4`).

## 9. Compatibilidade

- **Dados existentes:** valores já gravados (factory/seed) são inteiros — exibição inalterada. Eventuais strings numéricas antigas continuam sendo lidas via `(int)` no blade.
- **Sem migration:** coluna `jsonb` inalterada.
- **Actions:** contrato de `EvaluationForm::make()` preservado; `processAction` das duas actions inalterado.
- **StateTransitionAction:** lógica de transição intacta; apenas a UI de avaliação muda (decisão consciente — escopo "form compartilhado").
