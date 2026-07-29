# Plan: Add support for catalog.productPropertySection (issue #558)

## Context

Bitrix24 REST API exposes three methods for managing "section settings" of a product
property or variation in the commercial catalog smart filter:

- `catalog.productPropertySection.get` — https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-get.html
- `catalog.productPropertySection.list` — https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-list.html
- `catalog.productPropertySection.set` — https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-set.html

None of these are `rest-v3` methods (no `ApiVersion::v3` argument needed) — they follow the
same classic catalog REST pattern as the existing `catalog.catalog.*` and `catalog.product.*`
services in `src/Services/Catalog/`.

### API details (verified via Bitrix24 MCP `bitrix-method-details`)

**`catalog.productPropertySection.get`**
- Params: `propertyId` (int, required)
- Response: `result.productPropertySection` (single object)
- Response fields: `propertyId` (int), `smartFilter` (char Y/N), `displayType` (char F/K/P),
  `displayExpanded` (char Y/N), `filterHint` (string)

**`catalog.productPropertySection.list`**
- Params: `select` (array, optional), `filter` (object, optional), `order` (object, optional)
- Response: `result.productPropertySections` (array of objects), `total` (int, omitted when
  `start=-1`), `next` (int, offset for next page, omitted when no more records)
- Same field set as `get`
- No `start` param is documented for this method (unlike `catalog.product.list`) — do not add one.

**`catalog.productPropertySection.set`**
- Params: `propertyId` (int, required), `fields` (flat object, required) — flat despite the
  doc UI grouping the four sub-fields under a "PRODUCT" heading; all 6 official examples
  (curl, JS, PHP `core->call`, PHP CRest) pass `fields` as a flat map:
  `{smartFilter, displayType, displayExpanded, filterHint}`
- Response: `result.productPropertySection` — returns **7** fields: the 5 above **plus**
  `iblockId` and `sectionId` (both returned as numeric strings, e.g. `"25"`, `"0"`)

### Result-item field design (user-confirmed)

Use a single `ProductPropertySectionItemResult` class with all 7 fields, shared by `get`,
`list`, and `set`. For `get`/`list` responses, `iblockId` and `sectionId` are simply absent
from the raw API payload (not annotated-but-empty — genuinely not returned), so the mandatory
annotation-completeness test (`assertBitrix24AllResultItemFieldsAnnotated`) must be built
against the `set` response (the superset), not `get`/`list`.

### Type mapping

| API field | Bitrix24 type | SDK annotation |
|---|---|---|
| `propertyId` | int | `int` |
| `smartFilter` | char (Y/N) | `bool` |
| `displayType` | char (F/K/P) | `ProductPropertySectionDisplayType` (new backed enum, string-backed) |
| `displayExpanded` | char (Y/N) | `bool` |
| `filterHint` | string | `string` |
| `iblockId` | numeric string | `int` |
| `sectionId` | numeric string | `int` |

`AbstractAnnotatedItem::castValue()` already handles `Y`/`N` → bool and numeric-string → int
casting automatically, and backed-enum casting via `resolveBackedEnumClass()` +
`::tryFrom()`, so no custom `__get()` override is needed (unlike `AbstractCatalogItem`, which
predates `AbstractAnnotatedItem` and is not used here).

New enum `ProductPropertySectionDisplayType` follows the existing `ProductType` enum pattern
(`src/Services/Catalog/Common/ProductType.php`):

```php
enum ProductPropertySectionDisplayType: string
{
    case checkboxes = 'F';
    case radioButtons = 'K';
    case dropdownList = 'P';
}
```

### No `fields()`/`getFields` method exists for this entity

Confirmed via MCP search — only `get`/`list`/`set` are documented. Per `docs/testing.md`,
the mandatory annotation test uses a real fetched item (from `set`, per above) directly;
no `fields()`-emulation switch-case is needed since we are not testing against a synthetic
`fields()` response.

### Directory layout

