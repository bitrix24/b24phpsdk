# Plan: Add IM\Counters service for im.counters.get (issue #433)

## Context

The Bitrix24 REST API method `im.counters.get` returns unread message and notification counters
for the currently authenticated user. The response is a flat object (not a list):

```json
{
  "result": {
    "TYPE": "all",
    "MESSAGE": 2,
    "NOTIFY": 0,
    "CHAT": 2,
    "LINES": 0
  }
}
```

Fields: `TYPE` (string, filter type), `MESSAGE` (int), `NOTIFY` (int), `CHAT` (int), `LINES` (int).

This is a v3 API addition. The base branch is `v3-dev`. The current working branch is
`claude/fix-issue-433-2qxxs` which is based on `v3-dev`.

Pattern reference: `src/Services/IM/Chat/` and `src/Services/IM/Notify/` for service structure;
`src/Services/IM/Chat/Result/ChatResult.php` for single-item result wrapping.

---

## Files to Create

### 1. `src/Services/IM/Counters/Result/CountersItemResult.php`

```php
namespace Bitrix24\SDK\Services\IM\Counters\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read string $TYPE   counter filter type (e.g. "all")
 * @property-read int    $MESSAGE unread messages count
 * @property-read int    $NOTIFY  notifications count
 * @property-read int    $CHAT    chat messages count
 * @property-read int    $LINES   open lines count
 */
class CountersItemResult extends AbstractItem {}
```

### 2. `src/Services/IM/Counters/Result/CountersResult.php`

```php
namespace Bitrix24\SDK\Services\IM\Counters\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class CountersResult extends AbstractResult
{
    /** @throws BaseException */
    public function counters(): CountersItemResult
    {
        return new CountersItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()
        );
    }
}
```

### 3. `src/Services/IM/Counters/Service/Counters.php`

```php
namespace Bitrix24\SDK\Services\IM\Counters\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Counters\Result\CountersResult;

#[ApiServiceMetadata(new Scope(['im']))]
class Counters extends AbstractService
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.counters.get',
        'https://apidocs.bitrix24.com/api-reference/chats/counters/im-counters-get.html',
        'Get unread message and notification counters for the current user'
    )]
    public function get(): CountersResult
    {
        return new CountersResult($this->core->call('im.counters.get'));
    }
}
```

### 4. `tests/Unit/Services/IM/Counters/Service/CountersTest.php`

```php
namespace Bitrix24\SDK\Tests\Unit\Services\IM\Counters\Service;

use Bitrix24\SDK\Services\IM\Counters\Service\Counters;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Counters::class)]
class CountersTest extends TestCase
{
    private Counters $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new Counters(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testServiceInstantiates(): void
    {
        $this->assertInstanceOf(Counters::class, $this->service);
    }
}
```

### 5. `tests/Integration/Services/IM/Counters/Service/CountersTest.php`

```php
namespace Bitrix24\SDK\Tests\Integration\Services\IM\Counters\Service;

use Bitrix24\SDK\Services\IM\Counters\Service\Counters;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Counters::class)]
class CountersTest extends TestCase
{
    private Counters $countersService;

    #[\Override]
    protected function setUp(): void
    {
        $this->countersService = Factory::getServiceBuilder()->getIMScope()->counters();
    }

    #[Test]
    #[TestDox('im.counters.get returns a CountersItemResult with valid counter values')]
    public function testGet(): void
    {
        $result = $this->countersService->get();
        $counters = $result->counters();

        $this->assertIsString($counters->TYPE);
        $this->assertIsInt($counters->MESSAGE);
        $this->assertIsInt($counters->NOTIFY);
        $this->assertIsInt($counters->CHAT);
        $this->assertIsInt($counters->LINES);
    }
}
```

### 6. `tests/Integration/Services/IM/Counters/Result/CountersItemResultTest.php`

Standard annotation + type-cast test (see skill template).

---

## Files to Modify

### 1. `src/Services/IM/IMServiceBuilder.php`

Add after the `message()` method:

```php
use Bitrix24\SDK\Services\IM\Counters\Service\Counters;

public function counters(): Counters
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Counters($this->core, $this->log);
    }
    return $this->serviceCache[__METHOD__];
}
```

### 2. `tests/Unit/Services/IM/IMServiceBuilderTest.php`

Add test method:

```php
public function testGetCountersService(): void
{
    $this::assertSame($this->serviceBuilder->counters(), $this->serviceBuilder->counters());
}
```

### 3. `phpunit.xml.dist`

Add inside the `<testsuites>` block after `integration_tests_im_message`:

```xml
<testsuite name="integration_tests_im_counters">
    <directory>./tests/Integration/Services/IM/Counters/</directory>
</testsuite>
```

### 4. `Makefile`

Add after `test-integration-im-message` target:

```makefile
.PHONY: test-integration-im-counters
test-integration-im-counters:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_counters
```

Also add to the help section:

```
@echo "test-integration-im-counters - run IM Counters integration tests"
```

### 5. `CHANGELOG.md`

Under `## 3.2.0 – UNRELEASED` → `### Added`:

```markdown
- Added `IM\Counters` service with `im.counters.get` support ([#433](https://github.com/bitrix24/b24phpsdk/issues/433))
```

---

## Deptrac compliance

New classes are in `Services\IM\Counters\` layer which may depend on `Core` only (via
`AbstractService`, `AbstractResult`, `AbstractItem`, `Scope`, exceptions).
`IMServiceBuilder` is in `Services` layer and depends on `Core` — no new violations.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-im-counters
```
