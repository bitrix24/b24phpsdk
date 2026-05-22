# Plan: Add IM\UserStatus service for im.user.status.* methods (issue #430)

## Context

Issue #430 requests adding a `UserStatus` service under `src/Services/IM/User/Service/` that wraps four REST methods:

- `im.user.status.get` — returns the current user's status as a scalar string (e.g. "online", "idle", "dnd", "away", "offline"). The Response parser (Response.php:77-79) wraps non-array results in an array, so `getResult()[0]` yields the string.
- `im.user.status.set` — sets a custom status; takes a `STATUS` parameter; returns `true` (maps to `UpdatedItemResult`).
- `im.user.status.idle.start` — enables the automatic "Away" status; no parameters; returns `true`.
- `im.user.status.idle.end` — disables the automatic "Away" status; no parameters; returns `true`.

Because `im.user.status.get` returns a plain string (not a structured object), no `*ItemResult` with `@property-read` annotations is required, and no result-item annotation test is needed. The custom `UserStatusResult` exposes a `status(): string` accessor instead.

A `UserStatusType` string-backed enum encodes the allowed values (`online`, `idle`, `away`, `dnd`, `offline`) — consistent with how `ChatType`, `ChatColor`, and `ChatEntityType` are modelled.

Target branch: `v3-dev` (already checked out as `claude/fix-b24-issue-430-kUadi`).

---

## Files to Create

### 1. `src/Services/IM/User/UserStatusType.php`

```php
namespace Bitrix24\SDK\Services\IM\User;

enum UserStatusType: string
{
    case Online  = 'online';
    case Idle    = 'idle';
    case Away    = 'away';
    case Dnd     = 'dnd';
    case Offline = 'offline';
}
```

### 2. `src/Services/IM/User/Result/UserStatusResult.php`

```php
namespace Bitrix24\SDK\Services\IM\User\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class UserStatusResult extends AbstractResult
{
    /** @throws BaseException */
    public function status(): string
    {
        return (string)$this->getCoreResponse()->getResponseData()->getResult()[0];
    }
}
```

### 3. `src/Services/IM/User/Service/UserStatus.php`

```php
namespace Bitrix24\SDK\Services\IM\User\Service;

#[ApiServiceMetadata(new Scope(['im']))]
class UserStatus extends AbstractService
{
    #[ApiEndpointMetadata('im.user.status.get', '...', 'Get current user status')]
    public function get(): UserStatusResult { ... }

    #[ApiEndpointMetadata('im.user.status.set', '...', 'Set current user status')]
    public function set(UserStatusType $status): UpdatedItemResult { ... }

    #[ApiEndpointMetadata('im.user.status.idle.start', '...', 'Enable automatic Away status')]
    public function idleStart(): UpdatedItemResult { ... }

    #[ApiEndpointMetadata('im.user.status.idle.end', '...', 'Disable automatic Away status')]
    public function idleEnd(): UpdatedItemResult { ... }
}
```

### 4. `tests/Unit/Services/IM/User/Service/UserStatusTest.php`

Mirrors `tests/Unit/Services/IM/Chat/Service/ChatTest.php`. Uses `NullCore` + `NullLogger`.
Contains `testServiceInstantiates()`.

### 5. `tests/Integration/Services/IM/User/Service/UserStatusTest.php`

Tests all four methods against a real portal:
- `testGet()` — calls `get()`, asserts result is a non-empty string
- `testSet()` — calls `set(UserStatusType::Online)`, asserts `isSuccess()`
- `testIdleStart()` — calls `idleStart()`, asserts `isSuccess()`
- `testIdleEnd()` — calls `idleEnd()`, asserts `isSuccess()`

`setUp()` uses `Factory::getServiceBuilder()->getIMScope()->userStatus()`.

---

## Files to Modify

### 1. `src/Services/IM/IMServiceBuilder.php`

Add method (mirrors `chat()` / `message()` pattern):

```php
use Bitrix24\SDK\Services\IM\User\Service\UserStatus;

public function userStatus(): UserStatus
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new UserStatus($this->core, $this->log);
    }
    return $this->serviceCache[__METHOD__];
}
```

### 2. `phpunit.xml.dist`

Add inside the `<testsuites>` block, after the `integration_tests_im_message` suite:

```xml
<testsuite name="integration_tests_im_user_status">
    <directory>./tests/Integration/Services/IM/User/</directory>
</testsuite>
```

### 3. `Makefile`

Add after the `test-integration-im-message` target:

```makefile
.PHONY: test-integration-im-user-status
test-integration-im-user-status:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_user_status
```

Also add a line to the help echo block:

```
@echo "test-integration-im-user-status - run IM UserStatus integration tests"
```

### 4. `CHANGELOG.md`

Under `## 3.2.0 – UNRELEASED` → `### Added`:

```markdown
- Added `Bitrix24\SDK\Services\IM\User\Service\UserStatus` service wrapping `im.user.status.get`, `im.user.status.set`, `im.user.status.idle.start`, and `im.user.status.idle.end`, with `UserStatusType` enum and `UserStatusResult`; exposed via `IMServiceBuilder::userStatus()` ([#430](https://github.com/bitrix24/b24phpsdk/issues/430))
```

---

## Deptrac compliance

`UserStatus` service lives in `Services` layer. It imports:
- `Core\Credentials\Scope` — allowed (Services → Core)
- `Core\Exceptions\*` — allowed
- `Core\Result\AbstractResult`, `UpdatedItemResult` — allowed
- `Services\AbstractService` — allowed

No cross-service imports. No Infrastructure or Application imports. No new `skip_violations` entries needed.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-im-user-status
```
