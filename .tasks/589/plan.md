# Plan: Add support for catalog.userfield.document.* methods (issue #589)

## Context

Bitrix24 REST API exposes two methods for reading/writing custom (userfield) values attached to
warehouse accounting documents (`catalog.document.*`):

- `catalog.userfield.document.list` — https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-list.html
- `catalog.userfield.document.update` — https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-update.html

### Method details (fetched via Bitrix24 REST API MCP)

**`catalog.userfield.document.list`**
- Params: `select` (array, **required** — must include `documentType`), `filter` (object, **required** —
  must include `documentType`; keys may use `>=`/`>` prefixes), `order`, `start`.
- Response: `result.documents` (array of dynamic objects), `next` (offset for next page, present only
  if more records exist), `total` (omitted when `start=-1`).
- Each item in `documents[]` always contains `documentId` (int) and `documentType` (string), plus zero
  or more dynamic `fieldN` keys (N = userfield numeric ID, e.g. `field7097`) — the userfield set differs
  per portal, so these keys cannot be statically enumerated.
- Errors include "documentType field is not specified in select/filter parameter".

**`catalog.userfield.document.update`**
- Params: `documentId` (int, required), `fields` (object, required) containing `documentType` (string)
  and one or more `fieldN` (mixed) values to set.
- Response: `result.document` — single object with `documentId`, `documentType`, and the updated dynamic
  `fieldN` keys.
- Single-document only (no batch/list variant of update in the docs).

### Generator usage — explicitly skipped

Per the b24phpsdk-maintainer skill, `make oa-schema-build` was run successfully and
`docs/open-api/openapi.json` was rebuilt (220 paths, snapshot sourced from the portal's registered
local-app REST methods). `catalog.userfield.document.list` / `.update` are **not present** in that
snapshot (confirmed by running `php bin/console b24-dev:result-item-generator
catalog.userfield.document.list --stage=all`, which fails with "REST docs payload is required ... but
the documentation URL could not be resolved"). Therefore the `*ItemResult.php` generator, the
`*SelectBuilder.php` generator, and the `*ItemBuilder.php` generator cannot be used for this issue.
All SDK files are written manually, following the existing `Catalog\Document` /
`Catalog\DocumentElement` scope pattern (same parent scope, same author, same coding style).

### Design decision — dynamic userfield keys (confirmed with user)

`UserfieldDocumentItemResult` extends `AbstractAnnotatedItem` and annotates **only** the two fixed
system keys: `documentId` (int) and `documentType` (string). Dynamic `fieldN` keys remain accessible
through the inherited magic `__get()` (from `AbstractItem`) without annotation/type-casting — this is
consistent with how `AbstractAnnotatedItem::__get()` falls back to the raw value when no annotation
type is found for an offset.

The mandatory annotation integration test (`UserfieldDocumentItemResultTest`) normalizes the raw API
response by keeping only the fixed system keys (`documentId`, `documentType`) before calling
`assertBitrix24AllResultItemFieldsAnnotated()`, per the "normalize the field keys" guidance in
`docs/testing.md`. A separate assertion in the same test confirms a dynamically-added userfield value
is readable via magic getter (not covered by the shared annotation assertion, since it's inherently
dynamic).

### Batch support

`update` takes a single `documentId` + `fields` object — same one-at-a-time shape as
`catalog.document.update`. A batch wrapper is added for consistency with the rest of the SDK
(`Catalog\Document\Service\Batch`, `Catalog\DocumentElement\Service\Batch`), using a custom
`Catalog\UserfieldDocument\Batch` extending the core `Batch` class because the id key is `documentId`,
not the base class's default `ID`.

`list` does not need a batch wrapper (batch is for id-keyed add/update/delete operations, not for
paginated reads). `UserfieldDocument::list()` mirrors `Document::list()`'s signature shape
(`select`, `filter`) plus the two extra `list`-specific parameters this method supports (`order`,
`start`) for manual pagination. Unlike `DocumentsResult` (which has no `next`/`total` in its API
response), `UserfieldDocumentsResult` exposes `getNext(): ?int` and `getTotal(): ?int`, backed by the
existing `ResponseData::getPagination()` mechanism.

---

## Files to Create

### 1. `src/Services/Catalog/UserfieldDocument/Result/UserfieldDocumentItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int    $documentId
 * @property-read string $documentType
 */
class UserfieldDocumentItemResult extends AbstractAnnotatedItem
{
}
```

### 2. `src/Services/Catalog/UserfieldDocument/Result/UserfieldDocumentResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class UserfieldDocumentResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function document(): UserfieldDocumentItemResult
    {
        return new UserfieldDocumentItemResult($this->getCoreResponse()->getResponseData()->getResult()['document']);
    }
}
```

### 3. `src/Services/Catalog/UserfieldDocument/Result/UserfieldDocumentsResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class UserfieldDocumentsResult extends AbstractResult
{
    /**
     * @return UserfieldDocumentItemResult[]
     * @throws BaseException
     */
    public function getDocuments(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return array_map(
            static fn (array $item): UserfieldDocumentItemResult => new UserfieldDocumentItemResult($item),
            $result['documents'] ?? []
        );
    }

    /**
     * @throws BaseException
     */
    public function getNext(): ?int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getNextItem();
    }

    /**
     * @throws BaseException
     */
    public function getTotal(): ?int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
