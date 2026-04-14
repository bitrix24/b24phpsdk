# Remove cebe/php-openapi Dependency Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Remove the unused `cebe/php-openapi` package from `composer.json` to reduce the runtime dependency surface for SDK consumers.

**Architecture:** Single-file change to `composer.json` followed by a `composer remove` to regenerate `composer.lock`. No PHP source files, test files, or configuration files require modification because zero code in `src/` or `tools/` imports anything from the `cebe\openapi` namespace.

**Tech Stack:** Composer, Docker (via Makefile targets)

---

### Task 1: Remove cebe/php-openapi from composer.json

**Files:**
- Modify: `composer.json` (line 32 — `"cebe/php-openapi": "^1.8",`)

**Step 1: Remove the dependency line**

In `composer.json`, delete this line from the `require` section:

```json
"cebe/php-openapi": "^1.8",
```

The `require` block after the change must not contain any reference to `cebe`.

**Step 2: Run composer remove inside Docker to regenerate composer.lock**

```bash
docker compose run --rm php-cli composer remove cebe/php-openapi
```

Expected output: Composer confirms removal and updates `composer.lock`. No errors.

**Step 3: Verify cebe is gone from both files**

```bash
grep "cebe" composer.json composer.lock
```

Expected: only `composer.lock` entries under the `packages-dev` array (if `cebe/indent` is a transitive dev dep); `composer.json` must have zero matches.

Actually expected: `composer.json` zero matches. `composer.lock` should show no `cebe` entries at all after removal (it will be removed from the lockfile entirely).

**Step 4: Commit**

```bash
git add composer.json composer.lock
git commit -m "Remove unused cebe/php-openapi dependency (#418)"
```

---

### Task 2: Update CHANGELOG.md

**Files:**
- Modify: `CHANGELOG.md`

**Step 1: Add a Changed entry under `## 3.1.0 Unreleased`**

Locate the `## 3.1.0 Unreleased` section. Add a `### Changed` block (or append to it if it already exists) with:

```markdown
### Changed

- Remove unused `cebe/php-openapi` dependency from `require` ([#418](https://github.com/bitrix24/b24phpsdk/issues/418))
```

**Step 2: Commit**

```bash
git add CHANGELOG.md
git commit -m "Update CHANGELOG.md for #418"
```

---

### Task 3: Quality gate — Phase 1 (linters + unit tests)

Run each command in sequence. Do not proceed to the next if the current one fails.

**Step 1:**
```bash
make lint-allowed-licenses
```
Expected: exit 0, no license violations.

**Step 2:**
```bash
make lint-cs-fixer
```
Expected: exit 0, no style issues.

**Step 3:**
```bash
make lint-phpstan
```
Expected: exit 0, no static analysis errors.

**Step 4:**
```bash
make lint-rector
```
Expected: exit 0, no upgrade rule violations.

**Step 5:**
```bash
make lint-deptrac
```
Expected: exit 0, zero violations.

**Step 6:**
```bash
make test-unit
```
Expected: all unit tests pass, exit 0.

If any step fails, invoke `superpowers:systematic-debugging` before attempting a fix.

---

## Verification

All six commands in Task 3 must pass with exit 0 before creating a PR.
