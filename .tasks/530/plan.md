# Plan: Add support for catalog.enum.*, catalog.extra.*, catalog.measure.* (issue #530)

## Context

The `Catalog` scope already exists in the SDK (`src/Services/Catalog/`) with `Catalog` and `Product`
sub-services registered in `src/Services/Catalog/CatalogServiceBuilder.php`. This issue adds three
new sub-services to the same scope, following the existing `Catalog`/`Product` v1-style layout
(no `ApiVersion::v3`, classic `core->call('method', [...])` signature) — these are legacy-module
`catalog.*` REST methods (not REST v3), confirmed by their response envelopes using named keys
(`result.extra`, `result.measure`, `result.enum`) rather than the v3 `result.item` / `result.items`
convention used by e.g. `Services\Timeman\Record`.

Author of this implementation: © Dmitriy Ignatenko <algonexys@gmail.com>

### REST methods and confirmed response shapes (from Bitrix24 MCP method-details)

**catalog.enum.getRoundTypes** — no params.
Response: `result.enum` = array of `{id: int, name: string}`.

**catalog.enum.getStoreDocumentTypes** — no params.
Response: `result.enum` = array of `{id: string, name: string}` (single-letter codes: A, S, M, R, D).

**catalog.extra.get** — param `id` (int, required).
Response: `result.extra` = `catalog_extra` object: `{id: int, name: string, percentage: float}`.
CONFIRMED against a live portal via `catalog.extra.getFields` — the field set is `id` (integer,
readonly), `name` (string, required), `percentage` (double, required). The MCP documentation tool
omitted `name` from its field description; the live `getFields` response is authoritative.

**catalog.extra.list** — params `select` (array, optional), `filter` (object, optional).
Response CONFIRMED live: `result.extras` = array of `catalog_extra`, `total` = int as a **sibling
of `result`**, not nested inside it (i.e. `{"result": {"extras": [...]}, "total": N, "time": {...}}`).
`ExtrasResult::getTotal()` must therefore read `getResponseData()->getResult()` for `extras` but
pull `total` from the raw response body's top level, not from inside `result`. See corrected
`ExtrasResult` skeleton below, which reads `total` via a separate raw-data accessor.

**catalog.extra.getFields** — no params.
Response: `result.extra` = object keyed by field code → `rest_field_description`
(`isImmutable`, `isReadOnly`, `isRequired`, `type`). Same shape/pattern as `FieldsResult`
(`Core\Result\FieldsResult::getFieldsDescription()` just returns `getResult()`, so this maps 1:1;
no custom result class needed — reuse `FieldsResult`, mirroring `Catalog::fields()`).

**catalog.measure.add** — param `fields` (object: `code` int required, `measureTitle` string required,
`isDefault` string Y/N optional, `symbol`/`symbolIntl`/`symbolLetterIntl` string optional).
Response: `result.measure` = full `catalog_measure` object including generated `id`.

**catalog.measure.update** — params `id` (int, required), `fields` (object, same shape as add, all optional).
Response: `result.measure` = updated `catalog_measure` object.

**catalog.measure.get** — param `id` (int, required).
Response: `result.measure` = `catalog_measure` object.

**catalog.measure.list** — params `select` (array, optional), `filter` (object, optional).
Response CONFIRMED live: `result.measures` = array of `catalog_measure` objects, plus `total` as a
top-level sibling of `result` (same pagination pattern as `catalog.extra.list`). The official doc
example text ("result: (array) Массив объектов единиц измерения...") is misleading/outdated —
the actual live response nests items under `result.measures`, not directly under `result`.

**catalog.measure.delete** — param `id` (int, required).
Response: `result` = plain boolean (NOT `result[0]` like the generic v1 `DeletedItemResult`
assumes) — requires a custom result class overriding `isSuccess()`.

**catalog.measure.getFields** — no params.
Response: `result.measure` = object keyed by field code → `rest_field_description`. Reuse `FieldsResult`.

### catalog_measure field set (CONFIRMED live via `catalog.measure.getFields` + `catalog.measure.list`)

