# CRM call-list support implementation plan (issue #208)

**Goal:** Add all six `crm.calllist.*` methods and batch add/update/list wrappers to SDK 3.8.0.

**Architecture:** Register `CRM\CallList\Service\CallList` through `CRMServiceBuilder::callList()`. Use typed response wrappers and annotated entity items. Reuse core operation result contracts and batch transport; do not copy the old PR's generic pagination algorithm.

**Tech stack:** PHP, PHPUnit, Docker Compose, Bitrix24 REST API, existing SDK generators and quality gates.

## Context and approved scope

- Issue: https://github.com/bitrix24/b24phpsdk/issues/208
- Old draft: https://github.com/bitrix24/b24phpsdk/pull/213 (closed, unmerged). It supplies historical context, not current API truth.
- Base: `origin/v3-dev` at `1027b1e6`; branch: `feature/208-crm-calllist`.
- The user approved the six-method design and explicitly requested batch wrappers on 2026-10-28.
- Official Bitrix24 MCP documentation was fetched for every method below. Context7 is unavailable in this session.
- `make oa-schema-build` succeeded on 2026-10-28. Its refreshed snapshot contains no `crm.calllist.*` paths; legacy REST documentation and live responses remain necessary for these methods. SDK release 3.x does not imply REST API v3 for every endpoint.
- Baseline `make test-unit`: 2,101 tests, 9,565 assertions, exit 0, 11 existing deprecations.
- Read-only live probes succeeded: list accepts uppercase and lowercase parameter names; list returns a flat result array without `next` or `total`; status list returns four objects with `ID`, `NAME`, `SORT`, `STATUS_ID`. Only one existing call list was present, so this probe does not establish behavior for more than 50 call lists.
- No `crm.calllist.fields`, `field.get`, `field.list`, or delete method is documented. Do not invent a public fields/delete endpoint or claim live metadata validation exists. Validate annotations against raw live records and the documented contracts, with type-casting assertions; record this exception explicitly in the PR.

## Public service contract

Namespace: `Bitrix24\SDK\Services\CRM\CallList\Service`.

```php
class CallList extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger);
    public function add(string $entityType, array $entities, ?int $webformId = null): AddedItemResult;
    public function get(int $id): CallListResult;
    public function list(array $order = [], array $filter = [], array $select = []): CallListsResult;
    public function update(int $listId, string $entityType, array $entities, ?int $webformId = null): UpdatedItemResult;
    public function statusList(): CallListStatusesResult;
    public function getItems(int $listId, array $filter = []): CallListItemsResult;
}
```

| Method | Exact request payload | Response |
|---|---|---|
| `crm.calllist.add` | `ENTITY_TYPE`, `ENTITIES`, optional `WEBFORM_ID` | scalar integer ID; reuse `AddedItemResult::getId()` |
| `crm.calllist.get` | `ID` | flat object; `CallListResult::callList()` |
| `crm.calllist.list` | `ORDER`, `FILTER`, `SELECT` | flat objects; `CallListsResult::getCallLists()` |
| `crm.calllist.update` | `LIST_ID`, `ENTITY_TYPE`, `ENTITIES`, optional `WEBFORM_ID` | scalar boolean; reuse `UpdatedItemResult::isSuccess()` |
| `crm.calllist.statuslist` | empty payload | flat objects; `CallListStatusesResult::getStatuses()` |
| `crm.calllist.items.get` | `LIST_ID`, `FILTER` | flat objects; `CallListItemsResult::getItems()` |

`ENTITY_TYPE` accepts `CONTACT` or `COMPANY`; document this with array-shape/string-literal PHPDoc. `ENTITIES` is `list<int>`. Omit `WEBFORM_ID` only when null; preserve explicit zero. Document that omitting the form on update clears the associated form according to the remote contract. Keep unknown status codes as strings so custom statuses work.

Documentation attributes use `https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-calllist-<action>.html`, including `crm-calllist-items-get.html` and `crm-calllist-statuslist.html`.

## Files to create

### Entity items and response wrappers

Directory: `src/Services/CRM/CallList/Result/`.

