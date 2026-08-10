# Plan: Add support for catalog.ratio.* (issue #570)

## Context

Bitrix24 REST API scope `catalog.ratio.*` exposes measurement-unit ratio ("коэффициент единицы
измерения") entities linking a product (`productId`) to a measure ratio value. The scope exposes
exactly three read-only methods — there is no `add`, `update`, or `delete`:

- `catalog.ratio.get` — https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-get.html
- `catalog.ratio.list` — https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-list.html
- `catalog.ratio.getFields` — https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-get-fields.html

### API details (from Bitrix24 MCP method-details)

**`catalog.ratio.get`**
- params: `id` (int, required)
- response: `{"result": {"ratio": {...}}}`
- fields example: `id` (int), `isDefault` (Y/N char), `productId` (int), `ratio` (double/float)

**`catalog.ratio.list`**
- documented params: `select` (array, optional), `filter` (object, optional). Decision: mirror
  `Extra::list(array $select = [], array $filter = [])` exactly — Extra is the closest read-only,
  no-CRUD analog in this SDK and its `list()` intentionally omits `order`/`start` since neither
  is part of the documented parameter list for that scope either.
- response: `{"result": {"ratios": [{...}]}, "total": N}`

**`catalog.ratio.getFields`**
- no params
- response: `{"result": {"ratio": {"id": {...}, "isDefault": {...}, "productId": {...}, "ratio": {...}}}}`

### Chosen template: `src/Services/Catalog/Extra/`

`catalog.extra.*` is the closest existing analog in this codebase: read-only scope with exactly
`get`, `list`, `getFields`, no `add`/`update`/`delete`, no `Batch` class needed. `Ratio` will
mirror this file-for-file:

- `Service::get(int $id)` — guards positive id via `AbstractService::guardPositiveId()`, calls
  `catalog.ratio.get`, returns `RatioResult`
- `Service::list(array $select = [], array $filter = [])` — calls `catalog.ratio.list`, returns
  `RatiosResult`
- `Service::fields(): FieldsResult` — calls `catalog.ratio.getFields`, reuses the shared
  `Bitrix24\SDK\Core\Result\FieldsResult` (same as `Extra::fields()`) rather than a custom
  `RatioFieldsResult`, since the raw response is only ever read via `getFieldsDescription()`
  and no per-field key normalization is needed.

### Result item fields (`RatioItemResult`)

Based on the `catalog.ratio.get`/`getFields` documented response:

```
@property-read int    $id           // integer, read-only
@property-read bool   $isDefault    // char Y/N -> bool
@property-read int    $productId    // integer, required
@property-read float  $ratio        // double, required
```

`AbstractAnnotatedItem` casts `Y`/`N` automatically to bool and numeric strings to int/float, so
no manual `__get()` override or `AbstractCrmItem`-style casting is needed (this is not a CRM
scope).

### Deptrac compliance

New code lives entirely under `src/Services/Catalog/Ratio/` (`Services` layer), importing only
from `Core` (`AbstractAnnotatedItem`, `AbstractResult`, `FieldsResult`, `BaseException`,
`TransportException`, `Scope`) and `Services\AbstractService` — same dependency shape as
`Extra`. No new deptrac violations.

---

## Files to Create

### 1. `src/Services/Catalog/Ratio/Result/RatioItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Ratio\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int   $id
 * @property-read bool  $isDefault
 * @property-read int   $productId
 * @property-read float $ratio
 */
class RatioItemResult extends AbstractAnnotatedItem
{
}
```

### 2. `src/Services/Catalog/Ratio/Result/RatioResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Ratio\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class RatioResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function ratio(): RatioItemResult
    {
        return new RatioItemResult($this->getCoreResponse()->getResponseData()->getResult()['ratio']);
    }
}
```

### 3. `src/Services/Catalog/Ratio/Result/RatiosResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Ratio\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class RatiosResult extends AbstractResult
{
    /**
     * @return RatioItemResult[]
     * @throws BaseException
     */
    public function getRatios(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['ratios'] as $item) {
            $items[] = new RatioItemResult($item);
        }

        return $items;
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

### 4. `src/Services/Catalog/Ratio/Service/Ratio.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Ratio\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Ratio\Result\RatioResult;
use Bitrix24\SDK\Services\Catalog\Ratio\Result\RatiosResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Ratio extends AbstractService
{
    /**
     * Returns the values of the measurement unit ratio fields by identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-get.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.ratio.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-get.html',
        'Returns the values of the measurement unit ratio fields by identifier.'
    )]
    public function get(int $id): RatioResult
    {
        $this->guardPositiveId($id);

        return new RatioResult($this->core->call('catalog.ratio.get', ['id' => $id]));
    }

    /**
     * Returns a list of measurement unit ratios from the catalog matching the given filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-list.html
     *
     * @param string[]             $select
     * @param array<string, mixed> $filter
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.ratio.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-list.html',
        'Returns a list of measurement unit ratios from the catalog matching the given filter.'
    )]
    public function list(array $select = [], array $filter = []): RatiosResult
    {
        return new RatiosResult($this->core->call('catalog.ratio.list', [
            'select' => $select,
            'filter' => $filter,
        ]));
    }

    /**
     * Returns the available fields of a measurement unit ratio.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-get-fields.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.ratio.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-get-fields.html',
        'Returns the available fields of a measurement unit ratio.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('catalog.ratio.getFields'));
    }
}
```

### 5. `tests/Unit/Services/Catalog/Ratio/Service/RatioTest.php`

Mirrors `tests/Unit/Services/Catalog/Extra/Service/ExtraTest.php`:
- `testGetReturnsRatioResult` — `assertInstanceOf(RatioResult::class, $service->get(1))`
- `testListReturnsRatiosResult` — `assertInstanceOf(RatiosResult::class, $service->list())`
- `testFieldsReturnsFieldsResult` — `assertInstanceOf(FieldsResult::class, $service->fields())`
- `testGetThrowsOnNonPositiveId` — expects `InvalidArgumentException` on `get(0)`

Uses `NullCore` + `NullLogger`, `#[CoversClass(Ratio::class)]`.

### 6. `tests/Integration/Services/Catalog/Ratio/Service/RatioTest.php`

Mirrors `tests/Integration/Services/Catalog/Extra/Service/ExtraTest.php`:
- `setUp()` via `Factory::getServiceBuilder()->getCatalogScope()->ratio()`
- `testGetFields()` — asserts `ratio` key present with `id`, `isDefault`, `productId`, `ratio` sub-keys
- `testList()` — asserts array result + `getTotal() >= 0`
- `testGet()` — since `catalog.ratio` has no `add` REST method (ratios are created implicitly
  when a product's measure ratio is configured), follow the same "skip if portal has none"
  pattern as `ExtraTest::testGet()` if `list()` is empty. Confirmed via a live
  `catalog.ratio.list` call against the test webhook (`tests/.env.local`) that the current test
  portal returns `{"ratios": [], "total": 0}` — the skip guard is required, not just a safe
  default.

`#[CoversMethod(Ratio::class, 'get')]`, `#[CoversMethod(Ratio::class, 'list')]`,
`#[CoversMethod(Ratio::class, 'fields')]`.

### 7. `tests/Integration/Services/Catalog/Ratio/Result/RatioItemResultAnnotationsTest.php`

Mirrors `tests/Integration/Services/Catalog/Extra/Result/ExtraItemResultAnnotationsTest.php`:
- `getFirstRatioRawItem()` helper reading `list()->getCoreResponse()...->getResult()['ratios'][0]`
  (skip test if portal has no ratios — required, confirmed empty on the current test portal, see
  item 6 above)
- `testAllSystemFieldsAnnotated` — via `assertBitrix24AllResultItemFieldsAnnotated`
- `testAllSystemFieldsHasValidTypeAnnotation` — via `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations`

`#[CoversClass(RatioItemResult::class)]`.

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Confirmed: `extra()` is registered at line 114-124. Insert `ratio()` directly after it
(before `productImage()` at line 126), copying the exact no-batch construction shape:

```php
    public function ratio(): Catalog\Ratio\Service\Ratio
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\Ratio\Service\Ratio(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
```

### 2. `phpunit.xml.dist`

Add a new testsuite entry near `integration_tests_catalog_extra` (alphabetically after
`integration_tests_catalog_price*` group or near `extra`/`measure`, matching existing ordering
in the file):

```xml
        <testsuite name="integration_tests_catalog_ratio">
            <directory>./tests/Integration/Services/Catalog/Ratio/</directory>
        </testsuite>
```

### 3. `Makefile`

Add near `test-integration-catalog-measure` / `test-integration-catalog-extra`:

```makefile
.PHONY: test-integration-catalog-ratio
test-integration-catalog-ratio:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_ratio
```

Also add a row to the `docs/testing.md` Catalog integration test table (`make test-integration-catalog-ratio` | `Measurement unit ratio`).

### 4. `CHANGELOG.md`

Add under `## Unreleased` → `### Added`, at the top of the list:

```markdown
- Added service `Services\Catalog\Ratio` with support methods,
  see [catalog.ratio.* methods](https://apidocs.bitrix24.com/api-reference/catalog/ratio/index.html) ([#570](https://github.com/bitrix24/b24phpsdk/issues/570)):
    - `get` gets the values of a measurement unit ratio by identifier
    - `list` gets the list of measurement unit ratios matching a filter
    - `getFields` returns the description of measurement unit ratio fields
```

### 5. `docs/testing.md`

No linter-config changes needed (`.php-cs-fixer.php`, `phpstan.neon.dist`, `rector.php` already
glob the whole `src/Services/Catalog/` / `tests/Integration/Services/Catalog` tree). Only the
Catalog integration test table needs the new row (see item 3 above).

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-ratio
```
