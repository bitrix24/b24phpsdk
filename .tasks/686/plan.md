# Plan: Add REST v3 support for note.document.* (issue #686)

## Context

Issue: https://github.com/bitrix24/b24phpsdk/issues/686 — milestone 3.8.0, API version **v3**,
base branch `v3-dev` (`29fa8ad1`), work branch `feature/686-note-document-v3`.

The SDK already exposes `Services\Note\Document\Service\Document` (REST v3) with `add`, `get`,
`update`, `archive`, `delete`, `fieldGet`, `fieldList`, `treeList`, `treeField*`, `search*`.
The October 2026 API update adds four methods that are missing from the SDK:
`note.document.list`, `note.document.move`, `note.document.restore`, `note.document.unarchive`.
All released methods and public accessors stay unchanged.

### Contracts (refreshed `docs/open-api/openapi.json` + live portal probe on 2026-10-28)

| Method | Request | Response | Live notes |
|---|---|---|---|
| `note.document.list` | `filter` (REST v3 condition list), `pagination{limit<=200 (default 50), afterCursor}`, `select` | `result.items[]` of `bitrix.note.documentitemdto`, `result.nextCursor` (object or null) | Object-style filter `{"collectionId":1}` is refused with `INVALIDFILTEREXCEPTION`; condition list `[["collectionId","=",46],["state","in",["trashed","live"]]]` works. Without `state` only live documents are returned. Selecting `markdown` is refused (400). `nextCursor` is opaque; an empty `items` with a non-null cursor is not the end of the walk. |
| `note.document.move` | `id` (required), `collectionId`, `parentId`, `position` | `result.item` (document as saved) | Moving a parent to another collection moves its children (child keeps `parentId`, gets new `collectionId`). Stored `position` may differ from the requested one. |
| `note.document.restore` | `id` (required), `collectionId` | `result.item` | Works on a document deleted with `note.document.delete` (deletion = recycle bin). A non-trashed document -> `BITRIX_REST_V3_EXCEPTION_ENTITYNOTFOUNDEXCEPTION`. `NOTE_RESTORE_TARGET_REQUIRED` when the original collection is gone. |
| `note.document.unarchive` | `id` (required) | `result.result: bool` | A non-archived document -> `ENTITYNOTFOUNDEXCEPTION`. |

`ENTITYNOTFOUNDEXCEPTION` is not mapped in `ApiLevelErrorHandler`, so the SDK throws a plain
`BaseException`. Changing the mapping is out of scope here (it affects every v3 scope); tests assert
`BaseException`.

### Document item shape

`note.document.field.list` (live) and `bitrix.note.documentitemdto` now contain five fields that are
missing from `DocumentItemResult`: `contentUpdatedAt` (date-time, nullable — `null` on the live
portal for a document that was never edited), `state` (`live|archived|trashed`), `isArchived`,
`isTrashed`, `isOrphan` (bool). Live metadata reports date fields as type `object` (same as
`note.file`, see `FileItemResultAnnotationsTest`). `markdown` is absent from list items, so it stays
`string|null`.

### Design decisions

1. **List filter**: `array|FilterBuilderInterface $filter`, consistent with `delete()`/`update()`,
   plus a typed `DocumentFilter extends AbstractFilterBuilder` with `id()`, `collectionId()`,
   `parentId()`, `state()` (pattern: `Main\Service\EventLogFilter`). No generator exists for filters.
