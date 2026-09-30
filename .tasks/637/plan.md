# Plan: Fix legacy API coverage calculation on v3-dev (#637)

## Status and scope

Approved by the user on 2026-09-30. Implemented with TDD and inline execution;
local and live verification complete. PR delivery and terminal CI follow.

- Issue: https://github.com/bitrix24/b24phpsdk/issues/637
- Branch: `codex/637-legacy-api-coverage`
- Base: `origin/v3-dev`, `21bc5b9221386794c82fb730adbe3608ac4d38f4`
- Worktree: `/Users/mesilov/.codex/worktrees/637-legacy-coverage/b24phpsdk`
- Goal: report actual legacy REST API method coverage for SDK v3 without exceeding 100%.
- No SDK service API changes, v3 OpenAPI calculator changes, or #410 CLI redesign.

## Context and verified evidence

The current Make target reports 1,172 portal methods, 1,282 SDK metadata entries,
and 109.39% coverage. The command divides independent counts instead of comparing
method identities. AttributesParser supplies metadata for both ApiVersion::v1
and ApiVersion::v3; it only supports an optional scope filter.

The same faulty approach appears in the per-scope table. The unsupported list
normalizes names but also mixes versions and does not deduplicate the portal list.
The command explicitly discovers only src/Services, although legacy Task wrappers
are declared in src/Legacy/Services with ApiEndpointMetadata defaulting to v1.

Official Bitrix24 MCP documentation was checked on 2026-09-30:

- https://apidocs.bitrix24.ru/api-reference/common/system/methods.html:
  `methods` returns result as string[], accepts optional `scope` and `full`.
  Without full=true the list is limited to the application's available methods.
- https://apidocs.bitrix24.ru/api-reference/common/system/method-get.html:
  `method.get` checks one method name and returns isExisting/isAvailable booleans;
  it is not a replacement for enumerating all available methods in this patch.

Keep the existing live `methods` requests. Label the report as coverage of the
queried portal's available legacy methods, not the entire Bitrix24 API catalogue.
The deprecation warning is pre-existing and not the cause of the arithmetic bug.

## Design alternatives and decision

1. Recommended: a small pure legacy coverage calculator and immutable result,
   reused by all three views in the command. It centralizes normalization,
   version filtering and set operations, and supports deterministic tests.
2. Inline array operations in each view: fewer new files, but repeats the logic
   that caused inconsistent summary and uncovered-list results.
3. Generalize the existing OpenAPI calculator: unnecessary coupling to OA
   normalization and risk to the v3 report, explicitly outside this issue.

## Calculation contract

For each report (overall or a selected scope):

1. P = sorted unique lowercase portal method names.
2. S = sorted unique lowercase SDK endpoint names whose apiVersion is v1;
   apply the SDK scope filter before calculating a scope report.
3. Covered = intersection(P, S).
4. Uncovered = difference(P, S).
5. SDK-only = difference(S, P).
6. Percentage = round(100 * count(Covered) / count(P), 2), or 0.0 for empty P.
7. Display the empty-set percentage with an explicit no-available-methods note.
8. Never clamp the percentage; correct set arithmetic guarantees the bound.
9. Do not remove a legacy method because a v3 method has the same name.

The overall summary comes from the overall portal set. Per-scope sums must be
labelled as sums over scopes, because scope memberships may overlap; never
present those sums as the unique global total.

The normal uncovered list includes deprecated portal methods, preserving current
semantics. Its additional non-deprecated view filters the normalized deprecated
method set out of Uncovered. It does not change the coverage denominator.

The batch-wrapper count remains a separate all-version inventory metric and must
be labelled explicitly as such, so it cannot be mistaken for matched v1 coverage.

## Files to create

### Calculator and result

`src/Infrastructure/Console/Commands/Documentation/LegacySdkCoverageCalculator.php`

Namespace: `Bitrix24\SDK\Infrastructure\Console\Commands\Documentation`.

Public contract:

