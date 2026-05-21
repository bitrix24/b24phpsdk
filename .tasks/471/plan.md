# Plan: Remove `delete(Uuid $uuid)` from `Bitrix24PartnerRepositoryInterface` (issue #471)

## Context

`Bitrix24PartnerRepositoryInterface` exposes a `delete(Uuid $uuid): void` method that is
never invoked by any use case. The actual deletion flow is:

1. `$partner->markAsDeleted(?string $comment)` — entity transitions to `deleted` status
2. `$repository->save($partner)` — persists the state change
3. `$flusher->flush($partner)` — flushes and dispatches domain events

The `delete()` method in `InMemoryBitrix24PartnerRepositoryImplementation` physically
removes the entity from in-memory storage only after verifying it is already in `deleted`
status — replicating a business invariant that the entity itself already enforces.

This is a pure refactoring with no behaviour change to any production use case.
API version: v3 (branch base: `v3-dev`).

---

## Files to Modify

### 1. `src/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterface.php`

Remove the `delete(Uuid $uuid): void` method declaration (lines 32-37).
Check that the `InvalidArgumentException` import is still needed by remaining methods — it is
(used by `save()`, `findByTitle()`, `findByExternalId()`), so leave it in place.

### 2. `tests/Unit/Application/Contracts/Bitrix24Partners/Repository/InMemoryBitrix24PartnerRepositoryImplementation.php`

Remove the `delete()` method implementation (lines 136-151).
The `Uuid` import remains required by `getById()`.

### 3. `tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php`

Remove the `testDelete()` test method (lines 120-147).

### 4. `CHANGELOG.md`

Add under `## X.Y.Z Unreleased` → `### Changed` (or `### Removed`):

```
- Removed dead `delete(Uuid $uuid)` method from `Bitrix24PartnerRepositoryInterface` and its test/stub implementations ([#471](https://github.com/bitrix24/b24phpsdk/issues/471))
```

---

## Deptrac compliance

No new classes or imports are introduced. Only method removals. No new layer violations possible.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```
