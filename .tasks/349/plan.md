# Plan: Change `markMobilePhoneAsVerified` Method Signature

## Context

The `markMobilePhoneAsVerified()` method on `ContactPersonInterface` currently accepts no parameters and always sets the verification timestamp to "now". The task requires adding an optional `?CarbonImmutable $verifiedAt = null` parameter so callers can supply a specific verification timestamp (e.g. when importing historical data or replaying events). When `null` is passed, the behaviour stays identical to today — it defaults to the current timestamp.

Branch: `feature/348-change-method-signature`

---

## Files to Change

### 1. Interface definition
**File:** `src/Application/Contracts/ContactPersons/Entity/ContactPersonInterface.php` — line 111–114

Change the method signature and update the PHPDoc:

```php
// Before
/**
 * @return void mark contact person mobile phone as verified (send check main)
 */
public function markMobilePhoneAsVerified(): void;

// After
/**
 * @param CarbonImmutable|null $verifiedAt verification timestamp; defaults to now when null
 */
public function markMobilePhoneAsVerified(?CarbonImmutable $verifiedAt = null): void;
```

---

### 2. Reference entity implementation
**File:** `tests/Unit/Application/Contracts/ContactPersons/Entity/ContactPersonReferenceEntityImplementation.php` — lines 195–200

Update body to use `$verifiedAt ?? new CarbonImmutable()`:

```php
// Before
#[\Override]
public function markMobilePhoneAsVerified(): void
{
    $this->mobilePhoneVerifiedAt = new CarbonImmutable();
    $this->updatedAt = new CarbonImmutable();
}

// After
#[\Override]
public function markMobilePhoneAsVerified(?CarbonImmutable $verifiedAt = null): void
{
    $this->mobilePhoneVerifiedAt = $verifiedAt ?? new CarbonImmutable();
    $this->updatedAt = new CarbonImmutable();
}
```

---

### 3. Tests — extend `testMarkMobilePhoneAsVerified`
**File:** `tests/Application/Contracts/ContactPersons/Entity/ContactPersonInterfaceTest.php` — around line 608

Existing call sites all call `markMobilePhoneAsVerified()` without arguments — they remain valid (parameter is optional, no changes needed there).

However, `testMarkMobilePhoneAsVerified` (line 581) should gain an additional assertion that verifies the explicit-timestamp path:

```php
// add after the existing assertNotNull assertion
$specificTime = CarbonImmutable::parse('2020-01-15 12:00:00');
$contactPerson->changeMobilePhone(DemoDataGenerator::getMobilePhone());
$contactPerson->markMobilePhoneAsVerified($specificTime);
$this->assertEquals($specificTime, $contactPerson->getMobilePhoneVerifiedAt());
```

---

### 4. Documentation
**File:** `src/Application/Contracts/ContactPersons/Docs/ContactPersons.md` — line 23

Update the parameters column from `-` to the actual parameter description:

```markdown
| `markMobilePhoneAsVerified()` | `void` | Marks contact person mobile phone as verified | `?CarbonImmutable $verifiedAt = null` — verification timestamp, defaults to now |
```

---

### 5. Changelog
**File:** `CHANGELOG.md`

Add an entry under the current `[Unreleased]` section describing the signature change:

```markdown
### Changed
- `ContactPersonInterface::markMobilePhoneAsVerified()` now accepts an optional `?CarbonImmutable $verifiedAt = null`
  parameter. When omitted, the behaviour is identical to before (defaults to the current timestamp).
  Allows callers to supply a specific verification time (e.g. historical imports).
```

---

## Existing call sites (no changes required)

All four existing call sites pass no argument, which is backward-compatible with the new default-`null` signature:

| File | Line |
|---|---|
| `tests/Application/Contracts/ContactPersons/Entity/ContactPersonInterfaceTest.php` | 574, 608, 892 |
| `tests/Application/Contracts/ContactPersons/Repository/ContactPersonRepositoryInterfaceTest.php` | 391 |

---

## Verification

1. `make test-unit` — all unit tests must pass
2. `make lint-phpstan` — static analysis must pass (especially the override signature match)
3. `make lint-deptrac` — no new layer violations
4. `make lint-cs-fixer` — code style must pass

No integration tests need to change (the call sites use no-arg form which is still valid).