# IM Disk Folder Get Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add SDK support for `im.disk.folder.get` through a dedicated IM Disk service, builder accessor, result wrapper, tests, and dedicated integration wiring.

**Architecture:** Follow the existing service pattern used across the SDK: a focused service class under `src/Services/IM/Disk/Service`, a tiny custom result object for the `result.ID` envelope, builder registration in `IMServiceBuilder`, and targeted unit/integration tests. Keep the scope narrow to `im.disk.folder.get`; do not introduce broader `im.disk.*` abstractions in this task.

**Tech Stack:** PHP 8.4, PHPUnit 12, Bitrix24 PHP SDK service/result patterns, Docker-based `make` test targets

---

### Task 1: Lock the contract with failing unit tests

**Files:**
- Create: `tests/Unit/Services/IM/Disk/Service/DiskTest.php`
- Modify: `tests/Unit/Services/IM/IMServiceBuilderTest.php`
- Read: `src/Services/IM/IMServiceBuilder.php`
- Read: `src/Services/IMOpenLines/Session/Service/Session.php`

- [ ] **Step 1: Write the failing builder test**

Add a new assertion to `tests/Unit/Services/IM/IMServiceBuilderTest.php`:

```php
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;

public function testGetDiskService(): void
{
    $this->assertInstanceOf(Disk::class, $this->serviceBuilder->disk());
    $this->assertSame($this->serviceBuilder->disk(), $this->serviceBuilder->disk());
}
```

- [ ] **Step 2: Write the failing service contract test**

Create `tests/Unit/Services/IM/Disk/Service/DiskTest.php`:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Disk\Service;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\IM\Disk\Result\FolderIdResult;
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(Disk::class)]
final class DiskTest extends TestCase
{
    public function testGetFolderIdMapsChatIdAndDialogId(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core
            ->expects($this->once())
            ->method('call')
            ->with(
                'im.disk.folder.get',
                [
                    'CHAT_ID' => 17,
                    'DIALOG_ID' => 'chat17',
                ],
                ApiVersion::v1
            )
            ->willReturn(
                new Response(
                    new MockResponse(json_encode(['result' => ['ID' => 5153]], JSON_THROW_ON_ERROR)),
                    new Command('im.disk.folder.get', []),
                    new ApiLevelErrorHandler(new NullLogger()),
                    new NullLogger()
                )
            );

        $result = (new Disk($core, new NullLogger()))->getFolderId(17, 'chat17');

        $this->assertInstanceOf(FolderIdResult::class, $result);
        $this->assertSame(5153, $result->getId());
    }
}
```

- [ ] **Step 3: Run the targeted unit tests to verify they fail**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/IM/IMServiceBuilderTest.php tests/Unit/Services/IM/Disk/Service/DiskTest.php --display-warnings
```

Expected:
- `IMServiceBuilderTest` fails because `disk()` does not exist yet
- `DiskTest` fails because the new service/result classes do not exist yet

- [ ] **Step 4: Commit the red tests**

```bash
git add tests/Unit/Services/IM/IMServiceBuilderTest.php tests/Unit/Services/IM/Disk/Service/DiskTest.php
git commit -m "test: lock IM disk service contract"
```

### Task 2: Implement the minimal IM Disk service and make unit tests pass

**Files:**
- Create: `src/Services/IM/Disk/Result/FolderIdResult.php`
- Create: `src/Services/IM/Disk/Service/Disk.php`
- Modify: `src/Services/IM/IMServiceBuilder.php`
- Test: `tests/Unit/Services/IM/Disk/Service/DiskTest.php`
- Test: `tests/Unit/Services/IM/IMServiceBuilderTest.php`

- [ ] **Step 1: Add the dedicated result wrapper**

Create `src/Services/IM/Disk/Result/FolderIdResult.php`:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Disk\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

final class FolderIdResult extends AbstractResult
{
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['ID'];
    }
}
```

- [ ] **Step 2: Add the IM Disk service**

Create `src/Services/IM/Disk/Service/Disk.php`:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Disk\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Disk\Result\FolderIdResult;

#[ApiServiceMetadata(new Scope(['im']))]
final class Disk extends AbstractService
{
    #[ApiEndpointMetadata(
        'im.disk.folder.get',
        'https://apidocs.bitrix24.ru/api-reference/chats/files/im-disk-folder-get.html',
        'Get the identifier of the folder where chat files are stored'
    )]
    public function getFolderId(?int $chatId = null, ?string $dialogId = null): FolderIdResult
    {
        $params = [];

        if ($chatId !== null) {
            $params['CHAT_ID'] = $chatId;
        }

        if ($dialogId !== null) {
            $params['DIALOG_ID'] = $dialogId;
        }

        return new FolderIdResult($this->core->call('im.disk.folder.get', $params));
    }
}
```

