# Plan: [Bug in SDK]: Symfony dependency update breaks PHPStan class loading in SDK (issue #494)

## Context

Issue #494 tracks a quality-gate failure after Symfony dependency updates.
The confirmed root cause from the issue comments is:

- `composer.lock` is now committed as a temporary mitigation, so local CI/dev installs stay on Symfony `8.0.x`.
- Symfony `8.1.x` uses PHP 8.4 property hooks in classes referenced by the SDK, such as `Symfony\Component\HttpFoundation\Request`.
- PHPStan `1.x` cannot parse those syntax forms and reports affected Symfony classes as `class.notFound`.
- A permanent fix requires upgrading `phpstan/phpstan` and `rector/rector` to `^2`, then resolving the new static-analysis findings produced by the stricter toolchain.

The repository-required OpenAPI refresh was completed with:

```bash
make oa-schema-build
```

This issue does not add or change Bitrix24 REST API methods, so no `mcp__bitrix24__bitrix-method-details` lookup is applicable.

The baseline before this plan:

```bash
docker compose run --rm php-cli vendor/bin/phpstan --version
# PHPStan - PHP Static Analysis Tool 1.12.33

docker compose run --rm php-cli vendor/bin/rector --version
# Rector 1.2.10

make lint-phpstan
# [OK] No errors
```

---

## Files to Create

No SDK source files or service/result-item test files are expected for this issue.

---

## Files to Modify

### 1. `composer.json`

Update dev tool constraints:

```json
"phpstan/phpstan": "^2",
"rector/rector": "^2"
```

Keep runtime Symfony constraints unchanged (`^7||^8`) because this is a library and consumers should remain able to install compatible Symfony versions.

### 2. `composer.lock`

Regenerate the lock file after updating the dev tool constraints.

The lock should move PHPStan and Rector to `2.x` and should no longer rely on the temporary Symfony `8.0.x` lock as the only way to keep static analysis green.

### 3. `phpstan.neon.dist`

Fix only findings required by PHPStan `2.x`.

Preferred order:

1. Fix real type issues in source/tests.
2. Add narrow typed annotations where PHPStan lacks enough context.
3. Add a baseline/ignore entry only if the finding is a known tool limitation and the ignore is specific enough to avoid hiding unrelated regressions.

### 4. `rector.php`

Adjust configuration only if Rector `2.x` requires it.

Do not broaden Rector rules or perform unrelated modernization.

### 5. `CHANGELOG.md`

Add an entry under `## X.Y.Z Unreleased` -> `### Fixed`:

```markdown
- Fixed PHPStan class loading after Symfony dependency updates by upgrading the static-analysis toolchain ([#494](https://github.com/bitrix24/b24phpsdk/issues/494))
```

---

## TDD / Regression Gate

The regression test for this bug is the static-analysis quality gate itself.

1. Upgrade PHPStan/Rector constraints and lock file.
2. Run the PHPStan `2.x` gate and confirm it fails before fixes:

```bash
make lint-phpstan
```

Expected first result after the tool upgrade: fail with PHPStan `2.x` findings, not Symfony `class.notFound` parser failures.

3. Fix PHPStan findings in small batches.
4. Run `make lint-phpstan` after each batch until it passes.
5. Run Rector dry-run:

```bash
make lint-rector
```

Expected first result after the tool upgrade: either pass or fail with Rector `2.x` upgrade/config/code findings.

6. Fix Rector findings in small batches and rerun until it passes.

---

## Deptrac compliance

No production architecture or service dependencies are expected to change.

If any source-code fixes are needed for PHPStan `2.x`, they must preserve existing layer boundaries and be verified with:

```bash
make lint-deptrac
```

---

## Verification

Run these gates before reporting the issue as done:

```bash
make lint-phpstan
make lint-rector
make lint-cs-fixer
make lint-deptrac
make test-unit
composer validate --strict
```

If dependency updates change generated autoload metadata or lock validation behavior, rerun:

```bash
make composer-dumpautoload
```

and repeat the affected gates.
