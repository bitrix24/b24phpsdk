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

### 0. Core: SortOrder Enum
`src/Core/Contracts/SortOrder.php`

No sort direction enum exists in the codebase — `ASC`/`DESC` are hardcoded as raw strings
throughout (including `Core/Batch.php`, `Department`, and the `array<string, 'asc'|'desc'|'ASC'|'DESC'>`
annotation in Sale). Created alongside `ApiVersion` as a shared Core-level contract.

```php
namespace Bitrix24\SDK\Core\Contracts;

enum SortOrder: string
{
    case Ascending  = 'ASC';
    case Descending = 'DESC';
}
```

Used in:
- `EventLogTailCursor` — constructor parameter `SortOrder $order = SortOrder::Ascending`
- Future services — easy refactor of existing raw string usages

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

### 4. Select Builder
`src/Services/Main/Service/EventLogSelectBuilder.php`
- Extends `AbstractSelectBuilder` (`src/Services/AbstractSelectBuilder.php`)
- Constructor auto-includes `'id'`
- One method per selectable field, each returns `self` for fluent chaining

```php
class EventLogSelectBuilder extends AbstractSelectBuilder
{
    public function __construct() { $this->select[] = 'id'; }

    public function timestampX(): self  { $this->select[] = 'timestampX';  return $this; }
    public function severity(): self    { $this->select[] = 'severity';    return $this; }
    public function auditTypeId(): self { $this->select[] = 'auditTypeId'; return $this; }
    public function moduleId(): self    { $this->select[] = 'moduleId';    return $this; }
    public function itemId(): self      { $this->select[] = 'itemId';      return $this; }
    public function remoteAddr(): self  { $this->select[] = 'remoteAddr';  return $this; }
    public function userAgent(): self   { $this->select[] = 'userAgent';   return $this; }
    public function requestUri(): self  { $this->select[] = 'requestUri';  return $this; }
    public function siteId(): self      { $this->select[] = 'siteId';      return $this; }
    public function userId(): self      { $this->select[] = 'userId';      return $this; }
    public function guestId(): self     { $this->select[] = 'guestId';     return $this; }
    public function description(): self { $this->select[] = 'description'; return $this; }
}
```

Usage: `(new EventLogSelectBuilder())->severity()->timestampX()->userId()->buildSelect()`

### 5. Filter Builder
`src/Services/Main/Service/EventLogFilter.php`
- Extends `AbstractFilterBuilder` (`src/Filters/AbstractFilterBuilder.php`)
- Fields grouped by type, returns typed `*FieldConditionBuilder` per field

```php
class EventLogFilter extends AbstractFilterBuilder
{
    // Identifiers
    public function id(): IntFieldConditionBuilder
        { return new IntFieldConditionBuilder('id', $this); }

    // Date/time
    public function timestampX(): DateTimeFieldConditionBuilder
        { return new DateTimeFieldConditionBuilder('timestampX', $this); }

    // Integer fields
    public function userId(): IntFieldConditionBuilder
        { return new IntFieldConditionBuilder('userId', $this); }
    public function guestId(): IntFieldConditionBuilder
        { return new IntFieldConditionBuilder('guestId', $this); }

    // String fields
    public function severity(): StringFieldConditionBuilder
        { return new StringFieldConditionBuilder('severity', $this); }
    public function auditTypeId(): StringFieldConditionBuilder
        { return new StringFieldConditionBuilder('auditTypeId', $this); }
    public function moduleId(): StringFieldConditionBuilder
        { return new StringFieldConditionBuilder('moduleId', $this); }
    public function itemId(): StringFieldConditionBuilder
        { return new StringFieldConditionBuilder('itemId', $this); }
    public function remoteAddr(): StringFieldConditionBuilder
        { return new StringFieldConditionBuilder('remoteAddr', $this); }
    public function siteId(): StringFieldConditionBuilder
        { return new StringFieldConditionBuilder('siteId', $this); }
}
```

Usage: `(new EventLogFilter())->timestampX()->gte(new DateTime('-1 day'))->userId()->eq(1)`

### 6. Cursor Value Object
`src/Services/Main/Service/EventLogTailCursor.php`

Immutable value object initialized via constructor, serialized to array for the API call.

API cursor fields:
- `field` — sort field, default `id`
- `order` — `SortOrder::Ascending` or `SortOrder::Descending`, default `SortOrder::Ascending`
- `value` — start value (ID of the last known entry), default `0`
- `limit` — number of entries per iteration, default `50`

```php
use Bitrix24\SDK\Core\Contracts\SortOrder;

class EventLogTailCursor
{
    public function __construct(
        private readonly int       $value,
        private readonly string    $field = 'id',
        private readonly SortOrder $order = SortOrder::Ascending,
        private readonly int       $limit = 50,
    ) {}

    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'order' => $this->order->value,
            'value' => $this->value,
            'limit' => $this->limit,
        ];
    }
}
```

Usage: `new EventLogTailCursor(value: 446313, order: SortOrder::Ascending)`

### 7. Service
`src/Services/Main/Service/EventLog.php`
- Extends `AbstractService`
- `#[ApiServiceMetadata(new Scope(['main']))]`
- No `Batch` (like `Event` service, no batch wrapper needed)
- All calls pass `ApiVersion::v3` as third argument to `$this->core->call()` (see `Task.php:66`)
- `select` and `filter` parameters accept `array|Builder` union type, resolved via `instanceof`

