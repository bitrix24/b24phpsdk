# Plan: Add `catalog.vat.*` methods support (issue #590)

## Context

Bitrix24 REST API exposes VAT rate management methods under `catalog.vat.*`:
- `catalog.vat.add`
- `catalog.vat.update`
- `catalog.vat.get`
- `catalog.vat.list`
- `catalog.vat.delete`
- `catalog.vat.getFields`

Verified against a live test portal (webhook `tests/.env.local`) and MCP `bitrix-method-details`:

- `add` / `update` / `get` return `result.vat` (singular key) — a `catalog_vat` object.
- `list` returns `result.vats` (plural key) + `result.total`.
- `delete` returns a plain boolean `result`.
- `getFields` returns `result.vat` — a map of field-name => field descriptor (same shape as
  `catalog.priceType.getFields`).

Confirmed live field set for a `catalog_vat` item (matches `getFields` output exactly):

| Field | Type | Required | ReadOnly |
|---|---|---|---|
| `id` | integer | no | yes |
| `name` | string | yes | no |
| `active` | char (`Y`/`N`) | no | no |
| `rate` | double | yes | no |
| `sort` | integer | no | no |
| `timestampX` | datetime | no | no |

This scope is structurally identical to the existing `Services\Catalog\PriceType` scope
(same id/name/sort/timestampX shape, lowercase `id` REST parameter key, `add`/`update`/`get`/
`list`/`delete`/`getFields` method set). `PriceType` is used as the direct template for file
layout, naming, and the custom `Batch` override (lowercase `id` key for delete/list, differing
from the SDK's base `Batch` default of uppercase `ID`).

No prior `Vat` implementation exists in `src/Services/Catalog/` or `tests/` — confirmed via
grep before starting.

Author: © Dmitriy Ignatenko <algonexys@gmail.com>
Issue: https://github.com/bitrix24/b24phpsdk/issues/590

---

## Files to Create

### 1. `src/Services/Catalog/Vat/Result/VatItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Vat\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int                  $id
 * @property-read string               $name
 * @property-read bool                 $active
 * @property-read float                $rate
 * @property-read int                  $sort
 * @property-read CarbonImmutable|null $timestampX
 */
class VatItemResult extends AbstractAnnotatedItem
{
}
```

### 2. `src/Services/Catalog/Vat/Result/VatResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Vat\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class VatResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function vat(): VatItemResult
    {
        return new VatItemResult($this->getCoreResponse()->getResponseData()->getResult()['vat']);
    }
}
```

### 3. `src/Services/Catalog/Vat/Result/VatsResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Vat\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class VatsResult extends AbstractResult
{
    /**
     * @return VatItemResult[]
     * @throws BaseException
     */
    public function getVats(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return array_map(
            static fn (array $item): VatItemResult => new VatItemResult($item),
            $result['vats'] ?? []
        );
    }
}
```

### 4. `src/Services/Catalog/Vat/Result/VatFieldsResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Vat\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class VatFieldsResult extends AbstractResult
{
    /**
     * @return array<string, array<string, mixed>>
     * @throws BaseException
     */
    public function getFieldsDescription(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['vat'];
    }
}
```

### 5. `src/Services/Catalog/Vat/Result/VatAddedBatchResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Vat\Result;

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

class VatAddedBatchResult
{
    public function __construct(private readonly ResponseData $responseData)
    {
    }

    public function getResponseData(): ResponseData
    {
        return $this->responseData;
    }

    public function vat(): VatItemResult
    {
        return new VatItemResult($this->responseData->getResult()['vat']);
    }
}
```

### 6. `src/Services/Catalog/Vat/Result/VatUpdatedBatchResult.php`

Same shape as `VatAddedBatchResult`, class name `VatUpdatedBatchResult`.

### 7. `src/Services/Catalog/Vat/Batch.php`

Overrides base `\Bitrix24\SDK\Core\Batch` — lowercase `id` key for `determineKeyId()` and
`deleteEntityItems()`, following `src/Services/Catalog/PriceType/Batch.php` exactly (rename
`price type` → `VAT rate` in log/error messages).

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Vat;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle parameter naming differences in catalog.vat.* REST methods:
 * - delete uses lowercase 'id' instead of 'ID'
 *
 * @see https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-delete.html
 * @see https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-list.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    #[\Override]
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
    {
        return 'id';
    }

    /**
     * @param int[]             $entityItemId
     * @param array<mixed>|null $additionalParameters
     *
     * @return Generator<int, ResponseData>|ResponseData[]
     * @throws BaseException
     */
    #[\Override]
    public function deleteEntityItems(
        string $apiMethod,
        array $entityItemId,
        ?array $additionalParameters = null
    ): Generator {
        // body mirrors PriceType\Batch::deleteEntityItems, "price type" -> "VAT rate" wording
    }
}
```

### 8. `src/Services/Catalog/Vat/Service/Vat.php`

Main single-item service. Methods: `add`, `update`, `get`, `list`, `delete`, `getFields`,
mirroring `src/Services/Catalog/PriceType/Service/PriceType.php` 1:1, with `#[ApiEndpointMetadata]`
doc links replaced by:

