# Plan: Fix infinite recursion in Core::call() on 302 redirect (issue #372)

## Context

`Core::call()` handles HTTP `302 STATUS_FOUND` by:
1. Extracting `scheme://host` from the `Location` header
2. Calling `changeDomainUrl()` to update credentials
3. Recursively calling `$this->call($apiMethod, $parameters, $apiVersion)`

**The bug:** when a self-hosted Bitrix24 portal has an expired license, it redirects all requests
to `/bitrix/coupon_activation.php` on the **same domain**.
`parse_url` extracts the same `scheme://host` → `portalNewDomainUrlHost === portalOldDomainUrlHost`.
`changeDomainUrl()` accepts the same URL → no error.
Recursive call → infinite loop → PHP fatal stack overflow.

**The fix:**
After computing `portalNewDomainUrlHost`, compare it to `portalOldDomainUrlHost`.
If they are identical, the 302 is NOT a domain-migration redirect — throw
`PortalUnavailableException` with the full `Location` URL for debugging.

**Relevant code:** `src/Core/Core.php`, lines 84–107.

---

## Files to Create

### 1. `src/Core/Exceptions/PortalUnavailableException.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Core\Exceptions;

class PortalUnavailableException extends BaseException
{
}
```

### 2. `tests/Unit/Core/CoreTest.php`

Test class for `Core::call()` covering the infinite-recursion scenario:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Core;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Contracts\ApiClientInterface;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Core;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Core\Exceptions\PortalUnavailableException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(Core::class)]
class CoreTest extends TestCase
{
    #[Test]
    #[TestDox('call() throws PortalUnavailableException when 302 redirect stays on the same domain')]
    public function testCallThrowsPortalUnavailableExceptionOnSameDomainRedirect(): void
    {
        $domainUrl = 'https://myportal.example.com';
        $redirectLocation = $domainUrl . '/bitrix/coupon_activation.php';

        // Mock response: 302 with same-domain Location
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(302);
        $mockResponse->method('getHeaders')->willReturn(['location' => [$redirectLocation]]);

        // Mock ApiClient
        $credentials = Credentials::createFromWebhook(new WebhookUrl($domainUrl . '/rest/1/token/'));
        $mockApiClient = $this->createMock(ApiClientInterface::class);
        $mockApiClient->method('getCredentials')->willReturn($credentials);
        $mockApiClient->method('getResponse')->willReturn($mockResponse);

        $core = new Core(
            $mockApiClient,
            new ApiLevelErrorHandler(new NullLogger()),
            new EventDispatcher(),
            new NullLogger()
        );

        $this->expectException(PortalUnavailableException::class);
        $core->call('app.info');
    }
}
```

---

## Files to Modify

### 1. `src/Core/Core.php`

In the `STATUS_FOUND` branch (after line 88, before `changeDomainUrl()` call),
add a same-domain guard:

```php
case StatusCodeInterface::STATUS_FOUND:
    $portalOldDomainUrlHost = $this->apiClient->getCredentials()->getDomainUrl();
    $newDomain = parse_url($apiCallResponse->getHeaders(false)['location'][0]);
    $portalNewDomainUrlHost = sprintf('%s://%s', $newDomain['scheme'], $newDomain['host']);

    // Guard: if the redirect stays on the same domain, this is NOT a domain migration.
    // Infinite recursion would occur (e.g. expired-license redirect to /bitrix/coupon_activation.php).
    if ($portalNewDomainUrlHost === $portalOldDomainUrlHost) {
        throw new PortalUnavailableException(
            sprintf(
                'portal redirect loop detected: domain did not change (%s), redirect location: %s',
                $portalOldDomainUrlHost,
                $apiCallResponse->getHeaders(false)['location'][0]
            )
        );
    }

    $this->apiClient->getCredentials()->changeDomainUrl($portalNewDomainUrlHost);
    // ... rest of the existing code
```

Also add the import:
```php
use Bitrix24\SDK\Core\Exceptions\PortalUnavailableException;
```

### 2. `CHANGELOG.md`

Under `## 3.1.0 Unreleased` → `### Fixed`:
```markdown
- Fixed infinite recursion in `Core::call()` when portal returns a 302 redirect to the same domain (e.g. expired-license redirect to `/bitrix/coupon_activation.php`); now throws `PortalUnavailableException` ([#372](https://github.com/bitrix24/b24phpsdk/issues/372))
```

---

## Deptrac compliance

`Core::call()` is in layer `Core`. `PortalUnavailableException` is also in `Core\Exceptions`.
No new cross-layer imports — deptrac fully satisfied.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

No integration tests required: this is a pure Core unit-level fix.
