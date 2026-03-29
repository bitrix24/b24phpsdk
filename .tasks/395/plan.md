# Plan: Add support for tasks.task.field.* (issue #395)

## Context

Issue #395 requests adding SDK support for two REST API v3 methods:

- `tasks.task.field.get` — returns a single task field descriptor by name (`result.item`)
- `tasks.task.field.list` — returns all available task field descriptors (`result.items`)

Both methods accept an optional `select` array parameter with available fields:
`name`, `type`, `title`, `description`, `validationRules`, `requiredGroups`,
`filterable`, `sortable`, `editable`, `multiple`, `elementType`.

`tasks.task.field.get` additionally requires `name` (field code, e.g. `'id'`).

Response keys:
- `tasks.task.field.get` → `result['item']` (single object)
- `tasks.task.field.list` → `result['items']` (array)

The pattern follows `ChatMessageField`, `FileField`, and `AccessField` exactly.
Namespace: `Bitrix24\SDK\Services\Task\TaskField\`.
Service accessor in builder: `taskField()`.
API version: v3 (base branch: `v3-dev`).

---

## Files to Create

### 1. `src/Services/Task/TaskField/Result/TaskFieldItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\TaskField\Result;

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
class TaskFieldItemResult extends AbstractItem
{
}
```

### 2. `src/Services/Task/TaskField/Result/TaskFieldResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\TaskField\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class TaskFieldResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function taskField(): TaskFieldItemResult
    {
        return new TaskFieldItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()['item']
        );
    }
}
```

### 3. `src/Services/Task/TaskField/Result/TaskFieldsResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\TaskField\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class TaskFieldsResult extends AbstractResult
{
    /**
     * @return TaskFieldItemResult[]
     * @throws BaseException
     */
    public function getTaskFields(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new TaskFieldItemResult($item);
        }
        return $items;
    }
}
```

### 4. `src/Services/Task/TaskField/Service/TaskField.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\TaskField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Task\TaskField\Result\TaskFieldResult;
use Bitrix24\SDK\Services\Task\TaskField\Result\TaskFieldsResult;

#[ApiServiceMetadata(new Scope(['task']))]
class TaskField extends AbstractService
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.field.get',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-field-get.html',
        'Get metadata for a single task field by field name',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): TaskFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');
        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }
        return new TaskFieldResult(
            $this->core->call('tasks.task.field.get', $params, ApiVersion::v3)
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.field.list',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-field-list.html',
        'Get list of all available task field descriptors',
        ApiVersion::v3
    )]
    public function list(array $select = []): TaskFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];
        return new TaskFieldsResult(
            $this->core->call('tasks.task.field.list', $params, ApiVersion::v3)
        );
    }
}
```

### 5. `tests/Unit/Services/Task/TaskField/Service/TaskFieldTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Task\TaskField\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Task\TaskField\Result\TaskFieldResult;
use Bitrix24\SDK\Services\Task\TaskField\Result\TaskFieldsResult;
use Bitrix24\SDK\Services\Task\TaskField\Service\TaskField;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(TaskField::class)]
class TaskFieldTest extends TestCase
{
    private TaskField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new TaskField(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetReturnsTaskFieldResult(): void
    {
        $this->assertInstanceOf(
            TaskFieldResult::class,
            $this->service->get('id')
        );
    }

    #[Test]
    public function testListReturnsTaskFieldsResult(): void
    {
        $this->assertInstanceOf(
            TaskFieldsResult::class,
            $this->service->list()
        );
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

### 6. `tests/Integration/Services/Task/TaskField/Service/TaskFieldTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Task\TaskField\Service;

use Bitrix24\SDK\Services\Task\TaskField\Result\TaskFieldItemResult;
use Bitrix24\SDK\Services\Task\TaskField\Service\TaskField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskField::class)]
class TaskFieldTest extends TestCase
{
    use CustomBitrix24Assertions;

    private TaskField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getTaskScope()->taskField();
    }

    #[Test]
    public function testList(): void
    {
        $fields = $this->service->list()->getTaskFields();
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
    }

    #[Test]
    public function testGet(): void
    {
        $fieldItem = $this->service->get('id')->taskField();
        $this->assertNotEmpty($fieldItem->name);
        $this->assertNotEmpty($fieldItem->type);
        $this->assertNotEmpty($fieldItem->title);
    }

    #[Test]
    public function testAllFieldsAnnotated(): void
    {
        $rawItems = $this->service->list()->getCoreResponse()->getResponseData()->getResult()['items'];
        $this->assertNotEmpty($rawItems);
        $fieldCodesFromApi = array_keys($rawItems[0]);
        $this->assertBitrix24AllResultItemFieldsAnnotated($fieldCodesFromApi, TaskFieldItemResult::class);
    }
}
```

### 7. `tests/Integration/Services/Task/TaskField/Result/TaskFieldItemResultTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Task\TaskField\Result;

use Bitrix24\SDK\Services\Task\TaskField\Result\TaskFieldItemResult;
use Bitrix24\SDK\Services\Task\TaskField\Service\TaskField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskFieldItemResult::class)]
class TaskFieldItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private TaskField $taskFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->taskFieldService = Factory::getServiceBuilder()->getTaskScope()->taskField();
    }

    #[Test]
    #[TestDox('all fields in TaskFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $allFields = $this->taskFieldService->get('id')->getCoreResponse()
            ->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($allFields), TaskFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in TaskFieldItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $taskFieldItemResult = $this->taskFieldService->get('id')->taskField();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $taskFieldItemResult,
            TaskFieldItemResult::class
        );
    }
}
```

---

## Files to Modify

### 1. `src/Services/Task/TaskServiceBuilder.php`

Add after the `taskAccessField()` method (line ~98):

```php
public function taskField(): TaskField\Service\TaskField
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new TaskField\Service\TaskField(
            $this->core,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}
```

### 2. `phpunit.xml.dist`

Add after `integration_tests_task_access_field` suite (after line ~200):

```xml
<testsuite name="integration_tests_task_field">
    <file>./tests/Integration/Services/Task/TaskField/Service/TaskFieldTest.php</file>
    <file>./tests/Integration/Services/Task/TaskField/Result/TaskFieldItemResultTest.php</file>
</testsuite>
```

### 3. `Makefile`

Add after `test-integration-task-access-field` target:

```makefile
.PHONY: test-integration-task-field
test-integration-task-field:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_task_field
```

### 4. `CHANGELOG.md`

Add under `## 3.1.0 Unreleased` → `### Added`:

```markdown
- Added `TaskField` service for `tasks.task.field.get` and `tasks.task.field.list` support ([#395](https://github.com/bitrix24/b24phpsdk/issues/395))
```

---

## Deptrac compliance

- `TaskField` service is in `Services` layer → may depend on `Core` and `Application` — no violations
- No cross-service imports
- No `Infrastructure` imports

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-task-field
```
