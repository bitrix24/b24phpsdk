# Plan: Add support for catalog.price.*, catalog.priceType.*, catalog.priceTypeLang.*, catalog.priceTypeGroup.* (issue #536)

## Context

Issue: https://github.com/bitrix24/b24phpsdk/issues/536
Author of implementation: © Dmitriy Ignatenko <algonexys@gmail.com>
Docs root: https://apidocs.bitrix24.com/api-reference/catalog/price/index.html and
https://apidocs.bitrix24.com/api-reference/catalog/price-type/index.html

This adds four new sub-scopes under the existing `Bitrix24\SDK\Services\Catalog` namespace:

1. `catalog.price.*` — product price CRUD (`add`, `update`, `modify`, `getFields`, `list`, `get`, `delete`)
2. `catalog.priceType.*` — price type CRUD (`add`, `update`, `get`, `list`, `delete`, `getFields`)
3. `catalog.priceTypeLang.*` — price type name translations (`add`, `update`, `get`, `list`, `delete`, `getLanguages`, `getFields`)
4. `catalog.priceTypeGroup.*` — price type ↔ purchasing group bindings (`add`, `list`, `delete`, `getFields` — **no `update`/`get`**, confirmed against both the issue body and live MCP method docs)

### API version and envelope shape

All 24 methods are **classic flat REST (v1-style)** endpoints: parameters are passed directly
(`fields`, `id`, `select`/`filter`/`order`), not the v3 structured envelope. `catalog.price*` /
`catalog.priceType*` do **not** appear in `docs/open-api/openapi.json` after a fresh
`b24-dev:build-schema` run (v3 schema only lists REST v3 typed endpoints) — the mandatory
generators (`b24-dev:result-item-generator`, `*SelectBuilder`, `*ItemBuilder`) rely on this
schema and **cannot be used** for this issue. Falling back to manual implementation, matching
the existing hand-written scopes `Services\CRM\Currency` and `Services\Catalog\Product` as the
closest analogues, but using `AbstractAnnotatedItem` (not the legacy `AbstractItem`/`AbstractCrmItem`
hand-rolled `__get()` pattern) per current SDK convention (see `Services\HumanResources\Result\NodeItemResult`).

### Response envelopes (confirmed via MCP `bitrix-method-details` against live docs)

| Method | Root result key(s) |
|---|---|
| `catalog.price.add` | `result.price` (object) |
| `catalog.price.update` | `result.price` (object) |
| `catalog.price.modify` | `result.prices` (array) |
| `catalog.price.get` | `result.price` (object) |
| `catalog.price.list` | `result.prices` (array), `total` |
| `catalog.price.delete` | `result` (bool) |
| `catalog.price.getFields` | `result.price` (map field→descriptor) |
| `catalog.priceType.add` / `.update` / `.get` | `result.priceType` (object) |
| `catalog.priceType.list` | `result.priceTypes` (array), `total` |
| `catalog.priceType.delete` | `result` (bool) |
| `catalog.priceType.getFields` | `result.priceType` (map field→descriptor) |
| `catalog.priceTypeLang.add` / `.update` / `.get` | `result.priceTypeLang` (object) |
| `catalog.priceTypeLang.list` | `result.priceTypeLangs` (array), `total` |
| `catalog.priceTypeLang.delete` | `result` (bool) |
| `catalog.priceTypeLang.getLanguages` | `result.languages` (array), `total` |
| `catalog.priceTypeLang.getFields` | `result.priceTypeLang` (map field→descriptor) |
| `catalog.priceTypeGroup.add` | `result.priceTypeGroup` (object) |
| `catalog.priceTypeGroup.list` | `result.priceTypeGroups` (array), `total` |
| `catalog.priceTypeGroup.delete` | `result` (bool) |
| `catalog.priceTypeGroup.getFields` | `result.priceTypeGroup` (map field→descriptor) |

