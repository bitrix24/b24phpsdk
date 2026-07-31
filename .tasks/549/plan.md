# Plan: Add support for catalog.productPropertyEnum (issue #549)

## Context

The SDK already implements the `catalog` scope (`src/Services/Catalog/`) with `Catalog` and
`Product` services registered via `CatalogServiceBuilder`. This issue adds a new entity within
the same scope: `catalog.productPropertyEnum.*`, which manages list-property enum values
(possible values of a "list" type product/variation property, e.g. size/color options).

API methods (confirmed via `mcp__Bitrix24_REST_API__bitrix-method-details`, English docs at
`https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/`):

- `catalog.productPropertyEnum.add` — params: `fields: {propertyId, value, xmlId, def, sort}`.
  Returns `result.productPropertyEnum` (object).
- `catalog.productPropertyEnum.update` — params: `id`, `fields: {propertyId, value, xmlId, def, sort}`.
  Returns `result.productPropertyEnum` (object).
- `catalog.productPropertyEnum.get` — params: `id`. Returns `result.productPropertyEnum` (object).
- `catalog.productPropertyEnum.list` — params: `select`, `filter`, `order` (all optional, no
  `start` param documented — pagination via `next`/`total` in response, not requested by
  the method signature itself). Returns `result.productPropertyEnums` (array), `next`, `total`.
- `catalog.productPropertyEnum.delete` — params: `id`. Returns `result` as boolean directly
  (not nested).
- `catalog.productPropertyEnum.getFields` — no params. Returns `result.productPropertyEnum`
  as an object of field-name => `rest_field_description` (same shape consumed by `FieldsResult`
  style classes, but nested under a key, same pattern as `crm.documentgenerator.document.getfields`
  → `DocumentFieldsResult`).

Entity fields (from `getFields` response):
| Field | Bitrix type | required | readOnly |
|---|---|---|---|
| `id` | integer | no | yes |
| `propertyId` | integer | yes | no |
| `value` | string | yes | no |
| `xmlId` | string | yes | no |
| `def` | char (Y/N) | no | no |
| `sort` | integer | no | no |

All fields are scalar — no nested objects/arrays, no dates. This matches the "no `AbstractCrmItem`/
`AbstractCatalogItem`-style override needed" case: `def` (char Y/N) is handled automatically by
`AbstractAnnotatedItem::castValue()` when annotated as `bool`, and `id`/`propertyId`/`sort` as
`int`, `value`/`xmlId` as `string` — all built-in casts. So `ProductPropertyEnumItemResult` extends
`Bitrix24\SDK\Core\Result\AbstractAnnotatedItem` directly (per skill rule), no custom `__get()`.

### Naming and placement

Following the existing `Catalog` scope layout (`Product`, `Catalog` sub-entities each get their
own directory under `src/Services/Catalog/<Entity>/{Result,Service}`):

```
src/Services/Catalog/ProductPropertyEnum/
├── Result/
│   ├── ProductPropertyEnumItemResult.php
│   ├── ProductPropertyEnumResult.php        (single item envelope: add/update/get)
│   ├── ProductPropertyEnumsResult.php       (list envelope)
│   └── ProductPropertyEnumFieldsResult.php  (getFields envelope)
└── Service/
    ├── ProductPropertyEnum.php
    └── Batch.php
```

- `delete` reuses `Bitrix24\SDK\Core\Result\DeletedItemResult` as-is — the base class already
  reads `getResult()[0]` cast to bool, and the raw response is `"result": true` (unwrapped
  boolean, same shape base `DeletedItemResult` expects). No custom delete result class needed.
- No custom `Batch` behavior is expected (keys match style already used elsewhere: camelCase
  request params, `fields` wrapper) — but we still create `Service/Batch.php` (readonly wrapper,
  same shape as `Catalog\Product\Service\Batch`) so the constructor pattern stays consistent
  and batch support exists for `list`/`add`/`update`/`delete`. It extends nothing special
  (`Core\Batch` used directly through `BatchOperationsInterface`, same as `Product\Service\Batch`).

### CatalogServiceBuilder registration

Add a `productPropertyEnum()` factory method to `src/Services/Catalog/CatalogServiceBuilder.php`,
following the same pattern as `product()`.

