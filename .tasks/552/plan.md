# Plan: redact OAuth credentials in Core exceptions (issue #552)

Status: implementation and light gates passed; full integration gate blocked by portal operation quota and pre-existing scope drift. User approved draft PR publication with these documented blockers.

**Goal:** prevent OAuth query credentials from leaking through `Core::call()` transport/unknown exception logs and the SDK exceptions created by those handlers.

**Architecture:** add an internal Core helper to redact credential query values and create a diagnostic-only trace. Both catch blocks use the helper before logging and wrapping. The wrapper is constructed directly in `Core::call()` without the unsafe original exception as `previous`.

**Tech stack:** PHP 8.4/8.5, Symfony HttpClient, PSR-3, PHPUnit, Docker Compose.

## Context

- Issue: https://github.com/bitrix24/b24phpsdk/issues/552
- Additional requirement: https://github.com/bitrix24/b24phpsdk/issues/552#issuecomment-5890944906 identifies secrets in the thrown SDK exception as well as logs.
- User selected v3. Base: `origin/v3-dev`, `21bc5b9221386794c82fb730adbe3608ac4d38f4`.
- Branch: `codex/552-redact-oauth-exceptions`.
- Worktree: `/Users/mesilov/.codex/worktrees/issue-552-oauth-redaction/b24phpsdk`.
- The primary checkout contains unrelated `.tasks/578/`; preserve it.
- `make oa-schema-build` succeeded before issue investigation. The generated `docs/open-api/openapi.json` is the local REST baseline; refresh it again in the worktree before implementation.
- Official OAuth contract: https://apidocs.bitrix24.com/settings/oauth/auto-renewal.html specifies GET `/oauth/token/` with `grant_type`, `client_id`, `client_secret`, and `refresh_token`. Keep this request unchanged. OAuth is not a new v3 REST service or result-item contract.
- Existing `Core::call()` logs raw `getMessage()` and `getTrace()` in both transport/JSON and unknown-Throwable handlers. Both SDK wrappers also copy the raw message and retain the raw exception as `previous`.
- A diagnostic run using real Core/ApiClient with MockHttpClient, synthetic credentials and `zend.exception_ignore_args=0` reproduced all four leaks in both handlers: log message, log trace, wrapper message, previous message. No live OAuth request was sent.
- Existing unit tests already cover expired-token retry and API-version preservation. Existing `integration_tests_core` and `make test-integration-core` cover the Core integration boundary.

## Design and alternatives

1. **Recommended: redact messages, whitelist diagnostic trace metadata, detach the original cause in the two affected wrappers.** Retains URL host/path and non-sensitive query parameters, exception class/code and source locations in logs. Avoids recursively traversing arbitrary objects in trace arguments. Changes `getPrevious()` to null for these two wrappers.
2. **Log-only filtering:** smaller compatibility impact, but leaves secrets in the thrown exception and its cause. Does not satisfy the follow-up report.
3. **Generic messages with no trace:** reduces diagnostics unnecessarily. Prefer targeted redaction and safe frame metadata.

### Exact behavior

- Replace values of `client_secret`, `refresh_token`, `access_token`, and `auth` query parameters with `[REDACTED]`. `auth` is included because REST access tokens use that name.
- Match parameter names case-insensitively, including percent-encoded spellings; cover repeated parameters and multiple URLs in one message.
- Handle percent-encoded values without decoding them into delimiters; preserve non-sensitive parameters such as `grant_type=refresh_token`, `client_id`, and request identifiers.
- Handle query values at end of message and before `&`, URL fragment markers, whitespace, and enclosing quotes. Redaction must be idempotent. If parsing/redaction fails, emit a fixed safe diagnostic message rather than returning the unsafe original text.
- For logged traces, retain only the `file`, `line`, `class`, `function`, and `type` keys from each frame. Never retain `args`, `object`, or unknown payload keys. Redact retained string metadata too.
- In both affected catch blocks, compute the sanitized message once, use it in the logger and SDK wrapper, and keep the existing wrapper class and numeric code. Preserve existing event names; include the original exception class as safe diagnostic metadata.
- Do not attach the caught Throwable, its chain, or a copy of its raw trace to the wrapper. Do not construct the wrapper in a helper accepting the caught Throwable: with argument capture enabled, that helper frame could reintroduce the object in the new trace.
- The existing `catch (BaseException)` continues to rethrow known SDK exceptions unchanged.
- Do not change retry behavior, API version forwarding, the OAuth request, or public service signatures.

### Scope boundary

This change protects the two Core exception handlers and the wrappers they create. Direct `ApiClient::getNewAuthToken()` callers, success-path debug token logging, OAuth HTTP error logging in `ApiLevelErrorHandler`, arbitrary user-supplied secrets in `Core::call()` parameters/caller frames, webhook credentials in URL path segments, and application loggers that independently log HTTP traffic are outside this issue's implementation scope. Do not claim universal SDK log sanitization.

