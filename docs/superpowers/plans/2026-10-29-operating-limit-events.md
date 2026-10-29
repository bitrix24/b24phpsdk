# Operating Limit Events Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Dispatch events when a Bitrix24 method's `operating` time crosses configured thresholds of the window, ship an opt-in sleeping listener, and let `OperationTimeLimitExceededException` reach batch users with its type and context intact.

**Architecture:** A stateful `OperatingTracker` (one per `Core`) is given every parsed response body and dispatches `OperatingThresholdCrossedEvent` through the existing Symfony dispatcher. `Core` creates `Response` with the tracker, and `Response::getResponseData()` feeds it before error handling (parsing stays lazy, `Batch` constructors untouched). Configuration flows `ServiceBuilderFactory` → `CoreBuilder` → `Core`.

**Tech Stack:** PHP 8.2+, Symfony EventDispatcher contracts, Symfony HttpClient `MockHttpClient` for unit tests, PHPUnit 10.

**Spec:** `docs/superpowers/specs/2026-10-29-operating-limit-events-design.md`

## Global Constraints

- PHP `8.2.* || 8.3.* || 8.4.* || 8.5.*`; Symfony `^6 || ^7` — no Symfony 7.1-only APIs, no new composer dependencies.
- Default limit **480** seconds; default thresholds **10, 20, 30, 40, 50, 60, 70, 80, 90** (percent).
- No behaviour change without a listener: no sleeps, no extra HTTP requests, no new exceptions on the happy path.
- `Response` parsing stays lazy; constructors of `Core\Batch` and `Services\*\Batch` are not changed; only `Core\Batch` catch blocks change.
- All new constructor parameters are optional and appended last (no BC break).
- Must pass `make lint-all` and `make test-unit`.
- File header and `declare(strict_types=1);` as in existing `src/` files.

## Review Focus

1. A batch command that failed has no `result_time` entry → tracker skips it, no exception. (Task 1 test `testBatchSkipsCommandsWithoutTime`)
2. `operating_reset_at` is null while load is above a threshold → event fires once, never re-fires. (Task 1 test `testNullResetAtFiresOnce`)
3. `result_time` id not present in the request `cmd` map, or `cmd` missing / not an array → skipped silently. (Task 1 test `testBatchUnknownCommandIdIgnored`)
4. `getResponseData()` called twice on the same `Response` → tracker observes once (no duplicate events). (Task 2 test `testResponseObservedOnlyOnce`)
5. Config with unsorted/duplicate thresholds → normalised; out-of-range values (≤0, ≥100) or limit ≤ 0 → `InvalidArgumentException`. (Task 1 test `testConfigNormalisesAndValidates`)

---

### Task 1: Config, event, tracker

**Files:**
- Create: `src/Core/OperatingLimit/OperatingLimitConfig.php`
- Create: `src/Core/OperatingLimit/OperatingTracker.php`
- Create: `src/Events/OperatingThresholdCrossedEvent.php`
- Test: `tests/Unit/Core/OperatingLimit/OperatingTrackerTest.php`

**Interfaces:**
- Produces:
  - `final readonly class OperatingLimitConfig { __construct(float $limitSeconds = 480.0, array $thresholds = [10,20,30,40,50,60,70,80,90]) }` — public readonly `float $limitSeconds`, `list<int> $thresholds` (sorted ascending, unique). Throws `Bitrix24\SDK\Core\Exceptions\InvalidArgumentException` if `limitSeconds <= 0`, thresholds empty, or any threshold `<= 0` or `>= 100`.
  - `class OperatingThresholdCrossedEvent extends Symfony\Contracts\EventDispatcher\Event` with getters `getApiMethod(): string`, `getOperating(): float`, `getOperatingResetAt(): ?CarbonImmutable`, `getThresholdPercent(): int`, `getLimitSeconds(): float`.
  - `class OperatingTracker { __construct(OperatingLimitConfig $config, EventDispatcherInterface $eventDispatcher) }`
    - `observeResponse(Command $command, array $responseBody): void`
    - `getLastOperatingResetAt(string $apiMethod): ?int` — last non-null `operating_reset_at` seen for that method
    - `public static resolveBatchCommandMethod(Command $command, string $batchCommandId): ?string`

