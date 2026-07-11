# Plan: Add support for missing fields to `bizproc.robot.add` (issue #493)

## Context

Issue #493 asks to add four fields that are currently missing from the SDK wrapper of the
`bizproc.robot.add` REST method:

- `DESCRIPTION`
- `DOCUMENT_TYPE`
- `FILTER`
- `PLACEMENT_HANDLER`

Reference: https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-robot/bizproc-robot-add.html

In addition to the four fields, this change also:

- introduces a reusable `Bitrix24\SDK\Core\ValueObjects\Url` value object (a copy of the URL
  validation currently living in `Core\Credentials\WebhookUrl`) and types the new
  `PLACEMENT_HANDLER` parameter as `?Url`;
- applies **Stage 1** of the `Url` migration to the whole `Robot` service now: the existing
  `handlerUrl` parameter of `add()` and `update()` is widened to a `string|Url` union (raw strings
  stay valid — full backward compatibility);
- files a **follow-up issue** to roll Stage 1 out to the remaining ~68 SDK URL signatures and then
  execute Stage 2 (`Url`-only) in the next major (see "Follow-up issue" section below).

### API method details (from Bitrix24 docs)

- **Method**: `bizproc.robot.add`
- **Scope**: `bizproc` · **Module**: bizproc · **Category**: add
- **Docs**: https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-robot/bizproc-robot-add.html
- **Description**: Registers a new Automation rule in Bitrix24 within the context of an
  application. Requires administrator privileges and an application context.

#### Full API signature (all parameters, in doc order)

| API field | Type | Required | Description |
|---|---|---|---|
| `CODE` | string | **yes** | Internal identifier of the rule, unique within the application. Allowed chars: `a-z A-Z 0-9 . - _` |
| `HANDLER` | string | **yes** | URL that receives data via the Bitrix24 queue server; must share the app install domain |
| `AUTH_USER_ID` | integer | no | Id of the user whose token is passed to the application |
| `USE_SUBSCRIPTION` | boolean (`Y`/`N`) | no | Should the rule wait for a response from the application |
| `NAME` | string \| object | **yes** | Rule name; string or localized map `{ 'en': '...', 'de': '...' }` |
| `DESCRIPTION` | string \| object | no | Rule description; string or localized map `{ 'en': '...', 'de': '...' }` |
| `PROPERTIES` | object | no | Map of rule input parameters; each key starts with a letter, chars `a-z A-Z 0-9 _` |
| `RETURN_PROPERTIES` | object | no | Map of rule output parameters (results the app returns) |
| `DOCUMENT_TYPE` | array | no | `[module, class, type]`, e.g. `['crm','CCrmDocumentDeal','DEAL']` (leads / deals / quotes / invoices / SPA `DYNAMIC_XXX`) |
| `FILTER` | object | no | `INCLUDE` / `EXCLUDE` arrays of document-type rules; edition keys `'b24'` (cloud), `'box'` (on-premise) |
| `USE_PLACEMENT` | boolean (`Y`/`N`) | no | Enables an extra settings slider for the rule in the app |
| `PLACEMENT_HANDLER` | string | **conditional** | URL of the placement handler; **required when `USE_PLACEMENT = 'Y'`** |

The four fields requested by issue #493 are `DESCRIPTION`, `DOCUMENT_TYPE`, `FILTER`,
`PLACEMENT_HANDLER`.

#### Returns

| Field | Type | Description |
|---|---|---|
| `result` | boolean | `true` when the rule was added successfully |
| `time` | object | Request execution timing info |

Raw response shape:

```json
{ "result": true, "time": { "start": 1738148752.69, "finish": 1738148752.74, "duration": 0.056 } }
```

The current `AddedRobotResult` reads `getResult()[0]` and exposes `isSuccess()`.
No response-shape change is needed.

#### Raw REST call example (PHP via CRest)

