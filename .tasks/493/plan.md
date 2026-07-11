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
- adds two bizproc `CODE` value objects — `Bitrix24\SDK\Services\Workflows\ValueObjects\RobotCode`
  and `ActivityCode` (charset `a-z A-Z 0-9 . - _`, non-empty) — with unit tests. `RobotCode` is
  wired into `Robot::add()` / `Robot::update()` as a `string|RobotCode` union (same Stage-1
  pattern); `ActivityCode` is created and tested but not yet wired into the `Activity` service;
- introduces a reusable `Bitrix24\SDK\Core\ValueObjects\LocalizedString` value object (a typed
  replacement for raw `['en' => '...']` localization maps, reusing the existing
  `Core\Contracts\LangCodes` enum) and wires it into the `NAME` / `DESCRIPTION` parameters of
  `Robot::add()` / `Robot::update()` as an `array|LocalizedString` union (same Stage-1 pattern);
- files a **follow-up issue** to roll Stage 1 out to the remaining ~68 SDK URL signatures (plus the
  `Activity` code migration and the SDK-wide `LocalizedString` adoption) and then execute Stage 2
  (value-object-only) in the next major (see "Follow-up issue" section below).

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

1. **NAME / DESCRIPTION accept `array|LocalizedString`** — both localized parameters are widened
   from `array` to an `array|LocalizedString` union (Stage 1). A raw `['en' => '...']` array keeps
   working; the new `LocalizedString` VO is the typed, autocomplete-friendly alternative. The
   payload is normalized via `resolveLocalizedString()` (see decision 8).
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
   The Stage-1 migrations (`handlerUrl` → `string|Url`, `code` → `string|RobotCode`, `NAME` /
   `DESCRIPTION` → `array|LocalizedString`) additionally touch `Robot::update()`. The `Activity`
   service is **not** modified in this change.
6. **Docs link** — while editing the `#[ApiEndpointMetadata]` of `add()`, switch its URL and the
   `@see` docblock to the English `apidocs.bitrix24.com` page (skill rule for changed metadata).
7. **New `Url` value object** — introduce `Bitrix24\SDK\Core\ValueObjects\Url`, a copy of the
   validation in `Core\Credentials\WebhookUrl`, and use it as the type of the new
   `PLACEMENT_HANDLER` parameter (VO-only, `?Url`). `WebhookUrl` is **not** refactored here; that
   migration and the SDK-wide rollout are tracked by the follow-up issue.
8. **Normalization helpers** — private `resolveUrl(string|Url $url): string`,
   `resolveRobotCode(string|RobotCode $code): string` and
   `resolveLocalizedString(array|LocalizedString $value): array` in `Robot` convert a union argument
   to the payload shape: a value object returns `->getUrl()` / `->getCode()` / `->toArray()`; a raw
   `string` is wrapped in `new Url(...)` / `new RobotCode(...)` first (so raw strings are validated
   the same way), while a raw localization `array` is passed through unchanged (legacy behaviour).
   `placementHandlerUrl` is already a `Url`, so it uses `->getUrl()` directly.
9. **Bizproc CODE value objects** — introduce `RobotCode` and `ActivityCode` in the new
   `Bitrix24\SDK\Services\Workflows\ValueObjects` namespace (**Services** layer, bizproc-specific —
   unlike the generic `Url` in `Core`). Both validate the same charset from the API docs
   (`a-z A-Z 0-9 . - _`, non-empty); uniqueness is server-side and not validated. `RobotCode` is
   wired into `Robot::add()` / `Robot::update()` as `string|RobotCode`. `ActivityCode` is created
   and unit-tested but **not** wired into the `Activity` service in this change (deferred to the
   follow-up issue) to keep #493 from expanding into `bizproc.activity.*`.
