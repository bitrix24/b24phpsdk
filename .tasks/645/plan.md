# Pull application and watch scope implementation plan (#645)

## Goal and authorization
Cover four legacy pull endpoints in SDK v3-dev, following #647 Pull infrastructure. User authorized autonomous implementation while offline. Scope is SDK code, not sending notifications to real users.

## Design and contracts
- Add src/Services/Pull/Application/Service/Application.php with config(?bool $cache=null,?bool $reopen=null): PullConfigurationResult, event(string $command,array $params=[],?string $moduleId=null,int|string|array|null $userId=null): UpdatedItemResult, push(int|string|array $userId,string $text,?string $avatar=null): UpdatedItemResult. Preserve USER_ID string/int/array type (non-admin server requires own ID as string). Preserve associative PARAMS. Omit optional null fields, including USER_ID to select shared channel. Methods pull.application.config.get, pull.application.event.add, pull.application.push.add require OAuth app context; webhook rejection is expected.
- Add src/Services/Pull/Watch/Service/Watch.php: extend(array $tags): WatchTagsResult, mapping lowercase tags exactly as first-party SDK. Result is list<int|string> not bool.
- Add cached application() and watch() to PullServiceBuilder. WatchTagsResult::getTags() returns scalar list without IDs cast to int.
- Metadata scope pull; links to official docs source (application methods) or official SDK source (watch).
- Unit tests test exact payload/envelope/optional omission/non-admin string ID, required recipients, no loss of false/zero payload values, result true/false and tag strings/numbers, exception propagation and builder cache.
- Integration suite verifies application config webhook WRONG_AUTH_TYPE and read-safe facade/availability. No real event/push notification or subscription mutation. Mark successful OAuth delivery and watch extension not live-verified; do not claim those checks passed. Do not skip tests to mask failure.
- Dedicated tests/Integration/Services/Pull/Application and Watch as appropriate; PHPUnit named suite and Make target. No entity get/list ItemResult introduced by this issue, so generator/field-metadata tests do not apply.

## Evidence
Official b24-rest-docs revision 29bc9d2; documents /private/tmp/pull-application-config-get.md, /private/tmp/pull-application-event-add.md, /private/tmp/pull-application-push-add.md. Official b24jssdk watch.extend call contract in /private/tmp/pull-contracts.md.

## Steps
- [x] Add unit tests and observe RED.
- [x] Implement only verified contracts and focused GREEN.
- [ ] Run light quality gate and safe integration tests; disclose OAuth runtime limits.
- [ ] Verify coverage via Make CLI; review spec then code quality.
- [ ] CHANGELOG #645 and PR to v3-dev, wait for CI.

## Plan review
Explicit methods/envelopes/payload preservation; shared configuration result reused; runtime limitations and verification are separate from endpoint-wrapper coverage.

## Implementation checkpoint
- Unit RED captured: 38 tests fail because the Pull services/builders do not exist.
- Focused GREEN with `make test-file path=tests/Unit/Services/Pull`: 38 tests, 193 assertions.
- Direct vendor/bin/phpunit initially hit known Rector/Typhoon parser preload conflict; using the repository Make PHPUNIT preload guard resolved it without dependency changes.
- Added Pull source/integration/unit paths to .php-cs-fixer.php, Pull source/integration paths to rector.php, and Pull integration paths to phpstan.neon.dist. Deptrac already includes all src directories.
- Integration tests use a scope-local NullLogger factory with the same configured webhook precedence as the shared Factory. No live tests run before coordinator's light gate.
- Successful OAuth event/push delivery and watch mutation intentionally not executed. Unit coverage verifies those wrappers; application config webhook rejection has a dedicated integration test.
- Pull lacks a fields metadata endpoint: dedicated PublicChannelItemResultAnnotationsTest checks get/list raw field completeness and shared type-cast assertions; no invented OpenAPI entity attribute.

Verification: mandatory `make lint-rector` passed; targeted CS dry-run clean. Targeted PHPStan found no Pull errors but reported unmatched global ignore patterns; coordinator will run the full project PHPStan gate. Per-service unit contract files share an abstract fixture helper; focused result/builder checks remain in PullTest.

Review O2: strict mock payload comparison sorts only top-level keys then uses identity comparison, retaining nested PARAMS types/order. Mutation proof temporarily converted string event USER_ID to int: 2 expected unit failures; restored implementation: 38 tests / 193 assertions pass. Log: /private/tmp/pull-mutation.log.

## Final local verification

- Full ordered light gate passed: CS Fixer, Rector, PHPStan, Deptrac, PHPUnit (1,636 tests, 5,254 assertions; 11 existing deprecations).
- Safe integration gates passed: callback 1 test / 2 assertions, Pull channel 4 tests / 30 assertions, Pull application authorization 1 test / 1 assertion.
- Independent spec and code-quality reviews have no remaining findings. OAuth-only documentation and strict type-sensitive parameter assertions were corrected during review.
- No real callback, event/push delivery or watch extension was performed. Successful side-effecting runtime paths remain unverified; wrapper contracts are unit-tested.
- Implementation and local verification completed. Remote PR publication and terminal CI are tracked in the delivery report.
