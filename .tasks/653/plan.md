# Plan: Complete main.eventlog.* implementation and validation for REST v3 (issue #653)

Status: implementation delivered in PR #657; local quality gates, live validation, and initial PR CI passed on 2026-09-30.

**Goal:** Complete the existing event-log implementation and validate all five REST v3 contracts without adding duplicate services.
**Architecture:** Keep `MainServiceBuilder::eventLog()` and `eventLogField()`. Move event-log result casting into `AbstractAnnotatedItem`, preserve typed IP addresses, and use the existing field service for metadata-driven contract tests.
**Tech stack:** PHP 8.4/8.5, PHPUnit, CarbonImmutable, Darsyn IP, Typhoon Reflection, Docker-backed Make targets.
**Execution:** After approval, use `superpowers:executing-plans` and `superpowers:test-driven-development` in this chat, with a concrete result after each stage.

## Context

- Issue: https://github.com/bitrix24/b24phpsdk/issues/653 (open; no labels at research time).
- API version and PR base were already selected by the owner: REST v3 / `v3-dev`.
- Isolated worktree: `/Users/mesilov/.codex/worktrees/653-eventlog-validation/b24phpsdk`.
- Branch: `codex/653-eventlog-validation` (Codex branch convention).
- Fresh base: `origin/v3-dev`, `35195bf8b26c5ae3a0e183e45acd97f7e55caecc`; clean before research artifacts.
- The original checkout remains on `v3-dev`; its untracked `.tasks/578/` is preserved.
- `make oa-schema-build` succeeded on 2026-09-30, including in this worktree. Snapshot SHA-256: `f6be0a558c3395295cb39b094e5215b8458dea31482a6df1f98a60d6a7409402`.
- Official Bitrix24 MCP method details were read for all five endpoints. Current English pages were opened separately because MCP links partly use old paths and localized hosts.
- `make -s sdk-coverage-v3-show`: 314 OA methods, 98 covered, 216 uncovered, 31.21%; scope `main`: 39 OA methods, 5 covered. This measures endpoint presence, not runtime correctness.
- Baseline `make -s test-unit`: exit 0; 1592 tests, 5036 assertions, 11 deprecations. These deprecations predate implementation.
- Research logs: `schema-build.log`, `coverage-baseline.log`, `unit-baseline.log` beside this plan. Do not commit credentials, raw event-log data, vendor files, or runtime logs.
- No live event-log suite has been run during planning. Live permissions, populated records, and portal field representations remain to be validated after the light quality gates.

### Endpoint contract audit

| REST method | Existing SDK entry point | Request | Response | Audit result |
|---|---|---|---|---|
| `main.eventlog.get` | `EventLog::get(int $id, array\|EventLogSelectBuilder $select = [])` | positive ID; select array or builder | `result.item` -> `EventLogResult::eventLogItem()` | v3 passed explicitly; positive-ID guard exists; missing mapping tests |
| `main.eventlog.list` | `EventLog::list($select, $filter, $order, $pagination)` | array/builder select; array/FilterBuilderInterface filter; enum/string sort; page/limit/offset | `result.items` -> `EventLogsResult::getEventLogItems()` | v3 passed explicitly; empty optional arrays omitted; missing mapping tests |
| `main.eventlog.tail` | `EventLog::tail($select, $filter, EventLogTailCursor $eventLogTailCursor)` | select, filter, cursor with field/order/value/limit | `result.items` -> `EventLogsResult::getEventLogItems()` | v3 passed explicitly; cursor defaults id/ASC/50; value 0 is a valid starting point |
| `main.eventlog.field.get` | `EventLogField::get(string $name, array $select = [])` | nonempty name; optional select | `result.item` -> `EventLogFieldResult::eventLogField()` | guard exists; existing tests only cover wrapper type and empty name |
| `main.eventlog.field.list` | `EventLogField::list(array $select = [])` | optional select | `result.items` -> `EventLogFieldsResult::getEventLogFields()` | implemented; no keyed `getFieldsDescription()` adapter yet |

