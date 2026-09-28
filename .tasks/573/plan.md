# Plan: Add support for `catalog.roundingRule.*` methods (issue #573)

## Context

Bitrix24 REST API exposes a small set of methods to manage catalog price-rounding rules:

- `catalog.roundingRule.add` — https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-add.html
- `catalog.roundingRule.update` — https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-update.html
- `catalog.roundingRule.get` — https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-get.html
- `catalog.roundingRule.list` — https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-list.html
- `catalog.roundingRule.delete` — https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-delete.html
- `catalog.roundingRule.getFields` — https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-get-fields.html

Verified live against the test portal (`tests/.env.local` webhook) on 2026-08-12:

- `add` payload: `{"fields": {"catalogGroupId": int, "price": double, "roundType": int, "roundPrecision": double}}`
  → response `{"roundingRule": {...}}`.
- `update` payload: `{"id": int, "fields": {...}}`. **Important**: despite the docs implying a partial
  update, the live portal rejects `update` without `catalogGroupId` present in `fields`
  (`{"error":"0","error_description":"Required fields: catalogGroupId"}`). So `catalogGroupId` must
  always be supplied in `update` fields, same as `add`.
- `get` payload: `{"id": int}` → `{"roundingRule": {...}}`.
- `list` payload: `{"select": [...], "filter": {...}, "order": {...}}` → `{"roundingRules": [...], "total": int}`.
  Confirmed the method name is `catalog.roundingRule.list` in camelCase (works correctly on the live
  portal; the lowercase `catalog.roundingrule.list` variant shown in one doc source is REST's
  case-insensitive routing, not a required naming difference — no custom Batch key mapping is needed).
- `delete` payload: `{"id": int}` → boolean `true`.
- `getFields` → `{"roundingRule": {field: {isImmutable, isReadOnly, isRequired, type}}}`.

Raw field set (from live `getFields` + `add` response):

| Field | Type | Notes |
|---|---|---|
| `id` | integer | read-only |
| `catalogGroupId` | integer | required, references `catalog_price_type.id` |
| `price` | double | required, minimum price to apply rounding |
| `roundType` | integer | required, 1 = mathematical, 2 = round up, 4 = round down |
| `roundPrecision` | double | required |
| `createdBy` | integer | read-only |
| `modifiedBy` | integer | read-only |
| `dateCreate` | datetime | read-only → `CarbonImmutable` |
| `dateModify` | datetime | read-only → `CarbonImmutable` |

This scope is structurally identical to the existing `Services\Catalog\PriceType` scope (flat
add/update/get/list/delete/getFields, single item wrapped as `roundingRule`, list wrapped as
`roundingRules`).

Verified live: `catalog.roundingRule.delete` rejects the batch-default uppercase `ID` key
(`{"error":"100","error_description":"Could not find value for parameter {id}"}`) and only accepts
lowercase `id` (confirmed success). Inspecting `src/Core/Batch.php::deleteEntityItems` (line ~127)
shows the base implementation **hardcodes** `'ID' => $itemId` directly — it does not consult
`determineKeyId()` at all for the delete path (that hook is only used elsewhere, e.g.
`getLastElementId`). So, exactly as `Catalog\PriceType\Batch` already does, a full override of
`deleteEntityItems()` is required (not just `determineKeyId()`) to send lowercase `id`. Copy
`Catalog\PriceType\Batch::deleteEntityItems` verbatim into `Catalog\RoundingRule\Batch`, adjusting
log messages from "price type" to "rounding rule".

No `RoundType` enum class is added — `roundType` stays a plain `int` (the API returns/accepts a raw
integer, not an enum reference; unlike `catalog.enum.*`, `roundingRule.getFields` documents it as
`type: integer`, not a reference to `catalog_rounding_rule_round_type`). This matches how
`PriceType::base` etc. are annotated as raw scalars in this SDK.

## Files to Create

