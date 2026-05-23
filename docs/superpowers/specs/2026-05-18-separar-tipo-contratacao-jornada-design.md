# Design — Separação de Tipo de Contratação e Jornada de Trabalho

- **Data:** 2026-05-18
- **Branch:** `feat/separate-work-schedule` (a partir de `origin/develop`)
- **Módulo principal:** `app-modules/recruitment`
- **Status:** Aprovado para planejamento

## Problema

O `EmploymentTypeEnum` (campo "Tipo de contratação" em `recruitment_job_requisitions.employment_type`)
mistura **dois conceitos ortogonais e independentes**:

- **Regime jurídico do contrato** (CLT, PJ, Temporário...) — *como* a pessoa é contratada.
- **Jornada / carga horária** (Tempo integral, Meio período...) — *quanto* se trabalha.

Hoje o enum contém `full_time_employee` ("Tempo Integral") e `part_time` ("Meio Período"),
que são **jornada**, não regime. Consequência: ao criar uma vaga o recrutador escolhe **um único
valor** e perde a outra dimensão — é impossível registrar "CLT + tempo integral" juntos.

```
Vaga "Dev Backend Sênior":
  campo único "Tipo de contratação":
    [ CLT ] [ Contractor ] [ Estagiário ] [ Temporário ] [ Tempo Integral ] [ Meio Período ]
                                                            ▲ não é contrato!
  Escolhe "CLT"            → perde a jornada
  Escolhe "Tempo Integral" → perde o regime
```

Os eixos **Local** (`WorkArrangementEnum`: Remote/Hybrid/OnSite) e **Senioridade**
(`ExperienceLevelEnum`: Intern...CLevel) já estão corretamente separados e **não são tocados**.

## Decisões tomadas (brainstorming)

| Decisão | Resolução |
|---|---|
| Eixo regime final | `clt`, `contractor`, `temporary`, `freelancer` (sem estágio) |
| Eixo jornada (novo) | `full_time`, `part_time`, `hourly`, `shift` |
| Estratégia de dados | Ambas colunas **nullable**; `NULL` = "Não informado" (sem chute) |
| `contractor` | **Não renomear o valor**; só ajustar label → "Contrato/PJ" / "Contractor (PJ)" |
| Dados `intern` legados | Backfill → `employment_type = NULL` (estágio descartado do eixo) |
| Estratégia de migração | Abordagem A — migration única, transacional, reversível |

## Modelo de domínio

Dois eixos independentes na `JobRequisition`, ambos opcionais (`NULL` = "Não informado").
Qualquer combinação é válida.

```
ARQUITETURA DE ENUMS — He4rt\Recruitment\Requisitions\Enums\

  ┌────────────────────────┬──────────────────────────────────┬─────────────┐
  │ WorkArrangementEnum    │ Remote · Hybrid · OnSite          │ inalterado  │
  │ ExperienceLevelEnum    │ Intern · Trainee ... CLevel       │ inalterado  │
  │ EmploymentTypeEnum     │ clt · contractor · temporary      │ CORRIGIDO   │
  │   (regime, nullable)   │   · freelancer                    │             │
  │ WorkScheduleEnum       │ full_time · part_time · hourly    │ NOVO        │
  │   (jornada, nullable)  │   · shift                         │             │
  └────────────────────────┴──────────────────────────────────┴─────────────┘

  PERGUNTA 1: "Qual o regime?"        PERGUNTA 2: "Qual a carga horária?"
    employment_type                     work_schedule
    clt · contractor                ⊗   full_time · part_time
    temporary · freelancer        (qualquer  hourly · shift
    NULL = Não informado          combinação) NULL = Não informado
```

### EmploymentTypeEnum (corrigido)

- **Remove:** `FullTimeEmployee`, `Intern`, `PartTime`.
- **Mantém:** `Clt = 'clt'`, `Contractor = 'contractor'`, `Temporary = 'temporary'`.
- **Adiciona:** `Freelancer = 'freelancer'`.
- **Valor `contractor` preservado** (sem UPDATE em produção); apenas o label muda.
- Atualiza `getColor()`, `getIcon()` e chaves i18n para o novo conjunto.

### WorkScheduleEnum (novo)

Segue o padrão exato de `WorkArrangementEnum`: `string`, `use StringifyEnum`,
implementa `HasLabel`/`HasColor`/`HasIcon`, label via
`__('recruitment::requisitions.work_schedule.'.$this->value.'.label')`.

```
case FullTime = 'full_time'   → Tempo Integral
case PartTime = 'part_time'   → Meio Período
case Hourly   = 'hourly'      → Por Hora
case Shift    = 'shift'       → Escala (12x36, 6x1)
```

## Banco de dados — Abordagem A

Migration única, em transação, reversível.

```
FLUXO DE DADOS — migration up()

 [estado legado]        [transação]                    [estado novo]
 employment_type   ──► ALTER employment_type NULLABLE
 (string, 6 valores)    ADD work_schedule NULLABLE
                        UPDATEs de backfill        ──►  employment_type (nullable)
                                                        work_schedule  (nullable)

BACKFILL (mapeamento legado → par):
  clt                → (clt,        NULL)
  contractor         → (contractor, NULL)      ← no-op, valor preservado
  temporary          → (temporary,  NULL)
  intern             → (NULL,       NULL)       ← estágio descartado
  full_time_employee → (NULL,       full_time)  ← ~85% das linhas (factory)
  part_time          → (NULL,       part_time)
```

