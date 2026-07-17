# Plan: Add support for catalog.productImage.* methods (issue #537)

## Context

Bitrix24 REST API exposes `catalog.productImage.*` methods to manage images attached to a
commercial catalog product, parent product (SKU), product variation (offer), or service.
Docs: https://apidocs.bitrix24.com/api-reference/catalog/product-image/index.html

Author: © Dmitriy Ignatenko <algonexys@gmail.com>
Issue: https://github.com/bitrix24/b24phpsdk/issues/537
Branch (already checked out): `feature/537-add-catalog.productImage-v3`, base `v3-dev`.

### REST method details (fetched via Bitrix24 MCP `bitrix-method-details`)

All methods belong to scope `catalog`.

**catalog.productImage.add**
`https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-add.html`
- params: `fields` (object, required): `productId` (required), `type` (string, optional,
  one of `DETAIL_PICTURE`, `PREVIEW_PICTURE`, `MORE_PHOTO`, default `MORE_PHOTO`);
  `fileContent` (array, required): `[fileName: string, base64Content: string]`
- returns: `result.productImage` (single `catalog_product_image` object)

**catalog.productImage.get**
`https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-get.html`
- params: `productId` (required), `id` (required, image id)
- returns: `result.productImage`

**catalog.productImage.list**
`https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-list.html`
- params: `productId` (required), `select` (array, optional)
- returns: `result.productImages` (array), `result.total` — NOTE: no `filter`/`order`/`start`
  params exist for this method per the official docs; it is a flat list scoped to one product,
  not a paginated collection.
- returns: `result` (bool) — no `result.productImage*` wrapper

**catalog.productImage.delete**
`https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-delete.html`
- params: `productId` (required), `id` (required)
- returns: `result` (bool)

**catalog.productImage.getFields**
`https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-get-fields.html`
- params: none
- returns: `result.productImage` — a map `{field_code: rest_field_description}` (NOT an array of
  items), analogous to `catalog.catalog.getFields` pattern already in the SDK
  (`src/Services/Catalog/Catalog/Service/Catalog.php::fields()` returning `Core\Result\FieldsResult`).
  Difference: this method wraps the field map under a `productImage` key instead of returning it
  at the result root, so a small dedicated result class is needed instead of reusing
  `Core\Result\FieldsResult` directly.

### `catalog_product_image` fields (from `getFields` response + `add`/`get`/`list` examples)

| field | type | notes |
|---|---|---|
| `id` | int | read-only |
| `name` | string | read-only, file name |
| `productId` | int | required on add; typed as string in `getFields` metadata but is numeric everywhere else — annotate as `int` to match `get`/`list`/`add` response payloads |
| `type` | string | `DETAIL_PICTURE` \| `PREVIEW_PICTURE` \| `MORE_PHOTO` |
| `createTime` | datetime (ISO 8601) | read-only |
| `detailUrl` | string | read-only, relative URL |
| `downloadUrl` | string | read-only, absolute URL with token |

### Existing SDK patterns used as templates

- `src/Services/CRM/Documentgenerator/Document/` — full CRUD + custom `Batch` (id key
  lower-case, custom result envelope key) — used as the primary template for service method
  structure, docblocks, `#[ApiEndpointMetadata]` usage, and batch service pattern.
- `src/Services/Catalog/Catalog/Service/Catalog.php` — existing `catalog.catalog.getFields`
  precedent within the same `Catalog` scope.
- `src/Core/Result/AbstractAnnotatedItem.php` — mandatory base class for the new
  `ProductImageItemResult` (per skill rule): drives `CarbonImmutable`/`int`/`bool` casting from
  `@property-read` annotations, no manual `__get()` override needed.
- `src/Services/Catalog/CatalogServiceBuilder.php` — registration point for the new
  `productImage()` accessor.

### Batch support

