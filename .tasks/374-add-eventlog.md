# Plan: Implement `main.eventlog.*` Service (REST v3)

## Context

The `feature/374-add-eventlog` branch adds support for the Bitrix24 REST v3 Event Log API.
This is the **first REST v3 service** in the SDK. The v3 API uses a different URL path
(`/rest/api/{user_id}/{token}/method`) and a different response envelope:

- **`get`**: `{ "result": { "item": {...} }, "time": {...} }`
- **`list`/`tail`**: `{ "result": { "items": [...] }, "time": {...} }`

The existing `Response::getResponseData()` already handles this — after parsing,
`getResult()` returns `['item' => [...]]` or `['items' => [...]]`, so result classes
access the nested key explicitly.

**Scope required:** `main`
**Access:** administrator only

---

## API Methods to Implement

| Method | Description |
|--------|-------------|
| `main.eventlog.get` | Returns a single event log entry by ID |
| `main.eventlog.list` | Returns a list of entries (filter, select, order, pagination) |
| `main.eventlog.tail` | Returns new entries after a cursor point (polling/sync use case) |

**Event log item fields:** `id`, `timestampX`, `severity`, `auditTypeId`, `moduleId`,
`itemId`, `remoteAddr`, `userAgent`, `requestUri`, `siteId`, `userId`, `guestId`, `description`

---

## Files to Create

### 1. Result: Item
`src/Services/Main/Result/EventLogItemResult.php`
```php
/**
 * @property-read int    $id
 * @property-read string $timestampX
 * @property-read string $severity
 * @property-read string $auditTypeId
 * @property-read string $moduleId
 * @property-read string $itemId
 * @property-read string $remoteAddr
 * @property-read string $userAgent
 * @property-read string $requestUri
 * @property-read string $siteId
 * @property-read int    $userId
 * @property-read int    $guestId
 * @property-read string $description
 */
class EventLogItemResult extends AbstractItem {}
```

### 2. Result: Single response (for `get`)
`src/Services/Main/Result/EventLogResult.php`
- Extends `AbstractResult`
- Method `eventLogItem(): EventLogItemResult`
- Access: `$this->getCoreResponse()->getResponseData()->getResult()['item']`

### 3. Result: List response (for `list` and `tail`)
`src/Services/Main/Result/EventLogsResult.php`
- Extends `AbstractResult`
- Method `getEventLogItems(): EventLogItemResult[]`
- Access: `$this->getCoreResponse()->getResponseData()->getResult()['items']`

### 4. Service
`src/Services/Main/Service/EventLog.php`
- Extends `AbstractService`
- `#[ApiServiceMetadata(new Scope(['main']))]`
- No `Batch` (like `Event` service, no batch wrapper needed)

Methods:
```php
#[ApiEndpointMetadata('main.eventlog.get', 'https://apidocs.bitrix24.com/...', '...')]
public function get(int $id, array $select = []): EventLogResult

#[ApiEndpointMetadata('main.eventlog.list', 'https://apidocs.bitrix24.com/...', '...')]
public function list(array $select = [], array $filter = [], array $order = [], array $pagination = []): EventLogsResult

#[ApiEndpointMetadata('main.eventlog.tail', 'https://apidocs.bitrix24.com/...', '...')]
public function tail(array $select, array $filter, array $cursor): EventLogsResult
```

Guard: `get()` must call `$this->guardPositiveId($id)` before making API call.

### 5. Unit Test
`tests/Unit/Services/Main/Service/EventLogTest.php`
- `#[CoversClass(EventLog::class)]`
- Extends `TestCase`
- Uses `NullCore` (no HTTP calls)
- Tests: service instantiation, that methods return correct result types

### 6. Integration Test
`tests/Integration/Services/Main/Service/EventLogTest.php`
- Uses `Factory::getServiceBuilder()->getMainScope()->eventLog()`
- Tests: `get()` with a real ID, `list()` with a filter, `tail()` with a cursor
- `tearDown()` not needed (read-only API)

---

## Files to Modify

### 1. `src/Services/Main/MainServiceBuilder.php`
Add factory method:
```php
public function eventLog(): EventLog
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new EventLog($this->core, $this->log);
    }
    return $this->serviceCache[__METHOD__];
}
```

### 2. `phpunit.xml.dist`
Add test suite:
```xml
<testsuite name="integration_tests_scope_main_eventlog">
    <directory>./tests/Integration/Services/Main/</directory>
</testsuite>
```

### 3. `Makefile`
Add target:
```makefile
.PHONY: test-integration-main-eventlog
test-integration-main-eventlog:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_main_eventlog
```

---

## Critical Files (reference during implementation)

| File | Purpose |
|------|---------|
| `src/Services/Main/Service/Event.php` | Pattern to follow (no Batch, simple service) |
| `src/Services/Main/Result/EventHandlerItemResult.php` | Item result pattern |
| `src/Services/Main/Result/EventHandlersResult.php` | List result pattern |
| `src/Services/Main/MainServiceBuilder.php` | Where to add `eventLog()` method |
| `src/Core/Result/AbstractResult.php` | Base class for result objects |
| `src/Core/Result/AbstractItem.php` | Base class for item objects |
| `src/Services/AbstractService.php` | Base service class (`guardPositiveId`) |
| `tests/Unit/Services/Main/MainServiceBuilderTest.php` | Test pattern for builder |

---

## Response Data Access Pattern

The v3 envelope differs from v1. After `Response::getResponseData()`:

```php
// main.eventlog.get → { "result": { "item": {...} } }
$data = $this->getCoreResponse()->getResponseData()->getResult();
// $data = ['item' => [...field values...]]
return new EventLogItemResult($data['item']);

// main.eventlog.list / main.eventlog.tail → { "result": { "items": [...] } }
$data = $this->getCoreResponse()->getResponseData()->getResult();
// $data = ['items' => [[...], [...]]]
foreach ($data['items'] as $item) {
    $res[] = new EventLogItemResult($item);
}
```

---

## Verification

```bash
# 1. Unit tests pass
make test-unit

# 2. Static analysis clean
make lint-phpstan

# 3. Code style clean
make lint-cs-fixer

# 4. Integration tests (requires webhook with 'main' scope + admin user)
make test-integration-main-eventlog

# 5. Deptrac — no new violations
make lint-deptrac
```
