# Plan: Add REST v3 support for note.file.list (issue #688)

Status: implemented, locally verified, and published as PR #689; implementation CI passed all 12 checks.

Issue: https://github.com/bitrix24/b24phpsdk/issues/688
Target: SDK 3.8.0, REST API v3, scope `note`, PR base `v3-dev`.
Branch: `feature/688-note-file-list`.
Base: `origin/v3-dev` at `063e256cfe992d7f258eb297722e07fad1ae3fab`.
Worktree: `/Users/mesilov/.codex/worktrees/issue688-note-file-list/b24phpsdk`.

## Context

Research date: 2026-10-28.

- Official contract: https://apidocs.bitrix24.com/api-reference/note/file/note-file-list.html
- Context7 tools are unavailable in this session. Official Bitrix24 MCP search and method lookup did not find `note.file.list`; the official English HTML page was fetched directly instead.
- `make -s oa-schema-build` succeeded in this worktree. `/note.file.list` and `bitrix.note.fileitemdto` agree with the official page.
- Request: required integer `documentId`; optional `pagination.limit` and `pagination.afterCursor`. No select, filter, order, offset, or total parameter is documented.
- Response: `result.items` contains file entities; `result.nextCursor` is an opaque object or null. Preserve the cursor unchanged, including unknown fields and scalar value types. Do not construct a cursor from the last file ID or reuse `CollectionListCursor`.
- Files are ordered by ascending ID. Pagination defaults to 50 and is capped at 200 by the server; nonpositive limits become 50. Preserve this server behavior rather than adding incompatible client validation or normalization.
- Items contain `id`, `documentId`, `name`, `size`, `mimeType`, `assetType`, `assetMarkdown`, and `createdAt`. The current `FileItemResult` lacks `createdAt`.
- Listing requires document viewing rights, not editing rights. Accessible archived and recycled documents can be listed; missing and inaccessible documents receive the same entity-not-found response. Do not claim restricted-user verification from an administrator webhook.
- Existing Note builder registration and the `integration_tests_scope_note_file` suite / `make test-integration-note-file` already exist.

## Design and alternatives

Recommended: extend the existing `File` service with a page-oriented `list()` method, use a small typed pagination value object with an opaque array cursor, and return a dedicated typed collection wrapper that reuses `FileItemResult`. Preserve all released add/get/field methods and accessors.

Alternatives considered:
1. Accept an unstructured pagination array: smaller implementation, but less discoverable than the existing Note collection pagination value object.
2. Add a cursor class with a hard-coded integer `id`: rejected because it would discard future cursor fields and contradict the published opaque-cursor contract.
3. Implement an automatic iterator: unnecessary for this issue; callers can explicitly walk pages using `getNextCursor()`.

Use an additive dedicated `FileField` service for metadata access and annotation verification; reuse the released field result wrappers. Do not move classes or remove the existing `File::fieldGet()` / `fieldList()` methods. Add `getFieldsDescription()` to the existing field list wrapper for shared metadata assertions.

## Files to Create

All PHP files use strict types, repository headers, and existing namespace/import conventions.

### 1. `src/Services/Note/File/Service/FileListPagination.php`

```php
namespace Bitrix24\SDK\Services\Note\File\Service;

final class FileListPagination
{
    /** @param array<string, mixed>|null $afterCursor */
    public function __construct(
        private readonly int $limit = 50,
        private readonly ?array $afterCursor = null,
    ) {}

    /** @return array{limit: int, afterCursor?: array<string, mixed>} */
    public function toArray(): array;
}
```

`toArray()` includes limit verbatim and omits only a null cursor. Unknown cursor fields and values must round-trip without casting or filtering.

### 2. `src/Services/Note/File/Result/FilesResult.php`

```php
namespace Bitrix24\SDK\Services\Note\File\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class FilesResult extends AbstractResult
{
    /** @return FileItemResult[] */
    public function getFiles(): array;

    /** @return array<string, mixed>|null */
    public function getNextCursor(): ?array;
}
```

Map `result.items` into existing `FileItemResult` instances and return `result.nextCursor` unchanged. An empty list returns `[]`; null ends pagination. Retain inherited `getCoreResponse()`. Existing core Added/Updated/DeletedItemResult wrappers cannot represent an entity collection plus cursor; a dedicated wrapper is required.

### 3. `src/Services/Note/FileField/Service/FileField.php`