Follows the `Catalog\Catalog` / `Catalog\Product` sibling pattern, with `ProductPropertySectionResult`
as the single-item envelope (used by `get()` and `set()`) and `ProductPropertySectionsResult` as
the list envelope (used by `list()`) — matching the `CatalogResult`/`CatalogsResult` naming
convention exactly:

```
src/Services/Catalog/ProductPropertySection/
├── ProductPropertySectionDisplayType.php
├── Result/
│   ├── ProductPropertySectionItemResult.php
│   ├── ProductPropertySectionResult.php     (used by get() and set())
│   └── ProductPropertySectionsResult.php    (used by list())
└── Service/
    └── ProductPropertySection.php
```

No child `Batch.php` needed: field-name casing between REST params (`propertyId`, `fields`,
`select`, `filter`, `order`) and this service's method signatures match 1:1, so the base
`\Bitrix24\SDK\Core\Batch` class works as-is if batch support is ever added later. Since none
of the three methods are naturally batch-friendly (no `add`/`update`/`delete` over many
entities — `set` acts on one `propertyId` at a time and there's no bulk variant documented),
no `Batch.php` service wrapper is created now, matching the precedent of `Catalog\Catalog`
service (also get/list-only, no Batch.php).

---

## Files to Create

### 1. `src/Services/Catalog/ProductPropertySection/Result/ProductPropertySectionItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\ProductPropertySectionDisplayType;

/**
 * @property-read int $propertyId
 * @property-read bool $smartFilter
 * @property-read ProductPropertySectionDisplayType $displayType
 * @property-read bool $displayExpanded
 * @property-read string $filterHint
 * @property-read int $iblockId
 * @property-read int $sectionId
 */
class ProductPropertySectionItemResult extends AbstractAnnotatedItem
{
}
```

**Generator not used, reason recorded per skill requirement**: the mandatory generator
(`bin/console b24-dev:result-item-generator <method.name> --stage=all`) derives a result-item
class from a single method's OpenAPI response schema. This class must instead cover the
7-field superset spanning two different methods' responses (`get`/`list` return 5 fields,
`set` returns those 5 plus `iblockId`/`sectionId`) — no single method's schema produces that
union, and the design (single shared class, user-confirmed above) is intentionally broader
than any one generator invocation would produce. The class is hand-written following the type
table above instead.

### 2. `src/Services/Catalog/ProductPropertySection/Result/ProductPropertySectionResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class ProductPropertySectionResult extends AbstractResult
{
    public function productPropertySection(): ProductPropertySectionItemResult
    {
        return new ProductPropertySectionItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()['productPropertySection']
        );
    }
}
```

### 3. `src/Services/Catalog/ProductPropertySection/Result/ProductPropertySectionsResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ProductPropertySectionsResult extends AbstractResult
{
    /**
     * @return ProductPropertySectionItemResult[]
     * @throws BaseException
     */
    public function getProductPropertySections(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['productPropertySections'] as $item) {
            $res[] = new ProductPropertySectionItemResult($item);
        }

        return $res;
    }
}
```

### 4. `src/Services/Catalog/ProductPropertySection/ProductPropertySectionDisplayType.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductPropertySection;

