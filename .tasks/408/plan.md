# Plan: Add support for rest.scope.list (issue #408)

## Context

`rest.scope.list` is a v3 REST API method that returns the full list of API methods available
to the application, structured as a nested map: `module → controller → method → item`.

Each leaf item has four fields:
- `scope` (string) — full method name, e.g. `rest.scope.list`
- `title` (string) — human-readable title
- `description` (string) — method description
- `fields` (null|array) — field metadata or null

Request parameters (all optional):
- `filterModule` (string) — filter by module name, e.g. `rest`
- `filterController` (string) — filter by controller name
- `filterMethod` (string) — filter by method name

The service belongs to the `rest` scope. Since no `Rest` service builder exists yet, this
implementation creates a new `src/Services/Rest/` directory and registers it in the top-level
`ServiceBuilder`.

The response key is the top-level `result` object returned by `getCoreResponse()->getResponseData()->getResult()`.
`ScopeMethodsResult::getItems()` flattens the three-level nested structure into a flat
`ScopeMethodItemResult[]` list.

---

## Files to Create

### 1. `src/Services/Rest/RestServiceBuilder.php`

```php
namespace Bitrix24\SDK\Services\Rest;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Rest\Service\Scope;

#[ApiServiceBuilderMetadata(new Scope(['rest']))]
class RestServiceBuilder extends AbstractServiceBuilder
{
    public function scope(): Scope
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Scope($this->core, $this->log);
        }
        return $this->serviceCache[__METHOD__];
    }
}
```

### 2. `src/Services/Rest/Service/Scope.php`

```php
namespace Bitrix24\SDK\Services\Rest\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope as ScopeCredential;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Rest\Result\ScopeMethodsResult;

#[ApiServiceMetadata(new ScopeCredential(['rest']))]
class Scope extends AbstractService
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'rest.scope.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/rest/rest-scope-list.html',
        'Returns the list of available REST API methods grouped by module/controller/method',
        ApiVersion::v3
    )]
    public function list(
        ?string $filterModule = null,
        ?string $filterController = null,
        ?string $filterMethod = null,
    ): ScopeMethodsResult {
        return new ScopeMethodsResult(
            $this->core->call('rest.scope.list', [
                'filterModule'     => $filterModule,
                'filterController' => $filterController,
                'filterMethod'     => $filterMethod,
            ], ApiVersion::v3)
        );
    }
}
```

### 3. `src/Services/Rest/Result/ScopeMethodItemResult.php`

```php
namespace Bitrix24\SDK\Services\Rest\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read string     $scope
 * @property-read string     $title
 * @property-read string     $description
 * @property-read array|null $fields
 */
class ScopeMethodItemResult extends AbstractItem {}
```

### 4. `src/Services/Rest/Result/ScopeMethodsResult.php`

```php
namespace Bitrix24\SDK\Services\Rest\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ScopeMethodsResult extends AbstractResult
{
    /**
     * Flattens module → controller → method → item into a flat list.
     *
     * @return ScopeMethodItemResult[]
     * @throws BaseException
     */
    public function getItems(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $controllers) {
            foreach ($controllers as $methods) {
                foreach ($methods as $item) {
                    $items[] = new ScopeMethodItemResult($item);
                }
            }
        }
        return $items;
    }
}
```

### 5. `tests/Unit/Services/Rest/Service/ScopeTest.php`

```php
namespace Bitrix24\SDK\Tests\Unit\Services\Rest\Service;

use Bitrix24\SDK\Services\Rest\Result\ScopeMethodsResult;
use Bitrix24\SDK\Services\Rest\Service\Scope;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Scope::class)]
class ScopeTest extends TestCase
{
    private Scope $service;

    protected function setUp(): void
    {
        $this->service = new Scope(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testListReturnsScopeMethodsResult(): void
    {
        $this->assertInstanceOf(ScopeMethodsResult::class, $this->service->list());
    }

    #[Test]
    public function testListWithFilterModuleReturnsScopeMethodsResult(): void
    {
        $this->assertInstanceOf(ScopeMethodsResult::class, $this->service->list('rest'));
    }
}
```

