# Plan: veto portal redirects before credentials change (issue #632)

## Context and approved design

The user selected the preliminary-event approach with irreversible deny(), then authorized implementation for SDK v3 (base origin/v3-dev). Current Core follows HTTP 302 across domains and emits PortalDomainUrlChangedEvent only after retrying. Keep that successful-change notification and existing default behavior. Add an explicit veto before changing credentials or sending the retry. No allow-list, redirect policy builder or redirect-chain changes are included.

Official app.info documentation was checked through Bitrix24 MCP: OAuth auth is sent with the request; the response has result and time. It is only a representative endpoint; no REST response contract changes. No service/result generators apply.

## Files to create

- src/Events/PortalDomainUrlChangingEvent.php: final Event subclass; immutable old/new URL context via getOldDomainUrl()/getNewDomainUrl(); deny(string $reason): void, isDenied(): bool, getDenialReason(): ?string. First denial wins; no allow/reset method. stopPropagation alone does not veto.
- src/Core/Exceptions/PortalDomainChangeRejectedException.php: extends PortalUnavailableException, immutable old/new URL and reason, matching getters. No request parameters or token in context.
- tests/Unit/Core/PortalRedirectTest.php: real CoreBuilder + MockHttpClient + EventDispatcher, fake OAuth credentials. Cover denied redirect (one request, unchanged credentials, no Changed event), permitted/default redirect, event ordering, listener priority/first denial, stopPropagation semantics and each hop of a redirect chain.
- docs/portal-domain-redirects.md: listener registration through CoreBuilder, deny example, exception handling, default-follow compatibility, distinction from Changed event and stopPropagation.

## Files to modify

- src/Core/Core.php: after same-domain guard, dispatch Changing, check isDenied and throw dedicated exception, then retain existing change/retry/Changed flow.
- CHANGELOG.md: add Unreleased/Added entry linked to #632 for the event and exception.
- README.md: link the redirect documentation near existing documentation links.

## Deptrac compliance

New exception stays in Core. Event follows existing src/Events pattern and depends only on Symfony Event. No new cross-layer dependency or skip_violations entry.

## Execution and verification

- [x] Confirm clean worktree and baseline Core tests; refresh OpenAPI snapshot.
- [x] Write regression tests and run make test-file path=tests/Unit/Core/PortalRedirectTest.php; observe missing veto failures.
- [x] Implement event, exception and Core check; rerun focused tests.
- [x] Add usage documentation and changelog; review compatibility and denied-request invariants.
- [ ] Run make lint-cs-fixer, make lint-rector, make lint-phpstan, make lint-deptrac, make test-unit in order, then make test-integration-core.
- [ ] Review diff, commit, push, open PR against v3-dev using repository template, attach PR and wait for terminal CI.

Tests use synthetic credentials and mocked transport; no real portal migration is attempted. Live Core suite verifies ordinary SDK calls separately.

## Verification evidence

- Baseline Core: 7 tests / 9 assertions. OpenAPI refresh succeeded without a tracked snapshot diff.
- RED: two regression failures (missing veto and missing preliminary event).
- GREEN: 4 redirect tests / 33 assertions, no warnings or deprecations.
- CS Fixer, Rector, PHPStan, Deptrac passed. Unit suite: 1964 tests / 6492 assertions; exit 0 with 11 deprecations outside the focused new tests.
- Independent read-only review: no actionable findings.

- Full Core integration suite: 24 tests / 3174 assertions; one cleanup error (OPERATION_TIME_LIMIT on crm.contact.delete for the 2510-contact fixture) and one scope catalog drift failure (aiassistant, biconnectormobile, bizprocdesigner, mailsender). The affected source/test files are unchanged from the base. No failures were suppressed.
- Direct live Core integration: 3 tests / 3 assertions passed.
- Delivery is a draft PR because the full integration gate is not green; no unrelated scope/catalog or batch behavior changes are included. Cleanup of this run's uniquely marked remaining fixture contacts is being completed separately.
