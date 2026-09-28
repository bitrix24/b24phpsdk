# Plan: Add support for catalog.documentcontractor (issue #565)

## Context

Issue: https://github.com/bitrix24/b24phpsdk/issues565
Docs index: https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/index.html

`catalog.documentcontractor.*` binds a CRM contact/company (as the "Поставщик"/vendor category)
to a warehouse accounting document of type "Приход" (receipt, docType `A`). It supports 4 methods,
verified against both the official docs (via Bitrix24 MCP `bitrix-method-details`) and a live
webhook call against the dev portal (`tests/.env.local`):

- `catalog.documentcontractor.add(fields: {documentId, entityTypeId, entityId})` → `{documentContractor: {...}}`
- `catalog.documentcontractor.list(select, filter, order, start)` → `{documentContractor: [...], total, next}`
  **Note**: unlike `DocumentElement`/`PriceTypeGroup`, the list response key is the **singular**
  `documentContractor` even for the array of items (confirmed live: `{"result":{"documentContractor":[]},"total":0,...}`).
  This must NOT be pluralized to `documentContractors` in the result wrapper.
- `catalog.documentcontractor.delete(id)` → `bool`
- `catalog.documentcontractor.getFields()` → `{documentContractor: {field: {isImmutable, isReadOnly, isRequired, type}}}`

Live `getFields` response (confirmed via webhook):
```json
{
  "documentId":    {"isImmutable": false, "isReadOnly": false, "isRequired": true,  "type": "integer"},
  "entityId":      {"isImmutable": false, "isReadOnly": false, "isRequired": true,  "type": "integer"},
  "entityTypeId":  {"isImmutable": false, "isReadOnly": false, "isRequired": true,  "type": "integer"},
  "id":            {"isImmutable": false, "isReadOnly": true,  "isRequired": false, "type": "integer"}
}
```

All 4 fields are `integer` type — no CarbonImmutable/bool/enum casting needed. `entityTypeId` is a
plain CRM ownertype constant (3 = contact, 4 = company) but Catalog services must not import CRM
types (deptrac layer rule: Services must not import from each other), so it stays a plain `int`.

There is no `update` method for this entity (confirmed absent from docs and MCP tool). This makes
`Services\Catalog\PriceTypeGroup` (add/list/delete/getFields, no update, lowercase `id` batch key,
singular accessor method / `Result` class per item) the closest structural sibling — used as the
primary template, cross-checked against `Services\Catalog\DocumentElement` (same layout, has update).

New scope directory: `src/Services/Catalog/DocumentContractor/`. Builder accessor: `documentContractor()`
on `CatalogServiceBuilder`.

Author: © Dmitriy Ignatenko <algonexys@gmail.com> (existing project convention, MIT-LICENSE header).

---

## Files to Create

### 1. `src/Services/Catalog/DocumentContractor/Result/DocumentContractorItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\DocumentContractor\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int $id
 * @property-read int $documentId
 * @property-read int $entityTypeId
 * @property-read int $entityId
 */
class DocumentContractorItemResult extends AbstractAnnotatedItem
{
}
```

### 2. `src/Services/Catalog/DocumentContractor/Result/DocumentContractorResult.php`

Single-item wrapper, accessor `documentContractor(): DocumentContractorItemResult`, reads
`getResult()['documentContractor']` (object, from `add`).

### 3. `src/Services/Catalog/DocumentContractor/Result/DocumentContractorsResult.php`

List wrapper. **Reads the same key `documentContractor`** (singular, but it's an array in the
`list` response) — method name `getDocumentContractors(): DocumentContractorItemResult[]`.

### 4. `src/Services/Catalog/DocumentContractor/Result/DocumentContractorFieldsResult.php`

`getFieldsDescription(): array` reads `getResult()['documentContractor']`.

### 5. `src/Services/Catalog/DocumentContractor/Result/DocumentContractorAddedBatchResult.php`

Follows `PriceTypeGroupAddedBatchResult` pattern — extends `AbstractItem`-based added-batch result,
exposes `documentContractor(): DocumentContractorItemResult`.

### 6. `src/Services/Catalog/DocumentContractor/Batch.php`

Overrides base `Batch`: `determineKeyId()` returns lowercase `'id'`; overrides `deleteEntityItems()`
to register commands with `['id' => $itemId]` (mirrors `PriceTypeGroup\Batch` / `DocumentElement\Batch`
exactly, error messages reworded to "document contractor").

### 7. `src/Services/Catalog/DocumentContractor/Service/DocumentContractor.php`

```php
#[ApiServiceMetadata(new Scope(['catalog']))]
class DocumentContractor extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    // add(array $fields): DocumentContractorResult
    //   -> catalog.documentcontractor.add
    // list(array $select = [], array $filter = [], array $order = [], int $start = 0): DocumentContractorsResult
    //   -> catalog.documentcontractor.list
    // delete(int $id): DeletedItemResult
    //   -> catalog.documentcontractor.delete
    // getFields(): DocumentContractorFieldsResult
    //   -> catalog.documentcontractor.getFields
}
```

Each method carries `#[ApiEndpointMetadata('catalog.documentcontractor.<method>', '<English apidocs URL>', '<description>')]`
and a docblock with method name + doc link, per project convention. English doc links:
- add: https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-add.html
- list: https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-list.html
- delete: https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-delete.html
- getFields: https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-get-fields.html

### 8. `src/Services/Catalog/DocumentContractor/Service/Batch.php`

Mirrors `PriceTypeGroup\Service\Batch`: constructor takes `DocumentContractor\Batch $batch` +
logger; `add(array $documentContractors): Generator<int, DocumentContractorAddedBatchResult>`;
`delete(array $documentContractorId): Generator<int, DeletedItemBatchResult>`. No `update` (method
does not exist in the API).

