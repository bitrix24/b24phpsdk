# Plan: Add note.* v3 service — Knowledge Base 2.0 (25 methods) (issue #515)

## Context

Bitrix24 REST API v3 exposes a `note.*` scope (Knowledge Base 2.0) with 25 methods across
5 method groups. None of it is implemented in the SDK yet (`src/Services/` has no `Note`
directory). This is confirmed against the live OpenAPI snapshot rebuilt via
`make oa-schema-build` (`docs/open-api/openapi.json`, 2026-07-02).

All 25 methods use the v3 response envelope: single item → `result.item`, list → `result.items`
(sometimes with an extra pagination field), boolean toggle → `result.result` (a **second**
nested `result` key, not a bare boolean — confirmed from the schema, e.g.
`note.collection.archive` → `{"result": {"result": true}}`).

### Correction found during live verification (post-implementation)

The OpenAPI schema describes every `*.field.list` response as a **flat array** directly under
`result` (e.g. `note.collection.field.list` → `{"result": [...]}`). Live testing against a real
portal (once the `note` scope was granted) showed the actual wire format wraps it one level
deeper, exactly like every other list endpoint: `{"result": {"items": [...]}}`. All five
`*FieldsResult::getFields()` implementations (`Collection`, `Document`, `DocumentTree`,
`DocumentSearch`, `File`) were written against the schema first, caught failing against the
live API (`testFieldList` asserted `'name'` was in the list and failed), and fixed to read
`getResult()['items']`. `*.field.get` (single field) matched the schema as documented
(`result.item`) and needed no fix. Lesson: for this scope, OpenAPI schema is a starting point,
not a substitute for one live call per response shape before shipping.

Separately: manual `curl` verification against `note.*` endpoints must use `/rest/api/<user>/<token>/`,
not `/rest/<user>/<token>/` — REST v3 methods are routed under an extra `/api` path segment
(see `src/Core/EndpointUrlFormatter.php:93-97`). The SDK itself builds this correctly for
`ApiVersion::v3` calls; this only matters when curling the API by hand outside the SDK.

### Method groups (from the OpenAPI schema)

| Group | Methods | Count |
|---|---|---|
| Collection | `add`, `archive`, `delete`, `field.get`, `field.list`, `get`, `list`, `update` | 8 |
| Document | `add`, `archive`, `delete`, `field.get`, `field.list`, `get`, `update` | 7 |
| Document search | `search.field.get`, `search.field.list`, `search.list` | 3 |
| Document tree | `tree.field.get`, `tree.field.list`, `tree.list` | 3 |
| File | `add`, `field.get`, `field.list`, `get` | 4 |

Total: 25. Note there is **no plain `note.document.list`** — documents are only enumerated via
`note.document.tree.list` (by `collectionId`) or `note.document.search.list` (full-text query).

### Design decisions (grounded in existing SDK precedent)

1. **Three service classes**, per the issue's own proposed solution and to keep `Document`
   (its richest entity) self-contained: `Collection.php`, `Document.php` (folds in `tree.*`
   and `search.*`), `File.php`. Registered via `NoteServiceBuilder`.
2. **Boolean-result methods** (`archive`, `delete`) get a dedicated `Archived<Entity>Result`
   / `Deleted<Entity>Result` extending `Core\Result\DeletedItemResult`/custom, overriding
   `isSuccess()` to read `getResult()['result']` — mirrors
   `Documentgenerator\Document\Result\DeletedDocumentResult`, adjusted for the extra nesting
   level that `note.*` uses.
3. **Field-discovery DTO** (`bitrix.rest.dtofielddto`, shared by all 5 `field.get`/`field.list`
   pairs) is **duplicated per entity** as `<Entity>FieldItemResult`, not shared across
   entities — this matches the newest precedent in the codebase,
   `Timeman\RecordField\Result\RecordFieldItemResult`, for the exact same DTO.
