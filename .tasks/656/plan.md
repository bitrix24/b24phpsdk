# Plan: Add REST v3 services for main.user.history.* (issue #656)

Status: user approved implementation on 2026-09-30; implementation and local verification complete, ready for PR delivery.

## Context

- Issue: https://github.com/bitrix24/b24phpsdk/issues/656
- Target: REST v3; base `origin/v3-dev`, refreshed to `342140903f5d536325f5eeccdc3b82909d41d3f4` on 2026-09-30.
- Branch: `feature/656-user-history`. The generator requires the repository feature/<issue>-... naming pattern, so the initially prepared codex-prefixed branch was renamed before any commits.
- Worktree: `/Users/mesilov/.codex/worktrees/656-user-history/b24phpsdk`; clean when created. The original checkout and its unrelated `.tasks/578/` remain untouched by implementation.
- `make -s oa-schema-build` succeeded in this worktree. Baseline: `docs/open-api/openapi.json`.
- The official Bitrix24 MCP search and individual method-details calls did not resolve any of the seven methods. Web research found the official REST v3 overview, but no specific history-method page: https://apidocs.bitrix24.com/api-reference/rest-v3.html. Use this real English overview URL in endpoint metadata while method-specific documentation is unavailable. Do not invent URLs or event-type meanings.
- Documentation transport correction: REST v3 uses `/rest/api/`; SDK calls must explicitly pass `ApiVersion::v3`.

### Read-only live evidence collected on 2026-09-30

All seven endpoints returned HTTP 200 for valid requests against the configured test portal. Probe output retained only field names, types, counts and booleans; no credentials, user values, IP addresses or URIs.

| Contract | Evidence and implementation consequence |
|---|---|
| `main.user.history.list` | Requires a `userId` filter. Missing filter and an `id`-only filter returned 400. Return envelope is `result.items`. |
| `main.user.history.fields.list` | Requires a `historyId` filter. Missing filter returned 400. Return envelope is `result.items`. |
| List options | OpenAPI defines `select`, `filter`, `order`, and `pagination` with page/limit/offset. Tested history order by descending ID and limit 3. |
| History fields | Schema has id, userId, eventType, dateInsert, remoteAddr, userAgent, requestUri, updatedById, field. Default sample omitted `field`; explicit select of id/dateInsert/field returned it as a string. All item properties must allow omitted fields. |
| Date | Live `dateInsert` is a string; OpenAPI specifies date-time; field metadata says object. Expose `CarbonImmutable|null`. |
| Change data | Live change entries have id, historyId, field and data. Sample data was an object with before/after strings. OpenAPI places no type constraint on data, so preserve it as `mixed`, including arrays, nested values, scalars and null. |
| Tail | Requires user filter. Cursor `{field: id, value: 0, order: ASC}` returned 13 items and a string cursor value. A cursor after the latest entry returned empty items, false hasMore and a null cursor value. |
| Tail parameters | Schema has select, filter and cursor, not separate list order/pagination. Do not copy the EventLog cursor's unverified limit parameter. |
| Metadata | List/get envelopes are `result.items` / `result.item`. Two distinct metadata services are required. Preserve raw metadata types and null descriptions. Include editableGroups from the current schema. |
| Event type | Integer; no verified enumeration. Keep an integer without invented constants. |

### Design alternatives and decision

1. **Recommended: required identifiers plus SDK-built filters.** Require positive `userId`/`historyId` arguments, then prepend the mandatory equality filter and AND any additional array/FilterBuilderInterface conditions. This makes invalid omissions impossible in ordinary calls and provides early rejection for zero/negative IDs.
2. Keep only generic filter parameters and inspect their contents. This preserves EventLog's exact parameter layout but introduces a second parser for nested REST filters and weaker discoverability.
3. Forward generic filters without validation. This is the smallest wrapper, but callers discover required identifiers only through API errors.

Use option 1. Group the four responsibilities into `UserHistory`, `UserHistoryChange`, `UserHistoryField`, and `UserHistoryChangeField`. The public term “change” distinguishes field-change records from field definitions; REST method names remain unchanged.

## Files to Create

### 1. History service and results

Root: `src/Services/Main/UserHistory/`.