```php
$result = CRest::call('bizproc.robot.add', [
    'CODE' => 'test_robot',
    'HANDLER' => 'https://your_domain/robot.php',
    'AUTH_USER_ID' => 1,
    'USE_SUBSCRIPTION' => 'Y',
    'NAME' => 'Отправить сообщение',
    'PROPERTIES' => [
        'datetime' => ['Name' => 'Во сколько', 'Type' => 'datetime'],
        'text'     => ['Name' => 'Текст', 'Type' => 'text'],
        'user'     => ['Name' => 'Кому', 'Type' => 'user', 'Default' => 'Автор;'],
    ],
    'FILTER' => [
        'INCLUDE' => [
            ['crm', 'CCrmDocumentDeal'],
            ['crm', 'CCrmDocumentLead'],
        ],
    ],
]);
```

### Locked design decisions

1. **DESCRIPTION is typed as a localized `array`** — `array $localizedRobotDescription = []`,
   consistent with the existing `array $localizedRobotName` parameter (`NAME`). A plain string
   is passed by the caller as `['en' => '...']`.
2. **PLACEMENT_HANDLER is a `Url` value object, validated client-side (fail-fast)** — the new
   parameter is typed `?Url $placementHandlerUrl = null`. When `$isUsePlacement === true` and
   `$placementHandlerUrl === null`, `add()` throws `InvalidArgumentException` before calling the
   API (mirrors the precondition already present in `update()` — `'no fields to update…'`). URL
   syntax itself is validated inside the `Url` constructor.
3. **Backward compatibility** — the four new parameters are appended to the end of the `add()`
   signature with defaults, so existing 8-argument callers keep working. The pre-existing
   `handlerUrl` parameter is **widened** from `string` to a `string|Url` union in both `add()` and
   `update()` (Stage 1); a raw `string` remains a valid argument, so no caller breaks.
4. **Conditional payload** — each optional field is added to the request payload only when it is
   provided: arrays when non-empty; `PLACEMENT_HANDLER` when the `Url` is not `null`, serialized
   via `$placementHandlerUrl->getUrl()`. Matches `required: no` semantics.
5. **Scope** — the four new API fields are added to `add()` only (`update()` gains no new fields).
   The `string|Url` migration additionally touches `update()`'s `handlerUrl`, but no other method.
6. **Docs link** — while editing the `#[ApiEndpointMetadata]` of `add()`, switch its URL and the
   `@see` docblock to the English `apidocs.bitrix24.com` page (skill rule for changed metadata).
7. **New `Url` value object** — introduce `Bitrix24\SDK\Core\ValueObjects\Url`, a copy of the
   validation in `Core\Credentials\WebhookUrl`, and use it as the type of the new
   `PLACEMENT_HANDLER` parameter (VO-only, `?Url`). `WebhookUrl` is **not** refactored here; that
   migration and the SDK-wide rollout are tracked by the follow-up issue.
8. **URL normalization helper** — a private `resolveUrl(string|Url $url): string` in `Robot`
   converts a union argument to a validated string for the payload: an existing `Url` returns
   `->getUrl()`; a raw `string` is wrapped in `new Url(...)` first, so raw strings are validated
   the same way. `placementHandlerUrl` is already a `Url`, so it uses `->getUrl()` directly.

### Testing constraint (documented rationale)

`bizproc.robot.add` requires an **OAuth application context and administrator rights** and cannot
be called through an incoming webhook. This is why the Robot service currently has **no**
integration tests. Therefore:

- No new integration test is added for `add()` in this change; the webhook-based
  `integration_tests_scope_workflows` suite cannot register robots.
- The mandatory `*ItemResultTest` does **not** apply here: `AddedRobotResult` extends
  `AbstractResult` (not `AbstractAnnotatedItem`) and has no `@property-read` annotations.
- The automated quality gate for this change is the **unit test** plus the full linter set.

---

## API method implementation

### Current (grounded on `src/Services/Workflows/Robot/Service/Robot.php:57-78`)

