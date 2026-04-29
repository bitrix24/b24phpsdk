# Plan: Extend IM\Notify service with missing im.notify.* methods (issue #428)

## Context

The existing `Bitrix24\SDK\Services\IM\Notify\Service\Notify` wraps 6 of the 12
`im.notify.*` REST API methods. Six are missing and two existing ones call the wrong
API method (`im.notify.read` instead of `im.notify.read.list`).

**API version**: v3 (milestone 3.2.0), base branch `v3-dev`.

### Methods to add

| SDK method | REST method | Notes |
|---|---|---|
| `send()` | `im.notify` | App-context notification; returns ID or false |
| `getList()` | `im.notify.get` | Paginated list; complex result with notifications[], users[] |
| `historySearch()` | `im.notify.history.search` | Search; same shape as getList but `total_results` instead of `total_count` |
| `markAllAsRead()` | `im.notify.read.all` | No params; returns bool + newCounter |
| `getSchema()` | `im.notify.schema.get` | No params; returns keyed map of modules/types |

### Methods to refactor

| SDK method | Old REST call | New REST call |
|---|---|---|
| `markMessagesAsRead()` | `im.notify.read` | `im.notify.read.list` |
| `markMessagesAsUnread()` | `im.notify.read` | `im.notify.read.list` |

### Key API response shapes

**im.notify.get** → `result.total_count`, `result.total_unread_count`, `result.chat_id`,
`result.notifications[]`, `result.users[]`.
Notification item fields: `id`, `chat_id`, `author_id`, `date` (ISO 8601), `notify_type`,
`notify_module`, `notify_event`, `notify_tag`, `notify_sub_tag`, `notify_title`,
`setting_name`, `text`, `notify_read`, `notify_buttons`, `params`.

**im.notify.history.search** → same notification item shape as above; top-level has
`result.chat_id`, `result.total_results`, `result.notifications[]`, `result.users[]`.

**im.notify.read.all** → `result.result` (bool), `result.newCounter` (int).

**im.notify.schema.get** → keyed map `{ MODULE_ID: { name, module_id, list: [{id, name}] } }`.

**im.notify** (send) → `result` is integer (created notification ID) or false.

### Generator note

Docker is unavailable in this environment; `result-item-generator` cannot be run.
Result item classes (`NotifyItemResult`, `NotifySchemaItemResult`) are written manually
following the same conventions the generator produces (extending `AbstractAnnotatedItem`,
`@property-read` PHPDoc).

---

## Files to Create

### 1. `src/Services/IM/Notify/Result/NotifyItemResult.php`

Extends `AbstractAnnotatedItem`. Represents one notification entry from `im.notify.get`
and `im.notify.history.search`.

```php
namespace Bitrix24\SDK\Services\IM\Notify\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int $id
 * @property-read int $chat_id
 * @property-read int $author_id
 * @property-read CarbonImmutable $date
 * @property-read int $notify_type
 * @property-read string $notify_module
 * @property-read string $notify_event
 * @property-read string $notify_tag
 * @property-read string $notify_sub_tag
 * @property-read string $notify_title
 * @property-read string $setting_name
 * @property-read string $text
 * @property-read string $notify_read
 * @property-read string|null $notify_buttons
 * @property-read array|null $params
 */
class NotifyItemResult extends AbstractAnnotatedItem {}
```

### 2. `src/Services/IM/Notify/Result/NotifiesResult.php`

Wraps the `im.notify.get` response. Extends `AbstractResult`.

```php
namespace Bitrix24\SDK\Services\IM\Notify\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class NotifiesResult extends AbstractResult
{
    /** @return NotifyItemResult[] */
    public function notifications(): array { … }
    public function totalCount(): int { … }
    public function totalUnreadCount(): int { … }
    public function chatId(): int { … }
    /** @return array<int, array<string, mixed>> */
    public function users(): array { … }
}
```

### 3. `src/Services/IM/Notify/Result/NotifyHistorySearchResult.php`

