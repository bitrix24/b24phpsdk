# Plan: Add IM placement codes to PlacementLocationCodes (issue #437)

## Context

Issue [#437](https://github.com/bitrix24/b24phpsdk/issues/437) asks to expose the four
IM widget placement codes so that consumers registering handlers via `placement.bind`
no longer hard-code the placement strings.

The four placements (scope `im`) and their documentation URLs:

| Code | Purpose | Docs |
|---|---|---|
| `IM_TEXTAREA` | Widget panel above the chat message input field | https://apidocs.bitrix24.com/api-reference/widgets/im/textarea.html |
| `IM_SIDEBAR` | Chat sidebar widget | https://apidocs.bitrix24.com/api-reference/widgets/im/sidebar.html |
| `IM_CONTEXT_MENU` | Context-menu item on a chat message | https://apidocs.bitrix24.com/api-reference/widgets/im/context-menu.html |
| `IM_SMILES_SELECTOR` | Smiles / Giphy selector — **no longer works since `im 25.1600.0`** | https://apidocs.bitrix24.com/api-reference/widgets/im/smile-selector.html |

Key findings from the Bitrix24 MCP article lookups (`bitrix24-article-details`):

- All four placements belong to scope `im`.
- `IM_SMILES_SELECTOR` was removed starting with module `im 25.1600.0` — smiles were
  replaced by stickers. It stays in the SDK as a constant for backward compatibility
  with older portals, but must be marked `@deprecated`.
- A fifth IM placement exists (`IM_NAVIGATION`, left chat menu) but is out of scope
  for this issue — it is not listed in the acceptance criteria. Not added here.

### Design decision: separate constants holder under the IM service folder

The issue body suggests adding the constants to the existing
`Bitrix24\SDK\Services\Placement\Service\PlacementLocationCode`. During
brainstorming the maintainer decided to introduce a **separate, IM-specific
constants holder** under the IM service folder instead. Rationale:

- Keeps IM placement codes grouped with other IM-specific code
  (`src/Services/IM/IMServiceBuilder.php`, `src/Services/IM/Notify/`).
- Leaves the legacy `Placement\Service\PlacementLocationCode` untouched — no
  churn for existing consumers.
- Lives at the root of the IM service folder (next to `IMServiceBuilder.php`),
  not inside a `Service/` subfolder, because it is a constants holder, not a
  service that calls the REST API.

Target file: `src/Services/IM/PlacementLocationCodes.php`
FQCN: `Bitrix24\SDK\Services\IM\PlacementLocationCodes`

The class name is **plural** (`PlacementLocationCodes`) — it is a holder of
multiple codes, not a single value object.

The issue body will **not** be rewritten on GitHub; the divergence is documented
here and the PR closes #437 with `Closes #437`.

### Design decision: class with `public const`, not enum

Considered making this a string-backed enum (modern PHP 8.1+ idiom, used elsewhere
in the project: `SysPageType`, `DealSemanticStage`, `Bitrix24AccountStatus`).
Rejected in favour of a plain `class` with `public const`:

- Consistency with the existing `Placement\Service\PlacementLocationCode` in the
  same domain — mixing two models for placement codes would be inconsistent.
- `Placement::bind(string $placementCode, ...)` already accepts a `string`;
  constants give zero call-site friction (`bind(PlacementLocationCodes::IM_TEXTAREA, ...)`),
  whereas an enum would require `->value` everywhere.
- Placement codes are an open set — the existing legacy class already exposes
  factory methods (`getForCrmDynamicListMenu(int $entityId): string`) for
  dynamic codes; enums cannot host factories that return new cases.
- `@deprecated` on a `public const` is standard and machine-readable by PHPStan
  and IDEs; per-case `@deprecated` on enum cases is not first-class in PHP.

### Out of scope

- No changes to `Placement\Service\Placement` service.
- No changes to `IMServiceBuilder` or any service registration — the new class is
  a plain constants holder.
- No new tests — the existing `Placement\Service\PlacementLocationCode` has no
  dedicated tests and constants do not need them.

---

## Files to Create

### 1. `src/Services/IM/PlacementLocationCodes.php`

```php
<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM;

/**
 * IM widget placement codes (scope `im`).
 *
 * @link https://apidocs.bitrix24.com/api-reference/widgets/im/index.html
 */
class PlacementLocationCodes
{
    // Widget panel above the chat message input field.
    // See https://apidocs.bitrix24.com/api-reference/widgets/im/textarea.html
    public const IM_TEXTAREA = 'IM_TEXTAREA';

    // Chat sidebar widget.
    // See https://apidocs.bitrix24.com/api-reference/widgets/im/sidebar.html
    public const IM_SIDEBAR = 'IM_SIDEBAR';

    // Context-menu item on a chat message ("Create content based on").
    // See https://apidocs.bitrix24.com/api-reference/widgets/im/context-menu.html
    public const IM_CONTEXT_MENU = 'IM_CONTEXT_MENU';

    /**
     * Smiles / Giphy selector pop-up.
     *
     * @deprecated No longer works since module `im 25.1600.0` — smiles were
     *             replaced by stickers. See
     *             https://apidocs.bitrix24.com/api-reference/widgets/im/smile-selector.html
     */
    public const IM_SMILES_SELECTOR = 'IM_SMILES_SELECTOR';
}
```

Notes:
- Short inline comments for the three active placements (satisfies issue AC
  "short inline comment describing each placement").
- `IM_SMILES_SELECTOR` uses a full PHPDoc block so the `@deprecated` tag is
  machine-readable by PHPStan / IDEs and references the `im 25.1600.0` removal
  note (satisfies issue AC).
- No base class / no interface — matches the existing
  `Placement\Service\PlacementLocationCode` style.
- Class is **non-`final`** to keep parity with the existing
  `Placement\Service\PlacementLocationCode`, which is a plain `class`.

---

## Files to Modify

### 1. `CHANGELOG.md`

Add one line under `## 3.2.0 – UNRELEASED` → `### Added` section
(the section does not yet exist on this branch — create it below the existing
`### Changed` block):

```markdown
### Added

- Added `Bitrix24\SDK\Services\IM\PlacementLocationCodes` with constants `IM_TEXTAREA`, `IM_SIDEBAR`, `IM_CONTEXT_MENU`, and `IM_SMILES_SELECTOR` (deprecated since `im 25.1600.0`) for IM widget placement codes ([#437](https://github.com/bitrix24/b24phpsdk/issues/437))
```

---

## Deptrac compliance

New class lives in the `Services` layer and imports nothing outside that layer
(it has no `use` statements at all). No new violations are introduced.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

No integration suite needs to run for this change — the new class is a plain
constants holder with no runtime behaviour and no REST API calls.

---

## Acceptance-criteria traceability

| Issue criterion | Addressed by |
|---|---|
| `PlacementLocationCode` exposes the four constants with a short inline comment describing each placement | New class `Bitrix24\SDK\Services\IM\PlacementLocationCodes` with 4 `public const` entries, each with an inline or PHPDoc comment |
| `IM_SMILES_SELECTOR` marked with `@deprecated` pointing at the `im 25.1600.0` removal note | PHPDoc block on the constant with `@deprecated No longer works since module im 25.1600.0…` |
| `CHANGELOG.md` entry under `## 3.2.0 – UNRELEASED` → `### Added` with a link to #437 | One new line added |
| `make lint-all` passes | Verified in the Verification section above (we run the four linters + unit tests that make up the light gate; `make lint-all` is a superset) |
