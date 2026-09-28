# Plan: Add markAsNeedReinstall() to ApplicationInstallationInterface (#577)

## Context and proposed design

Issue: https://github.com/bitrix24/b24phpsdk/issues/577

This is an application-domain contract change, not a REST endpoint. No REST payloads,
result-item generators, service registrations, or live portal operations are involved.
The required `make oa-schema-build` succeeded on 2026-09-29.

Approved release line: v3, based on the issue's 3.6.0 milestone and the user's
approval of this proposal on 2026-09-29.
Branch: `feature/577-add-need-reinstall-method`, based on fresh `origin/v3-dev`.
Use an isolated managed worktree; preserve the primary checkout's `.mcp.json` changes
and the refreshed OpenAPI snapshot.

Dependency #576 is open, and fresh `origin/v3-dev` does not contain
`ApplicationInstallationStatus::needReinstall`. The recommended scope includes that
minimal prerequisite in this PR. An alternative is to wait for a separate #576 PR;
adding an interface declaration alone would leave the reference implementation unable
to perform the transition. The user approved including this prerequisite on 2026-09-29.

## Files to Create

No new production or test files. Extend the existing reusable contract test and its
reference implementation. Keep design and implementation decisions in this task folder.

## Files to Modify

### 1. `src/Application/Contracts/ApplicationInstallations/Entity/ApplicationInstallationStatus.php`

Add the prerequisite from #576, with a comment explaining the expired wait for ONAPPINSTALL:

```php
case needReinstall = 'needReinstall'; // installation timed out waiting for ONAPPINSTALL and must be reinstalled
```

### 2. `src/Application/Contracts/ApplicationInstallations/Entity/ApplicationInstallationInterface.php`

Add the following contract next to the existing status-changing methods:

```php
/**
 * Mark a stale installation as needing reinstall.
 *
 * Called by a background cleanup worker when the installation has been in status
 * «new» longer than a TTL and ONAPPINSTALL was never received.
 * Only the transition new → needReinstall is allowed.
 *
 * @param non-empty-string|null $comment
 * @throws LogicException if the current status is not «new»
 */
public function markAsNeedReinstall(?string $comment): void;
```

Add `needReinstall → deleted (direct transition, no blocked step)` to
`applicationUninstalled()` PHPDoc. Describe the new status in `getStatus()` PHPDoc.

### 3. `tests/Unit/Application/Contracts/ApplicationInstallations/Entity/ApplicationInstallationReferenceEntityImplementation.php`

Implement the new method with `#[\Override]`: check for status `new`, otherwise throw
the existing SDK `LogicException`; then set status `needReinstall`, store the nullable
comment, and update `updatedAt` using `CarbonImmutable`, following `markAsActive()`.

Keep uninstall available from `needReinstall` and include that status in its rejection
message. Preserve the existing `markAsBlocked()` contract by explicitly allowing only
`new` and `active`; its current negative check would otherwise admit the new enum case.

### 4. `tests/Application/Contracts/ApplicationInstallations/Entity/ApplicationInstallationInterfaceTest.php`

Extend the reusable contract tests via `createApplicationInstallationImplementation()`:

- New installation transitions to `needReinstall`; test both null and non-empty comments.
- The transition preserves identity/account association and updates `updatedAt`.
- Calls from active, blocked, deleted, and needReinstall throw SDK `LogicException`
  without mutating status, comment, or update time.
- Uninstall from needReinstall transitions directly to deleted, without blocking first.
- Blocking needReinstall throws, preserving the documented new/active-only contract.

Use deterministic timestamps when testing `updatedAt`; restore any Carbon test clock
in a `finally` block. Run these tests through the existing reference implementation test.

### 5. `CHANGELOG.md`

Under the current `Unreleased` section, add:

```markdown
### Added

- Added the `needReinstall` application installation status for installations that timed out waiting for `ONAPPINSTALL` ([#576](https://github.com/bitrix24/b24phpsdk/issues/576))
- Added `ApplicationInstallationInterface::markAsNeedReinstall()` and documented direct uninstall of stale installations; existing implementations must add the new method ([#577](https://github.com/bitrix24/b24phpsdk/issues/577))
```

## Deptrac compliance

No new layers or dependencies: the enum and interface stay in Application contracts,
and the reference implementation uses existing Core exceptions and Carbon imports.
No changes to dependency exclusions, Makefile, or PHPUnit suites are needed.

## Execution and verification

- [x] Obtain version selection and approval of this design, including prerequisite #576.
- [x] Create an isolated worktree from the selected fresh base; install dependencies and
      refresh its OpenAPI snapshot without logging webhook credentials.
- [x] Add failing contract tests and confirm the expected missing enum/method failures.
- [x] Add the enum case, interface contract, and reference implementation; run focused tests.
- [x] Run the quality gate in order:

```bash
make test-file path=tests/Unit/Application/Contracts/ApplicationInstallations/Entity/ApplicationInstallationInterfaceReferenceImplementationTest.php
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
git diff --check
```

Live integration tests are not applicable: this contract has no REST calls or persistent
production implementation in this repository. The full unit suite covers consumers and
the in-memory repository. Do not claim live integration coverage.

- [x] Update changelog and review the exact diff.
- [ ] Commit only task-scoped changes.
- [ ] Read the current PR template, push, create the PR against the approved base,
      attach it to this chat, and poll all CI checks to a terminal state.
- [ ] Report PR URL, test results, final CI state, and the consumer compatibility impact.
      Do not merge without user authorization.

## Plan review

- Unambiguity: exact contract, allowed transitions, exception type, nullable comment,
  affected files, and verification commands are specified.
- Non-contradiction: the enum prerequisite and reference implementation accompany the
  new interface method; existing blocked-state restrictions are preserved.
- No gaps: all #577 acceptance criteria, the #576 prerequisite, reusable tests,
  changelog, consumer compatibility, and PR/CI delivery are covered.

## Execution evidence

- Worktree: `/Users/mesilov/.codex/worktrees/577-need-reinstall/b24phpsdk`.
- Dependencies installed with `make -s composer-install`.
- Worktree schema refreshed successfully using the existing local webhook settings via
  `make -s oa-schema-build ENV_LOCAL=/Users/mesilov/work/Bitrix24/b24phpsdk/tests/.env.local`.
  The first attempt had no worktree-local webhook; no credentials were copied into Git.
- Focused baseline: 54 tests, 100 assertions; PHP 8.4.21, PHPUnit 12.5.36.

- Initial RED: 62 tests, 103 assertions, 8 errors (missing `markAsNeedReinstall()`).
- Guard regression RED: 62 tests, 130 assertions, 1 failure (blocking needReinstall).
- Focused GREEN: 62 tests, 132 assertions.
- Full unit suite: 1281 tests, 3533 assertions.
- CS Fixer, Rector, PHPStan, Deptrac, and dependency license checker: exit 0.
- Independent specification review: compliant after retaining both existing uninstall paths.
- The refreshed OpenAPI snapshot contains unrelated remote API changes; it remains local
  and is excluded from this contract-only PR.
- Independent code-quality review: approved, no actionable findings.
