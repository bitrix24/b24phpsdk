# Plan: Core::call() preserves API version after token renewal (issue #544)

## Context

Issue #544 reports a bug in `Bitrix24\SDK\Core\Core::call()`: when an OAuth request receives
`401 expired_token`, the method renews the token and repeats the request as
`$this->call($apiMethod, $parameters)`. Because the third argument is omitted, the recursive retry
uses the default `ApiVersion::v1`, even when the original request was made with `ApiVersion::v3`.

The 302 domain-change retry in the same method already preserves the version with
`$this->call($apiMethod, $parameters, $apiVersion)`, so the expected fix is to make the
`expired_token` retry use the same argument forwarding pattern.

The issue uses `tasks.task.get` as the v3 example. Current Bitrix24 documentation confirms
`tasks.task.get` is a tasks method with required task id parameters and a v3-style response envelope
containing `result.item`. This bug is in the SDK core retry layer, not in the Tasks service wrapper,
so no service/result-item generator is applicable.

Baseline setup in the issue worktree:

- Branch: `bugfix/544-preserve-api-version-on-token-renew`, based on `origin/v3-dev`.
- `make composer-install` completed because the fresh worktree had no `vendor/autoload.php`.
- `make oa-schema-build` completed successfully after copying the ignored local webhook env file.
- `make test-unit` baseline passed with `1221 tests, 3336 assertions`.

### Brainstorming

1. Fix only `src/Core/Core.php` without a regression test.
   This is too weak because the one-line fix is easy to regress and the bug is hard to notice manually.

2. Add a unit regression test around `Core::call()` and then apply the one-line retry fix.
   This is the recommended approach: it tests the core retry contract directly, avoids live OAuth
   expiration setup, and keeps the implementation scoped to the actual bug.

3. Add an integration test with a real expired OAuth token.
   This would be brittle and hard to run deterministically because it depends on live token state and
   external refresh credentials.

Use approach 2.

---

## Files to Create

No production files need to be created.

The task plan file is created at `.tasks/544/plan.md`.

---

## Files to Modify

### 1. `tests/Unit/Core/CoreTest.php`

Add a regression test before changing production code:

```php
#[Test]
#[TestDox('call() preserves API version when repeating a request after expired_token renewal')]
public function testCallPreservesApiVersionAfterExpiredTokenRenewal(): void
{
    $capturedApiVersions = [];

    // First response: 401 expired_token.
    // Second response: 200 OK.
    // ApiClient::getResponse() callback records each ApiVersion argument.
    // ApiClient::getNewAuthToken() returns a valid RenewedAuthToken DTO.

    $core->call('tasks.task.get', ['id' => 1], ApiVersion::v3);

    $this->assertSame([ApiVersion::v3, ApiVersion::v3], $capturedApiVersions);
}
```

The test must fail on the current code because the second captured version is `ApiVersion::v1`.

### 2. `src/Core/Core.php`

Change the `expired_token` recursive retry:

```php
$response = $this->call($apiMethod, $parameters, $apiVersion);
```

No other retry semantics should change.

### 3. `CHANGELOG.md`

After the quality gate is green, add an entry under `## Unreleased` -> `### Fixed`:

```markdown
- Fixed `Core::call()` preserving `ApiVersion::v3` when retrying a request after OAuth token renewal ([#544](https://github.com/bitrix24/b24phpsdk/issues/544))
```

### 4. `rector.php`

If `make lint-rector` fails before code analysis with `Unknown named parameter $strictBooleans`,
remove the `strictBooleans: false` argument from `withPreparedSets()`. Current `rector/rector`
2.6.1 no longer accepts that named argument, and the removed value is the default behavior.

### 5. `tests/Unit/Services/RemoteEventsFactoryTest.php`

If the updated Rector run reports `AllowMockObjectsForDataProviderRector`, apply the requested
class-level `#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]` attribute to keep
the repository's Rector gate green with the current dependency set.

### 6. `src/Core/Credentials/Scope.php`

If `make test-integration-core` fails because the live `scope` endpoint returns new available scope
codes that are absent from `Scope::$availableScope`, add only those missing codes to the canonical
scope list. In the current live portal response this is limited to `timemanmobile` and
`vibecodeconnector`.

---

## Deptrac compliance

The production change stays inside `src/Core/Core.php` and adds no new dependencies.

The unit test will reuse existing test dependencies plus existing SDK DTOs:

- `Bitrix24\SDK\Application\ApplicationStatus`
- `Bitrix24\SDK\Core\Credentials\AuthToken`
- `Bitrix24\SDK\Core\Response\DTO\RenewedAuthToken`

No `deptrac.yaml` skip violations should be added.

---

## Verification

TDD cycle:

```bash
docker compose run --rm php-cli php -d auto_prepend_file=tests/phpunit-preload-guard.php vendor/bin/phpunit --filter testCallPreservesApiVersionAfterExpiredTokenRenewal --testsuite unit_tests --display-warnings
```

Expected RED result before the production change: the captured API versions are
`[ApiVersion::v3, ApiVersion::v1]` instead of `[ApiVersion::v3, ApiVersion::v3]`.

Phase 1 quality gate:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Phase 2 heavy checks:

No new integration suite is needed for this core retry bug. Use the existing core integration suite
as the heavy check:

```bash
make test-integration-core
```

Current Phase 2 status:

- `make test-integration-core` reached the full suite summary with `24 tests, 3174 assertions`.
- The only error is `BatchTraversableListTest::testSingleBatchWithDescSortingMore` failing in
  `tearDown()` while deleting test CRM contacts because the live portal returned
  `operation time limit exceeded method is blocked due to operation time limit`.
- The same run reached and executed the live `ScopeTest`; the `scope` endpoint response included
  `timemanmobile` and `vibecodeconnector`, confirming the `Scope` list update against live metadata.
- After waiting for the portal operating window reset, the targeted retry
  `make test-file path='tests/Integration/Core/BatchTraversableListTest.php --filter testSingleBatchWithDescSortingMore'`
  failed the same way in `tearDown()` after `1 test, 2512 assertions`, confirming this is a
  reproducible live cleanup limit in the existing heavy batch test rather than a regression from
  issue #544.
- Leftover test contacts from the failed full-suite and targeted runs were cleaned up by filtering
  `crm.contact.list` on the two failed-run `ORIGINATOR_ID` values and deleting only matching
  contacts. The final cleanup count check returned `found=0` for both originator values.
