# Architecture Guide

## Layer Diagram

```
┌─────────────────────────────────────────────────────────┐
│                        Services                          │
│  src/Services/CRM/, src/Services/Sale/, ...             │
│  Business-level Bitrix24 REST API wrappers              │
└──────────────────────┬──────────────────────────────────┘
                       │ depends on
          ┌────────────┴────────────┐
          ▼                         ▼
┌─────────────────┐     ┌──────────────────────┐
│   Application   │     │     Infrastructure    │
│  src/Application│     │  src/Infrastructure/  │
│  OAuth flows,   │     │  Console commands,    │
│  local portals  │     │  HTTP client adapters │
└────────┬────────┘     └──────────┬────────────┘
         │                         │
         └──────────┬──────────────┘
                    │ depends on
                    ▼
          ┌──────────────────┐
          │       Core        │
          │   src/Core/       │
          │  Contracts, HTTP, │
          │  Credentials,     │
          │  Exceptions       │
          └──────────────────┘
                    ▲
                    │ depends on
          ┌──────────────────┐
          │      Legacy       │
          │   src/Legacy/     │
          │  Task v1 API,     │
          │  deprecated wrappers│
          └──────────────────┘
```

> **Key rule**: arrows point toward dependencies. Outer layers can use inner layers; inner layers CANNOT use outer layers. `Core` depends on nothing inside the SDK.

Architectural boundaries are enforced by **deptrac** (`make lint-deptrac`). See `deptrac.yaml` for the machine-readable ruleset.

---

## Layers

### Core (`src/Core/`)

The foundational layer. Contains:

| Sub-directory | Purpose |
|---|---|
| `Contracts/` | Interfaces: `CoreInterface`, `ApiClientInterface`, `BatchOperationsInterface`, `BulkItemsReaderInterface` |
| `Credentials/` | `Scope`, `AuthToken`, `Webhook`, `ApplicationProfile` |
| `Exceptions/` | Typed exceptions: `BaseException`, `TransportException`, `InvalidArgumentException`, etc. |
| `Result/` | Base result types: `AbstractResult`, `AbstractItem`, `AddedItemResult`, `DeletedItemResult`, etc. |
| `Requests/` | Request value objects |
| `Response/` | Raw API response parsing |

**Dependency rule**: Core imports NOTHING from other SDK layers.

**Catches for agents**: When writing Core code, do NOT import from `Services\`, `Application\`, or `Infrastructure\`.

---

### Application (`src/Application/`)

Handles OAuth app lifecycle and local portal management.

| Sub-directory | Purpose |
|---|---|
| `Contracts/` | Application-level interfaces |
| `Local/` | Local application management |
| `Workflows/` | OAuth authorization flows |
| `Requests/` | Application request value objects |

**Dependency rule**: May import from `Core` only.

---

### Infrastructure (`src/Infrastructure/`)

Adapters for external systems (console, HTTP, filesystem).

| Sub-directory | Purpose |
|---|---|
| `Console/` | Symfony Console commands (`bin/console`) |
| `HttpClient/` | Symfony HTTP Client adapter wrapping `CoreInterface` |
| `Filesystem/` | File utilities |

**Dependency rule**: May import from `Core` only.

---

### Services (`src/Services/`)

One directory per Bitrix24 API scope. Each scope follows the same layout:

```
src/Services/<Scope>/
├── <Scope>ServiceBuilder.php          ← factory, registered in ServiceBuilder
├── Service/
│   ├── <Entity>.php                   ← main service (extends AbstractService)
│   └── Batch.php                      ← batch wrapper (when applicable)
└── Result/
    ├── <Entity>ItemResult.php         ← single item (@property-read annotations)
    ├── <Entity>Result.php             ← single-entity response
    └── <Entity>sResult.php            ← list response
