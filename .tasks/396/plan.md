# Plan: Add support for tasks.task.access.field.* (issue #396)

## Context

Issue #396 requests SDK support for two REST API v3 methods:

| Method | Description | Returns |
|---|---|---|
| `tasks.task.access.field.get` | Get description of a single access field by name | `result.item` (object) |
| `tasks.task.access.field.list` | Get list of all available access fields | `result.items` (array) |

Both methods belong to scope `task` and support an optional `select` array parameter with the following available fields:
`name`, `type`, `title`, `description`, `validationRules`, `requiredGroups`, `filterable`, `sortable`, `editable`, `multiple`, `elementType`.

Method `get` additionally requires a mandatory `name` string parameter (field code, e.g. `'id'`).

**Pattern reference**: `FileField` service (`src/Services/Task/FileField/`) — identical structure, same field set, same API version (v3).

**Directory name convention**: `AccessField` (matches `ChatMessageField`, `FileField`).

**TaskServiceBuilder accessor name**: `taskAccessField()`.

---

## Files to Create

### 1. `src/Services/Task/AccessField/Result/AccessFieldItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\AccessField\Result;

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
class AccessFieldItemResult extends AbstractItem {}
```

### 2. `src/Services/Task/AccessField/Result/AccessFieldResult.php`

Single-item response for `tasks.task.access.field.get`. Key in response: `item`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\AccessField\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class AccessFieldResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function accessField(): AccessFieldItemResult
    {
        return new AccessFieldItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()['item']
        );
    }
}
```

### 3. `src/Services/Task/AccessField/Result/AccessFieldsResult.php`

List response for `tasks.task.access.field.list`. Key in response: `items`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\AccessField\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class AccessFieldsResult extends AbstractResult
{
    /**
     * @return AccessFieldItemResult[]
     * @throws BaseException
     */
    public function getAccessFields(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new AccessFieldItemResult($item);
        }
        return $items;
    }
}
```

### 4. `src/Services/Task/AccessField/Service/AccessField.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\AccessField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Task\AccessField\Result\AccessFieldResult;
use Bitrix24\SDK\Services\Task\AccessField\Result\AccessFieldsResult;

#[ApiServiceMetadata(new Scope(['task']))]
class AccessField extends AbstractService
{
    #[ApiEndpointMetadata(
        'tasks.task.access.field.get',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-access-field-get.html',
        'Get metadata for a single task access field by field code',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): AccessFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');
        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }
        return new AccessFieldResult(
            $this->core->call('tasks.task.access.field.get', $params, ApiVersion::v3)
        );
    }

    #[ApiEndpointMetadata(
        'tasks.task.access.field.list',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-access-field-list.html',
        'Get list of all available task access field descriptors',
        ApiVersion::v3
    )]
    public function list(array $select = []): AccessFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];
        return new AccessFieldsResult(
            $this->core->call('tasks.task.access.field.list', $params, ApiVersion::v3)
        );
    }
}
```

### 5. `tests/Unit/Services/Task/AccessField/Service/AccessFieldTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Task\AccessField\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Task\AccessField\Result\AccessFieldResult;
use Bitrix24\SDK\Services\Task\AccessField\Result\AccessFieldsResult;
use Bitrix24\SDK\Services\Task\AccessField\Service\AccessField;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(AccessField::class)]
class AccessFieldTest extends TestCase
{
    private AccessField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new AccessField(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetReturnsAccessFieldResult(): void
    {
        $this->assertInstanceOf(AccessFieldResult::class, $this->service->get('id'));
    }

    #[Test]
    public function testListReturnsAccessFieldsResult(): void
    {
        $this->assertInstanceOf(AccessFieldsResult::class, $this->service->list());
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

### 6. `tests/Integration/Services/Task/AccessField/Service/AccessFieldTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Task\AccessField\Service;

use Bitrix24\SDK\Services\Task\AccessField\Result\AccessFieldItemResult;
use Bitrix24\SDK\Services\Task\AccessField\Service\AccessField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AccessField::class)]
class AccessFieldTest extends TestCase
{
    use CustomBitrix24Assertions;

    private AccessField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getTaskScope()->taskAccessField();
    }

    #[Test]
    public function testList(): void
    {
        $fields = $this->service->list()->getAccessFields();
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
    }

    #[Test]
    public function testGet(): void
    {
        $accessFieldItemResult = $this->service->get('id')->accessField();
        $this->assertNotEmpty($accessFieldItemResult->name);
        $this->assertNotEmpty($accessFieldItemResult->type);
        $this->assertNotEmpty($accessFieldItemResult->title);
    }

    #[Test]
    public function testAllFieldsAnnotated(): void
    {
        $rawItems = $this->service->list()->getCoreResponse()->getResponseData()->getResult()['items'];
        $this->assertNotEmpty($rawItems);
        $fieldCodesFromApi = array_keys($rawItems[0]);
        $this->assertBitrix24AllResultItemFieldsAnnotated($fieldCodesFromApi, AccessFieldItemResult::class);
    }
}
```

### 7. `tests/Integration/Services/Task/AccessField/Result/AccessFieldItemResultTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Task\AccessField\Result;

use Bitrix24\SDK\Services\Task\AccessField\Result\AccessFieldItemResult;
use Bitrix24\SDK\Services\Task\AccessField\Service\AccessField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(AccessFieldItemResult::class)]
class AccessFieldItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private AccessField $accessFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->accessFieldService = Factory::getServiceBuilder()->getTaskScope()->taskAccessField();
    }

    #[Test]
    #[TestDox('all fields in AccessFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->accessFieldService->get('id')->getCoreResponse()
            ->getResponseData()->getResult()['item'];

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            AccessFieldItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in AccessFieldItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $accessFieldItemResult = $this->accessFieldService->get('id')->accessField();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $accessFieldItemResult,
            AccessFieldItemResult::class
        );
    }
}
```

---

## Files to Modify

### 1. `src/Services/Task/TaskServiceBuilder.php`

Add accessor method after `taskFileField()`:

```php
public function taskAccessField(): AccessField\Service\AccessField
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new AccessField\Service\AccessField(
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}
```

### 2. `phpunit.xml.dist`

Add new testsuite block after the `integration_tests_task_file_field` block:

```xml
<testsuite name="integration_tests_task_access_field">
    <file>./tests/Integration/Services/Task/AccessField/Service/AccessFieldTest.php</file>
    <file>./tests/Integration/Services/Task/AccessField/Result/AccessFieldItemResultTest.php</file>
</testsuite>
```

### 3. `Makefile`

Add make target after `test-integration-task-file-field`:

```makefile
.PHONY: test-integration-task-access-field
test-integration-task-access-field:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_task_access_field
```

### 4. `CHANGELOG.md`

Add entry under `## 3.1.0 Unreleased` → `### Added`:

```markdown
- Added support for `tasks.task.access.field.get` and `tasks.task.access.field.list` via `AccessField` service ([#396](https://github.com/bitrix24/b24phpsdk/issues/396))
```

---

## Deptrac compliance

`AccessField` service depends only on:
- `Bitrix24\SDK\Core\*` (interfaces, attributes, credentials, exceptions)
- `Bitrix24\SDK\Services\AbstractService`

No cross-service dependencies, no Infrastructure imports. Fully compliant with the Services layer rules.

---

## Verification

```bash
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-task-access-field
```