`id` (integer, readonly), `code` (integer, required), `measureTitle` (string, marked required by
getFields but observed `null` on real portal data for pre-existing system measures — annotate as
nullable), `isDefault` (char Y/N, optional), `symbol` (string, optional, observed `null` in
practice), `symbolIntl` (string, optional), `symbolLetterIntl` (string, optional).

### catalog_extra field set (CONFIRMED live via `catalog.extra.getFields`)

`id` (integer, readonly), `name` (string, required), `percentage` (double, required).

---

## Files to Create

### 1. `src/Services/Catalog/Enum/Result/RoundTypeItemResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Enum\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int    $id
 * @property-read string $name
 */
class RoundTypeItemResult extends AbstractAnnotatedItem
{
}
```

### 2. `src/Services/Catalog/Enum/Result/RoundTypesResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Enum\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class RoundTypesResult extends AbstractResult
{
    /**
     * @return RoundTypeItemResult[]
     * @throws BaseException
     */
    public function getRoundTypes(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['enum'] as $item) {
            $items[] = new RoundTypeItemResult($item);
        }
        return $items;
    }
}
```

### 3. `src/Services/Catalog/Enum/Result/StoreDocumentTypeItemResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Enum\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read string $id
 * @property-read string $name
 */
class StoreDocumentTypeItemResult extends AbstractAnnotatedItem
{
}
```

### 4. `src/Services/Catalog/Enum/Result/StoreDocumentTypesResult.php`

Same pattern as `RoundTypesResult`, method `getStoreDocumentTypes()`, wraps `StoreDocumentTypeItemResult`.

### 5. `src/Services/Catalog/Enum/Service/CatalogEnum.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Enum\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Enum\Result\RoundTypesResult;
use Bitrix24\SDK\Services\Catalog\Enum\Result\StoreDocumentTypesResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class CatalogEnum extends AbstractService
{
    /**
     * Returns a list of rounding types available in the catalog.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/enum/catalog-enum-get-round-types.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.enum.getRoundTypes',
        'https://apidocs.bitrix24.com/api-reference/catalog/enum/catalog-enum-get-round-types.html',
        'Returns a list of rounding types available in the catalog.'
    )]
    public function getRoundTypes(): RoundTypesResult
    {
        return new RoundTypesResult($this->core->call('catalog.enum.getRoundTypes'));
    }

    /**
     * Returns the types of store accounting documents available for REST.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/enum/catalog-enum-get-store-document-types.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.enum.getStoreDocumentTypes',
        'https://apidocs.bitrix24.com/api-reference/catalog/enum/catalog-enum-get-store-document-types.html',
        'Returns the types of store accounting documents available for REST.'
    )]
    public function getStoreDocumentTypes(): StoreDocumentTypesResult
    {
        return new StoreDocumentTypesResult($this->core->call('catalog.enum.getStoreDocumentTypes'));
    }
}
```

Class is named `CatalogEnum` (not `Enum`) because `Enum` collides with the PHP reserved word
context of `enum` (soft reserved, but avoid ambiguity/tooling confusion — no other service in the
SDK is literally named `Enum`).

### 6. `src/Services/Catalog/Extra/Result/ExtraItemResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Extra\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int    $id
 * @property-read string $name
 * @property-read float  $percentage
 */
class ExtraItemResult extends AbstractAnnotatedItem
{
}
```

### 7. `src/Services/Catalog/Extra/Result/ExtraResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Extra\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ExtraResult extends AbstractResult
{
    /** @throws BaseException */
    public function extra(): ExtraItemResult
    {
        return new ExtraItemResult($this->getCoreResponse()->getResponseData()->getResult()['extra']);
    }
}
```

### 8. `src/Services/Catalog/Extra/Result/ExtrasResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Extra\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ExtrasResult extends AbstractResult
{
    /**
     * @return ExtraItemResult[]
     * @throws BaseException
     */
    public function getExtras(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['extras'] as $item) {
            $items[] = new ExtraItemResult($item);
        }
        return $items;
    }

    /** @throws BaseException */
    public function getTotal(): int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getTotal() ?? 0;
    }
}
```

`total` is parsed by `Core\Response\Response` into `ResponseData::getPagination()->getTotal()`
(a top-level sibling of `result` in the raw HTTP body is stripped out and moved into the
`Pagination` DTO before reaching `AbstractResult`) — CONFIRMED by reading `src/Core/Response/Response.php`
lines ~83-99, and by a live `catalog.extra.list` call showing `{"result":{"extras":[]},"total":0,...}`
at the top level. Do **not** read `getResult()['total']` (some older service classes in this repo,
e.g. `Sale\TradePlatform\TradePlatformsResult`, do this, but it only works if the API nests `total`
inside `result` — `catalog.extra.list` does not, so that pattern must not be copied here).

### 9. `src/Services/Catalog/Extra/Service/Extra.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Extra\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Extra\Result\ExtraResult;
use Bitrix24\SDK\Services\Catalog\Extra\Result\ExtrasResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Extra extends AbstractService
{
    /**
     * Returns information about a markup by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/extra/catalog-extra-get.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.extra.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/extra/catalog-extra-get.html',
        'Returns information about a markup by its identifier.'
    )]
    public function get(int $id): ExtraResult
    {
        $this->guardPositiveId($id);
        return new ExtraResult($this->core->call('catalog.extra.get', ['id' => $id]));
    }

    /**
     * Returns a list of markups matching the given filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/extra/catalog-extra-list.html
     * @param string[] $select
     * @param array<string, mixed> $filter
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.extra.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/extra/catalog-extra-list.html',
        'Returns a list of markups matching the given filter.'
    )]
    public function list(array $select = [], array $filter = []): ExtrasResult
    {
        return new ExtrasResult($this->core->call('catalog.extra.list', [
            'select' => $select,
            'filter' => $filter,
        ]));
    }

    /**
     * Returns the fields for markup in the catalog module.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/extra/catalog-extra-get-fields.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.extra.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/extra/catalog-extra-get-fields.html',
        'Returns the fields for markup in the catalog module.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('catalog.extra.getFields'));
    }
}
```

### 10. `src/Services/Catalog/Measure/Result/MeasureItemResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Measure\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int         $id
 * @property-read int         $code
 * @property-read string|null $measureTitle
 * @property-read bool        $isDefault
 * @property-read string|null $symbol
 * @property-read string|null $symbolIntl
 * @property-read string|null $symbolLetterIntl
 */
class MeasureItemResult extends AbstractAnnotatedItem
{
}
```

`measureTitle` and `symbol` are annotated nullable despite `getFields` marking `measureTitle` as
`isRequired: true` — CONFIRMED live: pre-existing system measures on a real portal return
`"measureTitle":null,"symbol":null` for several records (see raw `catalog.measure.list` response
captured during planning). `isRequired` describes create-time validation, not the shape of stored
data returned by read methods.

### 11. `src/Services/Catalog/Measure/Result/MeasureResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Measure\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class MeasureResult extends AbstractResult
{
    /** @throws BaseException */
    public function measure(): MeasureItemResult
    {
        return new MeasureItemResult($this->getCoreResponse()->getResponseData()->getResult()['measure']);
    }
}
```

### 12. `src/Services/Catalog/Measure/Result/MeasuresResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Measure\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class MeasuresResult extends AbstractResult
{
    /**
     * @return MeasureItemResult[]
     * @throws BaseException
     */
    public function getMeasures(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['measures'] as $item) {
            $items[] = new MeasureItemResult($item);
        }
        return $items;
    }

    /** @throws BaseException */
    public function getTotal(): int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getTotal() ?? 0;
    }
}
```

CONFIRMED live: items are nested under `result.measures`, with `total` as a sibling of `result`
at the top level of the raw HTTP body (identical pagination shape to `catalog.extra.list`).

### 13. `src/Services/Catalog/Measure/Result/AddedMeasureResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Measure\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemResult;

