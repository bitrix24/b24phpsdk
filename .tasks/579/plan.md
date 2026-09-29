# Plan: Validate findStaleInstallations() (issue #579)

## Context

- Issue: https://github.com/bitrix24/b24phpsdk/issues/579
- Target confirmed by the user: SDK v3; PR base `v3-dev`.
- Branch: `codex/579-stale-installations`.
- The user approved the original interface, reference implementation, regression tests, changelog and PR plan.
- While this PR was being validated, PR #622 merged the same interface method and reference implementation into `v3-dev` via issue #580. The final scope therefore retains the accepted implementation and extends its regression coverage.
- This is an application persistence contract, not a Bitrix24 REST endpoint. Service generators and live API integration tests do not apply.

## Required contract

```php
/** @return ApplicationInstallationInterface[] */
public function findStaleInstallations(
    ApplicationInstallationStatus $status,
    CarbonImmutable $olderThan
): array;
```

Match the requested status AND `createdAt < olderThan`. Return matches ordered by `createdAt ASC`, or an empty array. The threshold is exclusive and applies to creation time, not update time or time spent in the current status. Downstream repositories must implement the new method; the accepted changelog and migration documentation already disclose this.

## Final files

### Create `tests/Unit/Application/Contracts/ApplicationInstallations/Repository/StaleApplicationInstallationRepositoryTest.php`

Use installation stubs with immutable timestamps and the real in-memory repository. The existing reference entity sets creation time internally, so stubs allow deterministic fixtures without changing entity constructors or global clocks.

- Exercise every enum status through a provider, including `needReinstall` and `deleted`.
- Save matching and excluded installations in non-chronological order.
- Exclude the wrong status, exact-cutoff timestamps, equivalent instants in different time zones, and newer installations.
- Assert an oldest-first zero-indexed result independent of `updatedAt`.
- Check empty and populated repositories with no matches.
- Verify matching and excluded installations remain retrievable after the query.

### Modify `CHANGELOG.md`

Record expanded regression coverage under `Unreleased` / `Changed` with issue #579 link. Preserve the already merged method/compatibility entry.

### Preserve accepted production code and shared contract tests

Keep `ApplicationInstallationRepositoryInterface`, `InMemoryApplicationInstallationRepositoryImplementation`, and `ApplicationInstallationRepositoryInterfaceTest` identical to the merged `v3-dev` versions. Do not duplicate methods or existing shared tests.

## Deptrac compliance

No production dependency changes remain in this PR.

## Verification and delivery

- [x] Refresh OpenAPI via `make -s oa-schema-build`. Unrelated schema drift stays local and outside the commit.
- [x] Original RED: six new cases failed because the method did not exist; GREEN: 49 focused tests, 61 assertions.
- [x] Original quality gate: CS Fixer, Rector, PHPStan, Deptrac and 1279 unit tests (3519 assertions) passed.
- [x] Read-only review found no actionable code issues.
- [x] Merge current `origin/v3-dev` and retain its already delivered implementation.
- [x] Run focused repository tests on the merged tree: 51 tests, 103 assertions.
- [x] Run `make lint-cs-fixer`, `make lint-rector`, `make lint-phpstan`, `make lint-deptrac`, and `make test-unit` sequentially: all passed; 1297 tests, 3646 assertions.
- [x] Check `git diff --check` and independently review the final diff against `origin/v3-dev`: no findings; only the new test, plan and changelog differ.
- [ ] Update PR #624 title/body to the final regression-test scope, push and monitor all CI checks to completion. Do not merge automatically.

Live API integration is not applicable. Shared contract tests execute against the in-memory repository; downstream database adapters must validate them independently.
