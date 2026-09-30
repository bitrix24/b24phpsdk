# Plan: Prepare SDK 3.7.0 release (#664)

## Context

Prepare the user-requested v3-dev to v3 release PR from origin/v3-dev 8be2874e. Preserve the primary checkout. No feature implementation or release publication is included. Open milestone issue #632 blocks readiness pending maintainer disposition; keep the release PR draft.

## Files to Modify

- CHANGELOG.md: consolidate the current notes into Added, Changed and Fixed under 3.7.0, preserve historical sections, add fresh final API coverage.
- src/Core/ApiClient.php: SDK_VERSION 3.7.0.
- tests/Unit/Core/ApiClientTest.php: expected version headers 3.7.0.
- README.md: Composer constraint ^3.7.

## Files to Create

- .tasks/664/milestone-audit.md: map every closed milestone item to notes and document unresolved runtime limits.
- .tasks/664/verification.md: record fresh release-candidate checks.

## Deptrac compliance

No new dependencies or architectural layers.

## Verification and delivery

1. Audit closed milestone items and check all merged PR commits are ancestors of the candidate.
2. Update version metadata and normalize changelog without changing historical releases.
3. Run make -s oa-schema-build, sdk-coverage-v3-show and sdk-coverage-v1-show; validate arithmetic.
4. Run ordered CS Fixer, Rector, PHPStan, Deptrac and unit checks, then Main user-history/event-log and focused Core integration smoke tests. Record any skips/deprecations and historical limitations.
5. Validate Composer metadata and git diff; refresh coverage before push.
6. Fast-forward remote v3-dev with the reviewed release preparation commit, open draft PR v3-dev to v3, and wait for terminal CI. Do not merge or publish a tag.

## Plan review

- Unambiguity: exact release version, source and destination, files and gates specified.
- Non-contradiction: release metadata only; known blockers remain visible and the PR stays draft.
- No gaps: audit, coverage, local checks, remote delivery and CI reporting included.
