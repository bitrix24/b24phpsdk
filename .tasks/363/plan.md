# Plan: Reconcile child batch pagination with Core (issue #363)

## Context

[Issue #363](https://github.com/bitrix24/b24phpsdk/issues/363), SDK 3.8.0; base `v3-dev`.
Task and Order still use the older range-based algorithm. BasketItem has a newer sequential algorithm, but rewrites arbitrary sorting to `id` and compares the direction case-sensitively. Core already provides direction-aware filters, counted fallback and protected result/ID/reference hooks.

Verified official contracts:

- [Legacy Task list](https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-list.html): `result.tasks`, response entity `id`, fixed 50-record offset pages. Do not substitute REST v3 `result.items` or its filter/pagination syntax; the MCP detail result mixes these versions, so the explicit legacy page and existing facade are the authority here.
- [Order list](https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-list.html): `result.orders`, camelCase entity fields, `next` and `total`.
- [Basket item list](https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-list.html): `result.basketItems`, camelCase fields, `order`, `filter`, `start`, `next` and `total`.

## Design

Reuse the Core paginator rather than copy it. Preserve specialized mutation methods. Give each child small envelope-specific overrides for `determineKeyId`, `extractElementsFromBatchResult` and `getReferenceFieldPath`; normalize ID-only Task order aliases at its boundary. The same extraction hook must serve counted and keyset paths. Generated batch references must include the corresponding nested envelope and lowercase result `id`.

For non-ID primary sort, use Core's counted fallback without changing the requested order. Recognize ASC/DESC case-insensitively. Preserve all filters and additional parameters on first and subsequent calls, and the public nullable signature; normalize null order/filter/select values to empty arrays before delegation. Include the result ID in selection before the first keyset request when needed for pagination. Do not broaden this task into a new batch-composition API.

## Files to Create

- `tests/Unit/Services/Task/BatchPaginationTest.php`.
- `tests/Unit/Services/Sale/Order/BatchPaginationTest.php`.
- `tests/Unit/Services/Sale/BasketItem/BatchPaginationTest.php`.
- `tests/Unit/Services/BatchPaginationTestCase.php`: a shared fixture harness only if all three tests need the same protocol simulator. Build real ResponseData with nested envelopes; parse and inspect outgoing commands instead of mocking the implementation under test.

Each test class covers empty/single/multiple pages, ascending/descending IDs, uppercase/lowercase direction, default ordering, ID gaps, missing ID in select, non-ID sort fallback, filter/additional-parameter retention, and a limit crossing a page. Use at least 101 fixture records with gaps; assert the exact result ID sequence once with no omissions/duplicates. Check nested result references and preserve field order/direction. Include a limit exactly at 50 and an empty terminal page.

## Files to Modify

- `src/Core/Batch.php`: reuse the existing getLastElementId hook for batch pages as well as the first page, so request/result ID casing can differ without duplicating the algorithm.
- `src/Services/Task/Batch.php`: replace its obsolete list algorithm with delegation and the Task envelope hooks. Preserve public method and all add/update/delete behavior. Only normalize the ID order key, not unrelated task fields.
- `src/Services/Sale/Order/Batch.php`: delegate pagination and provide Order hooks; retain mutation overrides.
- `src/Services/Sale/BasketItem/Batch.php`: replace divergent list/helper implementations with the shared algorithm plus BasketItem hooks. Preserve unrelated CRUD.
- `tests/Integration/Legacy/Services/Task/Service/BatchTest.php`: add multi-page read/order regression over tagged test-owned tasks with cleanup.
- `tests/Integration/Services/Sale/Order/Service/BatchTest.php` and `tests/Integration/Services/Sale/BasketItem/Service/BatchTest.php`: add targeted pagination regressions using owned fixtures; avoid deleting unrelated portal records.
- `tests/Unit/Core/BatchTest.php`: add a guard only if implementation changes Core hooks; otherwise retain the current core suite as a regression gate.
- `CHANGELOG.md`: Fixed entry under Unreleased linked to #363.

No generator applies: only pagination services and tests change, without annotated entity classes or builders. Existing suites and Make targets discover these tests; no new suite is needed.

## Steps

- [x] Add focused tests proving Task/Order order errors and BasketItem sort loss/case behavior before changing code.
- [x] Add child envelope hooks, delegate to Core, and verify both keyset and counted paths with the protocol simulator.
- [x] Check missing-ID selection and nested command references; make a bounded Core correction only if an existing hook cannot support the verified contract, and document that exact change here before applying it.
- [x] Run the five light gates and focused Core/legacy-task/Sale regressions; record broad-suite baseline failures separately.
- [x] Add changelog entry, review all three result envelopes, commit and prepare PR.
- [ ] Backport after merge: the issue originally affects SDK 1.x as well; preserve that branch's Task namespace layout.

## Deptrac compliance

Children depend on Core only. The shared fixture belongs to tests. No dependency from Core to any service, and no new skip_violations entry.

## Verification

```bash
docker compose run --rm -T php-cli vendor/bin/phpunit tests/Unit/Services/Task/BatchPaginationTest.php tests/Unit/Services/Sale/Order/BatchPaginationTest.php tests/Unit/Services/Sale/BasketItem/BatchPaginationTest.php tests/Unit/Core/BatchTest.php
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-core
make test-integration-legacy-task
make test-integration-scope-sale
```

All live fixture mutation must be scoped to the configured test portal and cleaned up in finally/tearDown. Planning has not run these live regressions.

## Live contract correction

The live legacy Task endpoint ignores lowercase `<id` filters (50 records for `<id: 0`) but applies uppercase `<ID` (zero records). Task requests now use ID, while response extraction/reference paths use lowercase id through the existing getLastElementId hook. The simulator models this case sensitivity. Unit coverage includes 2601 records spanning multiple batch packets; live ID-sequence assertions normalize the legacy string IDs without changing the DTO contract.

## Implementation verification outcome

The deterministic unit simulator covers 2601 rows, multiple batch packets and Task filter casing. All three owned live regressions pass (3 tests, 9 assertions). No full Sale or legacy-Task sweep is claimed; the initial broad Core run failed only its scope catalog equality test, subsequently fixed in the credentials follow-up. All shared light gates are run on the combined candidate; PR CI verifies each isolated branch. Broader scope-catalog readiness is recorded separately and PR readiness is tracked by CI.
