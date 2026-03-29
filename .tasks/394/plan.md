# Plan: Add support for main.eventlog.field.* (issue #394)

## Context

Issue #394 requests support for two REST API v3 methods:
- `main.eventlog.field.get` — returns metadata for a single event log field by name
- `main.eventlog.field.list` — returns list of all available event log field descriptors

Both methods belong to `scope: main`.

**API response shapes (confirmed from official docs):**
- `field.get` → `result.item` (single object)
- `field.list` → `result.items` (array of objects)

**Field descriptor properties** (available via `select`):
`name`, `type`, `title`, `description`, `validationRules`, `requiredGroups`,
`filterable`, `sortable`, `editable`, `multiple`, `elementType`

**Implementation pattern**: identical to `src/Services/Task/ChatMessageField/` —
new sub-directory under `src/Services/Main/EventLogField/` with `Service/` and `Result/` sub-directories.

**Base branch**: `v3-dev`. Branch: `feature/394-add-main-eventlog-field`.

---

## Files to Create

### 1. `src/Services/Main/EventLogField/Result/EventLogFieldItemResult.php`

```php
<?php
declare(strict_types=1);
namespace Bitrix24\SDK\Services\Main\EventLogField\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read string      $name
 * @property-read string      $type
 * @property-read string      $title
 * @property-read string|null $description
 * @property-read array|null  $validationRules
 * @property-read array|null  $requiredGroups
 * @property-read bool        $filterable
 * @property-read bool        $sortable
 * @property-read bool        $editable
 * @property-read bool        $multiple
 * @property-read string|null $elementType
 */
class EventLogFieldItemResult extends AbstractItem {}
```

### 2. `src/Services/Main/EventLogField/Result/EventLogFieldResult.php`

```php
<?php
declare(strict_types=1);
namespace Bitrix24\SDK\Services\Main\EventLogField\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class EventLogFieldResult extends AbstractResult
{
    /** @throws BaseException */
    public function eventLogField(): EventLogFieldItemResult
    {
        return new EventLogFieldItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()['item']
        );
    }
}
```

### 3. `src/Services/Main/EventLogField/Result/EventLogFieldsResult.php`

```php
<?php
declare(strict_types=1);
namespace Bitrix24\SDK\Services\Main\EventLogField\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class EventLogFieldsResult extends AbstractResult
{
    /**
     * @return EventLogFieldItemResult[]
     * @throws BaseException
     */
    public function getEventLogFields(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new EventLogFieldItemResult($item);
        }
        return $items;
    }
}
```

### 4. `src/Services/Main/EventLogField/Service/EventLogField.php`

```php
<?php
declare(strict_types=1);
namespace Bitrix24\SDK\Services\Main\EventLogField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Main\EventLogField\Result\EventLogFieldResult;
use Bitrix24\SDK\Services\Main\EventLogField\Result\EventLogFieldsResult;

#[ApiServiceMetadata(new Scope(['main']))]
class EventLogField extends AbstractService
{
    /**
     * Get metadata for a single event log field by name.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/rest-v3/main/main-eventlog-field-get.html
     *
     * @param non-empty-string $name   Field code, e.g. 'timestampX'
     * @param string[]         $select Fields to return.
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'main.eventlog.field.get',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/main/main-eventlog-field-get.html',
        'Get metadata for a single event log field by name',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): EventLogFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new EventLogFieldResult(
            $this->core->call('main.eventlog.field.get', $params, ApiVersion::v3)
        );
    }

    /**
     * Get list of all available event log field descriptors.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/rest-v3/main/main-eventlog-field-list.html
     *
     * @param string[] $select Fields to return.
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'main.eventlog.field.list',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/main/main-eventlog-field-list.html',
        'Get list of all available event log field descriptors',
        ApiVersion::v3
    )]
    public function list(array $select = []): EventLogFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];

        return new EventLogFieldsResult(
            $this->core->call('main.eventlog.field.list', $params, ApiVersion::v3)
        );
    }
}
```

### 5. `tests/Unit/Services/Main/EventLogField/Service/EventLogFieldTest.php`