- [ ] **Step 1: Write failing tests** (build `Time`-shaped arrays like `makeTime()` in `tests/Unit/Services/Landing/BatchTest.php`, add `operating`/`operating_reset_at`; collect events with a real `EventDispatcher` + listener closure; config `new OperatingLimitConfig(100.0)` so operating == percent)
  - `testSingleCallCrossesThresholds`: command `crm.contact.update`, `time.operating = 25` → events thresholds `[10, 20]`, method `crm.contact.update`, `getLimitSeconds() === 100.0`.
  - `testMultipleThresholdsInOneResponseAscending`: operating 5 then 47 → second response yields `[10,20,30,40]` in that order.
  - `testThresholdFiresOnceWhileResetAtUnchanged`: operating 15 (reset 1000) twice → one event for 10.
  - `testRefiresWhenResetAtMovesAndLoadStillHigh`: operating 92 reset 1000 → events 10..90; then operating 91 reset 1060 → events 10..90 again (all still-high thresholds with newer reset).
  - `testRearmsAfterDrop`: 15 → (10); 5 → none; 15 (same reset) → (10) again.
  - `testNullResetAtFiresOnce`: operating 50 reset null twice → `[10..50]` once.
  - `testZeroOrMissingOperatingFiresNothing`.
  - `testMethodsTrackedIndependently`: batch with `cmd = ['a' => 'crm.deal.update?ID=1', 'b' => 'crm.contact.update?ID=2']`, `result_time` a: 30, b: 10 → deal `[10,20,30]`, contact `[10]`.
  - `testBatchIgnoresTopLevelTime`: command `batch`, top-level `time.operating = 99`, empty `result_time` → no events.
  - `testBatchSkipsCommandsWithoutTime`, `testBatchUnknownCommandIdIgnored` (see Review Focus 1, 3).
  - `testGetLastOperatingResetAt`: after reset 1000 then reset null → returns 1000; unknown method → null.
  - `testConfigNormalisesAndValidates`: `[30,10,10]` → `[10,30]`; `[0]`, `[100]`, `[]`, limit `0` → exception.
- [ ] **Step 2: Run** `docker-compose run --rm php-cli vendor/bin/phpunit tests/Unit/Core/OperatingLimit/OperatingTrackerTest.php` — expected FAIL (classes missing).
- [ ] **Step 3: Implement the three classes.** Read times via `Time::initFromResponse()`. For `command->getApiMethod() === 'batch'` iterate `responseBody['result']['result_time']` in order, method = text before `?` of `parameters['cmd'][$id]`; otherwise use top-level `responseBody['time']` with the command's method. Per method keep `array<int, ?int>` (threshold → `operating_reset_at` of the last event for it; key absent = armed); per observation (load = operating / limit × 100; skip if operating ≤ 0), for each threshold ascending:

```text
if load < t:                                        unset state[t]   // re-arm
elif not array_key_exists(t, state):                fire; state[t] = resetAt
elif resetAt !== null and state[t] !== null and resetAt > state[t]:
                                                    fire; state[t] = resetAt
```
- [ ] **Step 4: Run tests** — expected PASS.
- [ ] **Step 5: Commit** `feat(core): add operating tracker and threshold event (#76)`

### Task 2: Wire tracker into Response, Core, builders; enrich exception

**Files:**
- Modify: `src/Core/Exceptions/OperationTimeLimitExceededException.php`
- Modify: `src/Core/ApiLevelErrorHandler.php:150` (pass `batchCommandId` to the exception)
- Modify: `src/Core/Response/Response.php` (constructor, `getResponseData()`)
- Modify: `src/Core/Core.php` (constructor, `new Response(...)`, add `STATUS_TOO_MANY_REQUESTS` case)
- Modify: `src/Core/CoreBuilder.php`
- Modify: `src/Services/ServiceBuilderFactory.php` (constructor, `getServiceBuilder()`)
- Test: `tests/Unit/Core/OperatingLimit/OperatingLimitWiringTest.php`

**Interfaces:**
- Consumes: Task 1 classes.
- Produces:
  - `OperationTimeLimitExceededException::__construct(string $message = '', int $code = 0, ?Throwable $previous = null, public readonly ?string $apiMethod = null, public readonly ?string $batchCommandId = null, public readonly ?int $operatingResetAt = null)`
  - `ApiLevelErrorHandler` passes `$batchCommandId` when throwing it.
  - `Response::__construct(..., LoggerInterface $logger, ?OperatingTracker $operatingTracker = null)`
  - `Core::__construct(..., LoggerInterface $logger, ?OperatingTracker $operatingTracker = null)`
  - `CoreBuilder::withOperatingLimitConfig(OperatingLimitConfig $config): self`; `build()` always creates `new OperatingTracker($config ?? new OperatingLimitConfig(), $this->eventDispatcher)`.
  - `ServiceBuilderFactory::__construct(EventDispatcherInterface $eventDispatcher, LoggerInterface $log, ?OperatingLimitConfig $operatingLimitConfig = null)`; static `createServiceBuilderFrom*` unchanged.

