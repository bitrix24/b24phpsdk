# Plan: [Shipping new SDK release]: 3.6.0 (issue #627)

## Context

Issue [#627](https://github.com/bitrix24/b24phpsdk/issues/627) is a release-engineering task
for SDK version **3.6.0**. Label: `enhancement in SDK`. Milestone is currently unset on the
issue; the repository has open milestone `3.6.0` (number 19, due 2026-09-01, 25 closed issues).

Version 3.6.0 belongs to the **v3** release line (the v1 line is numbered 1.x), so the base
branch is `v3-dev`. Branch `feature/627-ship-3-6-0` was created from local `v3-dev` at commit
`1f7bf0b4`.

`make oa-schema-build` was run before planning, as the maintainer skill requires. The resulting
`docs/open-api/openapi.json` diff is unrelated to release preparation and is **excluded** from
this PR, following the precedent set by the 3.5.0 release plan (`.tasks/593/plan.md`).

### Release recipe precedent

The 3.5.0 release (issue #593, PR #594, merge `c3c9802f`) touched exactly these files:
`CHANGELOG.md`, `README.md`, `composer.json`, `src/Core/ApiClient.php`,
`tests/Unit/Core/ApiClientTest.php`, plus its own task plan. This plan follows the same shape.

### Carried commit

`1f7bf0b4` ("migrate @deprecated docblocks to `#[\Deprecated]` attribute (#595)") is already
committed on this branch and is **not** yet on `origin/v3-dev`. Per the maintainer's decision it
rides along in this PR rather than being pushed to `v3-dev` separately, so CI validates the
toolchain upgrade and the release together.

### Stale local toolchain (not a repository blocker)

PR #625 (issue [#595](https://github.com/bitrix24/b24phpsdk/issues/595)) raised the `require-dev`
constraints to `rector/rector: ^2.6.7` and `phpstan/phpstan: ^2.2.16`. The local container still
had `2.5.2` / `2.2.2` installed, so `composer validate` reported lock-file errors.

**Correction to the original plan**: `composer.lock` is listed in `.gitignore` (line 6), is not
tracked, and was deliberately removed from the repository in commit `429b62e8` ("Remove temporary
Composer lock from PHPStan fix (#494)"). CI workflows run `make composer-install` against a
checkout with no lock file, so they always resolve fresh from `composer.json`. There is therefore
no committed desynchronisation and nothing to ship — the mismatch existed only in this workstation's
container.

What the local `composer update rector/rector phpstan/phpstan --with-dependencies` achieved is
still required for this task: it moved the local toolchain to exactly the versions CI resolves,
so the quality gate below is verified against **Rector 2.6.7 / PHPStan 2.2.16** rather than the
stale 2.5.2 / 2.2.2 that every earlier gate run in this branch's history used.

`composer.lock` is consequently **not** part of the deliverable.

This also closes the loop on the 3.5.0 release note: `.tasks/593/plan.md` pinned Rector and
PHPStan precisely because Rector 2.6.x dropped `PHPUnitSetList::PHPUNIT_110`, and deferred the
upgrade to #595. PR #625 replaced the removed constant with `->withComposerBased(phpunit: true)`,
so the pin is no longer needed.

---

## Files to Create

### 1. `.tasks/627/plan.md`

This plan file.

---

## Files to Modify

### 1. `tests/Unit/Core/ApiClientTest.php` (TDD: write this first)

Line 251:

```php
self::assertSame('3.5.0', $headers['x-bitrix24-php-sdk-version'] ?? null);
```

becomes:

```php
self::assertSame('3.6.0', $headers['x-bitrix24-php-sdk-version'] ?? null);
```

Line 253:

```php
'b24-php-sdk-vendor-v-3.5.0-php-',
```

becomes:

```php
'b24-php-sdk-vendor-v-3.6.0-php-',
```

This is the RED step: the test must fail against the current `SDK_VERSION` before step 2.

### 2. `src/Core/ApiClient.php`

Line 40:

```php
protected const string SDK_VERSION = '3.5.0';
```

becomes:

```php
protected const string SDK_VERSION = '3.6.0';
```

This is the GREEN step. The constant feeds both the `User-Agent` and the
`X-BITRIX24-PHP-SDK-VERSION` request headers (lines 71 and 73).

### 3. `README.md`

Line 50:

```bash
composer require bitrix24/b24phpsdk:"^3.5"
```

becomes:

```bash
composer require bitrix24/b24phpsdk:"^3.6"
```

The v1 installation example on line 44 (`^1.0`) stays unchanged.

### 4. `CHANGELOG.md`

Fold the whole `## Unreleased` section into `## 3.6.0` and leave `## Unreleased` empty at the
top, matching what the 3.5.0 ship commit did.

Current structure:

| Line | Heading |
|---|---|
| 3 | `## Unreleased` |
| 5 | `### Added` |
| 14 | `### Changed` |
| 29 | `### Fixed` |
| 33 | `## 3.6.0` |
| 35 | `### Added` |
| 75 | `### Fixed` |
| 80 | `## 3.5.0` |

Target structure:

| Heading | Content |
|---|---|
| `## Unreleased` | empty |
| `## 3.6.0` | `### Added` — Unreleased's Added entries appended to 3.6.0's existing Added list |
| | `### Changed` — Unreleased's Changed entries, placed between Added and Fixed |
| | `### Fixed` — Unreleased's Fixed entries appended to 3.6.0's existing Fixed list |
| `## 3.5.0` | unchanged |

Subsection order inside a release is `Added` → `Changed` → `Fixed`, matching `## 3.4.0`.

While folding, remove the two stray blank lines that the #576–#580 and #533 merges left
**inside** the Unreleased list bodies: source line 10 (splits the `### Added` list) and source
line 26 (splits the `### Changed` list). Lines 13 and 28 are blank separators before the next
`###` heading and must stay.

The `### Changed` entry for #533 begins with the word "Added" but is kept under `Changed`, where
its author placed it: it changes existing service signatures across the SDK rather than only
adding new surface. No entry is re-categorised by this task.

No entry text is rewritten and no entry is dropped — folding is a move operation only.

---

## Deptrac compliance

No new classes, namespaces, or imports are introduced. `src/Core/ApiClient.php` changes one
string constant, so the `Core` layer gains no dependency. Deptrac violation count must stay at
0 with 22 pre-existing skipped violations.

---

## Verification

Phase 1 — light checks, run after the local toolchain upgrade so they execute against Rector
2.6.7 and PHPStan 2.2.16 (the versions CI resolves):

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Expected: cs-fixer 0 files; PHPStan no errors; Rector done; deptrac 0 violations; `make test-unit` green. `make test-unit` currently reports 11 deprecations from the
SDK's own use of `PlacementLocationCodes::IM_SMILES_SELECTOR` — expected after `1f7bf0b4`, and
not a failure because `failOnDeprecation` is not enabled in `phpunit.xml.dist`.

Phase 2 — integration tests: **not applicable**. This task changes a version constant, a README
line, and changelog prose; no service behaviour or API surface is touched.

---

## Out of scope

- `docs/open-api/openapi.json` — regenerated by the mandatory `make oa-schema-build`, unrelated
  to the release, excluded from the commit.
- `composer.lock` — gitignored and untracked in this repository; the local upgrade is a
  workstation-only step, see the Context section.
- `.mcp.json`, `.tasks/577/plan.md`, `.tasks/578/` — pre-existing local working-tree changes,
  not touched.
- Tagging and publishing the release on GitHub — this task prepares the branch and the PR only.

---

## Follow-up

Set milestone `3.6.0` (number 19) on issue #627 — it is currently unset, while the analogous
3.5.0 issue #593 carried milestone `3.5.0`.
