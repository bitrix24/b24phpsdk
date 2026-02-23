# Testing Guide

## Overview

The test suite is split into two levels:

| Level | Location | Speed | Requires portal |
|---|---|---|---|
| **Unit** | `tests/Unit/` | Fast (in-memory) | No |
| **Integration** | `tests/Integration/` | Slow (HTTP) | Yes |

Unit tests use stub implementations of core interfaces (`NullCore`, `NullBatch`, `NullBulkItemsReader`) and never make HTTP calls. Integration tests connect to a real Bitrix24 portal via an incoming webhook.

---

## Requirements

- Docker and Docker Compose
- A Bitrix24 portal with an **incoming webhook** that has the required scopes enabled
- `tests/.env.local` file (see below)

---

## Environment setup

The Makefile loads `tests/.env` (committed defaults) and then merges `tests/.env.local` (local overrides, git-ignored).

Create `tests/.env.local`:

```dotenv
BITRIX24_WEBHOOK=https://your-portal.bitrix24.com/rest/1/your-webhook-token/
```

Optional fields (defaults are set in `tests/.env`):

```dotenv
INTEGRATION_TEST_LOG_LEVEL=100   # Monolog level (100 = DEBUG)
LOGS_FILE=sdk.log
INTEGRATION_TEST_OPEN_LINE_CODE= # required for IMOpenLines integration tests
```

---

## Running tests

### Unit tests

```bash
make test-unit
```

Runs the `unit_tests` PHPUnit suite inside Docker. No portal required.

### Integration tests (by scope)

Each integration suite maps to a specific Bitrix24 API scope. Run the one you need:

```bash
make test-integration-scope-crm
make test-integration-scope-lists
make test-integration-calendar-event
make test-integration-calendar-resource
make test-integration-im-open-lines-config
make test-integration-im-open-lines-crm-chat
make test-integration-im-open-lines-session
make test-integration-im-open-lines-operator
make test-integration-landing-page
make test-integration-landing-syspage
make test-integration-landing-repo
make test-integration-landing-demos
make test-integration-landing-role
make test-integration-sale-basket-property
make test-integration-sale-cashbox-handler
make test-integration-sale-cashbox
make test-integration-sale-delivery
make test-integration-sale-delivery-extra-service
make test-integration-sale-payment-item-basket
make test-integration-sale-payment-item-shipment
make test-integration-sale-property-relation
make test-integration-legacy-task
```

### Run a single test class

```bash
docker-compose run --rm php-cli vendor/bin/phpunit tests/Integration/Services/CRM/Deal/Service/DealTest.php
```

### Run a single test method

```bash
docker-compose run --rm php-cli vendor/bin/phpunit tests/Integration/Services/CRM/Deal/Service/DealTest.php --filter testAdd
```

---

## Code quality

Run all linters in sequence:

```bash
make lint-all
```

Individual linters:

| Target | Tool | Notes |
|---|---|---|
| `make lint-allowed-licenses` | license-checker | Validates dependency licenses |
| `make lint-cs-fixer` | php-cs-fixer | Check code style |
| `make lint-cs-fixer-fix` | php-cs-fixer | Auto-fix code style |
| `make lint-phpstan` | PHPStan | Static analysis |
| `make lint-rector` | Rector | Check upgrade rules |
| `make lint-rector-fix` | Rector | Auto-apply upgrade rules |

---

## Full make targets reference

### Docker

| Target | Description |
|---|---|
| `make docker-init` | First-time setup: build images, install dependencies |
| `make docker-up` | Build and start containers |
| `make docker-down` | Stop and remove containers |
| `make docker-down-clear` | Stop and remove containers + volumes |
| `make docker-pull` | Pull images (ignores pull failures) |
| `make docker-restart` | Restart all containers |

### Composer

| Target | Description |
|---|---|
| `make composer-install` | Install dependencies |
| `make composer-update` | Update dependencies |
| `make composer-dumpautoload` | Regenerate autoload |

### Lint

| Target | Description |
|---|---|
| `make lint-all` | Run all linters sequentially |
| `make lint-allowed-licenses` | Check dependency licenses |
| `make lint-cs-fixer` | Check code style |
| `make lint-cs-fixer-fix` | Fix code style |
| `make lint-phpstan` | Static analysis |
| `make lint-rector` | Check refactoring rules |
| `make lint-rector-fix` | Apply refactoring rules |

### Tests — unit

| Target | Description |
|---|---|
| `make test-unit` | Run all unit tests |

### Tests — integration (CRM)

| Target | Suite |
|---|---|
| `make test-integration-scope-crm` | Full CRM scope |

### Tests — integration (Lists)

| Target | Suite |
|---|---|
| `make test-integration-scope-lists` | Full Lists scope |
| `make test-integration-lists-service` | Lists service |
| `make test-integration-lists-field` | Lists fields |
| `make test-integration-lists-section` | Lists sections |
| `make test-integration-lists-element` | Lists elements |

### Tests — integration (IMOpenLines)

| Target | Suite |
|---|---|
| `make test-integration-scope-im-open-lines-connector` | Connector |
| `make test-integration-im-open-lines-config` | Config |
| `make test-integration-im-open-lines-crm-chat` | CRM Chat |
| `make test-integration-im-open-lines-session` | Session |
| `make test-integration-im-open-lines-operator` | Operator |

### Tests — integration (Calendar)

| Target | Suite |
|---|---|
| `make test-integration-calendar-event` | Events |
| `make test-integration-calendar-resource` | Resources |

### Tests — integration (Landing)

