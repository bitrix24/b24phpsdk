# REST API coverage reporting

The legacy command `b24-dev:show-sdk-coverage-statistics` measures REST API v1 SDK
wrappers against methods available on the configured Bitrix24 portal. Its baseline
depends on that portal's permissions, installed modules and available methods. It is
not the entire Bitrix24 API catalog, PHPUnit code coverage, or evidence that all
methods were exercised live.

REST API v3 uses the separate `make -s sdk-coverage-v3-show` report against
`docs/open-api/openapi.json`. Refresh that baseline with `make oa-schema-build`.
Do not combine the v1 and v3 denominators.

## Options

| Option | Values and behavior |
|---|---|
| `--webhook` | Required incoming webhook URL. Treat the URL as a credential. |
| `--show` | `scope-stat` reports scope counts; `uncovered` lists methods for one scope. |
| `--scope` | A known SDK scope code, such as `crm` or `task`. Required for `uncovered`; optionally restricts `scope-stat` to one scope. |
| `--format` | `table` (default) or `json`. Requires `--show`. |
| `--no-interaction` / `-n` | Never prompt. Requires `--show`. |

Without report flags, interactive execution keeps the existing overall summary and
menu. `--scope` and `--format` require `--show`, including explicit `--format=table`.
Unknown values and invalid combinations fail before portal initialization. Scope
codes are the SDK's `Scope::getAvailableScopeCodes()` values; they need not match a
method-name prefix (for example, scope `task` includes `tasks.*` methods).

## Examples

The Make target loads the existing configured webhook and runs without a TTY:

```sh
make -s sdk-coverage-v1-show
make -s sdk-coverage-v1-show ARGS='--scope=crm'
make -s sdk-coverage-v1-show ARGS='--format=json'
make -s sdk-coverage-v1-show ARGS='--show=uncovered --scope=crm --format=json'
```

For direct JSON output, use Docker Compose's `--no-TTY` (or `-T`) and Symfony's
`--no-interaction`. The following URL is a placeholder:

```sh
docker compose run --rm --no-TTY php-cli php bin/console \
  b24-dev:show-sdk-coverage-statistics \
  --webhook='https://your-domain.bitrix24.com/rest/1/REPLACE_WITH_TOKEN/' \
  --no-interaction --show=uncovered --scope=crm --format=json
```

To use the menu, omit `--show`, `--scope`, `--format` and `--no-interaction` from a
direct interactive invocation. The Make target always selects `scope-stat`.

Use `make -s` to suppress Make's command echo, which may contain the webhook.
Avoid saving credential-bearing shell history or logs. JSON stdout contains one
JSON document with no splash screen, progress bar, menu, ANSI decoration or trace.
Docker lifecycle messages may appear on stderr.

## JSON contract

Both reports contain `apiVersion: "v1"`, `basis: "configured-portal"`, `show`, and
`summary`. The summary always describes the unique global portal method baseline,
even when `--scope` restricts the report details.

| Summary field | Meaning |
|---|---|
| `totalPortalMethods` | Unique normalized methods available on the portal. |
| `coveredMethodsCount` | Portal methods implemented by a v1 SDK wrapper. |
| `uncoveredMethodsCount` | Portal methods with no v1 SDK wrapper, including deprecated methods. |
| `sdkOnlyMethodsCount` | v1 SDK methods absent from the portal baseline; excluded from the denominator. |
| `coveragePercentage` | Covered / total × 100, rounded to two decimals; numeric zero for an empty baseline. |

`scope-stat` adds `scopes`, an array of objects containing `scope` and the same
five numeric fields. Each row uses portal membership to determine coverage, even
when an SDK wrapper has different scope metadata. SDK-only counts use the SDK's
scope metadata. Scopes can overlap: summing scope rows does not give unique global
totals. `--scope` limits the rows to one scope.

`uncovered` adds:

- `scope`: the selected scope code.
- `uncoveredMethods`: uncovered names with the known deprecated methods removed.
- `uncoveredMethodsIncludingDeprecated`: all uncovered names for that scope.
- `sdkOnlyMethods`: SDK methods assigned to the scope but absent from that scope's portal baseline.

All method lists are lowercase, deduplicated and sorted; empty lists are `[]`.
Deprecated methods remain in the coverage denominator and in summary counts.
SDK batch wrappers are a separate human-readable inventory across API versions;
they are excluded from the coverage calculation and JSON schema.

## Exit codes and verification

| Code | Meaning |
|---|---|
| `0` | Report completed successfully. |
| `1` | Runtime/portal failure or Symfony argument parsing failure, such as `--show` without a value. |
| `2` | Report validation failure: unknown option values, invalid combinations, or missing required report input. |

Errors are written to stderr when available. Runtime messages omit credential-bearing
exception details and stack traces. Failed JSON reports leave stdout empty when
stderr is available. In JSON mode, custom output streams without stderr receive a JSON object containing
an `error` message; in table mode, these streams receive plain text.

The read-only live command regression test is available through
`make test-integration-coverage-command`. It creates no entities or fixtures.
