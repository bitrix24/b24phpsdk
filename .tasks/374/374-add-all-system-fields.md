# Plan: Add `allSystemFields()` to `AbstractSelectBuilder` (Variant A — Reflection)

## Context

`AbstractSelectBuilder` provides a fluent interface for building field-select arrays for REST v3 calls.
Each descendant defines individual field methods (`timestampX()`, `severity()`, etc.) that append
names to `protected array $select`.

Users need a convenience method `allSystemFields()` that selects **all available system fields**
without listing them manually.

Chosen approach: **Reflection** — implement once in the base class, zero changes to descendants.

---

## Implementation

### `src/Services/AbstractSelectBuilder.php` — add one method

```php
public function allSystemFields(): static
{
    $baseMethodNames = array_map(
        static fn(\ReflectionMethod $m): string => $m->getName(),
        (new \ReflectionClass(self::class))->getMethods(\ReflectionMethod::IS_PUBLIC)
    );

    foreach ((new \ReflectionClass(static::class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
        if (in_array($method->getName(), $baseMethodNames, true)) {
            continue; // skip inherited: buildSelect, withUserFields, allSystemFields
        }
        if ($method->getNumberOfRequiredParameters() > 0) {
            continue; // skip parameterized methods (e.g. withUserFields)
        }
        $this->{$method->getName()}();
    }
    return $this;
}
```

**How it works:**
1. Gets all public method names of `AbstractSelectBuilder` itself (`self::class`) — these are excluded
2. Iterates public methods of the **concrete class** (`static::class`)
3. Skips anything inherited from the base or requiring parameters
4. Calls the remaining zero-param methods (the field selectors) on `$this`
5. `buildSelect()` then returns `array_unique($this->select)` — deduplication already handled

**No changes needed** in `EventLogSelectBuilder`, `TaskItemSelectBuilder`, or any future descendant.

---

## Unit test

**File:** `tests/Unit/Services/AbstractSelectBuilderTest.php`

Uses an **anonymous class** as test double:

```php
$builder = new class extends AbstractSelectBuilder {
    public function __construct() { $this->select[] = 'id'; }
    public function name(): self  { $this->select[] = 'name'; return $this; }
    public function email(): self { $this->select[] = 'email'; return $this; }
};
```

| Test case | Assertion |
|-----------|-----------|
| `allSystemFields()` collects all field methods | `buildSelect()` contains `['id', 'name', 'email']` |
| Calling `allSystemFields()` twice — no duplication | `count(buildSelect())` still equals 3 |
| Chaining `->allSystemFields()->withUserFields(['UF_FOO'])` | Result contains all system fields + `'UF_FOO'` |
| `chat()`-style method adding multiple fields at once | All sub-fields appear in result |

Edge case for `chat()` pattern (`TaskItemSelectBuilder`): the method itself is called, so
`array_merge` inside it adds all sub-fields correctly — no special handling needed.

---

## Files to modify / create

| Action | File |
|--------|------|
| **Modify** | `src/Services/AbstractSelectBuilder.php` — add `allSystemFields()` |
| **Create** | `tests/Unit/Services/AbstractSelectBuilderTest.php` |

No changes to any descendant.

---

## Verification

```bash
make test-unit
make lint-phpstan
make lint-cs-fixer
make lint-rector
```
