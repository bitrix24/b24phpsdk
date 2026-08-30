# Plan: Add support for catalog.document, catalog.document.element (issue #559)

## Context

Bitrix24 REST API exposes warehouse accounting ("inventory management") document methods under
the `catalog` scope:

- `catalog.document.*` — CRUD + workflow (conduct/cancel) for warehouse documents
  (`https://apidocs.bitrix24.com/api-reference/catalog/document/index.html`)
- `catalog.document.element.*` — CRUD for line items ("document elements") attached to a document
  (`https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/index.html`)

None of these methods currently exist in the SDK. This plan adds two new service directories under
the existing `Catalog` scope: `Document` and `DocumentElement`.

### Generator note (required per skill rules)

`make oa-schema-build` was run successfully against the configured test portal, but the resulting
`docs/open-api/openapi.json` does not contain any `catalog.document*` operations (only
`catalog.item.*`). This means warehouse accounting ("складской учет") is not enabled/available on
the test portal's tariff, so the portal's OpenAPI snapshot has no schema entry for these methods.
Consequently `b24-dev:result-item-generator` and `b24-dev:generate-select-builder` /
`b24-dev:generate-item-builder` cannot be used for this issue — there is no OpenAPI entity to feed
them. Result item classes below are written manually, with `@property-read` annotations derived
directly from the official REST API documentation (`bitrix-method-details` responses, recorded
below), matching the same shape/precision as generator output for comparable existing classes
(e.g. `PriceTypeItemResult`).

### REST methods and response shapes (from `mcp__Bitrix24_REST_API__bitrix-method-details`)

**catalog.document.\* returns `result.document` (single) / `result.documents` (list, assumed
plural key consistent with `result.priceTypes` pattern — to verify against real portal response
during integration testing) with fields:**

| Field | Type | Notes |
|---|---|---|
| id | integer | read-only |
| docType | char | immutable, required. `A`,`S`,`M`,`R`,`D` |
| currency | char | immutable, required, ISO 4217 |
| responsibleId | integer | required |
| siteId | char | default `s1` |
| dateDocument | datetime | nullable |
| dateCreate | datetime | immutable, read-only |
| dateModify | datetime | |
| dateStatus | datetime | read-only, nullable |
| title | string | nullable |
| commentary | char | nullable |
| total | double | nullable |
| docNumber | string | |
| createdBy | integer | immutable |
| modifiedBy | integer | |
| status | char | read-only (`N`,`Y`,`C`) |
| statusBy | integer | nullable |

**catalog.document.element.\* returns `result.documentElement` (single) /
`result.documentElements` (list) with fields:**

| Field | Type | Notes |
|---|---|---|
| id | integer | read-only |
| docId | integer | immutable, required |
| elementId | integer | immutable, required |
| storeFrom | integer | nullable, used for write-off/moving-out docs |
| storeTo | integer | nullable, used for receipt/moving-in docs |
| amount | double | |
| purchasingPrice | double | nullable |

**Methods to implement (Document service):**

| Method | REST method | Request shape | Response |
|---|---|---|---|
| add | `catalog.document.add` | `{fields: {...}}` | `{document}` |
| update | `catalog.document.update` | `{id, fields: {...}}` | `{document}` |
| list | `catalog.document.list` | `{select, filter}` (no `order` param per docs) | `{documents}` (list) |
| delete | `catalog.document.delete` | `{id}` | `bool` |
| deleteList | `catalog.document.deleteList` | `{documentIds: int[]}` | `bool` |
| conduct | `catalog.document.conduct` | `{id}` | `bool` |
| conductList | `catalog.document.conductList` | `{documentIds: int[]}` | `bool` |
| cancel | `catalog.document.cancel` | `{id}` | `bool` |
| cancelList | `catalog.document.cancelList` | `{documentIds: int[]}` | `bool` |
| getFields | `catalog.document.getFields` | `{}` | `{document: {field: descriptor}}` |
| modeStatus | `catalog.document.mode.status` | `{}` | `bool` ('Y'/'N') — placed in Document service since there's no separate "mode" sub-scope elsewhere in the SDK |

**Methods to implement (DocumentElement service):**

