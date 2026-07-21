# Plan: Add support for catalog.productProperty (issue #538)

## Context

Bitrix24 REST API exposes `catalog.productProperty.*` methods to manage properties of catalog
products and SKUs (variations): https://apidocs.bitrix24.com/api-reference/catalog/product-property/index.html

The SDK already has a `Catalog` scope (`src/Services/Catalog/`) registered via
`CatalogServiceBuilder` (`src/Services/ServiceBuilder.php::getCatalogScope()`), with existing
`Catalog\Catalog` and `Catalog\Product` services. This issue adds a third entity,
`Catalog\ProductProperty`, following the same directory layout.

### Generator note

`make oa-schema-build` was run and `docs/open-api/openapi.json` was rebuilt from the live portal
(`ignatenko.bitrix24.com`). The rebuilt schema does **not** contain any `catalog.productProperty.*`
entries (only `catalog.item.*` methods are present under the `catalog` module). Therefore the
`b24-dev:result-item-generator`, `b24-dev:generate-select-builder`, and
`b24-dev:generate-item-builder` generators cannot be used for this issue — result item classes,
Batch, and Service classes are hand-written, following the most recent equivalent scope
(`src/Services/Documentgenerator/Role/`, same author, same add/update/get/list/delete/count shape).

### REST methods and confirmed real API behaviour

Verified directly against the portal webhook (not just docs), because docs are inconsistent with
actual behaviour in one case (see `update` note below).

1. **`catalog.productProperty.add`** — `POST fields: {...}` → `result.productProperty` (full object).
2. **`catalog.productProperty.update`** — `POST id, fields: {...}` → `result.productProperty` (full object).
   - **Docs say `fields.iblockId` is optional on update. Live testing proved this false** — omitting
     `iblockId` on update returns `{"error":"0","error_description":"Required fields: iblockId"}`.
     The SDK `update()` signature must NOT make `iblockId` implicit/optional; it is required in the
     `fields` payload same as `add()`.
   - `propertyType` and `userType` cannot be changed after creation (per docs); not enforced client-side.
3. **`catalog.productProperty.get`** — `POST id` → `result.productProperty` (full object).
4. **`catalog.productProperty.list`** — `POST select?, filter?, order?` → `result.productProperties[]`,
   plus top-level `total` and `next` (standard paginated list, offset-based via `start`, but the
   `list` method itself does not take `start` as a named top-level parameter separate from filter
   according to docs — pagination is handled the same way as other list methods, via `start`).
   Confirmed narrowed `select` works (`{"select":["id","name"]}` returns only those keys).
5. **`catalog.productProperty.delete`** — `POST id` → `result: true|false` (plain boolean, not an
   object with a `[0]` index like `DeletedItemResult` expects for some legacy methods). Verified:
   `{"result":true,...}`. This matches `Bitrix24\SDK\Core\Result\DeletedItemResult::isSuccess()`
   only if we override it (that base class does `getResult()[0]`, which fails against a scalar
   `true`). Must override `isSuccess()` like `DeletedRoleResult` does:
   `(bool)$this->getCoreResponse()->getResponseData()->getResult()`.
6. **`catalog.productProperty.getFields`** — `POST {}` → `result.productProperty` (map of field-name
   to `rest_field_description`: `isImmutable`, `isReadOnly`, `isRequired`, `type`). Use `FieldsResult`
   like `Currency::fields()`, but need a thin subclass because the payload is nested one level under
   `productProperty` (not the root `result`), same situation as `Product::fieldsByFilter()`. A plain
   `FieldsResult` (`getFieldsDescription()` returns `getResult()` directly) would return
   `{"productProperty": {...}}` instead of the field map — so add a
   `ProductPropertyFieldsResult extends FieldsResult` overriding `getFieldsDescription()` to unwrap
   the `productProperty` key.

### Real field set (confirmed via live add/get/list calls)

