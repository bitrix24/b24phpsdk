# Plan: Add REST v3 support for catalog.section.* (issue #672)

## Context

- Issue: https://github.com/bitrix24/b24phpsdk/issues/672 — REST 3.0 wrappers for
  `catalog.section.*`, scope `catalog`, milestone 3.8.0.
- Follows the pattern approved for #674 (`.tasks/674/plan.md`) and used in #673: services are exposed
  through `CatalogServiceBuilder::v3()`, classes live under `Services\Catalog\V3\*`, the legacy REST v1
  `CatalogServiceBuilder::section()` stays untouched.
- Branch `feature/672-catalog-section-rest-v3` is stacked on `feature/673-catalog-measure-rest-v3`
  (PR #706, itself on #705). Merge order: #705 → #706 → this PR.

### Verified live contracts (test portal, 2026-10-29)

| Method | Request | Response |
|---|---|---|
| `catalog.section.get` | `{id, select?}` | `result.item` |
| `catalog.section.list` | `{filter (MUST contain iblockId), select?, order?, pagination?}` | `result.items[]` |
| `catalog.section.add` | `{fields{iblockId*, name*, iblockSectionId?, active?, code?, xmlId?, sort?, description?, descriptionType?}}` | `result.item` |
| `catalog.section.update` | `{id, fields{...}}` (`iblockId` is not editable) | `result.item` |
| `catalog.section.delete` | `{id}` | `result.result = true` |
| `catalog.section.field.get` / `.field.list` | `{name, select?}` / `{select?}` | `result.item` / `result.items[]` |

Differences from VAT/measure that shape the API:

1. **`list` requires an `iblockId` filter** — without it the portal returns
   `BITRIX_REST_V3_EXCEPTION_VALIDATION_REQUESTFILTERVALIDATIONEXCEPTION` ("filter by required field
   `iblockId`"). Therefore `list(int $iblockId, ...)` takes it as the first mandatory argument and
   prepends `['iblockId', '=', $iblockId]` to the filter.
2. **`active` is a `Y`/`N` string**, both in fields and in filters; native booleans are rejected
   ("field `active` requires type `string`"). Field metadata reports type `string`.
   - `SectionItemResult::$active` is annotated `bool|null` (SDK convention: Y/N flags → bool;
     `AbstractAnnotatedItem` casts `Y`/`N`). The annotation test normalizes the live `string` type of
     `active` to `bool` with an explanatory comment.
   - Generated `SectionItemBuilder::active(string)` is changed to `active(bool)` sending `Y`/`N`.
   - `SectionFilter::active()` uses the shared `BoolFieldConditionBuilder` (it emits `Y`/`N`).
3. **Deleting a section also deletes its subsections** (verified: a child returned
   `BITRIX_REST_V3_EXCEPTION_ACCESSDENIEDEXCEPTION` after the parent was deleted). Documented in
   `delete()` phpdoc.
4. Root sections have `iblockSectionId = 0`; `descriptionType` is `text` or `html` (kept as string).
- Test catalog: product iblock is discovered at runtime via `catalog.catalog.list` (v3 raw call in the
  integration fixture: first item with `productIblockId = 0`); on the test portal it is `24`.

### Result-class reuse decision

Same as #674: `SectionResult` (get/add/update), `SectionsResult` (list), `DeletedSectionResult`.

---

## Files to Create

### Generated (done)

```bash
php bin/console b24-dev:generate-select-builder bitrix.catalog.sectiondto \
  --namespace='Bitrix24\SDK\Services\Catalog\V3\Section\Service' --class-name=SectionSelectBuilder \
  --output=src/Services/Catalog/V3/Section/Service/SectionSelectBuilder.php
php bin/console b24-dev:generate-item-builder /catalog.section.add \
  --namespace='Bitrix24\SDK\Services\Catalog\V3\Section\Service' --class-name=SectionItemBuilder \
  --output=src/Services/Catalog/V3/Section/Service/SectionItemBuilder.php
```

Manual change: `active(bool)` → `Y`/`N` (see point 2). `SectionItemResult` is written manually
(result-item generator cannot run in a worktree container, see #674).

### `src/Services/Catalog/V3/Section/Service/`

- `Section.php` — `get(int $id, array|SectionSelectBuilder $select = []): SectionResult`,
  `list(int $iblockId, array|FilterBuilderInterface $filter = [], array|SectionSelectBuilder $select = [], array $order = [], ?SectionListPagination $pagination = null): SectionsResult`,
  `add(array|SectionItemBuilder $fields): SectionResult`,
  `update(int $id, array|SectionItemBuilder $fields): SectionResult`,
  `delete(int $id): DeletedSectionResult`;
  links `https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-{method}-rest-v3.html`.
- `SectionListPagination.php` — as `VatListPagination`.
- `SectionFilter.php` — `id()`, `iblockSectionId()`, `sort()` → Int; `name()`, `code()`, `xmlId()` →
  String; `active()` → Bool (Y/N). No `iblockId()` (passed to `list()` directly).

### `src/Services/Catalog/V3/Section/Result/`

`SectionItemResult` (`#[OpenApiEntity('bitrix.catalog.sectiondto', SectionSelectBuilder::class)]`;
`int $id`, `int|null $iblockId`, `string|null $name`, `int|null $iblockSectionId`, `bool|null $active`,
`string|null $code`, `string|null $xmlId`, `int|null $sort`, `string|null $description`,
`string|null $descriptionType`), `SectionResult::section()`, `SectionsResult::getSections()`,
`DeletedSectionResult::isSuccess()`.

### `src/Services/Catalog/V3/SectionField/`

`Service/SectionField.php` (links `.../catalog/section/catalog-section-field-{get,list}.html`),
`Result/SectionFieldItemResult`, `Result/SectionFieldResult::field()`,
`Result/SectionFieldsResult::getFields()` / `getFieldsDescription()`.

### Tests

- Unit: `tests/Unit/Services/Catalog/V3/Section/Service/{SectionTest,SectionFilterTest,SectionListPaginationTest}.php`
  (incl. `iblockId` prepended to builder and array filters, `active(bool)` → `Y`/`N`, `active` Y/N cast),
  `tests/Unit/Services/Catalog/V3/SectionField/Service/SectionFieldTest.php`; extend
  `CatalogV3ServiceBuilderTest` with `section`/`sectionField` and legacy `section()` preservation.
- Integration: `.../Catalog/V3/Section/Service/SectionTest.php` (add root + child, get, list by
  iblock with filter/order/pagination, update incl. `active`, delete; cleanup in `tearDown`),
  `.../Section/Result/SectionItemResultAnnotationsTest.php`,
  `.../SectionField/Service/SectionFieldTest.php`,
  `.../SectionField/Result/SectionFieldItemResultAnnotationsTest.php`.

---

## Files to Modify

1. `src/Services/Catalog/V3/CatalogV3ServiceBuilder.php` — `section()`, `sectionField()`.
2. `phpunit.xml.dist` — suite `integration_tests_catalog_v3_section`.
3. `Makefile` — `test-integration-catalog-v3-section`.
4. `CHANGELOG.md` → `## Unreleased` → `### Added` — entry with `[#672]` link.

## Deptrac compliance

Same layers as #674; no new violations.

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-v3-section
make sdk-coverage-v3-show-uncovered   # catalog.section.* no longer listed
```

---

## Implementation notes (2026-10-29)

- Implemented as planned; the only generator deviation is `SectionItemBuilder::active(bool)` (Y/N),
  marked with a comment in the class.
- Integration fixture `tests/Integration/Services/Catalog/V3/ProductCatalogIblockTrait.php` finds the
  product iblock via a raw REST v3 `catalog.catalog.list` call; #671 can replace it with the typed
  `v3()->catalog()` service.
- Quality gate: cs-fixer, rector, phpstan, deptrac, `make test-unit` (2224 tests) and
  `make test-integration-catalog-v3-section` (12 tests, live portal) pass; `catalog.section.*` is no
  longer in `make sdk-coverage-v3-show-uncovered` (v3 coverage 134/578, 23.18%).
