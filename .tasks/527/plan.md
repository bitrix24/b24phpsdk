# Plan: Add support for catalog.product.* (issue #527)

## Context

Issue: https://github.com/bitrix24/b24phpsdk/issues/527
Author: Dmitriy Ignatenko <algonexys@gmail.com>
Base branch: `v3-dev` (API version confirmed as v3 by the user). Branch `feature/527-add-catalog.product-v3`
already exists, is checked out, and is up to date with `v3-dev` (no divergence).

The issue asks for full coverage of the `catalog.product.*` REST scope:

- `catalog.product.update`, `catalog.product.download` (the existing `Product` service already
  covers `add`, `get`, `delete`, `list`, `getFieldsByFilter` — see below)
- `catalog.product.service.*`: `add`, `update`, `get`, `list`, `download`, `delete`, `getFieldsByFilter`
- `catalog.product.sku.*`: `add`, `update`, `get`, `list`, `download`, `delete`, `getFieldsByFilter`
- `catalog.product.offer.*`: `add`, `update`, `get`, `list`, `download`, `delete`, `getFieldsByFilter`

### Existing code already in the scope

`src/Services/Catalog/Product/` already exists with:
- `Service/Product.php` — implements `get`, `add`, `delete`, `list`, `fieldsByFilter` (missing `update`, `download`)
- `Service/Batch.php` — empty stub (no batch methods wired up yet)
- `Result/ProductItemResult.php` — extends `Bitrix24\SDK\Services\Catalog\Common\Result\AbstractCatalogItem`
  (a **legacy manual `__get()` cast pattern**, not the modern `AbstractAnnotatedItem`)
- `Result/ProductResult.php`, `Result/ProductsResult.php`

`src/Services/Catalog/Common/`:
- `ProductType` enum (`simple=1, bundle=2, SKU=3, productOffer=4, genericOffer=5`)
- `Common/Result/AbstractCatalogItem.php` — shared manual-cast base class for all catalog.product.* result items

`src/Services/Catalog/CatalogServiceBuilder.php` currently exposes `product()` and `catalog()`.

There is **no existing test suite registration** for Catalog at all in `phpunit.xml.dist` and **no
Makefile target** — despite `tests/Integration/Services/Catalog/Product/Service/ProductTest.php` and
`tests/Integration/Services/Catalog/Catalog/Service/CatalogTest.php` already existing. This plan adds
the missing suites for the code delivered under this issue only (does not retroactively fix pre-existing
gaps outside the issue scope, but the new suites will also expose the existing `ProductTest`/`CatalogTest`
files since they share directories).

### Why `AbstractAnnotatedItem` + generators are NOT used here

Per the skill's mandatory rule, `*ItemResult` classes should be generated via
`php bin/console b24-dev:result-item-generator <method.name> --stage=all`, which reads
`docs/open-api/openapi.json`. After running `make oa-schema-build` (via `docker compose run --rm php-cli php bin/console b24-dev:build-schema`)
against the configured webhook, the schema contains **zero** `catalog.*` entries (`grep -c "catalog.product" docs/open-api/openapi.json` → 0).
This portal's OpenAPI introspection endpoint does not expose the legacy-generation `catalog.product.*`
methods at all (they predate the OpenAPI schema effort and are documented only via the classic docs site).