```php
namespace Bitrix24\SDK\Services\Note\FileField\Service;

use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldResult;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldsResult;

class FileField extends AbstractService
{
    public function get(string $name, array $select = []): FileFieldResult;
    public function list(array $select = []): FileFieldsResult;
}
```

Use scope `note`, v3 endpoint metadata, `note.file.field.get` / `note.file.field.list`, and verified English documentation URLs. Preserve parameter omission and result envelopes used by existing field methods. Reuse existing result types instead of duplicating their contracts.

### 4. Unit tests

- `tests/Unit/Services/Note/File/Service/FileListPaginationTest.php`: default limit, explicit limit, null omission, and cursor preservation with unknown/nested fields and numeric strings.
- `tests/Unit/Services/Note/File/Result/FilesResultTest.php`: nonempty and empty items, item types, numeric casts, `createdAt` conversion to `CarbonImmutable`, timezone preservation, missing `createdAt` on legacy get/add payloads, non-null opaque cursor, and null final cursor.
- `tests/Unit/Services/Note/File/Result/FileFieldsResultTest.php`: index original descriptors by name; retain unknown metadata fields; empty descriptors; reject missing or invalid names rather than silently dropping descriptors.
- `tests/Unit/Services/Note/FileField/Service/FileFieldTest.php`: exact method/arguments/ApiVersion::v3, default and explicit select, and preserved field result types.
- `tests/Unit/Services/Note/NoteServiceBuilderTest.php`: `file()` and additive `fileField()` return their services, retain cached identity, and expose scope metadata.

Use PHPUnit `TestCase`, `CoversClass`, and synthetic real SDK Response objects with MockResponse. For transport assertions, capture all three `CoreInterface::call()` arguments, including API version. Verify exception propagation without changing the core error model.

### 5. `tests/Integration/Services/Note/File/Result/FileItemResultAnnotationsTest.php`

Separate shared-assertion tests named `testAllSystemFieldsAnnotated()` and `testAllSystemFieldsHasValidTypeAnnotation()`. Fetch nonempty live descriptors through `getNoteScope()->fileField()->list()->getFieldsDescription()` and compare them with `FileItemResult` annotations. Do not filter out fields to make assertions pass. If live date metadata disagrees with OpenAPI, investigate and document the precise discrepancy before applying any narrowly justified normalization.

## Files to Modify

### 1. `src/Services/Note/File/Service/File.php`

Add the following method, importing `FilesResult`:

```php
public function list(int $documentId, ?FileListPagination $pagination = null): FilesResult;
```

Call `note.file.list` with `['documentId' => $documentId]`, adding `pagination` only when supplied; always pass `ApiVersion::v3`. Add `ApiEndpointMetadata` using the canonical English URL above. Return `FilesResult`. Preserve existing methods, signatures, return types, and builder access.

### 2. `src/Services/Note/File/Result/FileItemResult.php`

Run the mandatory generator first:

```bash
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator note.file.list --stage=all
```

Inspect the generator's output paths and selectors before accepting changes. If it cannot handle this list endpoint or requires a document fixture before sampling, record the exact failure here before making a manual annotation change. Preserve the existing class, annotations, and `OpenApiEntity(entityKey: 'bitrix.note.fileitemdto')`; add the `CarbonImmutable` import and `@property-read CarbonImmutable|null $createdAt`. Nullability preserves get/add responses that omit this list-specific date. Keep `AbstractAnnotatedItem` casting rather than a manual magic getter.

### 3. `src/Services/Note/File/Result/FileFieldsResult.php`

Add `getFieldsDescription(): array`, returning raw descriptors keyed by `name`, following `EventLogFieldsResult`. Retain `getFields()`. Reject absent/invalid names using the SDK LogicException and explain that callers selecting metadata fields must include name.

### 4. `src/Services/Note/NoteServiceBuilder.php`

Add cached `fileField(): FileField` through the existing service cache. Keep `file()`, `collection()`, and `document()` unchanged.

### 5. `tests/Unit/Services/Note/File/Service/FileTest.php`

Add tests for default/explicit pagination, exact endpoint and API version, documentId mapping, returned collection parsing, endpoint metadata documentation/scope, and unchanged exception propagation. Retain existing add/get/field regression tests. Run these tests before the implementation to record the expected missing-method failure.