### 1. `src/Services/Catalog/RoundingRule/Result/RoundingRuleItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\RoundingRule\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int                  $id
 * @property-read int                  $catalogGroupId
 * @property-read float                $price
 * @property-read int                  $roundType
 * @property-read float                $roundPrecision
 * @property-read int|null             $createdBy
 * @property-read int|null             $modifiedBy
 * @property-read CarbonImmutable|null $dateCreate
 * @property-read CarbonImmutable|null $dateModify
 */
class RoundingRuleItemResult extends AbstractAnnotatedItem
{
}
```

**Generator not usable for this issue**: `php bin/console b24-dev:result-item-generator
catalog.roundingRule.get --stage=all` (and the lowercase `catalog.roundingrule.get` variant) fails
with `REST docs payload is required ... but the documentation URL could not be resolved`, both
before and after a fresh `make oa-schema-build` run. Inspecting the rebuilt
`docs/open-api/openapi.json` shows it contains **zero** `catalog.*` paths at all (confirmed via
grep for `catalog` in the schema — only an unrelated `vibecodeconnector` DTO field matches), even
though direct webhook calls to all six `catalog.roundingRule.*` methods succeed live against the
same portal. The schema-build step does not currently crawl the `catalog` scope for this webhook,
so the generator has no source data to work from. Proceeding with manual creation of
`RoundingRuleItemResult.php`, modeled directly on the verified live API response fields (see table
above) and the existing `PriceTypeItemResult.php` pattern.

### 2. `src/Services/Catalog/RoundingRule/Result/RoundingRuleResult.php`

Single-item wrapper exposing `roundingRule(): RoundingRuleItemResult` reading key `roundingRule`
(mirrors `PriceTypeResult`).

### 3. `src/Services/Catalog/RoundingRule/Result/RoundingRulesResult.php`

List wrapper exposing `getRoundingRules(): RoundingRuleItemResult[]` reading key `roundingRules`
(mirrors `PriceTypesResult`).

### 4. `src/Services/Catalog/RoundingRule/Result/RoundingRuleFieldsResult.php`

`getFieldsDescription(): array` reading key `roundingRule` (mirrors `PriceTypeFieldsResult`).

### 5. `src/Services/Catalog/RoundingRule/Result/RoundingRuleAddedBatchResult.php`

Batch-add wrapper around `ResponseData`, `roundingRule(): RoundingRuleItemResult` (mirrors
`PriceTypeAddedBatchResult`).

### 6. `src/Services/Catalog/RoundingRule/Result/RoundingRuleUpdatedBatchResult.php`

Batch-update wrapper, same shape (mirrors `PriceTypeUpdatedBatchResult`).

### 7. `src/Services/Catalog/RoundingRule/Batch.php`

Full copy of `Catalog\PriceType\Batch` structure: overrides both `determineKeyId()` (returns
`'id'`) **and** `deleteEntityItems()` (registers `catalog.roundingRule.delete` commands with
`['id' => $itemId]` instead of the base class's hardcoded `['ID' => $itemId]`), since
`Core\Batch::deleteEntityItems` does not consult `determineKeyId()` for the delete path — verified
live that the base uppercase `ID` key is rejected by `catalog.roundingRule.delete`.

### 8. `src/Services/Catalog/RoundingRule/Service/RoundingRule.php`

Methods: `add(array $fields)`, `update(int $id, array $fields)`, `get(int $id)`,
`list(array $select = [], array $filter = [], array $order = [])`, `delete(int $id)`,
`getFields()`. Same shape as `Catalog\PriceType\Service\PriceType`, constructor takes
`public Batch $batch`. `#[ApiServiceMetadata(new Scope(['catalog']))]` on the class,
`#[ApiEndpointMetadata(...)]` on each method with links from the Context section above.

### 9. `src/Services/Catalog/RoundingRule/Service/Batch.php`

Batch-mode service: `add(array $roundingRules): Generator<RoundingRuleAddedBatchResult>`,
`update(array $roundingRules): Generator<RoundingRuleUpdatedBatchResult>`,
`delete(array $roundingRuleId): Generator<DeletedItemBatchResult>`. Mirrors
`Catalog\PriceType\Service\Batch`.