- `Service/UserHistory.php`: namespace `Bitrix24\SDK\Services\Main\UserHistory\Service`; extends `AbstractService`; `ApiServiceMetadata(new Scope(['main']))`.
- `Service/UserHistorySelectBuilder.php`: generated from `bitrix.main.historydto`.
- `Service/UserHistoryTailCursor.php`: immutable request object with `int|string $value`, `string $field = 'id'`, `SortOrder $order = SortOrder::Ascending`; `toArray(): array`. Safely normalize decimal ID strings to integer request values, reject overflow and empty field/value and negative integer values for ID cursors; zero is the initial position. Do not accept null as a new request position.
- `Result/UserHistoryItemResult.php`: extends `AbstractAnnotatedItem`, `OpenApiEntity('bitrix.main.historydto')`; nullable integer id/userId/eventType/updatedById, `CarbonImmutable|null` dateInsert, nullable string remoteAddr/userAgent/requestUri/field. No manual `__get()` override.
- `Result/UserHistoriesResult.php`: extends `AbstractResult`; `getUserHistoryItems(): array` returns `UserHistoryItemResult[]` from result.items.
- `Result/UserHistoryTailResult.php`: extends `UserHistoriesResult`; `hasMore(): bool`; `getCursor(): ?array` with documented shape `array{field: string, value: int|string|null}`. Preserve numeric strings and explicit null; absent cursor returns null. Never cast an absent/null value to zero.

Service signatures (imports: local result classes; Core Contracts ApiVersion/SortOrder; Filters FilterBuilderInterface):

```php
public function list(
    int $userId,
    array|UserHistorySelectBuilder $select = [],
    array|FilterBuilderInterface $filter = [],
    array $order = [],
    array $pagination = [],
): UserHistoriesResult;

public function tail(
    int $userId,
    UserHistoryTailCursor $cursor,
    array|UserHistorySelectBuilder $select = [],
    array|FilterBuilderInterface $filter = [],
): UserHistoryTailResult;

public function fields(): UserHistoryFieldsResult;
```

`fields()` delegates to the separate `UserHistoryField` metadata service's list operation and returns its metadata result. Use the same Core/logger; do not introduce a new endpoint or register a duplicate ApiEndpointMetadata for this convenience method.

### 2. Associated field changes

Root: `src/Services/Main/UserHistoryChange/`.

- `Service/UserHistoryChange.php`: namespace `Bitrix24\SDK\Services\Main\UserHistoryChange\Service`; extends AbstractService, Main scope.
- `Service/UserHistoryChangeSelectBuilder.php`: generated from `bitrix.main.historyfielddto`.
- `Result/UserHistoryChangeItemResult.php`: extends AbstractAnnotatedItem; OpenApiEntity for that schema; `int|null $id`, `int|null $historyId`, `string|null $field`, `mixed $data`. Preserve data without conversion.
- `Result/UserHistoryChangesResult.php`: extends AbstractResult; `getUserHistoryChanges(): array` of UserHistoryChangeItemResult from result.items.

```php
public function list(
    int $historyId,
    array|UserHistoryChangeSelectBuilder $select = [],
    array|FilterBuilderInterface $filter = [],
    array $order = [],
    array $pagination = [],
): UserHistoryChangesResult;

public function fields(): UserHistoryChangeFieldsResult;
```

`fields()` delegates to `UserHistoryChangeField::list()`.

### 3. Metadata services

Create each of these directories and files:

| Root under `src/Services/Main/` | Service | Single result/accessor | List result/accessors | Item |
|---|---|---|---|---|
| UserHistoryField | Service/UserHistoryField.php | Result/UserHistoryFieldResult.php; getUserHistoryField() | Result/UserHistoryFieldsResult.php; getUserHistoryFields(), getFieldsDescription() | Result/UserHistoryFieldItemResult.php |
| UserHistoryChangeField | Service/UserHistoryChangeField.php | Result/UserHistoryChangeFieldResult.php; getUserHistoryChangeField() | Result/UserHistoryChangeFieldsResult.php; getUserHistoryChangeFields(), getFieldsDescription() | Result/UserHistoryChangeFieldItemResult.php |

Namespaces mirror paths. Services extend AbstractService; wrappers extend AbstractResult; annotated metadata items extend AbstractAnnotatedItem.

Each service has `get(string $name, array $select = [])` and `list(array $select = [])`, returning its wrappers. Reject empty/whitespace names before Core calls. getFieldsDescription returns raw metadata keyed by field name; it must not alter API types.

Metadata items cover all current dtofielddto properties: name, type, title, description, validationRules, requiredGroups, filterable, sortable, editable, editableGroups, multiple, elementType. Annotations allow omissions under partial selection.

### 4. Exact endpoint mapping

Every row uses `ApiVersion::v3` in both ApiEndpointMetadata and Core::call, with Main scope.

