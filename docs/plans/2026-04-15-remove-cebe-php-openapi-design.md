# Design: Remove cebe/php-openapi dependency (issue #418)

## Problem

`cebe/php-openapi: ^1.8` is listed in the `require` section of `composer.json`, meaning it is installed
in production environments as a runtime dependency. However, the package is entirely unused:

- No PHP file in `src/` or `tools/` imports any class from the `cebe\openapi` namespace.
- All OpenAPI schema parsing in `src/OpenApi/Domain/` uses native `json_decode()`.
- `SchemaBuilder` command simply fetches the raw JSON payload from the Bitrix24 REST API and writes it to a file.
- No configuration file (`deptrac.yaml`, `phpstan.dist.neon`, `.php-cs-fixer.php`, `rector.php`) references the package.

## Decision

**Remove `cebe/php-openapi` entirely** from `composer.json` (not move to `require-dev`).

Rationale: the package is dead code at the dependency level. Moving it to `require-dev` would keep
installing an unused package for all contributors. Removal is the cleanest outcome and aligns with
YAGNI — if cebe-based tooling is needed in the future, it can be added back then.

## Scope of change

| File | Change |
|---|---|
| `composer.json` | Remove `"cebe/php-openapi": "^1.8"` from `require` |
| `composer.lock` | Regenerated automatically by `composer remove` |
| `CHANGELOG.md` | Add entry under `### Changed` |

No PHP source files, no test files, no configuration files require any modification.

## Verification

```bash
make lint-allowed-licenses
make lint-cs-fixer
make lint-phpstan
make lint-rector
make lint-deptrac
make test-unit
```
