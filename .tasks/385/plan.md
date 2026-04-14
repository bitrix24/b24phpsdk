# Plan: add current oauth server to LocalAppAuth (issue #385)

## Context

`LocalAppAuth` — entity in `Application\Local\Entity\` that stores auth data for a local app:
`authToken`, `domainUrl`, `applicationToken`.

When an event or placement request arrives, the raw auth payload contains a `server_endpoint` field —
the URL of the OAuth server that issued the token (east: `oauth.bitrix24.tech`, west: `oauth.bitrix.info`).
Currently `LocalAppAuth` does not store this URL, so a consumer reconstructing `Credentials`
from `LocalAppAuth` must hard-code or guess the OAuth server via `DefaultOAuthServerUrl::default()`.

`Endpoints` (Core layer) already holds both `clientUrl` and `authServerUrl`.
`DefaultOAuthServerUrl` (Core layer) provides east/west constants and ENV-based default.

The fix: add `oauthServerUrl: string` to `LocalAppAuth`, expose it via a getter,
persist it in `toArray()`, and restore it in `initFromArray()` with a fallback to
`DefaultOAuthServerUrl::default()` for backward compatibility with files written by older SDK versions.

Deptrac: `Application` may depend on `Core` — importing `DefaultOAuthServerUrl` is allowed.

---

## Files to Create

### 1. `tests/Unit/Application/Local/Entity/LocalAppAuthTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Application\Local\Entity;

use Bitrix24\SDK\Application\Local\Entity\LocalAppAuth;
use Bitrix24\SDK\Core\Credentials\AuthToken;
use Bitrix24\SDK\Core\Credentials\DefaultOAuthServerUrl;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Generator;

#[CoversClass(LocalAppAuth::class)]
class LocalAppAuthTest extends TestCase
{
    // Test that getOAuthServerUrl() returns the value passed to the constructor
    // Test that toArray() includes 'oauth_server_url' key
    // Test that initFromArray() restores oauthServerUrl from 'oauth_server_url'
    // Test that initFromArray() falls back to DefaultOAuthServerUrl::default() when 'oauth_server_url' is absent
    // Test round-trip: toArray() → initFromArray() preserves all fields including oauthServerUrl
}
```

---

## Files to Modify

### 1. `src/Application/Local/Entity/LocalAppAuth.php`

**Constructor** — add new parameter after `$applicationToken`:
```php
public function __construct(
    private AuthToken       $authToken,
    private readonly string $domainUrl,
    private readonly ?string $applicationToken,
    private readonly string  $oauthServerUrl,   // ← new
)
```

**New getter** — add after `getApplicationToken()`:
```php
public function getOAuthServerUrl(): string
{
    return $this->oauthServerUrl;
}
```

**`initFromArray()`** — read `oauth_server_url` with fallback:
```php
public static function initFromArray(array $localAppAuthPayload): self
{
    return new self(
        AuthToken::initFromArray($localAppAuthPayload['auth_token']),
        $localAppAuthPayload['domain_url'],
        $localAppAuthPayload['application_token'],
        $localAppAuthPayload['oauth_server_url'] ?? DefaultOAuthServerUrl::default(),  // ← new
    );
}
```

**`toArray()`** — include `oauth_server_url`:
```php
public function toArray(): array
{
    return [
        'auth_token' => [
            'access_token'  => $this->authToken->accessToken,
            'refresh_token' => $this->authToken->refreshToken,
            'expires'       => $this->authToken->expires,
        ],
        'domain_url'       => $this->domainUrl,
        'application_token'=> $this->applicationToken,
        'oauth_server_url' => $this->oauthServerUrl,  // ← new
    ];
}
```

Add `use Bitrix24\SDK\Core\Credentials\DefaultOAuthServerUrl;` to imports.

### 2. `CHANGELOG.md`

Under `## 3.1.0 Unreleased` → `### Added`:
```markdown
- Added `oauthServerUrl` field to `LocalAppAuth`: stored in `toArray()` as `oauth_server_url`, restored in `initFromArray()` with fallback to `DefaultOAuthServerUrl::default()` for backward compatibility ([#385](https://github.com/bitrix24/b24phpsdk/issues/385))
```

---

## Deptrac compliance

`LocalAppAuth` is in layer `Application`. It imports `DefaultOAuthServerUrl` from `Core\Credentials`.
`Application` → `Core` is explicitly allowed. No new violations introduced.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

No integration tests required: `LocalAppAuth` is a pure value object with no HTTP calls.