All five methods have `ApiEndpointMetadata(..., ApiVersion::v3)`. The two builder accessors exist and cache instances. `EndpointUrlFormatter` implements v3 `/rest/api/` routing, including webhook transformation. The maintainer skill's generic statement that v1/v3 share the same URL is stale; retain the actual SDK v3 routing and test it through explicit `ApiVersion::v3` assertions.

Official sources (retrieved 2026-09-30):

- https://apidocs.bitrix24.com/api-reference/event-log/main-eventlog-get.html
- https://apidocs.bitrix24.com/api-reference/event-log/main-eventlog-list.html
- https://apidocs.bitrix24.com/api-reference/event-log/main-eventlog-tail.html
- https://apidocs.bitrix24.com/api-reference/event-log/main-eventlog-field-get.html
- https://apidocs.bitrix24.com/api-reference/event-log/main-eventlog-field-list.html

The refreshed OA schema is the baseline when documentation examples omit fields. It contains 13 event-log properties and `editableGroups` in the common field-descriptor schema. `EventLogSelectBuilder` covers the entity fields and selects `id` by default. Do not regenerate this builder without a proven mismatch.

### Confirmed gaps and compatibility decisions

1. `EventLogItemResult` extends `AbstractItem`; its getter indexes absent `timestampX`, `userId`, `guestId`, and `remoteAddr` keys directly. Selected-out fields must return null without PHP warnings. Preserve int IDs, CarbonImmutable dates, nullable values, and `Multi` IP addresses.
2. `AbstractAnnotatedItem` does not cast `Multi`. Add an exact annotated-class match in its existing casting path; preserve already typed `Multi`, convert IPv4/IPv6 strings with `Multi::factory`, return null for empty nullable IP, and preserve invalid-address exceptions. Do not add an event-log-specific `__get` override.
3. `EventLogFieldItemResult` extends `AbstractItem` and omits `editableGroups`. Align this touched result class with `AbstractAnnotatedItem` and add `array|null $editableGroups`, checking live output and raw response keys.
4. Event-log unit mapping tests and dedicated annotation tests are missing. Existing integration list/tail tests can pass without checking a record when the response is empty.
5. Field metadata has no keyed accessor. Add `EventLogFieldsResult::getFieldsDescription()` using raw `result.items`, keyed by each descriptor's `name`. Use `eventLogField()->list()->getFieldsDescription()` in tests; keep the field service separate instead of inventing a legacy `fields()` endpoint.
6. Shared type-annotation assertions expect a string for `remoteAddr`. Add a narrowly scoped compatibility rule for `EventLogItemResult::remoteAddr`: require actual API type `string` and annotation `Multi|null`. Do not globally accept IP classes for arbitrary string fields. Normalize a live `timestampX` descriptor from string to datetime only when its documented datetime contract and metadata justify it; retain an assertion on the observed raw descriptor type.
7. Existing date filter helpers accept `DateTime|string`, rejecting `CarbonImmutable`. Broaden the shared helper to `DateTimeInterface|string`, including `between`, preserving mutable dates and strings. No new string-only date argument will be added to event-log services.
8. Existing service documentation links use stale paths; field-service attributes also use the localized host. Replace links for all five methods with the verified English URLs above.

### Alternatives considered

- **Recommended: annotation-based casting plus small, tested compatibility additions.** Meets repository conventions and preserves IP objects; touches common casting and assertions, so requires full unit regression coverage.
- Keep the manual getter and fix missing array keys only. Smaller diff, but retains duplication and violates the current result-item convention.
- Generate fresh services and wrappers. Rejected: duplicates working API entry points and creates unnecessary public API churn.

