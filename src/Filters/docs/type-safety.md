# Type Safety in Filter Builders

## Overview

The Bitrix24 PHP SDK filter system provides compile-time type safety through specialized field condition builders. Each field type has its own dedicated builder class that ensures correct value types at development time, preventing common runtime errors.

## Problem

The initial implementation used a generic `FieldConditionBuilder` with `mixed` type for all operator methods:

```php
class FieldConditionBuilder
{
    public function eq(mixed $value): AbstractFilterBuilder { /* ... */ }
    public function gt(mixed $value): AbstractFilterBuilder { /* ... */ }
    // ...
}
```

### Issues with Generic Approach

1. **No compile-time type checking**: PHP and IDEs cannot validate that you're passing the correct type
2. **Implicit type conversions**: DateTime objects need to be converted to strings, but this happens silently
3. **Incorrect usage allowed**: You can pass a string to an integer field or vice versa without warnings
4. **Poor IDE support**: Autocomplete cannot suggest the correct type for each field

### Example Problems

```php
// All of these compile but are semantically wrong:
$filter->id()->eq('not-a-number');           // ID should be int!
$filter->changedDate()->eq(12345);           // Date should be DateTime or string!
$filter->priority()->between('low', 'high'); // Priority should be numeric range!
```

## Solutions Considered

We evaluated three approaches to improve type safety:

### Option 1: Typed Field Condition Builders ✅ (Implemented)

Create specialized builder classes for each field type.

**Advantages:**
- ✅ Full compile-time type safety
- ✅ IDE autocomplete with correct types
- ✅ Errors caught during development, not at runtime
- ✅ Explicit type conversions (DateTime → string)
- ✅ Self-documenting API

**Disadvantages:**
- ❌ More classes to maintain
- ❌ Slightly more code in filter implementations

**Verdict:** Best balance of type safety and usability.

### Option 2: Generics via PHPDoc

Use PHPDoc annotations for type hints while keeping `mixed` in method signatures.

**Advantages:**
- ✅ Less code than Option 1
- ✅ PHPStan can check types via annotations
- ✅ IDE understands types through PHPDoc

**Disadvantages:**
- ❌ No runtime type enforcement
- ❌ PHPDoc can be ignored or outdated
- ❌ Weaker guarantees than native PHP types

**Verdict:** Acceptable compromise, but weaker than Option 1.

### Option 3: Enum Field Types + Runtime Validation

Add field type information and validate at runtime.

**Advantages:**
- ✅ Single FieldConditionBuilder class
- ✅ Runtime validation catches errors

**Disadvantages:**
- ❌ No compile-time checking
- ❌ Errors only discovered when code runs
- ❌ Performance overhead for validation

**Verdict:** Useful for defensive programming but not for development-time safety.

## Implementation

### Type-Safe Builder Classes

The SDK provides four specialized builder classes:

#### IntFieldConditionBuilder

For integer fields (IDs, counts, status codes).

```php
namespace Bitrix24\SDK\Filters\Core;

class IntFieldConditionBuilder
{
    public function eq(int $value): AbstractFilterBuilder;
    public function neq(int $value): AbstractFilterBuilder;
    public function gt(int $value): AbstractFilterBuilder;
    public function gte(int $value): AbstractFilterBuilder;
    public function lt(int $value): AbstractFilterBuilder;
    public function lte(int $value): AbstractFilterBuilder;
    public function in(array $values): AbstractFilterBuilder;
    public function between(int $min, int $max): AbstractFilterBuilder;
}
```

#### StringFieldConditionBuilder

For text fields (titles, descriptions, identifiers).

```php
class StringFieldConditionBuilder
{
    public function eq(string $value): AbstractFilterBuilder;
    public function neq(string $value): AbstractFilterBuilder;
    public function in(array $values): AbstractFilterBuilder;
}
```

#### DateFieldConditionBuilder

For date/datetime fields. Accepts both DateTime objects and strings.

```php
class DateFieldConditionBuilder
{
    public function eq(DateTime|string $value): AbstractFilterBuilder;
    public function neq(DateTime|string $value): AbstractFilterBuilder;
    public function gt(DateTime|string $value): AbstractFilterBuilder;
    public function gte(DateTime|string $value): AbstractFilterBuilder;
    public function lt(DateTime|string $value): AbstractFilterBuilder;
    public function lte(DateTime|string $value): AbstractFilterBuilder;
    public function between(DateTime|string $from, DateTime|string $to): AbstractFilterBuilder;
}
```

