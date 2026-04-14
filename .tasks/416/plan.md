# Add createRepositoryFlusherImplementation to Bitrix24PartnerRepositoryInterfaceTest Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Bring `Bitrix24PartnerRepositoryInterfaceTest` in line with all other repository contract tests by adding the `createRepositoryFlusherImplementation()` abstract method and calling `$flusher->flush()` after every `save()` / `delete()` operation.

**Architecture:** Single-file change in the test infrastructure layer. No production code is modified. The pattern mirrors `Bitrix24AccountRepositoryInterfaceTest` exactly: each test obtains a flusher instance and calls `flush()` after every write to ensure implementations backed by Doctrine ORM (or any unit-of-work storage) are exercised correctly.

**Tech Stack:** PHP 8.2, PHPUnit 11, `TestRepositoryFlusherInterface` (already exists at `tests/Application/Contracts/TestRepositoryFlusherInterface.php`)

**Design doc:** `docs/plans/2026-04-15-partner-repository-flusher-design.md`

---

### Task 1: Add import and abstract method declaration

**Files:**
- Modify: `tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php:22`

**Step 1: Add the missing import**

In the `use` block (after line 21 `use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;`), add:

```php
use Bitrix24\SDK\Tests\Application\Contracts\TestRepositoryFlusherInterface;
```

**Step 2: Add the abstract method**

After the existing `createBitrix24PartnerRepositoryImplementation()` declaration (line 49), add:

```php
abstract protected function createRepositoryFlusherImplementation(): TestRepositoryFlusherInterface;
```

**Step 3: Verify with static analysis**

```bash
make lint-phpstan
```

Expected: no new errors related to this file.

**Step 4: Commit**

```bash
git add tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php
git commit -m "Add createRepositoryFlusherImplementation to Bitrix24PartnerRepositoryInterfaceTest (#416)"
```

---

### Task 2: Update testSave

**Files:**
- Modify: `tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php:55-80`

**Step 1: Update the test body**

Locate `testSave`. The current body is:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();

$b24PartnerRepository->save($b24Partner);

$res = $b24PartnerRepository->getById($b24Partner->getId());
$this->assertEquals($b24Partner, $res);
```

Replace with:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
$flusher = $this->createRepositoryFlusherImplementation();

$b24PartnerRepository->save($b24Partner);
$flusher->flush();

$res = $b24PartnerRepository->getById($b24Partner->getId());
$this->assertEquals($b24Partner, $res);
```

**Step 2: Run unit tests**

```bash
make test-unit
```

Expected: all tests pass.

---

### Task 3: Update testSaveWithTwoBitrix24PartnerNumber

**Files:**
- Modify: `tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php:85-115`

**Step 1: Update the test body**

Locate `testSaveWithTwoBitrix24PartnerNumber`. Current body:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();

$b24PartnerRepository->save($b24Partner);

$res = $b24PartnerRepository->getById($b24Partner->getId());
$this->assertEquals($b24Partner, $res);