4. **Cursor pagination.** Confirmed against the official docs
   (https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-list.html):
   `note.collection.list` takes a `Pagination` parameter — its own named type, `limit`
   (1-200, default 50) plus a nested `afterCursor` object (itself a named type, `position` +
   `id`, both required together) — and returns `items` + `nextCursor{position,id}|null`.
   A precedent exists on the *request* side: `Main\Service\EventLogTailCursor` (used by the
   already-implemented `main.eventlog.tail`, confirmed via its own docs at
   https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-tail.html — the only
   other cursor-shaped method in the whole OpenAPI schema, checked by grepping the full schema
   for "ursor", 2 hits total) is a typed value object with a `toArray()` method, passed into
   the service method instead of a raw array. Follow that idiom, but mirror the docs' own
   two-level structure instead of flattening it: `Collection\Service\CollectionListCursor`
   (the inner `afterCursor`: `position`/`id`) wrapped by
   `Collection\Service\CollectionListPagination` (the outer `Pagination`: `limit` +
   `?CollectionListCursor`) — see § Files to Create, item 3.
   What's genuinely new for this SDK is the **response** side: `main.eventlog.tail` does not
   return a server-side "next cursor" at all — per its integration test
   (`tests/Integration/Services/Main/Service/EventLogTest.php:109-112`), the caller manually
   pulls the last item's `id` out of the response and builds the next `EventLogTailCursor`
   by hand. `note.collection.list` instead returns a ready-made `nextCursor` object from the
   server. So `CollectionsResult::getNextCursor(): ?CollectionListCursor` parses that object
   and hands back a typed, round-trippable cursor — pass it straight into the next `list()`
   call via a fresh `CollectionListPagination`. `null` signals end-of-list.
5. **No `Batch.php`.** The issue's boilerplate mentions "Batch.php where list/add methods
   support batch", but the closest recent precedent (`Timeman\Record`/`RecordField`, PR #519)
   shipped without one, and `note.*` has no documented batch-specific behavior beyond the
   generic Bitrix24 batch endpoint any method already supports. Skipping it keeps scope
   aligned with actual need; can be revisited in a follow-up issue if requested.
6. **SelectBuilder** generated for `Collection` (`get`/`list` share the same `select` field
   set) and `Document` (`get`). Not generated for `File`, `DocumentTree`, `DocumentSearch` —
   none of their methods accept a `select` parameter.
7. Date fields (`createdAt`, `updatedAt`) are typed `CarbonImmutable` in `@property-read`
   annotations, consistent with `RecordItemResult` and the maintainer rule in
   `.claude/skills/b24phpsdk-maintainer/SKILL.md` ("Service method date/time arguments").
   The DTOs only expose them as read-only output fields (no service method takes a date
   argument), so this only affects `ItemResult` annotations, not method signatures.
8. `note.document.tree.*` returns a **recursive** tree (`children: DocumentTreeItemDto[]`) —
   `DocumentTreeItemResult` needs a `@property-read array<DocumentTreeItemResult> $children`
   annotation, exercising `AbstractAnnotatedItem::castArrayValue()`'s typed-array casting.

### Generators (mandatory per SKILL.md before manual edits)

Run `make oa-schema-build` first (**already done** for this planning session).

| File | Generator command |
|---|---|
| `Collection/Result/CollectionItemResult.php` | `php bin/console b24-dev:result-item-generator note.collection.get --stage=all` |
| `Collection/Result/CollectionFieldItemResult.php` | `php bin/console b24-dev:result-item-generator note.collection.field.get --stage=all` |
| `Document/Result/DocumentItemResult.php` | `php bin/console b24-dev:result-item-generator note.document.get --stage=all` |
| `Document/Result/DocumentFieldItemResult.php` | `php bin/console b24-dev:result-item-generator note.document.field.get --stage=all` |
| `Document/Result/DocumentTreeFieldItemResult.php` | `php bin/console b24-dev:result-item-generator note.document.tree.field.get --stage=all` |
| `Document/Result/DocumentSearchFieldItemResult.php` | `php bin/console b24-dev:result-item-generator note.document.search.field.get --stage=all` |
| `File/Result/FileItemResult.php` | `php bin/console b24-dev:result-item-generator note.file.get --stage=all` |
| `File/Result/FileFieldItemResult.php` | `php bin/console b24-dev:result-item-generator note.file.field.get --stage=all` |
| `Collection/Service/CollectionSelectBuilder.php` | `php bin/console b24-dev:generate-select-builder bitrix.note.collectionitemdto --namespace=Bitrix24\\SDK\\Services\\Note\\Collection\\Service --class-name=CollectionSelectBuilder --output=src/Services/Note/Collection/Service/CollectionSelectBuilder.php` |
| `Document/Service/DocumentSelectBuilder.php` | `php bin/console b24-dev:generate-select-builder bitrix.note.documentitemdto --namespace=Bitrix24\\SDK\\Services\\Note\\Document\\Service --class-name=DocumentSelectBuilder --output=src/Services/Note/Document/Service/DocumentSelectBuilder.php` |

