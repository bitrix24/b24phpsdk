# Plan: bugfix/365 — Make getBitrix24UserId(): int non-nullable in ContactPersonInterface

## Context

`ContactPersonInterface::getBitrix24UserId()` currently returns `?int`.
Fix #365 requires making it `int` (non-nullable), aligning it with the existing contract in `Bitrix24AccountInterface::getBitrix24UserId(): int`.
The semantic meaning is: a ContactPerson is always linked to a Bitrix24 user, so the ID can never be null.

---

## Files to modify (8 files)

### 1. Interface (1 file)
`src/Application/Contracts/ContactPersons/Entity/ContactPersonInterface.php`
- Line 134: update `@return` docblock: `@return int get bitrix24 user id`
- Line 135: `public function getBitrix24UserId(): ?int;` → `public function getBitrix24UserId(): int;`

### 2. Reference implementation (1 file)
`tests/Unit/Application/Contracts/ContactPersons/Entity/ContactPersonReferenceEntityImplementation.php`
- Line 45: `private readonly ?int $bitrix24UserId` → `private readonly int $bitrix24UserId`
- Line 222: `public function getBitrix24UserId(): ?int` → `public function getBitrix24UserId(): int`

### 3. ContactPerson interface test (1 file)
`tests/Application/Contracts/ContactPersons/Entity/ContactPersonInterfaceTest.php`

a) Abstract method `createContactPersonImplementation` (line 49):
   `?int $bitrix24UserId` → `int $bitrix24UserId`

b) All test methods (DataProvider parameters, same pattern throughout):
   Every method that accepts `?int $bitrix24UserId` → change to `int $bitrix24UserId`
   (~15+ occurrences, use replace_all)

c) `testGetBitrix24UserId` (lines 768-770):
   Remove the null-case block:
   ```php
   $bitrix24UserId = null;
   $contactPerson = $this->createContactPersonImplementation(...);
   $this->assertNull($contactPerson->getBitrix24UserId());
   ```
   Keep only the positive case (already present at lines 772-774).
   Change `?int $bitrix24UserId` → `int $bitrix24UserId` in the method signature as well.

d) Data provider `contactPersonDataProvider` (lines 1007-1045):
   In both yield statements, the 14th element (position of `$bitrix24UserId`) is `null`.
   Replace both `null` values with `random_int(1, 1000)`.
   - yield `valid-all-fields-by-default`: line ~1021
   - yield `contact-person-is-partner-employee`: line ~1041

### 4. Reference implementation test (1 file)
`tests/Unit/Application/Contracts/ContactPersons/Entity/ContactPersonInterfaceReferenceImplementationTest.php`
- Line 43: `?int $bitrix24UserId` → `int $bitrix24UserId`

### 5. ContactPerson repository test (1 file)
`tests/Application/Contracts/ContactPersons/Repository/ContactPersonRepositoryInterfaceTest.php`
- Line 51 (abstract method): `?int $bitrix24UserId` → `int $bitrix24UserId`
- All occurrences of `?int $bitrix24UserId` in test methods → `int $bitrix24UserId` (replace_all)
- Data provider `contactPersonDataProvider` (line ~635): `null` for `$bitrix24UserId` → `random_int(1, 1000)`

### 6. In-memory repository test (1 file)
`tests/Unit/Application/Contracts/ContactPersons/Repository/InMemoryContactPersonRepositoryImplementationTest.php`
- Line 77: `?int $bitrix24UserId` → `int $bitrix24UserId`

### 7. Entity documentation (1 file)
`src/Application/Contracts/ContactPersons/Docs/ContactPersons.md`
- Line 27: change `?int` → `int` and update the description:
  `Returns bitrix24 user id` (remove "if any")

### 8. CHANGELOG (1 file)
`CHANGELOG.md`
Add under `## Unreleased`:
```markdown
### Changed

- `ContactPersonInterface::getBitrix24UserId()` now returns `int` instead of `?int` — a ContactPerson is always linked to a Bitrix24 user ([#365](https://github.com/bitrix24/b24phpsdk/issues/365))
```

---

## Verification

```bash
make test-unit
make lint-phpstan
```

Both must pass with no errors.
