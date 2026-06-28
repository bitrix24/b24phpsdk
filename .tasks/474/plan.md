# Plan: Fix broken /examples/* references in README (issue #474)

## Context

Issue #474 reports that `README.md` tells the reader to "Go to `/examples/webhook` folder" and
"Go to `/examples/local-app` folder", but no `examples/` directory exists in the repository, so the
instructions cannot be followed.

Findings:
- `README.md:146` — `1. Go to `/examples/webhook` folder`
- `README.md:181` — `1. Go to `/examples/local-app` folder`
- No `examples/` directory exists.
- A complete, runnable local-application reference already exists in `tests/ApplicationBridge/`
  (`index.php`, `install.php`, token storage, credentials provider).

Decision (brainstormed, option B): documentation-only fix — make the README instructions
self-contained instead of pointing to non-existent folders. Do NOT create new `examples/` dirs.

Base branch: `v3-dev` (milestone 3.3.0; examples use the v3 `ServiceBuilderFactory` API).

## Files to Modify

### 1. `README.md` — "Work with webhook" section (~144-177)
- Replace step "Go to `/examples/webhook` folder" + `composer install` with installing the SDK via
  `composer require bitrix24/b24phpsdk`.
- Renumber the remaining steps; keep the existing inline code snippet (add a `<?php` opening tag so
  the file is runnable). Run step becomes `php -f webhook-example.php`.

### 2. `README.md` — "Work with local application" section (~179-253)
- Replace step "Go to `/examples/local-app` folder" + `composer install` with installing the SDK via
  `composer require bitrix24/b24phpsdk`.
- Add a sentence pointing to `tests/ApplicationBridge/` as a complete runnable reference
  implementation.
- Keep the rest of the section unchanged (ngrok, creating the local app, the `index.php` snippet).

### 3. `CHANGELOG.md` (under `## 3.3.0 – UNRELEASED` → `### Changed`)
- `- Fixed README "Examples" section that pointed to non-existent `/examples/webhook` and
  `/examples/local-app` folders; instructions are now self-contained and reference
  `tests/ApplicationBridge` for the local application ([#474](https://github.com/bitrix24/b24phpsdk/issues/474))`
  (place under `### Fixed`).

## Out of scope
- No new `examples/` directories or example code files.
- No source code changes.

## Verification
- `grep -n "/examples/" README.md` returns nothing.
- Manual read-through of both sections for correct numbering and valid fenced code blocks.
- (No code touched → unit/lint suites unaffected; documentation change only.)
