# Plan: Add needReinstall status to ApplicationInstallationStatus (issue #576)

## Context

Issue: https://github.com/bitrix24/b24phpsdk/issues/576

The user selected SDK v3. Base: origin/v3-dev at d49096a464ca23ea86524b816b8b41103d66ae82.
Branch: codex/576-need-reinstall-status.

The issue explicitly requests one enum case and its explanatory comment. A consumer's TTL-based cleanup worker marks installations that timed out waiting for ONAPPINSTALL. The installation flow does not set this status. Interface methods, events, repository operations, and the cleanup worker are separate work.

The official Bitrix24 MCP documentation confirms ONAPPINSTALL is an application-installation event. The timeout policy is the issue's consumer-side requirement, not a documented REST guarantee. No REST methods or payload contracts change.
Source: https://apidocs.bitrix24.ru/api-reference/chat-bots/events/on-app-install.html

`make oa-schema-build` succeeded in the primary checkout before planning. Refresh the isolated checkout's snapshot before implementation as well; keep unrelated refreshed schema content out of this enum-only PR.

## Design decision

Add the requested backed enum case, preserving the four existing names, values, and ordering. Append the new case after `blocked`. Expanding lifecycle logic would exceed issue #576; retaining only existing cases would not satisfy its acceptance criteria.

## Files to Create

Only this task plan. No new test file: asserting a literal enum declaration would duplicate the implementation. Use the existing application contract tests and full unit suite to detect regressions.

## Files to Modify

### 1. src/Application/Contracts/ApplicationInstallations/Entity/ApplicationInstallationStatus.php

Append inside the existing enum:

```php
    // Timed out waiting for ONAPPINSTALL; marked by a TTL-based cleanup worker and requires reinstallation.
    case needReinstall = 'needReinstall';
```

No methods, interfaces, events, or service builders change. SDK file generators do not apply to enums.

### 2. CHANGELOG.md

Under the existing `## Unreleased` heading add `### Added` and:

```markdown
- Added `ApplicationInstallationStatus::needReinstall` for installations that timed out waiting for `ONAPPINSTALL` and require reinstallation ([#576](https://github.com/bitrix24/b24phpsdk/issues/576))
```

No agent configuration or skill files change. No Makefile or phpunit.xml.dist changes are required.

## Deptrac compliance

The enum remains in its existing Application Contracts namespace and introduces no imports or dependencies.

## Execution

- [x] Read issue, refresh primary OpenAPI snapshot, inspect enum and existing application tests.
- [x] Confirm SDK v3, fetch origin/v3-dev, create an isolated checkout and branch.
- [x] Prepare and self-review this plan.
- [x] Obtain explicit approval of this plan as required by b24phpsdk-maintainer.
- [x] Prepare isolated Composer dependencies and refresh OpenAPI using the existing Make target without exposing webhook credentials.
- [x] Verify the baseline application contract tests with `make test-file path=tests/Unit/Application/Contracts/ApplicationInstallations`.
- [x] Add the enum case and comment exactly as specified above.
- [x] Run the quality gate in the order below; diagnose and resolve any change-related failures.
- [x] Add the CHANGELOG entry, inspect the complete diff, and run `git diff --check`.
- [ ] Commit only the intended enum, CHANGELOG, and task-plan changes.
- [ ] Read .github/PULL_REQUEST_TEMPLATE.md, push and create a PR targeting v3-dev with `Closes #576`.
- [ ] Attach the PR to this chat, wait for terminal CI results, and report the PR URL and actual validation results.

## Verification

Run sequentially:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
git diff --check
```

Expected: every command exits successfully. No live integration suite applies: the change adds only an Application contract enum value and has no REST interaction. Do not add tests for PHP's built-in enum behavior.

## Plan review

- Unambiguity: exact enum value, comment, insertion point, changelog text, branch, and commands are specified.
- Non-contradiction: the change remains enum-only and introduces no lifecycle behavior or API dependencies.
- No gaps: both acceptance criteria, changelog, local checks, PR creation, and terminal CI reporting are covered.

## Validation results

- OpenAPI refresh: passed in the isolated checkout. The refreshed schema remains local and is excluded from the PR.
- Baseline application contract tests: passed, 97 tests and 143 assertions.
- `make lint-cs-fixer`: passed on the first run, no files require formatting.
- `make lint-rector`: passed on the first run.
- `make lint-phpstan`: passed on the first run, no errors.
- `make lint-deptrac`: passed on the first run, zero violations, warnings, or errors; 22 pre-existing skipped violations.
- `make test-unit`: passed on the first run, 1273 tests and 3501 assertions.
- Independent read-only review: no actionable findings.

Status: implementation and local checks complete; remote delivery and CI are pending.