```
active (char Y/N), code (string|null), colCount (int), defaultValue (text|null),
fileType (string|null), filtrable (char Y/N), hint (string|null), iblockId (int),
id (int), isRequired (char Y/N), linkIblockId (int|null), listType (char L/C),
multiple (char Y/N), multipleCnt (int|null), name (string), propertyType (string),
rowCount (int), searchable (char Y/N), sort (int|null), timestampX (datetime),
userType (string|null), userTypeSettings (object|null — scalar/nested-scalar map,
  free-form depending on userType, e.g. {"tableName":"...","group":"N","multiple":"N","size":1,"width":0}),
withDescription (char Y/N|null), xmlId (string|null)
```

`userTypeSettings` has no fixed shape (depends on `userType`), so annotate as `?array`.

### iblockId source for tests

`catalog.catalog.list` (existing `Catalog::list()`) is used in the existing `ProductTest` to obtain
a valid `iblockId` for creating a test entity — same approach will be used here, calling
`Factory::getServiceBuilder()->getCatalogScope()->catalog()->list([], [], [], 1)->getCatalogs()[0]->iblockId`.

---

## Files to Create

### 1. `src/Services/Catalog/ProductProperty/Result/ProductPropertyItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read bool          $active
 * @property-read string|null   $code
 * @property-read int           $colCount
 * @property-read string|null   $defaultValue
 * @property-read string|null   $fileType
 * @property-read bool          $filtrable
 * @property-read string|null   $hint
 * @property-read int           $iblockId
 * @property-read int           $id
 * @property-read bool          $isRequired
 * @property-read int|null      $linkIblockId
 * @property-read string        $listType
 * @property-read bool          $multiple
 * @property-read int|null      $multipleCnt
 * @property-read string        $name
 * @property-read string        $propertyType
 * @property-read int           $rowCount
 * @property-read bool          $searchable
 * @property-read int|null      $sort
 * @property-read CarbonImmutable $timestampX
 * @property-read string|null   $userType
 * @property-read array|null    $userTypeSettings
 * @property-read bool|null     $withDescription
 * @property-read string|null   $xmlId
 */
class ProductPropertyItemResult extends AbstractAnnotatedItem
{
}
```

### 2. `src/Services/Catalog/ProductProperty/Result/ProductPropertyResult.php`

Single-item wrapper for `add`/`update`/`get`, unwrapping `result.productProperty`:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ProductPropertyResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function productProperty(): ProductPropertyItemResult
    {
        return new ProductPropertyItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()['productProperty']
        );
    }
}
```

### 3. `src/Services/Catalog/ProductProperty/Result/ProductPropertiesResult.php`

List wrapper for `list`, unwrapping `result.productProperties[]`:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ProductPropertiesResult extends AbstractResult
{
    /**
     * @return ProductPropertyItemResult[]
     * @throws BaseException
     */
    public function getProductProperties(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['productProperties'] as $item) {
            $items[] = new ProductPropertyItemResult($item);
        }

        return $items;
    }
}
```

### 4. `src/Services/Catalog/ProductProperty/Result/ProductPropertyFieldsResult.php`

Thin subclass of `Core\Result\FieldsResult` unwrapping the `productProperty` key
(same rationale as `Product::fieldsByFilter()`'s `FieldsResult` usage, but here the raw
`getResult()` is `{"productProperty": {field: description, ...}}`, so it must be unwrapped):

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\FieldsResult;

class ProductPropertyFieldsResult extends FieldsResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function getFieldsDescription(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['productProperty'];
    }
}
```

### 5. `src/Services/Catalog/ProductProperty/Result/DeletedProductPropertyResult.php`

`catalog.productProperty.delete` returns a plain boolean `result`, not the `[0]`-indexed shape the
base `DeletedItemResult` expects — override like `DeletedRoleResult`:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;