| Method | REST method | Request shape | Response |
|---|---|---|---|
| add | `catalog.document.element.add` | `{fields: {...}}` | `{documentElement}` |
| update | `catalog.document.element.update` | `{id, fields: {...}}` | `{documentElement}` |
| list | `catalog.document.element.list` | `{select, filter, order}` | `{documentElements}` (list) |
| delete | `catalog.document.element.delete` | `{id}` | `bool` |
| getFields | `catalog.document.element.getFields` | `{}` | `{documentElement: {field: descriptor}}` |

### Batch scope

`add`/`update`/`delete` for both `Document` and `DocumentElement` follow the standard one-item-per-call
shape and get a `Batch.php` service (same pattern as `Catalog\PriceType\Service\Batch`), backed by a
custom `Core\Batch` subclass (`Catalog\Document\Batch` / `Catalog\DocumentElement\Batch`) because the
REST parameter for the id is lowercase `id` (not `ID`), matching the `PriceType\Batch` precedent.

`list`/`conduct`/`cancel`/`deleteList`/`conductList`/`cancelList`/`getFields`/`modeStatus` are NOT
batch-wrapped: the `*List` variants already accept an array of ids in a single REST call (bulk
semantics baked into the method itself), so they don't fit the SDK's per-item batch generator
pattern and are exposed only as plain service methods.

### Deptrac compliance

New classes live entirely under `src/Services/Catalog/Document/` and
`src/Services/Catalog/DocumentElement/`, importing only from `Core` (via `AbstractService`,
`AbstractAnnotatedItem`, `AbstractResult`, `Core\Batch`) — same dependency shape as existing
`Services` layer code. No new deptrac violations expected.

---

## Files to Create

### Document service

1. `src/Services/Catalog/Document/Result/DocumentItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Document\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int                  $id
 * @property-read string               $docType
 * @property-read string               $currency
 * @property-read int                  $responsibleId
 * @property-read string|null          $siteId
 * @property-read CarbonImmutable|null $dateDocument
 * @property-read CarbonImmutable|null $dateCreate
 * @property-read CarbonImmutable|null $dateModify
 * @property-read CarbonImmutable|null $dateStatus
 * @property-read string|null          $title
 * @property-read string|null          $commentary
 * @property-read float|null           $total
 * @property-read string|null          $docNumber
 * @property-read int|null             $createdBy
 * @property-read int|null             $modifiedBy
 * @property-read string|null          $status
 * @property-read int|null             $statusBy
 */
class DocumentItemResult extends AbstractAnnotatedItem
{
}
```

2. `src/Services/Catalog/Document/Result/DocumentResult.php` — single-item wrapper, `document()`
   method reading `result['document']`, modeled on `PriceTypeResult`.

3. `src/Services/Catalog/Document/Result/DocumentsResult.php` — list wrapper, `getDocuments()`
   reading `result['documents']`, modeled on `PriceTypesResult`.

4. `src/Services/Catalog/Document/Result/DocumentFieldsResult.php` — `getFieldsDescription()`
   reading `result['document']`, modeled on `PriceTypeFieldsResult`.

5. `src/Services/Catalog/Document/Result/DocumentAddedBatchResult.php` — plain class (not extending
   `AbstractResult`) wrapping a raw `ResponseData` with a `document(): DocumentItemResult` accessor
   reading `document` key, byte-for-byte modeled on `PriceTypeAddedBatchResult`.

6. `src/Services/Catalog/Document/Result/DocumentUpdatedBatchResult.php` — same shape, modeled on
   `PriceTypeUpdatedBatchResult`.

7. `src/Services/Catalog/Document/Batch.php` — extends `\Bitrix24\SDK\Core\Batch`, overrides
   `determineKeyId()` to return lowercase `id`, and overrides `deleteEntityItems()` the same way as
   `Catalog\PriceType\Batch` (registers `catalog.document.delete` commands with lowercase `id` key).