The current wrapper exposes 8 positional arguments and maps them 1:1 to the payload. Note that
the SDK already **forces** `AUTH_USER_ID`, `USE_SUBSCRIPTION`, `PROPERTIES`, `USE_PLACEMENT` and
`RETURN_PROPERTIES` as required positional arguments, even though the API marks them optional.
This is kept unchanged for backward compatibility.

```php
public function add(
    string $code,
    string $handlerUrl,
    int    $b24AuthUserId,
    array  $localizedRobotName,
    bool   $isUseSubscription,
    array  $properties,
    bool   $isUsePlacement,
    array  $returnProperties
): AddedRobotResult
{
    return new AddedRobotResult($this->core->call('bizproc.robot.add', [
        'CODE' => $code,
        'HANDLER' => $handlerUrl,
        'AUTH_USER_ID' => $b24AuthUserId,
        'NAME' => $localizedRobotName,
        'USE_SUBSCRIPTION' => $isUseSubscription ? 'Y' : 'N',
        'PROPERTIES' => $properties,
        'USE_PLACEMENT' => $isUsePlacement ? 'Y' : 'N',
        'RETURN_PROPERTIES' => $returnProperties
    ]));
}
```

Sends: `CODE`, `HANDLER`, `AUTH_USER_ID`, `NAME`, `USE_SUBSCRIPTION`, `PROPERTIES`,
`USE_PLACEMENT`, `RETURN_PROPERTIES`. The four fields from issue #493 are **not** sent.

### Proposed

Four optional parameters are appended (defaults `[]` / `null`), a client-side `PLACEMENT_HANDLER`
precondition is added, and each optional field is written to the payload only when provided. The
full method body lives in **Files to Modify → 1**; the signature is:

```php
public function add(
    string      $code,
    string|Url  $handlerUrl,                     // widened from string (Stage 1) ← changed
    int         $b24AuthUserId,
    array       $localizedRobotName,
    bool        $isUseSubscription,
    array       $properties,
    bool        $isUsePlacement,
    array       $returnProperties,
    array       $localizedRobotDescription = [], // DESCRIPTION        ← new
    array       $documentType = [],              // DOCUMENT_TYPE      ← new
    array       $filter = [],                    // FILTER             ← new
    ?Url        $placementHandlerUrl = null      // PLACEMENT_HANDLER  ← new (Url VO)
): AddedRobotResult
```

`update()` is changed too — only its `handlerUrl` parameter is widened
`?string` → `Url|string|null` (no new fields):

```php
public function update(
    string          $code,
    Url|string|null $handlerUrl = null,          // widened from ?string (Stage 1) ← changed
    ?int            $b24AuthUserId = null,
    // …remaining parameters unchanged…
): UpdateRobotResult
```

Parameter → API field mapping (`add()`):

| SDK parameter | API field | Sent when |
|---|---|---|
| `$handlerUrl` (`string\|Url`) | `HANDLER` | always; normalized via `resolveUrl()` |
| `$localizedRobotDescription` | `DESCRIPTION` | array is non-empty |
| `$documentType` | `DOCUMENT_TYPE` | array is non-empty |
| `$filter` | `FILTER` | array is non-empty |
| `$placementHandlerUrl` (`?Url`) | `PLACEMENT_HANDLER` | value is not `null`; sent as `->getUrl()` |

Precondition: if `$isUsePlacement === true && $placementHandlerUrl === null` →
`throw InvalidArgumentException`.

#### Call example — required arguments only (unchanged, backward compatible)

```php
$robot = $serviceBuilder->getBizProcScope()->robot();

$robot->add(
    'my_robot',                       // code
    'https://example.com/handler',    // handlerUrl
    1,                                // b24AuthUserId
    ['en' => 'My robot'],             // localizedRobotName
    false,                            // isUseSubscription
    [],                               // properties
    false,                            // isUsePlacement
    []                                // returnProperties
);
```

#### Call example — all arguments (including the four new fields)

