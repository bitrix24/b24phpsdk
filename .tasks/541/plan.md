# Plan: Shipping new SDK release 3.4.0 (issue #541)

## Context

Issue [#541](https://github.com/bitrix24/b24phpsdk/issues/541) is a release-engineering
checklist for shipping SDK version **3.4.0** (the 3.x line → base branch `v3-dev`).
No REST API methods are involved, so the API-documentation research step and the
brainstorming step are intentionally skipped: the issue body is a fixed checklist with
no design space.

Issue checklist state at start of work:

- [ ] write release notes documentation in the changelog.MD — the `## 3.4.0` section was
  already tidied against milestone 3.4.0 (deduplicated Booking entry that shipped in 3.3.0,
  added missing entry for PR #532, expanded the Mail #516 entry); the tidy-up is sitting
  uncommitted in the working tree and will be committed in this branch. Remaining work:
  replace the `UNRELEASED` marker with the release date.
- [ ] update version in all code examples in main README.md — the only 3.x version
  reference is the composer constraint on line 50.
- [ ] update the version in headers in `/src/Core/ApiClient.php` — `SDK_VERSION` const on
  line 40 (used for `User-Agent` and `X-BITRIX24-PHP-SDK-VERSION` headers).
- [ ] local pass phpstan linter
- [ ] local pass rector linter
- [ ] local pass PHPUnit tests
- [x] pass all integration tests by scope — already checked off in the issue.

No test pins the SDK version string, so bumping `SDK_VERSION` cannot break the unit suite.

---

## Files to Create

None (only this plan file).

---

## Files to Modify

### 1. `CHANGELOG.md`

- Change heading `## 3.4.0 – UNRELEASED` → `## 3.4.0 - 2026.07.19`
  (date style follows the existing `## 3.0.0 - 2026.02.27` entry).
- Commit the already-prepared 3.4.0 section tidy-up (currently uncommitted).

### 2. `README.md`

- Line 50: `composer require bitrix24/b24phpsdk:"^3.3"` → `composer require bitrix24/b24phpsdk:"^3.4"`.
- Line 44 (`^1.0`) belongs to the 1.x line and stays unchanged.

### 3. `src/Core/ApiClient.php`

- Line 40: `protected const string SDK_VERSION = '3.3.0';` → `'3.4.0'`.

---

## Out of scope

- `docs/open-api/openapi.json` — modified as a side effect of the mandatory
  `make oa-schema-build` skill step; not part of the release checklist, left uncommitted.
- Untracked `.ai/` and `docs/open-api/v3-uncovered-methods.md` — not part of the release.
- Creating the git tag / GitHub release — not in the issue checklist; done by the
  maintainer after the PR merges.

---

## Deptrac compliance

No imports are added or changed; the change set is version strings and documentation.
No new layer violations are possible.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Integration tests by scope are already marked as passed in the issue and are not re-run.

After the quality gate is green: commit, push `feature/541-shipping-release-3-4-0`,
open a PR against `v3-dev` using `.github/PULL_REQUEST_TEMPLATE.md`, milestone `3.4.0`,
with `Closes #541`.