None of these use the generic `result.item` / `result.items` envelope, so the `ResultExtractor`
trait (`Services\HumanResources\Result\ResultExtractor`) does not apply — each `*Result` class
reads its named key directly from `getCoreResponse()->getResponseData()->getResult()`, following
the `Services\CRM\Currency\Result\CurrenciesResult` pattern.

### Field shape and casting notes

- `catalog_price` fields: `id`, `productId`, `catalogGroupId`, `price` (float), `currency` (string),
  `extraId` (int, nullable), `quantityFrom`/`quantityTo` (int, nullable), `timestampX` (datetime,
  nullable → `CarbonImmutable`), `priceScale` (float, present only in `modify` response — treat as
  nullable since `get`/`list`/`add` responses omit it).
- `catalog_price_type` fields: `id`, `name`, `base` (`Y`/`N` → bool), `sort` (int), `xmlId`
  (string, nullable), `createdBy`/`modifiedBy` (int), `dateCreate`/`timestampX` (datetime →
  `CarbonImmutable`).
- `catalog_price_type_lang` fields: `id`, `catalogGroupId` (int), `lang` (string), `name` (string).
- `catalog_price_type_group` fields: `id`, `catalogGroupId` (int), `groupId` (int), `access`
  (`Y`/`N` → bool).
- `catalog_language` fields (from `getLanguages`): `lid` (string), `name` (string), `active`
  (`Y`/`N` → bool).

All boolean-like `Y`/`N` fields are annotated `bool` — `AbstractAnnotatedItem::castValue()`
already maps `Y`/`N`/`1`/`0` to bool automatically, no manual casting needed.

### Batch-parameter key mismatch (custom Batch classes required)

The generic `Core\Batch::deleteEntityItems()` hardcodes parameter key `'ID'` (uppercase) and
`Core\Batch::updateEntityItems()` sends `'id'` (lowercase) + `'fields'` — this happens to match
`catalog.price.update` / `catalog.priceType.update` / `catalog.priceTypeLang.update` (all expect
lowercase `id` + `fields`), but the generic delete's uppercase `'ID'` does **not** match
`catalog.price.delete` / `catalog.priceType.delete` / `catalog.priceTypeLang.delete` /
`catalog.priceTypeGroup.delete`, which all expect lowercase `id`. Following the
`Services\CRM\Currency\Batch` precedent (child class overriding delete with a scope-specific
method), each of the four services gets a custom `Batch` class extending `Core\Batch` with a
scope-specific delete override (`deletePriceItems`, `deletePriceTypeItems`,
`deletePriceTypeLangItems`, `deletePriceTypeGroupItems`) using `'id'` lowercase. The generic
`updateEntityItems()` already matches for `price`/`priceType`/`priceTypeLang` update, so no
override needed there. `priceTypeGroup` has no `update`, so its Batch only needs add/delete/list.

### Directory layout

```
src/Services/Catalog/
├── Price/
│   ├── Batch.php                          (custom, delete key override)
│   ├── Result/
│   │   ├── PriceItemResult.php
│   │   ├── PriceResult.php                (single price envelope: add/update/get)
│   │   └── PricesResult.php                (list/modify envelope: prices[] + total)
│   └── Service/
│       ├── Price.php
│       └── Batch.php                       (batch wrapper, mirrors CRM\Currency\Service\Batch)
├── PriceType/
│   ├── Batch.php
│   ├── Result/
│   │   ├── PriceTypeItemResult.php
│   │   ├── PriceTypeResult.php
│   │   └── PriceTypesResult.php
│   └── Service/
│       ├── PriceType.php
│       └── Batch.php
├── PriceTypeLang/
│   ├── Batch.php
│   ├── Result/
│   │   ├── PriceTypeLangItemResult.php
│   │   ├── PriceTypeLangResult.php
│   │   ├── PriceTypeLangsResult.php
│   │   ├── LanguageItemResult.php
│   │   └── LanguagesResult.php
│   └── Service/
│       ├── PriceTypeLang.php
│       └── Batch.php
└── PriceTypeGroup/
    ├── Batch.php
    ├── Result/
    │   ├── PriceTypeGroupItemResult.php
    │   ├── PriceTypeGroupResult.php
    │   └── PriceTypeGroupsResult.php
    └── Service/
        ├── PriceTypeGroup.php
        └── Batch.php
```

