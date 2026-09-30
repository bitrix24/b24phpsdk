# Complete documented SonetGroup gaps for SDK v3

## Authorization and design
The user authorized autonomous implementation of remaining scopes, each in a separate PR targeting v3-dev. Implement five documented gaps in the existing SonetGroup service: feature.access, user.get, user.invite, user.request, user.update. Preserve all existing methods. No official deprecation is documented for existing or new SonetGroup wrappers; do not label them deprecated merely because they are legacy REST. All newly created PHP files use Maksim Mesilov copyright; preserve existing authorship notices.

Official contracts: /private/tmp/remaining-sonet_group-contracts.md (official docs revision29bc9d2 and MCP). feature.access returns bool; user.get uses ID, returns list{USER_ID,ROLE}; invite/update return successful user-ID lists (including empty), not boolean; request uses current user and returns bool. Reuse scalar response wrappers only where their exact semantics match. Existing SonetGroupUserOperationResult isSuccess loses per-ID information, so don't use it for new list contracts.

Four subject endpoints remain undocumented: sonet_group_subject.add/get/update/delete. Do not invent wrappers. Issue331 remains open for unresolved contracts.

## Steps
1. Refresh OpenAPI and establish baseline using Make. Corrected baseline8/17 requires the scope attribution fix in PR648; don't merge unrelated scopes into this separate PR. For CLI measurement temporarily apply only the reporting-command fix, record that dependency, then restore before committing.
2. Write failing contract unit tests for five methods: exact keys/types, omitted optional message, empty lists and false, exception propagation, typed membership results.
3. Run generator first for membership ItemResult; record actual failure before manual fallback. New item extends AbstractAnnotatedItem with USER_ID integer and ROLE string. Keep entity result separate from response envelope. Add dedicated AnnotationsTest; no fields endpoint exists, compare documented raw system keys/types.
4. Implement wrappers; no notifications to other users in live tests. Safe integration may create a hidden disposable current-user-owned group, read members/access and delete in finally. Invite/request/role mutation contracts are unit-tested unless isolated controlled accounts exist. Existing suite must be inspected for effects before execution; use NullLogger, do not leak webhook.
5. Run ordered make lint-cs-fixer, lint-rector, lint-phpstan, lint-deptrac, test-unit; then applicable integration. Fix actual failures without exclusions. Update CHANGELOG and plan evidence. Review spec and quality, commit, separatePR and terminalCI.

Expected measured coverage after supported methods:13/17. This is not100percent and does not prove undocumented subject method contracts or notification runtime.

## Existing integration safety defect to correct before running
SonetGroupTest::cleanupTestGroups currently searches all portal groups with NAME containing Test and deletes them. Do not run it in that form. Replace broad cleanup with exact IDs created by this test instance, recorded immediately on creation and deleted in teardown/finally. Make fixtures hidden (VISIBLE=N) where compatible, preserve existing user-owned groups regardless of names, and route test services through NullLogger. This correction is required for safely running the scope suite and should be described in the PR.

## Generator evidence
`docker compose run --rm php-cli php bin/console b24-dev:result-item-generator sonet_group.user.get --stage=all` failed: Unable to determine the current git branch (container does not mount the managed worktree git directory). Manual membership result fallback follows the verified official USER_ID/ROLE contract.

## Implementation and validation
- Extended existing `SonetGroup` with `featureAccess`, `getUsers`, `inviteUser`, `requestUser`, `updateUser`; preserved all existing entry points. Boolean responses reuse UpdatedItemResult; invite/update expose affected IDs through SonetGroupUserIdsResult; membership entries use AbstractAnnotatedItem.
- Generator fallback produced SonetGroupUserItemResult (USER_ID int, ROLE string); no field metadata endpoint or OpenAPI entity exists for this legacy membership result. Dedicated SonetGroupUserItemResultAnnotationsTest checks real system keys, official field types, and live casts.
- Shared SonetGroupFixture records returned IDs immediately, forces VISIBLE=N, uses NullLogger, refuses unknown ID deletion, and reports failed cleanup IDs. Replaced global name-based cleanup; unit regression verifies exact-ID deletion, no group search, and rejecting unowned IDs.
- RED: 34 tests failed on missing methods before implementation. GREEN: 34 tests / 139 assertions. Final SonetGroup unit directory: 38 tests / 151 assertions, no deprecations.
- `make lint-cs-fixer`: passed first run. `make lint-rector`: initial variable-name/newline findings fixed with Rector, rerun passed. `make lint-phpstan`: initial mock property needed SonetGroup&MockObject type; fixed, rerun passed. `make lint-deptrac`: passed first run, no violations/warnings/errors.
- Final ordered phase one passed: CS, Rector, PHPStan, Deptrac, then `make test-unit`: 1628 tests / 5185 assertions, exit 0; 11 existing deprecations outside SonetGroup.
- `make test-integration-scope-sonet-group`: passed first live run, 13 tests / 85 assertions. Created hidden own groups; read members and task-feature access; exact fixture cleanup succeeded. Invite/request/update contracts are unit-tested only; no live invitations, membership requests, or role changes sent.
- Existing integration suite/Make target already discover the new tests; no additional suite duplication required.
- Spec and quality review approved by independent parent reviewers. CHANGELOG updated. Issue #331 stays open for four undocumented sonet_group_subject methods; this change does not claim full scope coverage.

