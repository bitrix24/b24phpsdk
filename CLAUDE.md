# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Bitrix24 REST API PHP SDK - An official PHP library providing typed, developer-friendly access to Bitrix24 REST API with support for webhooks, OAuth applications, batch operations, and automatic token renewal.

**PHP Version:** 8.2, 8.3, or 8.4
**Current SDK Version:** 1.8.0

## Development Environment

All development commands run inside Docker containers via `docker-compose`. The main container is `php-cli` (PHP 8.4-cli on Debian Bookworm).

### Essential Commands

```bash
# Initial setup
make docker-init              # Build containers, install dependencies

# Daily workflow
make docker-up                # Start containers
make docker-down              # Stop containers

# Dependencies
make composer-install         # Install composer dependencies
make composer-update          # Update dependencies
```

### Code Quality & Testing

```bash
# Run ALL linters sequentially
make lint-all                 # Runs: licenses, cs-fixer, phpstan, rector

# Individual linters
make lint-phpstan             # Static analysis (PHPStan level 5)
make lint-rector              # Code quality checks
make lint-rector-fix          # Auto-fix code quality issues
make lint-cs-fixer            # Code style check (PHP-CS-Fixer)
make lint-cs-fixer-fix        # Auto-fix code style
make lint-allowed-licenses    # Verify dependency licenses (MIT, BSD-3, Apache only)

# Testing
make test-unit                # Fast in-memory unit tests

# Integration tests (require real Bitrix24 portal - see below)
make test-integration-core
make test-integration-scope-crm
make test-integration-scope-telephony
make test-integration-scope-user
# ... many more scope-specific test targets in Makefile
```

### Running Single Tests

```bash
# Run specific test class
docker-compose run --rm php-cli vendor/bin/phpunit tests/Unit/Core/Credentials/VersionedScopeTest.php

# Run specific test method
docker-compose run --rm php-cli vendor/bin/phpunit --filter testConstructorWithValidScopes
```

## Architecture

### Layered Architecture

The SDK uses a clean layered architecture with clear separation of concerns:

```
┌─────────────────────────────────────────────────────┐
│ Services Layer (src/Services/*)                     │
│ - ServiceBuilder: Main entry point                  │
│ - Scope-specific builders: CRMServiceBuilder, etc.  │
│ - Service classes: typed methods, DTO results       │
│ - Result DTOs: strongly-typed response objects      │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ Core Layer (src/Core/*)                             │
│ - ApiClient: HTTP client, handles auth & requests   │
│ - Batch: Batch operations with generators           │
│ - Credentials: Auth tokens, webhooks, endpoints     │
│ - ApiLevelErrorHandler: Processes B24 error codes   │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ Infrastructure Layer                                 │
│ - Symfony HttpClient (HTTP/2)                       │
│ - PSR-3 Logger                                       │
│ - Event Dispatcher                                   │
└─────────────────────────────────────────────────────┘
```

### Key Design Patterns

1. **Service Builder Pattern**: Each Bitrix24 scope (CRM, Sale, User, etc.) has a dedicated `*ServiceBuilder` that provides access to scope-specific services
2. **Factory Pattern**: `ServiceBuilderFactory` creates service builders from webhooks, placement requests, or OAuth credentials
3. **Generator Pattern**: Batch operations use PHP generators for constant memory usage regardless of data size
4. **DTO Pattern**: All API responses wrapped in strongly-typed Result objects (e.g., `LeadItemResult`, `DealItemResult`)
5. **Immutable Value Objects**: Credentials, Scope, AuthToken are immutable

### Directory Structure

```
src/
├── Core/                    # Low-level API client, batch, credentials
│   ├── ApiClient.php       # Main HTTP client
│   ├── Batch.php           # Batch operations with generators
│   ├── Credentials/        # Auth tokens, webhooks, endpoints
│   └── Response/           # Response parsing and DTOs
├── Services/               # High-level typed services
│   ├── ServiceBuilder.php  # Main service entry point
│   ├── CRM/               # CRM scope (deals, leads, contacts, etc.)
│   ├── Sale/              # E-commerce scope
│   ├── Task/              # Tasks and projects
│   ├── Calendar/          # Calendar events
│   └── [other scopes]/    # User, Telephony, IM, etc.
└── Application/           # Application-level contracts
    └── Contracts/         # Interfaces for app installations, accounts

tests/
├── Unit/                  # Fast in-memory tests
├── Integration/           # Tests with real B24 portal (slow)
│   ├── Core/             # Core functionality tests
│   └── Services/         # Scope-specific integration tests
└── bootstrap.php         # Test environment setup
```

## Integration Tests Setup

Integration tests require a **test** Bitrix24 account (never use production):

1. Create new Bitrix24 account for testing
2. Navigate to: Sitemap → Developer resources → Other → Inbound webhook
3. Grant all permissions to webhook
4. Create file `tests/.env.local`:
```bash
APP_ENV=dev
BITRIX24_WEBHOOK=https://your-test-portal.bitrix24.com/rest/1/webhook_code/
INTEGRATION_TEST_LOG_LEVEL=500
```

## Working with CHANGELOG.md