- [ ] **Step 1: Write failing tests** — build `Core` via `(new CoreBuilder())->withCredentials(Credentials::createFromWebhook(new WebhookUrl('https://test.bitrix24.com/rest/1/xxx/')))->withHttpClient(new MockHttpClient([...]))->withEventDispatcher($dispatcher)->withOperatingLimitConfig(new OperatingLimitConfig(100.0))->build()`:
  - `testSingleCallDispatchesEvents`: response `time.operating = 25` → after `->call('crm.contact.get', ['id'=>1])->getResponseData()` events `[10,20]`; before `getResponseData()` no events (lazy).
  - `testResponseObservedOnlyOnce`: `getResponseData()` twice → events once.
  - `testBatchPartialOperationTimeLimitKeepsType`: batch response 200 with `result_time` for `a` (operating 30, reset 1000) and `result_error` `{'b': {'error':'OPERATION_TIME_LIMIT','error_description':'...'}}`, `cmd` a,b both `crm.deal.update` → `getResponseData()` throws `OperationTimeLimitExceededException` (exact class, `assertInstanceOf` on caught exception) with `apiMethod 'crm.deal.update'`, `batchCommandId 'b'`, `operatingResetAt 1000`; events for a were dispatched before the throw.
  - `testSingleCall429Enriched`: first response 200 `crm.deal.update` operating 50 reset 2000; second `MockResponse` HTTP 429 `{'error':'OPERATION_TIME_LIMIT','error_description':'...'}` → `call()` throws `OperationTimeLimitExceededException` with `apiMethod 'crm.deal.update'`, `operatingResetAt 2000`, `batchCommandId null`.
  - `testNoListenerNoBehaviourChange`: default config, no listeners, operating 470 → normal result.
  - `testServiceBuilderFactoryAcceptsConfig`: constructing with 3rd arg works; with 2 args still works.
- [ ] **Step 2: Run** `docker-compose run --rm php-cli vendor/bin/phpunit tests/Unit/Core/OperatingLimit/OperatingLimitWiringTest.php` — expected FAIL.
- [ ] **Step 3: Implement.** In `getResponseData()` call `$this->operatingTracker?->observeResponse($this->apiCommand, $responseResult)` right after `toArray()` and **before** `apiLevelErrorHandler->handle()`; in its `catch`, if the throwable is `OperationTimeLimitExceededException`, log and rethrow a new one with `apiMethod` (for batch via `OperatingTracker::resolveBatchCommandMethod`, else the command method), the original `batchCommandId`, `operatingResetAt` from `getLastOperatingResetAt()`, original as `previous`; all other throwables keep the existing `BaseException` wrapping. In `Core::call()` add `case StatusCodeInterface::STATUS_TOO_MANY_REQUESTS` that handles the body like `default` but enriches `OperationTimeLimitExceededException` the same way (method = `$apiMethod`).
- [ ] **Step 4: Run** the new test and `make test-unit` — expected PASS (existing `new Response(...)` call sites in tests still compile thanks to the optional param).
- [ ] **Step 5: Commit** `feat(core): wire operating tracker and enrich OperationTimeLimitExceededException (#76)`

### Task 3: Preserve exception type in Core\Batch

**Files:**
- Modify: `src/Core/Batch.php` — catch blocks of `addEntityItems()`, `deleteEntityItems()`, `updateEntityItems()`
- Test: `tests/Unit/Core/BatchOperationTimeLimitTest.php`

**Interfaces:**
- Consumes: enriched `OperationTimeLimitExceededException` (Task 2).

