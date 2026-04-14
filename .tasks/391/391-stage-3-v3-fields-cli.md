# Plan: Stage 3 — CLI utility for API v3 field metadata by entity

## Context

Stage 2 adds CLI tooling around the checked-in OpenAPI snapshot for REST v3 coverage.
This stage builds on the same baseline and adds a developer CLI utility that shows field metadata
for a selected v3 entity using `*.field.list` endpoints.

The utility must support:

- interactive mode: developer selects an entity from the checked-in OA schema snapshot
- argument mode: developer passes an entity key directly
- output as either JSON or a console table

Unlike the legacy `b24-dev:show-fields-description`, this utility is v3-oriented and must not
guess entity names from arbitrary fragments.

As part of this stage, the user-facing descriptions/help text of the specific overlapping
legacy utility should be updated to mark it as legacy for field-inspection workflows.

## Goal

Create a new Symfony Console command that:

1. reads the current OA schema snapshot from `docs/open-api/openapi.json`
2. discovers all entities that expose a v3 `*.field.list` endpoint
3. lets the developer choose one entity interactively or pass it explicitly
4. resolves the entity to a concrete v3 REST method `<entity>.field.list`
5. calls that method against the current dev webhook
6. prints field metadata as JSON or as a table

## Proposed Command

Add a new console command:

- `b24-dev:show-v3-field-metadata`

Its command description/help text should position it as the primary v3 field-metadata utility.
The descriptions/help text of `b24-dev:show-fields-description` should explicitly mark it as a
legacy utility.

Proposed syntax:

```bash
php bin/console b24-dev:show-v3-field-metadata [entity] [--format=json|table] [--webhook=...] [--schema-file=docs/open-api/openapi.json]
```

Arguments:

- `entity` optional

Options:

- `--format` optional, default `json`
- `--webhook` optional
- `--schema-file` optional, default `docs/open-api/openapi.json`

## Entity Input Contract

The command must accept a precise entity key, not a free-form fragment.

Rule:

- `entity key` = OA method name without the `.field.list` suffix

Examples:

- `main.eventlog` -> `main.eventlog.field.list`
- `tasks.task` -> `tasks.task.field.list`
- `tasks.task.access` -> `tasks.task.access.field.list`
- `tasks.task.file` -> `tasks.task.file.field.list`
- `tasks.task.chat.message` -> `tasks.task.chat.message.field.list`

Implications:

- do not auto-resolve partial prefixes
- do not guess the "best" candidate for short inputs
- if exact `<entity>.field.list` is absent in OA snapshot, return a clear error

This keeps the CLI contract deterministic and aligned with the real OA schema structure.

## Interactive Mode

If `entity` is not passed:

- load `docs/open-api/openapi.json`
- scan all OA paths
- keep only methods ending with `.field.list`
- normalize each to its `entity key`
- show the resulting list through `ChoiceQuestion`

Interactive mode must list only entities that actually have `*.field.list` in the snapshot.
The UI should display the exact `entity key` that is accepted in argument mode.

## Webhook Resolution

The command must use the current dev environment conventions instead of introducing a new config source.

Resolve webhook in this order:

1. explicit `--webhook`
2. `BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK`
3. `BITRIX24_WEBHOOK`

This matches the current repository conventions:

- `tests/.env` and `tests/.env.local`
- `Makefile`
- `tests/Integration/Factory.php`

Important implementation note:

- `tests/.env` and `tests/.env.local` are currently loaded into the command environment by
  `Makefile`, not by `bin/console` itself
- therefore direct `php bin/console ...` invocation only sees these variables if they were already
  exported in the shell environment
- the command should read from `$_ENV`/`$_SERVER`/`getenv()` but should not, in this task,
  introduce extra dotenv loading for `tests/.env.local`

If webhook is still empty, fail with a direct message:

- `Webhook is not configured. Pass --webhook or set BITRIX24_WEBHOOK in tests/.env.local`

## Output Rules

### JSON mode

Default output mode is `json`.

The command should print a normalized JSON array where each item contains:

- `code`
- `title`
- `metadata`

`metadata` must contain the full field payload returned by Bitrix24 without dropping attributes.

### Table mode

If `--format=table` is passed, render a Symfony Console table with columns:

- `code`
- `title`
- `metadata`

Rules:

- `title` falls back to `code` when absent
- `metadata` is the full field payload serialized as JSON
- do not flatten or truncate metadata semantically; keep all attributes visible

## Input Data

### 1. OA schema snapshot

- Default path: `docs/open-api/openapi.json`
- Optional CLI override:
  - `--schema-file=...`

### 2. Live field payload

- REST method invoked: `<entity>.field.list`
- API version: `ApiVersion::v3`
- Authentication: webhook resolved from current dev environment

## Implementation Changes

Before implementation, refresh the local OA snapshot baseline:

- `make oa-schema-build`

Code changes:

- add a new command class in `src/Infrastructure/Console/Commands`
- register the command in `bin/console`
- update `src/Infrastructure/Console/Commands/ShowFieldsDescriptionCommand.php` description/help
  text to mark it as legacy in user-facing CLI output
- add a small OA resolver/helper in Infrastructure or OpenApi layer for:
  - reading schema file
  - extracting `.field.list` methods
  - converting method names to `entity key`
  - validating exact entity match
- build the REST client with `CoreBuilder` and `Credentials::createFromWebhook(...)`
- call the resolved method with `ApiVersion::v3`

## Validation And Errors

Fail with `INVALID` when:

- `entity` is unknown
- exact `<entity>.field.list` is missing
- OA schema file is missing or invalid
- webhook cannot be resolved
- `--format` has an unsupported value

Error messages should be short and explicit.
Do not silently skip bad input and do not auto-correct entity keys.

## Tests

### Unit tests

- extract entity keys from OA paths ending with `.field.list`
- ignore methods not ending with `.field.list`
- resolve exact entity key to exact method name
- reject partial or unknown entity inputs
- verify webhook resolution priority:
  - CLI option
  - `BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK`
  - `BITRIX24_WEBHOOK`

### Command tests

- no `entity` argument -> interactive list is built from OA snapshot
- `tasks.task` -> resolves to `tasks.task.field.list`
- JSON output contains complete metadata payload
- table output renders the 3 agreed columns
- missing webhook produces a clear error
- `b24-dev:show-fields-description --help` visibly marks the command as legacy

## Acceptance Criteria

- A new command `b24-dev:show-v3-field-metadata` exists and is registered in `bin/console`
- The new command description/help text identifies it as the v3-oriented field metadata utility
- `b24-dev:show-fields-description` is clearly marked as legacy in its command description/help
  text
- The command accepts exact entity keys derived from OA snapshot method names
- Interactive mode lists entities discovered from `docs/open-api/openapi.json`
- The command calls `<entity>.field.list` using `ApiVersion::v3`
- Webhook is taken from the current dev environment unless overridden explicitly
- Default output is JSON
- Table output shows `code`, `title`, and full `metadata`
- Invalid entity keys and missing webhook fail with explicit errors

## Non-Goals

- Do not extend or rewrite the execution logic of the legacy `b24-dev:show-fields-description`
- Only its user-facing description/help text may be updated to mark it as legacy
- Do not auto-discover entities from a live portal through `rest.scope.list`
- Do not support fuzzy entity matching or prefix guessing
- Do not redesign SDK service builders for this task