Keep current public service signatures, argument order, cursor int value/defaults, response accessors, and request omission behavior unless a failing contract test proves a defect. Do not add speculative pagination restrictions or cursor-field validation; the server validates supported fields and cursor/filter conflicts. DateTimeInterface support is additive. Unselected fields return null; tests use a selected positive `id` for full-item casting assertions. No promise is made that a selected-out identifier remains an integer.

## Files to Create

### 1. `tests/Unit/Services/Main/Service/EventLogTest.php`

```php
namespace Bitrix24\SDK\Tests\Unit\Services\Main\Service;
use Bitrix24\SDK\Services\Main\Service\EventLog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
#[CoversClass(EventLog::class)]
class EventLogTest extends TestCase
{
    public function testGetMapsArrayAndBuilderSelections(): void;
    public function testGetRejectsNonPositiveIdWithoutCallingCore(): void;
    public function testListMapsFilterOrderAndPagination(): void;
    public function testListOmitsEmptyOptionalArguments(): void;
    public function testTailPreservesEmptyFilterAndCursorZero(): void;
    public function testTailMapsBuilderArgumentsAndDescendingCursor(): void;
}
```

Use parameterized cases for array/builder combinations, SortOrder/string values, and zero/negative IDs. Mock `CoreInterface::call` with exact method, payload, and `ApiVersion::v3`; return a real synthetic `Response` fixture with `result.item` / `result.items` and assert decoded values, including an empty items array. Test propagation of core exceptions. Passing characterization tests for existing methods do not require production edits.

### 2. `tests/Unit/Services/Main/Result/EventLogItemResultTest.php`

Namespace `Bitrix24\SDK\Tests\Unit\Services\Main\Result`; cover `EventLogItemResult` with parameterized full/partial payloads. Assert int numeric strings; nullable and empty user/guest IDs; timezone-preserving CarbonImmutable timestamp; empty/null timestamp; IPv4 and IPv6 `Multi`; empty/null/missing remote address; invalid IP exception; safe selected-out fields. Assert iterator/raw data remain the original API values. Convert warnings into failures for selected-out keys.

### 3. `tests/Integration/Services/Main/Result/EventLogItemResultAnnotationsTest.php`

```php
namespace Bitrix24\SDK\Tests\Integration\Services\Main\Result;
use Bitrix24\SDK\Services\Main\Result\EventLogItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
#[CoversClass(EventLogItemResult::class)]
class EventLogItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;
    public function testAllSystemFieldsAnnotated(): void;
    public function testAllSystemFieldsHasValidTypeAnnotation(): void;
}
```

These two tests fetch nonempty metadata from `eventLogField()->list()->getFieldsDescription()`. Pass keys to `assertBitrix24AllResultItemFieldsAnnotated` and descriptors to `assertBitrix24AllResultItemFieldsHasValidTypeAnnotation`. Do not require event-log records for metadata tests and do not drop the IP/date fields from validation.

### 4. `tests/Integration/Services/Main/Result/EventLogItemResultTest.php`

Same result namespace, `#[CoversClass(EventLogItemResult::class)]`, shared assertions, exactly two methods: `testAllFieldsAreAnnotated()` and `testAllFieldsHasValidTypeCastingInMagicGetters()`. Fetch a record using all system fields; compare raw keys and assert actual getter casts. If the journal has no records, report an explicit skip for these record-dependent tests only. API errors must not become skips.

### 5. `tests/Unit/Services/Main/EventLogField/Result/EventLogFieldsResultTest.php`

Namespace `Bitrix24\SDK\Tests\Unit\Services\Main\EventLogField\Result`. Cover descriptor indexing, raw metadata preservation, typed descriptor access, `editableGroups`, and empty `result.items`. Use synthetic v3 responses.

### 6. `tests/Unit/Filters/Types/DateTimeFieldConditionBuilderTest.php`

Namespace `Bitrix24\SDK\Tests\Unit\Filters\Types`. Parameterize eq/neq/gt/gte/lt/lte and between over CarbonImmutable, DateTimeImmutable, DateTime and strings. Assert DATE_ATOM serialization with timezone offset and unchanged existing string behavior through `EventLogFilter::timestampX()`.