- [ ] **Step 1: Write failing tests** — a `CoreInterface` stub whose `call('batch', ...)` returns a `Response` (pattern: `makeResponse()` in `tests/Unit/Services/Landing/BatchTest.php`, with `new Command('batch', $parameters)`) whose body has `result_error` `OPERATION_TIME_LIMIT` for one command:
  - `testUpdateEntityItemsRethrowsOperationTimeLimitUnwrapped`, `testAddEntityItemsRethrows...`, `testDeleteEntityItemsRethrows...`: iterating the generator throws exactly `OperationTimeLimitExceededException` (not `BaseException` wrapper).
  - `testOtherErrorsStillWrapped`: `result_error` `ACCESS_DENIED` → `BaseException` with message prefix `batch update entity items:`.
- [ ] **Step 2: Run** `docker-compose run --rm php-cli vendor/bin/phpunit tests/Unit/Core/BatchOperationTimeLimitTest.php` — expected FAIL.
- [ ] **Step 3: Implement:** in each of the three methods add a `catch (OperationTimeLimitExceededException $e)` before `catch (\Throwable ...)` that logs via `$this->logger->error(...)` with method/command/reset context and rethrows `$e`.
- [ ] **Step 4: Run** test — expected PASS.
- [ ] **Step 5: Commit** `fix(core): keep OperationTimeLimitExceededException type in Core\Batch (#76)`

### Task 4: Ready-made sleeping listener

**Files:**
- Create: `src/Core/OperatingLimit/OperatingLimitSleepListener.php`
- Test: `tests/Unit/Core/OperatingLimit/OperatingLimitSleepListenerTest.php`

**Interfaces:**
- Consumes: `OperatingThresholdCrossedEvent` (Task 1).
- Produces: `final class OperatingLimitSleepListener { __construct(int $sleepFromThresholdPercent = 90, int $safetyMarginSeconds = 1, ?LoggerInterface $logger = null, ?\Closure $sleep = null, ?\Closure $now = null); __invoke(OperatingThresholdCrossedEvent $event): void }` — defaults: `$sleep = static fn(int $s) => sleep($s)`, `$now = time(...)`.

- [ ] **Step 1: Write failing tests** with recording `$sleep` closure and fixed `$now = fn() => 1000`:
  - `testSleepsUntilResetPlusMargin`: threshold 90, resetAt 1030 → sleeps `31`.
  - `testIgnoresLowerThresholds`: threshold 80 → no sleep.
  - `testNoSleepWhenResetAtNullOrPast`: null → none; 990 → none.
  - `testWorksAsDispatcherListener`: registered via `$dispatcher->addListener(OperatingThresholdCrossedEvent::class, $listener)`; dispatch → sleep recorded.
- [ ] **Step 2: Run** the test — expected FAIL.
- [ ] **Step 3: Implement**; log `info` before sleeping with method, operating, threshold, seconds.
- [ ] **Step 4: Run** — expected PASS.
- [ ] **Step 5: Commit** `feat(core): add OperatingLimitSleepListener (#76)`

### Task 5: Docs, CHANGELOG, linters

**Files:**
- Create: `docs/EN/Core/operating-limit.md`
- Modify: `CHANGELOG.md` (`## Unreleased`)

- [ ] **Step 1: Write docs**: what `operating` is (link https://apidocs.bitrix24.com/limits.html); event payload; defaults (480 s, 10–90%) and how to change via `new ServiceBuilderFactory($dispatcher, $logger, new OperatingLimitConfig(420.0))` (static helpers use defaults); example: `$dispatcher->addListener(OperatingThresholdCrossedEvent::class, new OperatingLimitSleepListener(90))` then `foreach ($sb->getCRMScope()->contact()->batch->update($items) as ...)`; catching `OperationTimeLimitExceededException` and reading `apiMethod`/`operatingResetAt`; Known limitations (partial batch pack results lost; overshoot by one pack; `Services\*\Batch` subclasses still wrap exceptions).
- [ ] **Step 2: CHANGELOG** under `## Unreleased`: `### Added` — operating threshold events, `OperatingLimitConfig`, `OperatingLimitSleepListener`, optional config param in `ServiceBuilderFactory`/`CoreBuilder` ([#76](https://github.com/bitrix24/b24phpsdk/issues/76)); `### Changed` — `OperationTimeLimitExceededException` now reaches callers of `Response::getResponseData()` and `Core\Batch` add/update/delete unwrapped (previously `BaseException`), carries `apiMethod`, `batchCommandId`, `operatingResetAt`; code catching `BaseException` still catches it.
- [ ] **Step 3: Run** `make lint-all` and `make test-unit` — expected both green; fix findings.
- [ ] **Step 4: Commit** `docs: document operating limit events (#76)`