Because there is no OpenAPI entity to generate from, and because the sibling class `ProductItemResult`
in this exact scope already establishes a manual-cast pattern via `AbstractCatalogItem`, all new result
item classes (`ProductServiceItemResult`, `ProductSkuItemResult`, `ProductOfferItemResult`) will follow
the **same existing `AbstractCatalogItem` pattern** for internal consistency within the `Catalog` scope,
instead of `AbstractAnnotatedItem`. This is an explicit, documented deviation from the generator-first rule,
as permitted by the skill ("If the generator cannot be used for the current case, write the reason
explicitly in the plan before proceeding with manual edits").

### Key API research findings (from `bitrix-method-details`)

- `catalog.product.update`: `id` + `fields`, returns `result.element` (matches the existing "fix for
  catalog.product.add" comment in `ProductResult::product()`, which already special-cases `element`).
- `catalog.product.download`: `fields: {fileId, productId, fieldName}`, returns a raw file (HTTP 200,
  binary body) — not a JSON envelope. Implemented as a raw `call()` passthrough returning `ResponseData`
  wrapped minimally; no dedicated Result class needed (mirrors how raw-file endpoints are handled
  elsewhere — we return `Bitrix24\SDK\Core\Response\Response` directly since there is no `result` key
  to wrap for a binary download).
- `catalog.product.service.*`: response envelope key is `service` (single) / `services` (list, with `total`).
- `catalog.product.sku.*`: response envelope key is `sku` (single) / **`units`** (list, with `total`) —
  note the list key is `units`, NOT `skus`.
- `catalog.product.offer.*`: response envelope key is `offer` (single) / `offers` (list, with `total`).
  `offer` entities carry a `parentId` field shaped as `{value, valueId}` (a productproperty-typed
  reference to the SKU), and property fields are dynamic (`propertyN`).
- All `*.list` methods require `id` and `iblockId` in `select`, and `iblockId` in `filter`
  (enforced by Bitrix, not by the SDK — SDK just passes through select/filter/order).
- All `*.getFieldsByFilter` methods take `filter: {iblockId}` and return `FieldsResult`-compatible
  shape (same pattern as existing `Product::fieldsByFilter`), but service/sku/offer do NOT take a
  `productType` filter param the way `Product::fieldsByFilter` does — only `iblockId`.
- `*.delete` returns a plain boolean (`DeletedItemResult`-compatible, same shape as `Product::delete`).

### Layer / architecture

All new code lives under `src/Services/Catalog/Product/`, adding `ProductService`, `Sku`, `Offer` as
sibling sub-entity folders directly under `Product/` (not nested inside `Product/Service/`), matching
how sibling scopes like `CRM/Documentgenerator/Document` place `Service` and `Result` directly under
the entity folder rather than nesting new entities inside another entity's `Service/` directory.

To keep names unambiguous and avoid clashing short class names (`Service`, `Sku`, `Offer` are
overloaded terms elsewhere in the SDK), the `catalog.product.service.*` sub-scope uses the class name
`ProductService` (not bare `Service`) — but `Sku` and `Offer` are precise enough on their own and are
used as-is for both the directory/namespace segment and the class name.

Final namespace/directory layout:

```
src/Services/Catalog/Product/
├── Service/
│   ├── Product.php
│   └── Batch.php
├── Result/
│   ├── ProductItemResult.php
│   ├── ProductResult.php
│   └── ProductsResult.php
├── ProductService/
│   ├── Service/ProductService.php
│   └── Result/ProductServiceItemResult.php, ProductServiceResult.php, ProductServicesResult.php
├── Sku/
│   ├── Service/Sku.php
│   └── Result/SkuItemResult.php, SkuResult.php, SkusResult.php
└── Offer/
    ├── Service/Offer.php
    └── Result/OfferItemResult.php, OfferResult.php, OffersResult.php
```

Namespaces:
- `Bitrix24\SDK\Services\Catalog\Product\ProductService\Service\ProductService`
- `Bitrix24\SDK\Services\Catalog\Product\ProductService\Result\{ProductServiceItemResult,ProductServiceResult,ProductServicesResult}`
- `Bitrix24\SDK\Services\Catalog\Product\Sku\Service\Sku`
- `Bitrix24\SDK\Services\Catalog\Product\Sku\Result\{SkuItemResult,SkuResult,SkusResult}`
- `Bitrix24\SDK\Services\Catalog\Product\Offer\Service\Offer`
- `Bitrix24\SDK\Services\Catalog\Product\Offer\Result\{OfferItemResult,OfferResult,OffersResult}`

Deptrac: all new classes live in `Services` layer, importing only `Core` (`AbstractService`,
`AbstractResult`, `AbstractCatalogItem` which is itself `Core`-based via `AbstractItem`, exceptions,
credentials/Scope) and sibling `Services\Catalog\Common` classes. No cross-scope imports. This matches
the existing `Services → Core, Application, Legacy` rule — no new violations expected.

---

## Files to Create

### 1. `src/Services/Catalog/Product/ProductService/Result/ProductServiceItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Product\ProductService\Result;

use Bitrix24\SDK\Services\Catalog\Common\ProductType;
use Bitrix24\SDK\Services\Catalog\Common\Result\AbstractCatalogItem;
use Carbon\CarbonImmutable;

/**
 * @property-read bool $active
 * @property-read bool $available
 * @property-read bool $bundle
 * @property-read string $code
 * @property-read int $createdBy
 * @property-read CarbonImmutable|null $dateActiveFrom
 * @property-read CarbonImmutable|null $dateActiveTo
 * @property-read CarbonImmutable $dateCreate
 * @property-read array|null $detailPicture
 * @property-read string $detailText
 * @property-read string $detailTextType
 * @property-read int $id
 * @property-read int $iblockId
 * @property-read ?int $iblockSectionId
 * @property-read int $modifiedBy
 * @property-read string $name
 * @property-read array|null $previewPicture
 * @property-read string $previewText
 * @property-read string $previewTextType
 * @property-read int $sort
 * @property-read CarbonImmutable $timestampX
 * @property-read ProductType $type
 * @property-read ?int $vatId
 * @property-read bool $vatIncluded
 * @property-read string $xmlId
 */
class ProductServiceItemResult extends AbstractCatalogItem
{
}
```

(`sort` and `vatId`/`vatIncluded` casts must be added to `AbstractCatalogItem::__get()` — see
Files to Modify below — since the current implementation does not cast them.)

### 2. `src/Services/Catalog/Product/ProductService/Result/ProductServiceResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Product\ProductService\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class ProductServiceResult extends AbstractResult
{
    public function productService(): ProductServiceItemResult
    {
        return new ProductServiceItemResult($this->getCoreResponse()->getResponseData()->getResult()['service']);
    }
}
```

### 3. `src/Services/Catalog/Product/ProductService/Result/ProductServicesResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Product\ProductService\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ProductServicesResult extends AbstractResult
{
    /**
     * @return ProductServiceItemResult[]
     * @throws BaseException
     */
    public function getProductServices(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['services'] as $service) {
            $res[] = new ProductServiceItemResult($service);
        }

        return $res;
    }
}
```

### 4. `src/Services/Catalog/Product/ProductService/Service/ProductService.php`

Full method set with `#[ApiEndpointMetadata]` per method, doc-links to
`https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-*.html`:

```php
<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Product\ProductService\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Result\ProductServiceResult;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Result\ProductServicesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class ProductService extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    #[ApiEndpointMetadata(
        'catalog.product.service.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-add.html',
        'Adds a service to the commercial catalog.'
    )]
    public function add(array $fields): ProductServiceResult
    {
        return new ProductServiceResult($this->core->call('catalog.product.service.add', ['fields' => $fields]));
    }

    #[ApiEndpointMetadata(
        'catalog.product.service.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-update.html',
        'Updates a service in the commercial catalog by its identifier.'
    )]
    public function update(int $serviceId, array $fields): ProductServiceResult
    {
        return new ProductServiceResult($this->core->call('catalog.product.service.update', [
            'id' => $serviceId,
            'fields' => $fields,
        ]));
    }

    #[ApiEndpointMetadata(
        'catalog.product.service.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-get.html',
        'Gets field values of a commercial catalog service by ID.'
    )]
    public function get(int $serviceId): ProductServiceResult
    {
        return new ProductServiceResult($this->core->call('catalog.product.service.get', ['id' => $serviceId]));
    }

    #[ApiEndpointMetadata(
        'catalog.product.service.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-list.html',
        'Gets a list of commercial catalog services by filter.'
    )]
    public function list(array $select, array $filter, array $order = []): ProductServicesResult
    {
        return new ProductServicesResult($this->core->call('catalog.product.service.list', [
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
        ]));
    }

    #[ApiEndpointMetadata(
        'catalog.product.service.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-delete.html',
        'Deletes a commercial catalog service by ID.'
    )]
    public function delete(int $serviceId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.product.service.delete', ['id' => $serviceId]));
    }

    #[ApiEndpointMetadata(
        'catalog.product.service.getFieldsByFilter',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-get-fields-by-filter.html',
        'Returns commercial catalog service fields by filter.'
    )]
    public function fieldsByFilter(int $iblockId): FieldsResult
    {
        return new FieldsResult($this->core->call('catalog.product.service.getFieldsByFilter', [
            'filter' => ['iblockId' => $iblockId],
        ]));
    }

    #[ApiEndpointMetadata(
        'catalog.product.service.download',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-download.html',
        'Downloads a commercial catalog service file by the given parameters.'
    )]
    public function download(int $fileId, int $productId, string $fieldName): Response
    {
        return $this->core->call('catalog.product.service.download', [
            'fields' => [
                'fileId' => $fileId,
                'productId' => $productId,
                'fieldName' => $fieldName,
            ],
        ]);
    }
}
```

(`get`/`add`/`update`/`delete`/`list`/`fieldsByFilter` all `@throws BaseException, TransportException`
in real docblocks — omitted from this skeleton for brevity, must be written in full in the actual file.)

### 5–7. `src/Services/Catalog/Product/Sku/Result/{SkuItemResult,SkuResult,SkusResult}.php`

Same shape as ProductService equivalents, but:
- `SkuResult::sku()` reads `result['sku']`
- `SkusResult::getSkus()` reads `result['units']` (per confirmed API research — the list key is
  `units`, not `skus`)
- `SkuItemResult` additionally annotates SKU-only fields: `canBuyZero` (bool|null), `height`,
  `length`, `width`, `weight` (float|null), `measure` (int|null), `purchasingCurrency` (?Currency),
  `purchasingPrice` (?Money), `quantity` (?float), `subscribe` (string→ mapped as raw string Y/N/D,
  NOT boolean since it has 3 states)

### 8. `src/Services/Catalog/Product/Sku/Service/Sku.php`

Same method set as `ProductService`, but:
- `list()` result class is `SkusResult`
- REST method names are `catalog.product.sku.*`
- doc links `.../sku/catalog-product-sku-*.html`
- `fieldsByFilter(int $iblockId)` — same shape

### 9–11. `src/Services/Catalog/Product/Offer/Result/{OfferItemResult,OfferResult,OffersResult}.php`

- `OfferResult::offer()` reads `result['offer']`
- `OffersResult::getOffers()` reads `result['offers']`
- `OfferItemResult` additionally annotates offer-only fields: `barcodeMulti` (bool|null), `parentId`
  (array|null — kept as raw array since it's a `{value, valueId}` structure, not a scalar — a typed
  DTO is out of scope for this issue), `quantityReserved` (?float), `quantityTrace` (string
  Y/N/D — raw string, not boolean), `recurSchemeLength` (?int), `recurSchemeType` (?string),
  `trialPriceId` (?int), `withoutOrder` (string Y/N — mapped bool)

### 12. `src/Services/Catalog/Product/Offer/Service/Offer.php`

Same method set, REST method names `catalog.product.offer.*`, doc links
`.../offer/catalog-product-offer-*.html`. `add()` additionally documents the optional `parentId`
field usable inside `$fields` (kept as a raw array key in the fields payload — no dedicated typed
parameter, consistent with how `propertyN` dynamic fields are passed).

### 13. `tests/Unit/Services/Catalog/Product/ProductService/Service/ProductServiceTest.php`

Unit test using `NullCore` — asserts each method calls the correct REST method name and shape,
following the `AbstractServiceTest`-less simple pattern used elsewhere (mirrors
`tests/Unit/Services/Timeman/Record/Service/RecordServiceTest.php`-style setup):

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\Product\ProductService\Service;

use Bitrix24\SDK\Services\Catalog\Product\ProductService\Service\ProductService;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(ProductService::class)]
class ProductServiceTest extends TestCase
{
    private ProductService $productService;

