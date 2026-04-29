# Plan: Add IM\Revision service for im.revision.get (issue #434)

## Context

The Bitrix24 REST API method `im.revision.get` returns version/revision numbers for
the IM module used by clients to check compatibility. The response envelope is a flat
object under `result`:

```json
{"result": {"REST": 14, "MOBILE": 1, "WEB": 1}, "time": {...}}
```

Fields expected: `REST` (int), `MOBILE` (int), `WEB` (int).

The method takes no parameters. It belongs to the `im` scope.

Docs URL: `https://apidocs.bitrix24.com/api-reference/chats/im-revision-get.html`

API version: v1 (legacy method name style). Base branch is `v3-dev` per the milestone 3.2.0.

Existing pattern to follow:
- Service class: extends `AbstractService`, uses `#[ApiServiceMetadata]` + `#[ApiEndpointMetadata]`
- Result class: extends `AbstractResult`, exposes a typed accessor method
- Item result: extends `AbstractItem`, has `@property-read` PHPDoc annotations
- ServiceBuilder caches instances via `$this->serviceCache[__METHOD__]`

---

## Files to Create

### 1. `src/Services/IM/Revision/Result/RevisionItemResult.php`

```php
namespace Bitrix24\SDK\Services\IM\Revision\Result;
use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int $REST
 * @property-read int $MOBILE
 * @property-read int $WEB
 */
class RevisionItemResult extends AbstractItem {}
```

### 2. `src/Services/IM/Revision/Result/RevisionResult.php`

```php
namespace Bitrix24\SDK\Services\IM\Revision\Result;
use Bitrix24\SDK\Core\Result\AbstractResult;

class RevisionResult extends AbstractResult
{
    public function revision(): RevisionItemResult
    {
        return new RevisionItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()
        );
    }
}
```

### 3. `src/Services/IM/Revision/Service/Revision.php`

```php
namespace Bitrix24\SDK\Services\IM\Revision\Service;
use Bitrix24\SDK\Attributes\{ApiEndpointMetadata, ApiServiceMetadata};
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Revision\Result\RevisionResult;

#[ApiServiceMetadata(new Scope(['im']))]
class Revision extends AbstractService
{
    #[ApiEndpointMetadata(
        'im.revision.get',
        'https://apidocs.bitrix24.com/api-reference/chats/im-revision-get.html',
        'Get IM module API revision numbers for client/server compatibility checks'
    )]
    public function get(): RevisionResult
    {
        return new RevisionResult($this->core->call('im.revision.get'));
    }
}
```

### 4. `tests/Unit/Services/IM/Revision/Service/RevisionTest.php`

Unit test that checks the service instantiates (follows ChatTest pattern).

### 5. `tests/Integration/Services/IM/Revision/Service/RevisionTest.php`

Integration test with `testGet()` asserting the result has non-negative integer fields
for `REST`, `MOBILE`, `WEB`.

### 6. `tests/Integration/Services/IM/Revision/Result/RevisionItemResultTest.php`

Two methods: `testAllFieldsAreAnnotated` and `testAllFieldsHasValidTypeCastingInMagicGetters`.

---

## Files to Modify

### 1. `src/Services/IM/IMServiceBuilder.php`

Add:
```php
use Bitrix24\SDK\Services\IM\Revision\Service\Revision;

public function revision(): Revision
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Revision($this->core, $this->log);
    }
    return $this->serviceCache[__METHOD__];
}
```

### 2. `tests/Unit/Services/IM/IMServiceBuilderTest.php`

Add:
```php
public function testGetRevisionService(): void
{
    $this::assertSame($this->serviceBuilder->revision(), $this->serviceBuilder->revision());
}
```

### 3. `phpunit.xml.dist`

Add after the `integration_tests_im_message` suite (line ~30):
```xml
<testsuite name="integration_tests_im_revision">
    <directory>./tests/Integration/Services/IM/Revision/</directory>
</testsuite>
```

### 4. `Makefile`

Add after `test-integration-im-message` target:
```makefile
.PHONY: test-integration-im-revision
test-integration-im-revision:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_revision
```

### 5. `CHANGELOG.md`

Under `## 3.2.0 – UNRELEASED` → `### Added`:
```markdown
- Added `Bitrix24\SDK\Services\IM\Revision\Service\Revision` service wrapping `im.revision.get` for IM module API revision/compatibility checks, with `RevisionItemResult` (`REST`, `MOBILE`, `WEB` fields) and `IMServiceBuilder::revision()` accessor ([#434](https://github.com/bitrix24/b24phpsdk/issues/434))
```

---

## Deptrac compliance

- `Revision` service and result classes are in the `Services` layer
- They only depend on `Core` (`AbstractService`, `AbstractResult`, `AbstractItem`, `Scope`, `BaseException`, `TransportException`, `ApiServiceMetadata`, `ApiEndpointMetadata`)
- No cross-service imports; no `Infrastructure` or `Application` imports
- No new `skip_violations` needed

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-im-revision
```
