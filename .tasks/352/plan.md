# Plan: Sort `Added` block in CHANGELOG.md for release 3.0.0

## Context

The `### Added` section of release `3.0.0` contains mixed entries: core REST v3 infrastructure,
Task and EventLog services (the centrepiece of API 3.0 support), and all other new services added
in this release. The goal is to visually separate these two areas so readers immediately understand
what is related to API v3/Tasks/EventLog and what is unrelated.

There is also a typo duplicate on line 279 (`= Added support for Bitrix24 API v3` uses `=` instead
of `-`) that must be removed.

Branch: `feature/352-ship30`

---

## File to change

`CHANGELOG.md` — only the `### Added` block inside `## 3.0.0 - 2026.01.01`

---

## New order for `### Added`

### Group 1 — API v3 support: Tasks & EventLog (13 entries)

Logical order: core v3 infrastructure → Tasks (uses that infrastructure) → EventLog.

| # | Entry | Current location |
|---|---|---|
| 1 | `Added support for Bitrix24 API v3` | line 191 |
| 2 | `Added REST 3.0 API version support` (`ApiVersion` enum, `EndpointUrlFormatter`) | lines 301-303 |
| 3 | `Switched Task domain methods to Bitrix24 API v3` (task.task.*, TaskChat, TaskFile, Documentation) | lines 192-196 |
| 4 | `Added type-safe filter builder system for REST 3.0 filtering` (TaskFilter, FilterBuilderInterface, …) | lines 280-291 |
| 5 | `Added select builder infrastructure for type-safe field selection` (TaskItemSelectBuilder) | lines 297-300 |
| 6 | `Added comprehensive filter documentation` | lines 304-305 |
| 7 | `Added OpenAPI schema infrastructure` (Documentation service, `b24-dev:build-schema` command) | lines 292-296 |
| 8 | `Added Core\Contracts\SortOrder enum` (used by EventLog tail cursor) | lines 27-28 |
| 9 | `Added service Services\Main\Service\EventLog` (get / list / tail) | lines 29-37 |
| 10 | `Added Services\Main\Service\EventLogSelectBuilder` | line 38 |
| 11 | `Added Services\Main\Service\EventLogFilter` | lines 39-40 |
| 12 | `Added Services\Main\Service\EventLogTailCursor` | lines 41-42 |
| 13 | `Typed EventLogItemResult::$remoteAddr as Darsyn\IP\Version\Multi\|null` | lines 16-20 |

### Group 2 — Everything else (25 entries)

Order: tooling → general utilities → legacy → application services (alphabetical/logical).

| # | Entry | Current location |
|---|---|---|
| 1 | `Added deptrac/deptrac` dev dependency | lines 10-14 |
| 2 | `Added Services\AbstractSelectBuilder::allSystemFields()` | lines 22-25 |
| 3 | `Added src/Legacy/ namespace` with LegacyServiceBuilder and LegacyTaskServiceBuilder | lines 44-48 |
| 4 | `Added OpenApi\Domain\OpenApiSchemaReader` | lines 50-51 |
| 5 | `Added service Services\Lists\Lists\Service\Lists` | lines 53-58 |
| 6 | `Added service Services\Lists\Field\Service\Field` | lines 59-69 |
| 7 | `Added service Services\Lists\Section\Service\Section` | lines 70-75 |
| 8 | `Added service Services\Lists\Element\Service\Element` | lines 76-82 |
| 9 | `Added service Services\Landing\Site\Service\Site` | lines 83-105 |
| 10 | `Added service Services\Landing\SysPage\Service\SysPage` | lines 106-109 |
| 11 | `Added service Services\Landing\Role\Service\Role` | lines 110-119 |
| 12 | `Added service Services\Landing\Page\Service\Page` | lines 121-149 |
| 13 | `Added service Services\Landing\Block\Service\Block` | lines 151-169 |
| 14 | `Added service Services\Landing\Template\Service\Template` | lines 171-176 |
| 15 | `Added service Services\Landing\Repo\Service\Repo` | lines 178-183 |
| 16 | `Added service Services\Landing\Demos\Service\Demos` | lines 184-189 |
| 17 | `Added service Services\IMOpenLines\Connector\Service\Connector` | lines 197-209 |
| 18 | `Added service Services\IMOpenLines\Config\Service\Config` | lines 211-220 |
| 19 | `Added service Services\IMOpenLines\CRMChat\Service\Chat` | lines 221-225 |
| 20 | `Added service Services\IMOpenLines\Message\Service\Message` | lines 227-231 |
| 21 | `Added service Services\IMOpenLines\Bot\Service\Bot` | lines 232-238 |
| 22 | `Added service Services\IMOpenLines\Operator\Service\Operator` | lines 239-245 |
| 23 | `Added service Services\IMOpenLines\Session\Service\Session` | lines 247-259 |
| 24 | `Added service Services\SonetGroup\Service\SonetGroup` | lines 262-273 |
| 25 | `Added isPartner(): bool method to ContactPersonInterface` | lines 274-278 |

---

## Cleanup

Remove line 279: `= Added support for Bitrix24 API v3`
- Uses `=` instead of `-` — invalid markdown list item
- Is a duplicate of the entry already present at line 191

---

## Verification

After editing:
- Total entries in `### Added`: 38 (13 in group 1 + 25 in group 2)
- No duplicate lines
- All bullet points use `-` (not `=`)
- Markdown structure is valid