| Class/method | Endpoint |
|---|---|
| UserHistory::list | main.user.history.list |
| UserHistory::tail | main.user.history.tail |
| UserHistoryChange::list | main.user.history.fields.list |
| UserHistoryField::get | main.user.history.field.get |
| UserHistoryField::list | main.user.history.field.list |
| UserHistoryChangeField::get | main.user.history.fields.field.get |
| UserHistoryChangeField::list | main.user.history.fields.field.list |

Normalize select builders with buildSelect(), filters with toArray(), SortOrder values with their backing strings. Omit empty optional list arguments. Keep user-supplied nested filter groups intact as a group when combining with the mandatory equality condition, so OR cannot bypass the required identifier. Unit-test both list-shaped and grouped filters.

### 5. Generator-first procedure (after approval)

Run in the task worktree after the schema refresh, with a failing contract test already in place:

```bash
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator main.user.history.list --stage=all
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator main.user.history.fields.list --stage=all
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator main.user.history.field.list --stage=all
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator main.user.history.fields.field.list --stage=all
docker compose run --rm php-cli php bin/console b24-dev:generate-select-builder bitrix.main.historydto --namespace='Bitrix24\SDK\Services\Main\UserHistory\Service' --class-name=UserHistorySelectBuilder --output=src/Services/Main/UserHistory/Service/UserHistorySelectBuilder.php
docker compose run --rm php-cli php bin/console b24-dev:generate-select-builder bitrix.main.historyfielddto --namespace='Bitrix24\SDK\Services\Main\UserHistoryChange\Service' --class-name=UserHistoryChangeSelectBuilder --output=src/Services/Main/UserHistoryChange/Service/UserHistoryChangeSelectBuilder.php
```

Source inspection shows the result-item pipeline requires REST documentation and uses method-derived paths, so it may fail for these undocumented methods or emit a different namespace. Record the actual command outcome here before manual edits. If it cannot resolve documentation, record that precise blocker and use the refreshed schema plus sanitized live evidence for manual items, as allowed by the maintainer skill. Do not fabricate documentation inputs or expand this issue into generator changes. If successful, relocate/rename generated output to the paths above and review all annotations. Keep generated payloads free of personal data.

### 6. Unit tests

Create these files under `tests/Unit/Services/Main/` with namespaces mirroring paths, PHPUnit TestCase, CoversClass and explicit meaningful assertions:

- `UserHistory/Service/UserHistoryTest.php`: list/tail endpoint, API version, mandatory IDs, invalid IDs without a Core call, select/filter builders, nested filters, SortOrder, pagination and fields delegation.
- `UserHistory/Service/UserHistorySelectBuilderTest.php`: generated field contract and selections.
- `UserHistory/Service/UserHistoryTailCursorTest.php`: zero/int/string serialization and invalid inputs.
- `UserHistory/Result/UserHistoryResultTest.php`: empty/nonempty list, partial selection, nullable field, CarbonImmutable, integer casting, tail hasMore, string/null/missing cursor, and preservation of returned cursor for a subsequent request.
- `UserHistoryChange/Service/UserHistoryChangeTest.php`: required historyId, optional arguments and field-service delegation.
- `UserHistoryChange/Service/UserHistoryChangeSelectBuilderTest.php`: schema-backed field contract.
- `UserHistoryChange/Result/UserHistoryChangeResultTest.php`: before/after values of different types, nested arrays, scalars, null, partial/empty results and no payload conversion.
- `UserHistoryField/Service/UserHistoryFieldTest.php` and `UserHistoryChangeField/Service/UserHistoryChangeFieldTest.php`: metadata get/list calls, empty-name guards, select mapping, single/list parsing, partial metadata, booleans, null description and keyed getFieldsDescription.

Use synthetic data only. Do not snapshot live user/IP/request data.

### 7. Integration and annotation tests

Create corresponding service tests under `tests/Integration/Services/Main/{UserHistory,UserHistoryChange,UserHistoryField,UserHistoryChangeField}/Service/`.

- Resolve the current webhook user's ID through the existing SDK user-profile API; select history for that user only.
- Verify populated list, explicit field selection and partial selection. Find an actual history entry with changes by bounded read-only traversal. No profile mutation or user creation is required.
- Verify tail from an initial cursor, advance using a returned non-null cursor value, and verify a request after the newest known entry. Allow concurrent new entries; assertions use monotonic progression rather than assuming a quiescent portal. Keep a last non-null checkpoint when an empty response reports a null cursor.
- Verify both metadata lists and individual get calls, including dateInsert/data metadata.
- Missing required filters are already demonstrated by research; local ID guards are exercised in unit tests. Do not deliberately mutate portal state.
- If data is unavailable, skip only data-dependent cases with a specific reason. Metadata tests still run. Final report must state skips and must not claim populated behavior was validated when skipped.