`add` and `delete` fit the standard batch machinery from `Core\Batch` EXCEPT for the `delete`
parameter names: standard `deleteEntityItems()` sends `{'ID': $id}`, but
`catalog.productImage.delete` expects `{'productId': ..., 'id': ...}` (two required params, not
one). A custom `Batch` class (`src/Services/Catalog/ProductImage/Batch.php`, extends
`Core\Batch`) is required, overriding `deleteEntityItems()` similarly to
`CRM\Documentgenerator\Document\Batch::deleteEntityItems()`, but keyed by a composite
`[productId, imageId]` pair since a single int key is insufficient.

`add` via batch: `Core\Batch::addEntityItems()` forwards each array element as-is to
`registerCommand()`, so passing `['fields' => [...], 'fileContent' => [...]]` per item already
matches the required `add` payload shape — no override needed for add.

`list` has no `filter`/`order`/`start` params in the official docs, so the generic
`getTraversableList*()` pagination helpers do not apply. The batch `list()` method will do a
single-shot fetch per `productId` (looping client-side over multiple product ids is out of
scope — mirrors the single-product-scoped nature of the REST method itself). No `Core\Batch`
override needed for list; the batch `list()` method calls `$this->batch->addEntityItems()`-style
raw batch registration is unnecessary — simplest correct implementation is to expose a
`list(array $productIds): Generator` batch helper that registers one `catalog.productImage.list`
command per product id via the batch command channel, reusing `getTraversableBatchResults()`
indirectly through a thin wrapper — see Batch.php skeleton below for the concrete approach
(register commands directly through `BatchOperationsInterface`, no low-level protected access
needed since `addEntityItems` already provides a generic per-item command registration + yield
loop).

---

## Files to Create

### 1. `src/Services/Catalog/ProductImage/Result/ProductImageItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductImage\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read int $productId
 * @property-read string $type
 * @property-read CarbonImmutable|null $createTime
 * @property-read string|null $detailUrl
 * @property-read string|null $downloadUrl
 */