- `CallListItemResult.php` extends `AbstractAnnotatedItem`: `ID: int`, `ENTITY_TYPE_ID: int`, `ENTITY_TYPE: string|null` (not returned by list), `DATE_CREATE: CarbonImmutable`, `WEBFORM_ID: int|null`, `CREATED_BY_ID: int`. Partial SELECT records may omit fields; only selected fields are consumed.
- `CallListParticipantItemResult.php` extends `AbstractAnnotatedItem`: `ID: int`, `STATUS: string`, `ENTITY_TYPE: int`.
- `CallListStatusItemResult.php` extends `AbstractAnnotatedItem`: `ID: int`, `NAME: string`, `SORT: int`, `STATUS_ID: string`.
- `CallListResult.php` extends `AbstractResult`; `callList(): CallListItemResult` wraps the flat result object.
- `CallListsResult.php` extends `AbstractResult`; `getCallLists(): array` maps the flat array to call-list items.
- `CallListItemsResult.php` extends `AbstractResult`; `getItems(): array` maps the flat array to participant items.
- `CallListStatusesResult.php` extends `AbstractResult`; `getStatuses(): array` maps the flat array to status items.

Try the required result-item generator first after plan approval, before hand-written annotated items:

```bash
docker compose run --rm -T php-cli php bin/console b24-dev:result-item-generator crm.calllist.get --stage=all
docker compose run --rm -T php-cli php bin/console b24-dev:result-item-generator crm.calllist.items.get --stage=all
docker compose run --rm -T php-cli php bin/console b24-dev:result-item-generator crm.calllist.statuslist --stage=all
```

Record each outcome in this plan. If no OpenAPI entity, unsupported docs/result-item blocks, missing sample parameters, or incorrect generation target prevents generation, record the exact failure before manual edits. Review and move successful output to the canonical namespace/class names above. Do not add unrelated generator changes to this feature.

### Batch services

- `src/Services/CRM/CallList/Service/Batch.php`: public `add(array $callLists): Generator`, `update(array $callLists): Generator`, `list(array $order = [], array $filter = [], array $select = [], ?int $limit = null): Generator`. Annotate yields as `AddedItemBatchResult`, `UpdatedItemBatchResult`, and `CallListItemResult` respectively.
- `src/Services/CRM/CallList/Batch.php`: a narrow `Core\Batch` subclass only for adapting the flat update payload. Override `updateEntityItems()` with the same signature, register flat API arguments via the inherited command queue, and delegate chunking/execution/error propagation to inherited transport. Preserve input-key association where the existing batch API promises it. Do not copy generic list pagination code.
- Batch add accepts `list<array{ENTITY_TYPE: 'CONTACT'|'COMPANY', ENTITIES: list<int>, WEBFORM_ID?: int}>`; use inherited `addEntityItems()` with flat payloads, without a `fields` wrapper.
- Batch update accepts `array<int, array{ENTITY_TYPE: 'CONTACT'|'COMPANY', ENTITIES: list<int>, WEBFORM_ID?: int}>`, keyed by call-list ID. Map each key to `LIST_ID`, retaining the remaining flat fields.
- Batch list reuses the shared traversable-list path only after a deterministic contract test confirms correct handling of an absent total/next with more than 50 returned records. It must yield all returned records, respect `$limit`, and preserve requested ordering without inventing offset pagination. If that core helper fails this contract, implement a bounded call-list-specific generator over the native list result in the service facade; do not change unrelated Core behavior.
- Empty batch input yields no requests. No batch delete operation is introduced.

### Unit tests (write and run RED before each implementation)

Directory: `tests/Unit/Services/CRM/CallList/`.

- `Service/CallListTest.php`: exact payload/result contract tests for all six endpoints; form omitted vs explicit zero; get ID versus update LIST_ID; empty lists; filtered participants; sparse selections; API exception propagation; scope/endpoint metadata; builder cache.
- `Result/CallListResultTest.php`: flat object/list parsing; numeric-string IDs; `CarbonImmutable` creation date; absent nullable form/entity-type fields; participant ENTITY_TYPE integer; status STATUS_ID string; empty arrays.
- `Service/BatchTest.php`: flat add/update command arguments, no `fields` nesting, input-ID mapping, yields of correct result classes, empty input, limits and ASC/DESC ordering, >50 records without total metadata, and transport/API errors.
- `BatchTest.php`: real inherited batch transport with mocked Core; split more than 50 queued add/update requests into legal batches, assert every input appears once, and validate scalar per-command ID/boolean normalization.

Example payload expectation to implement first:

