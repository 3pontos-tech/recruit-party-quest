# Issue tracker: GitHub

Issues and PRDs for this repo live as GitHub issues on `3pontos-tech/recruit-party-quest`.
Use the `gh` CLI for all operations — pass `--json` for structured output and `--jq` for
filtering.

## Conventions

- **Create an issue**: `gh issue create --title "..." --body "..."`. Use a heredoc for
  multi-line bodies.
- **Read an issue**: `gh issue view <number> --comments`.
- **List issues**: `gh issue list --state open --json number,title,labels --jq '[.[] | {number, title, labels: [.labels[].name]}]'`
  with appropriate `--label` and `--state` filters.
- **Comment on an issue**: `gh issue comment <number> --body "..."`
- **Apply / remove labels**: `gh issue edit <number> --add-label "..."` / `--remove-label "..."`
- **Close**: `gh issue close <number> --comment "..."`

Infer the repo from `git remote -v` — `gh` does this automatically when run inside a clone.

## When a skill says "publish to the issue tracker"

Create a GitHub issue.

## When a skill says "fetch the relevant ticket"

Run `gh issue view <number> --comments`.

See the `triage-labels` guideline for the label and title conventions.
