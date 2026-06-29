# Plan: Add `auth_connector` support and offline-events methods (issue #386)

## Context

Issue #386 asks for a way to pass the offline-events `auth_connector` parameter through
SDK scope methods instead of only via the low-level `core->call()`.

`auth_connector` is an offline-events **cycle-avoidance** marker
(see https://apidocs.bitrix24.com/api-reference/events/offline-events.html#how-to-avoid-cycles).
It is a **top-level request parameter** (sibling of `id`/`fields`), value is an arbitrary
connector-defined string, and it is constant for the lifetime of a connector's sync process.
It separates the offline-events queue so a connector does not receive its own changes back.

During brainstorming the scope was expanded (user decision) to deliver the full offline-events
workflow, not just the `auth_connector` injection:

1. **Core-level `auth_connector` injection** — set once, auto-applied to every request and
   every batch sub-command.
2. **`event.offline.*` service methods** — `get`, `list`, `clear`, `error` (currently absent
   from the SDK), in a dedicated `OfflineEvent` service.
3. **`event.bind` / `event.unbind` rework** — add `event_type` (online|offline) and
   `auth_connector` support, and fix a pre-existing bug (the `event_type` key contains a tab
   character `"event_type\t"`, so the parameter never reaches the API).

### API research (grounded in apidocs.bitrix24.com)

Confirmed top-level parameter, request shape `{ "id": 1, "fields": {...}, "auth_connector": "my_connector" }`.

| Method | Required params | Optional params | `result` envelope |
|---|---|---|---|
| `event.offline.get` | — | `filter`, `auth_connector`, `clear` | object: `{ process_id, events: [...] }` |
| `event.offline.list` | — | `filter`, `order` | flat array of events + top-level `total` |
| `event.offline.clear` | `process_id` | `id[]`, `message_id[]` | boolean |
| `event.offline.error` | `process_id` | `message_id[]` | boolean |
| `event.bind` | `event`, `handler` | `auth_type`, `event_type` (online\|offline), `auth_connector`, `options` | boolean |

Offline event item fields (from `get`/`list`): `ID`, `TIMESTAMP_X`, `EVENT_NAME`, `EVENT_DATA`,
`EVENT_ADDITIONAL`, `MESSAGE_ID`, `PROCESS_ID`, `ERROR`. `EVENT_DATA`/`EVENT_ADDITIONAL`
come back as `false` when empty, otherwise as arrays.

### Authorization context — verified empirically

All `event.*` methods are **rejected for incoming webhook** auth and require **OAuth application
context**:

- webhook → `WRONG_AUTH_TYPE` ("Current authorization type is denied for this method")
- OAuth app token → `expired_token` (method accepts OAuth; token only needs refresh, which the
  SDK does automatically via the `expired_token` flow in `Core::call()`)

The integration harness already supports this through
`Factory::getServiceBuilder(true)` (application credentials via `tests/ApplicationBridge/`),
used widely (CRM Automation Trigger, Biconnector incl. annotation tests, Entity, Telephony).
Therefore offline-events methods CAN have full integration + annotation tests, run under
application credentials.

### Generator applicability

The `b24-dev:result-item-generator` relies on `docs/open-api/openapi.json`. The `event.offline.*`
methods are general REST methods and are **not** present in the v3 OpenAPI schema, so the
generator cannot be used for `OfflineEventItemResult`. It will be hand-written extending
`Core\Result\AbstractAnnotatedItem` (annotation-driven runtime casting, no manual `__get()`),
following the Booking result items, e.g. `Services\Booking\ClientType\Result\ClientTypeItemResult`.

### Design decisions (locked during brainstorming)

- API surface: **global set-once** on Core, auto-injected. No per-method `auth_connector`
  argument across the SDK (YAGNI). `event.offline.get` keeps an explicit optional
  `authConnector` argument because it selects the queue.
- Injection rule: inject only when value is set AND the key is not already present (explicit
  param via low-level `call()` always wins). `null` disables it.
- Offline methods live in a **dedicated `OfflineEvent` service** (not folded into `Event`).
- `event.bind`/`unbind` rework **is** in scope.

---

## Files to Create

### 1. `src/Services/Main/Service/EventType.php`

```php
<?php
declare(strict_types=1);
namespace Bitrix24\SDK\Services\Main\Service;

enum EventType: string
{
    case online = 'online';
    case offline = 'offline';
}
```

### 2. `src/Services/Main/Service/OfflineEvent.php`

```php
#[ApiServiceMetadata(new Scope([]))]
class OfflineEvent extends AbstractService
{
    // event.offline.get — docs: https://apidocs.bitrix24.com/api-reference/events/event-offline-get.html
    public function get(array $filter = [], ?string $authConnector = null, bool $clear = true): OfflineEventPacketResult
    {
        $params = ['clear' => $clear ? 1 : 0];
        if ($filter !== []) { $params['filter'] = $filter; }
        if ($authConnector !== null) { $params['auth_connector'] = $authConnector; }
        return new OfflineEventPacketResult($this->core->call('event.offline.get', $params));
    }

    // event.offline.list — docs: https://apidocs.bitrix24.com/api-reference/events/event-offline-list.html
    public function list(array $filter = [], array $order = []): OfflineEventsResult
    {
        $params = [];
        if ($filter !== []) { $params['filter'] = $filter; }
        if ($order !== []) { $params['order'] = $order; }
        return new OfflineEventsResult($this->core->call('event.offline.list', $params));
    }

    // event.offline.clear — docs: https://apidocs.bitrix24.com/api-reference/events/event-offline-clear.html
    public function clear(string $processId, array $id = [], array $messageId = []): OfflineEventClearResult
    {
        $params = ['process_id' => $processId];
        if ($id !== []) { $params['id'] = $id; }
        if ($messageId !== []) { $params['message_id'] = $messageId; }
        return new OfflineEventClearResult($this->core->call('event.offline.clear', $params));
    }

    // event.offline.error — docs: https://apidocs.bitrix24.com/api-reference/events/event-offline-error.html
    public function error(string $processId, array $messageId = []): OfflineEventErrorResult
    {
        $params = ['process_id' => $processId];
        if ($messageId !== []) { $params['message_id'] = $messageId; }
        return new OfflineEventErrorResult($this->core->call('event.offline.error', $params));
    }
}
```

Each method carries `#[ApiEndpointMetadata('event.offline.<x>', '<apidocs.bitrix24.com url>', '<desc>')]`.

### 3. `src/Services/Main/Result/OfflineEventItemResult.php`

Extends `Core\Result\AbstractAnnotatedItem` — the new runtime type-casting base class. It casts
values automatically from the `@property-read` annotations via Typhoon reflection
(`CarbonImmutable::parse()`, `int`/`float`/`bool`/`string`, `array<ItemClass>`, backed enums).
**No manual `__get()` override** — annotations only. Property names must match the raw API keys
exactly (UPPER_SNAKE for offline events). Mirrors the Booking result items, e.g.
`Services\Booking\ClientType\Result\ClientTypeItemResult`.

```php
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int                  $ID
 * @property-read CarbonImmutable|null $TIMESTAMP_X
 * @property-read string|null          $EVENT_NAME
 * @property-read mixed                $EVENT_DATA
 * @property-read mixed                $EVENT_ADDITIONAL
 * @property-read int                  $MESSAGE_ID
 * @property-read string|null          $PROCESS_ID
 * @property-read int                  $ERROR
 */
class OfflineEventItemResult extends AbstractAnnotatedItem
{
}
```

Notes:
- `EVENT_DATA` / `EVENT_ADDITIONAL` are annotated `mixed` because the API returns `false` when
  empty and an array otherwise — `mixed` passes the value through unchanged (do NOT annotate
  `array<...>`, which would coerce `false` into `[false]`).
- `TIMESTAMP_X` is ISO-8601 (`2024-07-18T12:32:31+02:00`); `AbstractAnnotatedItem` casts it via
  `CarbonImmutable::parse()` and returns `null` for empty strings.

### 4. `src/Services/Main/Result/OfflineEventsResult.php`  (for `event.offline.list`)

```php
class OfflineEventsResult extends AbstractResult
{
    /** @return OfflineEventItemResult[] */
    public function getEvents(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $event) {
            $res[] = new OfflineEventItemResult($event);
        }
        return $res;
    }
}
```

### 5. `src/Services/Main/Result/OfflineEventPacketResult.php`  (for `event.offline.get`)

```php
class OfflineEventPacketResult extends AbstractResult
{
    public function getProcessId(): ?string
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['process_id'] ?? null;
    }

    /** @return OfflineEventItemResult[] */
    public function getEvents(): array
    {
        $res = [];
        foreach (($this->getCoreResponse()->getResponseData()->getResult()['events'] ?? []) as $event) {
            $res[] = new OfflineEventItemResult($event);
        }
        return $res;
    }
}
```

### 6. `src/Services/Main/Result/OfflineEventClearResult.php` and
### 7. `src/Services/Main/Result/OfflineEventErrorResult.php`

Mirror `EventHandlerBindResult`:

```php
class OfflineEventClearResult extends AbstractResult
{
    public function isSuccess(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult()[0];
    }
}
```

(Verify the boolean-result envelope shape against `EventHandlerBindResult::isBinded()` during
implementation; reuse whichever indexing it uses.)

### 8. `tests/Unit/Core/BatchTest.php`

Unit test for `auth_connector` injection into batch sub-commands. Use a `createMock(CoreInterface::class)`
that returns `getAuthConnector()='conn'`, capture the `call('batch', ...)` argument, assert every
`cmd` query string contains `auth_connector=conn`, and assert no injection when `getAuthConnector()`
is null.

### 9. `tests/Unit/Services/Main/Service/EventTest.php`

Unit test for `Event::bind()`/`unbind()` parameter building with a `createMock(CoreInterface::class)`:
- `bind()` sends `event_type` (NOT `"event_type\t"`), `auth_connector` when provided, `auth_type` when userId provided.
- default `event_type` is `online`; `offline` is honoured.
- `unbind()` sends the corrected `event_type` key.

### 10. `tests/Unit/Services/Main/Service/OfflineEventTest.php`

Unit test for `OfflineEvent` parameter building with a `createMock(CoreInterface::class)`:
assert each method calls the right API method with the right params (`clear` flag, `filter`,
`order`, `process_id`, `id`, `message_id`, explicit `auth_connector`).

### 11. `tests/Integration/Services/Main/Service/OfflineEventTest.php`

Lifecycle integration test under application credentials (`Factory::getServiceBuilder(true)`):
1. `bind` an OFFLINE handler for `ONCRMDEALADD` with `auth_connector = 'b24phpsdk_test_<rand>'`.
2. create a deal via the CRM scope to trigger the offline event.
3. `list(['EVENT_NAME' => 'ONCRMDEALADD'])` → assert ≥1 event, assert item field values.
4. `get(clear: false)` → assert `process_id` returned + events; then `clear(processId)`.
5. tearDown: delete the deal, `unbind` the handler, clear the queue.

### 12. `tests/Integration/Services/Main/Result/OfflineEventItemResultTest.php`

Mandatory annotation test for `OfflineEventItemResult` (per maintainer skill). setUp uses
`Factory::getServiceBuilder(true)`. It must guarantee at least one queued event (reuse the
bind→create-deal step), then:
- `testAllFieldsAreAnnotated` — `assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), OfflineEventItemResult::class)` where `$rawItem` is the first element of `list()->getCoreResponse()->getResponseData()->getResult()`.
- `testAllFieldsHasValidTypeCastingInMagicGetters` — `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($item, OfflineEventItemResult::class)`.

---

## Files to Modify

### 1. `src/Core/Contracts/CoreInterface.php`

Add:
```php
public function setAuthConnector(?string $authConnector): void;
public function getAuthConnector(): ?string;
```

### 2. `src/Core/Core.php`

- Add `private ?string $authConnector = null;`
- Implement `setAuthConnector()`/`getAuthConnector()`.
- At the very start of `call()` (before the debug log so logs reflect the final payload):
```php
if ($this->authConnector !== null && !array_key_exists('auth_connector', $parameters)) {
    $parameters['auth_connector'] = $this->authConnector;
}
```
The "not already present" guard also prevents double-injection on the recursive retry paths
(302 redirect / expired_token).

### 3. `src/Core/Batch.php`

In `convertToApiCommands()`:
```php
$authConnector = $this->core->getAuthConnector();
foreach ($this->commands as $command) {
    $parameters = $command->getParameters();
    if ($authConnector !== null && !array_key_exists('auth_connector', $parameters)) {
        $parameters['auth_connector'] = $authConnector;
    }
    $apiCommands[$command->getId()] = sprintf('%s?%s', $command->getApiMethod(), http_build_query($parameters));
}
```

### 4. `src/Core/CoreBuilder.php`

- Add `private ?string $authConnector = null;`
- Add `public function withAuthConnector(?string $authConnector): self { $this->authConnector = $authConnector; return $this; }`
- In `build()`, after creating `Core`: `$core->setAuthConnector($this->authConnector);` then return it.

### 5. `tests/Unit/Stubs/NullCore.php`

Add `private ?string $authConnector = null;` and implement both interface methods.

### 6. `src/Services/Main/Service/Event.php`

- `bind()` new signature (append new optional params for BC):
```php
public function bind(
    string $eventCode,
    string $handlerUrl,
    ?int $userId = null,
    ?array $options = null,
    EventType $eventType = EventType::online,
    ?string $authConnector = null
): EventHandlerBindResult
```
  body builds `'event_type' => $eventType->value` (FIX: remove tab), adds `auth_connector` when set.
- `unbind()` add `EventType $eventType = EventType::online` (append), build `'event_type'` correctly (FIX tab).
- Import `EventType`.

### 7. `src/Services/Main/MainServiceBuilder.php`

Add:
```php
public function offlineEvent(): OfflineEvent
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new OfflineEvent($this->core, $this->log);
    }
    return $this->serviceCache[__METHOD__];
}
```
plus the `use` import.

### 8. `phpunit.xml.dist`

Add after the `integration_tests_scope_main_eventlog` suite:
```xml
<testsuite name="integration_tests_scope_main_event">
    <file>./tests/Integration/Services/Main/Service/OfflineEventTest.php</file>
    <file>./tests/Integration/Services/Main/Result/OfflineEventItemResultTest.php</file>
</testsuite>
```

### 9. `Makefile`

Add:
```make
.PHONY: test-integration-main-event
test-integration-main-event:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_main_event
```

### 10. `tests/Unit/Core/CoreTest.php`

Add tests:
- `auth_connector` injected into params passed to `ApiClient::getResponse()` when set.
- not injected when null.
- explicit `auth_connector` in params is not overwritten.
Use `createMock(ApiClientInterface::class)` with `->expects()->method('getResponse')->with(...)`
capturing the parameters argument.

### 11. `tests/Unit/Core/CoreBuilderTest.php`

Add: `withAuthConnector('x')` then `build()->getAuthConnector() === 'x'`.

### 12. `CHANGELOG.md` (under `## 3.3.0 – UNRELEASED`)

`### Added`:
```
- Added offline-events `auth_connector` support: `Core\Contracts\CoreInterface::setAuthConnector()`
  (and `Core\CoreBuilder::withAuthConnector()`) auto-injects the parameter into every request and
  batch sub-command; new `Services\Main\Service\OfflineEvent` wraps `event.offline.get`,
  `event.offline.list`, `event.offline.clear`, `event.offline.error`; `Services\Main\Service\Event::bind()`
  and `unbind()` gained `event_type` (online|offline) and `auth_connector` support
  ([#386](https://github.com/bitrix24/b24phpsdk/issues/386))
```
`### Fixed`:
```
- Fixed malformed `event_type` request parameter key (contained a tab character) in
  `Services\Main\Service\Event::bind()`/`unbind()` ([#386](https://github.com/bitrix24/b24phpsdk/issues/386))
```

---

## Deptrac compliance

- New Core code (`CoreInterface`, `Core`, `Batch`, `CoreBuilder`) stays within the Core layer and
  imports nothing from outer layers. `Batch` reading `core->getAuthConnector()` is a Core→Core call.
- New Services code (`OfflineEvent`, `EventType`, result classes, `MainServiceBuilder`) imports only
  from Core — allowed for the Services layer.
- No new `skip_violations` entries.

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

### Phase 2 — integration (requires authorized local app in tests/ApplicationBridge/)
```bash
make test-integration-main-event
```

### Phase 3
Update `CHANGELOG.md` (listed above) and commit.
