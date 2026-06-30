# Plan: Add timeman.record.* v3 Record service (issue #518)

## Context

The Bitrix24 REST API **v3 `timeman.record.*`** methods are not yet supported by the SDK.
The existing `src/Services/Timeman/` scope only wraps the v1 workday methods
(`timeman.open`, `timeman.pause`, `timeman.close`, `timeman.status`, `timeman.settings`)
in a single flat `Timeman/Service/Timeman.php`. The three v3 work-time-record methods are missing.

### API methods (confirmed via bitrix24 MCP `bitrix-method-details` + `docs/open-api/openapi.json`)

| Method | Envelope | Required params | Notes |
|---|---|---|---|
| `timeman.record.list` | `result.items` (array) | `filter` must contain `userId` | also: `select`, `order`, `pagination` |
| `timeman.record.field.get` | `result.item` (object) | `name` | optional `select` |
| `timeman.record.field.list` | `result.items` (array) | — | optional `select` |

All three are **v3** → base branch `v3-dev`, every `core->call(...)` passes `ApiVersion::v3`.

### Record entity (`bitrix.timeman.recorddto`)

| Field | OpenAPI type | SDK annotation | Cast |
|---|---|---|---|
| `id` | integer | `int` | `(int)` |
| `userId` | integer | `int|null` | `(int)` when set |
| `startTime` | date-time | `CarbonImmutable|null` | `CarbonImmutable::createFromFormat(DATE_ATOM, …)` |
| `endTime` | date-time | `CarbonImmutable|null` | `CarbonImmutable::createFromFormat(DATE_ATOM, …)` |
| `duration` | integer (seconds) | `int|null` | `(int)` when set |
| `breakLength` | integer (seconds) | `int|null` | `(int)` when set |
| `state` | object `{status, recommendedCloseTime}` (`bitrix.timeman.recordstatedto`) | — (see note) | — |
| `isApproved` | boolean | `bool|null` | raw (default getter) |

> **`state` decision (verified against live portal during implementation):** `timeman.record.list`
> never returns a `state` key in list items — selecting flat `state` returns `INTERNAL_SERVER_ERROR`,
> and dotted `state.status` / `state.recommendedCloseTime` returns HTTP 200 but the response item omits
> `state` entirely. Because the mandatory `assertBitrix24AllResultItemFieldsAnnotated` requires an exact
> match between returned keys and annotations, `state` is intentionally **not** annotated on
> `RecordItemResult` and the generated `state()` method is removed from `RecordSelectBuilder`. The
> `state` object remains discoverable through `timeman.record.field.*` metadata. Reliable list fields: 7
> (`id`, `userId`, `startTime`, `endTime`, `duration`, `breakLength`, `isApproved`).

### Field descriptor entity (`bitrix.rest.dtofielddto`)

Identical shape to `ChatMessageFieldItemResult` / `EventLogFieldItemResult`:
`name`, `type`, `title`, `description?`, `validationRules?`, `requiredGroups?`,
`filterable`, `sortable`, `editable`, `multiple`, `elementType?`.

### Design decisions (confirmed with maintainer)

1. **Split into two services** (like `Main\EventLog` + `Main\EventLogField`):
   - `Record` → `timeman.record.list`
   - `RecordField` → `timeman.record.field.get` / `timeman.record.field.list`
2. **No `Batch`** service (YAGNI; not in issue deliverables checklist; can be a follow-up).
3. **Typed builders** for `Record::list()`: `RecordSelectBuilder`, `RecordFilter`,
   and `SortOrder` enum for `order` — mirroring `EventLog`.
   `RecordField` keeps plain `array $select` (mirrors `ChatMessageField` / `EventLogField`).

### Reference implementations in repo

- `src/Services/Main/Service/EventLog.php` — v3 `.list(select, filter, order, pagination)` template
- `src/Services/Main/Service/EventLogSelectBuilder.php` / `EventLogFilter.php` — typed builders
- `src/Services/Main/Result/EventLogItemResult.php` — `__get` date casting + `#[OpenApiEntity]`
- `src/Services/Task/ChatMessageField/*` — `field.get` / `field.list` service + result classes
- `tests/Integration/Services/Task/ChatMessageField/Result/ChatMessageFieldItemResultTest.php` — mandatory annotation/type-cast test template

