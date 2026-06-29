# Plan: Shipping new SDK release 3.3.0 (issue #510)

## Context

Issue #510 is a release housekeeping task for the v3 line (milestone `3.3.0`, base branch
`v3-dev`). It is not a feature/bugfix — no new SDK service code, no OpenAPI generation, so
`make oa-schema-build` is intentionally skipped (it would only add an unrelated schema
snapshot diff to the release commit).

The `CHANGELOG.md` already contains a populated `## 3.3.0 – UNRELEASED` section produced by
commit `025f0ad` (the 3.2.0 release). Milestone 3.3.0 closed issues #386, #469, #473, #474,
#484 — all are represented in the section, **except** the Booking entry (line 12) which is
missing its trailing `([#473](...))` issue link required by the CHANGELOG convention.

Release procedure mirrors the previous release commit `025f0ad` (bump to 3.2.0):

1. `CHANGELOG.md`: finalize `## 3.3.0 – UNRELEASED` → `## 3.3.0` (no date — matches the
   3.1.0/3.2.0 v3 precedent, confirmed with the user), and open a fresh empty
   `## 3.4.0 – UNRELEASED` section above it.
2. `README.md`: bump the v3 install constraint `^3.2` → `^3.3`.
3. `src/Core/ApiClient.php`: bump `SDK_VERSION` `3.2.0` → `3.3.0`.

Release ships via a dedicated branch + PR into `v3-dev` (precedent: `feature/210-ship-new-release`
→ PR #212). Tagging / merge to `main` is a separate manual maintainer step outside this issue.

---

## Files to Modify

### 1. `CHANGELOG.md`

- Add the missing issue link to the Booking entry (currently line 12): append
  ` ([#473](https://github.com/bitrix24/b24phpsdk/issues/473))` before the closing period /
  at the end of the entry.
- Change the top header from:
  ```
  ## 3.3.0 – UNRELEASED
  ```
  to a new empty next-version section followed by the finalized 3.3.0 header:
  ```
  ## 3.4.0 – UNRELEASED

  ### Added

  ### Changed

  ### Fixed

  ## 3.3.0
  ```
  (i.e. insert the new `## 3.4.0 – UNRELEASED` block above, and drop `– UNRELEASED` from the
  3.3.0 header — no date.)
- Append a `### Statistics` block at the end of the 3.3.0 section (after `### Fixed`, just
  before `## 3.2.0`), by analogy with the 1.x release blocks (e.g. line 755 for 1.8.0).
  Numbers generated via the Makefile coverage tool `make sdk-coverage-v1-show`
  (`b24-dev:show-sdk-coverage-statistics`), which reports total SDK coverage across all
  `ApiEndpointMetadata`-annotated services vs the live Bitrix24 method list:
  ```
  ### Statistics

  ```
  Bitrix24 API-methods count: 1171
  Supported in bitrix24-php-sdk methods count: 978
  Coverage percentage: 83.52% 🚀
  Supported in bitrix24-php-sdk methods with batch wrapper count: 124
  ```
  ```
  (The v3-snapshot tool `make sdk-coverage-v3-show` reports 11.69% against the partial
  `openapi.json` snapshot of 154 methods — a narrow dev metric, not the historical headline
  format, so it is not used for the release `### Statistics` block.)

### 2. `README.md` (line 50)

```diff
-composer require bitrix24/b24phpsdk:"^3.2"
+composer require bitrix24/b24phpsdk:"^3.3"
```

This is the only version-bearing code example in README that references the v3 minor line.
(`README.md:188` / `:149` are generic `composer require bitrix24/b24phpsdk` without a
version and stay unchanged.)

### 3. `src/Core/ApiClient.php` (line 40)

```diff
-    protected const string SDK_VERSION = '3.2.0';
+    protected const string SDK_VERSION = '3.3.0';
```

`SDK_USER_AGENT` stays `b24-php-sdk-vendor`; the version is interpolated from `SDK_VERSION`
into the `User-Agent` and `X-BITRIX24-PHP-SDK-VERSION` headers (lines 71/73), so no further
edits are needed there.

---

## Deptrac compliance

No `use` imports or class dependencies change — only a constant string literal, a README
example, and CHANGELOG prose. No new deptrac violations possible.

---

## Verification

Quality gate (issue checklist: phpstan, rector, PHPUnit, integration by scope):

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Integration tests run by scope on a live portal per the issue checklist
("pass all integration tests by scope"). These require `tests/.env.local` with a valid
webhook and are run/confirmed by the maintainer; the release commit changes no runtime
behaviour (only a version string), so unit + linters are the gating local checks.

---

## PR

- Base: `v3-dev` (never `main`).
- Title: `Shipping new SDK release: 3.3.0` (≤72 chars).
- Body from `.github/PULL_REQUEST_TEMPLATE.md` + quality-gate checklist + `Closes #510`.
- Milestone: 3.3.0 (#16). Assignee: most recent commit author.
