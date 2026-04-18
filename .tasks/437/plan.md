# Plan: Add IM placement codes and fluent options builders (issue #437)

## Context

Issue [#437](https://github.com/bitrix24/b24phpsdk/issues/437) initially asked to expose
the four IM widget placement codes for `placement.bind`. Scope was extended in the same
PR (#438) to also provide:

- A new namespace `Bitrix24\SDK\Services\IM\Placements` housing all IM-placement
  related code.
- A `PlacementOptionsInterface` contract so IM widget placements can be configured
  via type-safe fluent builders instead of raw associative arrays.
- Three concrete option builders (`TextareaPlacementOptions`,
  `SidebarPlacementOptions`, `ContextMenuPlacementOptions`).
- Four supporting string-backed enums (`ChatContext`, `Role`, `ExtranetAvailability`,
  `PlacementColor`).
- A backwards-compatible widening of `Placement::bind()` to accept both `array` and
  `PlacementOptionsInterface`.

### IM placement codes (constants)

Scope `im`:

| Code | Purpose | Docs |
|---|---|---|
| `IM_TEXTAREA` | Widget panel above the chat message input field | https://apidocs.bitrix24.com/api-reference/widgets/im/textarea.html |
| `IM_SIDEBAR` | Chat sidebar widget | https://apidocs.bitrix24.com/api-reference/widgets/im/sidebar.html |
| `IM_CONTEXT_MENU` | Context-menu item on a chat message | https://apidocs.bitrix24.com/api-reference/widgets/im/context-menu.html |
| `IM_SMILES_SELECTOR` | Smiles / Giphy selector — **no longer works since `im 25.1600.0`** (`@deprecated`) | https://apidocs.bitrix24.com/api-reference/widgets/im/smile-selector.html |

`IM_NAVIGATION` exists in the docs but is not in the issue scope and is not added.

### Options structure (per `bitrix24-article-details` docs)

| Parameter | TEXTAREA | SIDEBAR | CONTEXT_MENU | Type / values |
|---|---|---|---|---|
| `iconName` | required | required | — | string (Font Awesome class, e.g. `fa-cloud`) |
| `context`  | optional | optional | optional | enum-set: `USER`, `CHAT`, `LINES`, `CRM`, `ALL`; multi-value joined by `;`; default `ALL` |
| `role`     | optional | optional | optional | enum: `USER`, `ADMIN`; default `USER` |
| `color`    | optional | optional | — | enum (18 values): `RED`, `GREEN`, `MINT`, `LIGHT_BLUE`, `DARK_BLUE`, `PURPLE`, `AQUA`, `PINK`, `LIME`, `BROWN`, `AZURE`, `KHAKI`, `SAND`, `ORANGE`, `MARENGO`, `GRAY`, `GRAPHITE` |
| `width`    | optional | — | — | int (default `100`) |
| `height`   | optional | — | — | int (default `100`) |
| `extranet` | optional | optional | optional | enum: `Y` (allowed), `N` (denied); default `N` |

Common across all three: `context`, `role`, `extranet` (handled by `AbstractPlacementOptions`).
Common to TEXTAREA + SIDEBAR: `+iconName`, `+color`.
Unique to TEXTAREA: `+width`, `+height`.

### Design decisions

- **Class with `public const`, not enum, for `PlacementLocationCodes`** — consistent with
  legacy `Placement\Service\PlacementLocationCode`; placement codes are an open set;
  `bind(string $placementCode)` accepts strings directly without `->value`.
- **String-backed enums for `ChatContext`, `Role`, `ExtranetAvailability`, `PlacementColor`** —
  closed sets, modern PHP 8.1+ idiom already used in v3 SDK (`SysPageType`, `DealSemanticStage`).
- **Abstract base class `AbstractPlacementOptions`** holds the three common fluent setters
  (`context(...)`, `role(...)`, `extranet(...)`) plus the `$fields` array and `build()`.
- **Interface method `build(): array`** matches existing `ItemBuilderInterface` convention
  in the SDK.
- **Flat layout** under `Placements/` — 10 files, no `Enum/` subfolder. Mirrors how
  `src/Services/CRM/Deal/` keeps enums next to other classes.
- **`Placement::bind()` widening** is backwards-compatible — existing array call sites keep
  working untouched. When `PlacementOptionsInterface` is passed, `build()` is called once
  to produce the array before sending to the REST API.
- **Class name `IconName` is plain string**, no value object. Font Awesome class names are
  an open set; validation would be brittle. Pass through as-is.
- **Multi-value `context`** — fluent setter accepts variadic enum cases:
  `->context(ChatContext::User, ChatContext::Chat)`; values joined with `;` in `build()`.
- **Width/height** — no validation. Docs only mention default `100`; SDK passes the
  number through.

### Out of scope

- Integration tests for the new fluent path. The existing
  `tests/Integration/Services/Placement/Service/PlacementTest.php` already covers
  `bind()` end-to-end with array options; widening the type does not change runtime
  behaviour for arrays. Unit tests cover the new `build()` output.
- `IM_NAVIGATION` / `IM_SMILES_SELECTOR` option builders. The first is out of issue
  scope; the second is deprecated.
- Changes to `Placement::unbind()` / `list()` / `get()` — they don't take options.
- Service registration changes in `IMServiceBuilder` — the new classes are
  data-only (interface, abstract, enums, builders).

---

## Files to Create

### 1. `src/Services/IM/Placements/PlacementOptionsInterface.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Placements;

interface PlacementOptionsInterface
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array;
}
```

### 2. `src/Services/IM/Placements/AbstractPlacementOptions.php`

Abstract class implementing `PlacementOptionsInterface`. Holds the `$fields` array,
provides `build()`, and exposes the three common fluent setters: `context(...)`,
`role(...)`, `extranet(...)`. Concrete subclasses extend this.

```php
abstract class AbstractPlacementOptions implements PlacementOptionsInterface
{
    /** @var array<string, mixed> */
    protected array $fields = [];

    public function context(ChatContext ...$contexts): static
    {
        $this->fields['context'] = implode(';', array_map(static fn(ChatContext $c) => $c->value, $contexts));
        return $this;
    }

    public function role(Role $role): static
    {
        $this->fields['role'] = $role->value;
        return $this;
    }

    public function extranet(ExtranetAvailability $availability): static
    {
        $this->fields['extranet'] = $availability->value;
        return $this;
    }

    public function build(): array
    {
        return $this->fields;
    }
}
```

### 3. `src/Services/IM/Placements/ChatContext.php`

```php
enum ChatContext: string
{
    case User = 'USER';
    case Chat = 'CHAT';
    case Lines = 'LINES';
    case Crm = 'CRM';
    case All = 'ALL';
}
```

### 4. `src/Services/IM/Placements/Role.php`

```php
enum Role: string
{
    case User = 'USER';
    case Admin = 'ADMIN';
}
```

### 5. `src/Services/IM/Placements/ExtranetAvailability.php`

```php
enum ExtranetAvailability: string
{
    case Allowed = 'Y';
    case Denied = 'N';
}
```

### 6. `src/Services/IM/Placements/PlacementColor.php`

Eighteen cases corresponding to the documented color palette
(`RED`, `GREEN`, `MINT`, `LIGHT_BLUE`, `DARK_BLUE`, `PURPLE`, `AQUA`, `PINK`,
`LIME`, `BROWN`, `AZURE`, `KHAKI`, `SAND`, `ORANGE`, `MARENGO`, `GRAY`, `GRAPHITE`).

### 7. `src/Services/IM/Placements/TextareaPlacementOptions.php`

```php
final class TextareaPlacementOptions extends AbstractPlacementOptions
{
    public function __construct(string $iconName)
    {
        $this->fields['iconName'] = $iconName;
    }

    public function color(PlacementColor $color): self
    {
        $this->fields['color'] = $color->value;
        return $this;
    }

    public function width(int $width): self
    {
        $this->fields['width'] = $width;
        return $this;
    }

    public function height(int $height): self
    {
        $this->fields['height'] = $height;
        return $this;
    }
}
```

`iconName` is required → constructor parameter. Other setters are fluent.

### 8. `src/Services/IM/Placements/SidebarPlacementOptions.php`

Same pattern as TEXTAREA but without `width()` / `height()`. Constructor takes
required `iconName`. Adds fluent `color()`.

### 9. `src/Services/IM/Placements/ContextMenuPlacementOptions.php`

```php
final class ContextMenuPlacementOptions extends AbstractPlacementOptions
{
}
```

No additional fields — exists only to provide a typed marker for context-menu
placements. All setters come from the abstract base.

### 10. Tests under `tests/Unit/Services/IM/Placements/`

- `TextareaPlacementOptionsTest.php`
- `SidebarPlacementOptionsTest.php`
- `ContextMenuPlacementOptionsTest.php`

Each test verifies:
- Default `build()` after constructor returns the expected minimal array.
- Each fluent setter mutates `build()` output correctly.
- Multi-value `context()` joins with `;` in deterministic order.
- Returned values match the underlying enum `->value` strings.

---

## Files to Move

### 1. `src/Services/IM/PlacementLocationCodes.php` → `src/Services/IM/Placements/PlacementLocationCodes.php`

Update namespace from `Bitrix24\SDK\Services\IM` to `Bitrix24\SDK\Services\IM\Placements`.

---

## Files to Modify

### 1. `src/Services/Placement/Service/Placement.php`

Widen the `$options` parameter on `bind()` from `array` to
`PlacementOptionsInterface|array`. Inside the body, normalise to array before
passing to the API:

```php
public function bind(
    string $placementCode,
    string $handlerUrl,
    array $lang,
    PlacementOptionsInterface|array $options = [],
    ?int $b24UserId = null,
): PlacementBindResult {
    if ($options instanceof PlacementOptionsInterface) {
        $options = $options->build();
    }
    // … unchanged body
}
```

This is **backwards compatible**: every existing `bind(..., array $options)` call
keeps working (PHPStan will not flag it because `array` is one of the accepted
types).

### 2. `CHANGELOG.md`

Replace the existing #437 line under `### Added` with two lines covering the
extended scope:

```markdown
### Added

- Added `Bitrix24\SDK\Services\IM\Placements` namespace with `PlacementLocationCodes` (constants `IM_TEXTAREA`, `IM_SIDEBAR`, `IM_CONTEXT_MENU`, deprecated `IM_SMILES_SELECTOR`), `PlacementOptionsInterface`, `AbstractPlacementOptions`, fluent options builders `TextareaPlacementOptions`, `SidebarPlacementOptions`, `ContextMenuPlacementOptions`, and supporting enums `ChatContext`, `Role`, `ExtranetAvailability`, `PlacementColor` ([#437](https://github.com/bitrix24/b24phpsdk/issues/437))

### Changed

- `Placement::bind()` `$options` parameter widened from `array` to `PlacementOptionsInterface|array` for type-safe fluent IM placement configuration; existing array call sites are unaffected ([#437](https://github.com/bitrix24/b24phpsdk/issues/437))
```

---

## Deptrac compliance

All new code lives in `src/Services/IM/Placements/`, which Deptrac classifies as
the `Services` layer. The interface, abstract, enums, builders, and constants
class import only PHP built-ins (no SDK cross-layer imports). The only
cross-package import is `Placement\Service\Placement` referencing
`PlacementOptionsInterface` — both are inside `Services`, so no new violations.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

No integration suite required — see Out of scope above.

---

## Acceptance-criteria traceability

| Issue criterion | Addressed by |
|---|---|
| `PlacementLocationCode` exposes the four constants with a short inline comment describing each placement | `Placements\PlacementLocationCodes` with 4 `public const` entries, each commented |
| `IM_SMILES_SELECTOR` marked `@deprecated` pointing at the `im 25.1600.0` removal note | PHPDoc block on the constant referencing `im 25.1600.0` |
| `CHANGELOG.md` entry under `## 3.2.0 – UNRELEASED` → `### Added` with link to #437 | One line under `### Added`, plus a `### Changed` line for the bind() signature widening |
| `make lint-all` passes | Verified by the Verification section (cs-fixer, rector, phpstan, deptrac, test-unit are the components of `lint-all`) |
