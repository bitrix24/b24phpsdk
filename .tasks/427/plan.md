# Plan: Add IM\Recent service for im.recent.* support (issue #427)

## Context

Issue #427 requests wrapping the following Bitrix24 REST API methods under `src/Services/IM/Recent/`:

| Method | Description |
|---|---|
| `im.recent.get` | Returns a shortened list of recent chats |
| `im.recent.list` | Returns the full recent dialogs list with pagination |
| `im.recent.pin` | Pins or unpins a dialog at the top of the list |
| `im.recent.unread` | Sets or removes the "unread" mark on a chat |
| `im.recent.hide` | Removes a chat from the recent list |

All methods belong to the `im` scope (same as `im.dialog.*`, `im.counters.*` etc.).
This is a v3 branch feature (`v3-dev` base).

**Response envelope notes:**
- `im.recent.get` and `im.recent.list` return an array of recent items at the top-level `result` key (same pattern as `im.user.list.get` → `UsersResult`).
- `im.recent.pin`, `im.recent.unread`, `im.recent.hide` return a boolean result (`getResult()[0]`) — same pattern as `UpdatedItemResult` from Core.

**Known parameters (based on API docs and IM scope conventions):**
- `im.recent.list`: `LAST_ID` (int, pagination cursor), `LIMIT` (int)
- `im.recent.get`: no required parameters
- `im.recent.pin`: `DIALOG_ID` (string), `PIN` ('Y'/'N')
- `im.recent.unread`: `DIALOG_ID` (string), `ACTION` ('mark'/'unmark')
- `im.recent.hide`: `DIALOG_ID` (string)

**RecentItemResult fields** (based on IM API conventions; integration test validates completeness):
`id`, `type`, `avatar`, `color`, `title`, `counter`, `unread`, `pinned`, `user_id`, `chat_id`, `message`, `date_message`

---

## Files to Create

### 1. `src/Services/IM/Recent/Result/RecentItemResult.php`

```php
namespace Bitrix24\SDK\Services\IM\Recent\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read string $id
 * @property-read string $type
 * @property-read string $avatar
 * @property-read string $color
 * @property-read string $title
 * @property-read int $counter
 * @property-read bool $unread
 * @property-read bool $pinned
 * @property-read int $user_id
 * @property-read int $chat_id
 * @property-read array $message
 * @property-read CarbonImmutable|null $date_message
 */
class RecentItemResult extends AbstractAnnotatedItem {}
```

### 2. `src/Services/IM/Recent/Result/RecentsResult.php`

Used for both `im.recent.get` and `im.recent.list`. Returns `RecentItemResult[]` from a flat array at `result`.

### 3. `src/Services/IM/Recent/Service/Recent.php`

Service class with 5 methods:
- `get(?int $lastId, ?int $limit): RecentsResult`
- `list(?int $lastId, ?int $limit): RecentsResult`
- `pin(string $dialogId, bool $pin): UpdatedItemResult`
- `unread(string $dialogId, string $action): UpdatedItemResult`
- `hide(string $dialogId): UpdatedItemResult`

### 4. `tests/Unit/Services/IM/Recent/Service/RecentTest.php`

```php
#[CoversClass(Recent::class)]
class RecentTest extends TestCase {
    public function testServiceInstantiates(): void { ... }
}
```

### 5. `tests/Integration/Services/IM/Recent/Service/RecentTest.php`

Tests for `get()`, `list()`, `pin()`, `unread()`, `hide()` using a real portal.

### 6. `tests/Integration/Services/IM/Recent/Result/RecentItemResultTest.php`

Annotation and type-cast tests via `CustomBitrix24Assertions`.

---

## Files to Modify

### 1. `src/Services/IM/IMServiceBuilder.php`

Add method:
```php
public function recent(): Recent
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Recent($this->core, $this->log);
    }
    return $this->serviceCache[__METHOD__];
}
```
Add `use Bitrix24\SDK\Services\IM\Recent\Service\Recent;` import.

### 2. `phpunit.xml.dist`

Add after `integration_tests_im_counters`:
```xml
<testsuite name="integration_tests_im_recent">
    <directory>./tests/Integration/Services/IM/Recent/</directory>
</testsuite>
```

### 3. `Makefile`

Add after `test-integration-im-counters` target:
```makefile
.PHONY: test-integration-im-recent
test-integration-im-recent:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_recent
```
Also add `test-integration-im-recent` to the help echo block.

### 4. `CHANGELOG.md`

Under `## 3.2.0 – UNRELEASED` → `### Added`:
```markdown
- Added `Bitrix24\SDK\Services\IM\Recent\Service\Recent` service wrapping `im.recent.get`, `im.recent.list`, `im.recent.pin`, `im.recent.unread`, and `im.recent.hide`, with `RecentItemResult` and `IMServiceBuilder::recent()` accessor ([#427](https://github.com/bitrix24/b24phpsdk/issues/427))
```

---

## Deptrac compliance

- `Recent` service extends `AbstractService` from `Core`
- `RecentItemResult` extends `AbstractAnnotatedItem` from `Core`
- `RecentsResult` extends `AbstractResult` from `Core`
- `pin`/`unread`/`hide` reuse `UpdatedItemResult` from `Core`
- No imports from `Infrastructure` or cross-scope `Services`
- All imports are `Core`-only → no new deptrac violations

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-im-recent
```
