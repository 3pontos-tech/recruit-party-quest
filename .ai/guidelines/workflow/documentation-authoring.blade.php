# Documentation Authoring

Conventions for where domain documentation lives and how it is structured. Docs are plain
Markdown co-located with the code — there is no docs portal or auto-discovery, so these
conventions are what keep documents findable.

## Where to save each document (co-location)

A document about **one module** lives inside that module; a **system-wide / cross-module**
document lives at the repo root.

```
app-modules/{module}/                  docs/            (system-wide / cross-module)
├── CONTEXT.md       (glossary)         ├── adr/
├── README.md        (entry point)      ├── specs/
└── docs/                               ├── plans/
    ├── adr/                            └── prd/
    ├── specs/
    ├── plans/
    └── prd/
```

- ADR numbering is **per module** (`{module}/docs/adr/0001-…`, `0002-…`), not global.
- Spec / Plan / PRD filenames are date-stamped: `AAAA-MM-DD-titulo.md` (PRDs may omit the date).
- Save a spec or plan under the **related module's** `docs/` (or root `docs/` if it spans modules).

## Skills that produce docs (brainstorm, grill-me, writing-plans)

These conventions **OVERRIDE** the superpowers skills' default save paths:

- `brainstorming` defaults specs to `docs/superpowers/specs/…`; `writing-plans` defaults
  plans to `docs/superpowers/plans/…`.
- In this repo, **do NOT save new docs to `docs/superpowers/`**. Save the spec/plan under
  the owning module's `docs/{specs,plans}/` (or root `docs/{specs,plans}/` if it spans
  modules), following the co-location rule above. ADRs → `app-modules/<module>/docs/adr/`.
- Pick the owning module before writing; if the document is genuinely cross-module, use the
  root `docs/`. Existing files already under `docs/superpowers/` stay as they are (legacy).

## Document types — purpose and boundaries

Each type has ONE owner — don't duplicate content across them.

| Type    | Lives in                               | Captures                                                                                  | Do NOT put here (→ owner)                                |
| ------- | -------------------------------------- | ----------------------------------------------------------------------------------------- | -------------------------------------------------------- |
| spec    | `…/docs/specs/AAAA-MM-DD-titulo.md`    | what & why: context, goals/non-goals, architecture, trade-offs                            | step-by-step checklist (→ plan); glossary (→ CONTEXT.md) |
| plan    | `…/docs/plans/AAAA-MM-DD-titulo.md`    | how: goal + ref to spec, tasks in phases as testable `- [ ]`                              | arch rationale (→ ADR); conceptual design (→ spec)       |
| prd     | `…/docs/prd/titulo.md`                 | product problem & solution: problem, solution, user stories, out-of-scope (usually born from an issue) | step-by-step impl (→ plan); technical trade-offs (→ ADR) |
| adr     | `app-modules/<module>/docs/adr/NNNN-…` | one decision + rationale + consequences (per-module numbering)                            | —                                                        |
| README  | `app-modules/<module>/README.md`       | entry point: overview, responsibilities, entry points, flows, how to test, links          | column/schema table (→ Model PHPDoc); glossary (→ CONTEXT.md); arch rationale (→ ADR) |
| CONTEXT | `app-modules/<module>/CONTEXT.md`      | glossary + module boundaries                                                              | how-to (→ README); decisions (→ ADR)                     |

The "column/schema table lives in the Model PHPDoc" boundary is the `model-phpdoc-sync`
guideline.

## Front-matter standard

Add a YAML front-matter block on new docs. All keys optional, but prefer them:

```yaml
---
type: spec            # spec | plan | adr | prd
title: "..."
module: nome-do-modulo
status: ...            # adr: accepted|superseded|… · plan: proposed|in_progress|completed
date: 2026-06-23
author: seu-handle-github
related:               # cross-links to other docs
  spec: nome-do-modulo/AAAA-MM-DD-titulo
---
```

## README vs CONTEXT (do not duplicate)

- `CONTEXT.md` = glossary + module boundaries (conceptual).
- `README.md` = practical entry point + roadmap (concrete), linking to CONTEXT/ADRs.
- A module README **must not** include a column/schema table (that lives in the Model PHPDoc
  — see `model-phpdoc-sync`), a glossary (that's CONTEXT), or architecture decisions with
  rationale (those become ADRs).

## Language

Write documentation in **pt_BR**. Existing English docs stay as-is.
