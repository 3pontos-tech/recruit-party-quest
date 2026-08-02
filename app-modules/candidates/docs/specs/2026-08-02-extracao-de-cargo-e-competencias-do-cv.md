---
module: candidates
date: 2026-08-02
authors: [Clintonrocha98]
id: candidates/spec/extracao-de-cargo-e-competencias
title: 'Extração de cargo e competências do currículo'
summary: 'Corrige o crash na hidratação das experiências extraídas por IA e passa a extrair cargo e competências do CV, substituindo as heurísticas que hoje inventam esses dados no painel do RH.'
format: spec
purpose: reference
department: ti
audience: [ti]
keywords: [onboarding, extracao, curriculo, gemini, metadata, cargo, competencias]
status: published
---

# Extração de cargo e competências do currículo

## Contexto

Duas questões independentes, com a mesma origem: o contrato entre o que pedimos ao
Gemini e o que o código assume ter recebido.

### O crash

`ErrorException: Undefined array key "company_name"` em
`CandidateWorkExperienceDTO::make()`, durante o `AiAnalyzeResumeJob`.

A causa raiz **não** é o PDF do candidato. É o schema enviado ao Gemini, que não
declara nenhum campo como obrigatório. `CvDataSchema` constrói todos os `ObjectSchema`
com três argumentos, deixando `requiredFields` no default `[]` — e o `SchemaMap` do
Prism descarta arrays vazios via `array_filter`. O JSON que chega à API não tem
`required` em nível nenhum:

```json
"work_experiences": {
  "type": "array",
  "items": {
    "type": "object",
    "properties": { "company_name": {...}, "description": {...} }
  }
}
```

O modelo está livre para omitir qualquer campo sem violar o contrato. Quando omitiu
`company_name`, o DTO — que acessa `$data['company_name']` sem guard — derrubou o job.

Efeito para o candidato: a análise inteira do CV é perdida e ele recebe uma mensagem
de indisponibilidade do serviço, porque `AiAnalyzeResumeJob::failed()` emite sempre a
notificação de rate limit, independente da causa.

### O cargo que nunca existiu

O painel da organização exibe um cargo no cabeçalho de cada experiência. Esse dado
não existe: não há coluna, não é extraído do CV e não há campo nos formulários do
candidato. O blade `work-experience.blade.php` o **infere** — lê `metadata['position']`
e, na ausência, usa a primeira linha da descrição quando ela tem até 60 caracteres.

A coluna `metadata` (jsonb) nunca teve produtor. Verificação em produção:

```sql
SELECT count(*) AS total, count(metadata) AS com_metadata
FROM candidate_work_experiences;
-- 3029 | 0
```

Zero registros. O único código que escreve nela é o `WorkExperienceFactory`, em seeds e
testes. O contrato imaginado pelo blade nunca bateu com o do factory:

| Chave          | Lida pelo blade | Escrita pelo factory | Existe em produção |
| -------------- | --------------- | -------------------- | ------------------ |
| `position`     | sim             | sim                  | não                |
| `technologies` | sim             | sim                  | não                |
| `skills`       | sim             | **não**              | não                |
| `team_size`    | não             | sim                  | não                |
| `project_type` | não             | sim                  | não                |

`skills` é lido por código que nunca executou; `team_size` e `project_type` são gerados
no seed e nunca lidos. Como `metadata` é nulo em produção, os guards `if ($metadata && …)`
fazem o blade cair direto nas heurísticas — o recrutador vê prosa da descrição
apresentada como cargo, ou um texto genérico, e tags de tecnologia produzidas por um
`stripos()` contra uma lista fixa de 15 termos.

## Objetivos

1. Eliminar o `ErrorException`, tornando o contrato com o Gemini explícito e a
   hidratação tolerante.
2. Extrair cargo e competências do CV e persistir ambos.
3. Substituir as heurísticas do painel do RH por leitura de dados reais.
4. Tipar o conteúdo de `metadata` com um value object, em vez de array solto.

## Não-objetivos

- **Backfill dos 3029 registros existentes.** Os CVs originais não são armazenados;
  não há como reprocessar. Esses registros permanecem sem cargo até edição manual.
- **Mensagem de erro por causa raiz.** `failed()` continua genérico; o contrato
  explícito reduz a incidência na origem.
- **Unificar `metadata->skills` com a relação `Candidate::skills()`.** São
  granularidades distintas — a relação é global ao candidato, o metadata é por
  experiência.

## Decisões

### Cargo é coluna, competências ficam no metadata

`position` vira `string` nullable em `candidate_work_experiences`. É o dado que vira
cabeçalho do card e o primeiro candidato a virar filtro no painel do RH — merece
índice B-tree comum, não query em jsonb.

`metadata` recebe um cast tipado com uma única chave, `skills`. Optamos por uma chave
só (e não `skills` + `technologies`): o blade atual já fazia merge das duas, o que
evidencia que são o mesmo conceito, e forçar o Gemini a escolher um balde por item só
adiciona ruído.

`team_size` e `project_type` são removidos, inclusive do factory. Não são extraíveis
de um currículo — eram chute de quem escreveu o seed.