### 6. `tests/Integration/Services/Note/File/Service/FileTest.php`

Use disposable collection/document fixtures with cleanup even after assertion failure. Add tests for:
- An empty document yielding an empty list and null cursor.
- Two distinct uploaded files, pages of size one, unchanged cursor continuation, ascending IDs without duplicates, and eventual null cursor. Follow any non-null cursor until completion, including a possible empty terminal page; enforce a finite request bound to catch loops.
- Correct IDs/documentId, file metadata, and CarbonImmutable createdAt for nonempty items.
- Invalid/missing document error propagation. Restricted-user access checks require a separately configured restricted test identity; do not infer them from the primary identity.
- Listing an accessible archived document and a disposable recycled document. Use existing archive/delete semantics, verify them first, and clean up without touching pre-existing documents.

### 7. `tests/Integration/Services/Note/File/Result/FileItemResultTest.php`

Retain existing get-based annotation/casting regression tests. Add list-item raw key completeness and getter casting coverage using a nonempty fixture, including createdAt.

### 8. `CHANGELOG.md`

Under Unreleased / Added, append after green quality gates:

```markdown
- Added REST v3 `note.file.list` support with typed file results and cursor pagination ([#688](https://github.com/bitrix24/b24phpsdk/issues/688))
```

### Existing test registration and schema

`phpunit.xml.dist` already discovers all Note/File Service and Result integration tests. `Makefile` already provides `test-integration-note-file`; use these registrations rather than adding duplicate suites or targets. The unit suite discovers the new tests recursively.

The refreshed `docs/open-api/openapi.json` is research evidence. Review its diff before staging; avoid bundling unrelated portal schema churn with this method implementation. Record final OA coverage evidence separately.

## Deptrac compliance

Services depend on Core contracts/results, Note DTOs, attributes, and existing logger facilities. New entity results retain `AbstractAnnotatedItem`. No new Core-to-Services dependency, Infrastructure import, skip_violations entry, or architectural exception is needed.

## Implementation sequence

- [x] Refresh `origin/v3-dev`, create isolated worktree and feature branch.
- [x] Read issue, repository skill, current services, result wrappers, and test registration.
- [x] Refresh schema and verify published request/result contracts.
- [x] Check baseline unit behavior.
- [x] Owner approves this draft.
- [x] Resolve the fresh dependency installation blocker with a supported patched PHP runtime; retain composer security constraints.
- [x] Add meaningful failing unit tests for requests, collections, cursor preservation, date casting, and metadata indexing; record RED.
- [x] Run the DTO generator; record and review generated artifacts or its exact limitation.
- [x] Add minimal production changes and run focused tests to GREEN.
- [x] Add disposable live fixtures and annotation/type checks.
- [x] Run all phase 1 checks below, then phase 2 integration tests.
- [x] Update CHANGELOG, review the scoped diff, verify OA endpoint coverage, and commit scoped files.
- [x] Read the current PR template, push branch, create PR against v3-dev, attach it to this task, and wait for final CI results. Do not merge without a separate instruction.

## Verification

