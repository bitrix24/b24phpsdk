# Plan: Add support for `call.followup.*` methods (issue #603)

## Context

Bitrix24 REST v3 exposes a new "BitrixGPT Follow-up" family of methods under module `call`:

- `call.followup.list` — cursor-paginated list of Follow-ups for a date range / filter
- `call.followup.get` — single Follow-up by `callId`
- `call.followup.field.list` — metadata of all Follow-up fields (for building `select`)
- `call.followup.field.get` — metadata of a single Follow-up field by name

Author: Dmitriy Ignatenko <algonexys@gmail.com>. Issue: https://github.com/bitrix24/b24phpsdk/issues/603.
Already on branch `feature/603-add-call.followup-v3` (created by the user beforehand; no branch/commit/push/PR
actions will be performed by the agent per explicit user instruction — CLAUDE.md forbids git operations and commits
in this session).

### REST contract (gathered via Bitrix24 REST API MCP + `docs/open-api/openapi.json` after `make oa-schema-build`)

- Required scope: **`call`** (confirmed by the "Follow-up звонков: обзор методов" overview article — not `telephony`).
- `call.followup.get(callId, select=[], mentionFormat='bb')` → `result.item`. Errors include
  `ERROR_BATCH_METHOD_NOT_ALLOWED` — **batch is explicitly forbidden**.
- `call.followup.list(filter, select=[], order={startDate:desc}, pagination={limit, afterCursor}, mentionFormat='bb')`
  → `result.items[]`, `result.hasMore`, `result.afterCursor` (`{startDate, id}` cursor, not the `{position,id}` shape
  used by `note.collection.list`). `filter` is documented as required.
- `call.followup.field.list(select=[])` / `call.followup.field.get(name, select=[])` → generic field-descriptor DTO
  `bitrix.rest.dtofielddto` (confirmed via OpenAPI `$ref` on both paths) — **identical shape** to the DTO already
  used by `Note\Collection\Result\CollectionFieldItemResult` and `Timeman\RecordField\Result\RecordFieldItemResult`:
  `name, type, title, description, validationRules, requiredGroups, filterable, sortable, editable, multiple, elementType`.
- Root Follow-up object (`bitrix.call.followupdto` schema + `call.followup.get` doc's "all fields" example, both
  independently confirmed via `b24-dev:generate-select-builder bitrix.call.followupdto`, 18 fields):
  `callId, callType, initiatorId, startDate, endDate, durationSeconds, uuid, language, version, participants,
  outcomes, createdAt, tracks, transcription, overview, summary, insights, evaluation`.
  - `participants[]` and `tracks[]` are fixed-shape repeated objects (documented field-by-field in the official
    "Поля Follow-up звонков" / fields.html article) → modeled as typed `array<XxxItemResult>`.
  - `transcription/overview/summary/insights/evaluation` are deeply nested, partly dynamic-keyed (e.g.
    `evaluation.criteria` is a map keyed by criterion code) AI-block objects whose presence depends on `outcomes`
    → modeled as `array|null` (raw), consistent with existing SDK precedent for open-ended payloads
    (`IMBot\Event\Result\EventItemResult::$data` is `array<string, mixed>`). `AbstractAnnotatedItem` only supports
    auto-casting `array<FooItemResult>` for **lists** of a fixed item class, not single nested typed objects, so a
    fully-typed object graph for these 5 blocks is not achievable through the existing base class without inventing
    a new casting mechanism — out of scope for this issue.

### Generator usage (per skill's mandatory-generator rule)

- `php bin/console b24-dev:generate-select-builder bitrix.call.followupdto ...` — **used successfully**, produced
  `FollowUpSelectBuilder.php`. Manually removed one generator bug unrelated to this issue: the shared
  `SelectBuilderCodeGenerator`/`SelectBuilder.tpl.php` unconditionally emits `$this->select[] = 'id';` in the
  constructor; `bitrix.call.followupdto` has no `id` field (its key is `callId`), so the line was deleted from the
  generated file. Not fixing the shared template/generator itself — that's a pre-existing generator issue outside
  this issue's scope, and every other current consumer of this generator happens to have a genuine `id` field.