## Files to Modify

1. `src/Core/Result/AbstractAnnotatedItem.php`: exact `Multi` casting branch as described above. No generic arbitrary-class factory mechanism.
2. `tests/Unit/Core/Result/AbstractAnnotatedItemTest.php`: independent annotated IP stub, both address families, nullable empty/missing values, preconstructed Multi, invalid addresses, and a string field proving no accidental conversion. Retain enum tests.
3. `src/Services/Main/Result/EventLogItemResult.php`: extend `AbstractAnnotatedItem`, retain imports/annotations and `OpenApiEntity`, remove manual `__get` after regression tests are red.
4. `src/Services/Main/EventLogField/Result/EventLogFieldItemResult.php`: extend `AbstractAnnotatedItem`, add `editableGroups` annotation; preserve the 11 existing annotations.
5. `src/Services/Main/EventLogField/Result/EventLogFieldsResult.php`: additive public `getFieldsDescription(): array`, documented as `array<string, array<string, mixed>>`; index raw items by name.
6. `tests/CustomAssertions/CustomBitrix24Assertions.php`: narrow typed-IP allowance; preserve unknown-type failures and all other mappings.
7. `tests/Unit/CustomAssertions/CustomBitrix24AssertionsTest.php`: positive typed-IP metadata case; reject wrong API types and wrong annotation types; ordinary strings still require string annotations.
8. `src/Filters/Types/DateTimeFieldConditionBuilder.php`: replace DateTime type/import/instanceof checks with DateTimeInterface in all seven methods, retaining string acceptance and DATE_ATOM formatting.
9. `src/Services/Main/Service/EventLog.php` and `src/Services/Main/EventLogField/Service/EventLogField.php`: update `@see`/`@link` and endpoint attribute URLs to the verified English `api-reference/event-log/` pages; document supported string sort directions without changing behavior.
10. `tests/Unit/Services/Main/EventLogField/Service/EventLogFieldTest.php`: replace wrapper-only evidence with exact get/list parameter and v3-version assertions, include selected/default descriptors and invalid empty name.
11. `tests/Unit/Services/Main/MainServiceBuilderTest.php`: verify accessor types and cached identity for both event-log services.
12. `tests/Integration/Services/Main/Service/EventLogTest.php`: use CarbonImmutable filters; test partial-selection access without warnings, get/list consistency, and cursor continuation IDs relative to the prior page in ascending and descending order. Make record-dependent empty-journal outcomes explicit skips; preserve successful empty-list behavior in unit tests.
13. `tests/Integration/Services/Main/EventLogField/Service/EventLogFieldTest.php`: verify keyed metadata and compare selected get descriptors to list descriptors. Existing `EventLogFieldItemResultTest.php` remains the live raw-key/casting check and may be adjusted only for the refreshed descriptor contract.
14. `phpunit.xml.dist`: append the following to existing `integration_tests_scope_main_eventlog`, retaining its existing three files:

```xml
<file>./tests/Integration/Services/Main/Result/EventLogItemResultAnnotationsTest.php</file>
<file>./tests/Integration/Services/Main/Result/EventLogItemResultTest.php</file>
```

15. `Makefile`: no edit required; `test-integration-main-eventlog` already runs this suite and covers both services. Unit files are discovered by the existing unit suite.
16. `CHANGELOG.md`: after successful gates, add under the existing `## Unreleased` / `### Fixed` (create Fixed if absent):

```markdown
- Fixed REST v3 event-log partial-result casting, preserved typed IP addresses, completed field metadata, and added request and live annotation validation ([#653](https://github.com/bitrix24/b24phpsdk/issues/653))
```

Add a separate concise Changed entry for additive immutable-date filter support if shipped. Do not rename the repository's existing Unreleased heading. No agent or skill files are part of this issue.

