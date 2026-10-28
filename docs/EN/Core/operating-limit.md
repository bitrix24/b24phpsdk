# Operating time limit events

Bitrix24 limits how long each REST method may run for one application or webhook.
Execution time is summed per method over a sliding 10-minute window (ten one-minute baskets).
When the sum exceeds the limit, the method is blocked with HTTP 429 and error `OPERATION_TIME_LIMIT`
until the oldest baskets drop out. Other methods keep working.
See [REST API limits](https://apidocs.bitrix24.com/limits.html).

Every response carries this state in the `time` object:

- `operating` — accumulated execution time of the method, seconds;
- `operating_reset_at` — Unix timestamp when the oldest basket is excluded from `operating`.

For `batch` the outer call is not counted; each nested command is counted against its own method.

Long batch writes (`*.add`, `*.update` over thousands of items) usually hit this limit first.
The SDK watches `operating` and tells your code when a method gets close, so you decide what to do.

## Threshold events

When a method's `operating` crosses a threshold of the limit, the SDK dispatches
`Bitrix24\SDK\Events\OperatingThresholdCrossedEvent` through the event dispatcher you pass to
`ServiceBuilderFactory` (the same one used for `AuthTokenRenewedEvent`).

Event payload:

| Getter                    | Meaning                                                      |
|---------------------------|--------------------------------------------------------------|
| `getApiMethod()`          | method name, for example `crm.contact.update`                |
| `getOperating()`          | accumulated execution time, seconds                          |
| `getOperatingResetAt()`   | `CarbonImmutable` when the oldest basket is released, or null |
| `getThresholdPercent()`   | threshold that was crossed                                   |
| `getLimitSeconds()`       | configured limit                                             |

Rules:

- each threshold fires once per method when it is crossed upward;
- if one response jumps over several thresholds, one event per threshold is dispatched, in ascending order;
- when `operating` drops below a threshold, the threshold is re-armed;
- if the load is still above a threshold but a newer `operating_reset_at` arrived (the window moved),
  the event for that threshold is dispatched again.

Listeners run synchronously between batch packs, so sleeping inside a listener pauses the batch
before the next request; when the listener returns, the batch continues where it stopped.

## Defaults and configuration

- limit: **480 seconds** (the cloud limit is not published, 480 s is the documented example);
- thresholds: **10, 20, 30, 40, 50, 60, 70, 80, 90** percent.

```php
use Bitrix24\SDK\Core\OperatingLimit\OperatingLimitConfig;
use Bitrix24\SDK\Services\ServiceBuilderFactory;

// for example, self-hosted Bitrix24 with the default 420 s limit
$factory = new ServiceBuilderFactory($dispatcher, $logger, new OperatingLimitConfig(420.0, [50, 80, 90]));
$serviceBuilder = $factory->initFromWebhook($webhookUrl);
```

`CoreBuilder::withOperatingLimitConfig()` does the same for a manually built core.
The static helpers `ServiceBuilderFactory::createServiceBuilderFrom*()` always use the defaults.

## Ready-made listener

`OperatingLimitSleepListener` sleeps until `operating_reset_at` (plus a safety margin) when an event
at or above its threshold arrives:

```php
use Bitrix24\SDK\Core\OperatingLimit\OperatingLimitSleepListener;
use Bitrix24\SDK\Events\OperatingThresholdCrossedEvent;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();
$dispatcher->addListener(OperatingThresholdCrossedEvent::class, new OperatingLimitSleepListener(90, 1, $logger));

$serviceBuilder = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhookUrl, $dispatcher, $logger);

foreach ($serviceBuilder->getCRMScope()->contact()->batch->update($contactsToUpdate) as $id => $result) {
    // the loop pauses here when crm.contact.update reaches 90% of the window
}
```

## When the limit is hit anyway

The real limit may differ from the configured one, and other processes may use the same webhook,
so a 429 can still happen. `Bitrix24\SDK\Core\Exceptions\OperationTimeLimitExceededException`
reaches your code with its own type (also through `Core\Batch` add/update/delete) and carries:

- `apiMethod` — blocked method, if known;
- `batchCommandId` — command id when the error came from a batch request;
- `operatingResetAt` — Unix timestamp from the last successful response of this method in this process, or null.

```php
use Bitrix24\SDK\Core\Exceptions\OperationTimeLimitExceededException;

try {
    foreach ($serviceBuilder->getCRMScope()->deal()->batch->update($deals) as $result) {
        // ...
    }
} catch (OperationTimeLimitExceededException $exception) {
    // retry later, starting from $exception->operatingResetAt
}
```

## Known limitations

- If a batch pack has both successful commands and an `OPERATION_TIME_LIMIT` error, the exception is thrown
  for the whole pack and the successful results of that pack are not returned (for `add` the new IDs of
  that pack are lost). The threshold events are meant to keep you from reaching this point.
- Events arrive after a whole pack (up to 50 commands) is done, so the load can overshoot a threshold by
  the cost of one pack.
- Batch classes of individual services (for example `Services\Sale\*\Batch`) still wrap errors into
  `BaseException`.