## Files to Create

### `src/Core/ExceptionContextSanitizer.php`

Internal, dependency-free helper in the Core layer:

```php
namespace Bitrix24\SDK\Core;

/** @internal */
final class ExceptionContextSanitizer
{
    public static function redactMessage(string $message): string;

    /**
     * @param list<array<string, mixed>> $trace
     * @return list<array<string, int|string>>
     */
    public static function sanitizeTrace(array $trace): array;
}
```

The declarations above describe the planned contract, not production-ready PHP. Implement the functions only after regression tests fail as expected.

### `tests/Unit/Core/ExceptionContextSanitizerTest.php`

PHPUnit class covering the helper with data providers:

- `testRedactsCredentialQueryValues`: each sensitive name; case variants; encoded names/values; repeated parameters; first/middle/last query position; multiple quoted URLs; fragment delimiters; empty values.
- `testPreservesNonSensitiveMessageDetails`: unchanged plain message, host/path, `grant_type=refresh_token`, `client_id`, request identifiers; similar non-sensitive parameter names must not match accidentally.
- `testRedactionIsIdempotent`: applying it twice yields identical output.
- `testTraceRetainsOnlyDiagnosticMetadata`: frames containing nested arrays, credential-bearing objects, and unknown keys yield only safe metadata. Include an object whose serialization would throw to prove it is never traversed.

### `tests/Unit/Core/CoreExceptionLoggingTest.php`

PHPUnit class using real Core and ApiClient with MockHttpClient, a recording PSR-3 logger and synthetic OAuth credentials:

- `testExpiredTokenRefreshFailureDoesNotExposeCredentials`: data provider for Symfony transport exception, Symfony JSON exception, and RuntimeException. First response is `401 expired_token`; refresh fails with a URL-bearing message. Assert that the refresh request still receives the original credential values.
- Assert original log event, diagnostic class/code, sanitized message, safe trace keys, SDK exception type, sanitized wrapper message and null `getPrevious()`.
- Assert neither raw nor URL-encoded test secrets occur in serialized log context or `(string) $exception`; include a nested previous cause containing additional credential query values.
- Execute with `zend.exception_ignore_args=0` and `=1` in fresh PHP processes. The enabled-args case must contain real unsafe source frames before handling, so the test cannot pass vacuously because args were disabled.
- `testKnownSdkExceptionIsRethrownUnchanged`: preserve instance identity and previous exception for a pre-existing non-sensitive SDK exception.
- `testNonSensitiveFailureRetainsDiagnostics`: plain network failure text and numeric code remain useful.

Use PHPUnit attributes and existing project patterns. No result items or SDK builders are generated, so generator and annotation-integration requirements do not apply.

## Files to Modify

### `src/Core/Core.php`

Modify only the transport/JSON and unknown-Throwable catch blocks at the end of `call()`. Use the sanitizer for messages and traces, and remove the unsafe `$exception` constructor argument from their SDK wrappers. Keep the known-BaseException path and all request/retry code unchanged.

### `CHANGELOG.md`

After both quality-gate phases pass, add under the existing Unreleased / Fixed section:

