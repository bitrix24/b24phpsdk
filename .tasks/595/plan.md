# Plan: Refactor Rector tooling for current stable releases (#595)

## Context and design

Target origin/v3-dev: the issue follows the SDK 3.5.0 maintenance pin and has milestone 3.6.0. No REST endpoint changes; API documentation and SDK generators do not apply. The required OpenAPI refresh completed successfully in the original checkout; its snapshot is copied here for reference only.

The old rector.php uses PHPUnitSetList::PHPUNIT_110, removed in newer Rector. Prefer RectorConfig::withComposerBased(phpunit: true), documented at https://getrector.com/documentation/composer-based-sets, to select rules for the installed PHPUnit version. Keeping exact tooling pins would not meet the issue. Replacing the old constant with another versioned constant would repeat the maintenance problem.

## Files to create

Only this task plan and local verification logs. No new application services, result classes, test suites, or generator output.

## Files to modify

- composer.json: relax Rector and PHPStan to caret constraints using the current stable compatible 2.x versions verified by a clean install.
- rector.php: remove PHPUnitSetList import and PHPUNIT_110; add ->withComposerBased(phpunit: true). Preserve PHP 8.4 target and existing checks. Review diagnostics from newly enabled rules and address necessary compatibility changes without disabling checks broadly.
- CHANGELOG.md: add Changed entry under Unreleased documenting Composer-based PHPUnit sets and current stable Rector/PHPStan tooling, ending with issue #595 link.
- Source/test files only if real new diagnostics require targeted behavior-preserving fixes; record those below.

## Steps and verification

- [x] Read issue, inspect baseline configuration, refresh schema, isolate fresh v3-dev.
- [x] Resolve current versions from Composer metadata and install in an empty vendor directory without a lock file.
- [x] Reproduce the removed-constant failure with updated tooling before changing rector.php (RED).
- [x] Migrate config and run make lint-rector (GREEN).
- [x] Run make lint-cs-fixer, make lint-rector, make lint-phpstan, make lint-deptrac, make test-unit, make lint-allowed-licenses, composer validate --strict, git diff --check.
- [ ] Review full patch, update changelog, commit scoped files, push and create template-based PR targeting v3-dev. Poll CI to a terminal result and report it.

Integration tests: no REST behavior changes or new scope suite; not applicable unless scope changes.
Deptrac: no new architecture dependencies are planned; validate using the existing gate.
Composer lock and vendor are ignored in this library and must stay untracked. The refreshed OpenAPI snapshot is not part of this tooling PR.

## Plan review

- Unambiguity: named configuration API, target branch, files, and verification commands.
- Non-contradiction: installed PHPUnit drives migration rules; supported PHP target remains 8.4.
- No gaps: fresh installation, both requested linters, full local gate, changelog, PR and CI are covered.

## Compatibility diagnostics

- Fresh install resolved Rector 2.6.7, PHPStan 2.2.16, PHPUnit 12.5.36. Old config reproduced the removed PHPUnit constant failure.
- Remove obsolete DeprecatedAnnotationToDeprecatedAttributeRector skip (no longer registered).
- Apply IfToNullCoalescingAssignRector to five CatalogServiceBuilder cache initializers; preserves lazy initialization and follows neighboring methods.
- Composer-based PHPUnit rules annotate three already-skipped integration test methods as non-asserting. Exclude only V3BuilderCoverageAuditorTest.php from AddDoesNotPerformAssertionToNonAssertingTestRector because its assertions run in data-provider callbacks; applying the attribute there would falsely mark passing tests risky.

## Local verification results

- Baseline Rector 2.5.2: passed.
- Fresh make composer-install with no lock/vendor: passed; Rector 2.6.7, PHPStan 2.2.16, PHPUnit 12.5.36.
- RED: make lint-rector with old config failed on undefined PHPUnitSetList::PHPUNIT_110.
- GREEN: migrated config and reviewed compatibility fixes pass make lint-cs-fixer, make lint-rector, make lint-phpstan (2555 files), make lint-deptrac (0 violations, 0 warnings, 0 errors), and make test-unit (1400 tests, 4032 assertions).

- Composer validate --strict, allowed licenses, and git diff --check: passed.
- Independent read-only code review: no actionable findings.
- Refreshed OpenAPI snapshot excluded from the patch; original checkout changes preserved.