10. **`LocalizedString` value object** — introduce `Bitrix24\SDK\Core\ValueObjects\LocalizedString`
    (**Core** layer, generic, reuses `Core\Contracts\LangCodes`) as a typed replacement for raw
    `['en' => '...']` maps. Minimal, immutable-by-convention API (PSR-7 `with*()` style — **not**
    `readonly`, so the clone-based `with()` works on PHP 8.4): a public constructor
    `__construct(LangCodes $lang, string $text)` for the common single-language case, a fluent
    `with(LangCodes $lang, string $text): self` for additional languages, and
    `toArray(): array<value-of<LangCodes>, string>`. No `of()` / `create()` / `fromArray()` /
    `get()` in the public surface (deliberately trimmed for a clean DX). Wired into `NAME` /
    `DESCRIPTION` as `array|LocalizedString`; SDK-wide adoption is a follow-up.

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
    string|RobotCode        $code,                        // widened from string (Stage 1) ← changed
    string|Url              $handlerUrl,                  // widened from string (Stage 1) ← changed
    int                     $b24AuthUserId,
    array|LocalizedString   $localizedRobotName,          // widened from array (Stage 1) ← changed
    bool                    $isUseSubscription,
    array                   $properties,
    bool                    $isUsePlacement,
    array                   $returnProperties,
    array|LocalizedString   $localizedRobotDescription = [], // DESCRIPTION        ← new
    array                   $documentType = [],              // DOCUMENT_TYPE      ← new
    array                   $filter = [],                    // FILTER             ← new
    ?Url                    $placementHandlerUrl = null      // PLACEMENT_HANDLER  ← new (Url VO)
): AddedRobotResult
```

`update()` is changed too — its `code`, `handlerUrl` and `localizedRobotName` parameters are
widened (no new fields):

```php
public function update(
    string|RobotCode           $code,                     // widened from string (Stage 1) ← changed
    Url|string|null            $handlerUrl = null,        // widened from ?string (Stage 1) ← changed
    ?int                       $b24AuthUserId = null,
    array|LocalizedString|null $localizedRobotName = null,// widened from ?array (Stage 1) ← changed
    // …remaining parameters unchanged…
): UpdateRobotResult
```

Parameter → API field mapping (`add()`):

| SDK parameter | API field | Sent when |
|---|---|---|
| `$code` (`string\|RobotCode`) | `CODE` | always; normalized via `resolveRobotCode()` |
| `$handlerUrl` (`string\|Url`) | `HANDLER` | always; normalized via `resolveUrl()` |
| `$localizedRobotName` (`array\|LocalizedString`) | `NAME` | always; normalized via `resolveLocalizedString()` |
| `$localizedRobotDescription` (`array\|LocalizedString`) | `DESCRIPTION` | non-empty after `resolveLocalizedString()` |
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
    new LocalizedString(LangCodes::EN, 'My robot'), // localizedRobotName — now also accepts a VO
    true,                                       // isUseSubscription
    ['text' => ['Name' => 'Text', 'Type' => 'text']],       // properties
    true,                                       // isUsePlacement
    ['result' => ['Name' => 'Result', 'Type' => 'string']], // returnProperties
    new LocalizedString(LangCodes::EN, 'Sends a message'), // localizedRobotDescription → DESCRIPTION
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

Raw values still work everywhere (as in the required-arguments example above) — `Url` for
`handlerUrl`, `RobotCode` for `code`, and `LocalizedString` for `NAME` / `DESCRIPTION` are the new,
type-safe options. Imports:
`use Bitrix24\SDK\Core\ValueObjects\Url;`,
`use Bitrix24\SDK\Core\ValueObjects\LocalizedString;`,
`use Bitrix24\SDK\Core\Contracts\LangCodes;`,
`use Bitrix24\SDK\Services\Workflows\ValueObjects\RobotCode;`.

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

### 3. `src/Services/Workflows/ValueObjects/RobotCode.php`

Value object for the `bizproc.robot.add` / `bizproc.robot.update` `CODE` field, in the new
`Bitrix24\SDK\Services\Workflows\ValueObjects` namespace (**Services** layer — bizproc-specific,
unlike the generic `Url` which lives in `Core`). Validation from the API docs: allowed characters
are `a-z A-Z 0-9 . - _`; the code must be non-empty (uniqueness is server-side, not validated
here).

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Workflows\ValueObjects;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

final class RobotCode
{
    private const string PATTERN = '/^[a-zA-Z0-9._-]+$/';

    private string $code;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $code)
    {
        if (preg_match(self::PATTERN, $code) !== 1) {
            throw new InvalidArgumentException(sprintf(
                'robot code "%s" is invalid, allowed characters are a-z, A-Z, 0-9, dot, hyphen and underscore',
                $code
            ));
        }

        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
```