```php
$core->expects($this->once())->method('call')
    ->with('crm.calllist.update', [
        'LIST_ID' => 12,
        'ENTITY_TYPE' => 'CONTACT',
        'ENTITIES' => [31, 32],
        'WEBFORM_ID' => 0,
    ])->willReturn($response);
self::assertTrue($service->update(12, 'CONTACT', [31, 32], 0)->isSuccess());
```

Use real `Response`/`ResponseData` fixtures where result parsing is under test. Mock `CoreInterface` only at the transport boundary.

### Integration tests

Directory: `tests/Integration/Services/CRM/CallList/`.

- `Service/CallListTest.php`: CONTACT and COMPANY add/get/list/getItems/update lifecycle, status list, unknown ID/status errors and optional form omission. Create SDK-owned contact/company fixtures; remove only those owned fixtures during cleanup.
- `Service/BatchTest.php`: add/update multiple SDK-owned lists using the batch facade; verify resulting IDs, membership and typed results with ordinary reads; compare batch list IDs/order/filter/limit to ordinary list results.
- `Result/CallListItemResultAnnotationsTest.php`, `CallListParticipantItemResultAnnotationsTest.php`, `CallListStatusItemResultAnnotationsTest.php`: separate raw live field completeness and shared type-cast assertion methods for each class. There is no remote field metadata endpoint; name the raw-response tests accurately and document the metadata-test exception.
- Call-list deletion is unavailable in the documented API. Keep created lists to a small deterministic test set, clean up owned participant contacts/companies, and document any retained list IDs without portal credentials. Do not delete unrelated CRM activities or existing lists by guessing their identities.

## Files to modify

1. `src/Services/CRM/CRMServiceBuilder.php`: add cached `callList(): CallList\Service\CallList`; inject the service batch facade backed by the narrow adapter above.
2. `phpunit.xml.dist`: add:

```xml
<testsuite name="integration_tests_crm_calllist">
    <directory>./tests/Integration/Services/CRM/CallList/</directory>
</testsuite>
```

3. `Makefile`: add:

```make
.PHONY: test-integration-crm-calllist
test-integration-crm-calllist:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_crm_calllist
```

4. `CHANGELOG.md`, Unreleased, Added:

```markdown
- Added typed CRM call-list services for all six `crm.calllist.*` methods and batch add/update/list support ([#208](https://github.com/bitrix24/b24phpsdk/issues/208))
```

5. Keep the refreshed OpenAPI snapshot as research evidence. Review its diff before committing; do not publish generated credentials or unrelated portal-specific schema churn.

## Deptrac compliance

New Services classes depend only on Core, their CRM namespace, Carbon and PSR logging. Tests may use shared test helpers. Do not introduce Infrastructure imports into Services or add deptrac skip violations.

## Execution checklist