---

## Files to Create

### 1. `src/Services/Catalog/ProductPropertyEnum/Result/ProductPropertyEnumItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int $id
 * @property-read int $propertyId
 * @property-read string $value
 * @property-read string $xmlId
 * @property-read bool|null $def
 * @property-read int|null $sort
 */
class ProductPropertyEnumItemResult extends AbstractAnnotatedItem
{
}
```

### 2. `src/Services/Catalog/ProductPropertyEnum/Result/ProductPropertyEnumResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class ProductPropertyEnumResult extends AbstractResult
{
    public function productPropertyEnum(): ProductPropertyEnumItemResult
    {
        return new ProductPropertyEnumItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()['productPropertyEnum']
        );
    }
}
```

### 3. `src/Services/Catalog/ProductPropertyEnum/Result/ProductPropertyEnumsResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ProductPropertyEnumsResult extends AbstractResult
{
    /**
     * @return ProductPropertyEnumItemResult[]
     * @throws BaseException
     */
    public function getProductPropertyEnums(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['productPropertyEnums'] as $item) {
            $items[] = new ProductPropertyEnumItemResult($item);
        }

        return $items;
    }
}
```

### 4. `src/Services/Catalog/ProductPropertyEnum/Result/ProductPropertyEnumFieldsResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ProductPropertyEnumFieldsResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getFieldsDescription(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!empty($result['productPropertyEnum']) && is_array($result['productPropertyEnum'])) {
            return $result['productPropertyEnum'];
        }

        return $result;
    }
}
```

### 5. `src/Services/Catalog/ProductPropertyEnum/Service/Batch.php`

Same shape as `Catalog\Product\Service\Batch` (empty readonly wrapper holding
`BatchOperationsInterface` + logger, ready for future batch-mode methods).

### 6. `src/Services/Catalog/ProductPropertyEnum/Service/ProductPropertyEnum.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumFieldsResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class ProductPropertyEnum extends AbstractService
{
    public function __construct(
        public Batch $batch,
        CoreInterface $core,
        LoggerInterface $logger
    ) {
        parent::__construct($core, $logger);
    }

    #[ApiEndpointMetadata(
        'catalog.productPropertyEnum.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-add.html',
        'Adds a new value for a list-type product or variation property.'
    )]
    public function add(array $fields): ProductPropertyEnumResult
    {
        return new ProductPropertyEnumResult(
            $this->core->call('catalog.productPropertyEnum.add', ['fields' => $fields])
        );
    }

    #[ApiEndpointMetadata(
        'catalog.productPropertyEnum.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-update.html',
        'Updates a list-type property value of a commercial catalog product or variation.'
    )]
    public function update(int $id, array $fields): ProductPropertyEnumResult
    {
        return new ProductPropertyEnumResult(
            $this->core->call('catalog.productPropertyEnum.update', [
                'id' => $id,
                'fields' => $fields,
            ])
        );
    }

    #[ApiEndpointMetadata(
        'catalog.productPropertyEnum.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-get.html',
        'Returns a list-type property value by its identifier.'
    )]
    public function get(int $id): ProductPropertyEnumResult
    {
        return new ProductPropertyEnumResult(
            $this->core->call('catalog.productPropertyEnum.get', ['id' => $id])
        );
    }

    public function list(array $select = [], array $filter = [], array $order = []): ProductPropertyEnumsResult
    {
        $params = [];
        if ($select !== []) {
            $params['select'] = $select;
        }
        if ($filter !== []) {
            $params['filter'] = $filter;
        }
        if ($order !== []) {
            $params['order'] = $order;
        }

        return new ProductPropertyEnumsResult(
            $this->core->call('catalog.productPropertyEnum.list', $params)
        );
    }

    #[ApiEndpointMetadata(
        'catalog.productPropertyEnum.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-delete.html',
        'Deletes a list-type property value by its identifier.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('catalog.productPropertyEnum.delete', ['id' => $id])
        );
    }

    #[ApiEndpointMetadata(
        'catalog.productPropertyEnum.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-get-fields.html',
        'Returns the field description of list-type property values.'
    )]
    public function getFields(): ProductPropertyEnumFieldsResult
    {
        return new ProductPropertyEnumFieldsResult(
            $this->core->call('catalog.productPropertyEnum.getFields', [])
        );
    }
}
```

Note: exact doc-comments (with `@link`, `@throws`) to be filled following `Document.php` style
during implementation (TDD), not just the attribute — this skeleton omits them for brevity.

`list()` has no `start` param (not documented in `catalog.productPropertyEnum.list`) — unlike
`Product::list()`. Signature intentionally omits it.

### 7. `tests/Unit/Services/Catalog/ProductPropertyEnum/Service/ProductPropertyEnumTest.php`

New — first unit test for the Catalog scope. Follows the pattern from
`docs/testing.md` using `NullCore`, `NullBatch`, `NullLogger`. One test per method asserting the
service calls the correct REST method name via a mock `CoreInterface` (or `createMock` on
`CoreInterface` to assert `call()` arguments), plus `#[CoversClass]`.

