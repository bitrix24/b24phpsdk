# Plan: Add tasks.task.chat.message.field.* support (issue #397)

## Context

Bitrix24 REST API v3 exposes two methods for retrieving field descriptors of chat messages in tasks:
- `tasks.task.chat.message.field.get` — metadata for one field by code (`name` param, returns `result.item`)
- `tasks.task.chat.message.field.list` — all available field descriptors (returns `result.items`)

The SDK already has a `TaskChat` service for `tasks.task.chat.message.send`. The new methods are metadata
accessors for the **chat message field** domain object — a distinct concept that warrants a dedicated
sub-namespace, consistent with `Commentitem/`, `Elapseditem/`, `Checklistitem/`, etc.

**Real field properties** (from API docs, both methods):
`name`, `type`, `title`, `description`, `validationRules`, `requiredGroups`,
`filterable`, `sortable`, `editable`, `multiple`, `elementType`

---

## Files to Create

### 1. `src/Services/Task/Chatmessagefield/Result/ChatmessagefieldItemResult.php`

```php
namespace Bitrix24\SDK\Services\Task\Chatmessagefield\Result;
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
class ChatmessagefieldItemResult extends AbstractItem {}
```

### 2. `src/Services/Task/Chatmessagefield/Result/ChatmessagefieldResult.php`

API returns `result: { item: {...} }` — key `item` must be used (same as `EventLogResult`).

```php
namespace Bitrix24\SDK\Services\Task\Chatmessagefield\Result;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ChatmessagefieldResult extends AbstractResult
{
    /** @throws BaseException */
    public function chatmessagefield(): ChatmessagefieldItemResult
    {
        return new ChatmessagefieldItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()['item']
        );
    }
}
```

### 3. `src/Services/Task/Chatmessagefield/Result/ChatmessagefieldsResult.php`

API returns `result: { items: [...] }` — key `items` must be used (same as `EventLogsResult`).

```php
namespace Bitrix24\SDK\Services\Task\Chatmessagefield\Result;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ChatmessagefieldsResult extends AbstractResult
{
    /**
     * @return ChatmessagefieldItemResult[]
     * @throws BaseException
     */
    public function getChatmessagefields(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new ChatmessagefieldItemResult($item);
        }
        return $items;
    }
}
```

### 4. `src/Services/Task/Chatmessagefield/Service/Chatmessagefield.php`

- `get()`: param is `name` (string, non-empty); validate with `guardNonEmptyString()`; optional `select`
- `list()`: no required params; optional `select`

```php
namespace Bitrix24\SDK\Services\Task\Chatmessagefield\Service;

use Bitrix24\SDK\Attributes\{ApiEndpointMetadata, ApiServiceMetadata};
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\{BaseException, TransportException};
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Task\Chatmessagefield\Result\{ChatmessagefieldResult, ChatmessagefieldsResult};

#[ApiServiceMetadata(new Scope(['task']))]
class Chatmessagefield extends AbstractService
{
    /**
     * Get metadata for a single task chat message field by field code.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-chat-message-field-get.html
     *
     * @param non-empty-string $name   Field code, e.g. 'taskId'
     * @param string[]         $select Fields to return. Available: name, type, title, description,
     *                                 validationRules, requiredGroups, filterable, sortable,
     *                                 editable, multiple, elementType
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.chat.message.field.get',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-chat-message-field-get.html',
        'Get metadata for a single task chat message field by field code',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): ChatmessagefieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new ChatmessagefieldResult(
            $this->core->call('tasks.task.chat.message.field.get', $params, ApiVersion::v3)
        );
    }

    /**
     * Get list of all available task chat message field descriptors.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-chat-message-field-list.html
     *
     * @param string[] $select Fields to return. Available: name, type, title, description,
     *                         validationRules, requiredGroups, filterable, sortable,
     *                         editable, multiple, elementType
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.chat.message.field.list',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-chat-message-field-list.html',
        'Get list of all available task chat message field descriptors',
        ApiVersion::v3
    )]
    public function list(array $select = []): ChatmessagefieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];

        return new ChatmessagefieldsResult(
            $this->core->call('tasks.task.chat.message.field.list', $params, ApiVersion::v3)
        );
    }
}
```

### 5. `tests/Unit/Services/Task/Chatmessagefield/Service/ChatmessagefieldTest.php`

```php
#[CoversClass(Chatmessagefield::class)]
class ChatmessagefieldTest extends TestCase
{
    private Chatmessagefield $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new Chatmessagefield(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetReturnsChatmessagefieldResult(): void
    {
        $this->assertInstanceOf(
            ChatmessagefieldResult::class,
            $this->service->get('taskId')
        );
    }

    #[Test]
    public function testListReturnsChatmessagefieldsResult(): void
    {
        $this->assertInstanceOf(
            ChatmessagefieldsResult::class,
            $this->service->list()
        );
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->get('');
    }
}
```

### 6. `tests/Integration/Services/Task/Chatmessagefield/Service/ChatmessagefieldTest.php`

No tearDown needed — read-only metadata, no portal state is mutated.

```php
#[CoversClass(Chatmessagefield::class)]
class ChatmessagefieldTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Chatmessagefield $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getTaskScope()->chatmessagefield();
    }

    #[Test]
    public function testList(): void
    {
        $fields = $this->service->list()->getChatmessagefields();
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
    }

    #[Test]
    public function testGet(): void
    {
        $field = $this->service->get('taskId')->chatmessagefield();
        $this->assertNotEmpty($field->name);
        $this->assertNotEmpty($field->type);
        $this->assertNotEmpty($field->title);
    }

    #[Test]
    public function testAllFieldsAnnotated(): void
    {
        $fields = $this->service->list()->getChatmessagefields();
        $fieldCodesFromApi = array_keys(
            $this->service->list([])->getCoreResponse()->getResponseData()->getResult()['items'][0]
        );
        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $fieldCodesFromApi,
            ChatmessagefieldItemResult::class
        );
    }
}
```

---

## Files to Modify

### 7. `src/Services/Task/TaskServiceBuilder.php`

Add after `taskChat()` method (line 63):

```php
public function chatmessagefield(): Chatmessagefield\Service\Chatmessagefield
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Chatmessagefield\Service\Chatmessagefield(
            $this->core,
            $this->log
        );
    }
    return $this->serviceCache[__METHOD__];
}
```

### 8. `phpunit.xml.dist`

Add after `integration_tests_task` suite (line 189):

```xml
<testsuite name="integration_tests_task_chatmessagefield">
    <file>./tests/Integration/Services/Task/Chatmessagefield/Service/ChatmessagefieldTest.php</file>
</testsuite>
```

### 9. `Makefile`

Add after `integration_tests_task` target (line ~494):

```makefile
.PHONY: test-integration-task-chatmessagefield
test-integration-task-chatmessagefield:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_task_chatmessagefield
```

---

## Deptrac compliance

New service imports only Core-layer symbols (`ApiVersion`, `Scope`, `BaseException`, `TransportException`,
`AbstractItem`, `AbstractResult`) and `AbstractService` from the same Services layer. Zero new violations.

---

## Verification

```bash
# Unit tests (fast, no portal)
make test-unit

# Integration tests for the new service (requires webhook)
make test-integration-task-chatmessagefield

# Full task integration suite (new test is auto-included)
make integration_tests_task

# Static analysis
make lint-phpstan
make lint-deptrac
```
