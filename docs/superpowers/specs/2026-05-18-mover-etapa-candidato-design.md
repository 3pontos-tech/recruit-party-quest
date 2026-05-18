# Design — Ação "Mover etapa" do candidato (RH)

- **Data:** 2026-05-18
- **Revisão:** 2 — pivot para **Abordagem B** (coexistência: nova ação ao lado da `StateTransitionAction`, que é mantida)
- **Branch:** `feat/move-stage-action` (a partir de `develop`)
- **Módulos afetados:** `panel-organization`, `applications` (lang)
- **Issue relacionada (fora de escopo):** [#160](https://github.com/3pontos-tech/recruit-party-quest/issues/160) — bypass da rejeição

## 1. Problema

O RH (usuário admin do painel da organização) precisa avançar candidatos pelo pipeline. Hoje:

- O caminho íntegro (`StateTransitionAction` no Kanban) **obriga** preencher o `EvaluationForm` inteiro a cada movimento → fricção alta.
- Por isso o RH passou a usar a **página de edição** (`EditApplication` + `ApplicationForm`), que expõe `Select::make('status')` e `Select::make('current_stage_id')` e salva via **UPDATE cru do Eloquent** — sem validação de transição, sem `ApplicationStageHistory`, sem evento `ApplicationStatusChanged`. Bypass total da máquina de estados.

O comportamento do usuário é sintoma de design: o caminho correto é o mais penoso.

## 2. Objetivo / escopo

Entregar uma ação nova **`MoveStageAction`** ("Mover etapa") que:

- Move via **select** (sem drag-and-drop), **forward-only** (etapas com `display_order` maior que a atual).
- Passa **sempre** pela máquina de estados (`$record->current_step->handle()`): validação de `choices()`, `ApplicationStageHistory`, evento `ApplicationStatusChanged`, tudo em transação.
- Permite avaliação **opcional** via toggle; se ligado, roda `StoreEvaluationAction`.
- Aparece **ao lado** da `StateTransitionAction` na página do candidato (Quick Actions) **e** no Kanban (card action).

A `StateTransitionAction` existente é **mantida intacta** (continua com `EvaluationForm` obrigatório). E remover o vetor de abuso: a página de edição inteira.

### Decisão de abordagem (Revisão 2)

Adotada a **Abordagem B (coexistência)**: a nova ação não substitui nem altera a `StateTransitionAction`. Motivo: evitar mexer em código já em produção no Kanban e isolar a nova funcionalidade. Trade-off aceito: dois botões de transição coexistem no card do Kanban e na seção Quick Actions (um com avaliação obrigatória, outro com avaliação opcional).

### Fora de escopo (YAGNI)

- Drag-and-drop.
- Mover para trás.
- Novas roles/permissões (admin do painel org já tem acesso via `SuperAdmin`/`Admin`).
- Mudança de schema / migration.
- Conserto do bypass da rejeição → **issue #160**.
- Alterar/remover/refatorar a `StateTransitionAction` (mantida como está).
- Edição administrativa de campos legítimos (`source`, `cover_letter`, datas manuais de oferta/rejeição) — perdida com a remoção do edit page; aceito pelo dono do produto.

## 3. Arquitetura

```
  ┌───────────────────────────────────────────────────────────┐
  │  MoveStageAction  (NOVA, Filament\Actions\Action)           │
  │  panel-organization/.../Applications/Actions/MoveStageAction│
  └──────────────┬───────────────────────────┬────────────────┘
                 │ ADICIONADA ao lado da antiga│
   ┌─────────────▼───────────┐   ┌────────────▼──────────────┐   ┌─ REMOVIDO ────────┐
   │ ApplicationInfolist      │   │ KanbanStages              │   │ rota 'edit'       │
   │ Actions::make([          │   │ ->cardActions([           │   │ EditAction tabela │
   │   StateTransitionAction, │   │   StateTransitionAction,  │   │ EditApplication   │
   │   MoveStageAction,  ◄NOVA│   │   MoveStageAction,  ◄NOVA │   │ ApplicationForm   │
   │   Comment, Reject ])     │   │   Reject ])               │   │ canEdit()         │
   └──────────────────────────┘   └───────────────────────────┘   └───────────────────┘
                 │                                  (StateTransitionAction
                 ▼  ->action()                       PERMANECE intacta)
   TransitionData::fromArray($data, auth()->id())
                 ▼
   $record->current_step->handle($transitionData)   ← núcleo íntegro (sempre)
        ├─► valida choices()  → InvalidTransitionException (rollback)
        ├─► update current_stage_id / status
        ├─► ApplicationStageHistory (from→to, moved_by, notes, team_id)
        └─► ApplicationStatusChanged (só se status mudou; após commit)
                 ▼
   SE toggle "Registrar avaliação" ligado → StoreEvaluationAction (opcional)
```

`StateTransitionAction` **não é tocada**. A nova ação é independente e adicionada como item extra nas listas de ações.

## 4. Máquina de estados (preservada)

```
  [New] ─InReview─► [InReview] ─InProgress─► [InProgress] ─┐
                                                ▲   │      │ avança etapa
                                                └───┘      │ (display_order
                                         (mesmo status,    │  > atual)
                                          só muda stage)   ▼
                                                     [OfferExtended] ...

  Select to_status   = current_step->choices()  (já filtra o válido)
  Select to_stage_id = stages active, display_order > atual  (forward-only)
```

A ação espelha o filtro de status da `StateTransitionAction` (`Arr::except` removendo `OfferAccepted/OfferDeclined/Hired/Rejected/OfferExtended`) e delega a `handle()`.

**Nota de implementação (visibilidade do `to_stage_id`):** a `StateTransitionAction` atual usa `in_array($get('to_status'), [ApplicationStatusEnum::InProgress, ApplicationStatusEnum::OfferExtended])` — comparação de string vs instância de enum, potencialmente sempre `false` dependendo do `EnumStateCast`. A nova ação deve usar comparação robusta por `.value` com `in_array(..., true)` e ter um **teste de regressão de visibilidade**. Esse padrão suspeito na `StateTransitionAction` antiga não será corrigido aqui (fora de escopo) — apenas registrado.

## 5. Wireframe do modal (MoveStageAction)

```
  ┌─────────────────────────────────────────────┐
  │  Mover etapa — {candidato}                   │
  ├─────────────────────────────────────────────┤
  │  Status destino   [ In Progress        ▾ ]   │  ← choices() do estado atual
  │  Etapa destino    [ Entrevista Técnica ▾ ]   │  ← visível p/ InProgress/OfferExtended
  │  Observações      [___________________]      │  → notes (vai p/ StageHistory)
  │                                              │
  │  [ ] Registrar avaliação                     │  ← Toggle (default off)
  │  ┌─ (visível só com toggle on) ───────────┐  │
  │  │ EvaluationForm (rating, scores…)        │  │
  │  └─────────────────────────────────────────┘ │
  ├─────────────────────────────────────────────┤
  │              [ Cancelar ]  [ Confirmar ]     │  ← requiresConfirmation()
  └─────────────────────────────────────────────┘
```

`->action()`: monta `TransitionData::fromArray($data, auth()->id())`, chama `current_step->handle()`; em seguida, **se** o toggle estiver ligado, executa `StoreEvaluationAction` (mesmo payload da `StateTransitionAction`). Visibilidade da ação: só `SuperAdmin`/`Admin`; escondida/desabilitada quando `is_last_stage` ou `! current_step->canChange()`.

## 6. Mudanças por arquivo

| Arquivo                                                                                 | Mudança                                                                                                                               |
| --------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| `panel-organization/.../Applications/Actions/MoveStageAction.php`                       | **Novo.** Ação reutilizável; avaliação opcional via toggle; `getDefaultName()` → `move-stage-action`.                                 |
| `panel-organization/.../JobRequisitions/Pages/Kanban/Actions/StateTransitionAction.php` | **Inalterado.**                                                                                                                       |
| `panel-organization/.../Kanban/KanbanStages.php`                                        | **Adicionar** `MoveStageAction::make()->visible(role)` ao `->cardActions([...])`, ao lado da `StateTransitionAction` (que permanece). |
| `panel-organization/.../Applications/Schemas/ApplicationInfolist.php`                   | **Adicionar** `MoveStageAction::make()` ao `Actions::make([...])` (manter `StateTransitionAction::make()`).                           |
| `panel-organization/.../Applications/ApplicationResource.php`                           | Remover `canEdit()`; remover `'edit'` de `getPages()`; remover import de `EditApplication`.                                           |
| `panel-organization/.../Applications/Tables/ApplicationsTable.php`                      | Remover o `EditAction` de `recordActions()` e seu import.                                                                             |
| `panel-organization/.../Applications/Pages/EditApplication.php`                         | **Removido.**                                                                                                                         |
| `panel-organization/.../Applications/Schemas/ApplicationForm.php`                       | **Removido.**                                                                                                                         |
| `applications/lang/{en,pt_BR}/filament.php`                                             | Chaves i18n `actions.move_stage.*`.                                                                                                   |
| `panel-organization/tests/.../Application/OwnerApplicationAccessTest.php`               | Remover testes do edit page (`EditApplication`, `canEdit`, `assertTableActionDoesNotExist('edit')`).                                  |
| `panel-organization/tests/.../MoveStageActionTest.php`                                  | **Novo.** Edge-cases (inclui regressão de visibilidade e coexistência com `StateTransitionAction`).                                   |

## 7. Tratamento de erros e plano de testes

**Erros:** `InvalidTransitionException` / `MissingTransitionDataException` capturadas no `->action()` → `Notification::danger`; estado intacto (transação em `AbstractApplicationTransition::handle`).

**Edge-cases a cobrir em `MoveStageActionTest`:**

| Cenário                                       | Esperado                                                                                 |
| --------------------------------------------- | ---------------------------------------------------------------------------------------- |
| Mover só etapa (`InProgress` + `to_stage_id`) | `current_stage_id` muda, status mantém, evento **não** dispara                           |
| Status-alvo ilegal                            | `InvalidTransitionException` → notificação, rollback (estado intacto)                    |
| Mudança de status legítima                    | grava `StageHistory` + dispara evento                                                    |
| Toggle de avaliação ligado                    | `StoreEvaluationAction` roda **+** transição                                             |
| Toggle desligado                              | só transição, nenhum `Evaluation` criado                                                 |
| Visibilidade `to_stage_id`                    | campo visível quando `to_status = InProgress` (regressão do bug de enum/string)          |
| Coexistência                                  | `move-stage-action` **e** `state-transition-action` ambas existem na página do candidato |
| Não-admin (Owner)                             | ação escondida                                                                           |

O núcleo de domínio já está coberto por `InProgressTransitionTest` e `AbstractTransitionTest`.

## 8. Compatibilidade

- `StateTransitionAction` inalterada: Kanban continua funcionando exatamente como antes; a nova ação é adicional.
- Quem usava o edit page para mover status/etapa passa a usar `MoveStageAction` (caminho íntegro, avaliação opcional).
- Rejeição na página do candidato continua via `RejectApplicationAction` (inalterada; bypass tratado na issue #160).
- Sem migration: `current_stage_id` e `status` já existem e suportam a operação.