```php
/** @param list<string> $portalMethods
 *  @param list<SupportedInSdkApiMethod> $sdkMethods
 */
public function calculate(array $portalMethods, array $sdkMethods): LegacySdkCoverageResult;
```

Private normalization helper accepts list<string> and returns sorted list<string>
using strtolower, array_unique, array_values, and sort. The calculator depends
only on SupportedInSdkApiMethod, ApiVersion and its result class, with no I/O.

`src/Infrastructure/Console/Commands/Documentation/LegacySdkCoverageResult.php`

A final readonly value object with constructor-promoted fields:

```php
public int $totalPortalMethods;
/** @var list<string> */ public array $coveredMethods;
/** @var list<string> */ public array $uncoveredMethods;
/** @var list<string> */ public array $sdkOnlyMethods;
public float $coveragePercentage;
```

### Regression tests

`tests/Unit/Infrastructure/Console/Commands/Documentation/LegacySdkCoverageCalculatorTest.php`

Use real calculator instances and explicit SupportedInSdkApiMethod fixtures:

| Test | Portal input / SDK metadata | Expected |
|---|---|---|
| Mixed versions | a.get, b.get / a.get v1, b.get v3, c.get v1 | 1 covered, b.get uncovered, c.get SDK-only, 50% |
| Duplicate wrappers and portal entries | A.GET, a.get / a.get v1 twice | total 1, covered 1, 100% |
| Case normalization | im.chat.setowner / im.chat.setOwner v1 | one match |
| Same name in both versions | tasks.task.get / v1 and v3 declarations | one legacy match |
| Only v3 declaration | tasks.task.get / v3 declaration only | zero legacy matches |
| Empty portal | [] / a.get v1 | 0.0%, SDK-only a.get, no exception |
| Empty SDK | a.get / [] | zero covered, one uncovered, 0.0% |
| Fractional rounding | a.get, b.get, c.get / a.get v1 | 33.33% |

`tests/Unit/Infrastructure/Console/Commands/Documentation/ShowCoverageStatisticsCommandTest.php`

Use Symfony CommandTester with explicit menu inputs and fake portal responses via
ServiceBuilderFactory/CoreInterface test doubles; use the real calculator.
Cover the summary, scope table, unsupported-list menu, empty portal, SDK-only
output, deprecated-method filtering and service discovery including legacy Task.
Verify v3-only names never count as covered in any legacy view. A fixture with
mixed-case deprecated names must yield consistent filtered output. Do not invoke
a live portal in unit tests.

## Files to modify

1. `src/Infrastructure/Console/Commands/Documentation/ShowCoverageStatisticsCommand.php`
   - Inject LegacySdkCoverageCalculator as an optional trailing constructor
     dependency with a default instance, keeping existing callers compatible.
   - Discover PHP service classes in src/Services and src/Legacy/Services using
     Finder; use the full namespace prefix length rather than magic number 12.
   - Pass metadata to the calculator for the global summary and scoped views.
   - Replace raw supported counts with count(coveredMethods); show uncovered
     and SDK-only counts separately and show SDK-only names for the selected scope.
   - Render scope columns: Scope, Portal methods, Covered, Uncovered, SDK-only,
     Coverage %. Handle empty sets explicitly.
   - Reuse result.uncoveredMethods for both uncovered-list views and normalize
     the deprecated list before subtraction.
   - Explain portal availability limits in help/output and label the batch count.
