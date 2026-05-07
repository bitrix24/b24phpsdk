# Design: Add ItemBuilderInterface Support for Task Service (issue #344)

## Status

Approved

## Context

Issue #344 requests `ItemBuilderInterface` support in `Task::add()` and `Task::update()` methods so that both the standard `TaskItemBuilder` and user-defined subclasses can be passed.

The infrastructure was partially built during v3 migration work:
- `ItemBuilderInterface` — `src/Core/Contracts/ItemBuilderInterface.php` ✓
- `AbstractItemBuilder` — `src/Services/AbstractItemBuilder.php` ✓
- `TaskItemBuilder` — `src/Services/Task/Service/TaskItemBuilder.php` ✓
- `Task::add(array|TaskItemBuilder)` — already accepts builder ✓
- `Task::update(int $id, array|TaskItemBuilder)` — already accepts builder ✓

## Decision

**Keep `array|TaskItemBuilder` as the PHP type hint** — this covers both:
- Scenario 1: standard `new TaskItemBuilder(...)` usage
- Scenario 2: `class MyBuilder extends TaskItemBuilder` with custom user-field methods

Custom builders not derived from `TaskItemBuilder` are out of scope.

## Remaining delta

### 1. Internal instanceof check in Task.php

Change both `add()` and `update()` internal checks from:

```php
if ($fields instanceof TaskItemBuilder) {
```

to:

```php
if ($fields instanceof ItemBuilderInterface) {
```

**Why**: semantically correct — `build()` is called because the object implements the builder
interface, not because it is specifically a `TaskItemBuilder`. Functionally equivalent under
the existing `array|TaskItemBuilder` type hint (every `TaskItemBuilder` implements
`ItemBuilderInterface`), but aligns with the intent of the issue.

### 2. CHANGELOG entry

Add under `## 3.1.0 Unreleased` → `### Added`:

```markdown
- Added `ItemBuilderInterface` and `AbstractItemBuilder` for type-safe task field building;
  `Task::add()` and `Task::update()` now accept `array|TaskItemBuilder` where `TaskItemBuilder`
  extends `AbstractItemBuilder implements ItemBuilderInterface`, allowing user subclasses with
  custom typed user-field methods ([#344](https://github.com/bitrix24/b24phpsdk/issues/344))
```

## Deptrac compliance

No new dependencies introduced. `Task.php` (Services layer) already imports
`ItemBuilderInterface` from `Core\Contracts` — allowed by the ruleset.

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Integration tests (`make test-integration-legacy-task`) already pass since
`TaskItemBuilder implements ItemBuilderInterface`.