### 4. `src/Services/Workflows/ValueObjects/ActivityCode.php`

Sibling value object for the `bizproc.activity.add` / `bizproc.activity.update` `CODE` field. Same
allowed character set (`a-z A-Z 0-9 . - _`, non-empty) per the API docs (errors `Empty activity
code!` / `Wrong activity code!`). **Created and unit-tested in #493; wiring it into the `Activity`
service is deferred** (the `Activity` service is not modified in this change — see decision 9) and
tracked by the follow-up issue, unless explicitly requested for #493.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Workflows\ValueObjects;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

final class ActivityCode
{
    private const string PATTERN = '/^[a-zA-Z0-9._-]+$/';

    private string $code;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $code)
    {
        if (preg_match(self::PATTERN, $code) !== 1) {
            throw new InvalidArgumentException(sprintf(
                'activity code "%s" is invalid, allowed characters are a-z, A-Z, 0-9, dot, hyphen and underscore',
                $code
            ));
        }

        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
```

### 5. `tests/Unit/Services/Workflows/ValueObjects/RobotCodeTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Workflows\ValueObjects;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Workflows\ValueObjects\RobotCode;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RobotCode::class)]
class RobotCodeTest extends TestCase
{
    #[Test]
    #[TestDox('valid robot code is accepted and returned unchanged')]
    #[DataProvider('validCodeProvider')]
    public function testValidCode(string $code): void
    {
        $this->assertSame($code, (new RobotCode($code))->getCode());
    }

    #[Test]
    #[TestDox('invalid robot code throws InvalidArgumentException')]
    #[DataProvider('invalidCodeProvider')]
    public function testInvalidCode(string $code): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RobotCode($code);
    }

    public static function validCodeProvider(): Generator
    {
        yield 'letters' => ['robotCode'];
        yield 'digits' => ['robot123'];
        yield 'dot, hyphen, underscore' => ['my.robot-code_1'];
    }

    public static function invalidCodeProvider(): Generator
    {
        yield 'empty' => [''];
        yield 'space' => ['robot code'];
        yield 'slash' => ['robot/code'];
        yield 'unicode' => ['робот'];
    }
}
```

### 6. `tests/Unit/Services/Workflows/ValueObjects/ActivityCodeTest.php`

Same structure as `RobotCodeTest`, targeting `ActivityCode`
(`#[CoversClass(ActivityCode::class)]`, identical valid/invalid data sets and assertions).

### 7. `tests/Unit/Services/Workflows/Robot/Service/RobotTest.php`

New unit test (the Robot service has no unit tests yet). Uses `NullCore` / `NullBatch` — no HTTP.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Workflows\Robot\Service;