```

**Dependency rule**: May import from `Core` and `Application`. Services MUST NOT import from each other or from `Infrastructure`.

**Real example**: `src/Services/CRM/Deal/`

```
CRM/Deal/
├── Service/
│   ├── Deal.php           ← crm.deal.* methods
│   ├── Batch.php          ← batch-mode deal operations
│   ├── DealCategory.php   ← crm.dealcategory.* methods
│   └── ...
└── Result/
    ├── DealItemResult.php
    ├── DealResult.php
    └── DealsResult.php
```

---

### Legacy (`src/Legacy/`)

Wraps Bitrix24 Task API v1 (deprecated endpoint format). New development goes into `src/Services/`, not here.

**Dependency rule**: May import from `Core`, `Application`, and `Services`.

---

## Service Registration

Every service scope has a `<Scope>ServiceBuilder.php` that creates service instances. These are wired together in `src/Services/ServiceBuilder.php` which is the single entry point for SDK consumers:

```php
// Consumer code
$serviceBuilder = (new ServiceBuilderFactory())->initFromWebhook($webhookUrl);
$deal = $serviceBuilder->getCRMScope()->deal();
$result = $deal->get(42);
```

To add a new scope:
1. Create `src/Services/<Name>/<Name>ServiceBuilder.php`
2. Add a `get<Name>Scope(): <Name>ServiceBuilder` method to `src/Services/ServiceBuilder.php`
3. Follow the pattern used by `getCRMScope()` or `getSaleScope()`

---

## Result Item Pattern

All API responses are wrapped in result objects. The pattern uses PHP magic `__get()` to expose raw API fields:

```php
// AbstractItem base class provides magic __get()
// Result item only needs @property-read annotations for IDE support + static analysis

/**
 * @property-read int    $ID
 * @property-read string $TITLE
 * @property-read string $STATUS_ID
 */
class DealItemResult extends AbstractItem {}

// Usage
$deal = $dealService->get(42)->deal();
echo $deal->ID;     // int, type-safe via PHPDoc
echo $deal->TITLE;  // string
```

The `assertBitrix24AllResultItemFieldsAnnotated()` custom assertion (in integration tests) verifies that every field returned by the API is annotated in the result item's PHPDoc.

---

## Attributes

Two custom PHP 8 attributes drive the SDK coverage documentation system:

| Attribute | Location | Purpose |
|---|---|---|
| `#[ApiServiceMetadata(new Scope([...]))]` | Service class | Declares which Bitrix24 scope this service needs |
| `#[ApiEndpointMetadata('method.name', 'docs-url', 'description')]` | Service method | Documents each REST method call |

These feed `make show-sdk-coverage-statistics` and `make build-documentation`.

---

## Adding a New Service: Step-by-Step Checklist

- [ ] Create `src/Services/<Name>/` directory structure (see above)
- [ ] Implement `<Name>Service.php` extending `AbstractService`
- [ ] Add `#[ApiServiceMetadata]` and `#[ApiEndpointMetadata]` attributes
- [ ] Create result item classes with `@property-read` annotations
- [ ] Create `<Name>ServiceBuilder.php`
- [ ] Register in `ServiceBuilder.php`
- [ ] Write `tests/Unit/Services/<Name>/Service/<Name>ServiceTest.php`
- [ ] Write `tests/Integration/Services/<Name>/Service/<Name>ServiceTest.php`
- [ ] Add integration test suite to `phpunit.xml.dist`
- [ ] Add `make test-integration-<name>` target to `Makefile`
- [ ] Run `make lint-all` and `make test-unit` — both must pass

---

## Dependency Enforcement

Architectural rules are enforced at three levels:

| Level | Tool | When |
|---|---|---|
| Local pre-commit | captainhook + phpstan | Every `git commit` |
| Local manual | `make lint-deptrac` | On demand |
| CI | `.github/workflows/deptrac.yml` | Every push / PR |

If deptrac reports a violation, do NOT add an exception — fix the import instead. The only allowed workaround is adding a `# deptrac-ignore-line` comment with a detailed explanation.