### Code generators

- `RecordSelectBuilder` — generate offline from openapi:
  `php bin/console b24-dev:generate-select-builder bitrix.timeman.recorddto --namespace="Bitrix24\\SDK\\Services\\Timeman\\Record\\Service" --class-name=RecordSelectBuilder --output=src/Services/Timeman/Record/Service/RecordSelectBuilder.php`
  Review the output, add the standard file header and `id` constructor seed (matching `EventLogSelectBuilder`).
- `RecordItemResult` — attempt `php bin/console b24-dev:result-item-generator timeman.record.list --stage=all`.
  **Outcome**: generator FAILED with
  `REST docs payload is required for "timeman.record.list", but the documentation URL could not be resolved.`
  → fall back to **manual** `RecordItemResult` derived from the `bitrix.timeman.recorddto` schema above
  (reason documented here per skill rule), keeping the mandatory live annotation/type-cast integration test.
- `RecordFieldItemResult` — mirror `ChatMessageFieldItemResult` (`bitrix.rest.dtofielddto`); no generator needed.

---

## Files to Create

### 1. `src/Services/Timeman/Record/Service/RecordSelectBuilder.php`

`namespace Bitrix24\SDK\Services\Timeman\Record\Service;`
Extends `AbstractSelectBuilder`. Constructor seeds `$this->select[] = 'id';`.
Fluent `self` methods (one per non-id field): `userId()`, `startTime()`, `endTime()`,
`duration()`, `breakLength()`, `state()`, `isApproved()`.

### 2. `src/Services/Timeman/Record/Service/RecordFilter.php`

`namespace Bitrix24\SDK\Services\Timeman\Record\Service;`
Extends `AbstractFilterBuilder`. Documented filterable fields only:
- `userId(): IntFieldConditionBuilder` → `new IntFieldConditionBuilder('userId', $this)`
- `startTime(): DateTimeFieldConditionBuilder` → `new DateTimeFieldConditionBuilder('startTime', $this)`

### 3. `src/Services/Timeman/Record/Service/Record.php`

```php
namespace Bitrix24\SDK\Services\Timeman\Record\Service;

#[ApiServiceMetadata(new Scope(['timeman']))]
class Record extends AbstractService
{
    #[ApiEndpointMetadata(
        'timeman.record.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/timeman/timeman-record-list.html',
        'Returns a list of employee work-time records.',
        ApiVersion::v3
    )]
    public function list(
        array|RecordSelectBuilder  $select = [],
        array|RecordFilter         $filter = [],
        array                      $order = [],
        array                      $pagination = []
    ): RecordsResult {
        // $select->buildSelect() when SelectBuilderInterface
        // $filter->toArray()    when FilterBuilderInterface
        // normalize $order: SortOrder -> ->value
        // array_filter([...], fn($v) => $v !== []) and core->call(..., ApiVersion::v3)
    }
}
```

`filter` must carry `userId` (server validates and returns
`BITRIX_REST_V3_EXCEPTION_VALIDATION_REQUESTVALIDATIONEXCEPTION` otherwise — documented in phpdoc,
no client-side enforcement, matching `EventLog`). Doc link uses the **English** apidocs host.

### 4. `src/Services/Timeman/Record/Result/RecordItemResult.php`

`namespace Bitrix24\SDK\Services\Timeman\Record\Result;`
`@property-read` block per the entity table above. `#[OpenApiEntity(entityKey: 'bitrix.timeman.recorddto', selectBuilder: RecordSelectBuilder::class)]`.
Override `__get($offset)` with a `match`:
- `id` → `(int)`
- `userId`, `duration`, `breakLength` → `(int)` when not null/empty, else `null`
- `startTime`, `endTime` → `CarbonImmutable::createFromFormat(DATE_ATOM, …)` when not null/empty, else `null`
- default → `$this->data[$offset] ?? null` (covers `state` array and `isApproved` bool)

