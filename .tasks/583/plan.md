# Plan: Add support for catalog.section.* (issue #583)

## Context

The Bitrix24 REST API exposes a `catalog.section.*` group for managing trade-catalog
sections (product categories inside an infoblock-backed catalog). This SDK does not yet
implement it. The issue lists six methods:

- `catalog.section.add`
- `catalog.section.update`
- `catalog.section.get`
- `catalog.section.list`
- `catalog.section.delete`
- `catalog.section.getFields`

Method details were fetched live via the Bitrix24 MCP docs tool
(`https://apidocs.bitrix24.com/api-reference/catalog/section/*`):

### `catalog.section.add`
- param: `fields` (object, required) — `catalog_section` shape:
  `iblockId` (int), `iblockSectionId` (int, parent, default = top level),
  `name` (string, required), `xmlId` (string), `code` (string, must be unique),
  `sort` (int, default 500), `active` (`Y`/`N`, default `Y`),
  `description` (string), `descriptionType` (`text`|`html`)
- returns: `result.section` (full `catalog_section` object incl. `id`)

### `catalog.section.update`
- params: `id` (int, required), `fields` (object, required) — same shape as add minus
  requiredness (all optional on update)
- returns: `result.section`

### `catalog.section.get`
- param: `id` (int, required)
- returns: `result.section`

### `catalog.section.list`
- params: `select` (array, optional — all fields if omitted), `filter` (object,
  **required**, must contain `iblockId`; prefix `>=`/`>` supported for numeric filters)
- returns: `result.sections` (array) — confirmed by symmetry with `catalog.document.list`
  (`result.documents`) and `catalog.productPropertySection.list`
  (`result.productPropertySections`); will be verified against the live raw response in
  the annotation test.

### `catalog.section.delete`
- param: `id` (int, required)
- returns: `result` (bool)

### `catalog.section.getFields`
- no params
- returns: `result.section` = map of field code → `rest_field_description`
  (`isImmutable`, `isReadOnly`, `isRequired`, `type`). Confirmed field set from live
  doc example:
  - `id`: integer, readOnly
  - `iblockId`: integer, required
  - `iblockSectionId`: integer
  - `name`: string, required
  - `xmlId`: string
  - `code`: string
  - `sort`: integer
  - `active`: char (`Y`/`N`)
  - `description`: string
  - `descriptionType`: string

### Key-naming difference vs. base `Core\Batch`

`catalog.section.update` and `catalog.section.delete` use **lowercase** `id`, while the
base `Bitrix24\SDK\Core\Batch::deleteEntityItems()` registers deletes with uppercase
`ID`. This is the same situation already solved for `Services\Catalog\Document` — a
child `Batch` class overrides `determineKeyId()` and `deleteEntityItems()` to use
lowercase `id`. `updateEntityItems()` in the base class already builds `['id' => ...,
'fields' => ...]` (lowercase), so no override is needed there.

`catalog.section.add` has no key-mapping concerns (`addEntityItems()` just forwards the
given `['fields' => ...]` item), so the base implementation is reused as-is.

This SDK version targets **v3** (branch `v3-dev`, already checked out per user
instruction — no new branch/task-folder-only decisions needed from the user).

Author attribution for all new files: `© Dmitriy Ignatenko <algonexys@gmail.com>`
(per project convention seen in recently-added Catalog files).

Reference implementations used as templates:
- `src/Services/Catalog/Document/*` — full pattern with custom `Batch` (lowercase `id`)
- `src/Services/Catalog/ProductPropertySection/*` — simple pattern, no batch

---

## Files to Create

### 1. `src/Services/Catalog/Section/Batch.php`

Child of `\Bitrix24\SDK\Core\Batch`. Overrides `determineKeyId()` to return `'id'` and
`deleteEntityItems()` to register commands with `['id' => $itemId]` instead of
`['ID' => $itemId]`. Mirrors `src/Services/Catalog/Document/Batch.php` exactly, replacing
"document" wording with "section".

### 2. `src/Services/Catalog/Section/Result/SectionItemResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Section\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int         $id
 * @property-read int         $iblockId
 * @property-read int|null    $iblockSectionId
 * @property-read string      $name
 * @property-read string|null $xmlId
 * @property-read string|null $code
 * @property-read int|null    $sort
 * @property-read bool|null   $active
 * @property-read string|null $description
 * @property-read string|null $descriptionType
 */