Dedicated entity annotation files:

- `tests/Integration/Services/Main/UserHistory/Result/UserHistoryItemResultAnnotationsTest.php`
- `tests/Integration/Services/Main/UserHistoryChange/Result/UserHistoryChangeItemResultAnnotationsTest.php`

Each uses `CustomBitrix24Assertions`, CoversClass for its item, and methods `testAllSystemFieldsAnnotated` and `testAllSystemFieldsHasValidTypeAnnotation`. Fetch live `fields()->getFieldsDescription()`. Completeness checks include every metadata field, even when absent from default list results. For type checks, assert the observed discrepancy first, then normalize **only in the test** dateInsert object -> datetime and data object -> any; these mappings follow OpenAPI date-time/unconstrained data. Do not change or discard the raw metadata exposed by the SDK.

Also create item casting tests under each entity's `Result/` directory and metadata `Result/UserHistoryFieldItemResultTest.php` / `Result/UserHistoryChangeFieldItemResultTest.php`. These compare raw response field coverage and getter types using shared assertions. Request all metadata descriptor properties explicitly when comparing exact field sets; nullable values remain valid.

## Files to Modify

1. `src/Services/Main/MainServiceBuilder.php`: add cached accessors userHistory(): UserHistory, userHistoryChange(): UserHistoryChange, userHistoryField(): UserHistoryField, userHistoryChangeField(): UserHistoryChangeField. Extend `tests/Unit/Services/Main/MainServiceBuilderTest.php` to validate construction and cache reuse.
2. `tests/CustomAssertions/CustomBitrix24Assertions.php`: add exact `mixed` handling to the magic-getter casting assertion, which currently treats mixed as a class name. Keep all existing concrete type checks unchanged. The metadata type assertion already supports API type `any`, so no global object mapping change is necessary.
3. `tests/Unit/CustomAssertions/CustomBitrix24AssertionsTest.php`: regression tests for mixed values (including null/nested arrays/scalars) and continued rejection of incorrect concrete types.
4. `phpunit.xml.dist`: add this suite:

```xml
<testsuite name="integration_tests_scope_main_user_history">
    <directory>./tests/Integration/Services/Main/UserHistory</directory>
    <directory>./tests/Integration/Services/Main/UserHistoryChange</directory>
    <directory>./tests/Integration/Services/Main/UserHistoryField</directory>
    <directory>./tests/Integration/Services/Main/UserHistoryChangeField</directory>
</testsuite>
```

5. `Makefile`: help entry, PHONY and target `test-integration-main-user-history` executing `docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_scope_main_user_history`.
6. `docs/main-user-history.md` (new): examples for list(userId), changes list(historyId), both metadata services, select/filter/order/pagination, safe cursor checkpoint handling, null/partial fields and documented uncertainty. Add a link from `docs/api-v3-dev.md`.
7. `CHANGELOG.md`: under existing top-level `## Unreleased` -> `### Added`, add: `- Added REST v3 user history, field-change and metadata services with cursor-based incremental reads ([#656](https://github.com/bitrix24/b24phpsdk/issues/656))`. This is a feature PR, not a release; no release coverage block is required.
8. `docs/open-api/openapi.json`: refreshed; include a diff only if the refresh changes the tracked snapshot and the change is relevant. Do not invent edits to correct portal metadata.

## Core result reuse and Deptrac compliance

Inspected `src/Core/Result/`: scalar add/update/delete wrappers do not match result.items, result.item or tail cursor envelopes. Existing FieldsResult handles legacy field maps, not v3 DTO metadata lists. Dedicated typed wrappers therefore extend AbstractResult; the tail wrapper reuses the history list wrapper. Entity and metadata records extend AbstractAnnotatedItem.

New production dependencies stay within Services and existing Core/Filters/Attributes contracts, plus Carbon and PSR logging. No reverse Core dependency, cross-scope coupling, generator dependency in runtime code, or deptrac skip is introduced.

## Implementation order and verification

1. Record approval; invoke TDD. Run generator attempts, preserve actual outcomes, and implement result contracts and cursor from failing tests.
2. Implement both entity services, both metadata services and builder accessors from failing parameter-mapping/guard tests.
3. Add examples, separate live annotation/casting tests, suite/Make target, and the narrow shared assertion fix with regression tests.
4. Run the following phase-1 checks sequentially; fix diagnosed failures before proceeding:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