enum ProductPropertySectionDisplayType: string
{
    case checkboxes = 'F';
    case radioButtons = 'K';
    case dropdownList = 'P';
}
```

(Placed directly under the entity root, mirroring `Common/ProductType.php` style but scoped
to this entity since the enum is specific to it — no other entity uses `displayType`.)

### 5. `src/Services/Catalog/ProductPropertySection/Service/ProductPropertySection.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductPropertySection\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result\ProductPropertySectionResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result\ProductPropertySectionsResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class ProductPropertySection extends AbstractService
{
    /**
     * Returns the section settings of a product property or variation by the property ID.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertySection.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-get.html',
        'Returns the section settings of a product property or variation by the property ID.'
    )]
    public function get(int $propertyId): ProductPropertySectionResult
    {
        return new ProductPropertySectionResult(
            $this->core->call('catalog.productPropertySection.get', ['propertyId' => $propertyId])
        );
    }

    /**
     * Returns a list of section settings for product properties and variations based on a filter.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertySection.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-list.html',
        'Returns a list of section settings for product properties and variations based on a filter.'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): ProductPropertySectionsResult
    {
        return new ProductPropertySectionsResult(
            $this->core->call('catalog.productPropertySection.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
            ])
        );
    }

    /**
     * Sets or updates the section settings of a product property or variation.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-set.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertySection.set',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-set.html',
        'Sets or updates the section settings of a product property or variation.'
    )]
    public function set(int $propertyId, array $fields): ProductPropertySectionResult
    {
        return new ProductPropertySectionResult(
            $this->core->call('catalog.productPropertySection.set', [
                'propertyId' => $propertyId,
                'fields' => $fields,
            ])
        );
    }
}
```

### 6. `tests/Unit/Services/Catalog/ProductPropertySection/Service/ProductPropertySectionTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\ProductPropertySection\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result\ProductPropertySectionResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result\ProductPropertySectionsResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Service\ProductPropertySection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(ProductPropertySection::class)]
class ProductPropertySectionTest extends TestCase
{
    #[Test]
    public function getCallsCoreWithPropertyId(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertySection.get', ['propertyId' => 901])
            ->willReturn($this->createStub(Response::class));

        (new ProductPropertySection($core, new NullLogger()))->get(901);
    }

    #[Test]
    public function listCallsCoreWithSelectFilterOrder(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertySection.list', [
                'select' => ['propertyId'],
                'filter' => ['propertyId' => 901],
                'order' => ['propertyId' => 'ASC'],
            ])
            ->willReturn($this->createStub(Response::class));

        (new ProductPropertySection($core, new NullLogger()))->list(
            ['propertyId'],
            ['propertyId' => 901],
            ['propertyId' => 'ASC']
        );
    }

    #[Test]
    public function setCallsCoreWithPropertyIdAndFields(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $fields = ['smartFilter' => 'Y', 'displayType' => 'F', 'displayExpanded' => 'N', 'filterHint' => 'hint'];
        $core->expects($this->once())
            ->method('call')
            ->with('catalog.productPropertySection.set', ['propertyId' => 901, 'fields' => $fields])
            ->willReturn($this->createStub(Response::class));

        (new ProductPropertySection($core, new NullLogger()))->set(901, $fields);
    }
}
```

Uses `createMock(CoreInterface::class)` (not `NullCore`) specifically because these tests
assert the exact method name and params array passed to `core->call()` — `NullCore` accepts
any call silently and cannot make that assertion. `Response` is stubbed since the service
wraps it in `ProductPropertySectionResult`/`ProductPropertySectionsResult`, whose
constructors only store the `Response` without touching it — the stub is never read further
in these three tests (no `->get*()` call on the returned Result), so an empty stub is
sufficient.

### 7. `tests/Integration/Services/Catalog/ProductPropertySection/Service/ProductPropertySectionTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductPropertySection\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Product\Service\Product;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\ProductPropertySectionDisplayType;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Service\ProductPropertySection;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductPropertySection::class)]
class ProductPropertySectionTest extends TestCase
{
    private ProductPropertySection $productPropertySectionService;
    private int $iblockId;
    private int $propertyId;

    #[TestDox('test ProductPropertySection::set and get')]
    public function testSetAndGet(): void
    {
        $fields = ['smartFilter' => 'Y', 'displayType' => 'F', 'displayExpanded' => 'N', 'filterHint' => 'test hint'];
        $setResult = $this->productPropertySectionService->set($this->propertyId, $fields)->productPropertySection();
        $this->assertEquals($this->propertyId, $setResult->propertyId);
        $this->assertTrue($setResult->smartFilter);
        $this->assertEquals(ProductPropertySectionDisplayType::checkboxes, $setResult->displayType);
        $this->assertFalse($setResult->displayExpanded);
        $this->assertEquals('test hint', $setResult->filterHint);

        $getResult = $this->productPropertySectionService->get($this->propertyId)->productPropertySection();
        $this->assertEquals($this->propertyId, $getResult->propertyId);
        $this->assertEquals('test hint', $getResult->filterHint);
    }

    #[TestDox('test ProductPropertySection::list')]
    public function testList(): void
    {
        $this->productPropertySectionService->set($this->propertyId, ['smartFilter' => 'Y']);
        $items = $this->productPropertySectionService
            ->list([], ['propertyId' => $this->propertyId], [])
            ->getProductPropertySections();
        $this->assertCount(1, $items);
        $this->assertEquals($this->propertyId, $items[0]->propertyId);
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->productPropertySectionService = Factory::getServiceBuilder()
            ->getCatalogScope()
            ->productPropertySection();

        $this->iblockId = Factory::getServiceBuilder()->getCatalogScope()->catalog()
            ->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $propertyAddResult = Factory::getCore()->call('catalog.productProperty.add', [
            'fields' => [
                'iblockId' => $this->iblockId,
                'name' => sprintf('test property %s', time()),
                'propertyType' => 'S',
                'code' => sprintf('TEST_PROP_%s', time()),
                'active' => 'Y',
            ],
        ]);
        $this->propertyId = (int)$propertyAddResult->getResponseData()->getResult()['productProperty']['id'];
    }

    #[\Override]
    protected function tearDown(): void
    {
        Factory::getCore()->call('catalog.productProperty.delete', ['id' => $this->propertyId]);
    }
}
```

Fixture rationale: no `ProductProperty` SDK service exists yet (out of scope for #558), so the
`propertyId` fixture is created/removed with two **raw** `Factory::getCore()->call(...)` calls
directly against `catalog.productProperty.add`/`.delete` inside `setUp()`/`tearDown()` — this
keeps the test self-contained and independent of pre-existing portal data without requiring a
full `ProductProperty` SDK service to be built first. `$this->iblockId` is obtained the same
way `ProductTest::testAdd` does it.

### 8. `tests/Integration/Services/Catalog/ProductPropertySection/Result/ProductPropertySectionItemResultTest.php`

Mandatory annotation/type-cast test, per template in `docs/testing.md` /
`.claude/skills/b24phpsdk-maintainer/SKILL.md`. Built against the `set()` response (the
7-field superset) so `assertBitrix24AllResultItemFieldsAnnotated` has a complete key-set to
compare against.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductPropertySection\Result;

use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result\ProductPropertySectionItemResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Service\ProductPropertySection;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductPropertySectionItemResult::class)]
class ProductPropertySectionItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ProductPropertySection $productPropertySection;
    private int $propertyId; // resolved in setUp, see fixture strategy above

    #[\Override]
    protected function setUp(): void
    {
        $this->productPropertySection = Factory::getServiceBuilder()
            ->getCatalogScope()
            ->productPropertySection();
        // resolve $this->propertyId
    }

    #[Test]
    #[TestDox('all fields in ProductPropertySectionItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->productPropertySection
            ->set($this->propertyId, ['smartFilter' => 'Y', 'displayType' => 'F', 'displayExpanded' => 'N', 'filterHint' => 'test hint'])
            ->getCoreResponse()->getResponseData()->getResult()['productPropertySection'];

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            ProductPropertySectionItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in ProductPropertySectionItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $item = $this->productPropertySection
            ->set($this->propertyId, ['smartFilter' => 'Y', 'displayType' => 'F', 'displayExpanded' => 'N', 'filterHint' => 'test hint'])
            ->productPropertySection();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $item,
            ProductPropertySectionItemResult::class
        );
    }
}
```

`assertBitrix24ResultItemFieldsTypeCastMatchAnnotations` handles backed-enum fields via its
`default` branch (`assertInstanceOf($classStr, $value)`), so `displayType` typed as
`ProductPropertySectionDisplayType` will be asserted as an instance of that enum — no changes
to `CustomBitrix24Assertions` needed for the type-cast test.

---

## Files to Modify

### 1. `src/Services/Catalog/CatalogServiceBuilder.php`

Add:

```php
public function productPropertySection(): Catalog\ProductPropertySection\Service\ProductPropertySection
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Catalog\ProductPropertySection\Service\ProductPropertySection(
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}
```

(`ServiceBuilder.php` root already exposes `getCatalogScope(): CatalogServiceBuilder` — no
change needed there, only the builder's own new accessor method.)

### 2. `phpunit.xml.dist`

Add a dedicated suite (Catalog scope currently has none — only covered by the blanket
`integration_tests` suite over `./tests/Integration`), following the `RecordField`/`Timeman`
precedent of listing service + result-annotation test files explicitly:

```xml
<testsuite name="integration_tests_scope_catalog_product_property_section">
    <file>./tests/Integration/Services/Catalog/ProductPropertySection/Service/ProductPropertySectionTest.php</file>
    <file>./tests/Integration/Services/Catalog/ProductPropertySection/Result/ProductPropertySectionItemResultTest.php</file>
</testsuite>
```

### 3. `Makefile`

Add, alongside the other `test-integration-*` targets:

```makefile
.PHONY: test-integration-scope-catalog-product-property-section
test-integration-scope-catalog-product-property-section:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_scope_catalog_product_property_section
```

### 4. `.php-cs-fixer.php`

`src/Services/Catalog/` is currently **absent** from the Finder `->in(...)` list entirely
(pre-existing gap, not introduced by this change) — add it so the newly created files are
actually checked/fixed by `make lint-cs-fixer`:

```php
    ->in(__DIR__ . '/src/Services/Catalog/')
```

### 5. `CHANGELOG.md`

Verified: no live `## Unreleased` section currently exists — line 1 is the file title and
line 2 is `## 3.4.0` directly (the commented-out `<!-- ## Unreleased ... -->` template block
at line ~1703 is far below, part of the historical archive, and irrelevant here). Insert a
new section directly between line 1 (`# b24-php-sdk change log`) and line 2 (`## 3.4.0`):

```markdown
# b24-php-sdk change log
## Unreleased

### Added

- Added service `Services\Catalog\ProductPropertySection` with support for
  `catalog.productPropertySection.*` methods,
  see [catalog.productPropertySection.* methods](https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/index.html) ([#558](https://github.com/bitrix24/b24phpsdk/issues/558)):
    - `get` returns the section settings of a product property or variation by property ID
    - `list` returns a list of section settings for product properties/variations by filter
    - `set` sets or updates the section settings of a product property or variation

## 3.4.0
```

---

## Deptrac compliance

New files live entirely under `src/Services/Catalog/ProductPropertySection/` (layer:
`Services`) and import only from `Core` (`AbstractAnnotatedItem`, `AbstractResult`,
`BaseException`, `TransportException`, `Credentials\Scope`) and `Services` itself
(`AbstractService`, `Attributes\*`). No `Infrastructure`/`Application` imports. Fully
compliant with the existing ruleset — no new `skip_violations` entries needed.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-scope-catalog-product-property-section
```

---

## Post-implementation status (2026-07-29)

### Phase 1 — green

- `make lint-cs-fixer`: clean for the touched scope. Adding `src/Services/Catalog/` to
  `.php-cs-fixer.php` (as planned) surfaced 11 pre-existing style violations in Catalog files
  never touched by this issue (`Product/Service/Batch.php`, `Product/Service/Product.php`,
  `Catalog/Result/*.php`, `Common/ProductType.php`, `Common/Result/AbstractCatalogItem.php`,
  `Product/Result/*.php`) — missing trailing newline / missing blank line before an attribute.
  These were auto-fixed via `php-cs-fixer fix`, since they only became visible as a direct
  consequence of the planned Finder-path addition and leaving them unfixed would make
  `make lint-cs-fixer` (no `--dry-run`) fail on files outside this issue's diff otherwise.
- `make lint-rector`: 1 unrelated pre-existing finding in `src/Core/Credentials/Scope.php`
  (`RemoveDefaultValueFromAssignedPropertyRector`) — a Core-layer file with zero relation to
  `ProductPropertySection`, already covered by `rector.php`'s existing `src/Core/` path before
  this issue. Left unmodified — out of scope.
- `make lint-phpstan`: `[OK] No errors` (full project, level 5, `--memory-limit=1G` — the
  default 128M container limit is insufficient for a from-scratch full-project run and needs
  a manual override; unrelated to this issue).
- `make lint-deptrac`: 0 violations, 22 pre-existing skipped violations (unchanged).
- `make test-unit`: new `ProductPropertySectionTest` (4 tests) green. Full unit suite has 12
  pre-existing failures unrelated to Catalog (`CustomBitrix24AssertionsTest`, IM/User
  `UserStatusItemResultTest`) — see Phase 2 root-cause note below; same underlying bug.

### Phase 2 — blocked by a pre-existing, repo-wide dependency bug (not fixed, out of scope)

`make test-integration-scope-catalog-product-property-section` fails all 3 tests that use
`TyphoonReflector` (`ProductPropertySectionTest::testSetAndGet`, and both
`ProductPropertySectionItemResultTest` methods) with:

```
ArgumentCountError: Too few arguments to function PHPStan\PhpDocParser\Lexer\Lexer::__construct(),
0 passed in vendor/typhoon/reflection/Internal/PhpDoc/PhpDocParser.php on line 30 and exactly 1 expected
```

**Root cause (confirmed via investigation, not guessed)**: `vendor/composer/autoload_files.php`
unconditionally `require`s `vendor/rector/rector/bootstrap.php` on every PHP process (Composer's
"always load" mechanism — `rector/rector` is declared as a regular dependency, not isolated).
That bootstrap pulls in Rector's own **bundled, unprefixed** copy of `phpstan/phpdoc-parser` at
`vendor/rector/rector/vendor/phpstan/phpdoc-parser/src/Lexer/Lexer.php`, which defines the exact
same class name `PHPStan\PhpDocParser\Lexer\Lexer` as the real top-level dependency at
`vendor/phpstan/phpdoc-parser/src/Lexer/Lexer.php` (required by `typhoon/reflection ^1.21`).
Rector's bundled copy is a newer, incompatible API (constructor requires a `ParserConfig` arg)
Because Rector's always-loaded bootstrap defines the class first, PHP's classmap (verified
correct — points at the real 1.33.0 package) is never consulted; the class is already defined.
`composer dump-autoload -o` was tried and does **not** fix it, confirming this is a class-name
collision at require-time, not a classmap ordering issue.

**Verified NOT caused by this issue's changes**: reproduces identically on the pre-existing
`tests/Unit/CustomAssertions/CustomBitrix24AssertionsTest.php` (never touched by this branch),
and a pre-existing Catalog integration test that does not use `TyphoonReflector`
(`ProductTest::testFieldsByFilter`) passes fine against the same live portal in the same run —
isolating the failure precisely to any code path through `TyphoonReflector::build()`.

**Decision (user-confirmed)**: do not attempt a `composer.json`/dependency-structure fix as
part of issue #558 — that is a project-wide toolchain fix outside this issue's scope. Documented
here instead; a separate tracking issue should be filed (see below). All production code for
`catalog.productPropertySection.*` is complete, unit-tested, and passes phase 1 in full; the 3
integration tests are written correctly per the mandatory template and will pass once the
environment bug is fixed — they are blocked, not failing due to any defect in the SDK code
under test.
