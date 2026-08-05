# Plan: Add $withDeleted parameter to findByBitrix24PartnerNumber() (issue #490)

## Context

Issue #490 reports that consumer imports cannot detect a soft-deleted Bitrix24 partner by
`b24_partner_number`: the repository lookup hides partners in `deleted` status, so the consumer
tries to insert a duplicate partner number and hits a unique constraint violation.

This issue targets the v3 line and is implemented from `v3-dev` in branch
`feature/490-with-deleted-partner-lookup`.

No Bitrix24 REST API method is involved in this task. The change belongs to the application
contract layer under `src/Application/Contracts/Bitrix24Partners`, so there are no REST method
details to fetch from the official Bitrix24 API documentation. The local OpenAPI snapshot was
still refreshed with `make oa-schema-build` as required by the repository workflow.

The intended public contract is:

- `findByBitrix24PartnerNumber($number)` keeps the default behavior and must not return a
  partner whose status is `Bitrix24PartnerStatus::deleted`.
- `findByBitrix24PartnerNumber($number, withDeleted: true)` returns the matching partner
  regardless of status, including `deleted`.
- Existing calls remain source-compatible because the new parameter has a default value.

No SDK code generator applies: the changed files are application contracts, documentation, and
contract tests, not generated REST service result items or builders.

---

## Files to Create

No production source files need to be created.

---

## Files to Modify

### 1. `src/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterface.php`

Update the public method signature and PHPDoc:

```php
/**
 * Find bitrix24 partner with bitrix24 partner number.
 *
 * By default soft-deleted partners are excluded. Pass `$withDeleted = true` to include them.
 *
 * @param non-negative-int $bitrix24PartnerNumber
 */
public function findByBitrix24PartnerNumber(
    int $bitrix24PartnerNumber,
    bool $withDeleted = false
): ?Bitrix24PartnerInterface;
```

### 2. `tests/Unit/Application/Contracts/Bitrix24Partners/Repository/InMemoryBitrix24PartnerRepositoryImplementation.php`

Update the reference implementation signature and filtering:

```php
public function findByBitrix24PartnerNumber(
    int $bitrix24PartnerNumber,
    bool $withDeleted = false
): ?Bitrix24PartnerInterface
```

Loop behavior:

1. Skip partners whose number does not match.
2. If `$withDeleted === false` and the matching partner status is `Bitrix24PartnerStatus::deleted`,
   continue searching.
3. Return the first matching partner that passes the status filter.
4. Return `null` when no matching non-deleted partner exists.

Also include `withDeleted` in the debug context.

### 3. `tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php`

Keep the existing happy-path lookup test and add focused contract coverage for deleted partners:

```php
#[Test]
#[TestDox('findByBitrix24PartnerNumber ignores deleted partners by default')]
final public function testFindByBitrix24PartnerNumberIgnoresDeletedByDefault(): void
```

Expected behavior:

1. Create a partner with status `Bitrix24PartnerStatus::deleted` and a positive partner number.
2. Save it and flush.
3. Assert `findByBitrix24PartnerNumber($number)` returns `null`.

```php
#[Test]
#[TestDox('findByBitrix24PartnerNumber can include deleted partners')]
final public function testFindByBitrix24PartnerNumberCanIncludeDeleted(): void
```

Expected behavior:

1. Create a partner with status `Bitrix24PartnerStatus::deleted` and a positive partner number.
2. Save it and flush.
3. Assert `findByBitrix24PartnerNumber($number, withDeleted: true)` returns that partner.

### 4. `src/Application/Contracts/Bitrix24Partners/Docs/Bitrix24Partners.md`

Update the repository method list line from:

```markdown
- `public function findByBitrix24PartnerNumber(int $bitrix24PartnerNumber): ?Bitrix24PartnerInterface;`
```

to:

```markdown
- `public function findByBitrix24PartnerNumber(int $bitrix24PartnerNumber, bool $withDeleted = false): ?Bitrix24PartnerInterface;`
    - default lookup excludes `deleted` partners
    - pass `$withDeleted = true` to include soft-deleted partners
```

Do not refactor unrelated stale lines in this document.

### 5. `CHANGELOG.md`

Add one entry under `## Unreleased` -> `### Changed`:

```markdown
- Added optional `$withDeleted` flag to `Bitrix24PartnerRepositoryInterface::findByBitrix24PartnerNumber()` so import workflows can detect soft-deleted partners by partner number ([#490](https://github.com/bitrix24/b24phpsdk/issues/490))
```

---

## Deptrac compliance

The production change stays inside `Application\Contracts\Bitrix24Partners\Repository` and depends
only on existing contract-layer types. The in-memory implementation is test-only. No new service,
core, or infrastructure dependency is introduced, so no deptrac layer rule should need changes.

---

## TDD Steps

1. RED: add the two deleted-partner contract tests to
   `Bitrix24PartnerRepositoryInterfaceTest` and run the focused unit suite. The default-hidden test
   should fail against the current in-memory implementation.
2. GREEN: update `Bitrix24PartnerRepositoryInterface` and the in-memory implementation to accept
   `bool $withDeleted = false` and filter deleted partners by default.
3. REFACTOR: simplify the lookup loop if needed while keeping focused tests green.
4. Documentation: update `Bitrix24Partners.md`.
5. Quality gate: run the required checks before adding the changelog entry.
6. CHANGELOG: add the `### Changed` entry after the quality gate is green.

---

## Verification

Phase 1:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Phase 2:

No integration suite is required for this contract-only change. There is no REST service scope and
no live Bitrix24 API behavior to verify.

Focused checks during TDD:

```bash
make test-file path=tests/Unit/Application/Contracts/Bitrix24Partners/Repository/InMemoryBitrix24PartnerRepositoryImplementationTest.php
```