class DeletedProductPropertyResult extends DeletedItemResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function isSuccess(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult();
    }
}
```

### 6. `src/Services/Catalog/ProductProperty/Service/ProductProperty.php`

Main service class, methods `add`, `update`, `get`, `list`, `delete`, `getFields`:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\DeletedProductPropertyResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertiesResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertyFieldsResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertyResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class ProductProperty extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a product or variation property to the commercial catalog
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productProperty.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-add.html',
        'Adds a product or variation property to the commercial catalog'
    )]
    public function add(array $fields): ProductPropertyResult
    {
        return new ProductPropertyResult(
            $this->core->call('catalog.productProperty.add', ['fields' => $fields])
        );
    }

    /**
     * Updates fields of a product or variation property in the commercial catalog
     *
     * NOTE: despite the official docs marking `iblockId` as optional in `fields`, the live API
     * requires it — omitting it fails with "Required fields: iblockId". Callers must always pass
     * `iblockId` in $fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productProperty.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-update.html',
        'Updates fields of a product or variation property in the commercial catalog'
    )]
    public function update(int $id, array $fields): ProductPropertyResult
    {
        return new ProductPropertyResult(
            $this->core->call('catalog.productProperty.update', ['id' => $id, 'fields' => $fields])
        );
    }

    /**
     * Returns the values of the product or variation property fields by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productProperty.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-get.html',
        'Returns the values of the product or variation property fields by its identifier'
    )]
    public function get(int $id): ProductPropertyResult
    {
        return new ProductPropertyResult($this->core->call('catalog.productProperty.get', ['id' => $id]));
    }

    /**
     * Returns a list of product and variation properties by filter
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productProperty.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-list.html',
        'Returns a list of product and variation properties by filter'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): ProductPropertiesResult
    {
        return new ProductPropertiesResult(
            $this->core->call(
                'catalog.productProperty.list',
                [
                    'select' => $select,
                    'filter' => $filter,
                    'order' => $order,
                ]
            )
        );
    }

    /**
     * Removes a product or variation property by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productProperty.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-delete.html',
        'Removes a product or variation property by its identifier'
    )]
    public function delete(int $id): DeletedProductPropertyResult
    {
        return new DeletedProductPropertyResult(
            $this->core->call('catalog.productProperty.delete', ['id' => $id])
        );
    }

    /**
     * Returns the description of product or variation property fields
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productProperty.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-get-fields.html',
        'Returns the description of product or variation property fields'
    )]
    public function getFields(): ProductPropertyFieldsResult
    {
        return new ProductPropertyFieldsResult($this->core->call('catalog.productProperty.getFields'));
    }
}
```

### 7. `src/Services/Catalog/ProductProperty/Result/AddedProductPropertyBatchResult.php`,
    `UpdatedProductPropertyBatchResult.php`, `DeletedProductPropertyBatchResult.php`