```php
<?php
declare(strict_types=1);
namespace Bitrix24\SDK\Tests\Unit\Services\Main\EventLogField\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Main\EventLogField\Result\EventLogFieldResult;
use Bitrix24\SDK\Services\Main\EventLogField\Result\EventLogFieldsResult;
use Bitrix24\SDK\Services\Main\EventLogField\Service\EventLogField;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(EventLogField::class)]
class EventLogFieldTest extends TestCase
{
    private EventLogField $service;

    protected function setUp(): void
    {
        $this->service = new EventLogField(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetReturnsEventLogFieldResult(): void
    {
        $this->assertInstanceOf(EventLogFieldResult::class, $this->service->get('timestampX'));
    }

    #[Test]
    public function testListReturnsEventLogFieldsResult(): void
    {
        $this->assertInstanceOf(EventLogFieldsResult::class, $this->service->list());
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        $this->service->get('');
    }
}
```

### 6. `tests/Integration/Services/Main/EventLogField/Service/EventLogFieldTest.php`

```php
<?php
declare(strict_types=1);
namespace Bitrix24\SDK\Tests\Integration\Services\Main\EventLogField\Service;

use Bitrix24\SDK\Services\Main\EventLogField\Result\EventLogFieldItemResult;
use Bitrix24\SDK\Services\Main\EventLogField\Service\EventLogField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventLogField::class)]
class EventLogFieldTest extends TestCase
{
    use CustomBitrix24Assertions;

    private EventLogField $service;

    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getMainScope()->eventLogField();
    }

    #[Test]
    public function testList(): void
    {
        $fields = $this->service->list()->getEventLogFields();
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
    }

    #[Test]
    public function testGet(): void
    {
        $field = $this->service->get('timestampX')->eventLogField();
        $this->assertNotEmpty($field->name);
        $this->assertNotEmpty($field->type);
        $this->assertNotEmpty($field->title);
    }

    #[Test]
    public function testAllFieldsAnnotated(): void
    {
        $rawItems = $this->service->list()->getCoreResponse()->getResponseData()->getResult()['items'];
        $this->assertNotEmpty($rawItems);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItems[0]), EventLogFieldItemResult::class);
    }
}
```

### 7. `tests/Integration/Services/Main/EventLogField/Result/EventLogFieldItemResultTest.php`

```php
<?php
declare(strict_types=1);
namespace Bitrix24\SDK\Tests\Integration\Services\Main\EventLogField\Result;

use Bitrix24\SDK\Services\Main\EventLogField\Result\EventLogFieldItemResult;
use Bitrix24\SDK\Services\Main\EventLogField\Service\EventLogField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventLogFieldItemResult::class)]
class EventLogFieldItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private EventLogField $eventLogFieldService;

    protected function setUp(): void
    {
        $this->eventLogFieldService = Factory::getServiceBuilder()->getMainScope()->eventLogField();
    }

    #[Test]
    #[TestDox('all fields in EventLogFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $allFields = $this->eventLogFieldService->get('timestampX')
            ->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($allFields), EventLogFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in EventLogFieldItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $eventLogFieldItemResult = $this->eventLogFieldService->get('timestampX')->eventLogField();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $eventLogFieldItemResult,
            EventLogFieldItemResult::class
        );
    }
}
```

---

## Files to Modify

### 1. `src/Services/Main/MainServiceBuilder.php`

Add import:
```php
use Bitrix24\SDK\Services\Main\EventLogField\Service\EventLogField;
```

Add method after `eventLog()`:
```php
public function eventLogField(): EventLogField
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new EventLogField($this->core, $this->log);
    }
    return $this->serviceCache[__METHOD__];
}
```

### 2. `phpunit.xml.dist`

Replace the existing `integration_tests_scope_main_eventlog` suite:
```xml
<testsuite name="integration_tests_scope_main_eventlog">
    <file>./tests/Integration/Services/Main/Service/EventLogTest.php</file>
    <file>./tests/Integration/Services/Main/EventLogField/Service/EventLogFieldTest.php</file>
    <file>./tests/Integration/Services/Main/EventLogField/Result/EventLogFieldItemResultTest.php</file>
</testsuite>
```

### 3. `CHANGELOG.md`

Under `## 3.1.0 Unreleased` → `### Added`:
```markdown
- Added `EventLogField` service for `main.eventlog.field.*` support ([#394](https://github.com/bitrix24/b24phpsdk/issues/394))
```

---

## Deptrac compliance

`EventLogField` service is in `Services` layer.
It imports only from `Core` (`AbstractService`, `ApiVersion`, `Scope`, `BaseException`, `TransportException`) and its own `Result` classes.
No imports from `Application`, `Infrastructure`, or other `Services` — no new violations.

---

## Verification

```bash
make lint-cs-fixer
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-main-eventlog
```
