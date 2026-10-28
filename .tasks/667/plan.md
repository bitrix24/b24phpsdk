# Plan: Distinguish response decoding failures safely (issue #667)

## Context

[Issue #667](https://github.com/bitrix24/b24phpsdk/issues/667), SDK 3.8.0; base `v3-dev`.
Core currently catches transport errors and Symfony's concrete `JsonException` together, sanitizes messages/traces, and throws `TransportException` without a previous cause. Other decoding-interface implementations fall through to the unknown-error wrapper.
The author explicitly prefers a dedicated subclass. Keep all guarantees from #552, including a null previous cause and sanitized log context.
The existing security and coverage-command tests pass: 12 tests, 176 assertions combined.

## Files to Create

`src/Core/Exceptions/ResponseDecodingException.php`:

```php
namespace Bitrix24\SDK\Core\Exceptions;

/** The portal response could not be decoded. */
class ResponseDecodingException extends TransportException
{
}
```

`tests/Unit/Core/ResponseDecodingExceptionTest.php`: exercise a real `Core` with Symfony `MockHttpClient`/`MockResponse`, plus a stub implementing the decoding exception interface when needed. Tests must assert:

- malformed HTML/truncated JSON at non-200 HTTP statuses yields `ResponseDecodingException` from `Core::call()`;
- preserve the existing HTTP 200 lazy-decoding path: `Core::call()` returns a Response, and `Response::getResponseData()` wraps the decoding cause in `BaseException`. Unifying that separate public behavior is outside this issue unless explicitly approved;
- the subclass remains catchable as `TransportException`;
- a non-concrete `DecodingExceptionInterface` implementation is classified the same way;
- a network exception remains ordinary `TransportException`;
- `getPrevious()` is null, exception code survives, and fake auth/client-secret/refresh-token strings are absent from message, rendered exception and captured error logs;
- known SDK exceptions are still rethrown unchanged.

## Files to Modify

- `src/Core/Core.php`: import `DecodingExceptionInterface` and `ResponseDecodingException`; classify by the interface in the transport catch and construct the subclass only for decoding causes. Reuse the existing sanitization/logging block. Do not attach response objects or the source Throwable to the new exception.
- `tests/Unit/Core/CoreExceptionLoggingTest.php`: update the JSON provider's exact expected wrapper class to the new subclass; retain every existing credential-leak assertion and the transport/unknown cases.
- `CHANGELOG.md`: Fixed entry under Unreleased with #667.

No generator applies to exception classes or tests. No new REST method, result entity, suite or Make target is needed.

## Steps

- [x] Add the decoding-vs-network regression tests and observe incorrect classification on the base code.
- [x] Add the subclass and classify using the Symfony contract interface.
- [x] Update only the expected JSON wrapper in the existing security test and run both files.
- [x] Run the common quality gate, then core integration.
- [x] Add changelog entry, review compatibility/security, commit and prepare PR.
- [ ] Inspect `dev` after merge and backport if the regression/security baseline is present there.

## Deptrac compliance

Core-to-Core plus the already used external Symfony contracts. No dependencies on Services or Infrastructure are introduced.

## Verification

```bash
docker compose run --rm -T php-cli vendor/bin/phpunit tests/Unit/Core/ResponseDecodingExceptionTest.php tests/Unit/Core/CoreExceptionLoggingTest.php tests/Unit/Core/ExceptionContextSanitizerTest.php
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-core
```

The malformed-response regression uses deterministic local doubles; live portal integration is an additional gate, not a prerequisite for reproducing HTML/JSON failures.

## Implementation verification outcome

The decoding/security unit suites and focused live Core calls pass. The initial broad Core suite failed only ScopeTest; the user-authorized credentials follow-up now corrects its catalog and per-portal subset contract. All shared light gates are run on the combined candidate; PR CI verifies each isolated branch. Broader scope-catalog readiness is recorded separately and PR readiness is tracked by CI.