- Coluna `work_schedule` adicionada **após** `employment_type`, `nullable`,
  com `comment(WorkScheduleEnum::stringifyCases())`.
- `employment_type` alterado para `nullable`; comment atualizado. **Atenção Laravel 12:**
  ao alterar a coluna, repetir todos os atributos preexistentes (string, comment) para
  não perdê-los.
- `down()`: best-effort reverso — `work_schedule=full_time`→`employment_type=full_time_employee`,
  `work_schedule=part_time`→`part_time`; demais regimes preservados; drop `work_schedule`.
  Perda esperada: linhas que ficaram `(NULL, NULL)` por `intern` não recuperam `intern`
  (documentado; aceitável pois estágio foi descartado por decisão de produto).

## Camada de aplicação

| Componente | Mudança |
|---|---|
| `JobRequisition` (model) | cast `work_schedule` → `WorkScheduleEnum`; `@property ?EmploymentTypeEnum`; `@property ?WorkScheduleEnum` |
| `JobRequisitionDTO` | `employmentType` → `?EmploymentTypeEnum`; novo `?WorkScheduleEnum $workSchedule`; `fromArray` lê `work_schedule` |
| `GenerateJobRequisitionDTO` | idem: `employmentType` nullable + `workSchedule` |
| `StoreJobRequisitionAction` | persistir `work_schedule` no `create`/`update` |
| `GenerateJobRequisition` (IA) | prompt explica os 2 eixos; structured output passa a emitir `work_schedule`; enum atualizado no contexto do prompt |

## UI

- **Forms** (panel-admin, panel-organization, panel-app `JobRequisitionForm`):
  `Select::make('work_schedule')->options(WorkScheduleEnum::class)` ao lado de
  `employment_type`. **Ambos sem `->required()`** (nullable); placeholder "Não informado".
- **Tables/Infolists** (3 painéis): nova coluna/entry `work_schedule`; ambas exibem
  "Não informado" quando `NULL` (`->placeholder()` / `->default()`).
- **Blade:**
  - `panel-app/.../job-description.blade.php:62-63` — adicionar null-safe em
    `employment_type` e novo bloco para `work_schedule`.
  - `panel-app/.../job-card.blade.php:65` — substituir fallback hardcoded `'Full Time'`
    por label real / "Não informado".
- **Busca/Recomendação:**
  - `SearchJobs` — novo filtro `work_schedule` (`whereIn` análogo a `employment_type`);
    `employment_type` com `NULL` simplesmente não casa em `whereIn` (comportamento aceito).
  - `JobRecommendations` — idem; `normalizedJobTypes()` inalterado.
- **Onboarding** (`panel-app` `OnboardingWizard:443` `employment_type_interests`):
  campo de **texto livre** (sem FK ao enum) — apenas ajustar placeholder/helper i18n
  removendo `full_time_employee`. Risco baixo, cosmético.

## i18n (`app-modules/recruitment/lang/{en,pt_BR}`)

- `requisitions.php`: substituir bloco `employment_type` pelo novo conjunto
  (`clt`, `contractor`, `temporary`, `freelancer`); adicionar bloco `work_schedule.*`.
- `filament.php`: label/description do campo `work_schedule`; revisar `employment_type`.
- Label de `NULL`: pt_BR "Não informado" / en "Not specified".
- `panel-app/lang/{en,pt_BR}/pages/onboarding.php`: ajustar placeholder/helper de
  `employment_type_interests`.
- `panel-organization/lang/{en,pt_BR}/filament.php`: chaves do novo campo.

## Testes

- **Unit:** `WorkScheduleEnum` (label/color/icon p/ cada case); `EmploymentTypeEnum`
  revisado (cases corretos, label de `contractor`).
- **Migration:** para cada um dos 6 valores legados, asserir o par resultante
  `(employment_type, work_schedule)`; asserir reversibilidade do `down()`.
- **Feature:** criar/editar `JobRequisition` com os 2 campos incl. ambos `NULL`;
  Filament action tests nos 3 painéis (`callAction`/`TestAction`); busca filtrando por
  jornada; IA emitindo `work_schedule` válido.
- **Regressão:** arch/feature garantindo que `WorkArrangementEnum` e
  `ExperienceLevelEnum` não mudaram.
- Rodar por arquivo/filtro com `php artisan test --compact`.

## Side effects mapeados (risco)

```
ALTO   IA (GenerateJobRequisition) ainda pode emitir 'full_time_employee'
       → enum + prompt + schema structured output atualizados JUNTOS, mesmo PR
MÉDIO  job-description.blade.php:63 sem null-safe → quebra com NULL em prod
BAIXO  busca: employment_type/work_schedule NULL não aparece em whereIn
       (comportamento aceito: "Não informado" não é filtrável)
BAIXO  onboarding employment_type_interests é texto livre (sem FK ao enum)
```

## Fora de escopo (YAGNI)

- Não adicionar `estágio`, `jovem aprendiz`, `cooperado`, `trainee` ao regime agora.
- Não renomear o valor `contractor` no banco.
- Não tocar `WorkArrangementEnum` nem `ExperienceLevelEnum`.
- Sem comando Artisan de backfill nem migração multi-passo (Abordagem A apenas).
- Sem campo estruturado de jornada no onboarding do candidato (segue texto livre).
