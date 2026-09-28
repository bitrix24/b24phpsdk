# Plan: Implement stale installation reference behavior and tests (#580)

## Context and proposed design

Issue: https://github.com/bitrix24/b24phpsdk/issues/580

The required `make oa-schema-build` succeeded on 2026-09-29. This task changes
application-domain contracts and reference implementations, not REST endpoints.
REST method documentation, SDK result generators, and live portal tests do not apply.

Fresh `origin/v3-dev` at `e1ae4bf6a3bf0632ab98c4d67b78a28b1074adb8` has none of
the contracts required by #576–579. All four issues are open. The main checkout
contains unrelated `.mcp.json` changes and an existing `.tasks/577/plan.md` whose
proposed scope overlaps this work; preserve them and avoid parallel conflicting edits.

Approved release line: v3. The user approved the combined #576–580 scope on 2026-09-29. Use an isolated managed worktree
from the selected fresh base and branch `codex/580-stale-installations`.

Recommended approach: include the minimal prerequisite contracts from #576–579,
then implement #580 and test the complete behavior in a single coherent PR.
Alternative: wait for the prerequisite PRs and implement only #580 afterward.
The combined approach was explicitly approved by the user.

## Files to Create

### `src/Application/Contracts/ApplicationInstallations/Events/ApplicationInstallationMarkedNeedReinstallEvent.php`

Implement #578 using the namespace and imports of `ApplicationInstallationBlockedEvent`:
extend `Symfony\Contracts\EventDispatcher\Event`, with readonly constructor fields
`Uuid $applicationInstallationId`, `CarbonImmutable $timestamp`, and `?string $comment`.

## Files to Modify

### Prerequisite contracts in `src/Application/Contracts/ApplicationInstallations/`

- `Entity/ApplicationInstallationStatus.php`: add `case needReinstall = 'needReinstall'`
  with its timeout semantics (#576).
- `Entity/ApplicationInstallationInterface.php`: add
  `markAsNeedReinstall(?string $comment): void`, document new-only guard and SDK
  `LogicException`, and document direct uninstall from needReinstall (#577).
- `Repository/ApplicationInstallationRepositoryInterface.php`: add
  `findStaleInstallations(ApplicationInstallationStatus $status, CarbonImmutable $olderThan): array`
  with `ApplicationInstallationInterface[]` return PHPDoc, strict creation-date
  threshold, and ascending creation-date ordering (#579).

### `tests/Unit/Application/Contracts/ApplicationInstallations/Entity/ApplicationInstallationReferenceEntityImplementation.php`

- Implement `markAsNeedReinstall`: guard before any mutation, then update status,
  nullable comment, and updatedAt; record the event with that same timestamp.
- Implement the existing `AggregateRootEventsEmitterInterface` using the SDK's
  reference partner entity pattern: a typed event buffer and draining `emitEvents()`.
- Allow uninstall from needReinstall and update the rejection message accordingly.
- Preserve the existing documented new/active-only blocking contract: the present
  negative guard must not accidentally admit needReinstall after extending the enum.

### `tests/Unit/Application/Contracts/ApplicationInstallations/Repository/InMemoryApplicationInstallationRepositoryImplementation.php`

Implement `findStaleInstallations` by status AND `getCreatedAt() < $olderThan`, then
sort the matched entities by createdAt ascending. Return a numerically indexed array;
do not mutate repository storage or filter using updatedAt.

### `tests/Application/Contracts/ApplicationInstallations/Entity/ApplicationInstallationInterfaceTest.php`

Add reusable tests via the existing entity factory:

- new to needReinstall with null/non-empty comment, updatedAt, and preserved identity.
- Reject active, blocked, deleted, and needReinstall without state mutation.
- needReinstall to deleted through `applicationUninstalled(null)`.
- Preserve the existing blocking restrictions.

The reusable success test also verifies event payload and one-time emission through
the existing aggregate event-emitter contract. Require an event-capable entity from
the concrete factory for this test; do not silently skip the acceptance criterion.

### `tests/Application/Contracts/ApplicationInstallations/Repository/ApplicationInstallationRepositoryInterfaceTest.php`

Add reusable repository cases for matching status and time, no matches, strict
threshold equality exclusion, and ascending order independent of insertion order.
Use fixed Carbon test times around entity creation, restoring the clock in finally.
Persist through the existing repository and flusher contracts. Include a case where
updatedAt is recent but createdAt is old to distinguish the two fields.

### Existing concrete reference tests

Run new abstract tests through the existing entity and in-memory repository test
classes. Correct touched coverage attributes if necessary; no new PHPUnit suite or
Make target is required.

### `src/Application/Contracts/ApplicationInstallations/Docs/ApplicationInstallations.md`

Update the state diagram, method/event descriptions, and migration guidance for the
new entity and repository contracts.

### `CHANGELOG.md`

Under `## X.Y.Z Unreleased`, add an Added entry for stale-installation reference
behavior and reusable contract tests ending with a #580 link. If the combined scope
is approved, add distinct contract/event entries linked to #576–579 and state that
downstream interface implementations must add the new methods.

## Deptrac compliance

Reuse Application contracts, SDK Core exceptions, Carbon, Symfony Uid, and the
existing event contract. No new production layer dependency or exclusion is needed.

## Execution and verification

- [x] Read #580, dependencies #576–579, and current implementations.
- [x] Refresh OpenAPI and inspect fresh remote base.
- [x] Confirm release line and review this design, including prerequisite scope.
- [x] Create isolated worktree and refresh its schema without printing credentials.
      Worktree: `/Users/mesilov/.codex/worktrees/stale-installations/b24phpsdk`.
      Baseline installation suite: 97 tests, 143 assertions passed.
- [x] Add focused failing tests, verify expected failures, then implement behavior.
      RED: 107 tests, 153 assertions, 10 expected missing-method failures.
      GREEN after review: 107 tests, 239 assertions.
- [x] Run both concrete reference test files and all mandatory quality gates.
      Full unit suite: 1283 tests, 3597 assertions. CS Fixer, Rector, PHPStan,
      and Deptrac passed on their first run; no live integration suite applies.
      Spec review passed. Quality review passed after independent pre-query
      state snapshots replaced same-reference mutation assertions.

```bash
make test-file path=tests/Unit/Application/Contracts/ApplicationInstallations/Entity/ApplicationInstallationInterfaceReferenceImplementationTest.php
make test-file path=tests/Unit/Application/Contracts/ApplicationInstallations/Repository/InMemoryApplicationInstallationRepositoryImplementationTest.php
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
git diff --check
```

Live integration tests do not exercise these local reference implementations. Report
that limit explicitly; do not claim that all live SDK suites were run.

- [ ] Review exact diff, commit scoped files, read the current PR template, push,
      create and attach the PR against the confirmed base, and wait for terminal CI.
- [ ] Report PR URL, local checks, final CI, and downstream compatibility impact.
      Do not merge without user authorization.

## Design review

The plan covers every #580 acceptance criterion, dependency delivery, strict date
boundary, event observability, rejected-transition atomicity, and consumer impact.
No unrelated changes to the #577 plan, agent configuration, or live portal data.
