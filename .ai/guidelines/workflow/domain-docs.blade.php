# Domain Docs

How to consume this repo's domain documentation when exploring the codebase. These docs are
plain Markdown files co-located with the code — there is no docs portal or auto-discovery here.

## Before exploring, read these (if they exist)

- **`app-modules/<module>/CONTEXT.md`** — the module glossary: vocabulary and boundaries for
  that module.
- **`app-modules/<module>/ARCHITECTURE.md`** — the module's structure, runtime flows, and key
  classes (the "how it's wired"), distinct from the `CONTEXT.md` glossary.
- **`app-modules/<module>/README.md`** — the practical entry point: responsibilities, key
  Actions/Models, how to navigate `src/`, how to test.
- **`app-modules/<module>/docs/adr/`** — ADRs are **module-scoped**: read the ones in the
  module you're about to work in. They're numbered per-module from `0001` and referenced
  anywhere as `module/NNNN` (e.g. `recruitment/0003`). There is no root `docs/adr/`.
- **Root `docs/`** — only for cross-cutting documents that span multiple modules (e.g.
  `ANALISE-DESACOPLAMENTO-MODULOS.md` at the repo root).

If any of these files don't exist, **proceed silently**. Don't flag their absence and don't
suggest creating them upfront — they are created lazily, when a term or decision actually
gets resolved (typically by `grill-with-docs`, `brainstorm`, or `grill-me`).

## File structure

```
/
└── app-modules/
    ├── recruitment/
    │   ├── CONTEXT.md                     <- module glossary
    │   ├── ARCHITECTURE.md                <- module structure & flows
    │   └── docs/adr/                      <- module-scoped decisions (recruitment/0001…)
    ├── applications/
    │   ├── CONTEXT.md
    │   └── docs/adr/                      <- applications/0001…
    └── ...
```

All ADRs live under their owning module. Cross-cutting decisions live with the module that
owns them.

## Use the glossary's vocabulary

When your output names a domain concept (an issue title, a refactor proposal, a hypothesis, a
test name), use the term as defined in that module's `CONTEXT.md`. Don't drift to synonyms the
glossary explicitly avoids.

If the concept you need isn't in the glossary yet, that's a signal — either you're inventing
language the project doesn't use (reconsider) or there's a real gap (note it).

## Flag ADR conflicts

If your output contradicts an existing ADR, surface it explicitly rather than silently
overriding:

> _Contradicts ADR recruitment/0001 — but worth reopening because..._
