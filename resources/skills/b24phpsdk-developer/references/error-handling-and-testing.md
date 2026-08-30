# Error Handling and Testing

## Application Boundary

Catch SDK exceptions at the boundary where the product talks to Bitrix24:

```php
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;

try {
    $contacts = $contactService->list(['ID' => 'ASC'], [], ['ID', 'NAME'], 0)->getContacts();
} catch (TransportException $exception) {
    $logger->error('Bitrix24 transport error while reading contacts', [
        'method' => 'crm.contact.list',
        'exception' => $exception::class,
    ]);

    throw new RuntimeException('Bitrix24 is temporarily unavailable', 0, $exception);
} catch (BaseException $exception) {
    $logger->warning('Bitrix24 API error while reading contacts', [
        'method' => 'crm.contact.list',
        'exception' => $exception::class,
    ]);

    throw new RuntimeException('Bitrix24 rejected the contact read request', 0, $exception);
}
```

Log method, scope, portal identifier, correlation IDs, and product record IDs when useful. Do not log
webhook URLs, OAuth access tokens, refresh tokens, client secrets, or raw request payloads that may
contain credentials.

Do not log exception messages or traces by default. SDK exceptions can wrap transport-level details
from lower layers; include them only after the product has explicit redaction for URLs, tokens,
headers, and payloads.

## Retry Policy

Keep retries centralized in infrastructure code. Retry only:

- idempotent reads;
- writes with an explicit idempotency or reconciliation strategy;
- transient transport failures or documented rate-limit errors.

Do not retry validation failures, authorization failures, malformed requests, or unknown write
outcomes without product-specific recovery logic.

## Unit Tests

Unit-test product logic against product-owned interfaces:

```php
final class FakeContactDirectory implements ContactDirectory
{
    public function recentContacts(int $limit): array
    {
        return [
            ['id' => 1, 'name' => 'Ada Lovelace'],
        ];
    }
}
```

This keeps unit tests fast and independent from Bitrix24 network access.

## Integration Tests

Live Bitrix24 tests should be explicit and opt-in:

```php
$webhookUrl = getenv('BITRIX24_WEBHOOK');
if ($webhookUrl === false || $webhookUrl === '') {
    self::markTestSkipped('BITRIX24_WEBHOOK is required for live Bitrix24 checks');
}
```

Use live integration tests to verify credentials, scopes, SDK wiring, and the small set of workflows
that cannot be proven with fakes.