use Bitrix24\SDK\Core\Contracts\LangCodes;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\ValueObjects\LocalizedString;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\Workflows\Robot\Result\AddedRobotResult;
use Bitrix24\SDK\Services\Workflows\Robot\Result\UpdateRobotResult;
use Bitrix24\SDK\Services\Workflows\Robot\Service\Robot;
use Bitrix24\SDK\Services\Workflows\Template\Service\Batch;
use Bitrix24\SDK\Services\Workflows\ValueObjects\RobotCode;
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

    #[Test]
    #[TestDox('add() accepts a RobotCode value object for the code (Stage 1 migration)')]
    public function testAddAcceptsRobotCode(): void
    {
        $result = $this->robot->add(
            new RobotCode('test_robot'),
            'https://example.com/handler',
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
    #[TestDox('update() accepts a RobotCode value object for the code (Stage 1 migration)')]
    public function testUpdateAcceptsRobotCode(): void
    {
        $result = $this->robot->update(
            new RobotCode('test_robot'),
            new Url('https://example.com/handler')
        );

        $this->assertInstanceOf(UpdateRobotResult::class, $result);
    }

    #[Test]
    #[TestDox('add() accepts a LocalizedString for the NAME (Stage 1 migration)')]
    public function testAddAcceptsLocalizedStringName(): void
    {
        $result = $this->robot->add(
            'test_robot',
            'https://example.com/handler',
            1,
            new LocalizedString(LangCodes::EN, 'My robot'),
            false,
            [],
            false,
            []
        );

        $this->assertInstanceOf(AddedRobotResult::class, $result);
    }
}
```

> Note: confirm the exact `Batch` constructor signature used by other Workflows unit tests before
> finalizing `setUp()`. If constructing `Batch` in a unit test is awkward, replace it with the
> project's standard batch stub. This does not change production code.

### 8. `src/Core/ValueObjects/LocalizedString.php`

Generic **Core** value object replacing raw `['en' => '...']` localization maps; reuses the
existing `Core\Contracts\LangCodes` enum. Immutable-by-convention (PSR-7 `with*()` style) — **not**
`readonly`, so the clone-based `with()` works on PHP 8.4. Minimal public surface: constructor +
`with()` + `toArray()`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Core\ValueObjects;

use Bitrix24\SDK\Core\Contracts\LangCodes;

final class LocalizedString
{
    /** @var array<value-of<LangCodes>, string> */
    private array $values;

    public function __construct(LangCodes $lang, string $text)
    {
        $this->values = [$lang->value => $text];
    }

    public function with(LangCodes $lang, string $text): self
    {
        $clone = clone $this;
        $clone->values[$lang->value] = $text;

        return $clone;
    }

    /**
     * @return array<value-of<LangCodes>, string>
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
```

### 9. `tests/Unit/Core/ValueObjects/LocalizedStringTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Core\ValueObjects;

use Bitrix24\SDK\Core\Contracts\LangCodes;
use Bitrix24\SDK\Core\ValueObjects\LocalizedString;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocalizedString::class)]
class LocalizedStringTest extends TestCase
{
    #[Test]
    #[TestDox('single language via constructor maps to a lang => text array')]
    public function testSingleLanguage(): void
    {
        $this->assertSame(
            ['en' => 'My robot'],
            (new LocalizedString(LangCodes::EN, 'My robot'))->toArray()
        );
    }

    #[Test]
    #[TestDox('with() adds languages immutably (original is unchanged)')]
    public function testWithAddsLanguagesImmutably(): void
    {
        $en = new LocalizedString(LangCodes::EN, 'My robot');
        $both = $en->with(LangCodes::DE, 'Mein Roboter');

        $this->assertSame(['en' => 'My robot'], $en->toArray());
        $this->assertSame(['en' => 'My robot', 'de' => 'Mein Roboter'], $both->toArray());
    }