```php
$robot->add(
    'my_robot',                                 // code
    new Url('https://example.com/handler'),     // handlerUrl — now also accepts a Url VO
    1,                                          // b24AuthUserId
    ['en' => 'My robot'],                       // localizedRobotName
    true,                                       // isUseSubscription
    ['text' => ['Name' => 'Text', 'Type' => 'text']],       // properties
    true,                                       // isUsePlacement
    ['result' => ['Name' => 'Result', 'Type' => 'string']], // returnProperties
    ['en' => 'Sends a message'],                // localizedRobotDescription → DESCRIPTION
    ['crm', 'CCrmDocumentDeal', 'DEAL'],        // documentType             → DOCUMENT_TYPE
    [                                           // filter                   → FILTER
        'INCLUDE' => [
            ['crm', 'CCrmDocumentDeal'],
            ['crm', 'CCrmDocumentLead'],
        ],
    ],
    new Url('https://example.com/placement')    // placementHandlerUrl      → PLACEMENT_HANDLER
);
```

`handlerUrl` still accepts a raw string (as in the required-arguments example above); passing a
`Url` is the new option. `Url` is imported as `use Bitrix24\SDK\Core\ValueObjects\Url;`.

---

## Files to Create

### 1. `src/Core/ValueObjects/Url.php`

New reusable value object in the new `Bitrix24\SDK\Core\ValueObjects` namespace. It is a copy of
the URL validation logic in `Core\Credentials\WebhookUrl`: validate on construction, expose
`getUrl()`. Belongs to the `Core` layer and depends only on `Core\Exceptions`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Core\ValueObjects;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

final class Url
{
    private string $url;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(sprintf('URL %s is invalid', $url));
        }

        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
```

### 2. `tests/Unit/Core/ValueObjects/UrlTest.php`

Unit test for the `Url` VO (no HTTP). Covers accept-valid and reject-invalid.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Core\ValueObjects;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Url::class)]
class UrlTest extends TestCase
{
    #[Test]
    #[TestDox('valid URL is accepted and returned unchanged')]
    #[DataProvider('validUrlProvider')]
    public function testValidUrl(string $url): void
    {
        $this->assertSame($url, (new Url($url))->getUrl());
    }

    #[Test]
    #[TestDox('invalid URL throws InvalidArgumentException')]
    #[DataProvider('invalidUrlProvider')]
    public function testInvalidUrl(string $url): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Url($url);
    }

    public static function validUrlProvider(): Generator
    {
        yield 'https' => ['https://example.com/handler'];
        yield 'https with port and path' => ['https://example.com:8443/a/b?c=d'];
        yield 'http' => ['http://example.com'];
    }

    public static function invalidUrlProvider(): Generator
    {
        yield 'empty' => [''];
        yield 'no scheme' => ['example.com/handler'];
        yield 'plain text' => ['not a url'];
    }
}
```

### 3. `tests/Unit/Services/Workflows/Robot/Service/RobotTest.php`

New unit test (the Robot service has no unit tests yet). Uses `NullCore` / `NullBatch` — no HTTP.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Workflows\Robot\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\Workflows\Robot\Result\AddedRobotResult;
use Bitrix24\SDK\Services\Workflows\Robot\Result\UpdateRobotResult;
use Bitrix24\SDK\Services\Workflows\Robot\Service\Robot;
use Bitrix24\SDK\Services\Workflows\Template\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Robot::class)]
class RobotTest extends TestCase
{
    private Robot $robot;

    #[\Override]
    protected function setUp(): void
    {
        $this->robot = new Robot(
            new Batch(new NullCore(), new NullLogger()),
            new NullCore(),
            new NullLogger()
        );
    }

    #[Test]
    #[TestDox('add() returns AddedRobotResult')]
    public function testAddReturnsAddedRobotResult(): void
    {
        $result = $this->robot->add(
            'test_robot',
            'https://example.com/handler',
            1,
            ['en' => 'Robot name'],
            true,
            [],
            false,
            []
        );

        $this->assertInstanceOf(AddedRobotResult::class, $result);
    }

