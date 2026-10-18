---
type: spec
title: 'Espaço de candidaturas do recrutador: panorama de vagas → fila com prévia'
module: panel-organization, applications, recruitment, screening, feedback
status: approved
date: 2026-09-02
author: Clintonrocha98
related:
    plan: plans/2026-09-02-espaco-de-candidaturas-do-recrutador
    prototype: docs.local/prototype (descartável, fora do git)
---

# Espaço de candidaturas do recrutador

## Contexto

A página **Recrutamento → Candidaturas** do painel `organization`
(`/organization/{team}/recruitment/applications`) hoje é uma tabela Filament agrupada por vaga
(`ApplicationsTable`). Ela não escala para dezenas de vagas e centenas de candidatos, não mostra se
o candidato reprovou em pergunta eliminatória e não indica se alguém do time já olhou a candidatura.

Um protótipo (variantes A/B/C, depois D/E) foi validado com o usuário em 2026-09-01/02. A variante
**E** venceu: **panorama de vagas em cartões → fila de candidatos com prévia fixa**. O protótipo
está em `docs.local/prototype/` (fora do git) e serve de referência visual; nada dele é promovido
diretamente.

## Objetivos

1. Abrir a página no **panorama**: um cartão por vaga com novas pendentes, não vistos pelo time,
   funil por etapa e aprovados na pergunta eliminatória.
2. Ao entrar numa vaga, listar seus candidatos numa **fila** paginada (25 por página), com busca,
   filtros (etapa, grupo de status, veredito da eliminatória, visto/não visto) e ordenação no
   servidor, e uma **prévia** fixa do candidato selecionado.
3. Tornar o **veredito da pergunta eliminatória** um sinal de primeira classe: no cartão da vaga
   (aprovados), no cabeçalho da vaga (aprovados / reprovados / sem resposta, clicáveis), na fila
   (pill por candidato + borda vermelha para reprovado) e na prévia (todas as perguntas
   eliminatórias com a resposta). Nada disso aparece em vaga sem pergunta eliminatória.
4. Registrar **visto pelo time**: a primeira vez que qualquer recrutador abre a candidatura completa
   (`ViewApplication`), grava-se quem viu e quando. A fila mostra o ponto de "não visto", filtra por
   visto/não visto e os cartões contam não vistos.
5. Substituir a tabela Filament de vez, mantendo o restante da página (título, navegação, busca global).

## Não-objetivos

- Ações na lista (mover etapa, rejeitar, oferta) — continuam em `ViewApplication` e no Kanban.
- Alterar `ViewApplication` além de registrar o visto.
- Marcar como visto ao abrir a prévia (decisão do usuário: só a candidatura completa conta).
- Visto por usuário (decisão: por time; uma marcação por candidatura, guardando quem viu primeiro).
- Exportação de candidatos (já existe na tabela de vagas e não muda).

## Decisões (2026-09-02, usuário)

| Tema         | Decisão                                                                                                                 |
| ------------ | ----------------------------------------------------------------------------------------------------------------------- |
| Escala       | Dezenas de vagas, centenas de candidatos por vaga → tudo no servidor (busca, filtros, ordenação, paginação, agregações) |
| Navegação    | Panorama primeiro; `?job={requisition}` abre a vaga; seletor pesquisável troca de vaga                                  |
| Eliminatória | Badge + filtro + contagem; cartão mostra **aprovados**, não eliminados                                                  |
| Visto        | **Por time**, tabela `application_views` (uma linha por candidatura), gatilho **somente** `ViewApplication`             |
| Tabela atual | Substituir de vez; `ApplicationsTable` e colunas custom saem                                                            |
| Escopo       | Somente leitura + link "Abrir candidatura"                                                                              |

## Vocabulário