The CHANGELOG follows [Keep a Changelog](https://keepachangelog.com/) format with version sections:

- **Added**: New features
- **Changed**: Changes in existing functionality (including Breaking Changes)
- **Deprecated**: Soon-to-be removed features
- **Removed**: Removed features
- **Fixed**: Bug fixes
- **Security**: Security vulnerabilities

### Breaking Changes Documentation

Mark breaking changes clearly with `**Breaking changes**` or `❗**BC**`:

```markdown
### Changed

- **Breaking changes** in `SomeInterface` ([issue link]):
    - `methodName()` now returns `NewType` instead of `OldType`
    - Removed `oldMethod()` - use `newMethod()` instead
    - Migration path: [clear instructions for users]
```

## Code Conventions

### Result DTOs

All API method results return typed DTO objects with PHPDoc annotations:

```php
/**
 * @property-read int $ID
 * @property-read string $TITLE
 * @property-read CarbonImmutable|null $DATE_CREATE
 * @property-read Money|null $OPPORTUNITY
 */
class LeadItemResult extends AbstractCrmItem
```

- Use Bitrix24 field naming (uppercase with underscores)
- Map Bitrix24 types to PHP objects: `datetime` → `CarbonImmutable`, money amounts → `Money`, etc.
- All properties are read-only via `@property-read`

### Type Mapping from Bitrix24 API

- `datetime` → `CarbonImmutable|null`
- `date` → `CarbonImmutable|null`
- Money amounts → `Money|null` (with `Currency`)
- Boolean flags (Y/N) → `bool|null`
- IDs → `int|null`
- User fields prefixed with `UF_`

### Service Methods

```php
public function get(int $id): LeadItemResult
{
    return new LeadItemResult(
        $this->core->call('crm.lead.get', ['id' => $id])
    );
}
```

- All service methods must have return type declarations
- Use typed parameters (no mixed types)
- Throw specific exceptions from `Bitrix24\SDK\Core\Exceptions\`

### Naming Conventions

- ServiceBuilders: `{Scope}ServiceBuilder` (e.g., `CRMServiceBuilder`)
- Services: `{Entity}` (e.g., `Lead`, `Deal`, `Contact`)
- Result DTOs: `{Entity}ItemResult` or `{Entity}sResult` (collection)
- Batch services: `Batch` class in same namespace as service

## Important Implementation Details

### Batch Operations

Use generators for memory-efficient batch operations:

```php
// Reading large datasets
$leads = $serviceBuilder->getCRMScope()->lead()->list([], ['ID', 'TITLE']);
foreach ($leads as $lead) {
    // Process one at a time - constant memory usage
}

// Batch writing
$batch = $serviceBuilder->getCRMScope()->lead()->batch();
foreach ($generator as $leadData) {
    $batch->add($leadData);
}
```

### Event Handling

The SDK emits events for:
- `AuthTokenRenewedEvent`: When access token is renewed
- `PortalDomainUrlChangedEvent`: When B24 portal URL changes

Use Symfony Event Dispatcher to listen.

### Credentials & Authentication

Three auth modes:
1. **Webhook**: `ServiceBuilderFactory::createServiceBuilderFromWebhook($webhookUrl)`
2. **OAuth (placement)**: `ServiceBuilderFactory::createServiceBuilderFromPlacementRequest($request, $appProfile)`
3. **Manual**: Build `Credentials` with `AuthToken` and `ApplicationProfile`

Auto-renewal of expired tokens is built-in.

### Scope System

Bitrix24 API organized by scopes (permission groups). SDK mirrors this:
- `$builder->getCRMScope()` → CRM methods
- `$builder->getUserScope()` → User methods
- `$builder->getSaleScope()` → E-commerce methods

Each scope has a `*ServiceBuilder` providing service access.

## Common Workflows

### Adding Support for New Bitrix24 Method

1. Identify the scope (e.g., `crm`, `sale`, `task`)
2. Find or create corresponding `Service` class in `src/Services/{Scope}/`
3. Add method to service class
4. Create Result DTO if needed in `Result/` subdirectory
5. Add PHPDoc annotations matching B24 field types
6. Write unit test in `tests/Unit/Services/{Scope}/`
7. Write integration test in `tests/Integration/Services/{Scope}/`
8. Update CHANGELOG.md under `## Unreleased` → `### Added`
9. Run linters: `make lint-all`
10. Run tests: `make test-unit` and relevant integration tests

### Fixing Type Annotations

When Bitrix24 types don't match SDK annotations:
1. Check official docs: https://apidocs.bitrix24.ru or https://apidocs.bitrix24.com
2. Update PHPDoc in Result DTO
3. Add type conversion in constructor if needed
4. Document in CHANGELOG under `### Fixed` with `❗**BC**` if breaking
5. Write test case covering the type

### Working with Application Contracts

The `src/Application/Contracts/` namespace provides production-ready interfaces for marketplace applications:
- `Bitrix24Accounts`: Auth token storage and account management
- `ApplicationInstallations`: Installation lifecycle management
- `ContactPersons`: Contact person data
- `Bitrix24Partners`: Partner information

Implement these interfaces and test with contract tests in `tests/Unit/Application/Contracts/`.

## Documentation

- Main docs: `docs/EN/README.md`
- API coverage: `docs/EN/Services/bitrix24-php-sdk-methods.md`
- Contract docs: `src/Application/Contracts/{Context}/Docs/`
- Official B24 API: https://apidocs.bitrix24.com/ (English) or https://apidocs.bitrix24.ru/ (Russian)

## Troubleshooting

### "Permission denied" in Docker
```bash
docker-compose run --rm php-cli chown -R www-data:www-data /var/www/html/var/
```

### PHPStan/Rector cache issues
```bash
rm -rf var/.cache/
make lint-phpstan
```

### Integration tests failing
- Verify `.env.local` webhook URL is correct and not expired
- Check webhook has all required permissions
- Ensure using a test portal, not production

### Type errors in Result DTOs
- Check actual API response with `$this->core->call()` in debugger
- Compare with official docs
- Bitrix24 sometimes returns inconsistent types (handle with `|null`)