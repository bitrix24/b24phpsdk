# Plan: Add REST v3 support for catalog.measure.* (issue #673)

## Context

- Issue: https://github.com/bitrix24/b24phpsdk/issues/673 — REST 3.0 wrappers for
  `catalog.measure.*`, scope `catalog`, milestone 3.8.0.
- Follows the pattern approved for #674 (`.tasks/674/plan.md`): REST v3 catalog services are exposed
  through `CatalogServiceBuilder::v3()` (`CatalogV3ServiceBuilder`), classes live under
  `Services\Catalog\V3\*`, the legacy REST v1 `CatalogServiceBuilder::measure()` stays untouched.
- Branch `feature/673-catalog-measure-rest-v3` is stacked on `feature/674-catalog-vat-rest-v3`
  (PR #705), because it needs `CatalogV3ServiceBuilder`, `CatalogServiceBuilder::v3()` and the
  `float` type mapping in `CustomBitrix24Assertions`. The PR targets `v3-dev` and must be merged
  after #705.

### Verified live contracts (test portal, 2026-10-29)

| Method | Request | Response |
|---|---|---|
| `catalog.measure.get` | `{id, select?}` | `result.item` |
| `catalog.measure.list` | `{select?, filter?, order?, pagination?{page,limit,offset}}` | `result.items[]` |
| `catalog.measure.add` | `{fields{code*, measureTitle*, symbol?, symbolIntl?, symbolLetterIntl?, isDefault?}}` | `result.item` (created DTO) |
| `catalog.measure.update` | `{id, fields{...}}` | `result.item` (updated DTO) |
| `catalog.measure.delete` | `{id}` | `result.result = true` |
| `catalog.measure.field.get` | `{name, select?}` | `result.item` field descriptor |
| `catalog.measure.field.list` | `{select?}` | `result.items[]` field descriptors |

- Measure DTO (`bitrix.catalog.measuredto`): `id:int`, `code:int`, `measureTitle:string`,
  `symbol:string`, `symbolIntl:string`, `symbolLetterIntl:string`, `isDefault:bool`. No date fields.
- `code` is unique: a duplicate add fails with `BITRIX_REST_V3_EXCEPTION_VALIDATION_REQUESTVALIDATIONEXCEPTION`
  (`Duplicate entry for key [code]`), so integration tests use random codes.
- Boolean filter values must be native booleans (`["isDefault", "=", true]` works), same as VAT.
- Filter-based bulk `update`/`delete` from OpenAPI is out of scope (as in #674).

### Result-class reuse decision

Same as #674: `MeasureResult` (single `result.item`) for get/add/update, `MeasuresResult` for list,
`DeletedMeasureResult` (`result.result`) because core `DeletedItemResult` reads `result[0]`.

---

## Files to Create

### Generated (done)

```bash
php bin/console b24-dev:generate-select-builder bitrix.catalog.measuredto \
  --namespace='Bitrix24\SDK\Services\Catalog\V3\Measure\Service' --class-name=MeasureSelectBuilder \
  --output=src/Services/Catalog/V3/Measure/Service/MeasureSelectBuilder.php
php bin/console b24-dev:generate-item-builder /catalog.measure.add \
  --namespace='Bitrix24\SDK\Services\Catalog\V3\Measure\Service' --class-name=MeasureItemBuilder \
  --output=src/Services/Catalog/V3/Measure/Service/MeasureItemBuilder.php
```

Both generated complete builders (all DTO fields); no manual edits.
`MeasureItemResult` is written manually: `b24-dev:result-item-generator` cannot run inside the
Docker container for a git worktree (see #674 notes); guarded by `MeasureItemResultAnnotationsTest`.

### `src/Services/Catalog/V3/Measure/Service/`

- `Measure.php` — `get(int $id, array|MeasureSelectBuilder $select = []): MeasureResult`,
  `list(array|FilterBuilderInterface $filter = [], array|MeasureSelectBuilder $select = [], array $order = [], ?MeasureListPagination $pagination = null): MeasuresResult`,
  `add(array|MeasureItemBuilder $fields): MeasureResult`,
  `update(int $id, array|MeasureItemBuilder $fields): MeasureResult`,
  `delete(int $id): DeletedMeasureResult`; links
  `https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-{method}-rest-v3.html`.
- `MeasureListPagination.php` — `final readonly`, `?int $page/$limit/$offset`, `toArray()` drops nulls.
- `MeasureFilter.php` — `id()`, `code()` → Int; `measureTitle()`, `symbol()`, `symbolIntl()`,
  `symbolLetterIntl()` → String; `isDefault()` → generic `FieldConditionBuilder` (native bool).

### `src/Services/Catalog/V3/Measure/Result/`

`MeasureItemResult` (`AbstractAnnotatedItem`, `#[OpenApiEntity('bitrix.catalog.measuredto', MeasureSelectBuilder::class)]`;
`int $id`, `int|null $code`, `string|null $measureTitle`, `string|null $symbol`,
`string|null $symbolIntl`, `string|null $symbolLetterIntl`, `bool|null $isDefault`),
`MeasureResult::measure()`, `MeasuresResult::getMeasures()`, `DeletedMeasureResult::isSuccess()`.

### `src/Services/Catalog/V3/MeasureField/`

`Service/MeasureField.php` (`get(string $name, array $select = [])`, `list(array $select = [])`,
links `.../catalog/measure/catalog-measure-field-{get,list}.html`),
`Result/MeasureFieldItemResult`, `Result/MeasureFieldResult::field()`,
`Result/MeasureFieldsResult::getFields()` / `getFieldsDescription()`.

### Tests

- Unit: `tests/Unit/Services/Catalog/V3/Measure/Service/{MeasureTest,MeasureFilterTest,MeasureListPaginationTest}.php`,
  `tests/Unit/Services/Catalog/V3/MeasureField/Service/MeasureFieldTest.php`; extend
  `tests/Unit/Services/Catalog/V3/CatalogV3ServiceBuilderTest.php` with `measure`/`measureField`
  and legacy `measure()` preservation.
- Integration: `tests/Integration/Services/Catalog/V3/Measure/Service/MeasureTest.php` (CRUD with
  random unique `code`, cleanup in `tearDown`), `.../Measure/Result/MeasureItemResultAnnotationsTest.php`
  (`testAllSystemFieldsAnnotated`, `testAllSystemFieldsHasValidTypeAnnotation`, casting),
  `.../MeasureField/Service/MeasureFieldTest.php`,
  `.../MeasureField/Result/MeasureFieldItemResultAnnotationsTest.php`.

---

## Files to Modify

1. `src/Services/Catalog/V3/CatalogV3ServiceBuilder.php` — `measure()` and `measureField()` accessors.
2. `phpunit.xml.dist` — suite `integration_tests_catalog_v3_measure` (Measure + MeasureField dirs).
3. `Makefile` — `test-integration-catalog-v3-measure`.
4. `CHANGELOG.md` → `## Unreleased` → `### Added` — entry with `[#673]` link.

## Deptrac compliance

Same layers as #674; no new violations.

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-v3-measure
make sdk-coverage-v3-show-uncovered   # catalog.measure.* no longer listed
```

---

## Implementation notes (2026-10-29)

- No generator deviations beyond the manual `MeasureItemResult` noted above.
- `MeasureFilter::isDefault()` uses the generic `FieldConditionBuilder` (native bool), as decided in #674.
- Quality gate: cs-fixer, rector, phpstan, deptrac, `make test-unit` (2208 tests) and
  `make test-integration-catalog-v3-measure` (12 tests, live portal) pass; `catalog.measure.*` is no
  longer in `make sdk-coverage-v3-show-uncovered` (v3 coverage 127/578, 21.97%).