### Required generators and explicit limitations

Before manually editing the two result-item files after approval, attempt:

```bash
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator main.eventlog.get --stage=all
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator main.eventlog.field.get --stage=all
```

The current command cannot directly regenerate the existing classes correctly: `resolveGenerationTarget()` maps the methods to `Services/Main/Eventlog/Result/EventlogItemResult.php` and `Services/Main/Eventlog/Field/Result/FieldItemResult.php`, while actual paths are the existing Main/Result and Main/EventLogField/Result classes. Its default sample parameters are empty (get requires id; field.get requires name), and its default response path is empty rather than `item`. Documentation URL resolution also encounters the existing stale paths. Record actual failures/output before manual edits; do not expand this issue into generator redesign. If generation succeeds, review output and transfer the relevant annotations into existing files, preserving `Multi|null`, `OpenApiEntity`, namespaces and nullability. Remove only newly generated duplicate files owned by this task, after recording their provenance. No select or item builder changes are planned.

## Implementation sequence

- [x] Refresh schema, load issue, inspect official docs and existing source.
- [x] Create clean isolated branch and record unit/coverage baseline.
- [x] Compare alternatives, draft plan, self-review against acceptance criteria.
- [x] Obtain explicit owner approval of this plan.
- [x] Invoke TDD; add characterization tests for all five existing endpoints and builder registration; run the focused Main unit directory.
- [x] Add failing tests for partial results and IP casting; run generator attempts; implement annotation-based results and field metadata accessor; run affected result/core unit tests.
- [x] Add failing immutable-date tests; implement additive DateTimeInterface support; run the focused filter suite and event-log mapping cases.
- [x] Add narrow shared assertion tests and implementation; add dedicated live annotation and actual-casting tests; register both files in the existing suite and correct docs links.
- [x] Run the five light gates in order; diagnose each failure using systematic-debugging before fixing.
- [x] Run the existing event-log integration target; investigate real failures; explicitly record environmental errors and skips.
- [x] Update CHANGELOG and verify the final diff and new files; commit only issue-owned paths.
- [x] Create PR against `v3-dev` using the current template, attach it to this chat, and wait for terminal CI after every push.

Use RED/GREEN/REFACTOR for actual behavior changes. Preserve passing existing behavior with characterization tests; do not manufacture failures by breaking working service methods.

## Deptrac compliance

`Services` continues to depend on `Core`; `Core` gains only an already installed external Darsyn IP dependency. The shared filter class continues depending on the filter abstraction and native DateTimeInterface. Test-only assertions may refer to the event-log result class and do not introduce source-layer dependencies. Do not add `skip_violations` entries or service imports into Core.

## Verification

Run from the isolated worktree. Do not expose tests/.env.local or raw portal logs in reports.

Focused RED/GREEN checks use `make test-file path=<file-or-directory>` for the files above. Then run sequentially:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-main-eventlog
make -s sdk-coverage-v3-show
git diff --check
git status --short
```

Only run integration after all five light gates succeed. Record actual test/assertion counts, deprecations, skipped cases and their reasons. Empty event-log data can justify explicit record-dependent skips; it cannot count as successful get/tail record validation. Missing credentials, admin access, main scope, or endpoint availability must be reported as environmental limitations, not hidden by broad skip handlers or claimed as passed gates. A blocked live gate pauses PR delivery under the maintainer workflow until resolved or the owner explicitly authorizes a documented exception.

Before PR creation, invoke verification-before-completion and use fresh results for the final code state. Repeat checks only if relevant code changed, results are stale, or failures remain unresolved. Read `.github/PULL_REQUEST_TEMPLATE.md` fresh, select the current nearest open 3.x milestone, determine assignee from branch commit metadata, use `Closes #653` outside tables, push and create the PR. Poll `gh pr checks <number> --watch` or GitHub status tools to terminal state; report failures with names and causes. No merge is authorized by this plan.

