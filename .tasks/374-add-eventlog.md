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

Extends `AbstractItem` and **overrides `__get()`** with typed casting (following the
`AbstractCrmItem` pattern — not just PHPDoc annotations, but real PHP type conversion).

#### Field → PHP type mapping

| Field | API type | PHP type | Casting rule |
|-------|----------|----------|--------------|
| `id` | integer | `int` | `(int)$this->data[$offset]` |
| `timestampX` | datetime (ISO 8601 / DATE_ATOM) | `CarbonImmutable` | `CarbonImmutable::createFromFormat(DATE_ATOM, $this->data[$offset])` |
| `severity` | string | `string\|null` | raw (e.g. `"SECURITY"`, `"INFO"`, `"WARNING"`, `"ERROR"`) |
| `auditTypeId` | string | `string\|null` | raw (e.g. `"USER_AUTHORIZE"`) |
| `moduleId` | string | `string\|null` | raw |
| `itemId` | string | `string\|null` | raw |
| `remoteAddr` | string | `string\|null` | raw |
| `userAgent` | string | `string\|null` | raw |
| `requestUri` | string | `string\|null` | raw |
| `siteId` | string | `string\|null` | raw |
| `userId` | integer | `int\|null` | `(int)` only when not empty/null |
| `guestId` | integer | `int\|null` | `(int)` only when not empty/null |
| `description` | string (JSON) | `string\|null` | raw |

#### `__get()` implementation pattern (from `AbstractCrmItem`):
```php
public function __get($offset)
{
    return match ($offset) {
        'id'     => (int)$this->data[$offset],
        'userId', 'guestId' => ($this->data[$offset] !== null && $this->data[$offset] !== '')
                               ? (int)$this->data[$offset] : null,
        'timestampX' => ($this->data[$offset] !== '' && $this->data[$offset] !== null)
                        ? CarbonImmutable::createFromFormat(DATE_ATOM, $this->data[$offset]) : null,
        default  => $this->data[$offset] ?? null,
    };
}
```

#### PHPDoc annotations:
```php
/**
 * @property-read int                  $id
 * @property-read CarbonImmutable|null $timestampX
 * @property-read string|null          $severity
 * @property-read string|null          $auditTypeId
 * @property-read string|null          $moduleId
 * @property-read string|null          $itemId
 * @property-read string|null          $remoteAddr
 * @property-read string|null          $userAgent
 * @property-read string|null          $requestUri
 * @property-read string|null          $siteId
 * @property-read int|null             $userId
 * @property-read int|null             $guestId
 * @property-read string|null          $description
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
/**
 * Returns a single event log entry by identifier.
 *
 * @see https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-get.html
 *
 * @param positive-int $id     Event log entry identifier
 * @param string[]     $select Fields to return (id, timestampX, severity, auditTypeId,
 *                             moduleId, itemId, remoteAddr, userAgent, requestUri,
 *                             siteId, userId, guestId, description)
 * @throws BaseException
 * @throws TransportException
 */
#[ApiEndpointMetadata(
    'main.eventlog.get',
    'https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-get.html',
    'Returns a single event log entry by identifier.'
)]
public function get(int $id, array $select = []): EventLogResult

/**
 * Returns a list of event log entries by filter conditions.
 *
 * @see https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-list.html
 *
 * @param string[] $select     Fields to return
 * @param array    $filter     Filter conditions: ["field", "operator", value] or ["field", value]
 * @param array    $order      Sort order: ["field" => "ASC"|"DESC"]
 * @param array    $pagination Pagination: ["page" => int, "limit" => int, "offset" => int]
 * @throws BaseException
 * @throws TransportException
 */
#[ApiEndpointMetadata(
    'main.eventlog.list',
    'https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-list.html',
    'Returns a list of event log entries by filter conditions.'
)]
public function list(array $select = [], array $filter = [], array $order = [], array $pagination = []): EventLogsResult

/**
 * Returns new event log entries after a reference cursor point.
 *
 * @see https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-tail.html
 *
 * @param string[] $select Fields to return (required)
 * @param array    $filter Filter conditions (required, pass [] for no filter)
 * @param array    $cursor Cursor: ["field" => "id", "value" => int, "order" => "ASC"|"DESC", "limit" => int]
 * @throws BaseException
 * @throws TransportException
 */
#[ApiEndpointMetadata(
    'main.eventlog.tail',
    'https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-tail.html',
    'Returns new event log entries after a reference cursor point.'
)]
public function tail(array $select, array $filter, array $cursor): EventLogsResult
```

Guard: `get()` must call `$this->guardPositiveId($id)` before making API call.

### 5. Integration Test
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
| `src/Services/CRM/Common/Result/AbstractCrmItem.php` | **Type casting pattern** — override `__get()` with `match`/`switch` |
| `src/Services/Main/Result/EventHandlerItemResult.php` | Item result pattern (no casting — do NOT follow this for EventLog) |
| `src/Services/Main/Result/EventHandlersResult.php` | List result pattern |
| `src/Services/Main/MainServiceBuilder.php` | Where to add `eventLog()` method |
| `src/Core/Result/AbstractResult.php` | Base class for result objects |
| `src/Core/Result/AbstractItem.php` | Base class for item objects |
| `src/Services/AbstractService.php` | Base service class (`guardPositiveId`) |
| `tests/Integration/Services/Main/Service/MainTest.php` | Integration test pattern for Main scope |

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
# 1. Static analysis clean
make lint-phpstan

# 3. Code style clean
make lint-cs-fixer

# 4. Integration tests (requires webhook with 'main' scope + admin user)
make test-integration-main-eventlog

# 5. Deptrac — no new violations
make lint-deptrac
```
