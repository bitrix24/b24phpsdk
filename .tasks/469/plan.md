# Plan: Add `biconnector.source.*` service support (issue #469)

## Context

The `biconnector` scope already has `Connector` (add/get/list/update/delete/fields/count) implemented.
Now we add the `Source` entity, covering six REST methods:

| REST method                  | Response envelope             | Notes                      |
|------------------------------|-------------------------------|----------------------------|
| `biconnector.source.add`     | `result.id` (int)             | Fields wrapped in `fields` |
| `biconnector.source.update`  | `result` = `true`             |                            |
| `biconnector.source.get`     | `result.item.connection` + root connectorId/settings | Nested; must be flattened  |
| `biconnector.source.list`    | `result` = flat array         | Uses 'page' pagination (same as connector.list) |
| `biconnector.source.delete`  | `result` = `true`             |                            |
| `biconnector.source.fields`  | `result.fields` array         | Returns `FieldsResult`     |

### Key API notes
- `biconnector.source.delete` uses lowercase `id` parameter (not `ID`)
- `biconnector.source.get` returns nested: `result.item.connection.{id,type,code,title,...}` plus `result.item.connectorId` and `result.item.settings`
- `SourceResult` must flatten `connection` + root fields into a single `SourceItemResult`
- `biconnector.source.fields` lists: id, title, type, code, description, active, dateCreate, dateUpdate, createdById, updatedById, connectorId, settings (all camelCase)

### SourceItemResult field types
| Field         | Bitrix24 type | PHP type          |
|---------------|---------------|-------------------|
| id            | integer       | int               |
| title         | string        | string            |
| type          | string        | string\|null      |
| code          | string        | string\|null      |
| description   | string        | string\|null      |
| active        | boolean       | bool\|null        |
| dateCreate    | datetime      | CarbonImmutable   |
| dateUpdate    | datetime      | CarbonImmutable   |
| createdById   | integer       | int               |
| updatedById   | integer       | int               |
| connectorId   | integer       | int               |
| settings      | array         | array\|null       |

---

## Files to Create

### 1. `src/Services/Biconnector/Source/Result/SourceItemResult.php`
All 12 fields annotated with @property-read, `__get` with match expression.

### 2. `src/Services/Biconnector/Source/Result/SourceResult.php`
Reads `result.item.connection` merged with root fields (connectorId, settings), returns `SourceItemResult`.

### 3. `src/Services/Biconnector/Source/Result/SourcesResult.php`
Reads flat `result` array, returns `SourceItemResult[]` via `getSources()`.

### 4. `src/Services/Biconnector/Source/Result/AddedSourceResult.php`
Extends `AddedItemResult`, reads `result['id']`.

### 5. `src/Services/Biconnector/Source/Result/AddedSourceBatchResult.php`
Extends `AddedItemBatchResult`, reads `result['id']`.

### 6. `src/Services/Biconnector/Source/Result/UpdatedSourceResult.php`
Extends `UpdatedItemResult`, `isSuccess()` returns `(bool)$result`.

### 7. `src/Services/Biconnector/Source/Result/UpdatedSourceBatchResult.php`
Extends `UpdatedItemBatchResult`, `isSuccess()` returns `(bool)$result`.

### 8. `src/Services/Biconnector/Source/Result/DeletedSourceResult.php`
Extends `DeletedItemResult`, `isSuccess()` returns `(bool)$result`.

### 9. `src/Services/Biconnector/Source/Result/DeletedSourceBatchResult.php`
Extends `DeletedItemBatchResult`, `isSuccess()` returns `(bool)$result`.

### 10. `src/Services/Biconnector/Source/Batch.php`
Extends `\Bitrix24\SDK\Core\Batch`, overrides `determineKeyId` → 'id' and `deleteEntityItems` → lowercase 'id'.

### 11. `src/Services/Biconnector/Source/Service/Source.php`
Methods: add, update, get, list, delete, fields, count.

### 12. `src/Services/Biconnector/Source/Service/Batch.php`
Batch service: list, add, update, delete.

### 13. `tests/Unit/Services/Biconnector/SourceServiceBuilderTest.php`
Unit test: sourceService is cached.

### 14. `tests/Integration/Services/Biconnector/Source/Service/SourceTest.php`
Integration tests: add, get, list, update, delete, fields, count.

### 15. `tests/Integration/Services/Biconnector/Source/Service/BatchTest.php`
Integration tests: batchList, batchAdd, batchDelete.

### 16. `tests/Integration/Services/Biconnector/Source/Result/SourceItemResultAnnotationsTest.php`
Annotation tests: testAllSystemFieldsAnnotated, testAllSystemFieldsHasValidTypeAnnotation.

---

## Files to Modify

### 1. `src/Services/Biconnector/BiconnectorServiceBuilder.php`
Add `source(): Source` method using `SourceBatch` custom batch.

### 2. `phpunit.xml.dist`
Add `integration_tests_biconnector_source` test suite.

### 3. `Makefile`
Add `test-integration-biconnector-source` target.

### 4. `CHANGELOG.md`
Add entry under `## 3.2.0 – UNRELEASED` → `### Added`.

---

## Deptrac compliance
New namespaces under `Biconnector\Source` follow the same layer as `Biconnector\Connector` — no new cross-layer dependencies.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-biconnector-source
```

