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

| Operator | Value description                 | Example                                                                 |
|----------|-----------------------------------|-------------------------------------------------------------------------|
| `=`      | equals                            | `["status", "=", "NEW"]` → status exactly **NEW**                        |
| `!=`     | not equal                         | `["status", "!=", "CLOSED"]` → status is not **CLOSED**                 |
| `>`      | greater than                      | `["date", ">", "2025-01-01"]` → after January 1, 2025                   |
| `>=`     | greater than or equal             | `["price", ">=", 1000]` → price from **1000** and above                 |
| `<`      | less than                         | `["date", "<", "2025-01-01"]` → before January 1, 2025                  |
| `<=`     | less than or equal                | `["price", "<=", 1000]` → price up to **1000** inclusive                |
| `in`     | one of the values in the list     | `["id", "in", [1,2,3]]` → id is **1**, **2**, or **3**                  |
| `between`| in the range (inclusive)          | `["date", "between", ["2025-01-01","2025-12-31"]]` → within year 2025   |

### Mapping filter principles to the PHP SDK

#### Requirements
1. Filter must provide **entity depended** autocomplete values.
```php
$task->getList(
        new TaskFilter()
            ->withTitle('ASAP')
            ->withDuration(8)           
);
$deals->getList(
        new DealFilter()
            ->withTitle('OpenAI')
            ->withPrice(100500)           
);
```
2. Filter must support **user fields** for filtering.
```php
$task->getList(
        new TaskFilter()
            ->withTitle('ASAP')
            ->withUserField('UF_CRM_1234567890','danger')           
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
        [
            ["status", "=", "NEW"],
            ["id", "in", [3,4,5]]
        ]                     
);
$deals->getList(
        new DealFilter()
            ->withTitle('OpenAI')
            ->withPrice(100500)           
);
```