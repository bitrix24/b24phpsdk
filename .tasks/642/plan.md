# Complete legacy timeman coverage in SDK v3

## Authorization and scope
The user authorized autonomous implementation and explicitly requires a separate PR per scope, targeting v3-dev. This branch addresses #642 only. New file headers use Maksim Mesilov <mesilov.maxim@gmail.com>. Only officially deprecated methods receive the native Deprecated attribute; none of the nine new methods are documented deprecated.

## Design
Preserve the existing Timeman service. Add NetworkRange and TimeControl services with cached accessors in TimemanServiceBuilder. Implement all nine documented gaps: networkrange.check/get/set, timecontrol.report.add, timecontrol.reports.get/settings.get/users.get, timecontrol.settings.get/set. Use distinct response envelopes and annotated item classes for object records; scalar arrays remain response wrappers. In particular networkrange.check may be false, and networkrange.set nests result.result and error_ranges. reports.get nests report.days. Preserve optional argument omission and map ACTIVE false to 0.

Official research: /private/tmp/remaining-timeman-contracts.md, based on bitrix-tools/b24-rest-docs revision 29bc9d2 and official MCP. Validate exact fields before implementation.

## Implementation and verification
1. Refresh OpenAPI; baseline Make unit suite. Completed: 1592 tests, 5036 assertions, 11 existing deprecations.
2. Add failing contract tests for every missing endpoint, response shape, optional parameters, false/empty handling and errors.
3. Use the mandatory result-item generator first for new annotated ItemResult classes. Record actual failures before any manual fallback. Every ItemResult extends AbstractAnnotatedItem.
4. Implement wrappers, service-builder accessors and unit coverage. Register scope paths in lint and test configuration as necessary.
5. Add dedicated AnnotationsTest for entity get/list results; no fields metadata exists, so compare live raw keys and documented types. Do not invent fields().
6. Run ordered Make gates: lint-cs-fixer, lint-rector, lint-phpstan, lint-deptrac, test-unit. Then safe read-only integration.
7. Do not alter global network rules or employee-monitoring settings on the configured portal. Do not overwrite real absence reports. Mutations use unit contract tests; report successful mutation runtime as unverified. Disabled tool/access errors are explicit runtime limitations, never hidden skips.
8. Record before/after coverage through the Make CLI, update CHANGELOG, perform spec and quality review, commit and open a separate PR to v3-dev; wait for terminal CI. Keep the issue open if material runtime acceptance remains blocked and explain evidence.

## Generator and RED evidence
`make oa-schema-build` succeeded on this worktree. Ran `docker compose run --rm php-cli php bin/console b24-dev:result-item-generator <method> --stage=all` for timeman.networkrange.get/check and timeman.timecontrol.settings.get/reports.settings.get/reports.users.get/reports.get. Each exited 1 with `Unable to determine the current git branch`: the container cannot resolve the host managed worktree Git metadata. Manual fallback uses the official documented contracts, AbstractAnnotatedItem, and dedicated raw-key/type-cast annotation tests; these endpoints expose no fields metadata or matching OA legacy entity.

RED: NetworkRange 6 tests and TimeControl 10 tests failed before implementation because the new service classes do not yet exist. These cases cover all nine endpoints, optional omission, empty/false responses, nested failure ranges and ACTIVE=false conversion.


## Completion evidence (2026-09-30)
- Implemented all nine documented endpoints in NetworkRange and TimeControl, registered cached builder accessors and ApiServiceMetadata scope attributes. Existing service is preserved; no deprecated markers were added without official evidence.
- Result envelopes: check returns nullable match; set exposes nested status and error ranges; reports preserves report.days and separate user. Days/reports inside days intentionally remain raw nested arrays, not fully typed nested records.
- Required generator fallback recorded above. Six new annotated item classes use AbstractAnnotatedItem. Six dedicated AnnotationsTest classes use live raw keys and cast checks because no fields metadata endpoint exists. The shared exact-key assertion accounts for the documented optional active field in the report-user list response.
- Deterministic official response fixtures verify all six annotated item classes, including the user-list variant without active and nullable activity date. Strict payload identity checks cover false versus integer zero; all nine methods propagate API exceptions.
- Ordered final gates: lint-cs-fixer passed, lint-rector passed, lint-phpstan passed, lint-deptrac passed (0 new violations), test-unit passed: 1625 tests, 5195 assertions, 11 pre-existing deprecations. Rector initially requested only test variable/type-check conventions; applied and verified. Coverage CLI revealed missing scope metadata; added regression (observed RED), fixed metadata and reran every gate.
- Safe live integration: make test-integration-scope-timeman-readonly passed, 18 tests and 57 assertions. No skipped tests. All six read methods returned successfully.
- Exact live fixture availability: networkrange.get returned 0 ranges; networkrange.check returned a nonempty match object; reports.users.get returned 1 user; reports.get returned 0 report days. Settings, report-interface settings, user, and report envelope were nonempty. Network range list field casting and nested absence records are fixture-only evidence where live data was empty.
- No portal settings, network rules, absence comments or calendar entries were mutated. Successful runtime mutation behavior for networkrange.set, settings.set and report.add remains unverified; unit contracts cover these methods. Keep this distinction in the PR and final report. The broader pre-existing timeman integration suite was not run because it mutates workday state; the added read-only suite is the relevant safe gate.
- CHANGELOG updated. Final coverage row is recorded below after the Make CLI completes. Parent handles push/PR/terminal CI; no push performed here.
- Final `make sdk-coverage-v1-show` (scope-statistics menu): timeman 14 available, 14 covered, 0 uncovered, 0 SDK-only, 100.00%; baseline was 5/14 (35.71%). This is wrapper coverage, not successful mutation-runtime evidence.