```php
/**
 * Returns a single event log entry by identifier.
 *
 * @see https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-get.html
 *
 * @param positive-int                            $id
 * @param array<int,string>|EventLogSelectBuilder $select
 * @throws BaseException
 * @throws TransportException
 */
#[ApiEndpointMetadata(
    'main.eventlog.get',
    'https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-get.html',
    'Returns a single event log entry by identifier.',
    ApiVersion::v3
)]
public function get(int $id, array|EventLogSelectBuilder $select = []): EventLogResult

/**
 * Returns a list of event log entries by filter conditions.
 *
 * @see https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-list.html
 *
 * @param array<int,string>|EventLogSelectBuilder $select
 * @param array|EventLogFilter                    $filter     Filter conditions (REST 3.0 format)
 * @param array<string,SortOrder>                 $order      ["field" => SortOrder::Ascending]
 * @param array                                   $pagination ["page" => int, "limit" => int, "offset" => int]
 * @throws BaseException
 * @throws TransportException
 */
#[ApiEndpointMetadata(
    'main.eventlog.list',
    'https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-list.html',
    'Returns a list of event log entries by filter conditions.',
    ApiVersion::v3
)]
public function list(
    array|EventLogSelectBuilder $select = [],
    array|EventLogFilter $filter = [],
    array $order = [],
    array $pagination = []
): EventLogsResult

/**
 * Returns new event log entries after a reference cursor point.
 *
 * @see https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-tail.html
 *
 * @param array<int,string>|EventLogSelectBuilder $select (required)
 * @param array|EventLogFilter                    $filter (required, pass [] or new EventLogFilter() for no filter)
 * @param EventLogTailCursor                      $cursor value object with field/order/value/limit
 * @throws BaseException
 * @throws TransportException
 */
#[ApiEndpointMetadata(
    'main.eventlog.tail',
    'https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-tail.html',
    'Returns new event log entries after a reference cursor point.',
    ApiVersion::v3
)]
public function tail(
    array|EventLogSelectBuilder $select,
    array|EventLogFilter $filter,
    EventLogTailCursor $cursor
): EventLogsResult
```

#### Builder resolve pattern (inside each method):
```php
if ($select instanceof SelectBuilderInterface) {
    $select = $select->buildSelect();
}
if ($filter instanceof FilterBuilderInterface) {
    $filter = $filter->toArray();
}
// cursor is always an object, serialized explicitly:
$this->core->call('main.eventlog.tail', [
    'select' => $select,
    'filter' => $filter,
    'cursor' => $cursor->toArray(),
], ApiVersion::v3);
```

Guard: `get()` must call `$this->guardPositiveId($id)` before making the API call.

### 8. Integration Test
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

### 4. `CHANGELOG.md`

Add to the `## Unreleased → ### Added` section:

```markdown
- Added `Core\Contracts\SortOrder` enum (`Ascending = 'ASC'`, `Descending = 'DESC'`) —
  type-safe sort direction for use across all REST v3 API calls.

- Added service `Services\Main\Service\EventLog` with REST v3 event log methods
  (scope: `main`, requires administrator access),
  see [main.eventlog.* methods](https://github.com/bitrix24/b24phpsdk/issues/374):
    - `get(int $id, array|EventLogSelectBuilder $select)` — returns a single event log entry by ID
      ([main.eventlog.get](https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-get.html))
    - `list(array|EventLogSelectBuilder $select, array|EventLogFilter $filter, array $order, array $pagination)` — returns a list of entries with filtering and pagination
      ([main.eventlog.list](https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-list.html))
    - `tail(array|EventLogSelectBuilder $select, array|EventLogFilter $filter, EventLogTailCursor $cursor)` — returns new entries after a cursor point for polling/sync scenarios
      ([main.eventlog.tail](https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-tail.html))
- Added `Services\Main\Service\EventLogSelectBuilder` — fluent select builder for event log fields
- Added `Services\Main\Service\EventLogFilter` — type-safe filter builder with typed condition builders
  per field (`IntFieldConditionBuilder`, `DateTimeFieldConditionBuilder`, `StringFieldConditionBuilder`)
- Added `Services\Main\Service\EventLogTailCursor` — immutable value object for the tail cursor
  (`field`, `order: SortOrder`, `value`, `limit`), serialized via `toArray()`
```

---

## Critical Files (reference during implementation)

| File | Purpose |
|------|---------|
| `src/Services/Main/Service/Event.php` | Pattern for service without Batch |
| `src/Core/Contracts/ApiVersion.php` | Reference for the new `SortOrder` enum structure |
| `src/Services/Task/Service/Task.php` | **Builder union-type pattern** — `array\|Builder`, `ApiVersion::v3` in call and in `ApiEndpointMetadata` |
| `src/Services/Task/Service/TaskFilter.php` | **Filter builder pattern** to mirror for `EventLogFilter` |
| `src/Services/Task/Service/TaskItemSelectBuilder.php` | **Select builder pattern** to mirror for `EventLogSelectBuilder` |
| `src/Services/AbstractSelectBuilder.php` | Base class for `EventLogSelectBuilder` |
| `src/Filters/AbstractFilterBuilder.php` | Base class for `EventLogFilter` |
| `src/Filters/Types/IntFieldConditionBuilder.php` | Used for `id`, `userId`, `guestId` |
| `src/Filters/Types/StringFieldConditionBuilder.php` | Used for `severity`, `auditTypeId`, etc. |
| `src/Filters/Types/DateTimeFieldConditionBuilder.php` | Used for `timestampX` |
| `src/Services/CRM/Common/Result/AbstractCrmItem.php` | **Type casting pattern** — override `__get()` with `match`/`switch` |
| `src/Services/Main/Result/EventHandlerItemResult.php` | Item result pattern (no casting — do NOT follow for EventLog) |
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

# 2. Code style clean
make lint-cs-fixer

# 3. Integration tests (requires webhook with 'main' scope + admin user)
make test-integration-main-eventlog

# 4. No new deptrac violations
make lint-deptrac
```
