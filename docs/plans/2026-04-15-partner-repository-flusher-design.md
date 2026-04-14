# Design: Add createRepositoryFlusherImplementation to Bitrix24PartnerRepositoryInterfaceTest (issue #416)

## Context

`Bitrix24PartnerRepositoryInterfaceTest` is an abstract contract test class that SDK consumers
extend to verify their implementations of `Bitrix24PartnerRepositoryInterface`. All other
repository contract tests (`Bitrix24AccountRepositoryInterfaceTest`,
`ApplicationInstallationRepositoryInterfaceTest`, `ContactPersonRepositoryInterfaceTest`) already
declare an abstract `createRepositoryFlusherImplementation(): TestRepositoryFlusherInterface` method
and call `$flusher->flush()` after every `save()` / `delete()` call. `Bitrix24PartnerRepositoryInterfaceTest`
is the only one that is missing this method, making the contract incomplete and leaving repository
implementations that require an explicit flush (e.g., Doctrine ORM) untested.

`ContactPersonRepositoryInterfaceTest` already has the flusher — no changes needed there.

---

## File to Modify

### `tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php`

**Change 1 — add import** (after the existing `use` block):

```php
use Bitrix24\SDK\Tests\Application\Contracts\TestRepositoryFlusherInterface;
```

**Change 2 — add abstract method** (after `createBitrix24PartnerRepositoryImplementation()`):

```php
abstract protected function createRepositoryFlusherImplementation(): TestRepositoryFlusherInterface;
```

**Change 3 — update 7 test methods** to obtain the flusher and call `flush()`:

| Test method | Where to add flusher lines |
|---|---|
| `testSave` | obtain flusher before save; `flush()` after `save()` |
| `testSaveWithTwoBitrix24PartnerNumber` | obtain flusher before first save; `flush()` after first `save()` |
| `testDelete` | obtain flusher before save; `flush()` after `save()` and after `delete()` |
| `testGetById` | obtain flusher before save; `flush()` after `save()` |
| `testFindByBitrix24PartnerNumber` | obtain flusher before save; `flush()` after `save()` |
| `testFindByTitle` | obtain flusher before save; `flush()` after `save()` |
| `testFindByExternalId` | obtain flusher before save; `flush()` after `save()` |

Pattern to follow (from `Bitrix24AccountRepositoryInterfaceTest`):

```php
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
$flusher = $this->createRepositoryFlusherImplementation();

$b24PartnerRepository->save($b24Partner);
$flusher->flush();
```

---

## Deptrac Compliance

Changes are confined to `tests/`. No production source files are modified.
No new layer dependencies are introduced.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```