Run phase 1 sequentially before phase 2:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make lint-allowed-licenses
```

Phase 2:

```bash
make test-integration-note-file
make -s sdk-coverage-v3-show-uncovered
git diff --check
```

The coverage report must no longer list `note.file.list` as uncovered. Report unexecuted restricted-identity checks explicitly; they are not proven by a successful administrator call. If any mandatory test fails, diagnose before changing code or claiming success.

## Preparation evidence and limitations

- `make composer-install` failed with the pre-existing cached PHP 8.4.21 image; the current `roave/security-advisories` excludes PHP below 8.4.27 on the 8.4 line.
- Pulling the current `ghcr.io/bitrix24/b24phpsdk:php-cli` image succeeded, digest `sha256:7db2bfb7afaf462783043d1475b48e164c0517a7f28ce345df2eb3f8fb480f2d`, but its PHP 8.4.26 is still excluded. No security constraint was disabled and no dependency manifest was changed.
- For read-only schema research and a baseline check only, an external temporary Compose override mounted the primary checkout's existing vendor directory read-only. This does not establish a fresh Composer installation or final candidate verification.
- `make -s oa-schema-build`: exit 0, success marker confirmed. Private test configuration was copied to the ignored tests/.env.local without logging its contents.
- Baseline `make -s test-unit`: exit 0, 1,971 tests, 6,537 assertions, 11 PHPUnit deprecations. This is a baseline with notices, not a clean final gate.
- No production PHP files changed and no live document/file mutation was performed during preparation.

## Plan review

- Unambiguity: request keys, endpoint/version, public signatures, result envelope, opaque cursor handling, file paths, and test conditions are specified.
- Non-contradiction: add/get and field result types remain compatible; generated item changes keep the existing entity identity; existing suite/Make registrations are reused.
- No gaps: issue requirements map to service, result, pagination, metadata, annotation/casting, live integration, CHANGELOG, OA coverage, and PR/CI checks; dependency and restricted-identity limitations are explicit.

## Execution notes

- Owner explicitly approved implementation through a PR with green CI.
- Rebuilt the existing project Dockerfile with --pull in an isolated local image `b24phpsdk-688:php-cli`: PHP 8.4.27 confirmed. No repository dependency/security constraint was changed.
- Mandatory generator invocation `b24-dev:result-item-generator note.file.list --stage=all` failed before generation: `Unable to determine the current git branch`. The project PHP image lacks git, and the fallback reads `.git/HEAD`, unsupported for a linked worktree whose `.git` is a file. This concrete generator limitation permits a manual additive `createdAt` annotation change while retaining the verified OpenAPI entity and nullable compatibility with add/get. Generator source inspection also confirms no sample documentId resolver for note.file.list. No unrelated generator implementation change is included.

- Fresh `make composer-install` succeeded on PHP 8.4.27 with an independent worktree vendor. Temporary Compose override uses bridge network mode because Docker could not allocate another default subnet; no existing networks were pruned.
- Integration list use cases are isolated in `File/Service/FileListTest.php` rather than growing the existing FileTest; existing Note File test registration discovers it.
- The current core maps the v3 EntityNotFound error to BaseException, not ItemNotFoundException; the live missing-document test checks BaseException and the exact v3 error code, preserving core behavior.

- First live Note File run: 14 tests, 74 assertions, 3 failures. File listing scenarios (including pagination, archive, recycle, and missing document) passed. Failures identified concrete metadata contract discrepancies: field descriptors include editableGroups; createdAt metadata has type object whereas OpenAPI specifies string/date-time and list payloads contain ISO 8601 strings; get payloads omit createdAt.
- Ran `b24-dev:result-item-generator note.file.field.get --stage=all` before editing FileFieldItemResult; it failed with the same `Unable to determine the current git branch` limitation. Add nullable array editableGroups manually, retaining every released annotation. This is required to pass the existing live descriptor completeness test against refreshed metadata.
- Keep shared strict completeness assertions for full list payloads and metadata. For get payloads, explicitly verify their fields and values are a subset of the full list record, because createdAt is list-only; keep get-based magic-getter casting coverage.
- In the dedicated annotation type test only, assert the observed createdAt metadata type is object/string/datetime, then normalize that one descriptor to datetime following the existing EventLog annotation test precedent. Preserve the production descriptor returned by getFieldsDescription() unchanged. Actual list-item casting is checked independently against the live date value.

- Final local gates passed on independent fresh dependencies: CS Fixer, Rector, PHPStan, Deptrac, 1,991 unit tests / 6,623 assertions (same 11 baseline deprecations), license checker, and 14 live Note File tests / 84 assertions. Scoped Note CS, integration Rector, and integration PHPStan checks passed in addition to the standard targets. Both independent spec and code-quality reviews approved, including live-contract fixes.
- Standard OA coverage CLI completed: note.file.list is absent from its uncovered list. Refreshed schema stays as an uncommitted research snapshot to avoid unrelated generated changes in this feature PR.

- PR #689 created against v3-dev. Initial CI fails before checks: published PHP 8.4.26 is rejected by roave/security-advisories. The six quality workflows now build from the checked-out Dockerfile with `docker compose build --pull`, reproducing the verified local PHP 8.4.27 without weakening security constraints or publishing a shared image.

- Delivery proof: https://github.com/bitrix24/b24phpsdk/pull/689, base v3-dev, milestone 3.8.0, assignee mesilov. All 12 GitHub checks (push and pull_request) succeeded for implementation head b2f0d4c261e051a56812786961706ddf64f950a2: PHPUnit, PHPStan, PhpCsFixer, Rector, Deptrac, and composer-license-checker. No merge performed.
