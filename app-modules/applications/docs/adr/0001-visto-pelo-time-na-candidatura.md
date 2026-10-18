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
