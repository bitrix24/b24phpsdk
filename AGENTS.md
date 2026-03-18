# AGENTS.md

## Project Memory
- Use this file for repository-level working rules that should be committed and shared with other agents.
- Do not rely on machine-local memory as the primary source of project conventions when the rule can live in the repository.

## Task Plans
- Store task plans in the `.tasks` directory.
- Map task numbers directly to the GitHub issue ID in this repository.
- The canonical path for a task plan is `.tasks/<issue-id>/plan.md`.
- When creating a new task plan, prefer the canonical path over ad hoc files like `.tasks/plan-<issue-id>.md`.