### 8. `tests/Integration/Services/Catalog/ProductPropertyEnum/Service/ProductPropertyEnumTest.php`

`catalog.productProperty.*` is not implemented in the SDK yet (no dedicated service), so the
test creates its list-type property directly via the raw core client, scoped to `setUp`:

```php
#[\Override]
protected function setUp(): void
{
    $this->productPropertyEnumService = Factory::getServiceBuilder()->getCatalogScope()->productPropertyEnum();
    $catalogService = Factory::getServiceBuilder()->getCatalogScope()->catalog();
    $iblockId = $catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

    $propertyResponse = Factory::getCore()->call('catalog.productProperty.add', [
        'fields' => [
            'iblockId' => $iblockId,
            'name' => sprintf('test list property %s', time()),
            'propertyType' => 'L',
            'listType' => 'L',
        ],
    ]);
    $this->propertyId = (int)$propertyResponse->getResponseData()->getResult()['productProperty']['id'];
}

#[\Override]
protected function tearDown(): void
{
    Factory::getCore()->call('catalog.productProperty.delete', ['id' => $this->propertyId]);
}
```

Confirmed: `Factory::getCore(): CoreInterface` exists (`tests/Integration/Factory.php:82`) —
used as shown above.

CRUD tests:
- `testAdd` — add an enum value with `propertyId`, `value`, `xmlId`, `def: 'Y'`, `sort`; assert
  returned `value`/`xmlId`/`propertyId`; delete in test to keep portal clean (or via a per-test
  cleanup list if multiple enum values are created across tests).
- `testUpdate` — add then update `value`/`sort`/`def`; assert updated fields via `get()`.
- `testGet` — add then get by id; assert equality.
- `testList` — add one or more; list filtered by `propertyId`; assert count/values.
- `testDelete` — add, delete, then list filtered by id; assert empty.
- `testGetFields` — assert `id`, `propertyId`, `value`, `xmlId`, `def`, `sort` keys present in
  `getFieldsDescription()`.

### 9. `tests/Integration/Services/Catalog/ProductPropertyEnum/Result/ProductPropertyEnumItemResultTest.php`

Mandatory annotation/type-cast test per skill rule, following the
`ChatMessageFieldItemResultTest` template: `testAllFieldsAreAnnotated` (raw `get()` response
keys vs `@property-read`) and `testAllFieldsHasValidTypeCastingInMagicGetters`.

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Add:

```php
public function productPropertyEnum(): Catalog\ProductPropertyEnum\Service\ProductPropertyEnum
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\ProductPropertyEnum\Service\ProductPropertyEnum(
            new Catalog\ProductPropertyEnum\Service\Batch($this->batch, $this->log),
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}
```

### 2. `.php-cs-fixer.php`

Add `->in(__DIR__ . '/src/Services/Catalog/')` to the `Finder` chain (Catalog is not currently
listed at all).

### 3. `phpunit.xml.dist`

Add test suites (placed near other Catalog-adjacent entries, alphabetically with existing groups):

```xml
<testsuite name="integration_tests_catalog_product_property_enum">
    <directory>./tests/Integration/Services/Catalog/ProductPropertyEnum/Service/</directory>
</testsuite>
<testsuite name="integration_tests_catalog_product_property_enum_annotations">
    <file>./tests/Integration/Services/Catalog/ProductPropertyEnum/Result/ProductPropertyEnumItemResultTest.php</file>
</testsuite>
```