class ProductImageItemResult extends AbstractAnnotatedItem
{
}
```

### 2. `src/Services/Catalog/ProductImage/Result/ProductImageResult.php`

Envelope for `add`/`get`, unwraps `result.productImage`.

### 3. `src/Services/Catalog/ProductImage/Result/ProductImagesResult.php`

Envelope for `list`, unwraps `result.productImages` (+ exposes `getProductImages(): array` of
`ProductImageItemResult`, mirroring `ProductsResult::getProducts()`).

### 4. `src/Services/Catalog/ProductImage/Result/ProductImageFieldsResult.php`

Envelope for `getFields`, unwraps `result.productImage` (field-descriptor map, not items) via a
`getFieldsDescription(): array` method mirroring `Core\Result\FieldsResult`.

### 5. `src/Services/Catalog/ProductImage/Service/ProductImage.php`

```php
#[ApiServiceMetadata(new Scope(['catalog']))]
class ProductImage extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    public function add(int $productId, array $fileContent, ?string $type = null): ProductImageResult;
    public function get(int $productId, int $id): ProductImageResult;
    public function list(int $productId, array $select = []): ProductImagesResult;
    public function delete(int $productId, int $id): DeletedItemResult;
    public function getFields(): ProductImageFieldsResult;
}
```

- `add`: builds `fields = ['productId' => $productId] + ($type !== null ? ['type' => $type] : [])`,
  calls `catalog.productImage.add` with `['fields' => $fields, 'fileContent' => $fileContent]`.
- `delete`: returns `Core\Result\DeletedItemResult` (bool result, no envelope key) — matches
  existing SDK convention for boolean-result delete methods (see `Catalog\Product\Service\Product::delete`).
- `getFields`: no params.

### 6. `src/Services/Catalog/ProductImage/Batch.php`

Extends `\Bitrix24\SDK\Core\Batch`. Overrides `deleteEntityItems()` to accept
`array<int, array{productId:int, id:int}>` and register `catalog.productImage.delete` commands
with `['productId' => ..., 'id' => ...]` per item (standard base class assumes a single int key
named `ID`, which does not fit this two-key delete contract).

### 7. `src/Services/Catalog/ProductImage/Service/Batch.php`

Batch-mode wrapper service (mirrors `CRM\Documentgenerator\Document\Service\Batch`):
- `add(array $productImages): Generator` — `array<int, array{fields: array{productId:int, type?:string}, fileContent: array{0:string,1:string}}>`,
  delegates to `$this->batch->addEntityItems('catalog.productImage.add', $productImages)`,
  yields `ProductImageItemResult` unwrapped from `productImage` key per item.
- `delete(array $items): Generator` — `array<int, array{productId:int, id:int}>`, delegates to
  the overridden `deleteEntityItems()` on the custom `Batch` class, yields
  `Core\Result\DeletedItemBatchResult`.
- `list(array $productIds, array $select = []): Generator<int, ProductImageItemResult[]>` —
  `catalog.productImage.list` has no `filter`/`order`/`start` params (confirmed via docs), so
  `getTraversableList*()` pagination helpers do not apply. Instead reuse
  `BatchOperationsInterface::addEntityItems()` as a generic "one command per array item, yield
  raw `ResponseData` per item" primitive (it forwards each item array as-is to
  `registerCommand()` without assuming an `add`-specific shape): call
  `$this->batch->addEntityItems('catalog.productImage.list', array_map(static fn (int $productId) => ['productId' => $productId, 'select' => $select], $productIds))`,
  then for each yielded item wrap `result['productImages']` entries into `ProductImageItemResult`
  and yield the array keyed by the same integer index `addEntityItems` yields (which lines up
  positionally with `$productIds`). No custom `Batch` method needed for `list`.

### 8. `tests/Unit/Services/Catalog/ProductImage/Service/ProductImageTest.php`

Unit test using `NullCore`/`NullBatch`, covering all five service methods build correct
params and return the expected result type (per `docs/testing.md` unit pattern).

### 9. `tests/Integration/Services/Catalog/ProductImage/Service/ProductImageTest.php`

Integration CRUD test: `testGetFields`, `testAdd`, `testGet`, `testList`, `testDelete` against a
real product created via `catalog.product.add` in `setUp()`/cleaned in `tearDown()`.

### 10. `tests/Integration/Services/Catalog/ProductImage/Service/BatchTest.php`

Batch add/delete integration test.

### 11. `tests/Integration/Services/Catalog/ProductImage/Result/ProductImageItemResultTest.php`

Mandatory annotation + type-cast test per skill rule, using `CustomBitrix24Assertions`, fetching
a raw item via `get()`.

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Add:
```php
public function productImage(): Catalog\ProductImage\Service\ProductImage
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\ProductImage\Service\ProductImage(
            new Catalog\ProductImage\Service\Batch(
                new Catalog\ProductImage\Batch($this->core, $this->log),
                $this->log
            ),
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}
```

Note: `CatalogServiceBuilder` currently constructs `Product`'s batch with the generic
`$this->batch` (base `Core\Batch` instance shared via `AbstractServiceBuilder`). Because
`ProductImage` needs the custom `Batch` subclass (for `deleteEntityItems` override), its
`Service\Batch` must be constructed with a dedicated `new ProductImage\Batch($this->core, $this->log)`
instance rather than the shared `$this->batch`.

### 2. `.php-cs-fixer.php`

Add `->in(__DIR__ . '/src/Services/Catalog/')` to the finder chain (currently `Catalog` is
absent from this list even though `Product`/`Catalog` sub-scopes already exist). Confirmed with
user: widen to the whole `Catalog` scope, not just `ProductImage`, so pre-existing sibling code
gets linted too.

### 3. `phpunit.xml.dist`

Add test suites:
```xml
<testsuite name="integration_tests_catalog_product_image">
    <directory>./tests/Integration/Services/Catalog/ProductImage/</directory>
</testsuite>
<testsuite name="integration_tests_catalog_product_image_annotations">
    <file>./tests/Integration/Services/Catalog/ProductImage/Result/ProductImageItemResultTest.php</file>
</testsuite>
```

### 4. `Makefile`

```makefile
.PHONY: test-integration-catalog-product-image
test-integration-catalog-product-image:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_product_image
```

### 5. `CHANGELOG.md`

Under `## 3.4.0 – UNRELEASED` → `### Added`, insert at top of the `### Added` list:

