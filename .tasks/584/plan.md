# Plan: Add `catalog.storeproduct.*` methods support (issue #584)

## Context

The `catalog.storeproduct` entity represents product stock/quantity records per warehouse
(store). It is a **read-only** entity in the Bitrix24 REST API — only three methods exist,
confirmed via `mcp__Bitrix24_REST_API__bitrix-method-details` and a live webhook call against
`https://ignatenko.bitrix24.com`:

- `catalog.storeproduct.get(id)` — returns a single `storeProduct` record by its record id.
  https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-get.html
- `catalog.storeproduct.list(select, filter, order)` — returns a filtered/ordered list of
  `storeProduct` records plus `total`. The `order` parameter is not documented in the
  "Parameters" table but works in practice (verified live) and is used in the official PHP
  example, matching the `ProductPropertySection::list()` precedent.
  https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-list.html
- `catalog.storeproduct.getFields()` — returns field metadata wrapped in a `storeProduct` key
  (same envelope shape as `catalog.priceType.getFields`).
  https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-get-fields.html

No `add`/`update`/`delete` methods exist for this entity (verified against docs — stock is
managed via `catalog.document.*` warehouse accounting documents, not directly).

### Confirmed field set (live response + docs match exactly)

| Field | Bitrix type | PHP annotation | Nullable |
|---|---|---|---|
| `id` | integer | `int` | no |
| `productId` | integer | `int` | no |
| `storeId` | integer | `int` | no |
| `amount` | double | `float` | no |
| `quantityReserved` | double | `float\|null` | yes (observed `null` live) |

All fields are `isReadOnly: true` per `getFields()`.

### Response envelope keys

- `get` → `result.storeProduct` (single object)
- `list` → `result.storeProducts` (array) + `result.total` (sibling key, not nested)
- `getFields` → `result.storeProduct` (field-description map, same key name as `get` but
  different shape — this matches the `catalog.priceType.getFields` → `result.priceType`
  precedent, so a dedicated `StoreProductFieldsResult` class is required; the generic
  `Core\Result\FieldsResult` cannot be reused because it reads the top-level `result` directly)

### Architectural decisions

- **No Batch class.** The entity is read-only (`get`/`list`/`getFields` only, no
  add/update/delete), matching the existing `ProductPropertySection` and `Measure` scopes in
  this codebase, neither of which has a `Batch.php` or constructor-injected batch dependency.
  No other read-only Catalog scope uses `Core\Batch::getTraversableList()` for bulk `list`
  reading, so this plan does not introduce one either — consistent with precedent.