2. `CHANGELOG.md`, `## X.Y.Z Unreleased` -> `### Fixed`:
   - Fixed legacy REST API coverage on the SDK v3 line by matching unique v1
     methods against portal availability, including legacy services
     ([#637](https://github.com/bitrix24/b24phpsdk/issues/637)).

No new service, entity result item or generator-supported file is involved.
Existing unit_tests discovers tests/Unit recursively; no PHPUnit suite changes
are needed. Existing Make targets cover all validation; no Makefile changes.
No agent or skill configuration changes are planned.

## Deptrac compliance

New classes stay in Infrastructure alongside their sole consumer. Dependencies
use existing metadata types and Core/Contracts/ApiVersion. No Services-to-
Infrastructure dependencies or skip_violations additions are permitted.

## Execution and verification

- [x] Obtain explicit approval of this plan.
- [x] Established the clean worktree baseline: 1,558 tests, 4,781 assertions, exit 0; 11 existing deprecations (PHP 8.4.21 / PHPUnit 12.5.36).
- [x] Refreshed this worktree's schema with `make -s oa-schema-build` successfully; no tracked schema diff.
- [x] Write failing regressions; execute them and record the failing assertions.
- [x] Implement the calculator and command changes; rerun focused tests to green.
- [x] Review the diff against every acceptance criterion in #637.
- [x] Run light gates in order:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

- [x] Run focused existing OA calculator regressions:

```bash
make test-file path=tests/Unit/OpenApi/Domain/OaSdkCoverageCalculatorTest.php
```

- [x] Live read-only CLI verification replaces unrelated CRUD integration suites:

```bash
printf '0\n' | make -s sdk-coverage-v1-show
printf '1\n0\n' | make -s sdk-coverage-v1-show
make -s sdk-coverage-v3-show
```

Also exercise a legacy task scope in the uncovered menu using its actual menu
index. Confirm totals reconcile, percentages stay bounded, SDK-only entries are
separate, and the v3 report still returns the baseline for an unchanged schema
(98/314, 31.21% at initial observation). If live schema changes, compare v3 output
on base and patched code using the same new schema instead of forcing old counts.

- [x] Update CHANGELOG only after successful checks; inspect `git diff --check`
  and `git status --short`, including new files.
- [ ] Commit only issue files, read the current PR template, push and create a PR
  against v3-dev with `Closes #637` and concrete validation evidence.
- [ ] Attach the PR to this chat and monitor CI to a terminal state after every push.
- [ ] Report actual legacy coverage, v3 comparison, PR URL and terminal CI result.

## Plan review

- Unambiguity: version filter, denominator, case normalization, deduplication,
  empty sets, deprecation handling and scope totals have explicit contracts.
- Non-contradiction: one calculator feeds every legacy view; v3 remains independent;
  file paths, class names, test fixtures and constructor integration agree.
- No gaps: all issue acceptance criteria have regression cases and CLI checks;
  legacy discovery, changelog, linters, PR creation and CI completion are covered.

## Verification results (2026-09-30)

- RED: all five new CLI tests failed on the original implementation after fixing
  the test harness. Observed 133.33% in the duplicate/mixed-version fixture,
  division by zero, wrong scoped output and missing legacy class discovery.
  Nine calculator cases initially failed because the new calculator was absent.
- GREEN: documentation command tests: 25 tests / 95 assertions.
- Full unit suite: 1,572 tests / 4,856 assertions, exit 0, same 11 pre-existing
  deprecations as the 1,558-test baseline.
- PHP-CS-Fixer, PHPStan, Deptrac and license checks passed on first execution.
  Rector requested formatting/naming adjustments in the two new test files;
  applied only those changes and the full Rector rerun passed.
- Existing OA calculator: 2 tests / 11 assertions; no OA source or schema diff.
- Independent read-only code review: no actionable findings.
- Live legacy report: 1,160 unique portal methods; 731 covered, 429 uncovered,
  441 SDK-only; 63.02%. Scope table completed for all 69 configured scopes.
- Live task uncovered menu: 96 uncovered (also 96 excluding deprecated),
  42 SDK-only; consistent with the task scope table (125 / 29 / 96 / 42).
- Live v3 report unchanged: 314 OA methods, 98 covered, 216 uncovered,
  4 SDK-only, 31.21%.
- Existing `methods` service deprecation notices remain visible.
- Actual changelog heading is `## Unreleased`; added the issue-linked entry
  to its existing `### Fixed` section without introducing a second heading.