### Campos opcionais ficam fora de `requiredFields`

`requiredFields` passa a declarar apenas `company_name`, `start_date` e
`is_currently_working_here` — o mínimo que identifica uma experiência (`company_name` +
`start_date` são a chave do `firstOrCreate`).

`position`, `skills` e `description` ficam **fora**, deliberadamente. Extração de CV é
probabilística: currículos têm formatos muito variados e nem sempre trazem a informação.
Forçar o campo no schema não faz o modelo encontrar o dado — faz ele inventar. Cargo
inventado num sistema de contratação é pior que cargo ausente.

Consequência aceita: registros com descrição vazia são válidos. Melhor guardar empresa,
cargo e período que foram extraídos bem do que descartar a experiência inteira por um
campo faltante. O candidato completa depois.

A única exceção é `company_name`: sem ele não há chave, e o `firstOrCreate` criaria
registros anônimos colidindo entre si. A Action descarta esses itens, com log.

### O objeto tipado não cruza o Livewire

O DTO e o state do formulário trabalham com campos primitivos (`?string $position`,
`array $skills`). O value object existe apenas do Eloquent para a apresentação.

Livewire v4 aceita como propriedade somente primitivos, `BackedEnum`, `Collection`,
`Model`, `DateTime`/`Carbon` e `Stringable`; qualquer outro objeto exige `Wireable` ou
um Synthesizer. Mantendo a fronteira plana, essa restrição deixa de ser um problema por
construção — não por remendo. Nenhum componente do projeto guarda o model como
propriedade (todos usam `public ?array $data`), então o objeto nunca chega perto da
serialização.

A alternativa — objeto atravessando com `Wireable` — foi descartada: o `TagsInput` do
Filament precisa de `array<string>` no state de qualquer forma, então pagaríamos o
`Wireable` e ainda achataríamos o objeto no `mount()`.

### Cast devolve objeto vazio, nunca nulo

`AsWorkExperienceMetadata::get()` retorna uma instância vazia quando a coluna é nula.
Isso é possível porque custom casts são invocados mesmo com valor nulo
(`HasAttributes::getClassCastableAttributeValue()` chama `$caster->get()` sem checar
null — a ressalva da documentação vale apenas para casts nativos). O consumidor lê
`$experience->metadata->skills` direto, sem `?->` espalhado.

### Cargo obrigatório apenas no onboarding

Os dois fluxos de upload são diferentes:

```
ONBOARDING (OnboardingWizard)
  upload → job → broadcast .finished
                     ↓
            onResumeAnalyzed() → preenche o FORM
                                        ↓
                             candidato revisa e completa
                                        ↓
                             handleRegistration() → getState() VALIDA
                                        ↓
                                     banco

MYPROFILE (CandidateResumeUpload)
  upload → job → broadcast .finished
                     ↓
            finished() → StoreCandidateWorkExperiences → banco
                         SEM form, SEM revisão, SEM validação
```

`position` é `->required()` no wizard — é a única janela de revisão que existe, e o
candidato já está preenchendo o formulário. No MyProfile é opcional: torná-lo
obrigatório travaria o salvamento de quem tem experiências antigas sem cargo, que
precisaria preencher todas antes de qualquer alteração.

`description` mantém `->required()` no wizard pelo mesmo motivo, embora tenha saído do
schema.

### Re-upload não atualiza o que já existe

`StoreCandidateWorkExperiences` mantém `firstOrCreate` puro. Experiências novas são
criadas com cargo e competências; as existentes não são tocadas.

Foi considerado preencher retroativamente os campos vazios de registros existentes no
re-upload — seria a única forma de os 3029 registros antigos ganharem cargo sem
digitação. Descartado: o candidato pode editar ou apagar o que quiser pelo perfil, e a
gravação automática sem revisão do MyProfile torna qualquer escrita sobre dado
existente um risco desproporcional ao ganho.

## Arquitetura

```
  [PDF]              [Gemini]                    [DTO plano]              [Banco]
     │                   │                            │                      │
  arquivo ─► CvDataSchema ─► structured output ─► CandidateWork      ─► firstOrCreate
             pede:           devolve:               ExperienceDTO          grava:
             ├ company_name  ├ company_name         ├ companyName          ├ company_name
             ├ position      ├ position (opc.)      ├ position             ├ position
             ├ description   ├ description (opc.)   ├ description          ├ description
             ├ skills        ├ skills (opc.)        ├ skills               ├ metadata
             ├ start_date    ├ start_date           ├ startDate            ├ start_date
             ├ end_date      ├ end_date             ├ endDate              ├ end_date
             └ is_currently  └ is_currently         └ isCurrentlyWorking   └ is_currently
                                                            │                      │
             required: company_name,                        │                      │
                       start_date,          primitivos, nunca objeto ──────────────┘
                       is_currently_working_here            │
                                                            ▼
                                              Livewire / Filament state
                                              (arrays, sem Wireable)

  [Banco] ──► WorkExperience::$metadata ──► WorkExperienceMetadata ──► blade do RH
              cast AsWorkExperienceMetadata   objeto tipado,            $experience->position
                                              vazio quando nulo         $experience->metadata->skills
```

