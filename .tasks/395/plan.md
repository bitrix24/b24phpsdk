# Issue #395 Plan

## Summary
- Add SDK support for `tasks.task.field.get` and `tasks.task.field.list` as a dedicated v3 task-field service.
- Expose the new API through `Services\Task\Service\TaskField` and `TaskServiceBuilder::taskField()`, not through `Services\Task\Service\Task`.
- Return typed result wrappers for single-field and multi-field responses instead of exposing raw arrays from the service layer.
- Record the new support in `CHANGELOG.md` for release `3.1.0`.

## Implementation Changes
- Add `src/Services/Task/Service/TaskField.php` with two v3 methods:
  - `get(string $name, array $select = []): TaskFieldResult`
  - `list(array $select = []): TaskFieldsResult`
- Extend `src/Services/Task/TaskServiceBuilder.php` with `taskField(): Service\TaskField`.
- Annotate both methods with `ApiEndpointMetadata` using the canonical Bitrix24 REST v3 documentation URLs.
- Call the REST methods with `ApiVersion::v3`:
  - `tasks.task.field.get`
  - `tasks.task.field.list`
- Keep the method parameter contract simple in this task: plain `array<int,string>` for `select`; no dedicated select builder is introduced here.

## Result Model Changes
- Add a new immutable item result for task field metadata, for example `TaskFieldItemResult`.
- Add a single-item wrapper result `TaskFieldResult` that reads `result['item']`.
- Add a collection wrapper result `TaskFieldsResult` that maps `result` entries to `TaskFieldItemResult`.
- The new item result should expose the documented field metadata keys via `@property-read` PHPDoc, at minimum:
  - `name`
  - `type`
  - `title`
  - `description`
  - `validationRules`
  - `requiredGroups`
  - `filterable`
  - `sortable`
  - `editable`
  - `multiple`
  - `elementType`

## Test Changes
- Extend `tests/Integration/Services/Task/Service/TaskTest.php` with explicit coverage for:
  - `Task::getFields()`
  - `Task::getField()`
- `getFields()` test must verify that the response is non-empty and that returned entries are wrapped as `TaskFieldItemResult`.
- `getField()` test must first read the field list, then request one existing field by name, and assert that the returned item has the same `name`.
- Do not add synthetic mocks for these methods in this task if the integration fixture can cover them against a real portal.

## Files To Change
- `src/Services/Task/Service/Task.php`
- `src/Services/Task/Result/TaskFieldItemResult.php`
- `src/Services/Task/Result/TaskFieldResult.php`
- `src/Services/Task/Result/TaskFieldsResult.php`
- `tests/Integration/Services/Task/Service/TaskFieldTest.php`
- `src/Services/Task/TaskServiceBuilder.php`
- `CHANGELOG.md`

## Verification
- Run the focused integration test class:
  - `docker compose run --rm php-cli vendor/bin/phpunit tests/Integration/Services/Task/Service/TaskTest.php`
- Run style and static analysis after the code change:
  - `make lint-cs-fixer`
  - `make lint-phpstan`
  - `make lint-rector`

## Non-Goals
- Do not add support for other uncovered v3 `*.field.*` endpoints in this issue.
- Do not introduce a task-field select builder in this issue.
- Do not regenerate the checked-in OpenAPI snapshot in this issue.
