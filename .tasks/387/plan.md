# Issue #387 Plan

## Summary
- Fix `InMemoryApplicationInstallationRepositoryImplementation::findByBitrix24AccountMemberId()` so it can resolve pending installs for master accounts in `new`, while staying compatible with existing `active` lookups.
- Define the lookup against non-deleted master accounts only: allowed account statuses are `new`, `active`, and `blocked`; `deleted` is always excluded.
- Return only non-deleted installations. Selection must be deterministic and based on an explicit priority rule, not repository insertion order leaking by accident.
- Explicitly document the account lifecycle used in this task: happy path is `new -> active -> deleted`, and `blocked` is a side branch reachable from both `new` and `active`.
- Update `CHANGELOG.md` to record the repository behavior fix for issue `#387`.

## Implementation Changes
- Replace the current single repository call that hard-codes `Bitrix24AccountStatus::active` with a local candidate-selection flow inside `InMemoryApplicationInstallationRepositoryImplementation`.
- Load master accounts for the `memberId` separately for these statuses and preserve this priority order: `active`, then `new`, then `blocked`.
- For each candidate account in that priority order, look up linked installations in the in-memory installation store and skip any installation whose status is `deleted`.
- Return the first non-deleted installation found for the highest-priority matching account status.
- If an `active` account exists but its linked installation is `deleted`, continue scanning lower-priority candidates and return a `new` or `blocked` installation if present.
- If no non-deleted installation is linked to any non-deleted master account for that `memberId`, return `null`.
- Do not change `ApplicationInstallationRepositoryInterface` or any production API surface; this is a behavior fix in the in-memory test implementation only.

## Test Changes
- Add a concrete regression test suite in `InMemoryApplicationInstallationRepositoryImplementationTest` for `findByBitrix24AccountMemberId()` that creates real reference `Bitrix24Account` entities plus linked reference `ApplicationInstallation` entities in the same test fixture.
- Cover these exact scenarios in the concrete in-memory test:
  - master account `new` + installation `new` => installation is returned
  - master account `active` + installation `active` => installation is returned
  - master account `deleted` + any installation => nothing is returned
  - master account `active` + installation `deleted`, plus master account `new` + installation `new` for the same `memberId` => `new` installation is returned
  - master account `active` + installation `active`, plus master account `new` + installation `new` for the same `memberId` => `active` installation is returned
  - unknown `memberId` => `null`
  - empty `memberId` => `InvalidArgumentException`
- Strengthen the shared `ApplicationInstallationRepositoryInterfaceTest` only for the generic negative-path contract that all implementations can satisfy without extra Bitrix24-account fixtures:
  - unknown `memberId` returns `null`
  - empty `memberId` throws `InvalidArgumentException`
- Do not move the new positive-path lifecycle scenarios into the shared abstract contract test in this task. The current abstract fixture API does not expose Bitrix24-account repository setup, and expanding that abstraction is out of scope for this bugfix.

## Files To Change
- `tests/Unit/Application/Contracts/ApplicationInstallations/Repository/InMemoryApplicationInstallationRepositoryImplementation.php`
- `tests/Unit/Application/Contracts/ApplicationInstallations/Repository/InMemoryApplicationInstallationRepositoryImplementationTest.php`
- `CHANGELOG.md`

## Verification
- Run the in-memory repository test class:
  - `phpunit tests/Unit/Application/Contracts/ApplicationInstallations/Repository/InMemoryApplicationInstallationRepositoryImplementationTest.php`
- If the project uses the Composer binary wrapper instead of a global `phpunit`, run the repository-equivalent local command, but the minimum acceptance bar is this test class passing with the new scenarios.

## Assumptions
- The intended compatibility target is the library behavior described in issue #387: resolve by `memberId` across non-deleted master accounts and exclude only deleted installations from the result set.
- The default account lifecycle for this plan is `new -> active -> deleted` as the happy path.
- `blocked` is not part of the happy path; it is a side-state reachable from both `new` and `active`, so blocked master accounts remain eligible for lookup only after `active` and `new`.
- Determinism is defined by explicit status priority after filtering deleted installations, not by timestamp or array insertion order.