### 5. `src/Services/Timeman/Record/Result/RecordsResult.php`

Extends `AbstractResult`. `getRecords(): RecordItemResult[]` iterates `getResult()['items']`.

### 6. `src/Services/Timeman/RecordField/Service/RecordField.php`

`namespace Bitrix24\SDK\Services\Timeman\RecordField\Service;`
`#[ApiServiceMetadata(new Scope(['timeman']))]`. Two methods, mirroring `ChatMessageField`:
- `get(string $name, array $select = []): RecordFieldResult` → `timeman.record.field.get`,
  `guardNonEmptyString($name, …)`, params `['name' => $name] (+ 'select')`, `ApiVersion::v3`.
- `list(array $select = []): RecordFieldsResult` → `timeman.record.field.list`,
  params `['select' => …]` when non-empty, `ApiVersion::v3`.
Both `ApiEndpointMetadata` doc links use the English apidocs host.

### 7. `src/Services/Timeman/RecordField/Result/RecordFieldItemResult.php`

Mirror `ChatMessageFieldItemResult` `@property-read` block (`bitrix.rest.dtofielddto`). Extends `AbstractItem`, empty body.

### 8. `src/Services/Timeman/RecordField/Result/RecordFieldResult.php`

Extends `AbstractResult`. `recordField(): RecordFieldItemResult` from `getResult()['item']`.

### 9. `src/Services/Timeman/RecordField/Result/RecordFieldsResult.php`

Extends `AbstractResult`. `getRecordFields(): RecordFieldItemResult[]` from `getResult()['items']`.

### 10. `tests/Unit/Services/Timeman/Record/Service/RecordTest.php`

`#[CoversClass(Record::class)]`, `new Record(new NullCore(), new NullLogger())`.
- `testListReturnsRecordsResult` — `assertInstanceOf(RecordsResult::class, $service->list())`.

### 11. `tests/Unit/Services/Timeman/RecordField/Service/RecordFieldTest.php`

`#[CoversClass(RecordField::class)]`, `new RecordField(new NullCore(), new NullLogger())`.
- `testGetReturnsRecordFieldResult` — `assertInstanceOf(RecordFieldResult::class, $service->get('startTime'))`.
- `testListReturnsRecordFieldsResult` — `assertInstanceOf(RecordFieldsResult::class, $service->list())`.
- `testGetThrowsOnEmptyName` — `expectException(InvalidArgumentException::class); $service->get('')`.

### 12. `tests/Integration/Services/Timeman/Record/Service/RecordTest.php`

`#[CoversClass(Record::class)]`. setUp gets
`Factory::getServiceBuilder()->getTimemanScope()->record()`.
- `testList`: seed a record (open+close a workday via
  `getTimemanScope()->timeman()->open()`/`->close()`), resolve current user id via
  `Factory::getServiceBuilder()->getUserScope()->user()->current()->user()->ID`, then call
  `list((new RecordSelectBuilder())->allSystemFields(), (new RecordFilter())->userId()->eq($userId), ['startTime' => SortOrder::Descending], ['limit' => 5])`.
  Assert `getRecords()` is array; when non-empty assert `$item->id > 0` and `startTime` is `CarbonImmutable`.
- `testListWithArrayArguments`: `list(['id','startTime','duration'], [['userId', $userId]], [], ['limit' => 3])`; assert array.

### 13. `tests/Integration/Services/Timeman/Record/Result/RecordItemResultTest.php`

Mandatory annotation/type-cast test (skill template). `#[CoversClass(RecordItemResult::class)]`,
`use CustomBitrix24Assertions`. Seed via open+close, resolve userId.
- `testAllFieldsAreAnnotated`: fetch `record()->list((new RecordSelectBuilder())->allSystemFields(), (new RecordFilter())->userId()->eq($userId), [], ['limit' => 1])->getCoreResponse()->getResponseData()->getResult()['items'][0]`;
  `assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), RecordItemResult::class)`.
