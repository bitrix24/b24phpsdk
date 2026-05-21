# Plan: Add IM\ChatUser service for im.chat.user.* support (issue #424)

## Context

Issue #424 requests a new `ChatUser` service under `src/Services/IM/Chat/Service/` that wraps
three Bitrix24 REST API methods in the `im` scope:

| Method              | Description                          | Returns      |
|---------------------|--------------------------------------|--------------|
| `im.chat.user.add`  | Add participants to a chat           | `true`       |
| `im.chat.user.delete` | Remove a participant from a chat   | `true`       |
| `im.chat.user.list` | List participant user IDs of a chat  | `int[]`      |

**API response shapes:**
- `im.chat.user.add`: `result` is `true`  → `UpdatedItemResult`
- `im.chat.user.delete`: `result` is `true` → `UpdatedItemResult`
- `im.chat.user.list`: `result` is a flat `int[]` of user IDs → custom `ChatUserListResult`

`im.chat.user.add` keeps the Bitrix24 API default of hiding previous chat history from
new participants by default (`HIDE_HISTORY=Y`); callers can pass `false` to expose history.

No `*ItemResult` with `@property-read` annotations is needed for `list` because the response
is a flat integer array, not an associative object. Therefore no annotation integration test
is required.

Branch: `claude/implement-issue-424-fGpaU` (based on `v3-dev`)

---

## Files to Create

### 1. `src/Services/IM/Chat/Result/ChatUserListResult.php`

```php
namespace Bitrix24\SDK\Services\IM\Chat\Result;

class ChatUserListResult extends AbstractResult
{
    /** @return int[] */
    public function getUserIds(): array;
}
```

### 2. `src/Services/IM/Chat/Service/ChatUser.php`

```php
#[ApiServiceMetadata(new Scope(['im']))]
class ChatUser extends AbstractService
{
    public function add(int $chatId, array $userIds, bool $hideHistory = true): UpdatedItemResult;
    public function delete(int $chatId, int $userId): UpdatedItemResult;
    public function list(int $chatId): ChatUserListResult;
}
```

### 3. `tests/Unit/Services/IM/Chat/Service/ChatUserTest.php`

Unit tests verify `im.chat.user.add` payload mapping for the default hidden-history
case (`HIDE_HISTORY=Y`) and the explicit visible-history case (`HIDE_HISTORY=N`).

### 4. `tests/Integration/Services/IM/Chat/Service/ChatUserTest.php`

Integration test covering all three methods:
- `testAdd` — creates a chat, calls `chatUser()->add()`, asserts success
- `testDelete` — creates a chat, adds a user, calls `chatUser()->delete()`, asserts success
- `testList` — creates a chat with known users, calls `chatUser()->list()`, asserts returned IDs include expected ones

---

## Files to Modify

### 1. `src/Services/IM/IMServiceBuilder.php`

Add `chatUser(): ChatUser` method following the `chat()` pattern (service cache).

### 2. `phpunit.xml.dist`

Add inside `<testsuites>` after `integration_tests_im_chat`:

```xml
<testsuite name="integration_tests_im_chat_user">
    <directory>./tests/Integration/Services/IM/Chat/Service/ChatUser*</directory>
</testsuite>
```

### 3. `Makefile`

Add target after `test-integration-im-chat:`:

```makefile
.PHONY: test-integration-im-chat-user
test-integration-im-chat-user:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_chat_user
```

### 4. `CHANGELOG.md`

Under `## 3.2.0 – UNRELEASED` → `### Added`:

```markdown
- Added `Bitrix24\SDK\Services\IM\Chat\Service\ChatUser` service wrapping `im.chat.user.add`, `im.chat.user.delete`, `im.chat.user.list` for chat participant management, with `ChatUserListResult` and `IMServiceBuilder::chatUser()` accessor ([#424](https://github.com/bitrix24/b24phpsdk/issues/424))
```

---

## Deptrac compliance

- `ChatUser` service is in `Services` layer → may depend on `Core` and `Application` only ✓
- `ChatUserListResult` is in `Services` layer → depends on `Core\Result\AbstractResult` only ✓
- No imports from `Infrastructure` or other service scopes ✓

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-im-chat-user
```
