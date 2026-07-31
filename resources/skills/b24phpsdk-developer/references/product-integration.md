# Product Integration

## Install and Inspect

Install the SDK in the product application:

```bash
composer require bitrix24/b24phpsdk
composer show bitrix24/b24phpsdk
```

Before using a service, inspect the installed version:

```bash
rg -n "function contact|class Contact" vendor/bitrix24/b24phpsdk/src/Services
```

Use the API that exists in the installed package. The SDK evolves, so do not assume examples from a
different branch match the consumer's version.

## Webhook Initialization

Use an incoming webhook for backend jobs, CLI tools, and simple server-side integrations scoped to
one Bitrix24 portal:

```php
use Bitrix24\SDK\Services\ServiceBuilderFactory;

$serviceBuilder = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhookUrl);
```

The webhook URL must come from environment or configuration:

```php
$webhookUrl = getenv('BITRIX24_WEBHOOK');
if ($webhookUrl === false || $webhookUrl === '') {
    throw new RuntimeException('BITRIX24_WEBHOOK is required');
}
```

Never commit real webhook URLs. They include credentials.

## OAuth Boundary

Use OAuth when the product is a Bitrix24 application that owns installation, token persistence, and
refresh flow. Inspect `ServiceBuilderFactory` in the installed SDK version for the available OAuth
factory methods. In current SDK versions, OAuth-oriented initialization is built around
`ApplicationProfile`, `AuthToken`, portal endpoints, and application account objects.

Keep OAuth token refresh and SDK construction at the infrastructure boundary. Domain services should
not parse placement requests, store tokens, or construct SDK credentials.

## Hello World: Read CRM Contacts

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Bitrix24\SDK\Services\ServiceBuilderFactory;

$webhookUrl = getenv('BITRIX24_WEBHOOK');
if ($webhookUrl === false || $webhookUrl === '') {
    fwrite(STDERR, "BITRIX24_WEBHOOK is required\n");
    exit(1);
}

$contacts = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhookUrl)
    ->getCRMScope()
    ->contact()
    ->list(['ID' => 'ASC'], [], ['ID', 'NAME', 'LAST_NAME'], 0)
    ->getContacts();

echo json_encode(
    [
        'count' => count($contacts),
        'items' => array_map(
            static fn ($contact): array => [
                'id' => (int)$contact->ID,
                'name' => trim(sprintf('%s %s', $contact->NAME ?? '', $contact->LAST_NAME ?? '')),
            ],
            $contacts,
        ),
    ],
    JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT,
) . PHP_EOL;
```

Run it with:

```bash
BITRIX24_WEBHOOK="https://example.bitrix24.com/rest/1/secret/" php hello-world.php
```

## Dependency Injection

Construct SDK services at the edge of the application:

```php
$serviceBuilder = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhookUrl);
$contactService = $serviceBuilder->getCRMScope()->contact();
```

Wrap SDK services in product-owned adapters before passing them into business code. This gives the
product a stable contract even when SDK service signatures or result wrappers change between SDK
versions.
