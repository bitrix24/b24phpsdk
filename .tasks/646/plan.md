# Complete legacy call scope coverage in SDK v3

## Authorization and context
The user explicitly authorized issue creation and autonomous implementation of the smallest scopes while offline. Target branch: v3-dev. Worktree base: 35195bf8 (includes #638). Use TDD and subagent-driven implementation/review; no additional plan approval needed under this explicit delegation.

## Design
Preserve existing InfoCall wrappers and telephony scope metadata (official docs label these endpoints telephony). Add the missing callback service using the established Voximplant facade. Fix scoped coverage to intersect portal scope methods with all SDK endpoints, rather than a metadata-prefiltered subset; this prevents false negatives when portal permissions and service grouping differ. No outbound phone call will be placed during validation.

## Files and contracts
- Create src/Services/Telephony/Voximplant/Callback/Service/Callback.php: start(string $lineId, string $toNumber, string $text, ?string $voiceCode = null), mapped to voximplant.callback.start FROM_LINE/TO_NUMBER/TEXT_TO_PRONOUNCE and optional VOICE.
- Reuse VoximplantInfoCallResult only if its existing RESULT/CALL_ID contract exactly fits the official callback response; otherwise generate and add an annotated callback result.
- Modify src/Services/Telephony/Voximplant/VoximplantServiceBuilder.php: cached callback() accessor.
- Modify src/Infrastructure/Console/Commands/Documentation/ShowCoverageStatisticsCommand.php: scope calculations use the global SDK inventory for covered/uncovered membership. Preserve the scope-specific SDK-only inventory separately, so unrelated scopes are not printed as SDK-only.
- Extend tests/Unit/Infrastructure/Console/Commands/Documentation/ShowCoverageStatisticsCommandTest.php: cover wrong/multi-scope metadata and both interactive branches.
- Add tests/Unit/Services/Telephony/Voximplant/Callback/Service/CallbackTest.php and builder/result tests as needed. Verify all required/optional mapping, optional omission, response casting and error propagation.
- Integration test: safe server-side rejection using absent required parameters or impossible line ID; never send a valid outbound call. Verify callable method and error propagation; document lack of a live successful phone-call test. Add a named PHPUnit suite and Make target.
- CHANGELOG.md: Fixed coverage attribution and Added callback support, linked to #646.

## Steps
- [ ] Establish baseline unit tests.
- [ ] Add regression tests and observe intended failure before implementation.
- [ ] Implement callback and scope membership fix; run focused tests.
- [ ] Run make lint-cs-fixer, make lint-rector, make lint-phpstan, make lint-deptrac, make test-unit.
- [ ] Run safe integration tests and Make CLI scope coverage; document portal constraints.
- [ ] Review spec compliance, then code quality.
- [ ] Commit, push and create PR to v3-dev using repository template; wait for terminal CI.

## Plan review
- Unambiguous: legacy call permission scope, SDK v3 target, and no-outbound-call validation are explicit.
- Consistent: existing InfoCall contracts and services retained; only missing callback added.
- Complete: tests, facade, reporting, docs, quality gates, and PR delivery included.

## Implementation evidence

- RED: both new scoped-report regression tests failed on incorrect coverage before production changes (7 tests, 25 assertions, 2 failures). Callback unit tests failed on missing service/accessor.
- Callback reuses the existing VoximplantInfoCallResult: documented response is exactly RESULT boolean and CALL_ID string. No new or modified ItemResult, SelectBuilder or ItemBuilder is required, so generator contracts do not apply.
- Added a dedicated callback integration suite/Make target; it sends an impossible line and empty destination/text only. Successful outbound-call behavior is deliberately unverified. No calls to real numbers are authorized or made.
- GREEN: targeted callback and scoped-report unit run passed, 11 tests / 46 assertions. Repeated after Rector refinements with the same result.
- Mandatory `make lint-rector` passed after adopting its test variable naming and nullable scope type-check conventions. Full ordered quality gates and safe live integration are delegated to the parent delivery workflow.

- Integration initially failed only on error-message capitalization: the portal returns lowercase `could not find line`. Changed assertion to a case-insensitive match of the same specific error, without broadening to arbitrary API errors.

## Final local verification

- Full ordered light gate passed: CS Fixer, Rector, PHPStan, Deptrac, PHPUnit (1,636 tests, 5,254 assertions; 11 existing deprecations).
- Safe integration gates passed: callback 1 test / 2 assertions, Pull channel 4 tests / 30 assertions, Pull application authorization 1 test / 1 assertion.
- Independent spec and code-quality reviews have no remaining findings. OAuth-only documentation and strict type-sensitive parameter assertions were corrected during review.
- No real callback, event/push delivery or watch extension was performed. Successful side-effecting runtime paths remain unverified; wrapper contracts are unit-tested.
- Implementation and local verification completed. Remote PR publication and terminal CI are tracked in the delivery report.