- **Result-item generator not used.** `docs/open-api/openapi.json` (rebuilt via
  `make oa-schema-build`) has no `catalog_storeproduct` schema entry, so
  `b24-dev:result-item-generator` has no source to run against. `StoreProductItemResult` is
  written manually following the `PriceTypeItemResult` / `ProductPropertySectionItemResult`
  pattern (5 flat scalar fields, no nested objects, no dates — no `SelectBuilder`/`ItemBuilder`
  generator applies either, since those are for CRM smart-process-like entities with typed
  builders and this scope's `list()`/`getFields()` are hand-rolled elsewhere in Catalog too).
- Field naming stays exactly as returned by the API (`productId`, `storeId`, `quantityReserved`
  — camelCase, not the CRM `UPPER_SNAKE` convention), matching `PriceTypeItemResult`.

---

## Files to Create

### 1. `src/Services/Catalog/StoreProduct/Result/StoreProductItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\StoreProduct\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int        $id
 * @property-read int        $productId
 * @property-read int        $storeId
 * @property-read float      $amount
 * @property-read float|null $quantityReserved
 */
class StoreProductItemResult extends AbstractAnnotatedItem
{
}
```

### 2. `src/Services/Catalog/StoreProduct/Result/StoreProductResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\StoreProduct\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class StoreProductResult extends AbstractResult
{
    public function storeProduct(): StoreProductItemResult
    {
        return new StoreProductItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()['storeProduct']
        );
    }
}
```

### 3. `src/Services/Catalog/StoreProduct/Result/StoreProductsResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\StoreProduct\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class StoreProductsResult extends AbstractResult
{
    /**
     * @return StoreProductItemResult[]
     * @throws BaseException
     */
    public function getStoreProducts(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['storeProducts'] as $item) {
            $res[] = new StoreProductItemResult($item);
        }

        return $res;
    }

    /**
     * @throws BaseException
     */
    public function getTotal(): int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getTotal() ?? 0;
    }
}
```

Confirmed accessor by precedent: `Catalog\Measure\Result\MeasuresResult::getTotal()` and
`Catalog\Extra\Result\ExtrasResult` both read the top-level `total` via
`getResponseData()->getPagination()->getTotal()`, not via `getResult()['total']`. Follow the
same pattern here.

### 4. `src/Services/Catalog/StoreProduct/Result/StoreProductFieldsResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\StoreProduct\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class StoreProductFieldsResult extends AbstractResult
{
    /**
     * @return array<string, array<string, mixed>>
     * @throws BaseException
     */
    public function getFieldsDescription(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['storeProduct'];
    }
}
```

### 5. `src/Services/Catalog/StoreProduct/Service/StoreProduct.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\StoreProduct\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\StoreProduct\Result\StoreProductFieldsResult;
use Bitrix24\SDK\Services\Catalog\StoreProduct\Result\StoreProductResult;
use Bitrix24\SDK\Services\Catalog\StoreProduct\Result\StoreProductsResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class StoreProduct extends AbstractService
{
    /**
     * Returns information about product stock by record identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.storeproduct.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-get.html',
        'Returns information about product stock by record identifier.'
    )]
    public function get(int $id): StoreProductResult
    {
        $this->guardPositiveId($id);

        return new StoreProductResult($this->core->call('catalog.storeproduct.get', ['id' => $id]));
    }

    /**
     * Returns a list of product stock records by filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-list.html
     *
     * @param string[]             $select
     * @param array<string, mixed> $filter
     * @param array<string, string> $order
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.storeproduct.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-list.html',
        'Returns a list of product stock records by filter.'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): StoreProductsResult
    {
        return new StoreProductsResult($this->core->call('catalog.storeproduct.list', [
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
        ]));
    }

    /**
     * Returns the fields of product stock records.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.storeproduct.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-get-fields.html',
        'Returns the fields of product stock records.'
    )]
    public function getFields(): StoreProductFieldsResult
    {
        return new StoreProductFieldsResult($this->core->call('catalog.storeproduct.getFields'));
    }
}
```

### 6. `tests/Unit/Services/Catalog/StoreProduct/Service/StoreProductTest.php`

Mirrors `tests/Unit/Services/Catalog/ProductPropertySection/Service/ProductPropertySectionTest.php`:
mock `CoreInterface::call` and assert exact method name + params for `get`, `list` (with args
and with defaults), `getFields`.

### 7. `tests/Integration/Services/Catalog/StoreProduct/Service/StoreProductTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\StoreProduct\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\StoreProduct\Service\StoreProduct;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(StoreProduct::class)]
class StoreProductTest extends TestCase
{
    private StoreProduct $storeProductService;

    #[TestDox('test StoreProduct::list finds existing storeProduct records')]
    public function testList(): void
    {
        $items = $this->storeProductService->list()->getStoreProducts();
        $this->assertNotEmpty($items, 'integration portal must have at least one product with stock');
        $this->assertGreaterThan(0, $this->storeProductService->list()->getTotal());
    }

    #[TestDox('test StoreProduct::get returns the same record as list')]
    public function testGet(): void
    {
        $listItem = $this->storeProductService->list()->getStoreProducts()[0];
        $getItem = $this->storeProductService->get($listItem->id)->storeProduct();
        $this->assertEquals($listItem->id, $getItem->id);
        $this->assertEquals($listItem->productId, $getItem->productId);
        $this->assertEquals($listItem->storeId, $getItem->storeId);
    }

    #[TestDox('test StoreProduct::getFields')]
    public function testGetFields(): void
    {
        $fields = $this->storeProductService->getFields()->getFieldsDescription();
        $this->assertArrayHasKey('id', $fields);
        $this->assertArrayHasKey('productId', $fields);
        $this->assertArrayHasKey('storeId', $fields);
        $this->assertArrayHasKey('amount', $fields);
        $this->assertArrayHasKey('quantityReserved', $fields);
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->storeProductService = Factory::getServiceBuilder()->getCatalogScope()->storeProduct();
    }
}
```

Fixture strategy confirmed live: the integration portal already has 1 store (`id=1`,
`title=Warehouse`) and at least one `storeProduct` row (`productId=133`, `storeId=1`,
`amount=10`), so no `setUp()`/`tearDown()` fixture creation is needed — tests read existing
stock data directly via `list()`, consistent with the entity being read-only.

### 8. `tests/Integration/Services/Catalog/StoreProduct/Result/StoreProductItemResultTest.php`

Mandatory annotation/type-cast test, following the template in `SKILL.md`:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\StoreProduct\Result;

use Bitrix24\SDK\Services\Catalog\StoreProduct\Result\StoreProductItemResult;
use Bitrix24\SDK\Services\Catalog\StoreProduct\Service\StoreProduct;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(StoreProductItemResult::class)]
class StoreProductItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private StoreProduct $storeProductService;

    #[Test]
    #[TestDox('all fields in StoreProductItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->storeProductService->list()->getCoreResponse()
            ->getResponseData()->getResult()['storeProducts'][0];

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            StoreProductItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in StoreProductItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $item = $this->storeProductService->list()->getStoreProducts()[0];
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $item,
            StoreProductItemResult::class
        );
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->storeProductService = Factory::getServiceBuilder()->getCatalogScope()->storeProduct();
    }
}
```

Depends on the pre-existing `storeProduct` record on the integration portal (confirmed live:
portal returns one row for `productId=133`/`storeId=1`), so no setUp/tearDown fixture creation
is required for this file.

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Add, following the `productPropertySection()` pattern (no batch):

```php
public function storeProduct(): Catalog\StoreProduct\Service\StoreProduct
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\StoreProduct\Service\StoreProduct(
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}
```

### 2. `phpunit.xml.dist`

Add after the `integration_tests_scope_catalog_product_property_section` suite block
(before `integration_tests_catalog_document`), around line 574:

```xml
<testsuite name="integration_tests_catalog_store_product">
    <directory>./tests/Integration/Services/Catalog/StoreProduct/</directory>
</testsuite>
```

### 3. `Makefile`

Add after the `test-integration-scope-catalog-product-property-section` target, before the
`test-integration-catalog-document` target:

```makefile
.PHONY: test-integration-catalog-store-product
test-integration-catalog-store-product:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_store_product
```

Also add a doc-table row under the "Tests — integration (Catalog)" section in `docs/testing.md`
(existing table already lists `catalog-price`, `catalog-document`, etc.):

```markdown
| `make test-integration-catalog-store-product` | Product stock by warehouse |
```

### 4. `CHANGELOG.md`

Add under `## Unreleased` → `### Added`, as the first entry (top of the list):

```markdown
- Added service `Services\Catalog\StoreProduct` with support methods,
  see [catalog.storeproduct.* methods](https://apidocs.bitrix24.com/api-reference/catalog/store-product/index.html) ([#584](https://github.com/bitrix24/b24phpsdk/issues/584)):
    - `get` returns product stock information by record identifier
    - `list` returns a list of product stock records by filter
    - `getFields` returns the description of product stock fields
```

---

## Deptrac compliance

New code lives entirely in `Services\Catalog\StoreProduct\*` (a `Services` sub-namespace) and
only imports from `Core` (`AbstractAnnotatedItem`, `AbstractResult`, `CoreInterface` via
`AbstractService`, exceptions) and `Services\AbstractService`/`Services\Catalog` itself — same
dependency shape as every existing Catalog service. No new deptrac violation is introduced; no
`skip_violations` entry needed.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-store-product
```
