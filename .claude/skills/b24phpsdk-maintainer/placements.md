# Implementing placements for a scope

Use this guide when the user asks to add support for Bitrix24 **widget placement codes**
(see `placement.list`), typed bind/unbind helpers, and `OPTIONS` payload builders within a
scope (e.g. IM, CRM, Tasks, Calendar, Sonet).

## Delivery order

Follow this order unless the issue explicitly narrows the scope:

1. Inspect the live `placement.list` response for the target scope and the official placement
   docs for every code you are going to expose.
2. Add or update `PlacementLocationCodes` for the scope.
3. Add or update scope-specific option builders and supporting enums.
4. If the scope exposes localized `LANG` payloads, model them with typed DTOs instead of
   exposing raw associative arrays in the scope service API.
5. Add a typed scope facade `Placements` with one bind and one unbind method per placement
   code, then register it in `<Scope>ServiceBuilder`.
6. Add unit and integration coverage for codes, options, localization DTOs, and the typed
   facade.
7. Add `@link` references to official docs and `@deprecated` tags where the upstream API is
   deprecated or no longer works.

## Directory layout

| Artefact | Location |
|---|---|
| Placement codes class for the scope | `src/Services/<Scope>/Placements/PlacementLocationCodes.php` |
| Typed bind/unbind facade for the scope | `src/Services/<Scope>/Placements/Placements.php` |
| Option builders (one per placement location) | `src/Services/<Scope>/Placements/<ScopePrefix><Location>PlacementOptions.php` |
| Localization DTOs for `LANG` payloads (if needed) | `src/Services/<Scope>/Placements/PlacementLangItem.php`, `PlacementLangMap.php` |
| Scope-specific enums (values used only by this scope) | `src/Services/<Scope>/Placements/<EnumName>.php` |
| Shared placement-layer contracts and enums (reused by ≥ 2 scopes) | `src/Services/Placement/` |
| Cross-SDK shared primitives (reused beyond placements) | `src/Core/Contracts/` |

## Naming rules

- The codes class is always named `PlacementLocationCodes` inside the scope namespace. Do not
  prefix the class name with the scope again.
- Public option builders should use a scope prefix when that keeps imports explicit and avoids
  collisions across scopes. IM uses `ImSidebarPlacementOptions`,
  `ImNavigationPlacementOptions`, `ImContextMenuPlacementOptions`, and
  `ImTextareaPlacementOptions`.
- Localization DTOs stay in the scope namespace unless they are reused by multiple scopes.
- If a language enum is shared across the SDK, place it in `src/Core/Contracts/`. IM uses
  `Bitrix24\SDK\Core\Contracts\LangCodes`.

## Placement codes: always a plain `class`, NOT an `enum`

```php
class PlacementLocationCodes
{
    // <short description>
    // See https://apidocs.bitrix24.com/...
    public const string <CODE> = '<CODE>';

    /**
     * @deprecated <when/why> — see https://apidocs.bitrix24.com/...
     */
    public const string <DEPRECATED_CODE> = '<DEPRECATED_CODE>';
}
```

**Why not an enum**: individual placements get deprecated independently (e.g. IM's
`IM_SMILES_SELECTOR` stopped working in `im 25.1600.0`, others remain active). A
`public const` carries a `@deprecated` PHPDoc tag cleanly; an enum `case` does not.

## Typed scope facade: expose one bind and one unbind method per placement

For scopes that expose a stable set of placement codes, add a dedicated `Placements` facade on
top of the generic `Placement` service.

```php
final readonly class Placements
{
    public function __construct(private Placement $placementService)
    {
    }

    /**
     * Register the `<CODE>` placement handler.
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/widgets/...
     */
    public function bind<PlacementName>(
        string $handlerUrl,
        PlacementLangMap $placementLangMap,
        <ScopePrefix><PlacementName>PlacementOptions $options,
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::<CODE>,
            $handlerUrl,
            $placementLangMap->toArray(),
            $options,
            $b24UserId,
        );
    }

    /**
     * Unregister the `<CODE>` placement handler.
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/widgets/...
     */
    public function unbind<PlacementName>(?string $handlerUrl = null): PlacementUnbindResult
    {
        return $this->placementService->unbind(PlacementLocationCodes::<CODE>, $handlerUrl);
    }
}
```

**Rules:**
- Bind methods should accept typed localization objects and typed option builders, not raw
  arrays.
- Call `PlacementLangMap::toArray()` inside the scope facade right before delegating to the
  generic `Placement` service.
- Add `@link` to the exact placement documentation page on every bind/unbind method. If the
  docs only provide a scope overview page for that placement, link the overview page.
- If a placement is deprecated upstream, keep the constant and facade methods for backward
  compatibility, mark them `@deprecated`, and avoid inventing new option builders unless the
  API still clearly documents them.
- Register the facade in `<Scope>ServiceBuilder` via a dedicated `placements()` method.

**Live example**: `src/Services/IM/Placements/Placements.php`,
`src/Services/IM/IMServiceBuilder.php`

## Localization payloads: typed DTOs instead of raw `array<string, array<string, string>>`

If the placement API expects a localized `LANG` payload, keep the raw array shape inside the
scope facade and expose typed DTOs publicly.

```php
final readonly class PlacementLangItem
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $groupName = null,
    ) {
    }
}

final readonly class PlacementLangMap
{
    public static function empty(): self
    {
        return new self([]);
    }

    public function with(LangCodes $langCode, PlacementLangItem $placementLangItem): self
    {
        // return cloned map with one locale added
    }

    public function toArray(): array
    {
        // convert to raw LANG payload expected by placement.bind
    }
}
```

