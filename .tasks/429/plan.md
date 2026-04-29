# Plan: Add IM\User service for im.user.get and im.user.list.get (issue #429)

## Context

Issue #429 asks for a new `Bitrix24\SDK\Services\IM\User\Service\User` service wrapping two IM REST methods:

- `im.user.get` — returns profile data for the current user (no params) or for a specified user ID (`ID` param). Response: `result` contains a single user object (assoc array, not a list).
- `im.user.list.get` — returns profile data for a list of user IDs (`ID` array param). Response: `result` is an associative hash keyed by user ID (string keys), each value is a user object array.

API version: the branch is based on `v3-dev` and the CHANGELOG entry targets `3.2.0 – UNRELEASED`.

**Field reference**: `DialogUserItemResult` (generated from `im.dialog.users.list` payload) contains the full user profile field set: `id`, `active`, `name`, `first_name`, `last_name`, `work_position`, `color`, `avatar`, `avatar_hr`, `gender`, `birthday`, `extranet`, `network`, `bot`, `connector`, `external_auth_id`, `status`, `idle`, `last_activity_date`, `mobile_last_date`, `desktop_last_date`, `absent`, `departments`, `phones`, `bot_data`, `type`, `website`, `email`. `im.user.get` returns the same shape. `UserItemResult` will mirror these annotations.

**Result envelope for `im.user.get`**: `result` is a single assoc array (not a list) — same as `DialogResult` pattern: check `!array_is_list($result)`.

**Result envelope for `im.user.list.get`**: `result` is `["1" => [...], "2" => [...]]` — a hash keyed by string user IDs. Use `array_values(array_filter(..., 'is_array'))` to extract user objects.

**Generator note**: Docker is not running in this environment, so `b24-dev:result-item-generator` cannot be executed. `UserItemResult` will be written manually, mirroring `DialogUserItemResult` field annotations.

---

## Files to Create

### 1. `src/Services/IM/User/Result/UserItemResult.php`

```php
namespace Bitrix24\SDK\Services\IM\User\Result;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int $id
 * @property-read bool $active
 * ... (all fields from DialogUserItemResult)
 */
class UserItemResult extends AbstractAnnotatedItem {}
```

### 2. `src/Services/IM/User/Result/UserResult.php`

Wraps single `im.user.get` response. `user()` method mirrors `DialogResult::dialog()`.

### 3. `src/Services/IM/User/Result/UsersResult.php`

Wraps `im.user.list.get` response. `users()` iterates over hash values.

### 4. `src/Services/IM/User/Service/User.php`

```php
#[ApiServiceMetadata(new Scope(['im']))]
class User extends AbstractService
{
    #[ApiEndpointMetadata('im.user.get', ...)]
    public function get(?int $userId = null): UserResult { ... }

    #[ApiEndpointMetadata('im.user.list.get', ...)]
    public function listGet(array $userIds): UsersResult { ... }
}
```

### 5. `tests/Unit/Services/IM/User/Service/UserTest.php`

Covers `User::get()` (with and without ID) and `User::listGet()` using `CoreInterface` mock.

### 6. `tests/Integration/Services/IM/User/Service/UserTest.php`

Tests `get()` (current user and by ID) and `listGet()` against live portal. Uses `Factory::getServiceBuilder()->getIMScope()->user()`.

### 7. `tests/Integration/Services/IM/User/Result/UserItemResultTest.php`

Two methods: `testAllSystemFieldsAnnotated` and `testAllSystemFieldsHasValidTypeAnnotation`.

---

## Files to Modify

### 1. `src/Services/IM/IMServiceBuilder.php`

Add `user(): User` method with service cache, following `dialog()` pattern. Add `use` import.

### 2. `phpunit.xml.dist`

Add after `integration_tests_im_dialog` suite:
```xml
<testsuite name="integration_tests_im_user">
    <directory>./tests/Integration/Services/IM/User/</directory>
</testsuite>
```

### 3. `Makefile`

Add after `test-integration-im-dialog` target:
```makefile
.PHONY: test-integration-im-user
test-integration-im-user:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_user
```

Also add echo line to help target.

### 4. `CHANGELOG.md`

Under `## 3.2.0 – UNRELEASED` → `### Added`:
```markdown
- Added `Bitrix24\SDK\Services\IM\User\Service\User` service for `im.user.get` and `im.user.list.get` support, with typed result wrappers `UserResult`/`UsersResult`/`UserItemResult`, `IMServiceBuilder::user()` accessor, and unit/integration/annotation test coverage ([#429](https://github.com/bitrix24/b24phpsdk/issues/429))
```

---

## Deptrac compliance

New code only imports from `Core` (`AbstractService`, `AbstractResult`, `AbstractAnnotatedItem`, `Scope`, `BaseException`, `TransportException`) and PHP standard library. No cross-service imports. No new `skip_violations` needed.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-im-user
```

---

## Plan review

✓ Unambiguity — every file path, class name, method name, and namespace is explicit; result envelope handling for both hash and single-object response shapes is specified.
✓ Non-contradiction — namespace `IM\User` is consistent across source, tests, and builder registration; `UserItemResult` annotations match `DialogUserItemResult` field set which is the known live payload.
✓ No gaps — all acceptance criteria from issue #429 are addressed: service class, builder accessor, result item, unit tests, integration tests, annotation test, phpunit suite, Makefile target, CHANGELOG entry.