class SectionItemResult extends AbstractAnnotatedItem
{
}
```

Generator note: this file matches the `*ItemResult.php` generator contract
(`src/Services/**/Result/*ItemResult.php` with `@property-read` annotations). The
generator (`b24-dev:result-item-generator catalog.section.get --stage=all`) requires a
live webhook call to `catalog.section.get`/`getFields` against a portal that already has
at least one section — since no section exists yet on the test portal and the exact
field set is already fully confirmed from the live MCP doc fetch (getFields example
above), the class is hand-written directly from that confirmed schema instead of running
the generator. This reason is recorded here per the generator-usage rule.

### 3. `src/Services/Catalog/Section/Result/SectionResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Section\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class SectionResult extends AbstractResult
{
    /** @throws BaseException */
    public function section(): SectionItemResult
    {
        return new SectionItemResult($this->getCoreResponse()->getResponseData()->getResult()['section']);
    }
}
```

### 4. `src/Services/Catalog/Section/Result/SectionsResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Section\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class SectionsResult extends AbstractResult
{
    /**
     * @return SectionItemResult[]
     * @throws BaseException
     */
    public function getSections(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return array_map(
            static fn (array $item): SectionItemResult => new SectionItemResult($item),
            $result['sections'] ?? []
        );
    }
}
```

### 5. `src/Services/Catalog/Section/Result/SectionFieldsResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Section\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class SectionFieldsResult extends AbstractResult
{
    /**
     * @return array<string, array<string, mixed>>
     * @throws BaseException
     */
    public function getFieldsDescription(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['section'];
    }
}
```

### 6. `src/Services/Catalog/Section/Result/SectionAddedBatchResult.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Section\Result;

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

class SectionAddedBatchResult
{
    public function __construct(private readonly ResponseData $responseData)
    {
    }

    public function getResponseData(): ResponseData
    {
        return $this->responseData;
    }

    public function section(): SectionItemResult
    {
        return new SectionItemResult($this->responseData->getResult()['section']);
    }
}
```

### 7. `src/Services/Catalog/Section/Result/SectionUpdatedBatchResult.php`

Same shape as `SectionAddedBatchResult`, named `SectionUpdatedBatchResult`.

### 8. `src/Services/Catalog/Section/Service/Section.php`

```php
namespace Bitrix24\SDK\Services\Catalog\Section\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Section\Result\SectionFieldsResult;
use Bitrix24\SDK\Services\Catalog\Section\Result\SectionResult;
use Bitrix24\SDK\Services\Catalog\Section\Result\SectionsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Section extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    #[ApiEndpointMetadata(
        'catalog.section.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-add.html',
        'Adds a new trade-catalog section'
    )]
    public function add(array $fields): SectionResult
    {
        return new SectionResult($this->core->call('catalog.section.add', ['fields' => $fields]));
    }

    #[ApiEndpointMetadata(
        'catalog.section.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-update.html',
        'Updates a trade-catalog section by its identifier'
    )]
    public function update(int $id, array $fields): SectionResult
    {
        return new SectionResult($this->core->call('catalog.section.update', ['id' => $id, 'fields' => $fields]));
    }

    #[ApiEndpointMetadata(
        'catalog.section.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-get.html',
        'Returns a trade-catalog section by its identifier'
    )]
    public function get(int $id): SectionResult
    {
        return new SectionResult($this->core->call('catalog.section.get', ['id' => $id]));
    }

    #[ApiEndpointMetadata(
        'catalog.section.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-list.html',
        'Returns a list of trade-catalog sections by filter'
    )]
    public function list(array $select = [], array $filter = []): SectionsResult
    {
        return new SectionsResult(
            $this->core->call('catalog.section.list', ['select' => $select, 'filter' => $filter])
        );
    }

    #[ApiEndpointMetadata(
        'catalog.section.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-delete.html',
        'Deletes a trade-catalog section by identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.section.delete', ['id' => $id]));
    }

    #[ApiEndpointMetadata(
        'catalog.section.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-get-fields.html',
        'Returns the description of trade-catalog section fields'
    )]
    public function getFields(): SectionFieldsResult
    {
        return new SectionFieldsResult($this->core->call('catalog.section.getFields'));
    }
}
```

`filter` is required by the API (must contain `iblockId`) but is kept as a plain
optional-with-default `array $filter = []` parameter in the SDK signature — consistent
with how `Document::list()` and `ProductPropertySection::list()` expose `filter` (SDK
does not pre-validate required filter keys; the API itself returns the documented error
if `iblockId` is missing).

### 9. `src/Services/Catalog/Section/Service/Batch.php`

Mirrors `src/Services/Catalog/Document/Service/Batch.php`: `add()`, `update()`,
`delete()` generator wrappers around `Section\Batch::addEntityItems() /
updateEntityItems() / deleteEntityItems()`, yielding `SectionAddedBatchResult` /
`SectionUpdatedBatchResult` / `DeletedItemBatchResult`.

### 10. `tests/Unit/Services/Catalog/Section/Service/SectionTest.php`

Mirrors `tests/Unit/Services/Catalog/Document/Service/DocumentTest.php`: one test per
method (`add`, `update`, `get`, `list`, `delete`, `getFields`), asserting the exact
`core->call()` method name + parameters and the correct result wrapper class.

### 11. `tests/Integration/Services/Catalog/Section/Service/SectionTest.php`

Mirrors `tests/Integration/Services/Catalog/Document/Service/DocumentTest.php`:
- `setUp()`: get `Section` service via `Factory::getServiceBuilder(true)->getCatalogScope()->section()`;
  resolve a real `iblockId` via `getCatalogScope()->catalog()->list([], [], [], 1)->getCatalogs()[0]->iblockId`
  (same helper pattern as `Document`'s `createDocumentWithElement()`).
- `tearDown()`: delete any section ids created during the test.
- `testAddUpdateGetListDelete()`: add a section with `name` + `iblockId`, assert
  returned fields, update `name`, assert via `get()`, assert presence via `list()`
  filtered by `iblockId` + `id`, delete and assert `isSuccess()`.
- `testGetFields()`: assert `getFields()->getFieldsDescription()` is an array containing
  the `iblockId` and `name` keys.

### 12. `tests/Integration/Services/Catalog/Section/Service/BatchTest.php`

Mirrors `tests/Integration/Services/Catalog/Document/Service/BatchTest.php`:
`testAddUpdateDelete()` using `$this->sectionService->batch->add/update/delete`.

### 13. `tests/Integration/Services/Catalog/Section/Result/SectionItemResultTest.php`

Follows the mandatory result-item annotation test template (see skill doc):
- `testAllFieldsAreAnnotated()` — fetch a raw section array via `list()` raw core
  response and assert against `SectionItemResult::class`.
- `testAllFieldsHasValidTypeCastingInMagicGetters()` — fetch via `list()->getSections()[0]`
  and assert type casts.

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Add, following the `document()` method pattern exactly:

```php
public function section(): Catalog\Section\Service\Section
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\Section\Service\Section(
            new Catalog\Section\Service\Batch(
                new Catalog\Section\Batch($this->core, $this->log),
                $this->log
            ),
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}
```

### 2. `phpunit.xml.dist`

Add three testsuites after the `integration_tests_catalog_document_element_annotations`
block (line ~586), following the `Document`/`DocumentElement` block layout:

```xml
<testsuite name="integration_tests_catalog_section">
    <directory>./tests/Integration/Services/Catalog/Section/Service/</directory>