`getFields` results reuse a small scope-local `*FieldsResult` per sub-scope (cannot reuse
`Core\Result\FieldsResult` because the descriptor map is nested under a named key, not at result
root) — modeled after `Services\HumanResources\NodeField\Result\NodeFieldsResult::getFieldsDescription()`.
Named `PriceFieldsResult`, `PriceTypeFieldsResult`, `PriceTypeLangFieldsResult`,
`PriceTypeGroupFieldsResult`, placed in each sub-scope's `Result/` directory.

---

## Files to Create

### `src/Services/Catalog/Price/Result/PriceItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Price\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int                  $id
 * @property-read int                  $productId
 * @property-read int                  $catalogGroupId
 * @property-read float                $price
 * @property-read string               $currency
 * @property-read int|null             $extraId
 * @property-read int|null             $quantityFrom
 * @property-read int|null             $quantityTo
 * @property-read float|null           $priceScale
 * @property-read CarbonImmutable|null $timestampX
 */
class PriceItemResult extends AbstractAnnotatedItem
{
}
```

### `src/Services/Catalog/Price/Result/PriceResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Price\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class PriceResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function price(): PriceItemResult
    {
        return new PriceItemResult($this->getCoreResponse()->getResponseData()->getResult()['price']);
    }
}
```

### `src/Services/Catalog/Price/Result/PricesResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Price\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class PricesResult extends AbstractResult
{
    /**
     * @return PriceItemResult[]
     * @throws BaseException
     */
    public function getPrices(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        $items = $result['prices'] ?? [];

        return array_map(static fn(array $item): PriceItemResult => new PriceItemResult($item), $items);
    }
}
```

Notes:
- Used for both `catalog.price.list` (`result.prices` + `total`) and `catalog.price.modify`
  (`result.prices`, no `total`) — service methods construct the same result class from either
  call since both share the `prices[]` shape.

### `src/Services/Catalog/Price/Result/PriceFieldsResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Price\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class PriceFieldsResult extends AbstractResult
{
    /**
     * @return array<string, array<string, mixed>>
     * @throws BaseException
     */
    public function getFieldsDescription(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['price'];
    }
}
```

### `src/Services/Catalog/Price/Batch.php`

Custom batch core, delete key override (mirrors `Services\CRM\Currency\Batch`):

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Price;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * @param int[] $entityItemId
     *
     * @return Generator<int, ResponseData>
     * @throws BaseException
     */
    public function deletePriceItems(string $apiMethod, array $entityItemId): Generator
    {
        $this->logger->debug('deletePriceItems.start', ['apiMethod' => $apiMethod, 'entityItems' => $entityItemId]);

        try {
            $this->clearCommands();
            foreach ($entityItemId as $cnt => $id) {
                if (!is_int($id)) {
                    throw new InvalidArgumentException(
                        sprintf('invalid type «%s» of price id at position %s, id must be integer type', gettype($id), $cnt)
                    );
                }

                $this->registerCommand($apiMethod, ['id' => $id]);
            }

            foreach ($this->getTraversable(true) as $cnt => $deletedItemResult) {
                yield $cnt => $deletedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new BaseException(sprintf('batch delete price items: %s', $exception->getMessage()), $exception->getCode(), $exception);
        }

        $this->logger->debug('deletePriceItems.finish');
    }
}
```

`getTraversable()` is `protected` on `Core\Batch`, so this subclass can call it directly.

