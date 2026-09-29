# Repair PR #567 (issue #559)

## Context
The existing v1 PR targets dev and has twelve green historical CI checks, no review comments, and eight merge conflicts. Work starts from PR commit 18704614 and merges current origin/dev without rewriting contributor history. The existing API design is retained.

## Repair steps
1. Refresh OpenAPI using the current checkout tooling, redirecting generated output into this isolated worktree because v1 dev has no schema build command.
2. Resolve eight merge conflicts, preserving current dev catalog services, product types, tooling, and shared assertions alongside document services.
3. Keep document changelog entries in Unreleased. Preserve both sets of Make targets and valid, complete PHPUnit suites.
4. Align dedicated document annotation tests with repository metadata completeness/type conventions; keep CRUD tests separate.
5. Run CS Fixer, Rector, PHPStan, Deptrac when available, full unit tests, and document/element integration and annotation targets. Fix reproducible regressions.
6. Review final diff, commit and update existing PR branch, then await terminal CI. Do not merge PR.

## Scope
No new endpoint or generated result-item contract is planned. No generator is needed for mechanical conflict resolution. Existing layers/dependencies remain unchanged; do not add Deptrac suppressions.

## Findings during verification
- OpenAPI refresh succeeded using the primary checkout generator with isolated output; v1 has no generator or lint-deptrac Make target. The schema remains a local reference artifact.
- CS Fixer, Rector and PHPStan passed. Full unit suite: 678 tests, 1298 assertions.
- Both metadata annotation suites passed (4 tests, 26 assertions).
- Document integration tests failed before API calls because they unnecessarily requested OAuth ApplicationBridge credentials. Official catalog.document.add documentation includes webhook authentication; document-element CRUD already uses this path. Use the configured webhook consistently for the document suite and rerun it.

## Final local verification
- `make oa-schema-build`: passed with isolated output via current checkout tooling.
- `make lint-cs-fixer`, `make lint-rector`, `make lint-phpstan`, `make lint-allowed-licenses`: passed.
- `make test-unit`: 678 tests, 1298 assertions passed.
- `make test-integration-catalog-document-annotations`: 2 tests, 18 assertions passed.
- `make test-integration-catalog-document-element-annotations`: 2 tests, 8 assertions passed.
- `make test-integration-catalog-document-element`: 3 tests, 11 assertions passed.
- `make test-integration-catalog-document`: 7 tests, 13 assertions, 2 errors. Conduct/cancel scenarios are blocked by inventory accounting being disabled on the configured test portal; all other scenarios passed. Tests are retained and not skipped. Portal configuration was not changed.
- `lint-deptrac`: unavailable in this v1 branch; the Makefile fallback silently accepts unknown targets. Not counted as passed.
- `git diff origin/dev --check`: passed; PHPUnit XML parses and all four document suite paths exist. Unrelated duplicate suite names also exist on origin/dev.

## Delivery
Independent read-only review found no important repair regressions. Delivery route: preserve contributor history with a merge commit, update the existing contributor PR branch, then verify terminal CI. Live conduct/cancel validation still requires a test portal with inventory accounting enabled.