    #[Test]
    #[TestDox('add() accepts a Url placement handler when placement is enabled')]
    public function testAddAcceptsPlacementHandlerUrl(): void
    {
        $result = $this->robot->add(
            'test_robot',
            'https://example.com/handler',
            1,
            ['en' => 'Robot name'],
            false,
            [],
            true,   // isUsePlacement = true
            [],
            [],
            [],
            [],
            new Url('https://example.com/placement')
        );

        $this->assertInstanceOf(AddedRobotResult::class, $result);
    }

    #[Test]
    #[TestDox('add() throws when placement is enabled but placement handler URL is missing')]
    public function testAddThrowsWhenPlacementHandlerMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->robot->add(
            'test_robot',
            'https://example.com/handler',
            1,
            ['en' => 'Robot name'],
            false,
            [],
            true,   // isUsePlacement = true
            [],
            [],
            [],
            [],
            null    // placementHandlerUrl = null -> must throw
        );
    }

    #[Test]
    #[TestDox('add() accepts a Url value object for the handler URL (Stage 1 migration)')]
    public function testAddAcceptsHandlerUrlAsUrl(): void
    {
        $result = $this->robot->add(
            'test_robot',
            new Url('https://example.com/handler'),
            1,
            ['en' => 'Robot name'],
            false,
            [],
            false,
            []
        );

        $this->assertInstanceOf(AddedRobotResult::class, $result);
    }

    #[Test]
    #[TestDox('update() accepts a Url value object for the handler URL (Stage 1 migration)')]
    public function testUpdateAcceptsHandlerUrlAsUrl(): void
    {
        $result = $this->robot->update(
            'test_robot',
            new Url('https://example.com/handler')
        );

        $this->assertInstanceOf(UpdateRobotResult::class, $result);
    }
}
```

> Note: confirm the exact `Batch` constructor signature used by other Workflows unit tests before
> finalizing `setUp()`. If constructing `Batch` in a unit test is awkward, replace it with the
> project's standard batch stub. This does not change production code.

---

## Files to Modify

### 1. `src/Services/Workflows/Robot/Service/Robot.php`

Three edits: rewrite `add()`, widen `update()`'s `handlerUrl`, add the private `resolveUrl()`
helper. Also add the import `use Bitrix24\SDK\Core\ValueObjects\Url;`
(`InvalidArgumentException` is already imported at line 21).

**1a. `add()`** — new signature and body (also update its `@see` docblock + `#[ApiEndpointMetadata]` URL):

```php
    /**
     * Registers new automation rule.
     *
     * @throws BaseException
     * @throws InvalidArgumentException
     * @throws TransportException
     * @see https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-robot/bizproc-robot-add.html
     */
    #[ApiEndpointMetadata(
        'bizproc.robot.add',
        'https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-robot/bizproc-robot-add.html',
        'Registers new automation rule.'
    )]
    public function add(
        string     $code,
        string|Url $handlerUrl,
        int        $b24AuthUserId,
        array      $localizedRobotName,
        bool       $isUseSubscription,
        array      $properties,
        bool       $isUsePlacement,
        array      $returnProperties,
        array      $localizedRobotDescription = [],
        array      $documentType = [],
        array      $filter = [],
        ?Url       $placementHandlerUrl = null
    ): Workflows\Robot\Result\AddedRobotResult
    {
        if ($isUsePlacement && $placementHandlerUrl === null) {
            throw new InvalidArgumentException(
                'placementHandlerUrl is required when isUsePlacement is true'
            );
        }

        $payload = [
            'CODE' => $code,
            'HANDLER' => $this->resolveUrl($handlerUrl),
            'AUTH_USER_ID' => $b24AuthUserId,
            'NAME' => $localizedRobotName,
            'USE_SUBSCRIPTION' => $isUseSubscription ? 'Y' : 'N',
            'PROPERTIES' => $properties,
            'USE_PLACEMENT' => $isUsePlacement ? 'Y' : 'N',
            'RETURN_PROPERTIES' => $returnProperties,
        ];

        if ($localizedRobotDescription !== []) {
            $payload['DESCRIPTION'] = $localizedRobotDescription;
        }

        if ($documentType !== []) {
            $payload['DOCUMENT_TYPE'] = $documentType;
        }

        if ($filter !== []) {
            $payload['FILTER'] = $filter;
        }

        if ($placementHandlerUrl !== null) {
            $payload['PLACEMENT_HANDLER'] = $placementHandlerUrl->getUrl();
        }

        return new Workflows\Robot\Result\AddedRobotResult(
            $this->core->call('bizproc.robot.add', $payload)
        );
    }
```