Wraps `im.notify.history.search`. Same pattern as `NotifiesResult` but exposes
`totalResults()` instead of `totalCount()`/`totalUnreadCount()`.

```php
class NotifyHistorySearchResult extends AbstractResult
{
    /** @return NotifyItemResult[] */
    public function notifications(): array { … }
    public function totalResults(): int { … }
    public function chatId(): int { … }
    /** @return array<int, array<string, mixed>> */
    public function users(): array { … }
}
```

### 4. `src/Services/IM/Notify/Result/NotifyReadAllResult.php`

Wraps `im.notify.read.all`. Response envelope: `result.result` (bool) + `result.newCounter` (int).

```php
class NotifyReadAllResult extends AbstractResult
{
    public function isSuccess(): bool { … }
    public function newCounter(): int { … }
}
```

### 5. `src/Services/IM/Notify/Result/NotifySchemaItemResult.php`

Extends `AbstractAnnotatedItem`. Represents one module entry from `im.notify.schema.get`.

```php
/**
 * @property-read string $name
 * @property-read string $module_id
 * @property-read array $list
 */
class NotifySchemaItemResult extends AbstractAnnotatedItem {}
```

### 6. `src/Services/IM/Notify/Result/NotifySchemaResult.php`

Wraps `im.notify.schema.get`. Iterates the keyed map and returns typed items.

```php
class NotifySchemaResult extends AbstractResult
{
    /** @return NotifySchemaItemResult[] */
    public function schema(): array { … }
}
```

### 7. `tests/Unit/Services/IM/Notify/Service/NotifyTest.php`

`#[CoversClass(Notify::class)]`, `final class NotifyTest extends TestCase`.

Test methods (each mocks `CoreInterface` and asserts the correct REST method name and parameters):
- `testSendCallsImNotify()` — asserts `im.notify` called with USER_ID, MESSAGE, TYPE, etc.
- `testGetListCallsImNotifyGet()` — asserts `im.notify.get` with LAST_ID, LAST_TYPE, LIMIT
- `testHistorySearchCallsImNotifyHistorySearch()` — asserts correct method + params incl. CarbonImmutable date args
- `testMarkAllAsReadCallsImNotifyReadAll()` — asserts `im.notify.read.all` with no params
- `testMarkMessagesAsReadCallsImNotifyReadList()` — asserts `im.notify.read.list` (regression for the refactor)
- `testMarkMessagesAsUnreadCallsImNotifyReadList()` — asserts `im.notify.read.list` with `ACTION=N`
- `testGetSchemaCallsImNotifySchemaGet()` — asserts `im.notify.schema.get` with no params

### 8. `tests/Integration/Services/IM/Notify/Service/NotifyTest.php`

`#[CoversClass(Notify::class)]`. Extends `TestCase`. Uses `Factory::getServiceBuilder()->getIMScope()->notify()`.

Test methods:
- `testSend()` — sends with TYPE=USER, asserts result ID > 0
- `testGetList()` — calls `getList()`, asserts `notifications()` returns array
- `testHistorySearch()` — calls `historySearch()` with SEARCH_TEXT, asserts result
- `testMarkAllAsRead()` — calls `markAllAsRead()`, asserts `isSuccess()` === true
- `testMarkMessagesAsRead()` — creates notifications, calls `markMessagesAsRead()`
- `testMarkMessagesAsUnread()` — creates notifications, marks read then unread
- `testGetSchema()` — calls `getSchema()`, asserts `schema()` returns non-empty array

### 9. `tests/Integration/Services/IM/Notify/Result/NotifyItemResultAnnotationsTest.php`

`#[CoversClass(NotifyItemResult::class)]`. Uses `CustomBitrix24Assertions` trait.

- `testAllSystemFieldsAnnotated()` — fetches raw notification item via `getList()`, checks all fields annotated
- `testAllSystemFieldsHasValidTypeAnnotation()` — checks type-cast match on `NotifyItemResult`

