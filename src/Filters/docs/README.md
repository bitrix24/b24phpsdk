# Type-Safe Filtering for REST 3.0

## Overview

The Bitrix24 PHP SDK provides a type-safe filter builder system for REST 3.0 API filtering. This system offers compile-time type checking, IDE autocomplete support, and automatic type conversions while maintaining full backward compatibility.

## Table of Contents

1. [REST 3.0 Filtering Basics](#rest-30-filtering-basics)
2. [Type Safety](#type-safety)
3. [Usage Examples](#usage-examples)
4. [Field Type Mapping](#field-type-mapping)
5. [Migration Guide](#migration-guide)

---

## REST 3.0 Filtering Basics

### Official Documentation

https://apidocs.bitrix24.com/api-reference/rest-v3/index.html#filtering

### Filtering Principles

In REST 3.0, data filtering is based on logical expressions that can be combined:

- **AND Logic**: Conditions within the same level are combined using AND — all must be satisfied simultaneously
- **OR Logic**: Groups of conditions can be combined using OR with a special `{"logic": "or"}` object

### Simple Filter Example

Find all records where Status = "NEW" AND ID is 3, 4, or 5:

```json
{
    "filter": [
        ["status", "=", "NEW"],
        ["id", "in", [3, 4, 5]]
    ]
}
```

All elements in the filter array are combined using AND logic.

### Complex Filter with OR Logic

Find all records where Status = "NEW" AND (ID is 1 or 2, OR ID is 3, 4, or 5):

```json
{
    "filter": [
        ["status", "=", "NEW"],
        {
            "logic": "or",
            "conditions": [
                ["id", "in", [1, 2]],
                ["id", "in", [3, 4, 5]]
            ]
        }
    ]
}
```

**Explanation:**
1. `["status", "=", "NEW"]` — simple condition: status field equals NEW
2. `{"logic": "or", "conditions": [...]}` — group of conditions combined with OR:
   - `["id", "in", [1, 2]]` — ID must be 1 or 2
   - `["id", "in", [3, 4, 5]]` — ID must be 3, 4, or 5
3. Result: Status = NEW AND (ID in [1,2] OR ID in [3,4,5])

### Supported Operators

| Operator  | Description                   | Example                                                               |
|-----------|-------------------------------|-----------------------------------------------------------------------|
| `=`       | equals                        | `["status", "=", "NEW"]` → status exactly **NEW**                     |
| `!=`      | not equal                     | `["status", "!=", "CLOSED"]` → status is not **CLOSED**               |
| `>`       | greater than                  | `["date", ">", "2025-01-01"]` → after January 1, 2025                 |
| `>=`      | greater than or equal         | `["price", ">=", 1000]` → price from **1000** and above               |
| `<`       | less than                     | `["date", "<", "2025-01-01"]` → before January 1, 2025                |
| `<=`      | less than or equal            | `["price", "<=", 1000]` → price up to **1000** inclusive              |
| `in`      | one of the values in the list | `["id", "in", [1, 2, 3]]` → id is **1**, **2**, or **3**              |
| `between` | in the range (inclusive)      | `["date", "between", ["2025-01-01", "2025-12-31"]]` → within year 2025|

---

## Type Safety

### The Problem

Generic filter builders accept `mixed` types, leading to several issues:

```php
// All of these compile but are semantically wrong:
$filter->id()->eq('not-a-number');           // ID should be int!
$filter->changedDate()->eq(12345);           // Date should be DateTime or string!
$filter->priority()->between('low', 'high'); // Priority should be numeric range!
```

**Issues:**
- ❌ No compile-time type checking
- ❌ Implicit type conversions happen silently
- ❌ Incorrect usage allowed without warnings
- ❌ Poor IDE support for autocomplete

### The Solution: Typed Field Condition Builders

The SDK provides specialized builder classes for each field type, ensuring compile-time type safety:

#### IntFieldConditionBuilder

For integer fields (IDs, counts, status codes):

```php
public function eq(int $value): AbstractFilterBuilder;
public function neq(int $value): AbstractFilterBuilder;
public function gt(int $value): AbstractFilterBuilder;
public function gte(int $value): AbstractFilterBuilder;
public function lt(int $value): AbstractFilterBuilder;
public function lte(int $value): AbstractFilterBuilder;
public function in(array $values): AbstractFilterBuilder;
public function between(int $min, int $max): AbstractFilterBuilder;
```

#### StringFieldConditionBuilder

For text fields (titles, descriptions):

```php
public function eq(string $value): AbstractFilterBuilder;
public function neq(string $value): AbstractFilterBuilder;
public function in(array $values): AbstractFilterBuilder;
```

#### DateFieldConditionBuilder

For date/datetime fields — accepts both DateTime objects and strings:

```php
public function eq(DateTime|string $value): AbstractFilterBuilder;
public function neq(DateTime|string $value): AbstractFilterBuilder;
public function gt(DateTime|string $value): AbstractFilterBuilder;
public function gte(DateTime|string $value): AbstractFilterBuilder;
public function lt(DateTime|string $value): AbstractFilterBuilder;
public function lte(DateTime|string $value): AbstractFilterBuilder;
public function between(DateTime|string $from, DateTime|string $to): AbstractFilterBuilder;
```

**Automatic Conversion:** DateTime objects are converted to `Y-m-d` format:

```php
->deadline()->eq(new DateTime('2025-01-15'))
// Results in: ['deadline', '=', '2025-01-15']
```

#### BoolFieldConditionBuilder

For boolean fields — automatically converts to Bitrix24's Y/N format:

```php
public function eq(bool $value): AbstractFilterBuilder;
public function neq(bool $value): AbstractFilterBuilder;
```

**Automatic Conversion:**

```php
->favorite()->eq(true)   // Results in: ['favorite', '=', 'Y']
->favorite()->eq(false)  // Results in: ['favorite', '=', 'N']
```

### Benefits

1. **Compile-Time Type Checking**
   ```php
   $filter->id()->eq(100);      // ✅ Compiles
   $filter->id()->eq('hundred'); // ❌ TypeError at development time
   ```

2. **IDE Autocomplete**
   ```php
   $filter->id()->eq(|)       // IDE suggests: int
   $filter->title()->eq(|)    // IDE suggests: string
   $filter->deadline()->eq(|) // IDE suggests: DateTime|string
   ```

3. **Self-Documenting Code**
   ```php
   // Signature explains everything:
   public function between(int $min, int $max): AbstractFilterBuilder
   ```

4. **Automatic Type Conversions**
   ```php
   // DateTime conversion
   ->changedDate()->eq(new DateTime('2025-01-01')) // ✅ → '2025-01-01'

   // Boolean conversion
   ->favorite()->eq(true) // ✅ → 'Y'
   ```

---

## Usage Examples

### Basic Filtering

```php
use Bitrix24\SDK\Filters\Task\TaskFilter;
use DateTime;

// Simple filter with type-safe fields
$filter = (new TaskFilter())
    ->id()->eq(100)
    ->title()->eq('Important Task')
    ->status()->gte(2);

// Result:
// [
//     ['id', '=', 100],
//     ['title', '=', 'Important Task'],
//     ['status', '>=', 2]
// ]
```

### Integer Fields

```php
$filter = (new TaskFilter())
    ->id()->eq(100)                      // Single value
    ->priority()->gte(2)                 // Comparison
    ->responsibleId()->in([1, 2, 3])     // Multiple values
    ->status()->between(1, 5);           // Range

// Compile-time error - wrong type:
// $filter->id()->eq('not-a-number'); // ❌ TypeError
```

### String Fields

```php
$filter = (new TaskFilter())
    ->title()->eq('Important Task')
    ->description()->neq('Draft')
    ->guid()->in(['guid-1', 'guid-2']);

// Compile-time error - wrong type:
// $filter->title()->eq(123); // ❌ TypeError
```

### Date Fields

```php
use DateTime;

$filter = (new TaskFilter())
    // Using DateTime objects (auto-converted to Y-m-d)
    ->changedDate()->eq(new DateTime('2025-01-01'))
    ->deadline()->gt(new DateTime('2025-06-01'))
    ->createdDate()->between(
        new DateTime('2025-01-01'),
        new DateTime('2025-12-31')
    )

    // Using strings (Y-m-d format)
    ->closedDate()->lt('2025-12-31')
    ->dateStart()->gte('2025-03-01');
```

### Boolean Fields

```php
$filter = (new TaskFilter())
    ->multitask()->eq(true)      // Converts to 'Y'
    ->favorite()->eq(false)      // Converts to 'N'
    ->isMuted()->neq(true);      // Not equal to 'Y'

// Compile-time error - wrong type:
// $filter->favorite()->eq('yes'); // ❌ TypeError
```

### OR Logic

```php
$filter = (new TaskFilter())
    ->status()->eq(2)
    ->or(function (TaskFilter $f) {
        $f->id()->in([1, 2]);
        $f->priority()->gt(3);
    });

// Result:
// [
//     ['status', '=', 2],
//     {
//         'logic': 'or',
//         'conditions': [
//             ['id', 'in', [1, 2]],
//             ['priority', '>', 3]
//         ]
//     }
// ]
```

### User Fields

```php
// UF_ prefix is added automatically if missing
$filter = (new TaskFilter())
    ->title()->eq('Task')
    ->userField('UF_CRM_TASK')->eq('value')
    ->userField('CRM_PROJECT')->in([1, 2, 3]); // UF_ auto-added

// Result:
// [
//     ['title', '=', 'Task'],
//     ['UF_CRM_TASK', '=', 'value'],
//     ['UF_CRM_PROJECT', 'in', [1, 2, 3]]
// ]
```

### Mixed Types in One Filter

```php
use DateTime;

$filter = (new TaskFilter())
    ->id()->eq(100)                                    // int
    ->title()->eq('ASAP')                             // string
    ->changedDate()->eq(new DateTime('2025-01-01'))   // DateTime → '2025-01-01'
    ->favorite()->eq(true)                            // bool → 'Y'
    ->priority()->between(1, 5);                      // int range

// Result:
// [
//     ['id', '=', 100],
//     ['title', '=', 'ASAP'],
//     ['changedDate', '=', '2025-01-01'],
//     ['favorite', '=', 'Y'],
//     ['priority', 'between', [1, 5]]
// ]
```

### Raw Array Fallback

For edge cases or unsupported scenarios:

```php
$filter = (new TaskFilter())
    ->title()->eq('Task')
    ->setRaw([
        ['customField', '=', 'value'],
        ['anotherField', '!=', 'test']
    ]);

// Result: raw array is used directly
```

### Using with Task Service

```php
// Task::list() accepts TaskFilter|array
$tasks = $serviceBuilder->getTaskScope()->task()->list(
    filter: (new TaskFilter())
        ->title()->eq('Important')
        ->deadline()->gt(new DateTime('2025-01-01'))
        ->favorite()->eq(true)
);

// Backward compatible with arrays
$tasks = $serviceBuilder->getTaskScope()->task()->list(
    filter: [
        ['title', '=', 'Important'],
        ['deadline', '>', '2025-01-01']
    ]
);
```

---

## Field Type Mapping

### Type Reference Table

| Field Type        | Builder Class                  | Accepted PHP Types    | Bitrix24 Format | Example                              |
|-------------------|--------------------------------|-----------------------|-----------------|--------------------------------------|
| Integer           | `IntFieldConditionBuilder`     | `int`                 | `int`           | `->id()->eq(100)`                    |
| String            | `StringFieldConditionBuilder`  | `string`              | `string`        | `->title()->eq('Task')`              |
| Date/DateTime     | `DateFieldConditionBuilder`    | `DateTime\|string`    | `string` (Y-m-d)| `->deadline()->eq(new DateTime())`   |
| Boolean           | `BoolFieldConditionBuilder`    | `bool`                | `string` (Y/N)  | `->favorite()->eq(true)`             |
| User Fields (UF_) | `FieldConditionBuilder`        | `mixed`               | `mixed`         | `->userField('UF_CODE')->eq($value)` |

### TaskFilter Field Types

**Integer Fields:**
- **Identifiers**: `id`, `parentId`, `groupId`, `stageId`, `forumTopicId`, `sprintId`
- **Status**: `status`, `priority`, `mark`
- **People**: `createdBy`, `responsibleId`, `changedBy`, `closedBy`
- **Numbers**: `timeEstimate`, `commentsCount`, `durationPlan`

**String Fields:**
- `title`, `description`, `xmlId`, `guid`

**Date Fields:**
- `createdDate`, `changedDate`, `closedDate`, `deadline`, `dateStart`, `startDatePlan`, `endDatePlan`

**Boolean Fields:**
- `multitask`, `taskControl`, `subordinate`, `favorite`, `isMuted`

## Backward Compatibility

- ✅ All existing filter methods remain available
- ✅ API surface unchanged — only types are stricter
- ✅ Generic `FieldConditionBuilder` still available for user fields
- ✅ `setRaw()` fallback for edge cases
- ✅ `Task::list()` accepts `TaskFilter|array` via union type

**Note:** Code that was passing wrong types will now fail at compile time. This is intentional — it catches errors earlier in the development cycle.