## Official references
- https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-feature-access.html
- https://apidocs.bitrix24.com/api-reference/sonet-group/members/sonet-group-user-get.html
- https://apidocs.bitrix24.com/api-reference/sonet-group/members/sonet-group-user-invite.html
- https://apidocs.bitrix24.com/api-reference/sonet-group/members/sonet-group-user-request.html
- https://apidocs.bitrix24.com/api-reference/sonet-group/members/sonet-group-user-update.html

## Coverage evidence
`make sdk-coverage-v1-show` with menu input `1`, `2`, `sonet_group`, `0` measured **13/17 (76.47%)**, four uncovered methods: sonet_group_subject.add/delete/get/update. Corrected baseline was 8/17. Both measurements depend on the scope-attribution reporting fix from PR #648: only ShowCoverageStatisticsCommand.php from codex/646-small-legacy-scopes was temporarily substituted, then restored byte-for-byte before commit. No reporting code or other scope implementation is included in this branch. Initial CLI invocation without menu input aborted; rerun with explicit input exited 0.


## PR #655 review 5361876030 follow-up (2026-09-30)

The user explicitly requested implementing the two review comments: migrate the membership ROLE to an enum, and expose extensible feature/operation arguments. This section supersedes the original string-only ROLE design.

Design: use string-backed `Common\MemberRole` (owner A, moderator E, member K), `Common\Feature` (photo, calendar, tasks, files, blog), and `Common\FeatureOperation` (all documented operation codes). `featureAccess()` accepts each enum or a string independently and serializes enums to their backing values at the REST boundary. Existing strings and module-specific codes pass through unchanged. Feature/operation compatibility remains server-validated, as before. No changes to unrelated membership mutation methods.

Generator prerequisite: reran `make -s oa-schema-build` successfully in the PR worktree. Reran `docker compose run --rm php-cli php bin/console b24-dev:result-item-generator sonet_group.user.get --stage=all`; it failed with `Unable to determine the current git branch`. The managed worktree Git directory is outside the container mount. Manually update the existing result annotation/import, relying on the existing AbstractAnnotatedItem enum casting. No new result class or manual getter is needed.

Files to create: `src/Services/SonetGroup/Common/{MemberRole,Feature,FeatureOperation}.php`.
Files to modify: SonetGroupUserItemResult.php, SonetGroup.php, SonetGroup unit tests, the existing membership annotation and service integration tests, CHANGELOG.md, and this plan. All enum dependencies stay inside Services; no Deptrac changes.

Steps:
1. Add regression coverage for all member roles and enum/string/mixed/custom feature access arguments; observe failures before production edits.
2. Add documented enums, annotate ROLE as MemberRole, normalize feature/operation enums to wire strings.
3. Adapt live membership tests to assert enum casting against the uncast live ROLE. The legacy endpoint has no fields metadata endpoint; remove the fabricated metadata literal and retain explicit live-response contract checks and shared annotation/casting assertions. Do not claim live field-metadata validation.
4. Run ordered `make lint-cs-fixer`, `make lint-rector`, `make lint-phpstan`, `make lint-deptrac`, `make test-unit`; then `make test-integration-scope-sonet-group` with exact-ID fixture cleanup.
5. Update CHANGELOG, inspect the final diff, commit and push the existing PR branch; await terminal CI.

Plan review: scope and wire-value contracts are explicit; enum types and imports agree across production/tests; regression, integration, changelog and remote-CI verification cover both requested comments.


Follow-up evidence before remote-base synchronization:
- RED: 61 service tests, 27 expected failures for missing enums. GREEN: complete SonetGroup unit directory, 65 tests / 311 assertions.
- All five ordered phase-one commands passed on their first run: CS Fixer, Rector, PHPStan, Deptrac, and 1655 unit tests / 5345 assertions (11 existing deprecations).
- Live SonetGroup suite passed: 13 tests / 86 assertions, including enum input serialization and ROLE casting; exact-ID fixture cleanup succeeded.
- Independent read-only review found no actionable issues.
- Before pushing, the remote PR branch advanced to 85981bf7 via a merge of v3-dev. Fast-forwarded safely with local fixes intact. Because this updates lint configuration and shared baseline, rerun the ordered gates and integration on the combined state before delivery.


Final verification and recovery:
- The combined branch passed all ordered phase-one gates: 1742 unit tests / 5597 assertions, with the same 11 existing deprecations.
- An external archive removed the original worktree during the subsequent integration run, causing missing source/vendor class errors. The archive retained all 10 edited files in Git snapshot 01256eea152c40148b2c65261c62eb13024247e7.
- Recovered those files byte-for-byte into the managed `pr655-review` worktree attached to the review-fix chat. Restored local ignored dependencies/test environment and refreshed OpenAPI successfully.
- On the recovered final state, all ordered gates passed again: CS Fixer, Rector, PHPStan, Deptrac, 1742 unit tests / 5597 assertions (11 existing deprecations), then 13 integration tests / 86 assertions. No test or production workaround for the archive failure was needed.
- CHANGELOG records MemberRole casting and extensible feature/operation enums. Both requested review comments are implemented; remote delivery must use a normal push and await terminal CI.