Confirm whether a `unit_tests` suite already globs `tests/Unit/` broadly (likely yes, single
directory) — no change needed there if so; verify during implementation.

### 4. `Makefile`

Add targets:

```makefile
.PHONY: test-integration-catalog-product-property-enum
test-integration-catalog-product-property-enum:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_product_property_enum

.PHONY: test-integration-catalog-product-property-enum-annotations
test-integration-catalog-product-property-enum-annotations:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_product_property_enum_annotations
```

No existing Catalog-scope targets appear in `docs/testing.md`'s reference tables or the
Makefile `help:` section (confirmed by grep — `catalog` has zero matches in both `phpunit.xml.dist`
test-suite names and `Makefile` before this change), so `docs/testing.md` is left unchanged to
stay consistent with how `catalog.product.*` was integrated (no dedicated doc row either).

### 5. `CHANGELOG.md`

Add under `## X.Y.Z Unreleased` → `### Added`:

```markdown
- Added service `Services\Catalog\ProductPropertyEnum` with support methods,
  see [catalog.productPropertyEnum.* methods](https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/index.html):
    - `add` creates a new list-type property value
    - `update` updates an existing list-type property value
    - `get` gets a list-type property value by identifier
    - `list` gets the list of list-type property values by filter
    - `delete` deletes a list-type property value by identifier
    - `getFields` returns the description of list-type property value fields
  ([#549](https://github.com/bitrix24/b24phpsdk/issues/549))
```

---

## Deptrac compliance

`src/Services/Catalog/ProductPropertyEnum/**` falls under the `Services` layer (directory-based
collector on `src/Services`), same as all sibling Catalog code. It only imports from `Core`
(`AbstractAnnotatedItem`, `AbstractResult`, `DeletedItemResult`, `CoreInterface`,
`BaseException`, `TransportException`, `Scope`) and `Bitrix24\SDK\Attributes\*`, which is
allowed for `Services`. No new deptrac violation, no `skip_violations` entry needed.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-product-property-enum
make test-integration-catalog-product-property-enum-annotations
```

## Outcome

All commands above ran inside Docker (`docker compose run --rm php-cli ...`) and are green
except for a pre-existing, repo-wide dependency conflict unrelated to this issue:

- `lint-cs-fixer`, `lint-rector`, `lint-phpstan`, `lint-deptrac` — all clean, 0 violations.
- `test-unit` — new `ProductPropertyEnumTest` (7 tests, 28 assertions) passes. The full
  `unit_tests` suite has 24 pre-existing failures/errors unrelated to this change, all caused
  by a `typhoon/reflection` vs `phpstan/phpdoc-parser` (vendored under `rector/rector`) version
  conflict in the committed `composer.lock`: `PhpDocParser\Lexer::__construct()` is called with
  0 args but requires 1. This reproduces identically for pre-existing code
  (`ChatMessageFieldItemResultTest`, `AbstractAnnotatedItemTest`, `ContactItemResultTest`, etc.)
  — confirmed inside the pinned Docker image, not a local-environment artifact. Out of scope
  for #549 to fix (would require a `composer.json`/`composer.lock` dependency-resolution change).
- `test-integration-catalog-product-property-enum` — 5/6 tests pass live against the real
  portal (`update`, `get`, `list`, `delete`, `getFields`). `add` hits the same pre-existing
  Typhoon/phpdoc-parser crash when reading an annotated field via `AbstractAnnotatedItem`
  (`ProductPropertyEnumResult::productPropertyEnum()->value`) — same root cause as above, not
  a defect in the new code. The REST call itself succeeds (HTTP 200, correct response body).
- `test-integration-catalog-product-property-enum-annotations` — both tests hit the same
  pre-existing Typhoon crash for the same reason.

Recommendation: this environment issue should be tracked and fixed separately (likely a
`composer.lock` update pinning a compatible `phpstan/phpdoc-parser` version), since it blocks
every `AbstractAnnotatedItem`-based annotation/type-cast integration test in the repository,
not just this new service.
