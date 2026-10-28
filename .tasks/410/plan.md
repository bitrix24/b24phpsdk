# Plan: Add non-interactive legacy coverage reporting (issue #410)

## Context

[Issue #410](https://github.com/bitrix24/b24phpsdk/issues/410), SDK 3.8.0; base `v3-dev`.
`ShowCoverageStatisticsCommand` computes legacy coverage correctly through `LegacySdkCoverageCalculator`, then enters an interactive menu. Preserve current scope membership, lowercase/deduplication, deprecation filtering and SDK-only inventory semantics. Existing command tests pass as part of the 12-test baseline.
This command uses the legacy `methods` endpoint with optional `scope`; do not migrate to `method.get` because it does not replace method enumeration. REST API coverage is distinct from PHPUnit code coverage and REST v3 OA coverage.

## CLI contract

```text
--show=scope-stat
--show=uncovered --scope=crm
--format=table|json                 (default table)
```

No `--show` preserves the interactive menu on interactive input. With `--no-interaction`, require `--show` and fail fast otherwise. `uncovered` requires a valid known scope; unknown show/format/scope values fail before portal access. Scope is optional for `scope-stat`; when supplied, restrict that report to that scope. Supplying format or scope without show is an invalid combination rather than silently ignoring it.

JSON stdout is one JSON document, without splash, progress, menu, ANSI decoration or stack trace. Return 0 for success; use the standard invalid-input code for invalid options and failure code for runtime/portal failures. Send errors to stderr when available and never echo the webhook or credential-bearing trace. The table mode may retain the human summary and inventory notes.

## JSON schema

Common fields: `apiVersion: "v1"`, `basis: "configured-portal"`, `show`, and `summary` containing `totalPortalMethods`, `coveredMethodsCount`, `uncoveredMethodsCount`, `sdkOnlyMethodsCount`, `coveragePercentage`.

`scope-stat` adds `scopes`, an array with `scope` and the same five numeric fields. Scope sums are not global unique totals; state this distinction in the docs.

`uncovered` adds `scope`, `uncoveredMethods` (deprecated names excluded), `uncoveredMethodsIncludingDeprecated`, and `sdkOnlyMethods`, each a sorted normalized list. Empty coverage has numeric percentage zero and empty lists as appropriate.

## Files to Create

- `docs/EN/coverage-reporting.md`: option reference, interactive/non-interactive examples, JSON schema, exit codes and portal-specific baseline explanation. Use placeholder webhook values only.
- `tests/Integration/Infrastructure/Console/ShowCoverageStatisticsCommandTest.php`: run the real command read-only against the configured test portal using CommandTester with non-interactive input; verify valid JSON, scope report consistency and successful no-TTY execution. Add no entity fixtures.

## Files to Modify

- `src/Infrastructure/Console/Commands/Documentation/ShowCoverageStatisticsCommand.php`: register the three flags, validate before initialization, extract shared scope/uncovered report calculations, dispatch flags once before entering the unchanged menu, and add separate table/JSON render paths. Keep the existing calculator and constructor injection.
- `tests/Unit/Infrastructure/Console/Commands/Documentation/ShowCoverageStatisticsCommandTest.php`: let `runCommand` accept additional arguments and an interaction setting. Add scope-stat table/JSON tests, uncovered JSON/deprecation tests, validation failures, empty portal/scopes, stdout cleanliness, no-input execution, and a factory expectation proving invalid options cause no API call. Retain all existing menu tests.
- `Makefile`: change `sdk-coverage-v1-show` to `docker compose run --rm -T ... --show=scope-stat $(ARGS)`, preserving existing environment loading. Add `test-integration-coverage-command` for the precise new integration test file.
- `docs/EN/README.md`: link the new option reference.
- `.claude/skills/b24phpsdk-maintainer/SKILL.md`: document `make -s sdk-coverage-v1-show`, direct `docker compose run --rm --no-TTY ... --show=... --format=json` syntax, and replace the obsolete piped-menu release-measurement example. Keep v1 and v3 reporting distinct and webhook values private.
- `CHANGELOG.md`: Added entry for the command flags and Changed entry for the maintainer skill, both under Unreleased and linked to #410, following AGENTS.md.

No SDK generator applies to console commands, docs or tests. Do not regenerate service entities or introduce new API wrappers. Context7 tools are not currently available in this session; use installed Console sources and official Symfony docs if implementation requires version-specific Console behavior.

## Steps

- [x] Add no-input CommandTester regression tests and observe missing-option failure.
- [x] Add validation and a once-only non-interactive dispatch using the existing calculator.
- [x] Share report collection between menu and flags, then implement strict JSON and table output.
- [x] Verify old menu tests and all new cases, including no unexpected portal calls for bad input.
- [x] Add docs, Make flags, integration target and maintainer-skill updates.
- [x] Run the five light gates, then the dedicated read-only live integration test and `make -s sdk-coverage-v1-show`.
- [x] Add both changelog entries, commit and prepare PR.
- [ ] After merge, assess a separate SDK 1.x tooling backport.

## Deptrac compliance

Use existing Infrastructure-to-Services/Core dependencies. Report data remains in the command's existing namespace. No Core-to-Console dependency or new deptrac suppression.

## Verification

```bash
docker compose run --rm -T php-cli vendor/bin/phpunit tests/Unit/Infrastructure/Console/Commands/Documentation/ShowCoverageStatisticsCommandTest.php tests/Unit/Infrastructure/Console/Commands/Documentation/LegacySdkCoverageCalculatorTest.php
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-coverage-command
make -s sdk-coverage-v1-show
```

For raw JSON, use direct `docker compose run --rm --no-TTY` with `--no-interaction --show=uncovered --scope=crm --format=json`; parse stdout with a JSON parser and keep the webhook out of recorded logs. Make's normal echo must never be treated as part of the JSON document.

## Implementation verification outcome

The command/calculator unit suites, live JSON report consistency test and non-TTY Make coverage target pass. The legacy menu remains covered. All shared light gates are run on the combined candidate; PR CI verifies each isolated branch. Broader scope-catalog readiness is recorded separately and PRs remain drafts.