class AddedMeasureResult extends AddedItemResult
{
    /** @throws BaseException */
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['measure']['id'];
    }
}
```

### 14. `src/Services/Catalog/Measure/Result/UpdatedMeasureResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Measure\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;

class UpdatedMeasureResult extends UpdatedItemResult
{
    /** @throws BaseException */
    #[\Override]
    public function isSuccess(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult()['measure'];
    }
}
```

### 15. `src/Services/Catalog/Measure/Result/DeletedMeasureResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Measure\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;

class DeletedMeasureResult extends DeletedItemResult
{
    /** @throws BaseException */
    #[\Override]
    public function isSuccess(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult();
    }
}
```

### 16. `src/Services/Catalog/Measure/Service/Measure.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Measure\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Measure\Result\AddedMeasureResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\DeletedMeasureResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\MeasureResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\MeasuresResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\UpdatedMeasureResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Measure extends AbstractService
{
    /**
     * Creates a new measurement unit in the catalog.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-add.html
     * @param array{
     *   code: int,
     *   measureTitle: string,
     *   isDefault?: string,
     *   symbol?: string,
     *   symbolIntl?: string,
     *   symbolLetterIntl?: string
     * } $fields
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-add.html',
        'Creates a new measurement unit in the catalog.'
    )]
    public function add(array $fields): AddedMeasureResult
    {
        return new AddedMeasureResult($this->core->call('catalog.measure.add', ['fields' => $fields]));
    }

    /**
     * Updates a measurement unit in the catalog.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-update.html
     * @param array{
     *   code?: int,
     *   measureTitle?: string,
     *   isDefault?: string,
     *   symbol?: string,
     *   symbolIntl?: string,
     *   symbolLetterIntl?: string
     * } $fields
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-update.html',
        'Updates a measurement unit in the catalog.'
    )]
    public function update(int $id, array $fields): UpdatedMeasureResult
    {
        $this->guardPositiveId($id);
        return new UpdatedMeasureResult($this->core->call('catalog.measure.update', [
            'id' => $id,
            'fields' => $fields,
        ]));
    }

    /**
     * Returns information about a measurement unit by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-get.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-get.html',
        'Returns information about a measurement unit by its identifier.'
    )]
    public function get(int $id): MeasureResult
    {
        $this->guardPositiveId($id);
        return new MeasureResult($this->core->call('catalog.measure.get', ['id' => $id]));
    }

    /**
     * Returns a list of measurement units from the catalog.
     *
     * Use MeasuresResult::getMeasures() for items and MeasuresResult::getTotal() for the total count.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-list.html
     * @param string[] $select
     * @param array<string, mixed> $filter
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-list.html',
        'Returns a list of measurement units from the catalog.'
    )]
    public function list(array $select = [], array $filter = []): MeasuresResult
    {
        return new MeasuresResult($this->core->call('catalog.measure.list', [
            'select' => $select,
            'filter' => $filter,
        ]));
    }

    /**
     * Deletes a measurement unit from the catalog.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-delete.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-delete.html',
        'Deletes a measurement unit from the catalog.'
    )]
    public function delete(int $id): DeletedMeasureResult
    {
        $this->guardPositiveId($id);
        return new DeletedMeasureResult($this->core->call('catalog.measure.delete', ['id' => $id]));
    }

    /**
     * Returns the available measurement unit fields in the catalog.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-get-fields.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-get-fields.html',
        'Returns the available measurement unit fields in the catalog.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('catalog.measure.getFields'));
    }
}
```

No `Batch.php` for `Measure`/`Extra`/`CatalogEnum`: the issue does not request batch support, and
none of the target methods are documented with a batch-oriented list contract beyond simple
pagination-free array responses, mirroring how `Catalog::fields()`/`Catalog::get()`/`Catalog::list()`
already exist without a dedicated Batch class in this SDK's older-style scopes when not asked for.

### 17. Unit tests

`tests/Unit/Services/Catalog/Enum/Service/CatalogEnumTest.php` — uses `NullCore`, asserts `getRoundTypes()`
and `getStoreDocumentTypes()` return the correct result types without throwing.

`tests/Unit/Services/Catalog/Extra/Service/ExtraTest.php` — uses `NullCore`, asserts `get`, `list`,
`fields` return correct result types.

`tests/Unit/Services/Catalog/Measure/Service/MeasureTest.php` — uses `NullCore`, asserts `add`, `update`,
`get`, `list`, `delete`, `fields` return correct result types, and `guardPositiveId` throws
`InvalidArgumentException` for `get`/`update`/`delete` with id `0`.

### 18. Integration tests

`tests/Integration/Services/Catalog/Enum/Service/CatalogEnumTest.php`
- `testGetRoundTypes` — call real API, assert non-empty array of `RoundTypeItemResult`.
- `testGetStoreDocumentTypes` — call real API, assert non-empty array of `StoreDocumentTypeItemResult`.

`tests/Integration/Services/Catalog/Enum/Result/RoundTypeItemResultAnnotationsTest.php` and
`StoreDocumentTypeItemResultAnnotationsTest.php` — since this scope has no `fields()` method,
emulate the fields-description response per `docs/testing.md` guidance: fetch one raw item via
`getRoundTypes()`/`getStoreDocumentTypes()`, then use `assertBitrix24AllResultItemFieldsAnnotated`
and `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations`.

`tests/Integration/Services/Catalog/Extra/Service/ExtraTest.php`
- `testGetFields` — call `fields()`, assert array contains expected field keys (`id`, `percentage`).
- `testList` — call `list()`, assert `ExtrasResult` with `getExtras()`/`getTotal()`.
- `testGet` — requires at least one existing markup id from `list()`; if the portal has none,
  skip with `markTestSkipped` (markups are typically portal-configured, not creatable via REST —
  confirmed: `catalog.extra` has no `add`/`delete` REST methods in the documented set).

`tests/Integration/Services/Catalog/Extra/Result/ExtraItemResultAnnotationsTest.php`
- Since `catalog.extra` has no add/delete, and may have zero items on a fresh portal, this test
  calls `list()` and uses the first item if present; if the portal has zero markups, skip with
  a clear message (documented limitation — cannot fabricate a markup via REST).

`tests/Integration/Services/Catalog/Measure/Service/MeasureTest.php`
- `testAdd`, `testGet`, `testList`, `testUpdate`, `testDelete`, `testGetFields` — full CRUD lifecycle,
  create in `setUp`/each test, delete in `tearDown` (matching `docs/testing.md` "Integration test
  pattern" convention: clean up entities created during the test).
- Use a random `code` (e.g. from Faker's unique number generator) per test run to avoid
  `200600000000` (duplicate code) collisions across CI runs.
- Always pass `isDefault: 'N'` explicitly, to avoid tripping `200600000010` if a default measure
  already exists on the portal.

`tests/Integration/Services/Catalog/Measure/Result/MeasureItemResultAnnotationsTest.php`
- Create a measure via `add()`, fetch raw item via `get()`, validate annotations + type casts,
  delete in cleanup (mirroring `RegionItemResultAnnotationsTest`'s helper-based structure with a
  try/delete cleanup that must not fail the test if delete errors).

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Add three new factory methods following the exact pattern of `catalog()`/`product()`:

```php
public function catalogEnum(): Catalog\Enum\Service\CatalogEnum
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\Enum\Service\CatalogEnum(
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}

public function extra(): Catalog\Extra\Service\Extra
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\Extra\Service\Extra(
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}

public function measure(): Catalog\Measure\Service\Measure
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\Measure\Service\Measure(
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}
```

Method named `catalogEnum()` (not `enum()`) to avoid using the soft-reserved word `enum` as a
bare method name, consistent with the `CatalogEnum` class naming decision above.

### 2. `phpunit.xml.dist`

Add new testsuites after the existing (unregistered) Catalog area — insert a new block near the
Documentgenerator testsuite group, following the same `scope` / per-service / per-annotation
3-tier pattern used for `documentgenerator_region`:

```xml
<testsuite name="integration_tests_scope_catalog">
    <directory>./tests/Integration/Services/Catalog/</directory>
</testsuite>
<testsuite name="integration_tests_catalog_enum">
    <directory>./tests/Integration/Services/Catalog/Enum/</directory>
</testsuite>
<testsuite name="integration_tests_catalog_extra">
    <directory>./tests/Integration/Services/Catalog/Extra/</directory>
</testsuite>
<testsuite name="integration_tests_catalog_measure">
    <directory>./tests/Integration/Services/Catalog/Measure/</directory>
</testsuite>
```

Note: `integration_tests_scope_catalog` did not previously exist even though `Catalog`/`Product`
tests exist under `tests/Integration/Services/Catalog/` — adding it now also brings the
pre-existing `Catalog`/`Product` integration tests under this umbrella suite as a side effect,
which is correct and desired (they live in the same directory tree).

### 3. `.php-cs-fixer.php`

Add `->in(__DIR__ . '/src/Services/Catalog/')` to the finder chain (currently absent despite
`Catalog` already being registered in `phpstan.neon.dist` and `rector.php`). Insert alphabetically
near the other scope entries, e.g. after `->in(__DIR__ . '/src/Services/Calendar/')`.

### 4. `Makefile`

Add new targets, following the `test-integration-scope-<x>` / `test-integration-<scope>-<service>`
naming convention used throughout the file:

```makefile
.PHONY: test-integration-scope-catalog
test-integration-scope-catalog:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_scope_catalog

.PHONY: test-integration-catalog-enum
test-integration-catalog-enum:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_enum

.PHONY: test-integration-catalog-extra
test-integration-catalog-extra:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_extra

.PHONY: test-integration-catalog-measure
test-integration-catalog-measure:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_measure
```

### 5. `CHANGELOG.md`

Add under `## 3.4.0 – UNRELEASED` → `### Added`, at the top of the list:

```markdown
- Added services `Services\Catalog\Enum\Service\CatalogEnum`, `Services\Catalog\Extra\Service\Extra`
  and `Services\Catalog\Measure\Service\Measure` with support for `catalog.enum.*`, `catalog.extra.*`
  and `catalog.measure.*` methods,
  see [catalog.enum.* methods](https://apidocs.bitrix24.com/api-reference/catalog/enum/index.html),
  [catalog.extra.* methods](https://apidocs.bitrix24.com/api-reference/catalog/extra/index.html) and
  [catalog.measure.* methods](https://apidocs.bitrix24.com/api-reference/catalog/measure/index.html) ([#530](https://github.com/bitrix24/b24phpsdk/issues/530)):
    - `CatalogEnum::getRoundTypes` returns available catalog rounding types
    - `CatalogEnum::getStoreDocumentTypes` returns available store accounting document types
    - `Extra::get` gets information about a markup by its identifier
    - `Extra::list` gets a list of markups by filter
    - `Extra::fields` returns the description of markup fields
    - `Measure::add` creates a new measurement unit
    - `Measure::update` updates an existing measurement unit
    - `Measure::get` gets information about a measurement unit by its identifier
    - `Measure::list` gets the list of measurement units
    - `Measure::delete` deletes a measurement unit
    - `Measure::fields` returns the description of measurement unit fields
```

---

## Deptrac compliance

All new classes live under `src/Services/Catalog/...` (layer: `Services`) and only import from
`Core` (`AbstractAnnotatedItem`, `AbstractResult`, `AddedItemResult`, `UpdatedItemResult`,
`DeletedItemResult`, `FieldsResult`, exceptions) and `Services` (`AbstractService`,
`ApiServiceBuilderMetadata`/`ApiEndpointMetadata`/`ApiServiceMetadata` attributes, `Scope`).
This satisfies the `Services: [Core, Application, Legacy]` rule (Core is a subset of what's
allowed) with no new violations and no `skip_violations` entries needed.

---

## Verification

```bash
docker compose run --rm php-cli vendor/bin/php-cs-fixer fix --dry-run --diff
docker compose run --rm php-cli vendor/bin/rector process --dry-run
docker compose run --rm php-cli vendor/bin/phpstan analyse
docker compose run --rm php-cli vendor/bin/deptrac analyse
make test-unit
make test-integration-scope-catalog
```