    #[\Override]
    protected function setUp(): void
    {
        $this->productService = new ProductService(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetReturnsResult(): void
    {
        $this->assertInstanceOf(
            \Bitrix24\SDK\Services\Catalog\Product\ProductService\Result\ProductServiceResult::class,
            $this->productService->get(1)
        );
    }

    // ... add/update/delete/list/fieldsByFilter/download analogous smoke tests
}
```

Analogous unit test files for `Sku` and `Offer` services:
`tests/Unit/Services/Catalog/Product/Sku/Service/SkuTest.php`,
`tests/Unit/Services/Catalog/Product/Offer/Service/OfferTest.php`.

### 14. `tests/Integration/Services/Catalog/Product/ProductService/Service/ProductServiceTest.php`

Full CRUD integration test against the real portal (mirrors
`tests/Integration/Services/Catalog/Product/Service/ProductTest.php` pattern — uses
`Factory::getServiceBuilder()->getCatalogScope()->catalog()->list(...)` to obtain a valid `iblockId`
for the products catalog, since services live in the same product iblock):

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Product\ProductService\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Service\ProductService;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductService::class)]
class ProductServiceTest extends TestCase
{
    private ProductService $productServiceScope;
    private Catalog $catalogService;

    #[\Override]
    protected function setUp(): void
    {
        $this->productServiceScope = Factory::getServiceBuilder()->getCatalogScope()->productService();
        $this->catalogService = Factory::getServiceBuilder()->getCatalogScope()->catalog();
    }

    #[TestDox('test ProductService::add, get, update, delete, list, fieldsByFilter')]
    public function testAddGetUpdateDeleteListFieldsByFilter(): void
    {
        $iblockId = $this->catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $addResult = $this->productServiceScope->add([
            'iblockId' => $iblockId,
            'name' => sprintf('test service %s', time()),
        ]);
        $serviceId = $addResult->productService()->id;
        $this->assertGreaterThan(0, $serviceId);

        $getResult = $this->productServiceScope->get($serviceId);
        $this->assertEquals($serviceId, $getResult->productService()->id);

        $updated = $this->productServiceScope->update($serviceId, ['name' => 'updated name']);
        $this->assertEquals('updated name', $updated->productService()->name);

        $listResult = $this->productServiceScope->list(
            ['id', 'iblockId'],
            ['id' => $serviceId, 'iblockId' => $iblockId]
        );
        $this->assertCount(1, $listResult->getProductServices());

        $fields = $this->productServiceScope->fieldsByFilter($iblockId);
        $this->assertIsArray($fields->getFieldsDescription());

        $this->assertTrue($this->productServiceScope->delete($serviceId)->isSuccess());
    }
}
```

Analogous integration test files (add/get/update/delete/list/fieldsByFilter, cleaning up in the same
test via `delete()` rather than `tearDown()` since the entities are created and destroyed within one
flow, consistent with the existing `ProductTest.php` pattern):
`tests/Integration/Services/Catalog/Product/Sku/Service/SkuTest.php` (SKU add requires only
`iblockId`/`name` — no `parentId`),
`tests/Integration/Services/Catalog/Product/Offer/Service/OfferTest.php` (offer add requires a valid
SKU `parentId` — created via the Sku service in the same test, then cleaned up in reverse order:
offer deleted before its parent SKU).

### 15. Mandatory `*ItemResult` annotation/type-cast tests

Per the skill's mandatory rule — one dedicated test file per new `*ItemResult` class:
- `tests/Integration/Services/Catalog/Product/ProductService/Result/ProductServiceItemResultTest.php`
- `tests/Integration/Services/Catalog/Product/Sku/Result/SkuItemResultTest.php`
- `tests/Integration/Services/Catalog/Product/Offer/Result/OfferItemResultTest.php`

(No `ProductItemResultTest.php` — see the "Scope correction" note under Files to Modify item 2 for why
this was descoped after discovering a pre-existing, unrelated bug.)

Each uses `CustomBitrix24Assertions::assertBitrix24AllResultItemFieldsAnnotated()` against a raw
`add()` response's `service`/`sku`/`offer` array keys, and
`assertBitrix24ResultItemFieldsTypeCastMatchAnnotations()` — matching the template in the skill's
"Mandatory integration test for every *ItemResult" section, adapted to call `add()` then `delete()`
for cleanup instead of a bare `get()`/`list()` fetch (since these entities require creation first).

**Confirmed compatible**: `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations()` (in
`tests/CustomAssertions/CustomBitrix24Assertions.php`) type-hints its first parameter as the generic
`Bitrix24\SDK\Core\Result\AbstractItem` (the parent of `AbstractCatalogItem`) and works purely via
`$item->$propName` magic-getter access + Typhoon Reflection against `@property-read` PHPDoc — it has
no dependency on `AbstractAnnotatedItem` internals. So the manual `__get()` cast pattern in
`AbstractCatalogItem` is fully compatible with the mandatory annotation/type-cast test, as long as the
PHPDoc annotations accurately describe what `__get()` actually returns for each key.

---

## Files to Modify

### 1. `src/Services/Catalog/Product/Service/Product.php`

Add two methods:

```php
/**
 * The method updates commercial catalog product by ID with the given fields.
 *
 * @see https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-update.html
 * @throws BaseException
 * @throws TransportException
 */
#[ApiEndpointMetadata(
    'catalog.product.update',
    'https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-update.html',
    'The method updates commercial catalog product by ID with the given fields.'
)]
public function update(int $productId, array $productFields): ProductResult
{
    return new ProductResult($this->core->call('catalog.product.update', [
        'id' => $productId,
        'fields' => $productFields,
    ]));
}

/**
 * The method downloads commercial catalog product files by the given parameters.
 *
 * @see https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-download.html
 * @throws BaseException
 * @throws TransportException
 */
#[ApiEndpointMetadata(
    'catalog.product.download',
    'https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-download.html',
    'The method downloads commercial catalog product files by the given parameters.'
)]
public function download(int $fileId, int $productId, string $fieldName): Response
{
    return $this->core->call('catalog.product.download', [
        'fields' => [
            'fileId' => $fileId,
            'productId' => $productId,
            'fieldName' => $fieldName,
        ],
    ]);
}
```

Add `use Bitrix24\SDK\Core\Response\Response;` import.

Also update the existing `@see https://training.bitrix24.com/...` links on `get`/`add`/`delete`/`list`/
`getFieldsByFilter` methods to the new `https://apidocs.bitrix24.com/api-reference/catalog/product/...`
URLs for consistency (per project rule: doc links must point to the English `apidocs.bitrix24.com`
site) — **only touch the `@see` line and the `ApiEndpointMetadata` URL argument**, no other changes
to existing methods.

### 2. `src/Services/Catalog/Common/Result/AbstractCatalogItem.php`

Confirmed current state of `AbstractCatalogItem::__get()` (read directly from source):
- `sort` is **already** cast to `int` (grouped in the existing
  `case 'createdBy': case 'iblockId': ... case 'sort': case 'height': case 'length':` int-casting
  branch) — **no change needed**, annotate `@property-read int $sort` to match.
- `vatId` is **not** handled by any case — falls through to the generic
  `return $this->data[$offset] ?? null;`, i.e. returned as a raw string or `null` — **needs a new
  case** added to the existing int-casting branch (`case 'vatId':` alongside `createdBy`/`iblockId`/etc.),
  since the annotation is `@property-read ?int $vatId`.
- `vatIncluded` is **not** handled — falls through raw (string `'Y'`/`'N'`) — **needs a new case**
  added to the existing bool-casting branch (`case 'active': case 'available': case 'bundle': case
  'vatIncluded':` → `return $this->data[$offset] === 'Y';`), since the annotation is
  `@property-read bool $vatIncluded`.
- `canBuyZero`, `barcodeMulti` already handled generically (nullable-bool branch) — reuse as-is.
- `subscribe`, `quantityTrace`, `withoutOrder` → kept as raw string (`Y`/`N`/`D`) per annotation
  (`@property-read string $subscribe` etc.), no cast needed — these are 3-state flags, not booleans.
- `parentId` → returned as raw array (`$this->data[$offset] ?? null`), annotated
  `@property-read ?array $parentId`, no special-case needed.

Only two new `case` labels are added to `AbstractCatalogItem::__get()` (`vatId` int-cast branch,
`vatIncluded` bool-cast branch) — both are additive to existing branches and do not change behavior
for any currently-annotated field of `ProductItemResult`, so no regression risk for the existing
`Product` service.

This file is shared by `ProductItemResult` too. The `vatId`/`vatIncluded` cases added here are
**purely additive** (new `case` labels in existing branches) and do not touch any code path used by
currently-annotated `ProductItemResult` fields, so there is no regression risk.

**Scope correction (found during implementation)**: while preparing the mandatory item-result test
for `ProductItemResult`, live-portal verification (`catalog.product.add` against the real webhook)
confirmed a pre-existing, unrelated bug: `ProductItemResult` annotates `purchasingCurrency` as
`?Currency` and `purchasingPrice` as `?Money`, but `AbstractCatalogItem::__get()` has no `case` for
either field, so both fall through to raw passthrough (raw string/`null`, not `Currency`/`Money`
objects). This bug predates issue #527 and is unrelated to `catalog.product.service/sku/offer`. Adding
a `ProductItemResultTest.php` now would fail on this pre-existing bug and force an out-of-scope fix.
Per user decision, this plan **does not** add `ProductItemResultTest.php` and does not fix the
`Currency`/`Money` cast bug — that is filed as a separate tracking issue instead
(`https://github.com/bitrix24/b24phpsdk/issues/[to be filed]`, see final report). The mandatory
item-result tests are added only for the three *new* result classes introduced by this issue.

### 3. `src/Services/Catalog/CatalogServiceBuilder.php`

Add three new builder methods:

```php
public function productService(): Catalog\Product\ProductService\Service\ProductService
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\Product\ProductService\Service\ProductService(
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}

public function productSku(): Catalog\Product\Sku\Service\Sku
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\Product\Sku\Service\Sku(
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}

public function productOffer(): Catalog\Product\Offer\Service\Offer
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\Product\Offer\Service\Offer(
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}
```

(No new Batch subclasses needed — `ProductService`/`Sku`/`Offer` do not receive a `Batch` collaborator
in this plan, matching the fact that the existing `Product::$batch` property is present but its
`Batch` class is an empty stub with no methods; batch support for `product.*` is a separate, already
tracked, un-scoped concern. If a reviewer requests batch parity, that would be a follow-up issue.)

### 4. `phpunit.xml.dist`

Add after the existing `integration_tests_scope_biconnector_dataset`-style blocks (alphabetically
near other scope entries, following the file's existing loose grouping):

```xml
<testsuite name="integration_tests_scope_catalog">
    <directory>./tests/Integration/Services/Catalog/</directory>
</testsuite>
<testsuite name="integration_tests_catalog_product">
    <file>./tests/Integration/Services/Catalog/Product/Service/ProductTest.php</file>
</testsuite>
<testsuite name="integration_tests_catalog_product_service">
    <file>./tests/Integration/Services/Catalog/Product/ProductService/Service/ProductServiceTest.php</file>
    <file>./tests/Integration/Services/Catalog/Product/ProductService/Result/ProductServiceItemResultTest.php</file>
</testsuite>
<testsuite name="integration_tests_catalog_product_sku">
    <file>./tests/Integration/Services/Catalog/Product/Sku/Service/SkuTest.php</file>
    <file>./tests/Integration/Services/Catalog/Product/Sku/Result/SkuItemResultTest.php</file>
</testsuite>
<testsuite name="integration_tests_catalog_product_offer">
    <file>./tests/Integration/Services/Catalog/Product/Offer/Service/OfferTest.php</file>
    <file>./tests/Integration/Services/Catalog/Product/Offer/Result/OfferItemResultTest.php</file>
</testsuite>
```

### 5. `Makefile`

Add after the existing `test-integration-mailservice` block (or in a new "Tests — integration
(Catalog)" section mirroring the doc table structure in `docs/testing.md`):

```makefile
.PHONY: test-integration-scope-catalog
test-integration-scope-catalog:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_scope_catalog

.PHONY: test-integration-catalog-product
test-integration-catalog-product:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_product

.PHONY: test-integration-catalog-product-service
test-integration-catalog-product-service:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_product_service

.PHONY: test-integration-catalog-product-sku
test-integration-catalog-product-sku:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_product_sku

.PHONY: test-integration-catalog-product-offer
test-integration-catalog-product-offer:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_product_offer
```

Also add corresponding `@echo` lines to the `help:` target's list, near the other
`test-integration-*` echo lines, under a new "Tests — integration (Catalog)" grouping.

### 6. `docs/testing.md`

Add a "Tests — integration (Catalog)" row group to the reference table (`test-integration-scope-catalog`,
`test-integration-catalog-product`, `test-integration-catalog-product-service`,
`test-integration-catalog-product-sku`, `test-integration-catalog-product-offer`), matching the
existing table format for other scopes (e.g. Sale, Landing).

### 7. `CHANGELOG.md`

Add under `## 3.4.0 – UNRELEASED` → `### Added` (top of that section, following the existing entry
style with a link to issue #527):

```markdown
- Added `update` and `download` methods to `Services\Catalog\Product\Service\Product` for
  `catalog.product.update` / `catalog.product.download`,
  see [catalog.product.* methods](https://apidocs.bitrix24.com/api-reference/catalog/product/index.html) ([#527](https://github.com/bitrix24/b24phpsdk/issues/527))
- Added service `Services\Catalog\Product\ProductService\Service\ProductService` with support for
  `catalog.product.service.*` methods,
  see [catalog.product.service.* methods](https://apidocs.bitrix24.com/api-reference/catalog/product/service/index.html) ([#527](https://github.com/bitrix24/b24phpsdk/issues/527)):
    - `add` creates a new service
    - `update` updates an existing service
    - `get` gets information about the service by its identifier
    - `list` gets the list of services by filter
    - `delete` deletes a service
    - `getFieldsByFilter` (`fieldsByFilter` method) returns service field descriptions by iblock filter
    - `download` downloads a service file
- Added service `Services\Catalog\Product\Sku\Service\Sku` with support for `catalog.product.sku.*`
  methods,
  see [catalog.product.sku.* methods](https://apidocs.bitrix24.com/api-reference/catalog/product/sku/index.html) ([#527](https://github.com/bitrix24/b24phpsdk/issues/527)):
    - `add` creates a new parent (SKU) product
    - `update` updates an existing parent product
    - `get` gets information about the parent product by its identifier
    - `list` gets the list of parent products by filter
    - `delete` deletes a parent product
    - `getFieldsByFilter` (`fieldsByFilter` method) returns parent product field descriptions by iblock filter
    - `download` downloads a parent product file
- Added service `Services\Catalog\Product\Offer\Service\Offer` with support for
  `catalog.product.offer.*` methods,
  see [catalog.product.offer.* methods](https://apidocs.bitrix24.com/api-reference/catalog/product/offer/index.html) ([#527](https://github.com/bitrix24/b24phpsdk/issues/527)):
    - `add` creates a new product variation (offer)
    - `update` updates an existing product variation
    - `get` gets information about the product variation by its identifier
    - `list` gets the list of product variations by filter
    - `delete` deletes a product variation
    - `getFieldsByFilter` (`fieldsByFilter` method) returns product variation field descriptions by iblock filter
    - `download` downloads a product variation file
```

---

## Deptrac compliance

All new classes are under `src/Services/Catalog/Product/{ProductService,Sku,Offer}/{Service,Result}/`.
They import only:
- `Bitrix24\SDK\Core\*` (Contracts, Credentials, Exceptions, Response, Result) — allowed
- `Bitrix24\SDK\Attributes\*` — allowed (metadata attributes, not layered)
- `Bitrix24\SDK\Services\AbstractService` and `Bitrix24\SDK\Services\Catalog\Common\*` — same-scope
  sibling imports within `Services`, allowed (`Services` may depend on `Core`, `Application`,
  `Legacy`; intra-`Services` sibling imports across scope subdirectories are the existing project
  norm, e.g. `ProductItemResult` already imports `Services\Catalog\Common\ProductType`)

No imports of `Infrastructure` or cross-scope `Services` (e.g. no CRM/Sale imports). No new
`deptrac.yaml` → `skip_violations` entries expected.

---

## Verification

```bash
docker compose run --rm php-cli vendor/bin/php-cs-fixer check --verbose --diff
docker compose run --rm php-cli vendor/bin/rector process --dry-run
docker compose run --rm php-cli vendor/bin/phpstan --memory-limit=2G analyse -vvv
docker compose run --rm php-cli vendor/bin/deptrac analyse
docker compose run --rm php-cli php -d auto_prepend_file=tests/phpunit-preload-guard.php vendor/bin/phpunit --testsuite unit_tests --display-warnings
docker compose run --rm php-cli php -d auto_prepend_file=tests/phpunit-preload-guard.php vendor/bin/phpunit --testsuite integration_tests_scope_catalog
```

(Using `docker compose run` directly instead of `make`, since `make` is not available in this
Windows/Git-Bash environment — `docker` and `docker-compose` are available and confirmed working.)