**1b. `update()`** — widen only the `handlerUrl` parameter and normalize it (rest of the method is
unchanged):

```php
    public function update(
        string          $code,
        Url|string|null $handlerUrl = null,   // ← changed from ?string
        ?int            $b24AuthUserId = null,
        ?array          $localizedRobotName = null,
        ?bool           $isUseSubscription = null,
        ?array          $properties = null,
        ?bool           $isUsePlacement = null,
        ?array          $returnProperties = null
    ): Workflows\Robot\Result\UpdateRobotResult
    {
        $fieldsToUpdate = [];
        if ($handlerUrl !== null) {
            $fieldsToUpdate['HANDLER'] = $this->resolveUrl($handlerUrl);   // ← changed
        }
        // …remaining if-blocks unchanged…
    }
```

**1c. Private helper** — add to the `Robot` class:

```php
    /**
     * @throws InvalidArgumentException
     */
    private function resolveUrl(string|Url $url): string
    {
        return $url instanceof Url ? $url->getUrl() : (new Url($url))->getUrl();
    }
```

A raw `string` is wrapped in `new Url(...)`, so an invalid URL string throws
`InvalidArgumentException` — same validation whether the caller passes a string or a `Url`.

### 2. `CHANGELOG.md`

Add under `## 3.4.0 – UNRELEASED` (create the `### Added` / `### Changed` sub-headers only if they
are not already present under 3.4.0):

```markdown
### Added
- Added `Bitrix24\SDK\Core\ValueObjects\Url` value object ([#493](https://github.com/bitrix24/b24phpsdk/issues/493))
- Added `DESCRIPTION`, `DOCUMENT_TYPE`, `FILTER` and `PLACEMENT_HANDLER` fields to `bizproc.robot.add` ([#493](https://github.com/bitrix24/b24phpsdk/issues/493))

### Changed
- `bizproc.robot.add` and `bizproc.robot.update` now accept a `Url` value object (or a raw string) for the handler URL ([#493](https://github.com/bitrix24/b24phpsdk/issues/493))
```

### Not modified (already in place)

- `phpunit.xml.dist` — `integration_tests_scope_workflows` suite already exists (lines 100-101).
- `Makefile` — `test-integration-scope-workflows` target already exists (lines 221-223).
- No new service builder wiring: `WorkflowsServiceBuilder::robot()` already exists (line 39).

---

## Follow-up issue: SDK-wide `Url` adoption (two-stage refactoring)

As part of this work, file a **separate** tracking issue in `bitrix24/b24phpsdk` to continue the
`Url` rollout beyond the `Robot` service (which is migrated in #493 as the reference
implementation). Before creating it: search for duplicates (`gh search issues`) and verify the
exact label name with `gh label list --repo bitrix24/b24phpsdk` (the repo uses `enhancement in
SDK`, not `enhancement`).

> Creating a public GitHub issue is an outward-facing action — do it only after the plan is
> approved (this section is the draft, not a created issue yet).

Draft issue body (English):