### 6. `tests/Integration/Services/Rest/Service/ScopeTest.php`

```php
namespace Bitrix24\SDK\Tests\Integration\Services\Rest\Service;

use Bitrix24\SDK\Services\Rest\Service\Scope;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
    private Scope $scopeService;

    protected function setUp(): void
    {
        $this->scopeService = Factory::getServiceBuilder()->getRestScope()->scope();
    }

    #[Test]
    public function testListReturnsItems(): void
    {
        $items = $this->scopeService->list('rest')->getItems();
        $this->assertNotEmpty($items);
    }

    #[Test]
    public function testListWithFilterModuleReturnsOnlyMatchingItems(): void
    {
        $items = $this->scopeService->list('rest')->getItems();
        foreach ($items as $item) {
            $this->assertStringStartsWith('rest.', $item->scope);
        }
    }
}
```

### 7. `tests/Integration/Services/Rest/Result/ScopeMethodItemResultTest.php`

```php
namespace Bitrix24\SDK\Tests\Integration\Services\Rest\Result;

use Bitrix24\SDK\Services\Rest\Result\ScopeMethodItemResult;
use Bitrix24\SDK\Services\Rest\Service\Scope;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScopeMethodItemResult::class)]
class ScopeMethodItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Scope $scopeService;

    protected function setUp(): void
    {
        $this->scopeService = Factory::getServiceBuilder()->getRestScope()->scope();
    }

    #[Test]
    #[TestDox('all fields in ScopeMethodItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $raw = $this->scopeService->list('rest')
            ->getCoreResponse()->getResponseData()->getResult();
        // Navigate to first leaf item: module -> controller -> method -> item
        $firstModule     = array_key_first($raw);
        $firstController = array_key_first($raw[$firstModule]);
        $firstMethod     = array_key_first($raw[$firstModule][$firstController]);
        $rawItem         = $raw[$firstModule][$firstController][$firstMethod];

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            ScopeMethodItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in ScopeMethodItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $items = $this->scopeService->list('rest')->getItems();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $items[0],
            ScopeMethodItemResult::class
        );
    }
}
```

---

## Files to Modify

### 1. `src/Services/ServiceBuilder.php`

Add import at top:
```php
use Bitrix24\SDK\Services\Rest\RestServiceBuilder;
```

Add method after `getListsScope()`:
```php
public function getRestScope(): RestServiceBuilder
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new RestServiceBuilder(
            $this->core,
            $this->batch,
            $this->bulkItemsReader,
            $this->log
        );
    }
    return $this->serviceCache[__METHOD__];
}
```

### 2. `phpunit.xml.dist`

Add before closing `</testsuites>`:
```xml
<testsuite name="integration_tests_scope_rest">
    <directory>./tests/Integration/Services/Rest/</directory>
</testsuite>
<testsuite name="integration_tests_rest_scope_service">
    <file>./tests/Integration/Services/Rest/Service/ScopeTest.php</file>
    <file>./tests/Integration/Services/Rest/Result/ScopeMethodItemResultTest.php</file>
</testsuite>
```

### 3. `Makefile`

Add after `test-integration-main-eventlog`:
```makefile
.PHONY: test-integration-rest-scope
test-integration-rest-scope:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_rest_scope_service
```

### 4. `CHANGELOG.md`

Under `## 3.1.0 Unreleased` → `### Added`:
```markdown
### Added
- Added `RestServiceBuilder` with `Scope` service for `rest.scope.list` support ([#408](https://github.com/bitrix24/b24phpsdk/issues/408))
```

---

## Deptrac compliance

All new code lives in `src/Services/Rest/` (Services layer) and imports only from:
- `Bitrix24\SDK\Core\*` — allowed (Services may depend on Core)
- `Bitrix24\SDK\Attributes\*` — allowed (not a restricted layer)
- `Bitrix24\SDK\Services\AbstractService` / `AbstractServiceBuilder` — allowed (same Services layer)

No cross-scope service imports. No new `skip_violations` entries needed.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-rest-scope
```