| Target | Suite |
|---|---|
| `make test-integration-landing-page` | Pages |
| `make test-integration-landing-syspage` | System pages |
| `make test-integration-landing-repo` | Repo |
| `make test-integration-landing-demos` | Demos |
| `make test-integration-landing-role` | Roles |
| `make test-integration-scope-landing-template` | Templates |

### Tests — integration (Sale)

| Target | Suite |
|---|---|
| `make test-integration-sale-basket-property` | Basket property |
| `make test-integration-sale-cashbox-handler` | Cashbox handler |
| `make test-integration-sale-cashbox` | Cashbox |
| `make test-integration-sale-delivery` | Delivery |
| `make test-integration-sale-delivery-extra-service` | Delivery extra service |
| `make test-integration-sale-payment-item-basket` | Payment item basket |
| `make test-integration-sale-payment-item-shipment` | Payment item shipment |
| `make test-integration-sale-property-relation` | Property relation |

### Tests — integration (Tasks)

| Target | Suite |
|---|---|
| `make test-integration-legacy-task` | Legacy task API (v1) |

---

## Test structure

```
tests/
├── Unit/
│   ├── Application/        # Application layer unit tests
│   ├── Core/               # Core (HTTP client, batch, credentials) unit tests
│   ├── Filters/            # Filter unit tests
│   ├── Infrastructure/     # Infrastructure unit tests
│   ├── OpenApi/            # OpenAPI unit tests
│   ├── Services/           # Service unit tests (mirrors src/Services/)
│   └── Stubs/              # Null/stub implementations (see below)
├── Integration/
│   ├── Core/               # Core integration tests
│   ├── Legacy/             # Legacy API v1 integration tests
│   └── Services/           # Service integration tests (mirrors src/Services/)
├── CustomAssertions/       # Shared assertion traits
├── Builders/               # Test builder helpers
├── Application/            # Application-level test helpers
├── ApplicationBridge/      # Webhook bridge for app-mode tests
├── bootstrap.php           # PHPUnit bootstrap (loads .env files)
├── .env                    # Default environment variables (committed)
└── .env.local              # Local overrides with your webhook (git-ignored)
```

---

## Patterns

### Unit test pattern

```php
#[CoversClass(MyService::class)]
class MyServiceTest extends TestCase
{
    private MyService $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new MyService(
            new NullCore(),   // no HTTP calls
            new NullBatch(),
            new NullLogger()
        );
    }

    #[Test]
    #[DataProvider('myDataProvider')]
    public function testSomeBehavior(string $input, string $expected): void
    {
        $this->assertEquals($expected, $this->service->process($input));
    }

    public static function myDataProvider(): Generator
    {
        yield 'case description' => ['input', 'expected'];
    }
}
```

Key conventions:
- Use `#[CoversClass]` so coverage reports are accurate
- Use `#[DataProvider]` for table-driven tests
- Prefer `createMock()` (strict `MockObject`) when you need to assert calls; use `createStub()` when you only need return values

### Integration test pattern

```php
class MyServiceTest extends TestCase
{
    use CustomBitrix24Assertions;

    private MyService $service;

    #[\Override]
    public function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getMyService();
    }

    #[\Override]
    public function tearDown(): void
    {
        // clean up entities created during the test
    }

    public function testAdd(): void
    {
        $result = $this->service->add(['TITLE' => 'Test']);
        $this->assertGreaterThan(0, $result->getId());
    }
}
```

Key conventions:
- Call `Factory::getServiceBuilder()` (or `Factory::getCore()`) in `setUp()`
- Clean up created records in `tearDown()` to keep the portal tidy
- Use `CustomBitrix24Assertions` for domain-specific assertions

### Contract tests

Abstract base classes enforce interface contracts across implementations:

```php
abstract class AbstractRepositoryInterfaceTest extends TestCase
{
    abstract protected function getRepository(): MyRepositoryInterface;

    public function testFind(): void { /* shared contract assertion */ }
}

class ConcreteRepositoryTest extends AbstractRepositoryInterfaceTest
{
    protected function getRepository(): MyRepositoryInterface
    {
        return Factory::getServiceBuilder()->getConcreteRepository();
    }
}
```

---

## Stubs

Located in `tests/Unit/Stubs/`, these implement core interfaces without making any HTTP calls:

| Stub | Interface | Purpose |
|---|---|---|
| `NullCore` | `CoreInterface` | Returns empty `Response` objects; use in service unit tests |
| `NullBatch` | `BatchInterface` | Returns empty batch results |
| `NullBulkItemsReader` | `BulkItemsReaderInterface` | Returns an empty traversable |

---

## Custom assertions

`tests/CustomAssertions/CustomBitrix24Assertions.php` is a trait providing domain-specific assertions:

- **`assertBitrix24AllResultItemFieldsAnnotated(array $fieldCodesFromApi, string $resultItemClassName)`** — verifies that every field returned by the API is documented in the result item's PHPDoc `@property` annotations. Use this in integration tests after fetching a real entity to catch undocumented fields.

---

## Troubleshooting

**PHPUnit can't write to `var/` or log files**

The container runs as `www-data`. Fix permissions with:
```bash
docker-compose run --rm php-cli chown -R www-data:www-data /var/www/html/var/
```

**PHPStan cache errors after a rebase or major refactor**

Clear the cache manually:
```bash
docker-compose run --rm php-cli vendor/bin/phpstan clear-result-cache
```

**Integration tests fail with 401 / "Wrong webhook"**

Your webhook has expired or was revoked. Generate a new incoming webhook in Bitrix24 (Settings → Developer resources → Incoming webhook) and update `tests/.env.local`.

**Integration tests fail with "scope not available"**

The webhook user does not have the required scope enabled. Edit the webhook in Bitrix24 and enable the missing scope (e.g., `crm`, `lists`, `imopenlines`).