`DocumentTreeItemResult`, `DocumentSearchItemResult` are **not** generator-covered entity DTOs
in the same sense (`bitrix.note.documenttreeitemdto` / `bitrix.note.searchresultitemdto` are
plain nested response shapes, not standalone list/get entities with their own CRUD) — written
by hand, reason logged here per SKILL.md ("If the generator cannot be used ... write the
reason explicitly in plan.md").

The `build`/`verify` stages of `b24-dev:result-item-generator` call the live portal
(`tests/.env.local` webhook). Before running them for `get`-based generators, seed at least
one real `Collection`, `Document`, and `File` on the portal (via direct `curl` against the
webhook, per SKILL.md's "Webhook URL format" section) so the generator has real data to
introspect.

---

## Files to Create

### 1. `src/Services/Note/NoteServiceBuilder.php`

```php
namespace Bitrix24\SDK\Services\Note;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Note\Collection\Service\Collection;
use Bitrix24\SDK\Services\Note\Document\Service\Document;
use Bitrix24\SDK\Services\Note\File\Service\File;

#[ApiServiceBuilderMetadata(new Scope(['note']))]
class NoteServiceBuilder extends AbstractServiceBuilder
{
    public function collection(): Collection { /* cached instance, ctor($this->core, $this->log) */ }
    public function document(): Document { /* cached instance */ }
    public function file(): File { /* cached instance */ }
}
```

### 2. `src/Services/Note/Collection/Service/Collection.php`

`#[ApiServiceMetadata(new Scope(['note']))]`, extends `AbstractService`. Methods
(each `#[ApiEndpointMetadata('note.collection.<x>', 'https://apidocs.bitrix24.com/api-reference/rest-v3/note/...', '...', ApiVersion::v3)]`):

```php
public function add(string $name, ?int $position = null): CollectionResult;
public function archive(int $id): ArchivedCollectionResult;
public function delete(?int $id = null, array $filter = []): DeletedCollectionResult;
public function fieldGet(string $name, array $select = []): CollectionFieldResult;
public function fieldList(array $select = []): CollectionFieldsResult;
public function get(int $id, array|CollectionSelectBuilder $select = []): CollectionResult;
public function list(?CollectionListPagination $pagination = null): CollectionsResult;
public function update(int $id, array $fields, array $filter = []): CollectionResult;
```

`list()` builds the request payload as
`$pagination !== null ? ['pagination' => $pagination->toArray()] : []`.

`list()`/`get()`: if `$select instanceof SelectBuilderInterface`, call `buildSelect()`, same
pattern as `Timeman\Record\Service\Record::list()`.

### 3. `src/Services/Note/Collection/Service/CollectionListCursor.php` and `CollectionListPagination.php`
(hand-written, reason: plain value objects, not generator-covered entity/select-builder contracts)

The official docs for `note.collection.list`
(https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-list.html)
document `Pagination` as its own named parameter type — `limit` plus a nested `afterCursor`
object (itself documented as its own named type, `position` + `id`) — not two loose scalars.
Model that two-level structure as two value objects instead of flattening it into the method
signature, extending the `EventLogTailCursor` idiom (§ design decision 4) to match the shape
the docs actually describe:

```php
final class CollectionListCursor
{
    public function __construct(
        private readonly int $position,
        private readonly int $id,
    ) {
    }

    public function toArray(): array
    {
        return ['position' => $this->position, 'id' => $this->id];
    }

    public static function fromArray(array $data): self
    {
        return new self((int)$data['position'], (int)$data['id']);
    }
}

final class CollectionListPagination
{
    public function __construct(
        private readonly int $limit = 50,
        private readonly ?CollectionListCursor $afterCursor = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'limit'       => $this->limit,
                'afterCursor' => $this->afterCursor?->toArray(),
            ],
            static fn (mixed $v): bool => $v !== null
        );
    }
}
```

`CollectionsResult::getNextCursor()` still returns `?CollectionListCursor` only (§7) — the
server's `nextCursor` field never includes `limit`, so the inner value object is what round-trips
into the next call: `$note->collection()->list(new CollectionListPagination(afterCursor: $result->getNextCursor()))`.

### 4. `src/Services/Note/Collection/Service/CollectionSelectBuilder.php`

Generated (see generator table above); typed builder over `bitrix.note.collectionitemdto`
fields (`id`, `name`, `position`, `policyLevel`, `createdBy`, `createdAt`, `updatedBy`, `updatedAt`).

### 5. `src/Services/Note/Collection/Result/CollectionItemResult.php`

Generated. Expected annotations:

```php
/**
 * @property-read int               $id
 * @property-read string            $name
 * @property-read int|null          $position
 * @property-read string|null       $policyLevel
 * @property-read int|null          $createdBy
 * @property-read CarbonImmutable   $createdAt
 * @property-read int|null          $updatedBy
 * @property-read CarbonImmutable   $updatedAt
 */
class CollectionItemResult extends AbstractAnnotatedItem {}
```

### 6. `src/Services/Note/Collection/Result/CollectionResult.php`

```php
class CollectionResult extends AbstractResult
{
    /** @throws BaseException */
    public function collection(): CollectionItemResult
    {
        return new CollectionItemResult($this->getCoreResponse()->getResponseData()->getResult()['item']);
    }
}
```

### 7. `src/Services/Note/Collection/Result/CollectionsResult.php`

```php
class CollectionsResult extends AbstractResult
{
    /** @return CollectionItemResult[] @throws BaseException */
    public function getCollections(): array { /* map result.items */ }

    /** @throws BaseException */
    public function getNextCursor(): ?CollectionListCursor
    {
        $cursor = $this->getCoreResponse()->getResponseData()->getResult()['nextCursor'] ?? null;

        return $cursor === null ? null : CollectionListCursor::fromArray($cursor);
    }
}
```

### 8. `src/Services/Note/Collection/Result/ArchivedCollectionResult.php` and `DeletedCollectionResult.php`

```php
class ArchivedCollectionResult extends AbstractResult
{
    /** @throws BaseException */
    public function isSuccess(): bool
    {
        return (bool)($this->getCoreResponse()->getResponseData()->getResult()['result'] ?? false);
    }
}
// DeletedCollectionResult: identical body, distinct class per SDK per-method-result convention.
```

### 9. `src/Services/Note/Collection/Result/CollectionFieldItemResult.php`, `CollectionFieldResult.php`, `CollectionFieldsResult.php`

`CollectionFieldItemResult` generated (see table); shape identical to
`Timeman\RecordField\Result\RecordFieldItemResult` (`name`, `type`, `title`, `description`,
`validationRules`, `requiredGroups`, `filterable`, `sortable`, `editable`, `multiple`,
`elementType`). `CollectionFieldResult::field(): CollectionFieldItemResult` (from `result.item`).
`CollectionFieldsResult::getFields(): array<CollectionFieldItemResult>` (from flat `result` array).

### 10. `src/Services/Note/Document/Service/Document.php`

13 methods, all `#[ApiEndpointMetadata('note.document.<x>', ...)]`:

```php
public function add(int $collectionId, string $title, ?int $parentId = null, ?string $markdown = null): DocumentResult;
public function archive(int $id): ArchivedDocumentResult;
public function delete(?int $id = null, array $filter = []): DeletedDocumentResult;
public function fieldGet(string $name, array $select = []): DocumentFieldResult;
public function fieldList(array $select = []): DocumentFieldsResult;
public function get(int $id, array|DocumentSelectBuilder $select = []): DocumentResult;
public function update(int $id, array $fields, array $filter = [], ?bool $overwrite = null): DocumentResult;

public function treeList(int $collectionId): DocumentTreeResult;
public function treeFieldGet(string $name, array $select = []): DocumentTreeFieldResult;
public function treeFieldList(array $select = []): DocumentTreeFieldsResult;

public function searchList(string $query, int $limit = 0): DocumentSearchResult;
public function searchFieldGet(string $name, array $select = []): DocumentSearchFieldResult;
public function searchFieldList(array $select = []): DocumentSearchFieldsResult;
```

### 11. `src/Services/Note/Document/Service/DocumentSelectBuilder.php`

Generated, over `bitrix.note.documentitemdto`.

### 12. `src/Services/Note/Document/Result/DocumentItemResult.php`

Generated:

```php
/**
 * @property-read int             $id
 * @property-read int             $collectionId
 * @property-read int|null        $parentId
 * @property-read string          $title
 * @property-read string|null     $markdown
 * @property-read int|null        $position
 * @property-read int|null        $createdBy
 * @property-read int|null        $updatedBy
 * @property-read CarbonImmutable $createdAt
 * @property-read CarbonImmutable $updatedAt
 */
class DocumentItemResult extends AbstractAnnotatedItem {}
```

### 13. `src/Services/Note/Document/Result/DocumentResult.php`, `ArchivedDocumentResult.php`, `DeletedDocumentResult.php`

Same shape as Collection's equivalents (§6, §8), adapted to `DocumentItemResult`.

### 14. `src/Services/Note/Document/Result/DocumentFieldItemResult.php`, `DocumentFieldResult.php`, `DocumentFieldsResult.php`

Same shape as §9, generated for `note.document.field.get`.

### 15. `src/Services/Note/Document/Result/DocumentTreeItemResult.php` (hand-written, reason: not a standalone CRUD entity)

```php
/**
 * @property-read int                                $id
 * @property-read int                                $collectionId
 * @property-read int|null                            $parentId
 * @property-read string                              $title
 * @property-read int|null                            $position
 * @property-read array<DocumentTreeItemResult>        $children
 */
class DocumentTreeItemResult extends AbstractAnnotatedItem {}
```

### 16. `src/Services/Note/Document/Result/DocumentTreeResult.php` (hand-written)

```php
class DocumentTreeResult extends AbstractResult
{
    /** @return DocumentTreeItemResult[] @throws BaseException */
    public function getItems(): array { /* map result.items */ }

    /** @throws BaseException */
    public function isTruncated(): bool
    {
        return (bool)($this->getCoreResponse()->getResponseData()->getResult()['truncated'] ?? false);
    }
}
```

### 17. `src/Services/Note/Document/Result/DocumentTreeFieldItemResult.php`, `DocumentTreeFieldResult.php`, `DocumentTreeFieldsResult.php`

Same shape as §9/§14, generated for `note.document.tree.field.get`.

### 18. `src/Services/Note/Document/Result/DocumentSearchItemResult.php` (hand-written, reason: search-result projection, not a CRUD entity)

```php
/**
 * @property-read int         $documentId
 * @property-read int         $collectionId
 * @property-read string      $title
 * @property-read float       $score
 * @property-read string|null $snippet
 * @property-read bool        $sharedAccess
 */
class DocumentSearchItemResult extends AbstractAnnotatedItem {}
```

### 19. `src/Services/Note/Document/Result/DocumentSearchResult.php` (hand-written)

```php
class DocumentSearchResult extends AbstractResult
{
    /** @return DocumentSearchItemResult[] @throws BaseException */
    public function getItems(): array { /* map result.items */ }

    /** @throws BaseException */
    public function hasMore(): bool
    {
        return (bool)($this->getCoreResponse()->getResponseData()->getResult()['hasMore'] ?? false);
    }
}
```

### 20. `src/Services/Note/Document/Result/DocumentSearchFieldItemResult.php`, `DocumentSearchFieldResult.php`, `DocumentSearchFieldsResult.php`

Same shape as §9/§14/§17, generated for `note.document.search.field.get`.

### 21. `src/Services/Note/File/Service/File.php`

```php
#[ApiServiceMetadata(new Scope(['note']))]
class File extends AbstractService
{
    public function add(int $documentId, string $fileName, string $fileContent): FileResult;
    public function fieldGet(string $name, array $select = []): FileFieldResult;
    public function fieldList(array $select = []): FileFieldsResult;
    public function get(int $id, int $documentId): FileResult;
}
```

`fileContent` is the raw/base64 file payload per the REST v3 schema (`type: string`) —
confirm exact encoding against `note.file.add` docs during Step 2 doc lookup (bitrix24 MCP
unavailable this session; verify against `apidocs.bitrix24.com` directly, or against a real
`curl` call, before finalizing the method's PHPDoc).

### 22. `src/Services/Note/File/Result/FileItemResult.php`

Generated:

```php
/**
 * @property-read int         $id
 * @property-read int         $documentId
 * @property-read string      $name
 * @property-read int|null    $size
 * @property-read string|null $mimeType
 * @property-read string|null $assetType
 * @property-read string|null $assetMarkdown
 */
class FileItemResult extends AbstractAnnotatedItem {}
```

### 23. `src/Services/Note/File/Result/FileResult.php`

Same shape as §6, adapted to `FileItemResult`.

### 24. `src/Services/Note/File/Result/FileFieldItemResult.php`, `FileFieldResult.php`, `FileFieldsResult.php`

Same shape as §9/§14/§17/§20, generated for `note.file.field.get`.

---

## Test files (mirrors every file above under `tests/Unit/` and `tests/Integration/`)

### Unit tests (`tests/Unit/Services/Note/...`, use `NullCore`/`NullBatch`)

- `Collection/Service/CollectionTest.php`
- `Document/Service/DocumentTest.php`
- `File/Service/FileTest.php`

Each asserts the correct REST method name and payload shape is sent to `CoreInterface::call()`
(via `createMock(CoreInterface::class)` with `expects($this->once())->method('call')->with(...)`),
for every public method, following the `docs/testing.md` unit test pattern.

### Integration tests (`tests/Integration/Services/Note/...`, use `Factory::getServiceBuilder()`)

- `Collection/Service/CollectionTest.php` — full CRUD + archive + list w/ cursor, `tearDown()`
  deletes any collection created during the test.
- `Document/Service/DocumentTest.php` — CRUD + archive + tree + search, `tearDown()` cleans up.
- `File/Service/FileTest.php` — add/get, `tearDown()` cleans up.

### Mandatory `*ItemResultTest` annotation/type-cast tests (one per `*ItemResult` with `@property-read`)

Per SKILL.md, exactly two test methods each (`testAllFieldsAreAnnotated`,
`testAllFieldsHasValidTypeCastingInMagicGetters`):

- `tests/Integration/Services/Note/Collection/Result/CollectionItemResultTest.php`
- `tests/Integration/Services/Note/Collection/Result/CollectionFieldItemResultTest.php`
- `tests/Integration/Services/Note/Document/Result/DocumentItemResultTest.php`
- `tests/Integration/Services/Note/Document/Result/DocumentFieldItemResultTest.php`
- `tests/Integration/Services/Note/Document/Result/DocumentTreeItemResultTest.php`
- `tests/Integration/Services/Note/Document/Result/DocumentTreeFieldItemResultTest.php`
- `tests/Integration/Services/Note/Document/Result/DocumentSearchItemResultTest.php`
- `tests/Integration/Services/Note/Document/Result/DocumentSearchFieldItemResultTest.php`
- `tests/Integration/Services/Note/File/Result/FileItemResultTest.php`
- `tests/Integration/Services/Note/File/Result/FileFieldItemResultTest.php`

(10 files — one per `*ItemResult` class; `DocumentTreeItemResultTest` and
`DocumentSearchItemResultTest` fetch their raw item via `treeList()`/`searchList()` instead of
a `get()`, since those entities have no standalone `get` endpoint.)

---

## Files to Modify

### 1. `src/Services/ServiceBuilder.php`

Confirmed exact pattern from `getMailServiceScope()` (line 404) — add, alphabetically placed
near `getTimemanScope()` (line 475):

```php
public function getNoteScope(): NoteServiceBuilder
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new NoteServiceBuilder(
            $this->core,
            $this->batch,
            $this->bulkItemsReader,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}
```

Add `use Bitrix24\SDK\Services\Note\NoteServiceBuilder;` to the import block.

### 2. `phpunit.xml.dist`

Add one integration `<testsuite>` block per entity, following the existing
`test-integration-<scope>` naming style:

```xml
<testsuite name="integration_note_collection">
    <directory>tests/Integration/Services/Note/Collection</directory>
</testsuite>
<testsuite name="integration_note_document">
    <directory>tests/Integration/Services/Note/Document</directory>
</testsuite>
<testsuite name="integration_note_file">
    <directory>tests/Integration/Services/Note/File</directory>
</testsuite>
```

### 3. `Makefile`

```makefile
test-integration-note-collection:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_note_collection

test-integration-note-document:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_note_document

test-integration-note-file:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_note_file
```

(match the exact `docker-compose run` invocation style already used by neighboring
`test-integration-timeman-*` targets — copy verbatim and rename.)

### 4. `CHANGELOG.md`

Under `## X.Y.Z Unreleased` → `### Added`:

```markdown
- Added Note scope (`note.*`, Knowledge Base 2.0): Collection, Document (incl. tree and search), and File services ([#515](https://github.com/bitrix24/b24phpsdk/issues/515))
```

---

## Deptrac compliance

All new code lives under `src/Services/Note/**`, importing only from `Core` (`AbstractService`,
`AbstractResult`, `AbstractAnnotatedItem`, `DeletedItemResult`, exceptions, `ApiVersion`,
`Scope`, `SelectBuilderInterface`) and `Services\AbstractServiceBuilder`. No cross-service
imports (does not depend on any other `Services\<OtherScope>\*`). This matches the `Services`
layer rule (`Core`, `Application`, `Legacy` allowed) — no new deptrac violations expected.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-note-collection
make test-integration-note-document
make test-integration-note-file
```