### 10. `tests/Integration/Services/IM/Notify/Result/NotifySchemaItemResultAnnotationsTest.php`

`#[CoversClass(NotifySchemaItemResult::class)]`. Uses `CustomBitrix24Assertions` trait.

- `testAllSystemFieldsAnnotated()` — fetches raw schema item via `getSchema()`, checks all fields annotated
- `testAllSystemFieldsHasValidTypeAnnotation()` — checks type-cast match on `NotifySchemaItemResult`

---

## Files to Modify

### 1. `src/Services/IM/Notify/Service/Notify.php`

Add 5 new public methods and refactor 2 existing ones:

**New imports to add**:
```php
use Bitrix24\SDK\Services\IM\Notify\Result\NotifiesResult;
use Bitrix24\SDK\Services\IM\Notify\Result\NotifyHistorySearchResult;
use Bitrix24\SDK\Services\IM\Notify\Result\NotifyReadAllResult;
use Bitrix24\SDK\Services\IM\Notify\Result\NotifySchemaResult;
use Carbon\CarbonImmutable;
```

**New methods** (append after `answer()`):
- `send(int $userId, string $message, string $type = 'USER', ?string $forEmailChannelMessage = null, ?string $notificationTag = null, ?string $subTag = null, ?array $attachment = null): AddedItemResult`
  → calls `im.notify`, maps TYPE/USER_ID/MESSAGE/MESSAGE_OUT/TAG/SUB_TAG/ATTACH
- `getList(?int $lastId = null, ?int $lastType = null, int $limit = 50): NotifiesResult`
  → calls `im.notify.get`
- `historySearch(?string $searchText = null, ?array $searchTypes = null, ?CarbonImmutable $searchDateFrom = null, ?CarbonImmutable $searchDateTo = null, ?array $searchAuthors = null, ?int $lastId = null, int $limit = 50): NotifyHistorySearchResult`
  → calls `im.notify.history.search`; converts CarbonImmutable to ISO 8601 string
- `markAllAsRead(): NotifyReadAllResult`
  → calls `im.notify.read.all`
- `getSchema(): NotifySchemaResult`
  → calls `im.notify.schema.get`

**Refactored methods** — change only the REST method name string and `#[ApiEndpointMetadata]`:
- `markMessagesAsRead()`: `'im.notify.read'` → `'im.notify.read.list'`
- `markMessagesAsUnread()`: `'im.notify.read'` → `'im.notify.read.list'`

Doc link base for all new `#[ApiEndpointMetadata]`: `https://apidocs.bitrix24.com/api-reference/chats/notifications/`

### 2. `phpunit.xml.dist`

Add inside `<testsuites>`, after the last IM suite entry:

```xml
<testsuite name="integration_tests_im_notify">
    <directory>./tests/Integration/Services/IM/Notify/</directory>
</testsuite>
```

### 3. `Makefile`

Add after the existing `test-integration-im-dialog` block:

```makefile
.PHONY: test-integration-im-notify
test-integration-im-notify:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_notify
```

Also add description line in the help section:
```
echo "test-integration-im-notify - run IM Notify integration tests"
```

### 4. `CHANGELOG.md`

Under `## 3.2.0 – UNRELEASED` → `### Added`:
```markdown
- Extended `IM\Notify` service with `send`, `getList`, `historySearch`, `markAllAsRead`, `getSchema` methods and refactored `markMessagesAsRead`/`markMessagesAsUnread` to use `im.notify.read.list` ([#428](https://github.com/bitrix24/b24phpsdk/issues/428))
```

---

## Deptrac compliance

All new code lives in `Services\IM\Notify\` (Services layer). It imports only from
`Core\Result\`, `Core\Exceptions\`, and `Carbon\CarbonImmutable` (external vendor).
No cross-service imports; no Application or Infrastructure imports. Zero new violations expected.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-im-notify
```