**Rules:**
- The generic `Placement::bind()` contract stays raw and array-based unless multiple scopes
  need the typed abstraction. The typed scope facade is additive.
- Use immutable DTOs (`readonly` + cloning `with(...)`) for the localization map.
- Place shared language codes in `src/Core/Contracts/` when the enum is reusable outside the
  placements subsystem.

**Live example**: `src/Services/IM/Placements/PlacementLangItem.php`,
`src/Services/IM/Placements/PlacementLangMap.php`, `src/Core/Contracts/LangCodes.php`

## Option builders: fluent interface extending `AbstractPlacementOptions`

- One class per placement location (e.g. `ImTextareaPlacementOptions`,
  `ImSidebarPlacementOptions`)
- `final class <ScopePrefix><Location>PlacementOptions extends AbstractPlacementOptions`
- **Required** option fields → constructor parameters
- **Optional** option fields → fluent setters returning `self`
- `AbstractPlacementOptions` already provides shared setters `context()`, `role()`,
  `extranet()` and the `build(): array` implementation — do NOT duplicate them.
- If one placement reuses another placement's option shape, direct inheritance is acceptable
  instead of duplicating setters. IM uses `ImNavigationPlacementOptions extends ImTextareaPlacementOptions`.

## Splitting enums: shared vs scope-specific

| Criterion | Location |
|---|---|
| Value appears in a single scope (e.g. IM `ChatContext`, IM `PlacementColor`) | `src/Services/<Scope>/Placements/` |
| Value is reused across scopes (e.g. `Role`, `ExtranetAvailability`, `PlacementOptionsInterface`) | `src/Services/Placement/` |
| Value is reused outside placements as a general SDK primitive (e.g. `LangCodes`) | `src/Core/Contracts/` |

If a scope-specific enum is likely to be reused by another scope later, place it in
`src/Services/Placement/` from the start instead of moving it later.

## `Placement::bind()` is already compatible with typed option builders

The service signature already accepts `PlacementOptionsInterface|array`:

```php
public function bind(
    string $placementCode,
    string $handlerUrl,
    array $lang,
    PlacementOptionsInterface|array $options = [],
    ?int $b24UserId = null,
): PlacementBindResult
```

Do **not** change this signature just to make one scope more typed. Keep the generic service
backward-compatible and implement typed builders and localization wrappers in the scope facade.

## Mandatory reflection-based integration test

**Rule**: every scope `PlacementLocationCodes` class MUST have a corresponding integration
test at `tests/Integration/Services/<Scope>/Placements/PlacementLocationCodesTest.php`.

**Purpose**: detect drift between Bitrix24 API and the SDK. When Bitrix24 ships a new
placement in the scope, the test fails and the missing code is immediately visible.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\<Scope>\Placements;

use Bitrix24\SDK\Services\<Scope>\Placements\PlacementLocationCodes;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(PlacementLocationCodes::class)]
class PlacementLocationCodesTest extends TestCase
{
    private ServiceBuilder $sb;

    #[Test]
    #[TestDox('PlacementLocationCodes declares every <SCOPE_PREFIX>_ placement returned by placement.list')]
    public function testAllApi<Scope>PlacementsAreDeclared(): void
    {
        $remoteCodes = $this->sb->getPlacementScope()->placement()->list()->getLocationCodes();

        $scopeCodes = array_values(array_filter(
            $remoteCodes,
            static fn (string $code): bool => str_starts_with($code, '<SCOPE_PREFIX>_'),
        ));

        $reflectionClass = new ReflectionClass(PlacementLocationCodes::class);
        $declared = array_values($reflectionClass->getConstants());

        $missing = array_values(array_diff($scopeCodes, $declared));

        $this->assertSame([], $missing, sprintf(
            'PlacementLocationCodes is missing constants for placements returned by placement.list: %s',
            implode(', ', $missing),
        ));
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder(true);
    }
}
```

**Template notes:**
- `<SCOPE_PREFIX>` is the common prefix of the scope's placement codes (e.g. `IM`, `CRM`,
  `TASK`, `SONET_GROUP`). Always verify the actual prefix against the raw `placement.list`
  response before writing the filter.
- `Factory::getServiceBuilder(true)` requires application credentials — an incoming
  webhook is not sufficient for `placement.list`.

**Live example**: `tests/Integration/Services/IM/Placements/PlacementLocationCodesTest.php`

## Mandatory tests for the typed placement layer

At minimum, add these tests:

1. Option-builder unit tests:
   - `build()` with the minimal payload
   - `build()` with the full payload
   - every fluent setter returns `$this`
2. `PlacementLangMap` unit tests:
   - empty map serializes to `[]`
   - `with()` returns a new map
   - `toArray()` produces the exact `LANG` payload shape the API expects
3. `Placements` facade unit tests:
   - every bind method delegates with the right placement code
   - `PlacementLangMap` is converted internally, not by the caller
   - options and optional `$b24UserId` are forwarded unchanged
4. `Placements` facade integration smoke test:
   - bind and unbind at least one handler per exposed placement method against a live portal
   - assert success or non-negative deleted-handler counts as appropriate

Do not keep legacy tests for classes that no longer exist after a naming refactor.

**Live examples**:
- `tests/Unit/Services/IM/Placements/ImNavigationPlacementOptionsTest.php`
- `tests/Unit/Services/IM/Placements/PlacementLangMapTest.php`
- `tests/Unit/Services/IM/Placements/PlacementsTest.php`
- `tests/Integration/Services/IM/Placements/PlacementsTest.php`
