# Plan: Add REST v3 support for catalog.vat.* (issue #674)

## Context

- Issue: https://github.com/bitrix24/b24phpsdk/issues/674 — REST 3.0 wrappers for `catalog.vat.*`,
  scope `catalog`. Base branch `v3-dev`, branch `feature/674-catalog-vat-rest-v3`, milestone 3.8.0.
- The legacy REST v1 service `Services\Catalog\Vat\Service\Vat` (accessor
  `CatalogServiceBuilder::vat()`, released in 3.7.0, #590) MUST stay untouched.
- **Design decision (agreed with maintainer, 2026-10-29):** REST v3 catalog services live in a
  dedicated sub-builder, `CatalogServiceBuilder::v3(): CatalogV3ServiceBuilder`, with classes under
  `Services\Catalog\V3\*`. The same sub-builder will host `measure`, `section`, `catalog`
  (#673, #672, #671) and later catalog v3 issues. Field metadata services also live in the
  sub-builder (`v3()->vatField()`), so every REST v3 catalog entry point is in one place and no
  legacy accessor name is reused.

### Verified live contracts (test portal, 2026-10-29, `/rest/api/<id>/<token>/`)

| Method | Request | Response |
|---|---|---|
| `catalog.vat.get` | `{id, select?}` | `result.item` = vat DTO |
| `catalog.vat.list` | `{select?, filter?, order?, pagination?{page,limit,offset}}` | `result.items[]` |
| `catalog.vat.add` | `{fields{name*, rate*, active?, sort?}}` | `result.item` = created DTO (not an ID) |
| `catalog.vat.update` | `{id, fields{...}}` | `result.item` = updated DTO |
| `catalog.vat.delete` | `{id}` | `result.result = true` |
| `catalog.vat.field.get` | `{name, select?}` | `result.item` = field descriptor |
| `catalog.vat.field.list` | `{select?}` | `result.items[]` field descriptors |

- Vat DTO (`bitrix.catalog.vatdto`): `id:int`, `timestampX:date-time`, `active:bool`, `sort:int`,
  `name:string`, `rate:float`. Live `rate` arrives as int for whole numbers (`20`) — the annotated
  item casts it to `float`.
- Live field metadata reports `timestampX` type as `object` (same as `main.eventlog`); the
  annotation test normalizes it to `datetime` as in `EventLogItemResultAnnotationsTest`.
- Field descriptor keys: `name, type, title, description, validationRules, requiredGroups,
  filterable, sortable, editable, editableGroups, multiple, elementType`.
- Missing entity → error `BITRIX_REST_V3_EXCEPTION_ENTITYNOTFOUNDEXCEPTION` (handled by core).
- OpenAPI also documents filter-based bulk `update`/`delete` (`filter` instead of `id`). Out of
  scope: the docs page describes id-based calls; bulk mode can be added later without BC break.

### Result-class reuse decision

- `Core\Result\DeletedItemResult` reads `result[0]`; v3 returns `result.result` → not compatible.
  Add `DeletedVatResult extends AbstractResult` with `isSuccess()` (same as Note `DeletedCollectionResult`).
- `AddedItemResult`/`UpdatedItemResult` do not fit: add/update return `result.item` DTO → reuse
  `VatResult` (single-item wrapper) for get/add/update.

---

## Files to Create

All PHP files: standard license header, `declare(strict_types=1);`.

### 1. `src/Services/Catalog/V3/CatalogV3ServiceBuilder.php`

```php
namespace Bitrix24\SDK\Services\Catalog\V3;

#[ApiServiceBuilderMetadata(new Scope(['catalog']))]
class CatalogV3ServiceBuilder extends AbstractServiceBuilder
{
    public function vat(): Vat\Service\Vat;            // serviceCache, new Vat($this->core, $this->log)
    public function vatField(): VatField\Service\VatField;
}
```

### 2. `src/Services/Catalog/V3/Vat/Service/Vat.php`

`#[ApiServiceMetadata(new Scope(['catalog']))] class Vat extends AbstractService`; every method has
`#[ApiEndpointMetadata('<method>', '<apidocs.bitrix24.com link>', '<description>', ApiVersion::v3)]`
and calls `$this->core->call(..., ApiVersion::v3)`. Doc links:
`https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-{get,list,add,update,delete}-rest-v3.html`.

```php
public function get(int $id, array|VatSelectBuilder $select = []): VatResult;
public function list(
    array|FilterBuilderInterface $filter = [],
    array|VatSelectBuilder $select = [],
    array $order = [],                       // array<string, 'ASC'|'DESC'>
    ?VatListPagination $pagination = null,
): VatsResult;
public function add(array|VatItemBuilder $fields): VatResult;
public function update(int $id, array|VatItemBuilder $fields): VatResult;
public function delete(int $id): DeletedVatResult;
```

Empty `select`/`filter`/`order`/`null` pagination are omitted from the payload (FollowUp pattern).

### 3. `src/Services/Catalog/V3/Vat/Service/VatListPagination.php`

`final class` with `?int $page`, `?int $limit`, `?int $offset`; `toArray()` drops nulls.

### 4. `src/Services/Catalog/V3/Vat/Service/VatSelectBuilder.php` (generated)

```bash
php bin/console b24-dev:generate-select-builder bitrix.catalog.vatdto \
  --namespace='Bitrix24\SDK\Services\Catalog\V3\Vat\Service' --class-name=VatSelectBuilder \
  --output=src/Services/Catalog/V3/Vat/Service/VatSelectBuilder.php
```

### 5. `src/Services/Catalog/V3/Vat/Service/VatItemBuilder.php` (generated)

```bash
php bin/console b24-dev:generate-item-builder /catalog.vat.add \
  --namespace='Bitrix24\SDK\Services\Catalog\V3\Vat\Service' --class-name=VatItemBuilder \
  --output=src/Services/Catalog/V3/Vat/Service/VatItemBuilder.php
```

### 6. `src/Services/Catalog/V3/Vat/Service/VatFilter.php`

`extends AbstractFilterBuilder`: `id()` / `sort()` → Int, `active()` → Bool, `name()` → String,
`timestampX()` → DateTime. `rate` (float) has no typed condition builder yet; use a raw array filter.

### 7. Results in `src/Services/Catalog/V3/Vat/Result/`

- `VatItemResult extends AbstractAnnotatedItem` — generated via
  `php bin/console b24-dev:result-item-generator catalog.vat.get --stage=all`, reviewed; annotations:
  `int $id`, `CarbonImmutable|null $timestampX`, `bool|null $active`, `int|null $sort`,
  `string|null $name`, `float|null $rate`; `#[OpenApiEntity(entityKey: 'bitrix.catalog.vatdto', selectBuilder: VatSelectBuilder::class)]`.
- `VatResult extends AbstractResult` — `vat(): VatItemResult` from `result.item`.
- `VatsResult extends AbstractResult` — `getVats(): VatItemResult[]` from `result.items`.
- `DeletedVatResult extends AbstractResult` — `isSuccess(): bool` from `result.result`.

### 8. `src/Services/Catalog/V3/VatField/Service/VatField.php`

`get(string $name, array $select = []): VatFieldResult` (guards non-empty name) and
`list(array $select = []): VatFieldsResult`, links
`https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-field-{get,list}.html`.

### 9. Results in `src/Services/Catalog/V3/VatField/Result/`

- `VatFieldItemResult extends AbstractAnnotatedItem` (descriptor annotations as `EventLogFieldItemResult`, incl. `editableGroups`).
- `VatFieldResult` — `field(): VatFieldItemResult`.
- `VatFieldsResult` — `getFields(): VatFieldItemResult[]`, `getFieldsDescription(): array<string, array>` (EventLogFieldsResult pattern).

### 10. Unit tests (`tests/Unit/Services/Catalog/V3/...`)

- `CatalogV3ServiceBuilderTest` — accessors return cached instances of the right classes;
  `CatalogServiceBuilderTest` (existing or new) — `v3()` returns `CatalogV3ServiceBuilder`, legacy `vat()` unchanged.
- `Vat/Service/VatTest` — MockHttpClient core: request payloads per method (builder and array
  forms, omitted empty params, pagination), v3 URL/version, result mapping incl. float cast of
  int `rate`, `CarbonImmutable` `timestampX`, `DeletedVatResult::isSuccess()`; metadata attributes
  report `ApiVersion::v3` and `apidocs.bitrix24.com` links.
- `Vat/Service/VatFilterTest`, `VatListPaginationTest`.
- `VatField/Service/VatFieldTest` — payloads, empty-name guard, `getFieldsDescription()`.

### 11. Integration tests (`tests/Integration/Services/Catalog/V3/...`)

- `Vat/Service/VatTest` — add → get → list (filter by id, select, order, pagination) → update → delete; cleanup in `tearDown`.
- `Vat/Result/VatItemResultAnnotationsTest` — `testAllSystemFieldsAnnotated`,
  `testAllSystemFieldsHasValidTypeAnnotation` (timestampX `object`→`datetime`) using
  `v3()->vatField()->list()->getFieldsDescription()`; plus result casting check via
  `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations` on a live item.
- `VatField/Service/VatFieldTest` — get/list live.
- `VatField/Result/VatFieldItemResultAnnotationsTest` — keys of a live descriptor annotated; casting.

---

## Files to Modify

1. `src/Services/Catalog/CatalogServiceBuilder.php` — add `public function v3(): V3\CatalogV3ServiceBuilder` (cached, `new V3\CatalogV3ServiceBuilder($this->core, $this->batch, $this->bulkItemsReader, $this->log)` — match the `AbstractServiceBuilder` constructor).
2. `phpunit.xml.dist` — suite `integration_tests_catalog_v3_vat` → `./tests/Integration/Services/Catalog/V3/`
   (vat + vatField dirs; named per entity so #673/#672/#671 add their own suites).
3. `Makefile` — `.PHONY: test-integration-catalog-v3-vat` + target running that suite with `-e BITRIX24_WEBHOOK`.
4. `CHANGELOG.md` → `## Unreleased` → `### Added`:
   `- Added REST v3 support for \`catalog.vat.*\` via \`CatalogServiceBuilder::v3()->vat()\` and \`v3()->vatField()\`; legacy \`vat()\` is unchanged ([#674](https://github.com/bitrix24/b24phpsdk/issues/674))`
5. `docs/EN/Services/bitrix24-php-sdk-methods.md` — regenerate if the repo has a make target for it.

---

## Deptrac compliance

New code depends only on `Core`, `Attributes`, `Filters`, `Services\AbstractService*` — same
layers as existing v3 services. No new `skip_violations`.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-v3-vat
make sdk-coverage-v3-show-uncovered   # catalog.vat.* no longer listed
```

---

## Implementation notes (2026-10-29)

- `VatSelectBuilder` and `VatItemBuilder` were generated with the commands above. The item
  generator skipped `rate` because the OpenAPI snapshot types it as the non-standard `float`;
  `rate(float)` was added manually with a comment, covered by `VatFilterTest`.
- `b24-dev:result-item-generator catalog.vat.get --stage=all` fails inside the Docker container
  for a git worktree (`Unable to determine the current git branch`: the worktree `.git` file
  points outside the mounted directory). `VatItemResult` was written manually from the verified
  live DTO and is guarded by `VatItemResultAnnotationsTest`.
- REST v3 catalog validates boolean filter values as native booleans and rejects `Y`/`N`
  (verified live). The shared `BoolFieldConditionBuilder` emits `Y`/`N`, so `VatFilter::active()`
  returns the generic `FieldConditionBuilder`. #673/#672/#671 must do the same for bool fields.
- `CustomBitrix24Assertions::assertBitrix24AllResultItemFieldsHasValidTypeAnnotation()` gained a
  `float` case (REST v3 field metadata type) expecting a `float` annotation; the legacy `double`
  mapping (Money/Percentage) is unchanged.
- PHPStan cannot follow filter chains across different fields (condition builders return
  `AbstractFilterBuilder`), so tests add conditions as separate statements.
- Quality gate: cs-fixer, rector, phpstan, deptrac, `make test-unit` (2193 tests) and
  `make test-integration-catalog-v3-vat` (12 tests, live portal) pass; `catalog.vat.*` is no
  longer in `make sdk-coverage-v3-show-uncovered` (v3 coverage 120/578, 20.76%).