$secondB24Partner = $this->createBitrix24PartnerImplementation(Uuid::v7(), $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$this->expectException(InvalidArgumentException::class);
$b24PartnerRepository->save($secondB24Partner);
```

Replace with:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
$flusher = $this->createRepositoryFlusherImplementation();

$b24PartnerRepository->save($b24Partner);
$flusher->flush();

$res = $b24PartnerRepository->getById($b24Partner->getId());
$this->assertEquals($b24Partner, $res);

$secondB24Partner = $this->createBitrix24PartnerImplementation(Uuid::v7(), $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$this->expectException(InvalidArgumentException::class);
$b24PartnerRepository->save($secondB24Partner);
```

**Step 2: Run unit tests**

```bash
make test-unit
```

Expected: all tests pass.

---

### Task 4: Update testDelete

**Files:**
- Modify: `tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php:120-148`

**Step 1: Update the test body**

Locate `testDelete`. Current body:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();

$b24Partner->markAsDeleted('delete partner');
$b24PartnerRepository->save($b24Partner);

$b24PartnerRepository->delete($b24Partner->getId());

$this->assertNull($b24PartnerRepository->findByBitrix24PartnerNumber($bitrix24PartnerNumber));
```

Replace with:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
$flusher = $this->createRepositoryFlusherImplementation();

$b24Partner->markAsDeleted('delete partner');
$b24PartnerRepository->save($b24Partner);
$flusher->flush();

$b24PartnerRepository->delete($b24Partner->getId());
$flusher->flush();

$this->assertNull($b24PartnerRepository->findByBitrix24PartnerNumber($bitrix24PartnerNumber));
```

**Step 2: Run unit tests**

```bash
make test-unit
```

Expected: all tests pass.

---

### Task 5: Update testGetById

**Files:**
- Modify: `tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php:153-182`

**Step 1: Update the test body**

Locate `testGetById`. Current body:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();

$b24PartnerRepository->save($b24Partner);

$res = $b24PartnerRepository->getById($b24Partner->getId());
$this->assertEquals($b24Partner, $res);

$this->expectException(Bitrix24PartnerNotFoundException::class);
$b24PartnerRepository->getById(Uuid::v7());
```

Replace with:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
$flusher = $this->createRepositoryFlusherImplementation();

$b24PartnerRepository->save($b24Partner);
$flusher->flush();

$res = $b24PartnerRepository->getById($b24Partner->getId());
$this->assertEquals($b24Partner, $res);

$this->expectException(Bitrix24PartnerNotFoundException::class);
$b24PartnerRepository->getById(Uuid::v7());
```

**Step 2: Run unit tests**

```bash
make test-unit
```

Expected: all tests pass.

---

### Task 6: Update testFindByBitrix24PartnerNumber

**Files:**
- Modify: `tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php:186-215`

**Step 1: Update the test body**

Locate `testFindByBitrix24PartnerNumber`. Current body:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();

$b24PartnerRepository->save($b24Partner);

$res = $b24PartnerRepository->findByBitrix24PartnerNumber($b24Partner->getBitrix24PartnerNumber());
$this->assertEquals($b24Partner, $res);


$this->assertNull($b24PartnerRepository->findByBitrix24PartnerNumber(0));
```

Replace with:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
$flusher = $this->createRepositoryFlusherImplementation();

$b24PartnerRepository->save($b24Partner);
$flusher->flush();

$res = $b24PartnerRepository->findByBitrix24PartnerNumber($b24Partner->getBitrix24PartnerNumber());
$this->assertEquals($b24Partner, $res);

$this->assertNull($b24PartnerRepository->findByBitrix24PartnerNumber(0));
```

**Step 2: Run unit tests**

```bash
make test-unit
```

Expected: all tests pass.

---

### Task 7: Update testFindByTitle

**Files:**
- Modify: `tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php:217-244`

**Step 1: Update the test body**

Locate `testFindByTitle`. Current body:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();

$b24PartnerRepository->save($b24Partner);

$res = $b24PartnerRepository->findByTitle($b24Partner->getTitle());
$this->assertEquals($b24Partner, $res[0]);

$this->assertEmpty($b24PartnerRepository->findByTitle('test'));
```

Replace with:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
$flusher = $this->createRepositoryFlusherImplementation();

$b24PartnerRepository->save($b24Partner);
$flusher->flush();

$res = $b24PartnerRepository->findByTitle($b24Partner->getTitle());
$this->assertEquals($b24Partner, $res[0]);

$this->assertEmpty($b24PartnerRepository->findByTitle('test'));
```

**Step 2: Run unit tests**

```bash
make test-unit
```

Expected: all tests pass.

---

### Task 8: Update testFindByExternalId

**Files:**
- Modify: `tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php:247-273`

**Step 1: Update the test body**

Locate `testFindByExternalId`. Current body:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();

$b24PartnerRepository->save($b24Partner);

$res = $b24PartnerRepository->findByExternalId($b24Partner->getExternalId());
$this->assertEquals($b24Partner, $res[0]);

$this->assertEmpty($b24PartnerRepository->findByExternalId('test'));
```

Replace with:

```php
$b24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
$b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
$flusher = $this->createRepositoryFlusherImplementation();

$b24PartnerRepository->save($b24Partner);
$flusher->flush();

$res = $b24PartnerRepository->findByExternalId($b24Partner->getExternalId());
$this->assertEquals($b24Partner, $res[0]);

$this->assertEmpty($b24PartnerRepository->findByExternalId('test'));
```

**Step 2: Run unit tests**

```bash
make test-unit
```

Expected: all tests pass.

---

### Task 9: Quality gate + CHANGELOG + final commit

**Step 1: Run full quality gate (Phase 1)**

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Expected: all pass with zero errors.

**Step 2: Update CHANGELOG.md**

Open `CHANGELOG.md`. Under `## X.Y.Z Unreleased` → `### Changed` (add section if missing), add:

```markdown
### Changed
- Add `createRepositoryFlusherImplementation()` to `Bitrix24PartnerRepositoryInterfaceTest` and update tests to call `flush()` after every write operation ([#416](https://github.com/bitrix24/b24phpsdk/issues/416))
```

**Step 3: Commit everything**

```bash
git add tests/Application/Contracts/Bitrix24Partners/Repository/Bitrix24PartnerRepositoryInterfaceTest.php CHANGELOG.md
git commit -m "Add flusher support to Bitrix24PartnerRepositoryInterfaceTest (#416)"
```