### 10. `tests/Unit/Services/Catalog/RoundingRule/Service/RoundingRuleTest.php`

Unit test using `NullCore`/`NullBatch`, following the repository's standard unit test pattern
(`docs/testing.md`) — verifies each method issues the right core call without HTTP.

### 11. `tests/Integration/Services/Catalog/RoundingRule/Service/RoundingRuleTest.php`

Mirrors `PriceType/Service/PriceTypeTest.php`: `testAddGetDelete`, `testUpdate`, `testList`,
`testGetFields`. Needs a valid `catalogGroupId` — reuse the portal's base price type (`catalog.priceType.list`
filtered by `base: 'Y'`) rather than hardcoding id `1`, to avoid environment coupling; resolve it once in
`setUp()` via `Factory::getServiceBuilder()->getCatalogScope()->priceType()->list([], ['base' => 'Y'])`.

### 12. `tests/Integration/Services/Catalog/RoundingRule/Service/BatchTest.php`

Mirrors `PriceType/Service/BatchTest.php`: `testAddUpdateDelete`.

### 13. `tests/Integration/Services/Catalog/RoundingRule/Result/RoundingRuleItemResultTest.php`

Mandatory annotation/type-cast test per `docs/testing.md` and skill guidance — two methods:
`testAllFieldsAreAnnotated`, `testAllFieldsHasValidTypeCastingInMagicGetters`.

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Add:

```php
public function roundingRule(): Catalog\RoundingRule\Service\RoundingRule
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\RoundingRule\Service\RoundingRule(
            new Catalog\RoundingRule\Service\Batch(
                new Catalog\RoundingRule\Batch($this->core, $this->log),
                $this->log
            ),
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}
```

Insert after `priceTypeGroup()` (or any alphabetically sensible spot near the other `PriceType*`
methods), following existing ordering conventions in the file.

### 2. `phpunit.xml.dist`

Add after the `integration_tests_catalog_price_type_group` block (around line 546):

```xml
<testsuite name="integration_tests_catalog_rounding_rule">
    <directory>./tests/Integration/Services/Catalog/RoundingRule/</directory>
</testsuite>
```

### 3. `Makefile`

Add after `test-integration-catalog-price-type-group` target (around line 902):

```makefile
.PHONY: test-integration-catalog-rounding-rule
test-integration-catalog-rounding-rule:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_rounding_rule
```

Also add a row to the Catalog integration-test table in `docs/testing.md`.

### 4. `docs/testing.md`

Add a `make test-integration-catalog-rounding-rule` row to the "Tests — integration (Catalog)"
table (the second one, alongside `test-integration-scope-catalog`).

### 5. `CHANGELOG.md`

Add under `## Unreleased` → `### Added`, at the top of the list:

```markdown
- Added service `Services\Catalog\RoundingRule` with support methods,
  see [catalog.roundingRule.* methods](https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/index.html) ([#573](https://github.com/bitrix24/b24phpsdk/issues/573)):
    - `add` creates a new price rounding rule, with batch calls support
    - `update` updates an existing price rounding rule, with batch calls support
    - `list` gets the list of price rounding rules
    - `delete` deletes a price rounding rule, with batch calls support
    - `get` gets information about a price rounding rule by its identifier
    - `getFields` returns the description of price rounding rule fields
```

---

## Deptrac compliance

New code lives entirely under `src/Services/Catalog/RoundingRule/` (Services layer) and only
depends on `Core` (via `AbstractService`, `AbstractAnnotatedItem`, `AbstractResult`,
`Core\Batch`, `Core\Result\DeletedItemResult`, `Core\Result\DeletedItemBatchResult`,
`Core\Response\DTO\ResponseData`) — matches the pattern of every existing Catalog sub-scope, so no
new deptrac rule or `skip_violations` entry is required. `src/Services/Catalog/` and
`tests/Integration/Services/Catalog/` are already wildcard-covered in `phpstan.neon.dist`,
`.php-cs-fixer.php`, and `rector.php`.

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-rounding-rule
```
