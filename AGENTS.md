# AGENTS.md

## Project Memory
- Use this file for repository-level working rules that should be committed and shared with other agents.
- Do not rely on machine-local memory as the primary source of project conventions when the rule can live in the repository.

## Task Plans
- Store task plans in the `.tasks` directory.
- Map task numbers directly to the GitHub issue ID in this repository.
- The canonical path for a task plan is `.tasks/<issue-id>/plan.md`.
- When creating a new task plan, prefer the canonical path over ad hoc files like `.tasks/plan-<issue-id>.md`.

## OpenAPI Schema
- Before implementing any task, refresh the local OpenAPI schema snapshot with `make oa-schema-build`.
- Treat `docs/open-api/openapi.json` as the repository baseline for current REST API research, implementation, and release-time verification.

## Coverage Tooling
- Inspect method coverage through the project CLI utilities exposed in `Makefile`, not by ad hoc manual counting.
- For SDK/live API coverage use the `make` targets that wrap the console commands.
- For OA-schema-based coverage use the dedicated `make` target once it is available in the repository.

## Testing Conventions
- If a service exposes entity-returning `get` and/or `list` methods, add a separate integration test file dedicated to result-item phpdoc annotation validation.
- That dedicated test file must verify both contracts against live field metadata:
  - annotation completeness: all system fields returned by `fields()->getFieldsDescription()` are present in the result-item annotations
  - annotation type validity: annotated field types match Bitrix24 field types using the shared custom assertions
- Prefer one dedicated annotation test file per result-item class, separate from CRUD/use-case tests.
- Naming convention for these files/classes: suffix them with `AnnotationsTest`, for example `TaskItemResultAnnotationsTest.php`.
- Naming convention for test methods inside those files: keep the explicit prefixes `testAllSystemFieldsAnnotated` and `testAllSystemFieldsHasValidTypeAnnotation`.
