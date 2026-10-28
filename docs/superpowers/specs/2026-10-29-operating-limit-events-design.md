# Operating limit events for long-running batch writes

Issue: [bitrix24/b24phpsdk#76](https://github.com/bitrix24/b24phpsdk/issues/76)
Status: design approved in conversation, pending implementation plan

## Intent

Long-running batch writes and updates (`*.add`, `*.update` over thousands of
items) most often fail with `OPERATION_TIME_LIMIT`: Bitrix24 sums the execution
time of each method per application/webhook over a sliding 10-minute window and
blocks the method with HTTP 429 once the sum exceeds the limit.

Every response already carries the data needed to see this coming
(`time.operating`, `time.operating_reset_at`, and the same per command in
`result_time` of a `batch` response), but the SDK ignores it today.

The SDK will **observe and report**; the client code **decides**. When the
operating time of a method crosses 10%, 20%, ... 90% of the window, the SDK
dispatches an event. A client listener may `sleep()` inside the handler (for
example until `operating_reset_at`), which freezes the batch generator in
place; when the listener returns, the generator continues from where it was.

## Bitrix24 facts this relies on

Source: https://apidocs.bitrix24.com/limits.html

- `operating` is accumulated execution time in seconds **per method, per
  application or webhook**. Other methods are unaffected when one is blocked.
- Accounting uses ten one-minute baskets. `operating_reset_at` is a Unix
  timestamp when the oldest basket drops out of `operating`.
- When exceeded, the next call to that method fails with HTTP 429,
  error `OPERATION_TIME_LIMIT`. Official advice: for the first retry use
  `operating_reset_at` from the last *successful* response.
- The numeric cloud limit is **not published**; 480 s is the documented
  example (self-hosted default is 420 s).
- An outer `batch` call is not counted in `operating`; every nested command is
  counted and checked against its own method's limit.

## Goals

1. Dispatch an event each time a method's operating time crosses a configured
   threshold of the window.
2. Cover nested commands of `batch` responses (the main use case) and plain
   single calls.
3. Ship an opt-in ready-made listener that sleeps until `operating_reset_at`
   at a chosen threshold, so the common case is one line of setup.
4. When `OPERATION_TIME_LIMIT` happens anyway, the client gets an exception of
   the right type that says which method was blocked and when it frees up.

## Non-goals

- `QUERY_LIMIT_EXCEEDED` / request intensity (leaky bucket) throttling.
- Automatic retries inside the SDK.
- Queues, multi-process or multi-thread coordination, shared storage of
  operating state. Single thread, in-memory state only.
- Resuming a batch in a different process/run (checkpoints).
- Re-sending only the failed commands of a partially failed batch pack (see
  "Known limitations").

## Anti-goals

- Must not change behaviour for users who register no listener: no sleeps, no
  extra requests, no new exceptions on the happy path.
- Must not break the lazy parsing of `Response` (`Core::call()` returns before
  the body is parsed; parsing happens in `Response::getResponseData()`).
- Must not require changing the constructors of `Core\Batch` or its many
  subclasses (e.g. `Services\Sale\*\Batch`).

## Decided behaviour

### Load calculation

- `load = operating / limit`, per method.
- `limit` is configurable, default **480 seconds**.
- Thresholds are configurable, default **10, 20, 30, 40, 50, 60, 70, 80, 90**
  (percent).

### Event semantics

- Fired on **crossing** a threshold upward, once per threshold per method.
- If one response jumps over several thresholds (a pack of 50 heavy updates can
  add 10–20% at once), **one event per crossed threshold** is dispatched, in
  ascending order.
- When a method's `operating` drops (baskets expired) below a threshold, that
  threshold is re-armed and can fire again later.
- **Window moved, load still high:** if a threshold already fired and a later
  response for the same method is still at or above it but carries a newer
  `operating_reset_at` than the one in the last event for that threshold, the
  event for that threshold is dispatched again. This lets a listener that slept
  until `operating_reset_at` (freeing only the oldest one-minute basket) be
  woken again and sleep another step, instead of the batch running on into
  429. Responses with the same `operating_reset_at` do not re-fire.
- Method name for a nested batch command is taken from the command string in
  the batch request (`cmd[<id>] = "<method>?<query>"`, the part before `?`),
  matched to `result_time[<id>]`.
- Responses without `operating` (self-hosted without the limit, or `operating`
  = 0) never fire events.

### Event payload

The event carries raw data so clients can ignore percentages entirely:

- method name (e.g. `crm.contact.update`)
- `operating` (float, seconds)
- `operatingResetAt` (Unix timestamp or `CarbonImmutable`, nullable)
- threshold that was crossed (percent, int)
- configured limit (seconds)

Events live next to the existing ones in `src/Events/` and are dispatched
through the same `EventDispatcherInterface` already passed to `Core`
(the one given to `ServiceBuilderFactory` / `CoreBuilder::withEventDispatcher`).

### Freezing

Listeners run synchronously inside the SDK while the batch generator is between
packs, so a `sleep()` in the listener pauses the whole batch before the next
HTTP request is sent. No special pause/resume API is needed.

### Ready-made listener (opt-in)

A listener/subscriber provided by the SDK, configured with a threshold (e.g.
90): when an event at or above that threshold arrives, it sleeps until
`operatingResetAt` (plus a small safety margin), logs what it is doing via the
PSR-3 logger, and returns. If `operatingResetAt` is null or in the past it
does not sleep. Users register it on their dispatcher themselves.

### Fallback when the limit is hit anyway

The limit is unknown and other processes may use the same webhook/app, so 429
can still happen:

- `OperationTimeLimitExceededException` gains the blocked **method name** and
  **`operatingResetAt`**. The reset time comes from the last successful
  response seen for that method (per the official guidance); null if none was
  seen in this process. For an error inside a batch, the batch **command id**
  is included too.
- Today, an error inside a batch `result_error` thrown from
  `Response::getResponseData()` is wrapped into a generic `BaseException`
  ("api request error: ..."), losing its type. For `OPERATION_TIME_LIMIT` the
  original `OperationTimeLimitExceededException` must reach the caller (as the
  thrown exception, not only as `previous`).
- The same applies one level up: `Core\Batch::addEntityItems()`,
  `updateEntityItems()` and `deleteEntityItems()` currently catch every
  `\Throwable` and rethrow a generic `BaseException`. They must let
  `OperationTimeLimitExceededException` through unwrapped (still logging it),
  so a user of `batch()->update()` can catch it by type.
- **Scope decided by the maintainer:** only `Core\Batch` is changed. The ~54
  `Batch` subclasses in `src/Services` (e.g. `Services\Sale\*\Batch`) keep
  their current wrapping and are out of scope.
- This is observable behaviour: CHANGELOG `Unreleased` → `Changed`, marked
  as a behaviour change for code that caught `BaseException` and inspected
  the message.

## Constraints

- PHP 8.2–8.5, Symfony components `^6 || ^7` (no Symfony 7.1-only APIs).
- Must pass `make lint-all` (PHPStan level 5, Rector, CS-Fixer) and
  `make test-unit`.
- Unit tests with fake HTTP responses (no real portal) covering: single call,
  batch with several methods, multiple thresholds crossed at once, re-arming
  after `operating` drops, no-listener behaviour, missing `operating`,
  exception enrichment and type preservation.
- CHANGELOG entry under `Unreleased` → `Added` for events/listener/config,
  and `Changed` for the exception type preservation.
- Documentation page in `docs/EN/` with a usage example: register the
  ready-made listener on the dispatcher passed to `ServiceBuilderFactory`, then
  run a large `batch()->update()`.

## Known limitations

- If a batch pack contains both successful commands and an
  `OPERATION_TIME_LIMIT` error, the exception is thrown for the whole pack and
  results of the successful commands in that pack are not yielded. For `add`
  this means the client does not learn the new IDs of that pack. Solving this
  (partial results + retry of failed commands only) is out of scope for this
  issue; the threshold events are meant to keep clients from reaching this
  point.
- With a pack of 50 commands, events arrive after the pack completes, so the
  load observed can overshoot a threshold by the cost of one pack.

## Builder's calls

Calls made by the designer, not explicitly agreed with the maintainer:

- **Hook point.** Track `operating` where the response body is parsed
  (`Response::getResponseData()`), with a tracker object passed into
  `Response` by `Core` the same way the error handler and logger are, rather
  than parsing eagerly in `Core::call()`. This keeps parsing lazy and keeps
  `Batch` untouched.
- Configuration of limit and thresholds via `CoreBuilder` with defaults when
  not set; exact method names are left to the builder.
- `ServiceBuilderFactory` gets a new **optional** constructor parameter for
  that configuration (default = defaults above), so its users can change the
  limit (e.g. 420 s for self-hosted) without BC break. The static
  `createServiceBuilderFrom*` helpers stay unchanged and use the defaults;
  this is stated in the docs.
- Ready-made listener and configurable thresholds.

## Left to the builder

- Class and method names, namespaces for the tracker/config.
- Exact safety margin of the ready-made listener.
- How tracker state is reset (per `Core` instance lifetime is fine).
