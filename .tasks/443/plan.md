# Plan: RemoteEventsFactory::isCanProcess() incorrectly rejects valid Bitrix24 form webhooks in Symfony/RoadRunner setups (issue #443)

## Context

Issue [#443](https://github.com/bitrix24/b24phpsdk/issues/443) reports that valid
Bitrix24 lifecycle webhook requests can already be parsed into
`Symfony\Component\HttpFoundation\Request::$request`, while
`Request::getContent()` may be empty or otherwise unsuitable for reparsing in a
Symfony + RoadRunner runtime.

The current implementation reparses the raw body in two places:

- `Bitrix24\SDK\Services\RemoteEventsFactory` uses `parse_str($request->getContent(), ...)`
  inside `isCanProcess()`, `create()`, and deprecated `createEvent()`.
- `Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest` reparses
  `Request::getContent()` again in the event object constructor, which means a
  fix limited to `isCanProcess()` would still leave event construction broken for
  parsed-only form requests.

For Bitrix24 webhook handling in Symfony HttpFoundation, the safest
backward-compatible normalization is:

1. Prefer `$request->request->all()` when it is non-empty.
2. Fall back to parsing `Request::getContent()` only when the parsed form bag is empty.

This keeps existing raw-body behavior intact while accepting valid
form-urlencoded requests that Symfony has already normalized.

---

## Files to Create

### 1. `src/Application/Requests/Events/EventRequestPayload.php`

Shared helper responsible for extracting the event payload from a Symfony
`Request` using the repository-approved precedence:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Application\Requests\Events;

use Symfony\Component\HttpFoundation\Request;

final class EventRequestPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function extract(Request $request): array
    {
        $payload = $request->request->all();
        if ($payload !== []) {
            return $payload;
        }

        $payload = [];
        parse_str($request->getContent(), $payload);

        return $payload;
    }
}
```

### 2. `tests/Unit/Application/Requests/Events/EventRequestPayloadTest.php`

Unit test covering the precedence rules directly:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Application\Requests\Events;

use Bitrix24\SDK\Application\Requests\Events\EventRequestPayload;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(EventRequestPayload::class)]
class EventRequestPayloadTest extends TestCase
{
    #[Test]
    public function testExtractPrefersParsedRequestPayload(): void
    {
        $request = new Request([], ['event' => 'ONAPPINSTALL', 'ts' => '1'], [], [], [], [], '');

        self::assertSame(
            ['event' => 'ONAPPINSTALL', 'ts' => '1'],
            EventRequestPayload::extract($request)
        );
    }
}
```

---

## Files to Modify

### 1. `src/Services/RemoteEventsFactory.php`

Replace all direct `parse_str($request->getContent(), $payload)` usages with
`EventRequestPayload::extract($request)` in:

- `isCanProcess()`
- `create()`
- `createEvent()`

No public API change is required.

### 2. `src/Application/Requests/Events/AbstractEventRequest.php`

Replace raw-body parsing in the constructor with the same shared extractor so
every supported and unsupported event object receives the normalized payload.

### 3. `tests/Unit/Services/RemoteEventsFactoryTest.php`

Add red/green coverage for the exact regression:

- `isCanProcess()` returns `true` when POST parameters already contain `event`
  and raw content is empty.
- `create()` successfully builds a supported event from a parsed-only request and
  exposes the expected event code, payload, and auth data.
- `createEvent()` remains backward-compatible for the same parsed-only request.

Extend the request helper methods in this test so they can create both:

- requests with raw content present
- requests with parsed POST data only

### 4. `CHANGELOG.md`

Add a `### Fixed` entry under `## 3.2.0 – UNRELEASED§`:

```markdown
- Fixed remote webhook payload normalization so `RemoteEventsFactory` and event requests accept valid Bitrix24 form webhooks already parsed by Symfony request bags ([#443](https://github.com/bitrix24/b24phpsdk/issues/443))
```

---

## Deptrac compliance

`EventRequestPayload` stays in the existing `Application\Requests\Events`
namespace and depends only on Symfony `Request`. `RemoteEventsFactory` already
depends on application request/event classes, so reusing the new extractor
should not introduce a new forbidden layer dependency.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```