### `src/Services/Catalog/Price/Service/Price.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Price\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Price\Result\PriceFieldsResult;
use Bitrix24\SDK\Services\Catalog\Price\Result\PriceResult;
use Bitrix24\SDK\Services\Catalog\Price\Result\PricesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Price extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    #[ApiEndpointMetadata(
        'catalog.price.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-add.html',
        'Adds a new product price'
    )]
    public function add(array $fields): PriceResult
    {
        return new PriceResult($this->core->call('catalog.price.add', ['fields' => $fields]));
    }

    #[ApiEndpointMetadata(
        'catalog.price.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-update.html',
        'Updates the price of a product by its identifier'
    )]
    public function update(int $id, array $fields): PriceResult
    {
        return new PriceResult($this->core->call('catalog.price.update', ['id' => $id, 'fields' => $fields]));
    }

    #[ApiEndpointMetadata(
        'catalog.price.modify',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-modify.html',
        'Adds, updates and deletes a product price collection in a single request'
    )]
    public function modify(int $productId, array $prices): PricesResult
    {
        return new PricesResult(
            $this->core->call('catalog.price.modify', ['fields' => ['product' => ['id' => $productId, 'prices' => $prices]]])
        );
    }

    #[ApiEndpointMetadata(
        'catalog.price.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-get-fields.html',
        'Returns the fields of a product price'
    )]
    public function getFields(): PriceFieldsResult
    {
        return new PriceFieldsResult($this->core->call('catalog.price.getFields'));
    }

    #[ApiEndpointMetadata(
        'catalog.price.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-list.html',
        'Returns a list of product prices by filter'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): PricesResult
    {
        return new PricesResult(
            $this->core->call('catalog.price.list', ['select' => $select, 'filter' => $filter, 'order' => $order])
        );
    }

    #[ApiEndpointMetadata(
        'catalog.price.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-get.html',
        'Returns product price information by identifier'
    )]
    public function get(int $id): PriceResult
    {
        return new PriceResult($this->core->call('catalog.price.get', ['id' => $id]));
    }

    #[ApiEndpointMetadata(
        'catalog.price.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-delete.html',
        'Deletes a product price by identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.price.delete', ['id' => $id]));
    }
}
```

`DeletedItemResult::isSuccess()` reads `getResult()[0]` — confirmed reusable as-is (see
"Resolved design questions" section below): `Response::getResponseData()` normalizes a bare
`"result": true` payload into `[0 => true]` before `DeletedItemResult` ever sees it. No
scope-local delete result class needed for any of the four sub-scopes.

### `src/Services/Catalog/Price/Service/Batch.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Price\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\Price;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected Price\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * @param array<int, array> $prices
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.price.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-add.html',
        'Batch adding product prices'
    )]
    public function add(array $prices): Generator
    {
        $items = [];
        foreach ($prices as $price) {
            $items[] = ['fields' => $price];
        }

        foreach ($this->batch->addEntityItems('catalog.price.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * @param int[] $priceId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.price.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-delete.html',
        'Batch delete product prices'
    )]
    public function delete(array $priceId): Generator
    {
        foreach ($this->batch->deletePriceItems('catalog.price.delete', $priceId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * @param array<int, array> $prices keyed by price id
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.price.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-update.html',
        'Batch update product prices'
    )]
    public function update(array $prices): Generator
    {
        $items = [];
        foreach ($prices as $id => $price) {
            $items[$id] = ['fields' => $price];
        }

        foreach ($this->batch->updateEntityItems('catalog.price.update', $items) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }
}
```

`add()`/`update()` yield scope-local `PriceAddedBatchResult` / `PriceUpdatedBatchResult` (see
"Resolved design questions" below), not the generic `AddedItemBatchResult`/`UpdatedItemBatchResult`
— those assume a bare scalar at `getResult()[0]`, but price add/update batch responses are
`{"price": {...}}` objects. `delete()` keeps the generic `DeletedItemBatchResult` (bare bool
response, confirmed compatible).

### `src/Services/Catalog/PriceType/...`

Same structural pattern as `Price/`, adapted:

- `Result/PriceTypeItemResult.php`:
  ```php
  /**
   * @property-read int                  $id
   * @property-read string               $name
   * @property-read bool                 $base
   * @property-read int                  $sort
   * @property-read string|null          $xmlId
   * @property-read int|null             $createdBy
   * @property-read int|null             $modifiedBy
   * @property-read CarbonImmutable|null $dateCreate
   * @property-read CarbonImmutable|null $timestampX
   */
  class PriceTypeItemResult extends AbstractAnnotatedItem {}
  ```
- `Result/PriceTypeResult.php` — `priceType(): PriceTypeItemResult` reading `result['priceType']`.
- `Result/PriceTypesResult.php` — `getPriceTypes(): array` reading `result['priceTypes']`.
- `Result/PriceTypeFieldsResult.php` — reads `result['priceType']` as descriptor map.
- `Batch.php` — `deletePriceTypeItems()`, same shape as `Price\Batch::deletePriceItems()`.
- `Service/PriceType.php` — methods `add(array $fields)`, `update(int $id, array $fields)`,
  `get(int $id)`, `list(array $select = [], array $filter = [], array $order = [])`,
  `delete(int $id)`, `getFields()`. All `#[ApiEndpointMetadata]` doc links point to
  `https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-*.html`.
- `Service/Batch.php` — add/delete/update batch wrapper, mirrors `Price\Service\Batch`.

### `src/Services/Catalog/PriceTypeLang/...`

- `Result/PriceTypeLangItemResult.php`:
  ```php
  /**
   * @property-read int    $id
   * @property-read int    $catalogGroupId
   * @property-read string $lang
   * @property-read string $name
   */
  class PriceTypeLangItemResult extends AbstractAnnotatedItem {}
  ```
- `Result/PriceTypeLangResult.php` — `priceTypeLang(): PriceTypeLangItemResult` reading
  `result['priceTypeLang']`.
- `Result/PriceTypeLangsResult.php` — `getPriceTypeLangs(): array` reading
  `result['priceTypeLangs']`.
- `Result/PriceTypeLangFieldsResult.php` — reads `result['priceTypeLang']` descriptor map.
- `Result/LanguageItemResult.php`:
  ```php
  /**
   * @property-read string $lid
   * @property-read string $name
   * @property-read bool   $active
   */
  class LanguageItemResult extends AbstractAnnotatedItem {}
  ```
- `Result/LanguagesResult.php` — `getLanguages(): array` reading `result['languages']`.
- `Batch.php` — `deletePriceTypeLangItems()`.
- `Service/PriceTypeLang.php` — methods `add(array $fields)`, `update(int $id, array $fields)`,
  `get(int $id)`, `list(array $select = [], array $filter = [])` (no `order` param — confirmed
  absent from `catalog.priceTypeLang.list` docs), `delete(int $id)`, `getLanguages()`,
  `getFields()`.
- `Service/Batch.php` — add/delete/update batch wrapper.

### `src/Services/Catalog/PriceTypeGroup/...`

- `Result/PriceTypeGroupItemResult.php`:
  ```php
  /**
   * @property-read int  $id
   * @property-read int  $catalogGroupId
   * @property-read int  $groupId
   * @property-read bool $access
   */
  class PriceTypeGroupItemResult extends AbstractAnnotatedItem {}
  ```
- `Result/PriceTypeGroupResult.php` — `priceTypeGroup(): PriceTypeGroupItemResult` reading
  `result['priceTypeGroup']`.
- `Result/PriceTypeGroupsResult.php` — `getPriceTypeGroups(): array` reading
  `result['priceTypeGroups']`.
- `Result/PriceTypeGroupFieldsResult.php` — reads `result['priceTypeGroup']` descriptor map.
- `Batch.php` — `deletePriceTypeGroupItems()`.
- `Service/PriceTypeGroup.php` — methods `add(array $fields)`,
  `list(array $select = [], array $filter = [], array $order = [], int $start = 0)`,
  `delete(string $id)` (docs type the `id` param as `string` for this method — confirm during
  TDD; if the live API also accepts int, keep the signature matching documented type `string`),
  `getFields()`. **No `update` or `get`** — not present in the issue or in live method docs.
- `Service/Batch.php` — add/delete batch wrapper only (no update).

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Add four new accessor methods after the existing `catalog()` method, following the existing
`product()`/`catalog()` construction pattern:

```php
public function price(): Catalog\Price\Service\Price
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\Price\Service\Price(
            new Catalog\Price\Service\Batch(new Catalog\Price\Batch($this->core, $this->log), $this->log),
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}

public function priceType(): Catalog\PriceType\Service\PriceType
{
    // same shape, Catalog\PriceType\*
}

public function priceTypeLang(): Catalog\PriceTypeLang\Service\PriceTypeLang
{
    // same shape, Catalog\PriceTypeLang\*
}

public function priceTypeGroup(): Catalog\PriceTypeGroup\Service\PriceTypeGroup
{
    // same shape, Catalog\PriceTypeGroup\*
}
```

Fixed pattern (settled, no ambiguity): each `<SubScope>\Service\Batch` constructor takes the
**scope-level** custom `<SubScope>\Batch` class (not the generic `BatchOperationsInterface`),
because all four sub-scopes need the lowercase-`id` delete override from their own `Batch.php`
(`Price\Batch`, `PriceType\Batch`, `PriceTypeLang\Batch`, `PriceTypeGroup\Batch` — all four,
including `PriceTypeGroup`, since its delete also expects lowercase `id`). This mirrors
`Services\CRM\Currency\Service\Batch`, which takes `Currency\Batch $batch` (the CRM Currency
scope's own custom `Batch` class), not `BatchOperationsInterface`. `CatalogServiceBuilder`
constructs the two layers explicitly, as shown above: `new <SubScope>\Service\Batch(new
<SubScope>\Batch($this->core, $this->log), $this->log)`.

No changes to top-level `src/Services/ServiceBuilder.php` — `CatalogServiceBuilder` is already
registered via `getCatalogScope()`.

### 2. `.php-cs-fixer.php`

Add to the `Finder`:
```php
    ->in(__DIR__ . '/src/Services/Catalog/')
```
(`src/Services/Catalog/` is not currently covered — `Product`/`Catalog` sub-scopes are also
missing style enforcement today; adding the parent directory covers the new Price* code plus
brings existing Catalog code under the same finder, which is in scope for this issue since new
files must pass `lint-cs-fixer`.)

### 3. `phpstan.neon.dist`

Already covers `src/` (wildcard) and `tests/Integration/Services/Catalog` (already listed at
line 10) — no change needed. Confirm new integration test files land under
`tests/Integration/Services/Catalog/` so they're picked up automatically.

### 4. `rector.php`

Already lists `src/Services/Catalog` and `tests/Integration/Services/Catalog` (lines 23–24) — no
change needed.

### 5. `deptrac.yaml`

No change expected — new code lives entirely in `Services` layer, importing only `Core`
(`AbstractAnnotatedItem`, `AbstractResult`, exceptions) and `Services\AbstractService` /
`Services\AbstractServiceBuilder`, consistent with existing `Services` → `Core` dependency rule.
Run `make lint-deptrac` to confirm zero new violations; do not pre-emptively add
`skip_violations`.

### 6. `phpunit.xml.dist`

Add four new `<testsuite>` entries (naming convention matches existing Catalog-adjacent suites,
e.g. `test-integration-scope-crm`):

```xml
<testsuite name="test-integration-catalog-price">
    <directory>tests/Integration/Services/Catalog/Price</directory>
</testsuite>
<testsuite name="test-integration-catalog-price-type">
    <directory>tests/Integration/Services/Catalog/PriceType</directory>
</testsuite>
<testsuite name="test-integration-catalog-price-type-lang">
    <directory>tests/Integration/Services/Catalog/PriceTypeLang</directory>
</testsuite>
<testsuite name="test-integration-catalog-price-type-group">
    <directory>tests/Integration/Services/Catalog/PriceTypeGroup</directory>
</testsuite>
```

Read the actual current `<testsuites>` block first to match exact indentation/attribute style
before inserting.

### 7. `Makefile`

Add four targets (verify the existing `test-integration-scope-*` target syntax before copying):

```makefile
test-integration-catalog-price:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite test-integration-catalog-price

test-integration-catalog-price-type:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite test-integration-catalog-price-type

test-integration-catalog-price-type-lang:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite test-integration-catalog-price-type-lang

test-integration-catalog-price-type-group:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite test-integration-catalog-price-type-group
```

### 8. `docs/testing.md`

Add a new `### Tests — integration (Catalog)` table (matching the existing per-scope table
style, e.g. `### Tests — integration (Sale)`) listing the four new targets:

```markdown
### Tests — integration (Catalog)

| Target | Suite |
|---|---|
| `make test-integration-catalog-price` | Price |
| `make test-integration-catalog-price-type` | Price type |
| `make test-integration-catalog-price-type-lang` | Price type language translations |
| `make test-integration-catalog-price-type-group` | Price type ↔ purchasing group bindings |
```

### 9. `CHANGELOG.md`

Add one entry under `## 3.4.0 – UNRELEASED` → `### Added`, at the top of the `### Added` list
(per user instruction: "в начало списка, под последнюю дату"):

```markdown
- Added services `Services\Catalog\Price\Service\Price`, `Services\Catalog\PriceType\Service\PriceType`,
  `Services\Catalog\PriceTypeLang\Service\PriceTypeLang`, `Services\Catalog\PriceTypeGroup\Service\PriceTypeGroup`
  with support methods, see [catalog.price.* methods](https://apidocs.bitrix24.com/api-reference/catalog/price/index.html)
  and [catalog.priceType.* methods](https://apidocs.bitrix24.com/api-reference/catalog/price-type/index.html)
  ([#536](https://github.com/bitrix24/b24phpsdk/issues/536)):
    - `Price::add` / `update` / `modify` / `get` / `list` / `delete` / `getFields`, with batch calls support
    - `PriceType::add` / `update` / `get` / `list` / `delete` / `getFields`, with batch calls support
    - `PriceTypeLang::add` / `update` / `get` / `list` / `delete` / `getLanguages` / `getFields`, with batch calls support
    - `PriceTypeGroup::add` / `list` / `delete` / `getFields`, with batch calls support
```

---

## Test files to create

Per `docs/testing.md` and skill conventions, for each of the 4 sub-scopes:

- `tests/Unit/Services/Catalog/Price/Service/PriceTest.php` (and PriceType/PriceTypeLang/PriceTypeGroup)
  — use `NullCore`/`NullBatch`, assert correct REST method name and parameter shape passed to
  `core->call()` per method (mock `CoreInterface` where param assertions are needed — `NullCore`
  alone can't assert call arguments, use `createMock(CoreInterface::class)` with
  `expects($this->once())->method('call')->with(...)` for at least one representative test per
  method, per TDD RED-GREEN cycle).
- `tests/Integration/Services/Catalog/Price/Service/PriceTest.php` (CRUD lifecycle against live
  portal: add → get → list → update → delete, with `tearDown()` cleanup)
- `tests/Integration/Services/Catalog/Price/Service/BatchTest.php`
- `tests/Integration/Services/Catalog/Price/Result/PriceItemResultTest.php` — mandatory
  annotation/type-cast test per skill rule, using `assertBitrix24AllResultItemFieldsAnnotated` +
  `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations`.
- Same four files repeated for `PriceType`, `PriceTypeLang` (plus a `LanguageItemResultTest.php`
  for `getLanguages()`), `PriceTypeGroup` (no separate CRUD-lifecycle `update`/`get` steps since
  those methods don't exist — lifecycle test is add → list → delete).

### CustomAssertions

If `assertBitrix24AllResultItemFieldsAnnotated` reports a mismatch for any field (e.g. real API
returns a field not in the documented list, or a documented field is absent from a real
response), add the missing `@property-read` to the corresponding `*ItemResult` — do not weaken
the assertion. If `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations` reports a mismatch
against a type not yet handled by `AbstractAnnotatedItem::castValue()`, extend
`tests/CustomAssertions/CustomBitrix24Assertions.php`'s type-matching table (per skill
instructions) rather than special-casing in the item class.

---

## Deptrac compliance

All new files live in `Services\Catalog\{Price,PriceType,PriceTypeLang,PriceTypeGroup}` and
import only from `Core` (`AbstractAnnotatedItem`, `AbstractResult`, `AbstractItem` indirectly,
exceptions, `Batch` base class) and `Services\AbstractService` / `AbstractServiceBuilder`. This
matches the existing `Services → Core` rule with no new dependency edges. Run `make
lint-deptrac` after implementation to confirm zero new violations.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-price
make test-integration-catalog-price-type
make test-integration-catalog-price-type-lang
make test-integration-catalog-price-type-group
```

## Resolved design questions (confirmed by reading `Core\Response\Response::getResponseData()`)

`Response::getResponseData()` (src/Core/Response/Response.php:70-79) normalizes any non-array
`result` payload into `[0 => <scalar>]` before constructing `ResponseData`. This means:

1. **Delete is reusable as-is.** `catalog.price.delete` / `catalog.priceType.delete` /
   `catalog.priceTypeLang.delete` / `catalog.priceTypeGroup.delete` all return a bare
   `"result": true`, which the framework normalizes to `[0 => true]`. `Core\Result\DeletedItemResult`
   (single call) and `Core\Result\DeletedItemBatchResult` (batch) both read `getResult()[0]` and
   work correctly without modification. **No scope-local delete result classes needed** — use the
   generic `DeletedItemResult` / `DeletedItemBatchResult` directly in all four services, same as
   `Services\Catalog\Product\Service\Product::delete()`.

2. **Add/update batch results are NOT reusable — need scope-local classes.** `catalog.price.add`
   and `catalog.price.update` (and the priceType/priceTypeLang/priceTypeGroup equivalents) return
   a named-key object (`{"price": {...}}`), which stays a string-keyed array — it is *not*
   renumbered to `[0 => ...]` because it's already an array. `Core\Result\AddedItemBatchResult` /
   `UpdatedItemBatchResult` read `getResult()[0]`, which does not exist on a `['price' => [...]]`
   array (PHP returns `null`, silently wrong — not even an exception). Each of the four sub-scopes
   therefore needs scope-local batch item-result classes:
   - `Price\Result\PriceAddedBatchResult` / `PriceUpdatedBatchResult` — wrap `ResponseData`,
     expose `price(): PriceItemResult` reading `getResult()['price']` (mirrors the single-call
     `PriceResult::price()` shape but takes a `ResponseData`, not a `Response`, as constructor
     arg — follow `Core\Result\AddedItemBatchResult`'s constructor shape:
     `__construct(private readonly ResponseData $responseData)`).
   - Same pattern: `PriceType\Result\PriceTypeAddedBatchResult` / `PriceTypeUpdatedBatchResult`,
     `PriceTypeLang\Result\PriceTypeLangAddedBatchResult` / `PriceTypeLangUpdatedBatchResult`,
     `PriceTypeGroup\Result\PriceTypeGroupAddedBatchResult` (add-only, no update method exists).
   - `Service\Batch::add()` / `::update()` in each sub-scope yield these scope-local classes
     instead of the generic `AddedItemBatchResult`/`UpdatedItemBatchResult`.
   - `Service\Batch::delete()` in each sub-scope keeps using the generic
     `Core\Result\DeletedItemBatchResult` per point 1.

This resolution applies uniformly to all four sub-scopes — implement the scope-local
added/updated batch result classes as part of each `Result/` directory in the "Files to Create"
section above (adjust that section's `Batch.php`/`Service/Batch.php` skeletons to yield the
scope-local classes for add/update, generic `DeletedItemBatchResult` for delete).