5. Run `make test-integration-main-user-history` only after phase 1 passes. Report passed/skipped/failed counts accurately.
6. Measure coverage using `make -s sdk-coverage-v3-show` and `make -s sdk-coverage-v3-show-uncovered`. Compare the Main scope and overall results to the baseline; verify all seven methods disappear from the CLI-generated uncovered list. Do not use ad hoc counts as the coverage result.
7. Update CHANGELOG, run `git diff --check`, inspect untracked files and review only this issue's diff. Perform fresh verification of the final candidate; rerun affected gates if code changes.
8. Read `.github/PULL_REQUEST_TEMPLATE.md`, commit with an imperative English message referencing #656, push and create the PR against v3-dev. Attach the PR to this chat; select author/milestone through the maintainer workflow. Poll CI after every push until terminal, resolve relevant failures, and report the final state. No automatic merge.

## Plan self-review

- Unambiguity: all seven methods map to explicit services, paths, signatures and envelope accessors; identifier/filter and cursor rules are explicit.
- Non-contradiction: entity changes and metadata have distinct names; fields() delegates to metadata list; nullable/mixed annotations match partial responses; no generic object-type weakening.
- No gaps: generator fallback, builders, examples, metadata/annotation/casting tests, quality gates, coverage, changelog, PR creation and terminal CI are included.

## Preparation results

- Worktree OpenAPI refresh: passed.
- Live probes: all seven endpoints reachable; populated history and associated changes observed; missing-ID-filter errors and empty tail observed.
- Baseline `make -s test-unit`: 1,679 tests, 5,288 assertions, no failed tests; 11 existing deprecations. Log: `/tmp/b24-656-baseline-unit.log`.
- Baseline `make -s sdk-coverage-v3-show`: 98 of 314 methods covered (31.21%); 216 uncovered; Main scope 5 of 39 (12.82%).
- Expected coverage increment on this unchanged snapshot: seven additional covered methods. Final figures must come from the CLI, not this expectation.
- At the preparation checkpoint, production code was not yet changed.

## Implementation evidence

- Entity service/result/cursor tests and builder registration tests written before production code; expected RED captured in `/tmp/b24-656-red.log` (missing feature classes/accessors).
- All four required result-item generator commands attempted with `--stage=all`; each exits 1 with `Unable to determine the current git branch`. The Docker image has no git executable, and its fallback reads `.git/HEAD`; a linked worktree has a `.git` pointer file. A read-only overlay of the real worktree git directory also fails because Docker cannot mount a directory over that file. No result artifacts were generated. Per the maintainer exception, manual entity/metadata items use the refreshed OpenAPI schema and sanitized live evidence. No generator production code changes are part of #656.
- For metadata `getFieldsDescription()`, a partial select omitting name must fail explicitly with an SDK BaseException explaining that name is required; do not silently drop descriptors or invent numeric names.

- Review O1 reproduced by three failing request-mapping tests: sparse keys from generated allSystemFields/repeated selections serialize as JSON objects. New entity list/tail boundaries reindex select with array_values; no shared builder behavior changed.

- First live run: 26 tests, 165 assertions, one error during cursor progression. Read-only probes confirmed integer ID request values return HTTP 200 while equivalent strings return HTTP 400 (id requires int), although response cursor values are strings. The response wrapper still preserves raw values; the request cursor now normalizes decimal strings to integers with round-trip overflow detection. Regression tests cover leading zeroes, zero, PHP_INT_MAX, overflow, and unchanged response-string/null semantics.
- Review O2: all new live tests now share a ServiceBuilderFactory configured with NullLogger, independent of .env log levels; ignored local level restored to 100 for validation. This prevents payload/credential output even when the suite is run directly with repository defaults.

## Final local verification

- Both select builders were generated successfully from the refreshed OpenAPI schema. The documented worktree-related result-item generator exception applies only to the four result items.
- `make -s lint-cs-fixer`, `make -s lint-rector`, `make -s lint-phpstan`, and `make -s lint-deptrac`: passed. A scoped CS Fixer run also covered all new/changed PHP files because the baseline finder excludes parts of Main.
- `make -s test-unit`: passed, 1,782 tests and 5,718 assertions. The same 11 deprecations are present in the baseline run; none were added by this change.
- `make -s test-integration-main-user-history`: passed against the configured live portal, 26 tests and 167 assertions, zero skips. No payload or credential logs were emitted with the local log level set to 100.
- `make -s sdk-coverage-v3-show`: 105 of 314 methods covered (33.44%); 209 uncovered; Main scope 12 of 39 (30.77%). All seven implemented methods are absent from the CLI-generated uncovered report.
- Independent specification and code-quality reviews approved the final implementation after O1 and O2 were fixed and rechecked.
- CHANGELOG updated after both quality-gate phases passed. No tracked OpenAPI snapshot changes were produced by the refresh.
