# Plan: Add SDK Versions Section and Update README

## Context

Bitrix24 released a new REST API version with breaking changes in endpoints and data structures.
Issue #317 describes a parallel versioning strategy (v1 and v3) rather than an in-place upgrade.
The README currently has only a 2-line "Branch status" section that does not adequately explain
the differences between versions. The goal is to make the README current: clearly communicate
both SDK versions and help developers choose the right one.

Branch: `feature/352-ship30`

---

## File to Modify

`README.md` (project root)

---

## Changes

### 1. New "SDK Versions" section at the top of the file

Insert **between line 5 (after badges) and the "Build status" section**:

- A brief explanation that two major versions coexist
- Version comparison table (from issue #317):

| | v1 (`main` branch) | v3 (`v3` branch) |
|---|---|---|
| **PHP** | 8.2, 8.3, 8.4 | 8.4, 8.5 |
| **API endpoints** | `{portal}/rest/{user_id}/{token}/{method}` | `{portal}/rest/api/{user_id}/{token}/{method}` |
| **New REST methods** | — | ✅ |
| **Breaking changes** | No | ✅ |
| **Semver** | `1.*` | `3.*` |
| **Status** | Stable / production-ready | Active development |

- "Which version to choose?" block:
  - v1 → PHP 8.2–8.4 projects, production use, no need for the newest API methods
  - v3 → PHP 8.4+ projects, need new API methods, comfortable with breaking changes

### 2. Update the "Installation" section

The current section shows only `composer require bitrix24/b24phpsdk` (installs v1)
and the example `"bitrix24/b24phpsdk": "1.9.*"`.

Add explicit install commands for both versions:
```bash
# Stable v1 (PHP 8.2+)
composer require bitrix24/b24phpsdk:"^1.0"

# New v3 (PHP 8.4+, breaking changes)
composer require bitrix24/b24phpsdk:"^3.0"
```

### 3. Expand the "Branch status" section

Current 2 lines → full description of all 4 branches from issue #317:
- `main` → stable v1.x production releases
- `dev` → v1.x integration and pre-release testing
- `v3` → stable v3.x production releases
- `v3-dev` → active v3 development with breaking changes

Rule: "Each major version has its own dev branch; cross-version changes use cherry-pick, never merge."

### 4. Minor accuracy fixes

- CI badge table header: currently says `master`, should be `main`
- Integration tests section — remove stale make targets (`test-integration-core`,
  `test-integration-scope-telephony`, `test-integration-scope-workflows`, `test-integration-scope-user`)
  that no longer exist in the current Makefile
- `.env.local` example in the Integration tests section — align with `docs/testing.md` format:
  remove `APP_ENV=dev`, fix `INTEGRATION_TEST_LOG_LEVEL` value (100 = DEBUG, not 500)

---

## Verification

After editing:
1. Read the file — confirm that Markdown tables and code blocks render correctly
2. Verify the versions section appears above "Build status"
3. Confirm install commands for both versions are present
4. Confirm no stale make targets remain in the integration tests section
