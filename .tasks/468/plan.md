# Plan: Refactor uniqueness check in Bitrix24PartnerRepository test contract (issue #468)

## Context

Issue #468 targets repository contract tests, not a Bitrix24 REST API method. The required
OpenAPI preflight was still executed through `make oa-schema-build` before the issue work.

`Bitrix24PartnerRepositoryInterfaceTest::testSaveWithTwoBitrix24PartnerNumber()` currently
requires every repository implementation to reject duplicate `bitrix24PartnerNumber` values
inside `save()`. That makes `save()` responsible for a business invariant that belongs in
an application/use-case command handler. The repository should stay focused on persistence;
database-level unique constraints remain the final safety net for duplicate writes.

The plan follows the issue acceptance criterion and keeps the change scoped to the partner
repository contract surface:

- remove the duplicate-number contract test
- remove the duplicate-number check from the in-memory contract implementation so the test
  double no longer documents that behavior
- remove the `@throws InvalidArgumentException` annotation from `save()` in the repository
  interface and fake implementation
- record the behavior change in `CHANGELOG.md`

No Bitrix24 REST methods are added or changed, so the official REST documentation lookup is
not applicable for this issue.

---

## Files to Create

No production or test files need to be created.

---

## Files to Modify

### 1. `tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php`

Remove the entire `testSaveWithTwoBitrix24PartnerNumber()` method. Keep the remaining
contract tests unchanged:

- `testSave()` continues to assert that `save()` persists one partner
- `testFindByBitrix24PartnerNumber()` continues to assert lookup behavior
- no contract test expects `save()` to query for duplicates or throw on duplicate partner
  numbers

### 2. `tests/Unit/Application/Contracts/Bitrix24Partners/Repository/InMemoryBitrix24PartnerRepositoryImplementation.php`

Remove the duplicate-number preflight from `save()`:

```php
$existsPartner = $this->findByBitrix24PartnerNumber($bitrix24Partner->getBitrix24PartnerNumber());
if ($existsPartner instanceof Bitrix24PartnerInterface && $existsPartner->getId() !== $bitrix24Partner->getId()) {
    throw new InvalidArgumentException(sprintf(
        'bitrix24 partner «%s» with bitrix24 partner number is «%s» already exists with id «%s» in status «%s»',
        $existsPartner->getTitle(),
        $bitrix24Partner->getBitrix24PartnerNumber(),
        $existsPartner->getId(),
        $existsPartner->getStatus()->name
    ));
}
```

After the change, `save()` only logs and stores the partner by UUID:

```php
$this->items[$bitrix24Partner->getId()->toRfc4122()] = $bitrix24Partner;
```

Remove the `@throws InvalidArgumentException` docblock from `save()`. Keep the import because
`findByTitle()` and `findByExternalId()` still throw that exception for empty input.

### 3. `src/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterface.php`

Remove the `@throws InvalidArgumentException` annotation from `save()`. Keep the import because
`findByTitle()` and `findByExternalId()` still declare this exception.

### 4. `CHANGELOG.md`

Add this entry under `## 3.2.0 – UNRELEASED` → `### Changed`:

```markdown
- Removed the duplicate `bitrix24PartnerNumber` uniqueness expectation from the `Bitrix24PartnerRepositoryInterface` contract so `save()` remains a persistence operation; uniqueness validation belongs in the use-case layer ([#468](https://github.com/bitrix24/b24phpsdk/issues/468))
```

---

## Deptrac compliance

The change only removes test-contract behavior and a test-double preflight. No new classes,
imports, dependencies, or layer references are introduced, so no new Deptrac edge is expected.

---

## Verification

```bash
make test-file path=tests/Unit/Application/Contracts/Bitrix24Partners/Repository
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

No integration suite is required because the issue touches an application contract unit test
and an in-memory unit-test implementation only.
