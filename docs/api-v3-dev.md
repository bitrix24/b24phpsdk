# Technical documentation for v3 API

## Development workflow

## Related entities for the new entity

### Service with api operations
Example:
```php
class TaskService
{
    public function add(TaskBuilder $taskBuilder): void
    {
        // Implementation
    }
}
```

### ItemBuilder class for add method from service
This class must be generated automatically from OA specification for the entity.

### TaskItemSelectBuilder class for select method from service
This class must be generated automatically from OA specification for the entity.