## Acceptance criteria coverage and plan review

| Issue criterion | Planned evidence |
|---|---|
| Refreshed OA and audit | Context, schema hash, five-row contract audit |
| All endpoints/routing/registration | Exact Core call tests; existing v3 URL formatter; builder cache tests; live suite |
| get/list/tail unit tests | New EventLogTest with argument combinations, errors, and v3 envelopes |
| Dedicated live annotation test | EventLogItemResultAnnotationsTest and both mandated method names |
| Casting/nullability/date/IP | Result unit tests, base IP tests, immutable filter tests, actual-item live assertions |
| Live validation/environment reporting | Existing expanded make target with explicit skip/error accounting |
| Quality gates and changelog | Five ordered light gates, heavy suite, linked Fixed entry |
| PR and terminal CI | PR to v3-dev, attachment, status polling after every push |

Plan review:
- Unambiguity: exact existing API signatures, response keys, paths, compatibility behavior, commands and gate order are identified.
- Non-contradiction: field metadata remains in its existing service, IP objects survive the base-class migration, and metadata tests are separated from record-dependent casting tests.
- No gaps: every acceptance criterion maps to implementation or verification evidence; generator limitations and environmental outcomes are explicit.

### Generator execution evidence

Both required generator attempts exited 1 before generation: `Unable to determine the current git branch`. Docker mounts the worktree without the parent Git metadata referenced by its `.git` file. No PHP source was generated. Together with the target/sample/envelope limitations documented above, this prevents using the generator for these existing classes; proceed with reviewed manual edits, without changing generator infrastructure.

### Review O1

Confirmed by RED tests: `getFieldsDescription()` must reject descriptors with omitted, null or empty `name` instead of collapsing them into one key. Added an explicit SDK LogicException and documented the accessor requirement; partial descriptors remain available through `getEventLogFields()`.

### Live metadata evidence

First live suite: 17 tests, 73 assertions, 1 failure, no skips. The metadata assertion exposed `timestampX.type=object` on the portal, while refreshed OpenAPI defines `string` with `format=date-time`; actual result-item date casts passed. The dedicated metadata test now explicitly accepts the observed object descriptor (alongside datetime/string representations) for timestampX only and normalizes it to datetime for the shared annotation assertion. Other field mappings remain strict.

## Final local verification

- CS Fixer passed on the first run; final run found 0 fixable files.
- Rector passed after variable-name and redundant-condition formatting corrections in the new tests.
- PHPStan passed after annotating the deliberate empty-name negative test with a targeted argument.type suppression.
- Deptrac passed on the first run: 0 violations, warnings, or errors.
- Full unit suite: 1663 tests, 5250 assertions, 11 pre-existing deprecations; exit 0.
- Live event-log suite: 17 tests, 87 assertions, no skips, errors, or warnings. All five endpoints exercised against the configured portal; both cursor directions, metadata, partial select, and typed results verified.
- Independent review: O1 addressed and re-reviewed; no other actionable findings.
- No new service methods or duplicate services were introduced; existing event-log signatures and builder registration remain intact.
- CHANGELOG updated under Unreleased. Logs are local, ignored, and excluded from the commit.

## Delivery

- Pull request: https://github.com/bitrix24/b24phpsdk/pull/657
- Base: `v3-dev`; head: `codex/653-eventlog-validation`; milestone: `3.7.0`; assignee: `mesilov`.
- Implementation commit: `d1d27ff9eafd34c042722a77954e58f9e890e4f1`; verified identical locally and on origin.
- Initial terminal CI: all 6 checks passed — Rector lint checks, PHPStan, PHPUnit tests, PhpCsFixer, Deptrac, composer-license-checker.
- This documentation-only completion record is pushed separately; the agent must also await its terminal CI before the final report. Current authoritative check state is attached to the PR.
- PR remains open for review; no merge performed. The original checkout and `.tasks/578/` were preserved.