```

Confirmed: `ResponseData::getPagination()` → `Pagination::getNextItem()` / `getTotal()` is the existing
SDK-wide mechanism (used internally by `Core\Batch`), reused here directly — no hand-rolled parsing.

### 4. `src/Services/Catalog/UserfieldDocument/Batch.php`

**Resolved during implementation:** overriding `determineKeyId()` alone is insufficient —
`Core\Batch::updateEntityItems()` hardcodes the command argument key to `'id'` (it does not consult
`determineKeyId()` at all; that hook is only used by other codepaths such as `deleteEntityItems()`).
Confirmed via a live batch-update integration test failure: `"could not find value for parameter
{documentid}"`. The fix is to override `updateEntityItems()` itself, building the command with
`'documentId'` instead of `'id'` (mirrors the base class's structure/exception handling, adapted for
the single supported case: `catalog.userfield.document.update`).

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\UserfieldDocument;

/**
 * Class Batch
 *
 * Overrides base Batch to handle parameter naming differences in catalog.userfield.document.* REST methods:
 * - update uses 'documentId' instead of 'ID'
 *
 * @see https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-update.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    #[\Override]
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
    {
        return 'documentId';
    }
}
```

### 5. `src/Services/Catalog/UserfieldDocument/Service/UserfieldDocument.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\UserfieldDocument\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result\UserfieldDocumentResult;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result\UserfieldDocumentsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class UserfieldDocument extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Returns a paginated list of userfield values for warehouse accounting documents.
     * The «documentType» key is required in both $select and $filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.userfield.document.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-list.html',
        'Returns a paginated list of userfield values for warehouse accounting documents'
    )]
    public function list(array $select, array $filter, array $order = [], int $start = 0): UserfieldDocumentsResult
    {
        return new UserfieldDocumentsResult(
            $this->core->call(
                'catalog.userfield.document.list',
                ['select' => $select, 'filter' => $filter, 'order' => $order, 'start' => $start]
            )
        );
    }

    /**
     * Updates userfield values of a warehouse accounting document.
     * $fields must contain «documentType» plus the fieldN values to update.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.userfield.document.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-update.html',
        'Updates userfield values of a warehouse accounting document'
    )]
    public function update(int $documentId, array $fields): UserfieldDocumentResult
    {
        return new UserfieldDocumentResult(
            $this->core->call('catalog.userfield.document.update', ['documentId' => $documentId, 'fields' => $fields])
        );
    }
}
```

### 6. `src/Services/Catalog/UserfieldDocument/Service/Batch.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\UserfieldDocument\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result\UserfieldDocumentUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected UserfieldDocument\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch update userfield values of warehouse accounting documents
     *
     * @param array<int, array> $documents keyed by document id, each value is the «fields» payload
     *
     * @return Generator<int, UserfieldDocumentUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.userfield.document.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-update.html',
        'Batch update userfield values of warehouse accounting documents'
    )]
    public function update(array $documents): Generator
    {
        $items = [];
        foreach ($documents as $id => $document) {
            $items[$id] = ['fields' => $document];
        }

        foreach ($this->batch->updateEntityItems('catalog.userfield.document.update', $items) as $key => $item) {
            yield $key => new UserfieldDocumentUpdatedBatchResult($item);
        }
    }
}
```

### 7. `src/Services/Catalog/UserfieldDocument/Result/UserfieldDocumentUpdatedBatchResult.php`

Mirrors `Catalog\Document\Result\DocumentUpdatedBatchResult` — check that file's exact shape (it wraps
`UpdatedItemBatchResult` or similar core class) before writing an equivalent for userfield documents.

### 8. `tests/Unit/Services/Catalog/UserfieldDocument/Service/UserfieldDocumentTest.php`

Unit tests mirroring `tests/Unit/Services/Catalog/Document/Service/DocumentTest.php` pattern
(`mockCore()` + `makeService()` helpers): assert `list()` and `update()` build the correct REST
parameters and return the correct Result type. No HTTP calls (uses `createMock(CoreInterface::class)`).

### 9. `tests/Integration/Services/Catalog/UserfieldDocument/Service/UserfieldDocumentTest.php`

**Resolved during implementation:** app-mode credentials (`Factory::getServiceBuilder(true)` /
`Factory::getCore(true)`) work correctly against the test portal (token auto-refreshes). The correct
`entityId` for warehouse accounting document userfields is `CAT_STORE_DOCUMENT_<documentType>` (e.g.
`CAT_STORE_DOCUMENT_A`), and `fieldName` must be prefixed `UF_<entityId>_`. Both were verified live via
`userfieldconfig.add` / `catalog.userfield.document.update` / `.list` round-trips before writing the
tests. This is implemented in the shared fixture
`tests/Builders/Services/Catalog/UserfieldDocument/CatalogDocumentUserfieldFixture.php`
(`getOrCreateFieldCode()`), used by all three integration test files in this scope — it discovers an
existing userfield for `CAT_STORE_DOCUMENT_A` via `userfieldconfig.list`, or creates one via
`userfieldconfig.add` if none exists yet (left in place for reuse across test runs, not deleted in
tearDown).

Test plan:
- `setUp()`: `Factory::getServiceBuilder(true)->getCatalogScope()->userfieldDocument()`, plus
  `document()` service to create a real document to attach userfield values to; `userfieldCode` is
  resolved via `CatalogDocumentUserfieldFixture::getOrCreateFieldCode(Factory::getCore(true))`.
- Test `update()` sets the discovered `fieldN` value on a created document and asserts the returned
  `UserfieldDocumentItemResult` exposes it via magic getter.
- Test `list()` with `select`/`filter` containing `documentType` returns the document with the
  expected `fieldN` value.

### 10. `tests/Integration/Services/Catalog/UserfieldDocument/Result/UserfieldDocumentItemResultTest.php`

Same app-mode fixture as file 9 above (`CatalogDocumentUserfieldFixture::getOrCreateFieldCode()`).

Per the mandatory annotation-test rule in `docs/testing.md`. Two test methods:
- `testAllFieldsAreAnnotated`: fetch a raw item from `list()`, **filter the raw keys down to
  `['documentId', 'documentType']`** (normalization required — dynamic `fieldN` keys are portal-specific
  and cannot be part of the static PHPDoc contract), then call
  `assertBitrix24AllResultItemFieldsAnnotated()`.
- `testAllFieldsHasValidTypeCastingInMagicGetters`: call
  `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations()` on a real `UserfieldDocumentItemResult`.
- A third, non-template test method `testDynamicUserfieldValueIsAccessible` (not part of the mandatory
  pair, but required to prove the design decision works): asserts that a dynamic `fieldN` set via
  `update()` is readable as `$item->fieldN` even though it's unannotated.

### 11. `tests/Integration/Services/Catalog/UserfieldDocument/Service/BatchTest.php`

Batch update test mirroring `tests/Integration/Services/Catalog/Document/Service/BatchTest.php`
(create 2+ documents, batch-update their userfield values, assert results).

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Add, after `documentElement()` (~line 292):

```php
    public function userfieldDocument(): Catalog\UserfieldDocument\Service\UserfieldDocument
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\UserfieldDocument\Service\UserfieldDocument(
                new Catalog\UserfieldDocument\Service\Batch(
                    new Catalog\UserfieldDocument\Batch($this->core, $this->log),
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

Confirmed convention from `Catalog\Document` (lines 575-580 of current file): one suite covering the
whole `Service/` directory (includes both the plain service test and `BatchTest.php`), plus one suite
for the annotations test. Add, after the `integration_tests_catalog_document_element_annotations` block
(~line 586):

```xml
        <testsuite name="integration_tests_catalog_userfield_document">
            <directory>./tests/Integration/Services/Catalog/UserfieldDocument/Service/</directory>
        </testsuite>
        <testsuite name="integration_tests_catalog_userfield_document_annotations">
            <file>./tests/Integration/Services/Catalog/UserfieldDocument/Result/UserfieldDocumentItemResultTest.php</file>
        </testsuite>
```

### 3. `Makefile`

Add, mirroring the exact `test-integration-catalog-document` / `-annotations` target pair (~line 932):

```makefile
.PHONY: test-integration-catalog-userfield-document
test-integration-catalog-userfield-document:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_userfield_document
.PHONY: test-integration-catalog-userfield-document-annotations
test-integration-catalog-userfield-document-annotations:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_userfield_document_annotations
```

### 4. `.php-cs-fixer.php`, `phpstan.neon.dist`, `rector.php`

Confirmed — no change needed. All three already cover the whole `Catalog` scope wholesale:
- `.php-cs-fixer.php:15` → `->in(__DIR__ . '/src/Services/Catalog/')`
- `phpstan.neon.dist:10` → `tests/Integration/Services/Catalog` (and `src/` is covered elsewhere at the
  top-level `src` scan)
- `rector.php:23-24` → `src/Services/Catalog` and `tests/Integration/Services/Catalog`

`UserfieldDocument` is a subdirectory of `Catalog`, so it's picked up automatically.

### 5. `CHANGELOG.md`

Add under the latest `## X.Y.Z Unreleased` version, at the top of the relevant section:

```markdown
- Added service `Services\Catalog\UserfieldDocument` with support methods,
  see [catalog.userfield.document.* methods](https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/index.html):
    - `list` gets a paginated list of userfield values for warehouse accounting documents, with batch calls support for update
    - `update` updates userfield values of a warehouse accounting document, with batch calls support
```

(Exact wording/issue link finalized during implementation once the actual CHANGELOG top section is
read.)

---

## Deptrac compliance

`Services\Catalog\UserfieldDocument\*` only imports from `Core` (via `AbstractService`,
`AbstractAnnotatedItem`, `AbstractResult`, `Core\Batch`, exceptions) and `Services\AbstractService` /
`Services\AbstractServiceBuilder` — same dependency shape as the sibling `Document` /
`DocumentElement` scopes, which already pass deptrac. No new violations expected; no `skip_violations`
entry needed.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-userfield-document
```