## Componentes

| Camada       | Arquivo                                                    | Mudança                                                                     |
| ------------ | ---------------------------------------------------------- | --------------------------------------------------------------------------- |
| Extração     | `AI/Schema/CvDataSchema.php`                               | `position` e `skills`; `requiredFields` nos três `ObjectSchema`             |
| Extração     | `AI/Prompts/CvAnalysisPrompt.php`                          | instrução para cargo e competências, e para omitir quando não identificável |
| Extração     | `Actions/Onboarding/CompleteOnboardingAction.php`          | `$output['is_cv'] ?? false` no `validate()`                                 |
| Domínio      | `DTOs/CandidateWorkExperienceDTO.php`                      | `position` e `skills`; acessos tolerantes                                   |
| Domínio      | `DTOs/CandidateEducationDTO.php`                           | acessos tolerantes                                                          |
| Domínio      | `DTOs/WorkExperienceMetadata.php`                          | **novo** — value object `final readonly`                                    |
| Domínio      | `Casts/AsWorkExperienceMetadata.php`                       | **novo** — `CastsAttributes`                                                |
| Domínio      | `Models/WorkExperience.php`                                | cast, PHPDoc, `#[Table]`                                                    |
| Domínio      | `Actions/Onboarding/StoreCandidateWorkExperiences.php`     | grava `position` e `metadata`; descarta item sem `company_name`             |
| Migração     | `..._add_position_to_candidate_work_experiences_table.php` | **novo** — `string` nullable                                                |
| Seed         | `database/factories/WorkExperienceFactory.php`             | `position` vira coluna; `team_size`/`project_type` removidos                |
| Apresentação | `panel-app/.../OnboardingWizard.php`                       | `TextInput` obrigatório + `TagsInput`; `itemLabel`                          |
| Apresentação | `panel-app/.../MyProfile/CandidateWorkExperience.php`      | campos opcionais; `mount()`/`submit()`                                      |
| Apresentação | `panel-organization/.../tabs/work-experience.blade.php`    | remove as três heurísticas                                                  |
| i18n         | `lang/{en,pt_BR}` dos dois painéis                         | labels, placeholders, helper e fallback                                     |

O PHPDoc de `metadata` hoje declara `Collection<int, string>`, tipo que nunca
correspondeu ao cast `array`. Passa a declarar o value object.

## Comportamento esperado

**Extração completa** — Dado um CV com cargo e stack identificáveis, Quando a análise
conclui, Então `position` e `skills` chegam preenchidos ao formulário.

**CV sem cargo identificável** — Dado que o Gemini não encontra o cargo, Então omite a
chave, o DTO usa `null`, e o candidato preenche no wizard (onde o campo é obrigatório).

**Campo omitido pelo modelo (o bug original)** — Dado um item sem `company_name`,
Então a hidratação não lança exceção; no wizard o `->required()` obriga o
preenchimento, e na Action o item é descartado com log.

**Descrição vazia** — Dado que o Gemini não extrai a descrição, Então a experiência é
gravada com descrição vazia, preservando empresa, cargo e período.

**Job enfileirado antes do deploy** — Dado um payload sem `position`/`skills`, Então os
defaults do DTO hidratam sem erro.

**Registro legado no painel do RH** — Dado uma experiência com `position` nulo, Então o
card exibe o fallback traduzido, nenhuma tag, e a descrição íntegra — sem a primeira
linha removida pelo `formatJobDescription`.

**Candidato antigo no MyProfile** — Dado um candidato com experiências sem cargo,
Quando ele edita qualquer campo, Então salva normalmente; o cargo é opcional.

**Re-upload no perfil** — Dado um CV com uma experiência nova e uma já cadastrada,
Então a nova é criada com cargo e competências e a existente permanece intocada.

## Trade-offs e riscos

**Os 3029 registros seguem sem cargo.** É consequência direta de não armazenarmos os
CVs. O painel do RH passa a mostrar um fallback honesto onde antes mostrava um palpite
— para o recrutador, é uma perda aparente de informação que na verdade é ganho de
confiabilidade.

**`description` fora do `requiredFields` reduz a pressão sobre o modelo.** Pode
aumentar a frequência de descrições vazias. É a escolha deliberada de aceitar lacuna em
vez de fabricação.

**`metadata` nasce com uma chave só.** O value object é a estrutura de extensão; se
nenhuma segunda chave aparecer, ele terá custado uma classe pequena e a remoção de duas
heurísticas.

**O `TagsInput` é texto livre.** `suggestions()` puxa o catálogo `candidate_skills`
para empurrar o candidato ao vocabulário canônico, mas nada impede variações. Aceito:
o metadata registra o que o CV diz, não o vocabulário controlado — esse papel continua
sendo da relação `Candidate::skills()`.

## Fora de escopo

- Mensagem de erro diferenciada por causa em `AiAnalyzeResumeJob::failed()`.
- Filtro ou busca por cargo no painel da organização (a coluna viabiliza, não implementa).
- Reconciliação entre `metadata->skills` e o catálogo `candidate_skills`.
- Command de backfill via reprocessamento de CV.
