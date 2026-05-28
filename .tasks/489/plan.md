# Plan: Add support for documentgenerator.document.* and documentgenerator.template.* methods (issue #489)

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
make integration_tests_documentgenerator_template
make integration_tests_documentgenerator_template_annotations
```

---

## Phase 2: documentgenerator.template.* (added 2026-05-26)

Template methods are implemented in `src/Services/Documentgenerator/Template/`.

Key differences from CRM variant:
- `getFields` requires only `id` (no `entityTypeId`)
- `add` supports `code` and `fileId` fields
- `update` supports `providers` in fields
- List response: `result.templates` keyed by id
- Single-item response: `result.template`
- Template fields response: `result.templateFields`

### Files Created (Phase 2)

1. `src/Services/Documentgenerator/Template/Result/TemplateItemResult.php`
2. `src/Services/Documentgenerator/Template/Result/TemplateResult.php`
3. `src/Services/Documentgenerator/Template/Result/TemplatesResult.php`
4. `src/Services/Documentgenerator/Template/Result/AddedTemplateResult.php`
5. `src/Services/Documentgenerator/Template/Result/UpdatedTemplateResult.php`
6. `src/Services/Documentgenerator/Template/Result/DeletedTemplateResult.php`
7. `src/Services/Documentgenerator/Template/Result/AddedTemplateBatchResult.php`
8. `src/Services/Documentgenerator/Template/Result/UpdatedTemplateBatchResult.php`
9. `src/Services/Documentgenerator/Template/Result/DeletedTemplateBatchResult.php`
10. `src/Services/Documentgenerator/Template/Result/TemplateFieldsResult.php`
11. `src/Services/Documentgenerator/Template/Batch.php`
12. `src/Services/Documentgenerator/Template/Service/Batch.php`
13. `src/Services/Documentgenerator/Template/Service/Template.php`
14. `tests/Integration/Services/Documentgenerator/Template/Service/TemplateTest.php`
15. `tests/Integration/Services/Documentgenerator/Template/Service/BatchTest.php`
16. `tests/Integration/Services/Documentgenerator/Template/Result/TemplateItemResultAnnotationsTest.php`

### Files Modified (Phase 2)

- `src/Services/Documentgenerator/DocumentgeneratorServiceBuilder.php` — added `template()` method
- `phpunit.xml.dist` — added 3 new test suites for template
- `Makefile` — added 3 new make targets
- `.php-cs-fixer.php` — added `src/Services/Documentgenerator/`
- `phpstan.neon.dist` — added `tests/Integration/Services/Documentgenerator`
- `CHANGELOG.md` — added Template entry under `## 3.3.0 – UNRELEASED`

---

## Plan: Add support for documentgenerator.region.* methods (issue #489)

## Context

The Bitrix24 REST API exposes a set of methods for managing document generator regions:
- `documentgenerator.region.add` — creates a new custom region
- `documentgenerator.region.update` — updates an existing region by `id` + `fields`
- `documentgenerator.region.get` — returns a region by `id`
- `documentgenerator.region.list` — returns a paginated list of regions
- `documentgenerator.region.delete` — deletes a region by `id` (returns null on success)

All methods belong to scope `documentgenerator`.

API response envelope (verified against `documentgenerator.region.delete` via MCP):
- Add → `result.region = {...}` (matching pattern of numerator.add)
- Update → `result = null` (boolean cast = true on success)
- Get → `result.region = {...}`
- List → `result.regions = [...]`
- Delete → `result = null` (boolean cast on result)

Region entity fields (based on API docs):
- `id` — int
- `languageId` — string
- `name` — string
- `code` — string

All REST methods use lowercase `id` parameter (not `ID`), matching the Numerator pattern.
A custom `Batch` class (like `Numerator\Batch`) is required to override lowercase `id`
and `regions` result key handling.

---

## Files to Create

- `src/Services/Documentgenerator/Region/Result/RegionItemResult.php`
- `src/Services/Documentgenerator/Region/Result/RegionResult.php`
- `src/Services/Documentgenerator/Region/Result/RegionsResult.php`
- `src/Services/Documentgenerator/Region/Result/AddedRegionResult.php`
- `src/Services/Documentgenerator/Region/Result/AddedRegionBatchResult.php`
- `src/Services/Documentgenerator/Region/Result/UpdatedRegionResult.php`
- `src/Services/Documentgenerator/Region/Result/UpdatedRegionBatchResult.php`
- `src/Services/Documentgenerator/Region/Result/DeletedRegionResult.php`
- `src/Services/Documentgenerator/Region/Result/DeletedRegionBatchResult.php`
- `src/Services/Documentgenerator/Region/Batch.php`
- `src/Services/Documentgenerator/Region/Service/Batch.php`
- `src/Services/Documentgenerator/Region/Service/Region.php`
- `tests/Integration/Services/Documentgenerator/Region/Service/RegionTest.php`
- `tests/Integration/Services/Documentgenerator/Region/Service/BatchTest.php`
- `tests/Integration/Services/Documentgenerator/Region/Result/RegionItemResultAnnotationsTest.php`

## Files to Modify

- `src/Services/Documentgenerator/DocumentgeneratorServiceBuilder.php`
- `phpunit.xml.dist`
- `Makefile`
- `CHANGELOG.md`