8. `src/Services/Catalog/Document/Service/Document.php` — main service:
   - `add(array $fields): DocumentResult`
   - `update(int $id, array $fields): DocumentResult`
   - `list(array $select = [], array $filter = []): DocumentsResult`
   - `delete(int $id): DeletedItemResult`
   - `deleteList(array $documentIds): DeletedItemResult`
   - `conduct(int $id): DeletedItemResult` (reuse `DeletedItemResult` for generic bool-success
     responses, consistent with existing SDK usage for boolean REST results)
   - `conductList(array $documentIds): DeletedItemResult`
   - `cancel(int $id): DeletedItemResult`
   - `cancelList(array $documentIds): DeletedItemResult`
   - `getFields(): DocumentFieldsResult`
   - `modeStatus(): DeletedItemResult` (result is `'Y'`/`'N'`, cast in the same way `DeletedItemResult::isSuccess()` does via `(bool)`)

   Each method carries `#[ApiEndpointMetadata]` with the exact `.html` doc link (English docs
   collected above) and a doc comment. Class carries
   `#[ApiServiceMetadata(new Scope(['catalog']))]`.

9. `src/Services/Catalog/Document/Service/Batch.php` — batch-mode wrapper (`add`, `update`,
   `delete` generators), modeled on `Catalog\PriceType\Service\Batch`.

### DocumentElement service

10. `src/Services/Catalog/DocumentElement/Result/DocumentElementItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\DocumentElement\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int        $id
 * @property-read int        $docId
 * @property-read int        $elementId
 * @property-read int|null   $storeFrom
 * @property-read int|null   $storeTo
 * @property-read float      $amount
 * @property-read float|null $purchasingPrice
 */
class DocumentElementItemResult extends AbstractAnnotatedItem
{
}
```

11. `src/Services/Catalog/DocumentElement/Result/DocumentElementResult.php` — `documentElement()`
    reading `result['documentElement']`.

12. `src/Services/Catalog/DocumentElement/Result/DocumentElementsResult.php` —
    `getDocumentElements()` reading `result['documentElements']`.

13. `src/Services/Catalog/DocumentElement/Result/DocumentElementFieldsResult.php` —
    `getFieldsDescription()` reading `result['documentElement']`.

14. `src/Services/Catalog/DocumentElement/Result/DocumentElementAddedBatchResult.php`

15. `src/Services/Catalog/DocumentElement/Result/DocumentElementUpdatedBatchResult.php`

16. `src/Services/Catalog/DocumentElement/Batch.php` — lowercase-`id` override, same shape as
    `Catalog\Document\Batch`.

17. `src/Services/Catalog/DocumentElement/Service/DocumentElement.php`:
    - `add(array $fields): DocumentElementResult`
    - `update(int $id, array $fields): DocumentElementResult`
    - `list(array $select = [], array $filter = [], array $order = []): DocumentElementsResult`
    - `delete(int $id): DeletedItemResult`
    - `getFields(): DocumentElementFieldsResult`

18. `src/Services/Catalog/DocumentElement/Service/Batch.php` — batch wrapper (`add`, `update`,
    `delete`).

### Tests — Unit

19. `tests/Unit/Services/Catalog/Document/Service/DocumentTest.php` — `NullCore`/`NullBatch`-based
    unit test asserting each method issues the correct REST method name and params (using
    `createMock(CoreInterface::class)` to assert `call()` arguments, per repo convention where
    precise parameter shape matters).

20. `tests/Unit/Services/Catalog/DocumentElement/Service/DocumentElementTest.php` — same pattern.

### Tests — Integration

21. `tests/Integration/Services/Catalog/Document/Service/DocumentTest.php` — full CRUD + conduct/
    cancel/list/deleteList/conductList/cancelList/getFields/modeStatus lifecycle test. Must guard the
    "conducted documents can't be deleted" rule from the docs (cancel before delete in tearDown).

22. `tests/Integration/Services/Catalog/Document/Service/BatchTest.php` — batch add/update/delete.

23. `tests/Integration/Services/Catalog/Document/Result/DocumentItemResultTest.php` — mandatory
    annotation + type-cast test, per skill template, using `document()`/`getCoreResponse()`.

24. `tests/Integration/Services/Catalog/DocumentElement/Service/DocumentElementTest.php` — CRUD +
    list lifecycle, requires a valid `elementId` (existing catalog product) and a document created
    via `Document` service as fixture; created document elements must be cleaned up before the
    parent document (if conducted) — coordinate teardown order.

25. `tests/Integration/Services/Catalog/DocumentElement/Service/BatchTest.php` — batch add/update/
    delete.