</testsuite>
<testsuite name="integration_tests_catalog_section_annotations">
    <file>./tests/Integration/Services/Catalog/Section/Result/SectionItemResultTest.php</file>
</testsuite>
```

(The `Service/` directory sweep already covers both `SectionTest.php` and
`BatchTest.php`, same as the `Document` block does.)

### 3. `Makefile`

Add after the `test-integration-catalog-document-element-annotations` target (~line 944),
following the exact `document`/`document-element` target style:

```makefile
.PHONY: test-integration-catalog-section
test-integration-catalog-section:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_section
.PHONY: test-integration-catalog-section-annotations
test-integration-catalog-section-annotations:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_catalog_section_annotations
```

Also add a row to the Catalog integration-tests reference table in `docs/testing.md`
(`make test-integration-catalog-section` / `make test-integration-catalog-section-annotations`).

### 4. `docs/testing.md`

Add two rows to the "Tests — integration (Catalog)" table:

```markdown
| `make test-integration-catalog-section` | Trade-catalog sections |
| `make test-integration-catalog-section-annotations` | Trade-catalog section result annotations |
```

### 5. `CHANGELOG.md`

Add under `## Unreleased` → `### Added`, above the existing `Services\Catalog\Document`
entry (newest entries go first per convention):

```markdown
- Added service `Services\Catalog\Section` with support methods,
  see [catalog.section.* methods](https://apidocs.bitrix24.com/api-reference/catalog/section/index.html) ([#583](https://github.com/bitrix24/b24phpsdk/issues/583)):
    - `add` creates a new trade-catalog section, with batch calls support
    - `update` updates an existing trade-catalog section, with batch calls support
    - `get` gets a trade-catalog section by its identifier
    - `list` gets the list of trade-catalog sections by filter
    - `delete` deletes a trade-catalog section, with batch calls support
    - `getFields` returns the description of trade-catalog section fields
```

### 6. `.php-cs-fixer.php`, `phpstan.neon.dist`, `rector.php`

**No changes** — confirmed by direct inspection:
- `.php-cs-fixer.php` already includes the whole `src/Services/Catalog/` directory
  (line 15), so `Section` is covered automatically.
- `phpstan.neon.dist` already includes plain `src/` (whole tree) plus the whole
  `tests/Integration/Services/Catalog` directory (line 10).
- `rector.php` already includes the whole `src/Services/Catalog` and
  `tests/Integration/Services/Catalog` directories (lines 23–24).

No new entries needed in any of the three files for this issue.

---

## Deptrac compliance

`Section` lives entirely under `Services/Catalog`, importing only from `Core` (via
`AbstractService`, `AbstractAnnotatedItem`, `AbstractResult`, exceptions, `Batch`) — same
dependency shape as `Document`. No new Deptrac violation is expected; no
`skip_violations` entry needed.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-catalog-section
make test-integration-catalog-section-annotations
```
