# Plan: Add support for documentgenerator.document.* methods (issue #489)

## Context

This issue adds SDK support for the `documentgenerator.document.*` REST API methods — the
**non-CRM** Document Generator scope. Unlike `crm.documentgenerator.document.*`, these methods
work with any data provider, not just CRM entities.

Key differences from the CRM scope (`src/Services/CRM/Documentgenerator/Document/`):

| Aspect | CRM scope | documentgenerator scope |
|---|---|---|
| Method prefix | `crm.documentgenerator.document.` | `documentgenerator.document.` |
| `add` params | `templateId`, `entityTypeId`, `entityId` | `templateId`, `providerClassName`, `value` |
| `update` extra params | `values`, `stampsEnabled` | `values`, `fields`, `stampsEnabled` |
| SDK scope | `['crm']` | `['documentgenerator']` |
| Builder access | `getCRMScope()->documentgeneratorDocument()` | `getDocumentgeneratorScope()->document()` |

Response shape for `list`: `result.documents[]` (same key as CRM).
Response shape for `get/add`: `result.document{}` (same key as CRM).

Custom `Batch` class is required because the API uses lowercase `id` for delete/update and
wraps list results under the `documents` key (same as CRM version).

---

## Files Created

### Source files
1. `src/Services/Documentgenerator/Document/Result/DocumentItemResult.php` — item result with field type casting
2. `src/Services/Documentgenerator/Document/Result/DocumentResult.php` — single document result
3. `src/Services/Documentgenerator/Document/Result/DocumentsResult.php` — list of documents result
4. `src/Services/Documentgenerator/Document/Result/AddedDocumentResult.php` — add result
5. `src/Services/Documentgenerator/Document/Result/AddedDocumentBatchResult.php` — batch add result
6. `src/Services/Documentgenerator/Document/Result/DeletedDocumentResult.php` — delete result
7. `src/Services/Documentgenerator/Document/Result/DeletedDocumentBatchResult.php` — batch delete result
8. `src/Services/Documentgenerator/Document/Result/UpdatedDocumentResult.php` — update result
9. `src/Services/Documentgenerator/Document/Result/UpdatedDocumentBatchResult.php` — batch update result
10. `src/Services/Documentgenerator/Document/Result/DocumentFieldsResult.php` — getFields result
11. `src/Services/Documentgenerator/Document/Result/PublicUrlResult.php` — enablePublicUrl result
12. `src/Services/Documentgenerator/Document/Batch.php` — custom Batch override (lowercase id, documents wrapper)
13. `src/Services/Documentgenerator/Document/Service/Batch.php` — service-level batch wrapper
14. `src/Services/Documentgenerator/Document/Service/Document.php` — main service class
15. `src/Services/Documentgenerator/DocumentgeneratorServiceBuilder.php` — scope builder

### Test files
16. `tests/Integration/Services/Documentgenerator/Document/Service/DocumentTest.php`
17. `tests/Integration/Services/Documentgenerator/Document/Service/BatchTest.php`

---

## Files Modified

### 1. `src/Services/ServiceBuilder.php`
- Added `use Bitrix24\SDK\Services\Documentgenerator\DocumentgeneratorServiceBuilder;`
- Added `getDocumentgeneratorScope(): DocumentgeneratorServiceBuilder` method

### 2. `rector.php`
- Added paths for `src/Services/Documentgenerator` and `tests/Integration/Services/Documentgenerator`

### 3. `phpunit.xml.dist`
- Added `integration_tests_scope_documentgenerator` and `integration_tests_documentgenerator_document` test suites

### 4. `Makefile`
- Added `integration_tests_scope_documentgenerator` and `integration_tests_documentgenerator_document` targets

### 5. `CHANGELOG.md`
- Added entry under `## 3.3.0 – UNRELEASED → ### Added`

---

## Deptrac compliance

New code lives in `src/Services/Documentgenerator/` which belongs to the `Services` layer.
It depends only on `Core` (AbstractItem, AbstractResult, AddedItemResult, etc.). No new violations.

---

## Verification

```bash
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make integration_tests_documentgenerator_document
```

