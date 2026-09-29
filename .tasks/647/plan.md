# Pull channel scope implementation plan (#647)

## Goal and authorization
Cover three legacy pull_channel endpoints in SDK v3-dev. User authorized autonomous implementation of smallest scopes. Base worktree 35195bf8. Schema refreshed, baseline unit tests pass.

## Design
Add a Pull scope builder with separate Channel and Configuration services. Public channel get/list share an annotated item with user_id:int, public_id:string, signature:string, start/end:CarbonImmutable. A single item response is direct; the list is a user-ID-keyed map, not a v3 items envelope. Configuration varies by deployment and must preserve all keys via a documented raw array accessor. Keep authentication/transport in CoreInterface. No channel token values may be saved in fixtures or logs.

## Contracts and files
- src/Services/Pull/PullServiceBuilder.php; src/Services/ServiceBuilder.php: cached getPullScope() and channel(), configuration() services.
- src/Services/Pull/Channel/Service/Channel.php: get(?int $userId = null, bool $application = false) and list(array $userIds, bool $application = false). Map USER_ID/USERS and APPLICATION Y/N. Preserve keyed results via getChannels(), with documented array<int,PublicChannelItemResult> return. No pagination because endpoint has none.
- src/Services/Pull/Channel/Result/PublicChannelItemResult.php extends AbstractAnnotatedItem. PublicChannelResult::getChannel() and PublicChannelsResult::getChannels().
- src/Services/Pull/Configuration/Service/Configuration.php: get(?bool $cache = null, ?bool $reopen = null), omit unset fields; map explicitly supplied flags Y/N. PullConfigurationResult::getConfiguration(): array preserves optional deployment fields.
- Separate dedicated channel annotations integration test checks get AND list raw fields/types; no fields endpoint exists, so do not invent one. Configuration tests use broad keys/shape, not sensitive values.
- Unit tests: exact endpoints/flags/omission, keyed list including empty/duplicate behavior, casts, facade caching, API error propagation.
- Register integration_tests_pull_channel in phpunit.xml.dist and test-integration-pull-channel in Makefile.

## Evidence and generators
Authoritative official b24jssdk source plus sanitized live webhook probes: /private/tmp/pull-contracts.md. Before handwritten ItemResult attempt `docker compose run --rm php-cli php bin/console b24-dev:result-item-generator pull.channel.public.get --stage=all`. If method lacks OpenAPI/docs generator input, record actual failure here and create the item from verified live schema. No URL guessing: metadata can link official first-party SDK source or verified overview.

## Sequence
- [x] Add failing unit tests, observe intended RED.
- [x] Attempt generator, inspect output or record unsupported source.
- [x] Implement minimal services, results and facades.
- [ ] Run focused tests, full light quality gate, then read-only live integration tests and coverage CLI.
- [ ] Independent spec review then quality review; fix findings.
- [ ] CHANGELOG #647, PR to v3-dev, terminal CI.

## Plan review
Unambiguous legacy envelopes, flags and keyed return contracts; no invented fields or notifications; unit/live gates and runtime limitations included.

Generator attempted in Docker: `b24-dev:result-item-generator pull.channel.public.get --stage=all` failed with `Unable to determine the current git branch` because the linked worktree Git directory is outside the container mount. Manual annotated item follows the independently verified five-field live response contract.

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