**Note:** DateTime objects are automatically converted to `Y-m-d` format:

```php
// Input: DateTime object
->deadline()->eq(new DateTime('2025-01-15'))

// Output: String in filter
['deadline', '=', '2025-01-15']
```

#### BoolFieldConditionBuilder

For boolean fields. Automatically converts to Bitrix24's Y/N format.

```php
class BoolFieldConditionBuilder
{
    public function eq(bool $value): AbstractFilterBuilder;
    public function neq(bool $value): AbstractFilterBuilder;
}
```

**Note:** PHP boolean values are converted to Bitrix24 format:

```php
// Input: PHP boolean
->favorite()->eq(true)

// Output: Bitrix24 Y/N string
['favorite', '=', 'Y']
```

### Generic FieldConditionBuilder

The original `FieldConditionBuilder` with `mixed` types is still available for:
- User-defined fields (UF_*)
- Edge cases with unknown field types
- Backward compatibility

## Usage Examples

### Integer Fields

```php
use Bitrix24\SDK\Filters\Task\TaskFilter;

$filter = (new TaskFilter())
    ->id()->eq(100)                    // Single value
    ->priority()->gte(2)               // Comparison
    ->responsibleId()->in([1, 2, 3])   // Multiple values
    ->status()->between(1, 5);         // Range

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
    // Using DateTime objects
    ->changedDate()->eq(new DateTime('2025-01-01'))
    ->deadline()->gt(new DateTime('2025-06-01'))
    ->createdDate()->between(
        new DateTime('2025-01-01'),
        new DateTime('2025-12-31')
    )

    // Using strings (Y-m-d format)
    ->closedDate()->lt('2025-12-31')
    ->dateStart()->gte('2025-03-01');

// DateTime is automatically converted to string
$date = new DateTime('2025-01-15');
$filter->deadline()->eq($date);
// Results in: ['deadline', '=', '2025-01-15']
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

### Mixed Type Filters

```php
$filter = (new TaskFilter())
    ->id()->eq(100)                                    // int
    ->title()->eq('ASAP')                             // string
    ->changedDate()->eq(new DateTime('2025-01-01'))   // DateTime
    ->favorite()->eq(true)                            // bool
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

## Field Type Mapping

| Field Type | Builder Class | Accepted PHP Types | Bitrix24 Format | Example |
|------------|--------------|-------------------|-----------------|---------|
| Integer (IDs, counts) | `IntFieldConditionBuilder` | `int` | `int` | `->id()->eq(100)` |
| String (text) | `StringFieldConditionBuilder` | `string` | `string` | `->title()->eq('Task')` |
| Date/DateTime | `DateFieldConditionBuilder` | `DateTime\|string` | `string` (Y-m-d) | `->deadline()->eq(new DateTime())` |
| Boolean | `BoolFieldConditionBuilder` | `bool` | `string` (Y/N) | `->favorite()->eq(true)` |
| User Fields | `FieldConditionBuilder` | `mixed` | `mixed` | `->userField('UF_CODE')->eq($val)` |

### TaskFilter Field Type Reference

**Integer Fields:**
- Identifiers: `id`, `parentId`, `groupId`, `stageId`, `forumTopicId`, `sprintId`
- Status: `status`, `priority`, `mark`
- People: `createdBy`, `responsibleId`, `changedBy`, `closedBy`
- Numbers: `timeEstimate`, `commentsCount`, `durationPlan`

**String Fields:**
- `title`, `description`, `xmlId`, `guid`

**Date Fields:**
- `createdDate`, `changedDate`, `closedDate`, `deadline`, `dateStart`, `startDatePlan`, `endDatePlan`

**Boolean Fields:**
- `multitask`, `taskControl`, `subordinate`, `favorite`, `isMuted`

## Benefits

### 1. Compile-Time Type Checking

```php
// ✅ Correct - compiles
$filter->id()->eq(100);

// ❌ Error at development time
$filter->id()->eq('hundred');
// TypeError: Argument #1 ($value) must be of type int, string given
```

### 2. IDE Autocomplete

Your IDE will suggest the correct type for each method:

```php
$filter->id()->eq(|)      // IDE suggests: int
$filter->title()->eq(|)   // IDE suggests: string
$filter->deadline()->eq(|) // IDE suggests: DateTime|string
```