**Confirmed via source inspection**: `Core\Batch::addEntityItems()`/`updateEntityItems()`/
`deleteEntityItems()` yield raw `ResponseData` objects per item (not unwrapped arrays) — see
`src/Core/Batch.php` `getTraversable(true)`. The exact same situation exists for
`Documentgenerator\Role\Service\Batch`, which wraps each yielded `ResponseData` in
`AddedRoleBatchResult`/`UpdatedRoleBatchResult` (subclasses of Core's `AddedItemBatchResult`/
`UpdatedItemBatchResult`, overriding `getId()`/`isSuccess()` to unwrap the `role` key instead of
the base classes' `getResult()[0]` assumption). Follow the identical pattern for `productProperty`:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Result;

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;

class AddedProductPropertyBatchResult extends AddedItemBatchResult
{
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['productProperty']['id'];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Result;

use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;

class UpdatedProductPropertyBatchResult extends UpdatedItemBatchResult
{
    #[\Override]
    public function isSuccess(): bool
    {
        return (bool)$this->getResponseData()->getResult();
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Result;

use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;

class DeletedProductPropertyBatchResult extends DeletedItemBatchResult
{
    #[\Override]
    public function isSuccess(): bool
    {
        return (bool)$this->getResponseData()->getResult();
    }
}
```

### 8. `src/Services/Catalog/ProductProperty/Service/Batch.php`

Batch wrapper, mirrors `Documentgenerator\Role\Service\Batch` exactly:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\AddedProductPropertyBatchResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\DeletedProductPropertyBatchResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertyItemResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\UpdatedProductPropertyBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
readonly class Batch
{
    public function __construct(
        protected BatchOperationsInterface $batch,
        protected LoggerInterface $log
    ) {
    }

    /**
     * Batch list method for product properties
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-list.html
     *
     * @return Generator<int, ProductPropertyItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productProperty.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-list.html',
        'Batch list method for product properties'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], ?int $limit = null): Generator
    {
        $itemsGenerator = $this->batch->getTraversableListWithCount(
            'catalog.productProperty.list',
            $order,
            $filter,
            $select,
            $limit
        );
        foreach ($itemsGenerator as $key => $value) {
            yield $key => new ProductPropertyItemResult($value);
        }
    }

    /**
     * Batch adding product properties
     *
     * @param array<int, array> $productProperties
     *
     * @return Generator<int, AddedProductPropertyBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productProperty.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-add.html',
        'Batch adding product properties'
    )]
    public function add(array $productProperties): Generator
    {
        $items = [];
        foreach ($productProperties as $item) {
            $items[] = ['fields' => $item];
        }

        foreach ($this->batch->addEntityItems('catalog.productProperty.add', $items) as $key => $item) {
            yield $key => new AddedProductPropertyBatchResult($item);
        }
    }

    /**
     * Batch update product properties
     *
     * Update elements in array with structure: id => ['fields' => []]
     *
     * @param array<int, array> $entityItems
     *
     * @return Generator<int, UpdatedProductPropertyBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productProperty.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-update.html',
        'Batch update product properties'
    )]
    public function update(array $entityItems): Generator
    {
        foreach ($this->batch->updateEntityItems('catalog.productProperty.update', $entityItems) as $key => $item) {
            yield $key => new UpdatedProductPropertyBatchResult($item);
        }
    }

    /**
     * Batch delete product properties
     *
     * @param int[] $productPropertyId
     *
     * @return Generator<int, DeletedProductPropertyBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productProperty.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-delete.html',
        'Batch delete product properties'
    )]
    public function delete(array $productPropertyId): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.productProperty.delete', $productPropertyId) as $key => $item) {
            yield $key => new DeletedProductPropertyBatchResult($item);
        }
    }
}
```

Note: `update()`'s per-item `fields` payload must include `iblockId` (same live-API requirement as
the single `update()` method) — document this in the method docblock.

**Post-implementation finding**: `catalog.productProperty.delete` requires a lowercase `id`
parameter in batch commands, but `Core\Batch::deleteEntityItems()` sends uppercase `ID` — confirmed
by a live batch-delete integration test failure (`could not find value for parameter {id}`). Fixed
by adding `src/Services/Catalog/ProductProperty/Batch.php extends \Bitrix24\SDK\Core\Batch`,
overriding `deleteEntityItems()` to send `['id' => $itemId]`, following the exact
`Services\Task\Batch`/`Services\CRM\Currency\Batch` pattern. Wired into
`CatalogServiceBuilder::productProperty()` by constructing `new Catalog\ProductProperty\Batch($this->core, $this->log)`
instead of reusing the shared `$this->batch`, matching `CRMServiceBuilder::currency()`.

No separate `ProductPropertyServiceBuilder.php` file is needed — `ProductProperty` is registered
directly inside the existing `src/Services/Catalog/CatalogServiceBuilder.php` (see Files to Modify
below), matching how `product()` and `catalog()` are already registered there.

### 9. `tests/Unit/Services/Catalog/ProductProperty/Service/ProductPropertyTest.php`

Unit test using `NullCore`/`NullBatch`, verifying each method builds the correct request and
result wrapper without HTTP calls:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\ProductProperty\Service;

use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertyResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Service\Batch;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Service\ProductProperty;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductProperty::class)]
class ProductPropertyTest extends TestCase
{
    private ProductProperty $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new ProductProperty(
            new Batch(new NullBatch(), new NullLogger()),
            new NullCore(),
            new NullLogger()
        );
    }

    #[Test]
    public function testGetReturnsProductPropertyResult(): void
    {
        $this->assertInstanceOf(ProductPropertyResult::class, $this->service->get(1));
    }

    // + add/update/list/delete/getFields smoke coverage against NullCore
}
```

### 10. `tests/Integration/Services/Catalog/ProductProperty/Service/ProductPropertyTest.php`

CRUD integration test, following `Documentgenerator\Role\Service\RoleTest` shape: helper
`createProductProperty()` (uses `catalog()->list()` for `iblockId`, adds with a Faker-suffixed
`name`/`code`), `safeDelete()`, and `testAdd`/`testGet`/`testList`/`testUpdate`/`testDelete`/
`testGetFields`.

### 11. `tests/Integration/Services/Catalog/ProductProperty/Service/BatchTest.php`

Batch CRUD test, following `Documentgenerator\Role\Service\BatchTest` shape:
`testBatchList`/`testBatchAdd`/`testBatchUpdate`/`testBatchDelete`.

### 12. `tests/Integration/Services/Catalog/ProductProperty/Result/ProductPropertyItemResultAnnotationsTest.php`

Mandatory annotation/type-cast test per `docs/testing.md` and the skill's dedicated-file rule,
following `RoleItemResultAnnotationsTest` shape: `getFirstProductPropertyRawItem()` helper (add →
`get()` raw result unwrap `['productProperty']` → delete), then
`testAllSystemFieldsAnnotated()` / `testAllSystemFieldsHasValidTypeAnnotation()` using
`assertBitrix24AllResultItemFieldsAnnotated()` / `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations()`.

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Add a `productProperty()` accessor, following the existing `product()` pattern exactly:

```php
    public function productProperty(): Catalog\ProductProperty\Service\ProductProperty
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\ProductProperty\Service\ProductProperty(
                new Catalog\ProductProperty\Service\Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
```

Add `use Bitrix24\SDK\Services\Catalog\ProductProperty\Service\ProductProperty;`-style import is
not required since the file already does `use Bitrix24\SDK\Services\Catalog;` and references
`Catalog\Product\Service\Product` inline — follow the same inline-namespace style for consistency.

### 2. `phpunit.xml.dist`

**Confirmed by grep**: `phpunit.xml.dist` currently has **zero** `Catalog` testsuite entries at
all — the existing `Catalog\Catalog` and `Catalog\Product` integration tests
(`tests/Integration/Services/Catalog/Catalog/Service/CatalogTest.php`,
`tests/Integration/Services/Catalog/Product/Service/ProductTest.php`) are not wired into any
testsuite. This is pre-existing SDK debt, out of scope for this issue — do not fix it, only add
new `ProductProperty` suites. Append a new block (no existing Catalog block to slot into):

```xml
        <testsuite name="integration_tests_catalog_product_property">
            <directory>./tests/Integration/Services/Catalog/ProductProperty/</directory>
        </testsuite>
        <testsuite name="integration_tests_catalog_product_property_service">
            <directory>./tests/Integration/Services/Catalog/ProductProperty/Service/</directory>
        </testsuite>
        <testsuite name="integration_tests_catalog_product_property_annotations">
            <file>./tests/Integration/Services/Catalog/ProductProperty/Result/ProductPropertyItemResultAnnotationsTest.php</file>
        </testsuite>
```

### 3. `Makefile`

**Confirmed**: no existing `catalog`-named make targets exist. Append matching targets following
the exact `integration_tests_documentgenerator_role*` block style (confirmed present at
Makefile:731-741):

```makefile
.PHONY: integration-tests-catalog-product-property
integration-tests-catalog-product-property:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_product_property

.PHONY: integration-tests-catalog-product-property-service
integration-tests-catalog-product-property-service:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_product_property_service

.PHONY: integration-tests-catalog-product-property-annotations
integration-tests-catalog-product-property-annotations:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_product_property_annotations
```

No corresponding `make help` echo line exists for `documentgenerator_role` either (confirmed by
inspection) — do not add one for `catalog_product_property`, keeping the same (lack of) convention.

### 4. `.php-cs-fixer.php`

**Confirmed**: this file enumerates `->in(__DIR__ . '/src/Services/...')` calls explicitly per
scope and has **no** `Catalog` entry at all currently. Add one line:

```php
    ->in(__DIR__ . '/src/Services/Catalog/')
```

Insert it alongside the other `->in(...)` calls (e.g. near `->in(__DIR__ . '/src/Services/Documentgenerator/')`).

### `phpstan.neon.dist` and `rector.php` — no edit needed

**Confirmed**: `phpstan.neon.dist` already lists `tests/Integration/Services/Catalog` under
`paths`, and `src/` is covered unconditionally at the top of the same list — so
`src/Services/Catalog/ProductProperty/` is already analysed. `rector.php` already lists both
`src/Services/Catalog` and `tests/Integration/Services/Catalog` in `withPaths()`. No changes
required to either file for this issue.

### 5. `CHANGELOG.md`

**Confirmed**: the file's top section is `## 3.4.0` (already released, no `Unreleased` header).
Add a new `## Unreleased` section above it, following the existing `### Added` bullet-list style
seen under `## 3.4.0`:

```markdown
### Added

- Added service `Services\Catalog\ProductProperty` with support methods,
  see [catalog.productProperty.* methods](https://apidocs.bitrix24.com/api-reference/catalog/product-property/index.html) ([#538](https://github.com/bitrix24/b24phpsdk/issues/538)):
    - `add` creates a new product or variation property, with batch calls support
    - `update` updates an existing product or variation property, with batch calls support
    - `get` gets information about a product or variation property by its identifier
    - `list` gets the list of product and variation properties by filter, with batch calls support
    - `delete` deletes a product or variation property, with batch calls support
    - `getFields` returns the description of product or variation property fields
```

---

## Deptrac compliance

`Services\Catalog\ProductProperty\*` depends only on `Core` (via `AbstractAnnotatedItem`,
`AbstractResult`, `AbstractItem`, `DeletedItemResult`, `FieldsResult`, `BatchOperationsInterface`,
`CoreInterface`) and `Services\AbstractService`/`Attributes\*` (same layer). This matches the
`Services → Core` allowed dependency rule. No new `skip_violations` entries are needed.

---

## Known pre-existing environment issue (out of scope)

`vendor/rector/rector/vendor/phpstan/phpdoc-parser/src/Lexer/Lexer.php` (a bundled, non-scoped copy
inside rector's own vendor tree) sometimes gets autoloaded instead of the top-level
`phpstan/phpdoc-parser` package that `typhoon/reflection` (used by `AbstractAnnotatedItem` for
`@property-read` type casting) requires. When this happens, the **first** call in a PHPUnit process
to `TyphoonReflector::build()->reflectClass()` for any `AbstractAnnotatedItem` subclass throws
`ArgumentCountError: Too few arguments to function PHPStan\PhpDocParser\Lexer\Lexer::__construct()`.

**Confirmed pre-existing and unrelated to this issue**: reproduced against
`tests/Unit/Core/Result/AbstractAnnotatedItemTest.php` (the SDK's own dedicated unit test for the
mechanism, untouched by this change) — same error, same stack trace, same non-deterministic
"only the first test in the process fails" pattern. `composer dump-autoload -o` does not fix it.

This affects `ProductPropertyItemResultAnnotationsTest` non-deterministically depending on process
test order (fails only if it is the first `AbstractAnnotatedItem`-touching test PHPUnit executes in
that process; passes/no-ops if another such test already ran first and populated the process-wide
`self::$annotatedTypesCache`... actually the ArgumentCountError itself, not cache absence, is what's
intermittent — whichever test class the reflector first parses in-process is the one that surfaces
the crash). This is a systemic composer dependency-conflict bug (likely needs PHP-Scoper prefixing
of rector's bundled `phpstan/phpdoc-parser`, or a composer.json `conflict`/replace entry) — fixing it
is out of scope for #538. Recommend filing a separate tracking issue.

---

## Verification

```bash
docker compose run --rm php-cli vendor/bin/php-cs-fixer check --verbose --diff
docker compose run --rm php-cli vendor/bin/rector process --dry-run
docker compose run --rm php-cli vendor/bin/phpstan analyse
docker compose run --rm php-cli vendor/bin/deptrac analyse
docker compose run --rm php-cli vendor/bin/phpunit --testsuite unit_tests
docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_catalog_product_property
docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_catalog_product_property_annotations
```

(`make` is unavailable in this shell environment — use the equivalent `docker compose run`
commands shown above, which are exactly what each `make lint-*`/`make test-*` target wraps.)