```markdown
## Problem

URLs are passed as raw `string` across ~70 service/application method signatures
(e.g. `handlerUrl`, `serverUrl`, `oauthServerUrl`). There is no shared value object, and URL
validation is duplicated — only `Core\Credentials\WebhookUrl` validates via `filter_var`. This is
inconsistent with the SDK's value-object approach for other primitives (money, IP, phone) and lets
invalid URLs reach the transport layer.

`#493` introduced `Bitrix24\SDK\Core\ValueObjects\Url` and applied **Stage 1** to the `Robot`
service (`bizproc.robot.add` / `bizproc.robot.update` accept `string|Url`). This issue tracks
rolling the same pattern out to the rest of the SDK and then executing Stage 2.

## Proposed solution

- **Stage 1 (non-breaking, minor release):** widen the remaining URL parameters to a `string|Url`
  union (raw strings wrapped internally into `Url`); document raw-string usage as soft-deprecated.
  Refactor `Core\Credentials\WebhookUrl` to build on top of `Core\ValueObjects\Url` so validation
  lives in one place. Consider extracting the `resolveUrl()` helper introduced in `Robot` into a
  shared location (trait or `AbstractService`).
- **Stage 2 (breaking, next major release):** type all URL parameters as `Url` only; remove the
  `string` union.

## Acceptance criteria

- [ ] `Core\ValueObjects\Url` is reused everywhere a URL is accepted as input
- [ ] `Core\Credentials\WebhookUrl` reuses `Url` (no duplicated validation)
- [ ] Stage 1 preserves full backward compatibility (`string|Url`) for the remaining signatures
- [ ] Stage 2 is scheduled for the next major and removes the `string` union
- [ ] Unit tests cover both stages
- [ ] `CHANGELOG.md` is updated with the issue link

Depends on #493 (introduces the `Url` value object and the Stage 1 reference implementation).
```

---

## Deptrac compliance

Changes touch:
- `src/Core/ValueObjects/Url.php` — new **Core** layer class; imports only
  `Core\Exceptions\InvalidArgumentException` (same layer). Core depends on nothing outside itself.
- `src/Services/Workflows/Robot/Service/Robot.php` — **Services** layer; adds an import of
  `Core\ValueObjects\Url`. Services → Core is an allowed dependency (same as the existing
  `InvalidArgumentException` / `CoreInterface` imports).
- `tests/Unit/...` — not covered by deptrac layers.

No new cross-layer edge and no new `skip_violations` entry are introduced.

---

## Verification

Light gate (must all pass before reporting done):

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Integration gate: **not applicable** for `bizproc.robot.add` — it requires an OAuth application
context and admin rights and cannot be exercised through the webhook-based
`integration_tests_scope_workflows` suite (see "Testing constraint" above).

---

## TDD order (for the implementation phase — do NOT start yet)

1. RED: add `tests/Unit/Core/ValueObjects/UrlTest.php`; run `make test-unit` and confirm it fails
   (class does not exist yet).
2. GREEN: create `src/Core/ValueObjects/Url.php`; run `make test-unit` until the `Url` tests pass.
3. RED: add `tests/Unit/Services/Workflows/Robot/Service/RobotTest.php` with the five tests
   (result type, placement-with-`Url`, placement-missing-throws, `add()` `handlerUrl` as `Url`,
   `update()` `handlerUrl` as `Url`); run `make test-unit` and confirm the new behaviours fail
   (method not yet updated).
4. GREEN: apply the `Robot.php` changes — add the `Url` import, the private `resolveUrl()` helper,
   the four new `add()` params + placement precondition + conditional payload, and widen
   `handlerUrl` to `string|Url` in `add()` and `update()`; run `make test-unit` until green.
5. REFACTOR: run the full light gate; adjust code style as reported by cs-fixer / rector.
6. Update `CHANGELOG.md` (the `Url` VO line, the robot-fields line, and the `### Changed` widening
   line).
7. After plan approval and a green light gate, create the follow-up issue (see "Follow-up issue"
   section) — search for duplicates and verify the label name first.