```markdown
- Added service `Services\Catalog\ProductImage` with support for `catalog.productImage.*` methods,
  see [catalog.productImage.* methods](https://apidocs.bitrix24.com/api-reference/catalog/product-image/index.html) ([#537](https://github.com/bitrix24/b24phpsdk/issues/537)):
    - `add` adds an image to a product, parent product, variation, or service, with batch calls support
    - `get` gets information about a product image by its identifier
    - `list` gets the list of images for a product
    - `delete` deletes a product image, with batch calls support
    - `getFields` returns the description of product image fields
```

### 6. `src/Services/ServiceBuilder.php`

No change expected — `getCatalogScope()` should already exist and return `CatalogServiceBuilder`;
verify during implementation, do not add a duplicate accessor.

---

## Deptrac compliance

New code lives entirely under `Services\Catalog\ProductImage\*` (Services layer) and imports only
from `Core` (`AbstractAnnotatedItem`, `AbstractResult`, `Batch`, `DeletedItemResult`,
`BatchOperationsInterface`, exceptions) and `Services\AbstractService` /
`Services\AbstractServiceBuilder`. No cross-service imports, no `Infrastructure` imports. No new
`deptrac.yaml` skip_violations entries expected.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-product-image
```

## User decisions (confirmed)

1. `.php-cs-fixer.php`: widen to the whole `src/Services/Catalog/` scope.
2. Batch `list`: implement `Service\Batch::list(array $productIds): Generator` looping over
   multiple product ids in a single batch round-trip (see Files to Create §7).

## Status: implementation complete, quality gate green

All files from the plan were created/modified. Quality gate results:
- `lint-cs-fixer`: 0 issues after auto-fix (fix also cleaned 12 pre-existing files in
  `src/Services/Catalog/*` that had never been run through cs-fixer before, per user decision #1)
- `lint-rector`: 0 issues after auto-fix (2 minor variable-naming fixes applied to new test files)
- `lint-phpstan`: no errors
- `lint-deptrac`: 0 violations
- `test-unit`: new `ProductImageTest` unit suite (5 tests) passes in isolation. Full unit suite
  has 8 pre-existing errors/12 failures unrelated to this change — root cause confirmed to be a
  `typhoon/reflection` vs `phpstan/phpdoc-parser` version conflict inside
  `vendor/rector/rector/vendor/phpstan/phpdoc-parser` that breaks `AbstractAnnotatedItem`
  reflection repo-wide (reproduced independently via
  `tests/Unit/Core/Result/AbstractAnnotatedItemTest.php`, which is not part of this issue's
  changes). Environment/dependency issue, out of scope for #537.
- `test-integration-catalog-product-image`: 10 tests run, 8 pass (all CRUD + batch add/delete/list
  against the real portal), 2 errors in `ProductImageItemResultTest` (the mandatory annotation
  test) — same pre-existing vendor conflict as above, since it also goes through
  `AbstractAnnotatedItem`'s Typhoon-based reflection.

CHANGELOG.md, phpunit.xml.dist, Makefile all updated per plan.

## Follow-up: determineKeyId() override (user feedback)

User pointed out that when the REST identifier field casing differs from the SDK's default
`ID` (e.g. `catalog.productImage.*` uses lowercase `id`), the convention in this codebase is to
extend `\Bitrix24\SDK\Core\Batch` and override `determineKeyId()` to return the lowercase key —
see `src/Services/Biconnector/Connector/Batch.php` and `src/Services/Biconnector/Source/Batch.php`
for the reference pattern. `src/Services/Catalog/ProductImage/Batch.php` already extended
`Core\Batch` (for the custom two-key `deleteEntityItems()`), but was missing the
`determineKeyId()` override. Added:

```php
#[\Override]
protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
{
    return 'id';
}
```

This keeps `getTraversableList()`/`getTraversableListWithCount()` correct if ever invoked
against this custom `Batch` instance (they default to `'ID'` otherwise). Verified no regression:
`lint-cs-fixer`, `lint-rector`, `lint-phpstan`, `lint-deptrac` all still green; unit tests (5/5)
and integration tests (8/10, same 2 pre-existing vendor-conflict errors as before) unchanged.