### 3. Self-Documenting Code

Method signatures clearly communicate expected types:

```php
// No documentation needed - signature explains everything
public function between(int $min, int $max): AbstractFilterBuilder
```

### 4. Prevent Common Mistakes

```php
// DateTime conversion is explicit and automatic
->changedDate()->eq(new DateTime('2025-01-01'))
// ✅ Converts to '2025-01-01' automatically

// Boolean conversion is explicit
->favorite()->eq(true)
// ✅ Converts to 'Y' automatically

// No silent type coercion surprises
->id()->eq('123')
// ❌ TypeError - must use int, not string
```

## Migration Guide

### From Generic FieldConditionBuilder

If you have existing code using the generic approach:

**Before (generic):**
```php
$filter = (new TaskFilter())
    ->id()->eq('100')                    // String accepted but wrong
    ->title()->eq(123)                   // Int accepted but wrong
    ->changedDate()->eq('2025-01-01');   // String works
```

**After (type-safe):**
```php
$filter = (new TaskFilter())
    ->id()->eq(100)                      // ✅ Correct type
    ->title()->eq('Task 123')            // ✅ Correct type
    ->changedDate()->eq(new DateTime('2025-01-01')); // ✅ DateTime or string
```

### DateTime Handling

**Before:** Manual date formatting
```php
$date = new DateTime('2025-01-01');
$filter->changedDate()->eq($date->format('Y-m-d'));
```

**After:** Automatic conversion
```php
$date = new DateTime('2025-01-01');
$filter->changedDate()->eq($date); // ✅ Automatically formatted
```

### Boolean Fields

**Before:** Manual Y/N conversion
```php
$filter->favorite()->eq($isFavorite ? 'Y' : 'N');
```

**After:** Use PHP booleans
```php
$filter->favorite()->eq($isFavorite); // ✅ Automatically converted
```

### Between Operator

**Before:** Array with two elements
```php
$filter->id()->between([1, 100]);
$filter->createdDate()->between(['2025-01-01', '2025-12-31']);
```

**After:** Two separate parameters
```php
$filter->id()->between(1, 100);
$filter->createdDate()->between('2025-01-01', '2025-12-31');

// Or with DateTime
$filter->createdDate()->between(
    new DateTime('2025-01-01'),
    new DateTime('2025-12-31')
);
```

### User Fields

User fields continue to use the generic builder:

```php
// Still works the same way
$filter->userField('UF_CRM_TASK')->eq('value');
$filter->userField('CRM_TASK')->in([1, 2, 3]); // Auto-adds UF_ prefix
```

## Backward Compatibility

- ✅ All existing filter methods remain available
- ✅ API surface unchanged - only types are stricter
- ✅ Generic `FieldConditionBuilder` still available for user fields
- ✅ `setRaw()` fallback for edge cases

**Breaking Change:** Code that was passing wrong types will now fail at compile time. This is a feature, not a bug - it catches errors earlier in the development cycle.

## Testing Type Safety

The SDK includes comprehensive tests demonstrating type-safe usage:

```php
// Integer type enforcement
$filter->id()->eq(100);                  // ✅
$filter->priority()->gte(2);             // ✅
$filter->responsibleId()->in([1, 2, 3]); // ✅

// DateTime conversion
$filter->changedDate()->eq(new DateTime('2025-01-01')); // ✅ → '2025-01-01'
$filter->deadline()->gt(new DateTime());                // ✅

// Boolean conversion
$filter->multitask()->eq(true);  // ✅ → 'Y'
$filter->favorite()->eq(false);  // ✅ → 'N'

// String fields
$filter->title()->eq('Task');           // ✅
$filter->guid()->in(['a', 'b', 'c']);   // ✅
```

See `tests/Unit/Filters/Task/TaskFilterTest.php` for complete examples.

## Summary

The type-safe filter builder system provides:

1. **Compile-time safety**: Catch type errors during development
2. **IDE support**: Better autocomplete and inline documentation
3. **Explicit conversions**: DateTime and boolean values handled automatically
4. **Self-documenting**: Method signatures show expected types
5. **Backward compatible**: Existing code continues to work with stricter types

This design follows PHP's philosophy of "fail fast" - catching errors as early as possible in the development cycle rather than at runtime.
