# Plan: Change `markEmailAsVerified` method signature

## Context

The `markEmailAsVerified()` method in `ContactPersonInterface` currently always sets `emailVerifiedAt` to "now" (`new CarbonImmutable()`). The new signature accepts an optional `?CarbonImmutable $verifiedAt = null` parameter, allowing callers to supply a specific verification timestamp (e.g. when restoring from persistence or syncing external data). If `null` is passed, the behaviour falls back to the current default — current time.

---

## Files to modify

### 1. Interface definition
**`src/Application/Contracts/ContactPersons/Entity/ContactPersonInterface.php`** — line 82

Change:
```php
public function markEmailAsVerified(): void;
```
To:
```php
public function markEmailAsVerified(?CarbonImmutable $verifiedAt = null): void;
```

Update the PHPDoc block above (line 79-81) to describe the optional parameter.

---

### 2. Reference implementation (test stub)
**`tests/Unit/Application/Contracts/ContactPersons/Entity/ContactPersonReferenceEntityImplementation.php`** — lines 162-167

Change:
```php
public function markEmailAsVerified(): void
{
    $this->emailVerifiedAt = new CarbonImmutable();
    $this->updatedAt = new CarbonImmutable();
}
```
To:
```php
public function markEmailAsVerified(?CarbonImmutable $verifiedAt = null): void
{
    $this->emailVerifiedAt = $verifiedAt ?? new CarbonImmutable();
    $this->updatedAt = new CarbonImmutable();
}
```

---

### 3. Tests — add coverage for the new parameter
**`tests/Application/Contracts/ContactPersons/Entity/ContactPersonInterfaceTest.php`**

Existing call sites (lines 480, 850) use `markEmailAsVerified()` with no argument — they remain valid since the parameter is optional, no change required.

Add a new test method `testMarkEmailAsVerifiedWithSpecificDate` that:
- Creates a contact person
- Calls `$contactPerson->markEmailAsVerified($specificDate)` with an explicit `CarbonImmutable`
- Asserts `$contactPerson->getEmailVerifiedAt()->equalTo($specificDate)` is `true`

This ensures the new parameter actually works and is not silently ignored.

---

### 4. Documentation
**`src/Application/Contracts/ContactPersons/Docs/ContactPersons.md`** — line 18

Update the method signature in the table:
```markdown
| `markEmailAsVerified(?CarbonImmutable $verifiedAt = null)` | `void` | Marks contact person email as verified; uses current time if no date supplied | - |
```

---

## Call sites that require NO changes

| File | Line | Reason |
|---|---|---|
| `tests/Application/Contracts/ContactPersons/Entity/ContactPersonInterfaceTest.php` | 480, 850 | No-arg call, default covers it |
| `tests/Application/Contracts/ContactPersons/Repository/ContactPersonRepositoryInterfaceTest.php` | 364 | No-arg call, default covers it |

---

### 5. CHANGELOG.md
**`CHANGELOG.md`** — добавить в секцию `## Unreleased` → `### Changed`:

```markdown
### Changed

- `ContactPersonInterface::markEmailAsVerified()` now accepts an optional
  `?CarbonImmutable $verifiedAt = null` parameter.
  When `null` (default), the current timestamp is used — fully backward-compatible.
  Callers may supply an explicit date when restoring state from persistence or syncing external data.
  Updated: `ContactPersonInterface`, `ContactPersonReferenceEntityImplementation`,
  `ContactPersons.md` documentation, added `testMarkEmailAsVerifiedWithSpecificDate` unit test.
```

---

## Verification

1. `make test-unit` — all unit tests must pass
2. `make lint-phpstan` — no type errors from changed signature
3. `make lint-cs-fixer` — code style clean
4. Manually confirm: new `testMarkEmailAsVerifiedWithSpecificDate` test is green