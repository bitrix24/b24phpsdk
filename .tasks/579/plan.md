# Plan: Add findStaleInstallations() (issue #579)

## Context

- Issue: https://github.com/bitrix24/b24phpsdk/issues/579
- Target confirmed by the user: SDK v3; PR base `v3-dev`.
- Branch: `codex/579-stale-installations`, based on refreshed `origin/v3-dev`.
- Status: implementation complete; local quality gates passed; PR publication and CI verification in progress.
- `make oa-schema-build` passed in the primary checkout before research. Refresh the snapshot in this worktree after dependency setup, using silent Make invocation to avoid printing credentials.
- This is an application persistence contract, not a Bitrix24 REST endpoint. REST documentation, service generators, live field annotation tests, and a new integration suite do not apply.
- Exact semantics: match the requested status AND `createdAt < olderThan`; return matches ordered by `createdAt ASC`, or an empty array. The threshold is exclusive. Do not use `updatedAt` or infer time spent in the current status.
- Adding an interface method requires downstream implementations to implement it. Explicitly state this compatibility impact in PHPDoc/changelog and the PR.

## Design and alternatives

1. Recommended: extend the existing interface as requested, update the in-memory reference repository, and add reusable contract tests plus deterministic reference tests. This follows the issue and existing SDK structure.
2. A separate optional capability interface would reduce the compatibility impact, but would not satisfy the requested public interface contract.
3. A worker-side scan/filter would avoid the interface change, but would move persistence filtering into consumers and would not satisfy the issue.

No database implementation or cleanup worker is included: production repositories live in consumer applications.

## Files to Create

### `tests/Unit/Application/Contracts/ApplicationInstallations/Repository/StaleApplicationInstallationRepositoryTest.php`

Namespace: `Bitrix24\SDK\Tests\Unit\Application\Contracts\ApplicationInstallations\Repository`.
Use PHPUnit `TestCase`, `CoversClass`, `Test`, the existing in-memory repositories, `NullLogger`, `CarbonImmutable`, `ApplicationInstallationInterface`, and `ApplicationInstallationStatus`.

Use installation stubs with fixed IDs, statuses, `createdAt`, and deliberately different `updatedAt`. Store them in non-chronological order. Test:

- `testFindStaleInstallationsFiltersByStatusAndExclusiveCreatedAtThreshold(): void`: include old matching installations; exclude a different status, an exact-threshold timestamp, and a newer timestamp; assert chronological IDs and valid item instances.
- `testFindStaleInstallationsReturnsEmptyArrayWhenNothingMatches(): void`.
- Cover every enum case with a provider so no status is implicitly excluded, including deleted installations.
- Include equal instants in different time zones in the boundary dataset; tie ordering between equal creation timestamps is not specified.

## Files to Modify

### `src/Application/Contracts/ApplicationInstallations/Repository/ApplicationInstallationRepositoryInterface.php`

Add imports for `ApplicationInstallationStatus` and `CarbonImmutable`, and this declaration:

```php
/**
 * Find installations with the given status created strictly before the threshold.
 *
 * Results are ordered by creation time ascending. Returns an empty array when none match.
 * The threshold applies to creation time, not time spent in the current status.
 *
 * @return ApplicationInstallationInterface[]
 */
public function findStaleInstallations(
    ApplicationInstallationStatus $status,
    CarbonImmutable $olderThan
): array;
```

### `tests/Unit/Application/Contracts/ApplicationInstallations/Repository/InMemoryApplicationInstallationRepositoryImplementation.php`

Add the matching public method with `#[\Override]` and `CarbonImmutable` import. Filter existing items by enum identity and creation instant, sort ascending with the immutable dates, and return a zero-indexed array. Do not alter stored state.

### `tests/Application/Contracts/ApplicationInstallations/Repository/ApplicationInstallationRepositoryInterfaceTest.php`

Add reusable contract checks using the existing repository/entity factory and flusher hooks, without changing abstract factory signatures:

- Save a new installation, flush, and check exclusion at its actual creation timestamp and inclusion one second after it.
- Query with a different status and assert the saved installation is excluded.
- Check returned elements implement the entity interface, meet the requested predicate, and are ordered ascending.

Use actual `getCreatedAt()` values and UUID comparisons rather than object identity, wall-clock sleeps, or assumptions that downstream repositories share a global Carbon test clock. Explicit non-chronological fixtures and empty-storage behavior are tested deterministically by the reference tests above.

### `CHANGELOG.md`

Add under the top `Unreleased` section, creating `### Added` if needed:

- Added `ApplicationInstallationRepositoryInterface::findStaleInstallations()` to find installations by status and exclusive creation-time threshold in oldest-first order; existing repository implementations must add this method ([#579](https://github.com/bitrix24/b24phpsdk/issues/579))

No changes to service builders, Makefile, or phpunit.xml.dist: existing unit suite discovers these tests.

## Deptrac compliance

Production changes stay in the Application layer and depend on its existing entity contract/status plus external Carbon. No new cross-layer dependency or skip_violations entry is needed.

## Execution and Verification

1. Set up worktree dependencies using repository Docker/Make conventions. Preserve unrelated changes in the primary checkout.
2. Refresh this worktree's schema with `make -s oa-schema-build`; inspect schema drift separately and do not include unrelated snapshot changes in the PR.
3. Run the existing repository tests as a baseline.
4. RED: add behavior tests and run `make test-file path=tests/Unit/Application/Contracts/ApplicationInstallations/Repository`; confirm failure because the new method is absent.
5. GREEN: add the interface declaration and reference implementation; rerun the focused tests.
6. Run the required phase-1 gate sequentially:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

7. Phase 2: not applicable; no REST service or database adapter changes. Shared persistence contract tests execute against the reference repository in the unit suite; real database implementations must validate this contract downstream.
8. Update CHANGELOG, run `git diff --check`, review exact diff and test evidence, and commit only issue-related files.
9. Read the current PR template, push, create the PR against `v3-dev`, attach it to this chat, and monitor CI until terminal. Report actual results and any remaining failure. Do not merge automatically.

## Plan review

- Unambiguity: exact signature, strict timestamp boundary, status filtering, ordering, and empty result are defined.
- Non-contradiction: interface and reference implementation use identical types; no new abstract factory arguments or unnecessary REST integration requirements.
- No gaps: contract change, the sole concrete implementation in this repository, reusable checks, deterministic edge cases, changelog, quality gates, and PR/CI delivery are covered.

## Verification evidence

- Worktree schema refresh: passed. Schema contains unrelated remote API drift; kept locally and excluded from issue commit.
- Baseline repository suite: 43 tests, 43 assertions.
- RED: 6 new cases failed with undefined `findStaleInstallations()`.
- GREEN: 49 repository tests, 61 assertions.
- First required gate: CS Fixer, Rector, PHPStan, Deptrac and full unit suite all passed; 1279 tests, 3519 assertions.
- Independent read-only review: no actionable code findings; compatibility disclosure added to CHANGELOG and planned PR body.
- Additional explicit-path style check found missing trailing newlines in two pre-existing files; corrected. Initial explicit-path invocation required `--config=.php-cs-fixer.php`.