| Termo                    | Significado                                                                                                                                                                                                 |
| ------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Panorama                 | Estado inicial da página: lista paginada de vagas (cartões) com agregados                                                                                                                                   |
| Fila                     | Lista paginada de candidaturas de uma vaga, ordenada por atenção por padrão                                                                                                                                 |
| Prévia                   | Painel fixo à direita com o resumo da candidatura selecionada na fila                                                                                                                                       |
| Grupo de status          | Agrupamento de `ApplicationStatusEnum` em Novas (new, in_review), Em processo (in_progress), Oferta e contratação (offer_extended, offer_accepted, hired), Encerradas (rejected, withdrawn, offer_declined) |
| Veredito da eliminatória | Para uma vaga com `ScreeningQuestion.is_knockout`: aprovado (respondeu e nenhuma `is_knockout_fail`), reprovado (alguma `is_knockout_fail`) ou sem resposta                                                 |
| Visto pelo time          | Existe `ApplicationView` para a candidatura (quem viu primeiro e quando)                                                                                                                                    |
| Tempo na etapa           | Dias desde a última movimentação (`application_stage_history.created_at` máximo) ou, sem histórico, desde a inscrição                                                                                       |
| Atraso                   | Tempo na etapa maior que `Stage.expected_duration_days`, para candidaturas não encerradas                                                                                                                   |
| Ordem de atenção         | Novas → Em análise → Em processo → Oferta → Contratada → Encerrada; desempate por mais tempo na etapa                                                                                                       |

## Arquitetura

```
panel-organization (apresentação)                  applications (domínio)
┌──────────────────────────────────────┐          ┌──────────────────────────────────────┐
│ ListApplications::content()          │          │ Application (scopes de listagem,     │
│   └─ Livewire ApplicationsWorkspace  │──usa────▶│   tempo na etapa, veredito, média)   │
│        ├─ workspace.blade (raiz)     │          │ ApplicationView + MarkApplication…   │
│        ├─ overview (cartões)         │          │ Enums: StatusGroup, ScreeningVerdict │
│        ├─ requisition (cabeçalho,    │          │   Filter, SeenFilter, ListSort       │
│        │   fila, prévia)             │          │ Actions: BuildRequisitionFunnels,    │
│        └─ components/workspace/*     │          │   BuildRequisitionApplicationStats   │
│ ViewApplication::mount → MarkViewed  │          │ DTO: RequisitionApplicationStats     │
└──────────────────────────────────────┘          └──────────────────────────────────────┘
        │ usa                                       recruitment: JobRequisition scopes de
        ▼                                           panorama + RequisitionOverviewSort
screening: CountKnockoutQuestionsByRequisition      feedback: EvaluationRatingEnum::score()
```

- Toda consulta vive em **scopes** (`#[Scope]`) ou **Actions** de domínio; o componente Livewire
  só compõe scopes, guarda estado e renderiza. Nenhum SQL no `panel-organization`.
- `Application::STAGE_SINCE_SQL` é a única expressão de "tempo na etapa"; scopes de seleção e de
  ordenação a reutilizam (alias `stage_since` não pode ser usado em `ORDER BY` com expressão no
  Postgres, por isso a expressão se repete via constante).
- Agregados por vaga vêm de `withCount`/`withMin` (`JobRequisition::withRecruiterOverviewCounts`)
  mais duas consultas por página: funil (uma consulta agrupada) e perguntas eliminatórias
  (vaga + etapas).

## Modelo de dados

Nova tabela `application_views` (módulo `applications`):

| Coluna                     | Tipo           | Regra                                 |
| -------------------------- | -------------- | ------------------------------------- |
| `id`                       | uuid           | pk                                    |
| `team_id`                  | uuid           | fk `teams`, cascade                   |
| `application_id`           | uuid           | fk `applications`, cascade, **único** |
| `viewed_by`                | uuid, nullable | fk `users`, null on delete            |
| `viewed_at`                | timestamp      | momento da primeira abertura          |
| `created_at`, `updated_at` | timestamp      |                                       |

`Application::teamView(): HasOne<ApplicationView>`. Scopes `unseenByTeam()` / `seenByTeam()`.