- `php bin/console b24-dev:result-item-generator call.followup.get --stage=all` — **cannot be used**:
  `ApiEndpointDocumentationUrlResolver` resolves doc URLs solely from `#[ApiEndpointMetadata]` attributes already
  present in `src/Services/**`, so it fails for a brand-new method family with no existing attribute
  ("documentation URL could not be resolved" — a bootstrap/chicken-and-egg limitation). Additionally,
  `call.followup.get` / `call.followup.list` responses are **not** `$ref`-linked in the OpenAPI schema (`result` is
  an untyped `{"type":"object"}`), so even after bootstrapping the URL, the OpenAPI half of the payload merge would
  be empty and the tool would fall back to REST-docs markdown alone, with no control over target
  namespace/class/path (it derives `Services\Call\Followup\Result\GetItemResult` from the method name — doesn't
  match this SDK's `Telephony` domain grouping or the shared field-metadata convention). The verify/apply stages
  are moot regardless: the live dev webhook (`tests/.env.local`) returns `ERROR_METHOD_NOT_FOUND` for
  `call.followup.list` (confirmed via direct curl) — the BitrixGPT Follow-up feature is not enabled on this test
  portal, so no live sample response is obtainable to verify against.
  → **All four `*ItemResult` classes are hand-authored**, using the OpenAPI `bitrix.call.followupdto` /
  `bitrix.rest.dtofielddto` schemas plus the official field-reference article as the source of truth, mirroring the
  proven `Note\Collection` (root entity + cursor pagination) and `Timeman\RecordField` (dedicated field-metadata
  service) patterns byte-for-byte in structure.
- `b24-dev:generate-item-builder` — not applicable; none of the four methods write data.

### Correction: the dev webhook does support `call.followup.*` (initial curl probe was misleading)

An early direct `curl` probe against `.../rest/1/<token>/call.followup.list` returned `ERROR_METHOD_NOT_FOUND`,
which was read as "feature not enabled on this portal". That was wrong: REST v3 methods live under a **different**
URL prefix, `/rest/api/<user>/<token>/<method>` (not `/rest/<user>/<token>/<method>` used by v1) — the SDK's
`ApiClient` builds this automatically for `ApiVersion::v3` calls. Once the integration suite ran through the SDK
(which uses the correct v3 prefix), `call.followup.list`, `call.followup.field.list` and `call.followup.field.get`
all returned real `200` responses from the live portal. `call.followup.field.get('callId')` on the live portal
returned the exact 11-field descriptor shape used for `FollowUpFieldItemResult`, confirming it hand-for-hand.
`call.followup.get`/the `FollowUpItemResult` annotation tests are skipped (not failed) via `markTestSkipped()`
because this dev portal has no historical calls with a completed BitrixGPT Follow-up yet — same "no fixture data"
pattern already used by `tests/Integration/Services/Main/Service/EventLogTest.php::testGet`. Both phases of the
quality gate are green; see Verification below for actual results.

### Naming / structure decisions

- Domain grouping: `src/Services/Telephony/FollowUp/` (root entity) and `src/Services/Telephony/FollowUpField/`
  (field metadata), registered on the existing `TelephonyServiceBuilder` — consistent with how `Call`, `ExternalCall`,
  `ExternalLine`, `Voximplant` are all grouped under `Telephony` regardless of their exact REST module/scope name.
- No `Batch.php` for either entity: `call.followup.get` explicitly forbids batch usage
  (`ERROR_BATCH_METHOD_NOT_ALLOWED`), and both field-metadata methods forbid it too. `call.followup.list` is
  cursor-paginated (not offset/`start`-based), matching `Note\Collection\Service\Collection`, which also ships
  with no `Batch.php`.
- `filter` on `list()` is accepted as `array|FilterBuilderInterface` (required, no default) — the API only
  documents a `startDate.from/to` range example plus prose mentioning participant filtering, with no complete
  structured filter-field list, so a raw/flexible filter (same convention as `Collection::delete()/update()`) is
  used rather than a bespoke typed filter DTO.
- `mentionFormat` (`bb`/`html`/`none`, shared by `list()` and `get()`) → new backed enum
  `FollowUp\Service\FollowUpMentionFormat`.
- `pagination`/`afterCursor` on `list()` → new `FollowUpListPagination` / `FollowUpListCursor`, modeled after
  `CollectionListPagination` / `CollectionListCursor`, but with cursor fields `{startDate: string, id: int}`
  (not `{position, id}` — different shape per `call.followup.list` docs).

## Files to Create

### `src/Services/Telephony/FollowUp/Result/FollowUpItemResult.php`

```php
/**
 * @property-read int                                    $callId
 * @property-read int                                    $callType
 * @property-read int                                    $initiatorId
 * @property-read CarbonImmutable                        $startDate
 * @property-read CarbonImmutable|null                   $endDate
 * @property-read int                                    $durationSeconds
 * @property-read string                                 $uuid
 * @property-read string|null                             $language
 * @property-read int                                    $version
 * @property-read array<FollowUpParticipantItemResult>   $participants
 * @property-read array                                   $outcomes
 * @property-read CarbonImmutable                        $createdAt
 * @property-read array<FollowUpTrackItemResult>          $tracks
 * @property-read array|null                              $transcription
 * @property-read array|null                              $overview
 * @property-read array|null                              $summary
 * @property-read array|null                              $insights
 * @property-read array|null                              $evaluation
 */
#[OpenApiEntity(entityKey: 'bitrix.call.followupdto', selectBuilder: FollowUpSelectBuilder::class)]
class FollowUpItemResult extends AbstractAnnotatedItem {}
```

(`array<X>` single-arg form confirmed as the one `AbstractAnnotatedItem::castArrayValue()`'s
`/array<(?<itemClass>[^,>]+)>/` regex actually parses — matches the live precedent
`DocumentTreeItemResult::$children`. The two-arg `array<int, X>` form used elsewhere in the codebase, e.g.
`ContactItemResult::$COMPANY_IDS`, would make the regex capture `int` as the class name and silently fall back to
a raw array — avoided here.)

### `src/Services/Telephony/FollowUp/Result/FollowUpParticipantItemResult.php`

`@property-read int $userId, string $name, int $talkedSeconds, string|null $avatar, string|null $workPosition`

### `src/Services/Telephony/FollowUp/Result/FollowUpTrackItemResult.php`

`@property-read int $trackId, string $type, int $fileId, int $diskFileId, int $duration, int $fileSize,
string $fileName, string $mimeType, int $callId, string $relUrl, string $url, CarbonImmutable $dateCreate`

### `src/Services/Telephony/FollowUp/Result/FollowUpResult.php`

`followUp(): FollowUpItemResult` from `result.item`.

### `src/Services/Telephony/FollowUp/Result/FollowUpsResult.php`

`getFollowUps(): FollowUpItemResult[]` from `result.items`; `hasMore(): bool` from `result.hasMore`;
`getNextCursor(): ?FollowUpListCursor` from `result.afterCursor`.

### `src/Services/Telephony/FollowUp/Service/FollowUpListCursor.php`, `FollowUpListPagination.php`

Same shape as `CollectionListCursor`/`CollectionListPagination` but cursor fields are `{startDate: string, id: int}`.

### `src/Services/Telephony/FollowUp/Service/FollowUpMentionFormat.php`

```php
enum FollowUpMentionFormat: string
{
    case Bb = 'bb';
    case Html = 'html';
    case None = 'none';
}
```

### `src/Services/Telephony/FollowUp/Service/FollowUpSelectBuilder.php` — already generated (see above), hand-fixed.

### `src/Services/Telephony/FollowUp/Service/FollowUp.php`

```php
#[ApiServiceMetadata(new Scope(['call']))]
class FollowUp extends AbstractService
{
    #[ApiEndpointMetadata('call.followup.list', 'https://apidocs.bitrix24.com/api-reference/telephony/follow-up/call-followup-list.html', '...', ApiVersion::v3)]
    public function list(
        array|FilterBuilderInterface $filter,
        array|FollowUpSelectBuilder $select = [],
        array $order = ['startDate' => 'desc'],
        ?FollowUpListPagination $pagination = null,
        ?FollowUpMentionFormat $mentionFormat = null,
    ): FollowUpsResult { /* core->call('call.followup.list', [...], ApiVersion::v3) */ }

    #[ApiEndpointMetadata('call.followup.get', 'https://apidocs.bitrix24.com/api-reference/telephony/follow-up/call-followup-get.html', '...', ApiVersion::v3)]
    public function get(
        int $callId,
        array|FollowUpSelectBuilder $select = [],
        ?FollowUpMentionFormat $mentionFormat = null,
    ): FollowUpResult { /* core->call('call.followup.get', [...], ApiVersion::v3) */ }
}
```

### `src/Services/Telephony/FollowUpField/Result/FollowUpFieldItemResult.php`, `FollowUpFieldResult.php`, `FollowUpFieldsResult.php`

Byte-for-byte structural copy of `Timeman\RecordField\Result\*` (field/fields accessors named `field()` /
`getFields()` per the `Note\Collection` naming).

### `src/Services/Telephony/FollowUpField/Service/FollowUpField.php`

Byte-for-byte structural copy of `Timeman\RecordField\Service\RecordField` (`get(string $name, array $select = [])`,
`list(array $select = [])`), scope `call`, doc links to `call-followup-field-get.html` / `call-followup-field-list.html`.

### Tests

- `tests/Unit/Services/Telephony/FollowUp/Service/FollowUpTest.php` — `NullCore`-based instantiation tests +
  a stub-`CoreInterface` capture test per method (mirrors `CollectionTest` unit style): asserts REST method name and
  captured payload shape for `list()` (pagination/order/mentionFormat forwarding) and `get()` (select/mentionFormat
  forwarding), plus a guard test if any input validation is added.
- `tests/Unit/Services/Telephony/FollowUpField/Service/FollowUpFieldTest.php` — mirrors `RecordFieldTest` unit style.
- `tests/Integration/Services/Telephony/FollowUp/Service/FollowUpTest.php` — mirrors `Note\Collection`'s
  `CollectionTest` integration style (list with cursor pagination, get by id).
- `tests/Integration/Services/Telephony/FollowUp/Result/FollowUpItemResultTest.php` — mandatory annotation +
  type-cast test pair (per `docs/testing.md` "Result-item annotation tests" + skill template), using `get()`
  without `select` (full field set) as the source of raw keys.
- `tests/Integration/Services/Telephony/FollowUpField/Service/FollowUpFieldTest.php`,
  `tests/Integration/Services/Telephony/FollowUpField/Result/FollowUpFieldItemResultTest.php` — mirrors
  `RecordField`'s two integration files exactly.

## Files to Modify

### `src/Services/Telephony/TelephonyServiceBuilder.php`

Add `followUp(): Telephony\FollowUp\Service\FollowUp` and `followUpField(): Telephony\FollowUpField\Service\FollowUpField`
accessors, same caching pattern as `call()` / `externalLine()`.

### `phpunit.xml.dist`

Add one composite suite (mirrors `integration_tests_scope_timeman_record`'s multi-directory style):

```xml
<testsuite name="integration_tests_scope_telephony_followup">
    <directory>./tests/Integration/Services/Telephony/FollowUp/Service/</directory>
    <directory>./tests/Integration/Services/Telephony/FollowUp/Result/</directory>
    <directory>./tests/Integration/Services/Telephony/FollowUpField/Service/</directory>
    <directory>./tests/Integration/Services/Telephony/FollowUpField/Result/</directory>
</testsuite>
```

(already covered transitively by the existing `integration_tests_scope_telephony` suite too — this one is the
finer-grained target for `make test-integration-telephony-followup`.)

### `Makefile`

```make
.PHONY: test-integration-telephony-followup
test-integration-telephony-followup:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_scope_telephony_followup
```

### `.php-cs-fixer.php`

Add two narrow finder entries (Telephony as a whole is currently **not** in the cs-fixer finder at all — adding the
whole tree would newly flag pre-existing, unrelated files; scoping to just the two new directories avoids that):

```php
    ->in(__DIR__ . '/src/Services/Telephony/FollowUp/')
    ->in(__DIR__ . '/src/Services/Telephony/FollowUpField/')
```

### `phpstan.neon.dist`, `rector.php`

No change needed — `src/` (phpstan) and `src/Services/Telephony` + `tests/Integration/Services/Telephony`
(both tools) already cover the new code transitively.

### `CHANGELOG.md`

Add under the current `## X.Y.Z Unreleased` → `### Added`:

```markdown
- Added service `Services\Telephony\FollowUp` and `Services\Telephony\FollowUpField` with support for
  `call.followup.*` methods, see [call.followup.* methods](https://apidocs.bitrix24.com/api-reference/telephony/follow-up/index.html):
    - `call.followup.list` gets a cursor-paginated list of Follow-ups for a period/filter
    - `call.followup.get` gets a single Follow-up by `callId`
    - `call.followup.field.list` gets the list of available Follow-up fields
    - `call.followup.field.get` gets the description of a single Follow-up field by name
  ([#603](https://github.com/bitrix24/b24phpsdk/issues/603))
```

## Deptrac compliance

New code lives entirely in the `Services` layer and only imports from `Core` (`AbstractAnnotatedItem`,
`AbstractResult`, `Scope`, `ApiVersion`, exceptions), `Services\AbstractService` /`AbstractSelectBuilder`
(same layer), and `Bitrix24\SDK\Filters\FilterBuilderInterface` (already used the same way by
`Note\Collection\Service\Collection`, so no new violation class). No cross-service imports. No new
`skip_violations` entries expected.

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-telephony-followup   # green; 2 sub-tests skipped — no historical Follow-up data on this portal
```

No git/branch/commit/push/PR actions will be taken (per explicit user instruction).