### 9. `tests/Unit/Services/Catalog/DocumentContractor/Service/DocumentContractorTest.php`

Mirrors `tests/Unit/Services/Catalog/PriceTypeGroup/Service/PriceTypeGroupTest.php`: mock-core
tests for `add`/`list`/`delete`/`getFields` parameter shapes, using `NullLogger` + `createMock(CoreInterface::class)`.

### 10. `tests/Integration/Services/Catalog/DocumentContractor/Service/DocumentContractorTest.php`

Needs a warehouse receipt document (docType `A`) and a CRM contact id to bind. **Verified live
against the dev portal** (webhook in `tests/.env.local`) that this fixture chain works without
conducting the document or creating a store:

1. `catalog.document.add(['docType' => 'A', 'currency' => 'USD', 'responsibleId' => 1, 'title' => ...])`
   → returns a document id immediately usable as `documentId` (confirmed: no store/conduct needed
   just to bind a contractor).
2. `crm.contact.add(['NAME' => ...])` via `Factory::getCore()->call(...)` — Catalog integration
   tests may call CRM REST methods directly through the raw core even though `src/Services/Catalog`
   itself cannot import CRM *service classes* (deptrac restricts `src/`, not `tests/`).
3. Bind via `documentContractorService->add(['documentId' => $documentId, 'entityTypeId' => 3, 'entityId' => $contactId])`
   (entityTypeId 3 = contact, confirmed working live, response `{"documentId":...,"entityId":...,"entityTypeId":3,"id":...}`).

`tearDown()` deletes the binding (if not already deleted by the test), the document
(`catalog.document.delete`), and the contact (`crm.contact.delete`) — mirror the try/catch-ignore
pattern from `DocumentTest::tearDown()` for idempotent cleanup.

Test methods (mirror `PriceTypeGroupTest`):
- `testListDeleteAdd` — list existing binding, delete it, re-add, assert returned fields.
- `testGetFields` — asserts `getFields()->getFieldsDescription()` is an array.

### 11. `tests/Integration/Services/Catalog/DocumentContractor/Service/BatchTest.php`

Mirrors `PriceTypeGroup\Service\BatchTest` — `testDelete` (batch-deletes bindings created in setUp).
`add` batch may be tested too if bindings for multiple contacts/companies are easy to set up; if not,
`testDelete` alone (list → batch delete → assert count) satisfies parity with `PriceTypeGroup`'s
Batch coverage, since batch `add` for this entity is a thin wrapper with no key-mapping quirks (the
mandatory coverage is the batch **delete** lowercase-id override, which is the actual reason a
custom `Batch` subclass exists).

### 12. `tests/Integration/Services/Catalog/DocumentContractor/Result/DocumentContractorItemResultTest.php`

Mandatory annotation/type-cast test per `docs/testing.md` and skill rules. Two methods:
- `testAllFieldsAreAnnotated` — raw keys from `list()`'s core response `['documentContractor'][0]`
  checked against `DocumentContractorItemResult` via `assertBitrix24AllResultItemFieldsAnnotated`.
- `testAllFieldsHasValidTypeCastingInMagicGetters` — via `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations`.

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Add, after `documentElement()`:

```php
public function documentContractor(): Catalog\DocumentContractor\Service\DocumentContractor
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\DocumentContractor\Service\DocumentContractor(
            new Catalog\DocumentContractor\Service\Batch(
                new Catalog\DocumentContractor\Batch($this->core, $this->log),
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

After the `integration_tests_catalog_document_element_annotations` block (~line 586), add:

```xml
<testsuite name="integration_tests_catalog_document_contractor">
    <directory>./tests/Integration/Services/Catalog/DocumentContractor/Service/</directory>
</testsuite>
<testsuite name="integration_tests_catalog_document_contractor_annotations">
    <file>./tests/Integration/Services/Catalog/DocumentContractor/Result/DocumentContractorItemResultTest.php</file>
</testsuite>
```

### 3. `Makefile`

After the `test-integration-catalog-document-element-annotations` target (~line 944), add:

```makefile
.PHONY: test-integration-catalog-document-contractor
test-integration-catalog-document-contractor:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_document_contractor
.PHONY: test-integration-catalog-document-contractor-annotations
test-integration-catalog-document-contractor-annotations:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_document_contractor_annotations
```

### 4. `CHANGELOG.md`

Under `## Unreleased` → `### Added`, insert at the top of the list (above the existing `Services\Catalog\Document` entry):

```markdown
- Added service `Services\Catalog\DocumentContractor` with support methods,
  see [catalog.documentcontractor.* methods](https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/index.html) ([#565](https://github.com/bitrix24/b24phpsdk/issues/565)):
    - `add` binds a CRM contractor (contact or company) to a warehouse accounting receipt document, with batch calls support
    - `list` gets the list of contractor bindings by filter
    - `delete` deletes a contractor binding, with batch calls support
    - `getFields` returns the description of contractor binding fields
```

No edits needed to `.php-cs-fixer.php`, `phpstan.neon.dist`, `rector.php`, or `deptrac.yaml` — all
already glob the whole `src/Services/Catalog/` and `tests/Integration/Services/Catalog/` directories.

---

## Deptrac compliance

`DocumentContractor` lives entirely in the `Services` layer (imports only `Core` + `Services\AbstractService`,
no cross-scope imports). No new deptrac violations expected; `make lint-deptrac` will confirm.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-document-contractor
make test-integration-catalog-document-contractor-annotations
```