    #[Test]
    #[TestDox('with() on the same language overwrites the value')]
    public function testWithOverwritesSameLanguage(): void
    {
        $this->assertSame(
            ['en' => 'new'],
            (new LocalizedString(LangCodes::EN, 'old'))->with(LangCodes::EN, 'new')->toArray()
        );
    }
}
```

---

## Files to Modify

### 1. `src/Services/Workflows/Robot/Service/Robot.php`

Edits: rewrite `add()`, widen `update()`, add the private `resolveUrl()`, `resolveRobotCode()` and
`resolveLocalizedString()` helpers. In both `add()` and `update()` the parameters `code`
(→ `string|RobotCode`), `handlerUrl` (→ `string|Url`) and `localizedRobotName`
(→ `array|LocalizedString`) are widened; `add()` also widens `localizedRobotDescription`. Add
imports `use Bitrix24\SDK\Core\ValueObjects\Url;`,
`use Bitrix24\SDK\Core\ValueObjects\LocalizedString;` and
`use Bitrix24\SDK\Services\Workflows\ValueObjects\RobotCode;` (`InvalidArgumentException` is already
imported at line 21). Column alignment below is illustrative — `php-cs-fixer` reformats it.

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
        string|RobotCode      $code,
        string|Url            $handlerUrl,
        int                   $b24AuthUserId,
        array|LocalizedString $localizedRobotName,
        bool                  $isUseSubscription,
        array                 $properties,
        bool                  $isUsePlacement,
        array                 $returnProperties,
        array|LocalizedString $localizedRobotDescription = [],
        array                 $documentType = [],
        array                 $filter = [],
        ?Url                  $placementHandlerUrl = null
    ): Workflows\Robot\Result\AddedRobotResult
    {
        if ($isUsePlacement && $placementHandlerUrl === null) {
            throw new InvalidArgumentException(
                'placementHandlerUrl is required when isUsePlacement is true'
            );
        }

        $payload = [
            'CODE' => $this->resolveRobotCode($code),
            'HANDLER' => $this->resolveUrl($handlerUrl),
            'AUTH_USER_ID' => $b24AuthUserId,
            'NAME' => $this->resolveLocalizedString($localizedRobotName),
            'USE_SUBSCRIPTION' => $isUseSubscription ? 'Y' : 'N',
            'PROPERTIES' => $properties,
            'USE_PLACEMENT' => $isUsePlacement ? 'Y' : 'N',
            'RETURN_PROPERTIES' => $returnProperties,
        ];

        $description = $this->resolveLocalizedString($localizedRobotDescription);
        if ($description !== []) {
            $payload['DESCRIPTION'] = $description;
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

**1b. `update()`** — widen `code`, `handlerUrl` and `localizedRobotName`, normalize each (rest of
the if-blocks unchanged):

```php
    public function update(
        string|RobotCode           $code,                 // ← changed from string
        Url|string|null            $handlerUrl = null,    // ← changed from ?string
        ?int                       $b24AuthUserId = null,
        array|LocalizedString|null $localizedRobotName = null, // ← changed from ?array
        ?bool                      $isUseSubscription = null,
        ?array                     $properties = null,
        ?bool                      $isUsePlacement = null,
        ?array                     $returnProperties = null
    ): Workflows\Robot\Result\UpdateRobotResult
    {
        $fieldsToUpdate = [];
        if ($handlerUrl !== null) {
            $fieldsToUpdate['HANDLER'] = $this->resolveUrl($handlerUrl);   // ← changed
        }
        if ($localizedRobotName !== null) {
            $fieldsToUpdate['NAME'] = $this->resolveLocalizedString($localizedRobotName);   // ← changed
        }
        // …remaining if-blocks unchanged…

        return new Workflows\Robot\Result\UpdateRobotResult($this->core->call(
            'bizproc.robot.update',
            [
                'CODE' => $this->resolveRobotCode($code),   // ← changed
                'FIELDS' => $fieldsToUpdate,
            ]
        ));
    }
```

**1c. Private helpers** — add to the `Robot` class:

```php
    /**
     * @throws InvalidArgumentException
     */
    private function resolveUrl(string|Url $url): string
    {
        return $url instanceof Url ? $url->getUrl() : (new Url($url))->getUrl();
    }

    /**
     * @throws InvalidArgumentException
     */
    private function resolveRobotCode(string|RobotCode $code): string
    {
        return $code instanceof RobotCode ? $code->getCode() : (new RobotCode($code))->getCode();
    }

    /**
     * @param array<string, string>|LocalizedString $value
     * @return array<string, string>
     */
    private function resolveLocalizedString(array|LocalizedString $value): array
    {
        return $value instanceof LocalizedString ? $value->toArray() : $value;
    }