- `testAllFieldsHasValidTypeCastingInMagicGetters`: take `getRecords()[0]` and
  `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($item, RecordItemResult::class)`.
- If the portal returns no records after seeding, `markTestSkipped(...)` with a clear message
  (mirrors `EventLog`'s empty-list guard) so the suite stays green on empty portals.

### 14. `tests/Integration/Services/Timeman/RecordField/Service/RecordFieldTest.php`

`#[CoversClass(RecordField::class)]`. setUp gets `getTimemanScope()->recordField()`.
- `testGet`: `get('startTime')->recordField()`; assert `name === 'startTime'`, `filterable`/`sortable` are bool.
- `testList`: `list()->getRecordFields()`; assert non-empty array of `RecordFieldItemResult`.

### 15. `tests/Integration/Services/Timeman/RecordField/Result/RecordFieldItemResultTest.php`

Mandatory annotation/type-cast test, mirroring `ChatMessageFieldItemResultTest`.
`#[CoversClass(RecordFieldItemResult::class)]`. `get('startTime')` → `['item']` for annotations,
`->recordField()` for type-cast.

---

## Files to Modify

### 1. `src/Services/Timeman/TimemanServiceBuilder.php`

Add two cached accessors after `timeman()`:

```php
public function record(): Record
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Record($this->core, $this->log);
    }
    return $this->serviceCache[__METHOD__];
}

public function recordField(): RecordField
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new RecordField($this->core, $this->log);
    }
    return $this->serviceCache[__METHOD__];
}
```

Add `use` imports for `...\Record\Service\Record` and `...\RecordField\Service\RecordField`.

### 2. `phpunit.xml.dist`

Add a new suite next to `integration_tests_scope_timeman` (≈ line 401):

```xml
<testsuite name="integration_tests_scope_timeman_record">
    <file>./tests/Integration/Services/Timeman/Record/Service/RecordTest.php</file>
    <file>./tests/Integration/Services/Timeman/Record/Result/RecordItemResultTest.php</file>
    <file>./tests/Integration/Services/Timeman/RecordField/Service/RecordFieldTest.php</file>
    <file>./tests/Integration/Services/Timeman/RecordField/Result/RecordFieldItemResultTest.php</file>
</testsuite>
```

(Unit tests auto-included via `unit_tests` `<directory>./tests/Unit</directory>`.)

### 3. `Makefile`

Add after the `test-integration-scope-timeman` target (≈ line 622):

```makefile
.PHONY: test-integration-timeman-record
test-integration-timeman-record:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_scope_timeman_record
```

### 4. `CHANGELOG.md`

Under `## 3.4.0 – UNRELEASED` → `### Added`, prepend:

```markdown
- Added services `Services\Timeman\Record\Service\Record` and `Services\Timeman\RecordField\Service\RecordField`
  with support for v3 `timeman.record.*` methods,
  see [timeman REST v3](https://apidocs.bitrix24.com/api-reference/rest-v3/timeman/index.html) ([#518](https://github.com/bitrix24/b24phpsdk/issues/518)):
    - `Record::list` returns employee work-time records (`timeman.record.list`), with typed `RecordSelectBuilder` / `RecordFilter`
    - `RecordField::get` returns a single record field descriptor (`timeman.record.field.get`)
    - `RecordField::list` returns all record field descriptors (`timeman.record.field.list`)
```

---

## Deptrac compliance

All new classes live under `Services` and depend only on `Core`
(`AbstractService`, `AbstractResult`, `AbstractItem`, `AbstractSelectBuilder`,
`AbstractFilterBuilder`, `Filters\Types\*`, `Attributes\*`, `Core\Contracts\*`, `Carbon`).
No `Services → Services`, `Services → Infrastructure`, or `Services → Application` imports.
No new `skip_violations` entry needed.

---

## Verification

### Phase 1 — light checks

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

### Phase 2 — integration (after phase 1 green)

```bash
make test-integration-timeman-record
```

### Phase 3 — CHANGELOG

Add the `### Added` entry above, commit.