- [ ] **Step 3: Register the service in the IM builder**

Update `src/Services/IM/IMServiceBuilder.php`:

```php
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;

public function disk(): Disk
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Disk($this->core, $this->log);
    }

    return $this->serviceCache[__METHOD__];
}
```

- [ ] **Step 4: Re-run the targeted unit tests to verify green**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/IM/IMServiceBuilderTest.php tests/Unit/Services/IM/Disk/Service/DiskTest.php --display-warnings
```

Expected:
- both files pass
- no warnings or risky tests

- [ ] **Step 5: Run the full unit suite to catch regressions**

Run:

```bash
make test-unit
```

Expected:
- `OK`
- the total test count increases from the current baseline

- [ ] **Step 6: Commit the implementation**

```bash
git add src/Services/IM/Disk/Result/FolderIdResult.php src/Services/IM/Disk/Service/Disk.php src/Services/IM/IMServiceBuilder.php
git add tests/Unit/Services/IM/IMServiceBuilderTest.php tests/Unit/Services/IM/Disk/Service/DiskTest.php
git commit -m "feat: add IM disk folder getter"
```

### Task 3: Add integration coverage, suite wiring, and changelog entry

**Files:**
- Create: `tests/Integration/Services/IM/Disk/Service/DiskTest.php`
- Modify: `phpunit.xml.dist`
- Modify: `Makefile`
- Modify: `CHANGELOG.md`
- Test: `tests/Integration/Services/IM/Disk/Service/DiskTest.php`

- [ ] **Step 1: Write the failing integration test**

Create `tests/Integration/Services/IM/Disk/Service/DiskTest.php`:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Disk\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Disk::class)]
final class DiskTest extends TestCase
{
    private Disk $diskService;

    #[\Override]
    protected function setUp(): void
    {
        $this->diskService = Factory::getServiceBuilder()->getIMScope()->disk();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFolderId(): void
    {
        $userId = (int)$this->diskService->core->call('PROFILE')->getResponseData()->getResult()['ID'];
        $chatId = (int)$this->diskService->core->call(
            'im.chat.add',
            [
                'USERS' => [$userId],
                'TYPE' => 'CHAT',
                'TITLE' => sprintf('IM Disk Test %s', time()),
            ]
        )->getResponseData()->getResult()[0];

        try {
            $folderId = $this->diskService->getFolderId(dialogId: 'chat' . $chatId)->getId();

            $this->assertGreaterThan(0, $folderId);
        } finally {
            $this->diskService->core->call('im.chat.leave', ['CHAT_ID' => $chatId]);
        }
    }
}
```

- [ ] **Step 2: Wire a dedicated PHPUnit suite and Make target**

Update `phpunit.xml.dist` near the other IM suites:

```xml
<testsuite name="integration_tests_im_disk">
    <directory>./tests/Integration/Services/IM/Disk/</directory>
</testsuite>
```

Update `Makefile` help and target sections:

```make
@echo "test-integration-im-disk - run IM Disk integration tests"

.PHONY: test-integration-im-disk
test-integration-im-disk:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_disk
```

- [ ] **Step 3: Add the changelog entry**

Update `CHANGELOG.md` under `## 3.2.0 – UNRELEASED` / `### Added`:

```markdown
- Added `Bitrix24\SDK\Services\IM\Disk\Service\Disk` with `getFolderId(?int $chatId = null, ?string $dialogId = null)` for `im.disk.folder.get`, plus dedicated `FolderIdResult`, IM builder registration, and focused unit/integration coverage ([#435](https://github.com/bitrix24/b24phpsdk/issues/435))
```

- [ ] **Step 4: Run the targeted integration suite and verify it starts red, then green after wiring**

Run:

```bash
make test-integration-im-disk
```

Expected before wiring:
- `No tests executed` or unknown testsuite / missing file failure

Expected after wiring and implementation:
- the new IM Disk integration test passes

- [ ] **Step 5: Run the final verification set**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/IM/Disk/Service/DiskTest.php tests/Unit/Services/IM/IMServiceBuilderTest.php --display-warnings
make test-unit
make test-integration-im-disk
```

Expected:
- targeted unit tests pass
- full unit suite stays green
- IM Disk integration suite passes

- [ ] **Step 6: Commit the integration and release wiring**

```bash
git add tests/Integration/Services/IM/Disk/Service/DiskTest.php phpunit.xml.dist Makefile CHANGELOG.md
git commit -m "test: cover IM disk folder getter"
```