## Antes / depois

Antes (`ListApplications` herda `content()` de `ListRecords`):

```php
// ApplicationResource::table() → ApplicationsTable::configure($table)
// EmbeddedTable::make() renderiza a tabela agrupada por vaga
```

Depois:

```php
public function content(Schema $schema): Schema
{
    return $schema->components([
        Livewire::make(ApplicationsWorkspace::class),
    ]);
}
```

`ViewApplication::mount()` passa a chamar `MarkApplicationAsViewed` após autorizar.

## Comportamento esperado

**Panorama**

- Dado um time com vagas publicadas e não publicadas, quando o recrutador abre a página, então vê
  só as publicadas, ordenadas por mais novas pendentes; com "Incluir não publicadas" vê todas.
- Dado uma vaga com pergunta eliminatória, então o cartão mostra "N aprovados na eliminatória" e
  "M pergunta(s)"; sem pergunta eliminatória, mostra "Sem pergunta eliminatória".
- Dado candidaturas abertas ainda sem `ApplicationView`, então o cartão mostra "N não vistos".
- Dado mais de 20 vagas, então o panorama pagina (`jobsPage`).

**Vaga (fila + prévia)**

- Dado `?job={id}` de outra vaga do mesmo time, então a fila abre nela; de outro time, então a
  vaga não é encontrada e a página volta ao panorama.
- Dado uma vaga com eliminatória, então cada linha mostra o veredito (aprovado / reprovado n de m /
  sem resposta) e o cabeçalho mostra os três totais clicáveis; sem eliminatória, nenhum desses sinais aparece.
- Dado uma candidatura sem `ApplicationView`, então a linha tem ponto índigo e nome em negrito;
  com `ApplicationView`, ponto vazio e peso normal.
- Dado o filtro "Não vistos", então só candidaturas sem `ApplicationView` aparecem; "Já vistos", só com.
- Dado a ordenação padrão, então Novas vêm antes de Em análise, estas antes de Em processo, e
  entre iguais a que está há mais tempo na etapa vem primeiro.
- Dado busca "ana", então casa nome, e-mail, headline e código de rastreio (case-insensitive).
- Dado um clique na linha, então a prévia mostra o candidato **sem** criar `ApplicationView`.
- Dado uma vaga sem etapas ou candidatura sem etapa atual, então a página renderiza sem erro.

**Visto pelo time**

- Dado um recrutador autorizado abrindo `ViewApplication`, então existe exatamente uma
  `ApplicationView` com `viewed_by` = ele; abrir de novo (ele ou outro) não cria nem altera.
- Dado um usuário sem permissão, então a página é 403 e nenhuma `ApplicationView` é criada.

**Compatibilidade**

- `OwnerApplicationAccessTest`: continua não havendo rota de edição; o dono do time abre o espaço.
- Busca global (`tracking_code`, nome, e-mail) inalterada.

## i18n

Todas as strings novas em `panel-organization/lang/{en,pt_BR}/workspace.php`; labels dos enums
novos em `applications/lang/{en,pt_BR}/enums.php` e `recruitment/lang/{en,pt_BR}/enums.php`.

## Testes

Pest: Actions e scopes nos módulos de domínio; Livewire (`livewire()`) para o componente e para
`ListApplications`/`ViewApplication`. Reescrever os três testes da tabela antiga preservando a
intenção (só publicadas por padrão; sem rota de edição; sem erro com etapas ausentes).

## Riscos e trade-offs

- `whereNotIn` sobre `applications.status` com strings: os valores vêm de `ApplicationStatusEnum`,
  nunca literais soltos.
- Contagem de perguntas eliminatórias considera vaga **e** etapas (`Stage::screeningQuestions`).
- `jobStats` faz várias consultas por render; aceitável (uma vaga por vez). Consolidar se pesar.
- Visto por time perde a informação "eu já vi"; decisão consciente do usuário, reversível
  (basta trocar a unicidade para `application_id + viewed_by`).
