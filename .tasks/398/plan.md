# Plan: Add support for tasks.task.file.field.* (issue #398)

## Context

Issue: add SDK support for two REST v3 methods:
- `tasks.task.file.field.get` — returns `result.item` (single field descriptor) for a given `name`
- `tasks.task.file.field.list` — returns `result.items` (array of field descriptors)

Both methods belong to the `task` scope and require `ApiVersion::v3`.

Available `select` fields: `name`, `type`, `title`, `description`, `validationRules`,
`requiredGroups`, `filterable`, `sortable`, `editable`, `multiple`, `elementType`.

Exact structural mirror of `ChatMessageField` (issue #397), which lives at
`src/Services/Task/ChatMessageField/`. The only differences are:
- namespace: `FileField` instead of `ChatMessageField`
- REST method names: `tasks.task.file.field.*` instead of `tasks.task.chat.message.field.*`
- accessor method names: `fileField()` / `getFileFields()` instead of `chatMessageField()` / `getChatMessageFields()`
- service builder accessor: `taskFileField()` instead of `taskChatMessageField()`

---

## Files to Create

### 1. `src/Services/Task/FileField/Result/FileFieldItemResult.php`

```php
namespace Bitrix24\SDK\Services\Task\FileField\Result;

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
class FileFieldItemResult extends AbstractItem {}
```

### 2. `src/Services/Task/FileField/Result/FileFieldResult.php`

```php
namespace Bitrix24\SDK\Services\Task\FileField\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class FileFieldResult extends AbstractResult
{
    public function fileField(): FileFieldItemResult
    {
        return new FileFieldItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()['item']
        );
    }
}
```

### 3. `src/Services/Task/FileField/Result/FileFieldsResult.php`

```php
namespace Bitrix24\SDK\Services\Task\FileField\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class FileFieldsResult extends AbstractResult
{
    /** @return FileFieldItemResult[] */
    public function getFileFields(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new FileFieldItemResult($item);
        }
        return $items;
    }
}
```

### 4. `src/Services/Task/FileField/Service/FileField.php`

```php
namespace Bitrix24\SDK\Services\Task\FileField\Service;

#[ApiServiceMetadata(new Scope(['task']))]
class FileField extends AbstractService
{
    #[ApiEndpointMetadata('tasks.task.file.field.get', '...', '...', ApiVersion::v3)]
    public function get(string $name, array $select = []): FileFieldResult { ... }

    #[ApiEndpointMetadata('tasks.task.file.field.list', '...', '...', ApiVersion::v3)]
    public function list(array $select = []): FileFieldsResult { ... }
}
```

### 5. `tests/Unit/Services/Task/FileField/Service/FileFieldTest.php`

Three test methods:
- `testGetReturnsFileFieldResult()` — asserts `instanceof FileFieldResult`
- `testListReturnsFileFieldsResult()` — asserts `instanceof FileFieldsResult`
- `testGetThrowsOnEmptyName()` — asserts `InvalidArgumentException` on empty string

Uses `NullCore` + `NullLogger` (no HTTP calls).

### 6. `tests/Integration/Services/Task/FileField/Service/FileFieldTest.php`

Three test methods:
- `testList()` — calls `list()`, asserts non-empty array of `FileFieldItemResult`
- `testGet()` — calls `get('taskId')`, asserts `name`, `type`, `title` are non-empty
- `testAllFieldsAnnotated()` — fetches raw `items[0]` keys and calls `assertBitrix24AllResultItemFieldsAnnotated`

### 7. `tests/Integration/Services/Task/FileField/Result/FileFieldItemResultTest.php`

Two test methods:
- `testAllFieldsAreAnnotated()` — fetches `result['item']` via `get('taskId')`, calls `assertBitrix24AllResultItemFieldsAnnotated`
- `testAllFieldsHasValidTypeCastingInMagicGetters()` — calls `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations`

---

## Files to Modify

### 1. `src/Services/Task/TaskServiceBuilder.php`

Add after the `taskChatMessageField()` method:

```php
public function taskFileField(): FileField\Service\FileField
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new FileField\Service\FileField(
            $this->core,
            $this->log
        );
    }
    return $this->serviceCache[__METHOD__];
}
```

### 2. `phpunit.xml.dist`

Add new testsuite after `integration_tests_task_chat_message_field`:

```xml
<testsuite name="integration_tests_task_file_field">
    <file>./tests/Integration/Services/Task/FileField/Service/FileFieldTest.php</file>
    <file>./tests/Integration/Services/Task/FileField/Result/FileFieldItemResultTest.php</file>
</testsuite>
```

### 3. `Makefile`

Add after `test-integration-task-chat-message-field`:

```makefile
.PHONY: test-integration-task-file-field
test-integration-task-file-field:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_task_file_field
```

### 4. `CHANGELOG.md`

Under `## Unreleased` → `### Added`:

```markdown
- Added `tasks.task.file.field.get` and `tasks.task.file.field.list` support via `FileField` service ([#398](https://github.com/bitrix24/b24phpsdk/issues/398))
```

---

## Deptrac compliance

`FileField` service depends on `Core` only (`AbstractService`, `Scope`, `ApiVersion`, exceptions).
Result classes depend on `Core` only (`AbstractResult`, `AbstractItem`).
No cross-service imports. No new deptrac violations.

---

## Verification

```bash
make test-unit
make test-integration-task-file-field
make lint-phpstan
make lint-deptrac
```
