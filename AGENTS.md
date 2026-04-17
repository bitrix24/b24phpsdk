# Agent Rules

Rules that apply to all agent/skill work in this repository.

---

## CHANGELOG updates

**Rule**: every time a skill or agent file is updated (created, modified, or deleted),
add an entry to `CHANGELOG.md` under `## X.Y.Z Unreleased` → `### Changed`:

```markdown
### Changed
- Updated `b24phpsdk-maintainer` skill: <one-line description of what changed>
```

This applies to:
- `.claude/skills/**/*.md`
- `.claude/agents.md`
- Any other agent configuration file

Do not skip this step even for small edits.

## Issue language

- Every GitHub issue in `bitrix24/b24phpsdk` — title, body, and checklists — MUST be written in **English only**, regardless of the language used in conversation.
- Applies to creating new issues, updating existing issue bodies or titles, and posting issue comments.
- Translate content to English before writing to GitHub; do not mix languages inside a single issue. Proper nouns (method names, file paths, URLs) stay as-is.

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
