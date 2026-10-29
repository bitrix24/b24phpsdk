# Plan: Add REST v3 support for catalog.catalog.* (issue #671)

## Context

- Issue: https://github.com/bitrix24/b24phpsdk/issues/671 — REST 3.0 wrappers for
  `catalog.catalog.*`, scope `catalog`, milestone 3.8.0.
- Follows the pattern approved for #674 and used in #673/#672: services are exposed through
  `CatalogServiceBuilder::v3()`, classes live under `Services\Catalog\V3\*`, the legacy REST v1
  `CatalogServiceBuilder::catalog()` stays untouched.
- Branch `feature/671-catalog-catalog-rest-v3` is stacked on `feature/672-catalog-section-rest-v3`
  (PR #707 → #706 → #705). Merge order: #705 → #706 → #707 → this PR.

### Verified live contracts (test portal, 2026-10-29)

| Method | Request | Response |
|---|---|---|
| `catalog.catalog.get` | `{id, select?}` | `result.item` |
| `catalog.catalog.list` | `{select?, filter?, order?, pagination?}` (no required filter) | `result.items[]` |
| `catalog.catalog.add` | `{fields{iblockId*, yandexExport?, subscription?, vatId?, productIblockId?, skuPropertyId?}}` | `result.item` |
| `catalog.catalog.update` | `{id, fields{yandexExport?, subscription?, vatId?, productIblockId?, skuPropertyId?}}` | `result.item` |
| `catalog.catalog.delete` | `{id}` | `result.result = true` |
| `catalog.catalog.isoffers` | `{id}` | `result.result` bool (true for a SKU/offers catalog) |
| `catalog.catalog.field.get` / `.field.list` | `{name, select?}` / `{select?}` | `result.item` / `result.items[]` |

- Catalog DTO (`bitrix.catalog.catalogdto`): `id:int` (equals `iblockId`), `iblockId:int`,
  `yandexExport:Y/N`, `subscription:Y/N`, `vatId:int` (0 = none), `productIblockId:int` (0 for a
  product catalog, parent iblock for an offers catalog), `skuPropertyId:int`, `iblockTypeId:string`,
  `lid:string`, `name:string` (the last three are read-only).
- `yandexExport` and `subscription` are Y/N strings in fields and filters; native booleans are
  rejected (verified). As with section `active`: annotate `bool|null`, change generated
  `CatalogItemBuilder::yandexExport()/subscription()` to `bool` → `Y`/`N`, use the shared
  `BoolFieldConditionBuilder` in `CatalogFilter`, normalize metadata type `string` → `bool` in the
  annotation test.
- **Delete semantics (issue requirement):** `catalog.catalog.delete` removes only the catalog flag; the
  underlying information block survives (verified with `lists.get` after delete). After delete,
  `get`/`isoffers` fail with `BITRIX_REST_V3_EXCEPTION_ENTITYNOTFOUNDEXCEPTION`.
- Exact lowercase method name `catalog.catalog.isoffers`; SDK method name `isOffers()`.
- Integration fixture for add/update/delete: a temporary information block created with REST v1
  `lists.add` (`IBLOCK_TYPE_ID = lists`) and removed with `lists.delete` in `tearDown`; the test also
  asserts the iblock still exists (`lists.get`) after the catalog is deleted.

### Result-class reuse decision

`CatalogResult` (get/add/update), `CatalogsResult` (list), `DeletedCatalogResult` (`result.result`),
`IsOffersCatalogResult::isOffers()` (`result.result`) — a dedicated wrapper because the value is a
domain flag, not an operation status.

---

## Files to Create

### Generated (done)

```bash
php bin/console b24-dev:generate-select-builder bitrix.catalog.catalogdto \
  --namespace='Bitrix24\SDK\Services\Catalog\V3\Catalog\Service' --class-name=CatalogSelectBuilder \
  --output=src/Services/Catalog/V3/Catalog/Service/CatalogSelectBuilder.php
php bin/console b24-dev:generate-item-builder /catalog.catalog.add \
  --namespace='Bitrix24\SDK\Services\Catalog\V3\Catalog\Service' --class-name=CatalogItemBuilder \
  --output=src/Services/Catalog/V3/Catalog/Service/CatalogItemBuilder.php
```

Manual change: `yandexExport(bool)` / `subscription(bool)` → `Y`/`N`. `CatalogItemResult` is written
manually (result-item generator cannot run in a worktree container, see #674).

### `src/Services/Catalog/V3/Catalog/Service/`

- `Catalog.php` — `get`, `list(array|FilterBuilderInterface $filter = [], array|CatalogSelectBuilder $select = [], array $order = [], ?CatalogListPagination $pagination = null)`,
  `add(array|CatalogItemBuilder $fields)`, `update(int $id, array|CatalogItemBuilder $fields)`,
  `delete(int $id): DeletedCatalogResult` (phpdoc: the information block is kept),
  `isOffers(int $id): IsOffersCatalogResult`; links
  `https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-get-rest-v3.html`,
  `-list-rest-v3.html`, `-is-offers-rest-v3.html`, and `catalog-catalog-{add,update,delete}.html`
  (exact URLs from the issue checklist).
- `CatalogListPagination.php`, `CatalogFilter.php` (`id()`, `iblockId()`, `vatId()`,
  `productIblockId()`, `skuPropertyId()` → Int; `iblockTypeId()`, `lid()`, `name()` → String;
  `yandexExport()`, `subscription()` → Bool Y/N).

### `src/Services/Catalog/V3/Catalog/Result/`

`CatalogItemResult` (`#[OpenApiEntity('bitrix.catalog.catalogdto', CatalogSelectBuilder::class)]`),
`CatalogResult::catalog()`, `CatalogsResult::getCatalogs()`, `DeletedCatalogResult::isSuccess()`,
`IsOffersCatalogResult::isOffers()`.

### `src/Services/Catalog/V3/CatalogField/`

`Service/CatalogField.php` (links `.../catalog/catalog/catalog-catalog-field-{get,list}.html`),
`Result/CatalogFieldItemResult`, `Result/CatalogFieldResult::field()`,
`Result/CatalogFieldsResult::getFields()` / `getFieldsDescription()`.

### Tests

- Unit: `tests/Unit/Services/Catalog/V3/Catalog/Service/{CatalogTest,CatalogFilterTest,CatalogListPaginationTest}.php`,
  `tests/Unit/Services/Catalog/V3/CatalogField/Service/CatalogFieldTest.php`; extend
  `CatalogV3ServiceBuilderTest` with `catalog`/`catalogField` and legacy `catalog()` preservation.
- Integration: `.../Catalog/V3/Catalog/Service/CatalogTest.php` (get/list/isOffers on existing catalogs;
  add → update → delete on a temporary `lists` iblock, iblock survives delete),
  `.../Catalog/Result/CatalogItemResultAnnotationsTest.php`,
  `.../CatalogField/Service/CatalogFieldTest.php`,
  `.../CatalogField/Result/CatalogFieldItemResultAnnotationsTest.php`.
- Refactor `tests/Integration/Services/Catalog/V3/ProductCatalogIblockTrait.php` (from #672) to use
  the typed `v3()->catalog()->list()` instead of a raw core call.

---

## Files to Modify

1. `src/Services/Catalog/V3/CatalogV3ServiceBuilder.php` — `catalog()`, `catalogField()`.
2. `phpunit.xml.dist` — suite `integration_tests_catalog_v3_catalog`.
3. `Makefile` — `test-integration-catalog-v3-catalog`.
4. `CHANGELOG.md` → `## Unreleased` → `### Added` — entry with `[#671]` link.

## Deptrac compliance

Same layers as #674; no new violations.

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-v3-catalog
make test-integration-catalog-v3-section   # ProductCatalogIblockTrait refactor
make sdk-coverage-v3-show-uncovered        # catalog.catalog.* no longer listed
```

---

## Implementation notes (2026-10-29)

- Implemented as planned; generator deviations: `CatalogItemBuilder::yandexExport(bool)` and
  `subscription(bool)` (Y/N), marked with comments in the class.
- `ProductCatalogIblockTrait` now uses `v3()->catalog()->list()` with `CatalogFilter::productIblockId()`;
  the #672 section suite still passes (12 tests).
- The add/update/delete integration test creates a temporary `lists` iblock and removes it in
  `tearDown`; no leftover `sdk_v3_*` iblocks after the run.
- Quality gate: cs-fixer, rector, phpstan, deptrac, `make test-unit` (2241 tests),
  `make test-integration-catalog-v3-catalog` (10 tests) and `make test-integration-catalog-v3-section`
  (12 tests) pass on the live portal; no `catalog.catalog.*` method remains in
  `make sdk-coverage-v3-show-uncovered` (v3 coverage 142/578, 24.57%).