- https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-add.html
- https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-update.html
- https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-get.html
- https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-list.html
- https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-delete.html
- https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-get-fields.html

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Vat\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Vat\Result\VatFieldsResult;
use Bitrix24\SDK\Services\Catalog\Vat\Result\VatResult;
use Bitrix24\SDK\Services\Catalog\Vat\Result\VatsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Vat extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    public function add(array $fields): VatResult { /* catalog.vat.add */ }

    public function update(int $id, array $fields): VatResult { /* catalog.vat.update */ }

    public function get(int $id): VatResult { /* catalog.vat.get */ }

    public function list(array $select = [], array $filter = [], array $order = []): VatsResult { /* catalog.vat.list */ }

    public function delete(int $id): DeletedItemResult { /* catalog.vat.delete */ }

    public function getFields(): VatFieldsResult { /* catalog.vat.getFields */ }
}
```

### 9. `src/Services/Catalog/Vat/Service/Batch.php`

Batch-mode wrapper, mirroring `src/Services/Catalog/PriceType/Service/Batch.php`: `add`,
`update`, `delete` generators using `Vat\Batch`, yielding `VatAddedBatchResult` /
`VatUpdatedBatchResult` / `DeletedItemBatchResult`.

### 10. `tests/Unit/Services/Catalog/Vat/Service/VatTest.php`

Unit test using `NullCore`/`NullBatch`, following the repo's standard unit test pattern
(`docs/testing.md`) — construct `Vat` service with `NullCore` and assert each method returns
the correct Result type without throwing.

### 11. `tests/Integration/Services/Catalog/Vat/Service/VatTest.php`

Mirrors `tests/Integration/Services/Catalog/PriceType/Service/PriceTypeTest.php`:
- `testAddGetDelete` — add, assert `name`/`rate`, get, delete
- `testUpdate` — add, update `name`, assert, delete
- `testList` — add, list filtered by `id`, assert count 1, delete
- `testGetFields` — assert `getFieldsDescription()` is an array

### 12. `tests/Integration/Services/Catalog/Vat/Service/BatchTest.php`

Mirrors `tests/Integration/Services/Catalog/PriceType/Service/BatchTest.php`:
`testAddUpdateDelete` using `$vatService->batch->add/update/delete`.

### 13. `tests/Integration/Services/Catalog/Vat/Result/VatItemResultTest.php`

Mandatory annotation/type-cast test (per skill rules), mirrors
`tests/Integration/Services/Catalog/PriceType/Result/PriceTypeItemResultTest.php`:
- `setUp()` adds a VAT rate, `tearDown()` deletes it
- `testAllFieldsAreAnnotated`
- `testAllFieldsHasValidTypeCastingInMagicGetters`

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Add a `vat()` accessor method (alphabetically placed is not required by convention — append
after `documentElement()`, consistent with existing append-at-end pattern):

```php
public function vat(): Catalog\Vat\Service\Vat
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\Vat\Service\Vat(
            new Catalog\Vat\Service\Batch(
                new Catalog\Vat\Batch($this->core, $this->log),
                $this->log
            ),
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}
```

### 2. `phpunit.xml.dist`

Add after the `integration_tests_catalog_document_element` (or appropriate alphabetical/logical
position near other Catalog suites) testsuite block:

```xml
<testsuite name="integration_tests_catalog_vat">
    <directory>./tests/Integration/Services/Catalog/Vat/</directory>
</testsuite>
```

### 3. `Makefile`

Add near other `test-integration-catalog-*` targets:

```makefile
.PHONY: test-integration-catalog-vat
test-integration-catalog-vat:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_vat
```

### 4. `CHANGELOG.md`

Add under `## Unreleased` (currently empty) a new `### Added` section:

```markdown
### Added

- Added service `Services\Catalog\Vat\Service\Vat` with support methods,
  see [catalog.vat.* methods](https://apidocs.bitrix24.com/api-reference/catalog/vat/index.html) ([#590](https://github.com/bitrix24/b24phpsdk/issues/590)):
    - `add` creates a new VAT rate, with batch calls support
    - `update` updates an existing VAT rate by its identifier, with batch calls support
    - `get` returns VAT rate information by identifier
    - `list` returns a list of VAT rates by filter
    - `delete` deletes a VAT rate by identifier, with batch calls support
    - `getFields` returns the description of VAT rate fields
```

### 5. `docs/testing.md`

Add a row/target reference for `make test-integration-catalog-vat` under the existing Catalog
table (matches the pattern documented for `catalog-price`, `catalog-price-type`, etc.).

---

## Deptrac compliance

New code lives entirely in `Services\Catalog\Vat\*` and imports only from `Core` (via
`AbstractAnnotatedItem`, `AbstractResult`, `AbstractService`, `Batch`, exceptions) and
`Services\AbstractService` / `Services\AbstractServiceBuilder` — same dependency shape as the
existing `PriceType` scope. No new deptrac violations expected; no `skip_violations` entry
needed.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-vat
```