> Redacted OAuth credential query parameters from Core transport/unknown exception logs and SDK exception messages, removed trace argument payloads from these logs, and stopped retaining unsafe original exceptions as previous causes ([#552](https://github.com/bitrix24/b24phpsdk/issues/552)).

Follow the actual existing `## Unreleased` heading; do not introduce a duplicate placeholder release section.

### Existing registration

No changes needed to `phpunit.xml.dist` or `Makefile`: new unit tests are discovered under `tests/Unit`, and `make test-integration-core` already exists. No agent or skill files are changed.

## Deptrac compliance

The helper depends only on PHP built-ins and lives in Core. Core references no new Application, Services, Infrastructure, or Legacy classes. Do not add `skip_violations` entries.

## Execution steps after approval

- [x] Read issue and latest comment; refresh schema; reproduce both leaks using synthetic credentials.
- [x] Confirm v3; fetch remote base; create isolated worktree and clean branch.
- [x] Draft design, scope, compatibility change, and verification plan.
- [x] Obtain explicit approval for this plan, including the previous-cause compatibility change.
- [x] Prepare worktree dependencies with `make composer-install`; copy private `tests/.env.local` only as an ignored file without printing contents. Run `make -s oa-schema-build` to avoid echoing the webhook command.
- [x] Run baseline `make test-unit` and preserve output outside tracked files; diagnose baseline failures before attributing them to this fix.
- [x] Invoke `superpowers:test-driven-development`. Add regression and helper tests first; run them and verify failures expose the actual leaks/missing helper.
- [x] Implement the helper and two handler changes. Run focused tests with trace args enabled and disabled; inspect resulting diagnostics.
- [x] Run the ordered light checks below, fixing only failures relevant to this task or required to execute the gate. Invoke systematic debugging before fixes.
- [x] Run the Core integration suite only after all light checks pass. Result: failed for the two external/base-line causes documented below; do not mark the gate green.
- [x] Update CHANGELOG and plan status; inspect complete tracked and untracked diff for unrelated changes or credentials.
- [ ] Perform final review, commit, push, and create the PR against `v3-dev` using the freshly read repository PR template. Attach PR to this chat.
- [ ] Poll CI until terminal state and report actual result. Do not merge automatically.

## Verification

Focused tests (inside the worktree):

```bash
docker compose run --rm -T php-cli php -d auto_prepend_file=tests/phpunit-preload-guard.php -d zend.exception_ignore_args=0 vendor/bin/phpunit tests/Unit/Core/CoreExceptionLoggingTest.php tests/Unit/Core/ExceptionContextSanitizerTest.php
docker compose run --rm -T php-cli php -d auto_prepend_file=tests/phpunit-preload-guard.php -d zend.exception_ignore_args=1 vendor/bin/phpunit tests/Unit/Core/CoreExceptionLoggingTest.php tests/Unit/Core/ExceptionContextSanitizerTest.php
```

Expected before implementation: failure demonstrating unsafe output and/or missing sanitizer. Expected after implementation: all focused cases pass in both modes, with no fixture secret in exposed logs or wrapped-exception strings.

Phase 1, run sequentially:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Phase 2, only after Phase 1 passes:

```bash
make test-integration-core
```

Record actual counts, failures and fixes. Run `git diff --check` and inspect `git status --short`, including all new files. Do not commit ignored local environment, vendor dependencies, generated schema or `.DS_Store`.

## Follow-up

Investigate the 1.x `dev` line after the v3 PR merges. If the same defect is confirmed there, backport through a separate branch and PR against `dev`; no 1.x fix or merge is part of this implementation.

## Plan review

- Unambiguity: sensitive query names, diagnostic trace allowlist, previous-cause policy, files, and exact quality gates are explicit.
- Non-contradiction: the same helper contracts are used in both catch blocks; known SDK exceptions and OAuth requests retain their existing behavior.
- No gaps: both reported catch paths, exposed wrapper messages and original cause chains have regression coverage; trace-argument configuration, changelog, local gates, PR creation and terminal CI are included.

## Execution evidence

- Approved by the user for v3; implementation stays in the dedicated worktree.
- Dependency installation and schema refresh succeeded.
- Baseline: 1,558 tests / 4,781 assertions; 11 existing deprecations; exit 0.
- RED: all three OAuth failure cases failed on leaked credential text before implementation.
- GREEN: 20 focused tests / 177 assertions with exception args enabled, and 20 / 174 with args disabled.
- PHP CS Fixer passed on first run. Rector required style changes and explicit `previous: null` to prevent ThrowWithPreviousExceptionRector from reintroducing the leak; subsequent full Rector check passed. No Rector configuration exclusion was added.
- PHPStan and Deptrac passed on first run (zero errors / violations). Full unit suite: 1,578 tests / 4,961 assertions, same 11 baseline deprecations, exit 0.
- Independent code review found no actionable findings in the approved scope.

### Integration gate blockers

- Full `make test-integration-core`: 24 tests / 3,174 assertions in 19m08s; one error and one failure.
- `BatchTraversableListTest::testSingleBatchWithDescSortingMore`: all 2,510 pagination-result checks completed, but `tearDown()` contact deletion hit Bitrix24 `OPERATION_TIME_LIMIT`.
- `Credentials/ScopeTest::testScopeCodesIsActual`: live API contains `aiassistant`, `biconnectormobile`, `bizprocdesigner`, `mailsender` absent from the SDK. The exact same mismatch reproduced on the unchanged primary checkout at base commit `21bc5b92` (1 test / 1 failure).
- Separate direct Core integration checks (`CoreTest`, `CoreStrictParamsOrderTest`, `ApiClientDefaultImplementationTest`): 5 tests / 5 assertions, passed. This focused result does not replace the failed full gate.
- Fixture cleanup completed after the quota subsided: all 426 remaining contacts under exact originator `01a0eeaf-9da6-7f9e-bdfd-a4a03875b1fd` were deleted in guarded batches; final list returned zero. All nine other contact-originator groups from this run were verified empty. The original integration gate remains failed; successful cleanup does not change that recorded result.
- Official resource-limit guidance: https://apidocs.bitrix24.com/settings/performance/limits.html — pause the blocked method and retry after operating buckets expire.
- No unrelated scope changes, skips or suppressed failures were introduced. The user explicitly approved opening a draft PR with these blockers; CHANGELOG is updated and delivery proceeds under that approval.