```

For `Url` / `RobotCode`, a raw `string` is wrapped in `new Url(...)` / `new RobotCode(...)`, so an
invalid value throws `InvalidArgumentException` — same validation whether the caller passes a string
or the value object. For `LocalizedString`, a raw `array` is passed through unchanged (legacy
behaviour); passing a `LocalizedString` is the typed path.

### 2. `CHANGELOG.md`

Add under `## 3.4.0 – UNRELEASED` (create the `### Added` / `### Changed` sub-headers only if they
are not already present under 3.4.0):

```markdown
### Added
- Added `Bitrix24\SDK\Core\ValueObjects\Url` value object ([#493](https://github.com/bitrix24/b24phpsdk/issues/493))
- Added `Bitrix24\SDK\Core\ValueObjects\LocalizedString` value object ([#493](https://github.com/bitrix24/b24phpsdk/issues/493))
- Added `Bitrix24\SDK\Services\Workflows\ValueObjects\RobotCode` and `ActivityCode` value objects ([#493](https://github.com/bitrix24/b24phpsdk/issues/493))
- Added `DESCRIPTION`, `DOCUMENT_TYPE`, `FILTER` and `PLACEMENT_HANDLER` fields to `bizproc.robot.add` ([#493](https://github.com/bitrix24/b24phpsdk/issues/493))

### Changed
- `bizproc.robot.add` and `bizproc.robot.update` now accept a `Url` value object (or a raw string) for the handler URL ([#493](https://github.com/bitrix24/b24phpsdk/issues/493))
- `bizproc.robot.add` and `bizproc.robot.update` now accept a `RobotCode` value object (or a raw string) for the code ([#493](https://github.com/bitrix24/b24phpsdk/issues/493))
- `bizproc.robot.add` and `bizproc.robot.update` now accept a `LocalizedString` value object (or a raw array) for the localized `NAME` / `DESCRIPTION` ([#493](https://github.com/bitrix24/b24phpsdk/issues/493))
```

### Not modified (already in place)

- `phpunit.xml.dist` — `integration_tests_scope_workflows` suite already exists (lines 100-101).
- `Makefile` — `test-integration-scope-workflows` target already exists (lines 221-223).
- No new service builder wiring: `WorkflowsServiceBuilder::robot()` already exists (line 39).

---

## Follow-up issue: SDK-wide value-object adoption (two-stage refactoring)