26. `tests/Integration/Services/Catalog/DocumentElement/Result/DocumentElementItemResultTest.php` —
    mandatory annotation + type-cast test.

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Add two accessor methods following the `priceType()` pattern:

```php
public function document(): Catalog\Document\Service\Document
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\Document\Service\Document(
            new Catalog\Document\Service\Batch(
                new Catalog\Document\Batch($this->core, $this->log),
                $this->log
            ),
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}

public function documentElement(): Catalog\DocumentElement\Service\DocumentElement
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\DocumentElement\Service\DocumentElement(
            new Catalog\DocumentElement\Service\Batch(
                new Catalog\DocumentElement\Batch($this->core, $this->log),
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

Add after the `integration_tests_catalog_price_type_group` suite block:

```xml
<testsuite name="integration_tests_catalog_document">
    <directory>./tests/Integration/Services/Catalog/Document/Service/</directory>
</testsuite>
<testsuite name="integration_tests_catalog_document_annotations">
    <file>./tests/Integration/Services/Catalog/Document/Result/DocumentItemResultTest.php</file>
</testsuite>
<testsuite name="integration_tests_catalog_document_element">
    <directory>./tests/Integration/Services/Catalog/DocumentElement/Service/</directory>
</testsuite>
<testsuite name="integration_tests_catalog_document_element_annotations">
    <file>./tests/Integration/Services/Catalog/DocumentElement/Result/DocumentElementItemResultTest.php</file>
</testsuite>
```

### 3. `Makefile`

Add after the `test-integration-catalog-price-type-group` target:

```makefile
.PHONY: test-integration-catalog-document
test-integration-catalog-document:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_document

.PHONY: test-integration-catalog-document-annotations
test-integration-catalog-document-annotations:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_document_annotations

.PHONY: test-integration-catalog-document-element
test-integration-catalog-document-element:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_document_element

.PHONY: test-integration-catalog-document-element-annotations
test-integration-catalog-document-element-annotations:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_document_element_annotations
```

Also add the two new target names to the `help`-style echo list near the top of the Makefile
(matching the existing convention around line 76).

### 4. `docs/testing.md`

Already updated (by linter/user) with a "Tests — integration (Catalog)" table row for
`make test-integration-catalog-price*`. Add rows for the new `catalog-document` /
`catalog-document-element` targets in that same table, matching its existing format.

### 5. `CHANGELOG.md`

Add under `## X.Y.Z Unreleased` → `### Added` (top of the list):

```markdown
- Added service `Services\Catalog\Document` with support methods,
  see [catalog.document.* methods](https://apidocs.bitrix24.com/api-reference/catalog/document/index.html):
    - `add` creates a new warehouse accounting document, with batch calls support
    - `update` updates an existing document, with batch calls support
    - `list` gets the list of documents
    - `delete` deletes a document, with batch calls support
    - `deleteList` deletes a group of documents
    - `conduct` conducts (activates) a document
    - `conductList` conducts a group of documents
    - `cancel` cancels conducting of a document
    - `cancelList` cancels conducting of a group of documents
    - `getFields` returns the description of document fields
    - `modeStatus` checks whether warehouse accounting mode is enabled
- Added service `Services\Catalog\DocumentElement` with support methods,
  see [catalog.document.element.* methods](https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/index.html):
    - `add` adds a product line item to a warehouse accounting document, with batch calls support
    - `update` updates a document line item, with batch calls support
    - `list` gets the list of document line items
    - `delete` deletes a document line item, with batch calls support
    - `getFields` returns the description of document element fields
```

Each bullet block ends with an issue link: `([#559](https://github.com/bitrix24/b24phpsdk/issues/559))`.

---

## Registration in linters (per CLAUDE.md instructions)

Verified: `.php-cs-fixer.php` (lines 15, 37) and `rector.php` (lines 23-24) already scan
`src/Services/Catalog/` and `tests/Integration/Services/Catalog` as whole-directory entries (not
per-sub-scope), and `phpstan.neon.dist` (line 10) already includes
`tests/Integration/Services/Catalog` in its analysed paths. No changes needed to any of the three
linter configs — new `Document`/`DocumentElement` sub-directories are picked up automatically.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-document
make test-integration-catalog-document-annotations
make test-integration-catalog-document-element
make test-integration-catalog-document-element-annotations
```