- [x] Fetch issue and old PR; read current repository rules and official API documentation.
- [x] Confirm SDK 3.8.0 target and include batch wrappers as requested.
- [x] Create isolated worktree/branch, install dependencies, copy ignored test configuration without exposing secrets.
- [x] Refresh OpenAPI; run baseline unit tests and read-only live shape probes.
- [x] Written plan approved by the user on 2026-10-28 ("бери в работу").
- [x] Run required generators and record the linked-worktree branch-resolution failure.
- [x] Write failing tests for each service/result/batch contract; observe RED before production changes.
- [x] Implement service/results/batch adapters and builder registration; observe GREEN.
- [x] Add integration suite, annotation/type tests, and Makefile target.
- [x] Run phase 1 checks in the order below and fix any failures based on their cause.
- [x] Run focused live integration suite after phase 1 is green.
- [ ] Update changelog, inspect final diff, and commit with issue reference.
- [ ] Read `.github/PULL_REQUEST_TEMPLATE.md`; push and open PR against `v3-dev` with `Closes #208`, milestone 3.8.0, validation evidence and metadata/cleanup limitations.
- [ ] Attach PR to this chat; wait for terminal CI status and report actual result. Do not merge without authorization.

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-crm-calllist
make sdk-coverage-v1-show
```

Use the standard CLI coverage target; absence from the REST v3 OpenAPI snapshot is not evidence of missing legacy coverage. Unit tests and all linters must pass; compare existing 11 deprecations to baseline. Report live API discrepancies explicitly rather than skipping assertions or inventing metadata.

## Plan self-review

- Unambiguity: fixed all six payloads, result accessors, service/batch signatures and exact file paths.
- Non-contradiction: SDK 3.x branch and legacy REST contracts are distinguished; flat batch updates are separated from generic fields-based updates; raw-response annotation tests do not claim unavailable metadata.
- No gaps: includes generators, RED/GREEN tests, registration, integration suite, changelog, quality gates, PR template, CI and cleanup limitations.

## Generator execution (2026-10-28)

All three required `--stage=all` commands above were attempted before creating annotated SDK files. Each exits 1 with `Unable to determine the current git branch`: Docker mounts the worktree but its `.git` points to an unmounted host metadata directory. No PHP output was generated. Manual annotated items are allowed under the documented generator-failure exception; implement them from official method contracts and live response verification without changing unrelated generator infrastructure.

## Implementation preparation

GitHub issue #208 is assigned to `mesilov`. Quality configuration also needs the new CallList directories in `.php-cs-fixer.php`, `rector.php`, and `phpstan.neon.dist` so every new file is covered by the required tools. Integration tests are drafted with shared fixture cleanup; retained call-list IDs are reported on stderr because no list-delete method is documented.

## Static-analysis and review refinements

PHPStan exposed an existing incorrect `array<string,mixed>` annotation for SELECT in `Core\Contracts\BatchOperationsInterface` and `Core\Batch`. Correct it to `array<array-key, mixed>`; ordered field-name lists have integer keys. This is documentation only and does not alter Core behavior. The company integration fixture uses the core `crm.company.add` call because the existing Company service incorrectly documents its PHONE multifield as a string; do not broaden this feature into that unrelated service's annotation repair.

Spec review found the planned transport-exception coverage missing from batch unit tests. Added add/update cases asserting the original transport exception is preserved as the previous exception.

## Live API findings (first integration run)

First live run: 14 tests, 63 assertions, 3 errors. All errors came from incorrect test assumptions, not request/response mismatches: (1) explicitly sending `WEBFORM_ID=0` produces `WEBFORM_ERROR` in native and batch calls; successful lifecycle tests must omit the optional parameter and separate tests assert zero is forwarded and rejected; (2) a valid STATUS filter with no matching participants returns `ACCESS_ERROR`, not `[]`. The SDK preserves both remote errors. Both cases now have explicit integration assertions, without skipped tests. All raw-field annotation and type-cast tests passed on the first run. Created contacts and company were deleted. Retained list IDs from the first run: 4, 6, 8, 10, 12, 14.

Second live run: 17 tests, 82 assertions, one failure: an absent web-form association is returned as null, not integer zero. The result item already correctly declares `int|null`; corrected that lifecycle assertion to null. Retained call-list IDs: 16, 18, 20, 22, 24, 26, 28. Review also hardened native zero-form rejection tests by consuming `getId()`/`isSuccess()` so lazy HTTP-200 error parsing is exercised if the portal changes its HTTP status convention.

## Final local verification (2026-10-28)

- `make lint-cs-fixer`: passed.
- `make lint-rector`: passed after adding required blank lines in new integration tests; existing deprecated-rule configuration warnings remain unchanged.
- `make lint-phpstan`: passed after correcting the existing SELECT PHPDoc key type and using core transport for the company PHONE fixture.
- `make lint-deptrac`: passed, zero violations/errors.
- `make test-unit`: passed, 2,135 tests, 11,647 assertions, 11 pre-existing deprecations (baseline unchanged).
- `make test-integration-crm-calllist`: passed, 17 tests, 82 assertions. All SDK-owned contacts/companies were deleted. Third-run retained list IDs: 30, 32, 34, 36, 38, 40, 42.
- `make sdk-coverage-v1-show ARGS='--scope=crm --format=json'`: passed; configured-portal CRM coverage 231/333 methods, 69.37%. This is portal-specific evidence, not a universal API coverage claim.
- Spec compliance review: approved after adding explicit transport-failure tests. Code-quality review: no remaining findings; native zero-form tests consume result accessors to exercise lazy error parsing.
- No `OpenApiEntity` key is invented for legacy call-list records absent from the snapshot.

The required OpenAPI refresh produced broad unrelated changes across global scopes; it is preserved locally at `/private/tmp/b24-208-openapi-refreshed.json` for research, and excluded from this feature PR. The checked-in snapshot was restored to the branch baseline after verification. No CallList endpoint is present in either snapshot.