2. **List pagination**: `DocumentListPagination(int $limit = 50, ?array $afterCursor = null)` with
   an opaque cursor array — the same contract as `FileListPagination` (#688), because the docs say
   the cursor fields are internal. A typed cursor (as in `CollectionListCursor`) is rejected.
3. **List result**: `DocumentsResult` with `getDocuments(): DocumentItemResult[]` and
   `getNextCursor(): ?array` (mirrors `FilesResult`).
4. **move/restore** return the existing `DocumentResult` (same `result.item` envelope).
5. **unarchive** returns a new `UnarchivedDocumentResult::isSuccess()` that reads `result.result`,
   like `ArchivedDocumentResult`. Core `UpdatedItemResult` reads `result[0]`, which does not match
   the v3 envelope, so it is not reused.
6. **State enum**: `Services\Note\Document\Result\DocumentState: string` (`live`, `archived`,
   `trashed`); `DocumentItemResult::$state` is annotated as `DocumentState` so `AbstractAnnotatedItem`
   casts it. The filter keeps `StringFieldConditionBuilder` (`->state()->eq(DocumentState::archived->value)`).
7. **Field metadata**: the four new methods add no `*.field.*` endpoints. The released
   `fieldGet()/fieldList()` stay in `Document`; `DocumentFieldsResult` gets
   `getFieldsDescription()` (copy of the `FileFieldsResult` contract) so the annotation test can
   read live metadata.
8. **Doc links**: `https://apidocs.bitrix24.com/api-reference/note/document/note-document-<name>.html`
   (as in the issue and in `note.file.list`).

---

## Files to Create

### 1. `src/Services/Note/Document/Result/DocumentState.php`

```php
enum DocumentState: string
{
    case live = 'live';
    case archived = 'archived';
    case trashed = 'trashed';
}
```

### 2. `src/Services/Note/Document/Service/DocumentFilter.php`

```php
class DocumentFilter extends AbstractFilterBuilder
{
    public function id(): IntFieldConditionBuilder;
    public function collectionId(): IntFieldConditionBuilder;
    public function parentId(): IntFieldConditionBuilder;
    public function state(): StringFieldConditionBuilder;
}
```

### 3. `src/Services/Note/Document/Service/DocumentListPagination.php`

`final readonly class` with `__construct(int $limit = 50, ?array $afterCursor = null)` and
`toArray(): array{limit: int, afterCursor?: array<string, mixed>}` (null cursor omitted).

### 4. `src/Services/Note/Document/Result/DocumentsResult.php`

`getDocuments(): DocumentItemResult[]`, `getNextCursor(): ?array` (opaque, returned unchanged).

### 5. `src/Services/Note/Document/Result/UnarchivedDocumentResult.php`

`isSuccess(): bool` reading `result.result`.

### 6. Unit tests

- `tests/Unit/Services/Note/Document/Service/DocumentListPaginationTest.php` — default limit,
  cursor passthrough, null cursor omitted.
- `tests/Unit/Services/Note/Document/Result/DocumentsResultTest.php` — items mapped to
  `DocumentItemResult`, `state` cast to `DocumentState`, nullable `contentUpdatedAt`, cursor
  passthrough / null on the last page, empty items with non-null cursor.
- `tests/Unit/Services/Note/Document/Result/UnarchivedDocumentResultTest.php`.
- `tests/Unit/Services/Note/Document/Service/DocumentFilterTest.php` — produces
  `[["collectionId","=",46],["state","in",["archived","trashed"]]]`.

### 7. `tests/Integration/Services/Note/Document/Result/DocumentItemResultAnnotationsTest.php`

`testAllSystemFieldsAnnotated` and `testAllSystemFieldsHasValidTypeAnnotation` against
`document()->fieldList()->getFieldsDescription()`, with the `object` -> `datetime` normalization for
`createdAt`, `updatedAt`, `contentUpdatedAt` (documented the same way as in
`FileItemResultAnnotationsTest`). The existing `DocumentItemResultTest` (raw-response annotation +
type casting via `get`) stays and is extended with a type-casting check on a `list()` item.

---

## Files to Modify

### 1. `src/Services/Note/Document/Result/DocumentItemResult.php`

Generator attempt (2026-10-28), not usable for this item — annotations are edited manually from
the OpenAPI DTO and live `note.document.field.list` metadata:

- `b24-dev:result-item-generator note.document.get --stage=all` — the `get` docs link still points to
  `api-reference/rest-v3/note/document/...`, whose raw markdown now returns 404 (the docs moved to
  `api-reference/note/document/...`).
- `b24-dev:result-item-generator note.document.list --stage=all` — fails with
  `Object "result-item" was not found in REST docs markdown file`: the current markdown page has no
  `result-item` object block the generator parses.
- Running the generator inside Docker from a git worktree also needs a branch stub, because the
  container has no `git` binary and the worktree `.git` file points to a host path.

Keep the existing nullability (`parentId`, `markdown`, `position`, `createdBy`, `updatedBy`), add

```php
 * @property-read CarbonImmutable|null $contentUpdatedAt
 * @property-read DocumentState        $state
 * @property-read bool                 $isArchived
 * @property-read bool                 $isTrashed
 * @property-read bool                 $isOrphan
```

### 2. `src/Services/Note/Document/Service/Document.php`

Add (methods in alphabetical position with the existing ones):

```php
#[ApiEndpointMetadata('note.document.list', '.../note-document-list.html', 'Lists documents visible to the user with cursor pagination.', ApiVersion::v3)]
public function list(
    array|FilterBuilderInterface $filter = [],
    array|DocumentSelectBuilder $select = [],
    ?DocumentListPagination $pagination = null,
): DocumentsResult;

#[ApiEndpointMetadata('note.document.move', '.../note-document-move.html', 'Moves a document with its nested documents.', ApiVersion::v3)]
public function move(int $id, ?int $collectionId = null, ?int $parentId = null, ?int $position = null): DocumentResult;

#[ApiEndpointMetadata('note.document.restore', '.../note-document-restore.html', 'Restores a document from the recycle bin.', ApiVersion::v3)]
public function restore(int $id, ?int $collectionId = null): DocumentResult;

#[ApiEndpointMetadata('note.document.unarchive', '.../note-document-unarchive.html', 'Returns a document from the archive.', ApiVersion::v3)]
public function unarchive(int $id): UnarchivedDocumentResult;
```

Null / empty arguments are dropped from the payload with `array_filter`, as in the existing methods.

### 3. `src/Services/Note/Document/Result/DocumentFieldsResult.php`

Add `getFieldsDescription(): array<string, array<string, mixed>>` keyed by field `name`
(same contract and `LogicException` as `FileFieldsResult`).

### 4. `tests/Unit/Services/Note/Document/Service/DocumentTest.php`

Return-type tests for the four methods, and payload tests: list with array filter / `DocumentFilter`
/ select builder / pagination; empty list call sends `[]`; move omits null arguments; restore with
and without `collectionId`; unarchive sends `id`; all calls use `ApiVersion::v3`.

### 5. `tests/Integration/Services/Note/Document/Service/DocumentTest.php`

- `testList` — filter by `collectionId`, items are `DocumentItemResult`, `markdown` absent.
- `testListWalksAllPagesWithCursor` — 3 documents, `limit = 1`, loop until `getNextCursor() === null`
  (not until empty `items`), every id seen exactly once.
- `testListFiltersByState` — archived and trashed documents appear only with matching `state` filter.
- `testMoveWithChildren` — move parent to a second collection; child follows with its `parentId`.
- `testMoveReorderInsideParent` — `position` change returns the saved item.
- `testUnarchive` — archive -> unarchive -> `isSuccess()`, state `live`.
- `testUnarchiveNotArchivedThrows` — `BaseException`.
- `testRestoreFromRecycleBin` — delete -> restore -> state `live`.
- `testRestoreNotTrashedThrows` — `BaseException`.
- `testRestoreToAnotherCollection` — restore with `collectionId`.

Teardown follows the existing test: delete every created document (ignoring `BaseException` for
documents already in the recycle bin), then every created collection.
`NOTE_RESTORE_TARGET_REQUIRED` needs a permanently purged collection, which the API does not let us
create deterministically; it is documented, not tested.

### 6. `tests/Unit/Services/Note/NoteServiceBuilderTest.php`

No accessor changes (methods are added to the existing `document()` service); no change expected.

### 7. `phpunit.xml.dist`, `Makefile`

No changes: `integration_tests_scope_note_document` / `make test-integration-note-document` already
cover `tests/Integration/Services/Note/Document/{Service,Result}/`.

### 8. `docs/open-api/openapi.json`

Commit the refreshed snapshot produced by `make oa-schema-build`.

### 9. `CHANGELOG.md` — under `## Unreleased` -> `### Added`

```markdown
- Added REST v3 `note.document.list` with typed filter and opaque cursor pagination, `note.document.move`, `note.document.restore` and `note.document.unarchive`; added `contentUpdatedAt`, `state`, `isArchived`, `isTrashed`, `isOrphan` to `DocumentItemResult` ([#686](https://github.com/bitrix24/b24phpsdk/issues/686))
```

---

## Deptrac compliance

New classes depend only on `Attributes`, `Core` (contracts, results, exceptions), `Filters` and the
`Services` layer — the same dependencies as the existing `Document` service and `EventLogFilter`.
No new layer edges.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-note-document
make sdk-coverage-v3-show-uncovered   # the four methods must disappear from the report
```

Local note: run with `COMPOSE_PROJECT_NAME=b24phpsdk` — Docker cannot create a new network for the
worktree project (address pools are exhausted).

---

## Implementation notes (2026-10-28)

- Lifecycle tests for the four new methods live in a dedicated
  `tests/Integration/Services/Note/Document/Service/DocumentLifecycleTest.php` (own collections and
  teardown) instead of being appended to `DocumentTest.php`; same suite, no config changes.
- **API discrepancy**: the docs say `note.document.restore` and `note.document.unarchive` bring back
  "everything nested under it". On the live portal `delete` / `archive` do cascade to children, but
  `restore` / `unarchive` of the parent leave the children trashed / archived (checked after an 8 s
  wait — not asynchronous). Tests assert the observed behaviour (child restored separately); service
  docblocks state the discrepancy.
- Shared `CustomBitrix24Assertions` type mapping: REST v3 metadata type `bool` maps to `bool`, and a
  `string` field may be annotated with a string-backed enum (`DocumentState`).
- `DocumentFilter` condition methods return `AbstractFilterBuilder`, so chained calls across fields
  fail PHPStan; tests build filters with separate statements.
- Pre-existing failures, unrelated to #686 (files untouched): `DocumentFieldItemResultTest`,
  `DocumentSearchFieldItemResultTest`, `DocumentTreeFieldItemResultTest` —
  `testAllFieldsAreAnnotated` fails because the live `*.field.list` responses no longer return
  `editableGroups`, still annotated on the field item results.

## Verification results (2026-10-28)

| Command | Result |
|---|---|
| `make lint-cs-fixer` | passed |
| `make lint-rector` | passed |
| `make lint-phpstan` | passed (after splitting chained filter calls in tests) |
| `make lint-deptrac` | passed, 0 violations |
| `make test-unit` | passed, 2014 tests (11 pre-existing deprecations in IM tests) |
| `make test-integration-note-document` | 34 tests, all new tests pass; 3 pre-existing `editableGroups` failures listed above |
| `make sdk-coverage-v3-show-uncovered` | no `note.document.*` methods left uncovered |
