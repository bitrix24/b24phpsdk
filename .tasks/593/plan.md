# Plan: [Shipping new SDK release]: ship 3.5.0 (issue #593)

## Context

Issue [#593](https://github.com/bitrix24/b24phpsdk/issues/593) is a release-engineering
task for SDK version **3.5.0**. The issue has label `enhancement in SDK` and milestone
`3.5.0`.

The user narrowed the implementation scope to:

- update `src/Core/ApiClient.php` so SDK request headers report version `3.5.0`
- update the v3 installation example in `README.md` from `^3.4` to `^3.5`
- move the current changelog release notes under `## 3.5.0` and create a new top
  `## Unreleased` section
- refresh the working branch from current `v3-dev`
- pin the Rector/PHPStan dev-toolchain to the last verified stable combination so fresh
  CI installs keep the existing Rector configuration working

The API line is **v3**, so the work uses base branch `v3-dev`. Branch
`feature/593-ship-3-5-0` was rebased onto current `origin/v3-dev` at commit `4298b37`,
which includes merged PR #569. The main checkout has unrelated local changes and remains
untouched.

Mandatory `make oa-schema-build` was run before implementation as required by `AGENTS.md`,
but the generated OpenAPI diff is outside the narrowed user scope and will not be included
in this PR.

Fresh CI installs currently resolve `rector/rector 2.6.4`, where
`PHPUnitSetList::PHPUNIT_110` is no longer available. Pinning only Rector to `2.5.2`
still leaves `phpstan/phpstan 2.2.10`, which is incompatible with Rector 2.5.2 internals.
This PR therefore pins both `rector/rector` to `2.5.2` and `phpstan/phpstan` to `2.2.2`.
Upgrading Rector and PHPStan is tracked separately in
[#595](https://github.com/bitrix24/b24phpsdk/issues/595).

---

## Files to Create

### 1. `.tasks/593/plan.md`

This issue plan records the narrowed release-prep scope and verification commands.

---

## Files to Modify

### 1. `README.md`

Current line 50:

```bash
composer require bitrix24/b24phpsdk:"^3.4"
```

Change it to:

```bash
composer require bitrix24/b24phpsdk:"^3.5"
```

The v1 installation example on line 44 stays unchanged.

### 2. `src/Core/ApiClient.php`

Current line 40:

```php
protected const string SDK_VERSION = '3.4.0';
```

Change it to:

```php
protected const string SDK_VERSION = '3.5.0';
```

This updates both `User-Agent` and `X-BITRIX24-PHP-SDK-VERSION` request headers.

### 3. `CHANGELOG.md`

Change the current top section from:

```markdown
## Unreleased
```

to:

```markdown
## Unreleased

## 3.5.0
```

The existing `### Added` and `### Fixed` content remains under `## 3.5.0`.

### 4. `tests/Unit/Core/ApiClientTest.php`

Add or update a focused unit test that proves `ApiClient` sends SDK version `3.5.0` in
the default request headers. The test must fail before the `SDK_VERSION` change and pass
after it.

### 5. `composer.json`

Pin the current Rector-compatible static-analysis toolchain:

```json
"phpstan/phpstan": "2.2.2",
"rector/rector": "2.5.2"
```

This keeps `make lint-rector` green on fresh CI installs until issue #595 upgrades the
Rector configuration to the current stable API.

---

## Out of Scope

- Committing `docs/open-api/openapi.json`; it was refreshed for pre-work compliance only.
- Creating the git tag, GitHub Release, Packagist publication, or stable `v3` branch merge.
- Upgrading Rector to 2.6.x or changing `rector.php`; this is tracked in
  [#595](https://github.com/bitrix24/b24phpsdk/issues/595).
- Committing ignored local setup files such as `vendor/`, `composer.lock`, or
  `tests/.env.local`.

---

## Deptrac Compliance

The production change updates an existing constant and documentation text. The added unit
test stays inside the existing unit-test layer. No new production dependencies or layer
directions are introduced.

---

## Verification

Run the focused and required release-prep checks:

```bash
make test-file path=tests/Unit/Core/ApiClientTest.php
docker compose run --rm php-cli composer validate --strict
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

There is no new REST service scope in this issue, so no new scope-specific integration
suite is added.