As part of this work, file a **separate** tracking issue in `bitrix24/b24phpsdk` to continue the
value-object rollout (`Url`, `LocalizedString`, bizproc codes) beyond the `Robot` service (which is
migrated in #493 as the reference implementation). Before creating it: search for duplicates (`gh search issues`) and verify the
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

`#493` introduced `Bitrix24\SDK\Core\ValueObjects\Url` and `LocalizedString`, plus the
`Services\Workflows\ValueObjects\RobotCode` / `ActivityCode` code objects, and applied **Stage 1**
to the `Robot` service (`bizproc.robot.add` / `bizproc.robot.update` accept `string|Url`,
`string|RobotCode`, `array|LocalizedString`). This issue tracks rolling the same pattern out to the
rest of the SDK and then executing Stage 2.

## Proposed solution

- **Stage 1 (URLs):** widen the remaining URL parameters to a `string|Url` union (raw strings
  wrapped internally into `Url`); document raw-string usage as soft-deprecated. Refactor
  `Core\Credentials\WebhookUrl` to build on top of `Core\ValueObjects\Url` so validation lives in
  one place. Consider extracting the `resolve*()` helpers introduced in `Robot` into a shared
  location (trait or `AbstractService`).
- **Stage 1 (bizproc codes):** wire the `ActivityCode` value object (already created in #493) into
  `bizproc.activity.add` / `bizproc.activity.update` as `string|ActivityCode`, mirroring the
  `RobotCode` migration.
- **Stage 1 (localization):** widen the remaining localized `array` parameters (e.g. the `Activity`
  `NAME` / `DESCRIPTION`, and other services) to `array|LocalizedString`, reusing the VO created in
  #493.
- **Stage 2 (breaking, next major release):** type all URL, code and localization parameters as
  their value objects only; remove the `string` / `array` unions.

## Acceptance criteria

- [ ] `Core\ValueObjects\Url` is reused everywhere a URL is accepted as input
- [ ] `Core\ValueObjects\LocalizedString` is reused everywhere a localized string map is accepted
- [ ] `Core\Credentials\WebhookUrl` reuses `Url` (no duplicated validation)
- [ ] Stage 1 preserves full backward compatibility (`string|Url`, `array|LocalizedString`) for the remaining signatures
- [ ] Stage 2 is scheduled for the next major and removes the `string` / `array` unions
- [ ] Unit tests cover both stages
- [ ] `CHANGELOG.md` is updated with the issue link

Depends on #493 (introduces the `Url` / `LocalizedString` value objects and the Stage 1 reference implementation).
```

---

## Deptrac compliance

Changes touch:
- `src/Core/ValueObjects/Url.php` — new **Core** layer class; imports only
  `Core\Exceptions\InvalidArgumentException` (same layer). Core depends on nothing outside itself.
- `src/Core/ValueObjects/LocalizedString.php` — new **Core** layer class; imports only
  `Core\Contracts\LangCodes` (same layer). No validation/exception dependency.
- `src/Services/Workflows/ValueObjects/RobotCode.php` and `ActivityCode.php` — new **Services**
  layer classes; import only `Core\Exceptions\InvalidArgumentException` (Services → Core allowed).
- `src/Services/Workflows/Robot/Service/Robot.php` — **Services** layer; adds imports of
  `Core\ValueObjects\Url` and `Core\ValueObjects\LocalizedString` (Services → Core) and
  `Workflows\ValueObjects\RobotCode` (intra-Services, same `Workflows` scope — the same kind of
  import as the existing `Workflows\Robot\Result\*` and `Workflows\Template\Service\Batch` uses).
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

1. RED: add `tests/Unit/Core/ValueObjects/UrlTest.php` and
   `tests/Unit/Core/ValueObjects/LocalizedStringTest.php`; run `make test-unit` and confirm they
   fail (classes do not exist yet).
2. GREEN: create `src/Core/ValueObjects/Url.php` and `src/Core/ValueObjects/LocalizedString.php`;
   run `make test-unit` until the `Url` and `LocalizedString` tests pass.
3. RED: add `tests/Unit/Services/Workflows/ValueObjects/RobotCodeTest.php` and
   `ActivityCodeTest.php`; run `make test-unit` and confirm they fail (classes do not exist yet).
4. GREEN: create `src/Services/Workflows/ValueObjects/RobotCode.php` and `ActivityCode.php`; run
   `make test-unit` until the code-VO tests pass.
5. RED: add `tests/Unit/Services/Workflows/Robot/Service/RobotTest.php` with the eight tests
   (result type, placement-with-`Url`, placement-missing-throws, `add()`/`update()` `handlerUrl`
   as `Url`, `add()`/`update()` `code` as `RobotCode`, `add()` `NAME` as `LocalizedString`); run
   `make test-unit` and confirm the new behaviours fail (method not yet updated).
6. GREEN: apply the `Robot.php` changes — add the `Url`, `LocalizedString` and `RobotCode` imports,
   the private `resolveUrl()`, `resolveRobotCode()` and `resolveLocalizedString()` helpers, the four
   new `add()` params + placement precondition + conditional payload, and widen
   `code`/`handlerUrl`/`localizedRobotName`(+`localizedRobotDescription` in `add()`) in `add()` and
   `update()`; run `make test-unit` until green.
7. REFACTOR: run the full light gate; adjust code style as reported by cs-fixer / rector.
8. Update `CHANGELOG.md` (the three `### Added` VO lines, the robot-fields line, and the three
   `### Changed` widening lines).
9. After plan approval and a green light gate, create the follow-up issue (see "Follow-up issue"
   section) — search for duplicates and verify the label name first.
