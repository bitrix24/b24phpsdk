## Filtering

In REST 3.0, data filtering is based on logical expressions that can be combined. All methods that support the filter parameter operate according to this
scheme.

### Vendor documentation

https://apidocs.bitrix24.com/api-reference/rest-v3/index.html#filtering

#### Filtering Principles

Conditions within the same level are combined using the logic of AND — meaning all must be satisfied simultaneously.

Groups of conditions can be combined using the logic of OR with a special object with the key "logic": "or".

#### Example of a simple filter.

Find all records that satisfy two conditions simultaneously:

- Status = "NEW".
- ID is 3, 4, or 5.

```
{
    "filter": [
        ["status", "=", "NEW"],
        ["id", "in", [3,4,5]]
    ]
}
```

All elements in the filter array are combined using AND logic -> Status = NEW AND (ID = 3, 4, or 5).

#### Example of a complex filter with logic.

Find all records that satisfy two conditions simultaneously:

- Status = "NEW".
- ID is 1 or 2, OR ID is 3, 4, or 5.

```
{
    "filter": [
        ["status", "=", "NEW"],
        {
            "logic": "or",
            "conditions": [
                ["id", [1,2]], // id in (1,2)
                ["id", "in", [3,4,5]]
            ]
        }
    ]
}
```

1. ["status", "=", "NEW"] -> This is a simple condition: find all elements where the status field equals the value NEW.
2. { "logic": "or", "conditions": [...] } -> This is a group of conditions combined with OR logic. Inside the group are two conditions:

- ["id", [1,2]] — this is a shorthand for ["id", "in", [1,2]] -> "ID must be one of these numbers: 1 or 2",
- ["id", "in", [3,4,5]] -> "ID must be 3, 4, or 5". "logic": "or" indicates that the element will match if at least one of these two conditions is satisfied.

3. All elements in the filter array are combined using AND logic -> Status = NEW AND (ID = 1 or 2 OR ID = 3 or 4 or 5).

#### Supported Operators

| Operator  | Value description             | Example                                                               |
|-----------|-------------------------------|-----------------------------------------------------------------------|
| `=`       | equals                        | `["status", "=", "NEW"]` → status exactly **NEW**                     |
| `!=`      | not equal                     | `["status", "!=", "CLOSED"]` → status is not **CLOSED**               |
| `>`       | greater than                  | `["date", ">", "2025-01-01"]` → after January 1, 2025                 |
| `>=`      | greater than or equal         | `["price", ">=", 1000]` → price from **1000** and above               |
| `<`       | less than                     | `["date", "<", "2025-01-01"]` → before January 1, 2025                |
| `<=`      | less than or equal            | `["price", "<=", 1000]` → price up to **1000** inclusive              |
| `in`      | one of the values in the list | `["id", "in", [1,2,3]]` → id is **1**, **2**, or **3**                |
| `between` | in the range (inclusive)      | `["date", "between", ["2025-01-01","2025-12-31"]]` → within year 2025 |

### Mapping filter principles to the PHP SDK

#### Requirements

1. Filter must provide **entity depended** autocomplete values.

```php
$task->getList(
        new TaskFilter()
            ->title('ASAP')
            ->duration(8)           
);
$deals->getList(
        new DealFilter()
            ->title('OpenAI')
            ->price(100500)           
);
```

2. Filter must support **user fields** for filtering.

```php
$task->getList(
        new TaskFilter()
            ->title('ASAP')
            ->userField('UF_CRM_1234567890','danger')           
);
```

3. Filter must support all operations from the table above.

```php
$deals->getList(
        new DealFilter()
            ->title()->eq('ASAP')
            ->id()->in([1,2,3]
);
```

4. Filter must support **OR** logic.

```php
$deals->getList(
        new DealFilter()
            ->status()->eq('NEW')
            ->or(function (DealFilter $f) {
                $f->id()->in([1, 2]);
                $f->stageId()->eq('WON');
                $f->userField('UF_CRM_1700000000')->eq('yes');
            })
);            
```

5. Methods must pass a raw array or FilterBuilder object to the filter parameter.

```php
$deals->getList(
        new DealFilter()
            withRaw(
            [
                ["status", "=", "NEW"],
                ["id", "in", [3,4,5]]
            ]
        )                 
);
$deals->getList(
        new DealFilter()
            ->withTitle('OpenAI')
            ->withPrice(100500)           
);
```

6. Entity filters must be deterministic code-generated automatically from the entity metadata and update automatically when the entity metadata changes.

### Type Safety

The filter system provides compile-time type safety through specialized field condition builders. Each field type returns a dedicated builder that ensures correct value types at development time.

**Type-Safe Field Accessors:**

- Numeric fields (id, priority, counts) → `IntFieldConditionBuilder` - accepts only `int` values
- String fields (title, description) → `StringFieldConditionBuilder` - accepts only `string` values
- Date fields (deadline, createdDate) → `DateFieldConditionBuilder` - accepts `DateTime|string`, auto-converts DateTime to Y-m-d format
- Boolean fields (favorite, multitask) → `BoolFieldConditionBuilder` - accepts `bool`, auto-converts to Y/N format

**Example:**

```php
use DateTime;

$filter = (new TaskFilter())
    ->id()->eq(100)                                  // ✅ int only
    ->title()->eq('Task')                           // ✅ string only
    ->deadline()->eq(new DateTime('2025-01-15'))    // ✅ DateTime or string, auto-converts to '2025-01-15'
    ->favorite()->eq(true);                         // ✅ bool, auto-converts to 'Y'

// Compile-time errors prevent wrong types:
// $filter->id()->eq('not-a-number');  // ❌ TypeError
// $filter->favorite()->eq('yes');     // ❌ TypeError - must use bool
```

**Benefits:**

- **Compile-time safety**: Type errors caught during development, not at runtime
- **IDE autocomplete**: Your IDE suggests the correct type for each field
- **Explicit conversions**: DateTime and boolean values handled automatically
- **Self-documenting**: Method signatures clearly show expected types

For detailed information about the type-safe filter design, implementation details, and migration guide, see [Type Safety Documentation](type-safety.md).


