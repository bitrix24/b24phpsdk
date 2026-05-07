# Plan: Stage 2 — CLI utility for OA schema vs SDK v3 coverage

## Context

Stage 1 fixes `AttributesParser` and moves SDK method metadata to readonly VO objects.
Stage 2 builds on top of that and adds a dedicated CLI utility that compares:

- the current OpenAPI schema snapshot stored in `docs/open-api/openapi.json`
- the SDK methods implemented in `src/Services/`
- only methods that are explicitly marked as `ApiVersion::v3`

Unlike the existing `show-sdk-coverage-statistics`, this stage must not depend on a live portal webhook.
The source of truth is the checked-in OA schema snapshot.

## Goal

Create a CLI utility that:

1. reads the current OA schema snapshot from the repository
2. extracts API methods described in the schema
3. extracts SDK methods implemented for REST v3
4. calculates coverage statistics between OA schema and SDK
5. optionally prints the list of methods present in OA schema but not yet covered by the SDK

## Proposed Command

Add a new console command, for example:

- `b24-dev:show-oa-sdk-coverage`

Required `make` target:

- `oa-sdk-coverage`

This command is separate from the existing live-API coverage command and does not replace it.

## Input Data

### 1. OA schema snapshot

- Default path: `docs/open-api/openapi.json`
- Optional CLI override:
  - `--schema-file=...`

### 2. SDK metadata

- Load service classes from `src/Services`
- Reuse `AttributesParser`
- Filter only methods whose endpoint metadata is marked with `ApiVersion::v3`

## Required Stage 1 Dependency

Stage 1 is already implemented.
Stage 2 updates the Stage 1 metadata VO just enough to expose API version explicitly.

The SDK method metadata VO used by `AttributesParser` must include `apiVersion`, for example:

- `apiVersion: ApiVersion`

Without that field, Stage 2 would need to re-scan attributes separately just to distinguish v1 from v3 methods.
That would duplicate parser responsibility and weaken the design.

## OA Method Extraction Rules

The CLI utility must derive a normalized API method identifier from OA schema entries.

### Normalization rules

- Primary source: OpenAPI `paths`
- For a path like `/main.eventlog.list`, normalized method name is `main.eventlog.list`
- Leading slash is removed
- Coverage is counted by logical API method name, not by `(path, HTTP verb)` pair

### Important edge case

Some OA entries may not map 1:1 to the SDK method name.

Example:

- OA path: `/rest.documentation.openapi`
- SDK method name: `documentation`

The plan must therefore include a small alias/normalization layer:

- default: strip leading slash and use path as method name
- fallback: explicit alias map for known exceptions

If more exceptions appear, they should be added to the alias map, not hardcoded inline across the command.

## Alias-Layer Design

Introduce a small dedicated normalization policy object or class, for example:

- `src/OpenApi/Domain/OaToSdkMethodNormalizationPolicy.php`

Responsibilities:

- normalize raw OA path names into logical method names
- apply explicit OA-to-SDK aliases
- expose explicitly ignored logical methods with reasons
- expose scope compatibility aliases for validation between endpoint prefix and service scope

Suggested internal structure:

- `methodAliases: array<string, string>`
- `ignoredMethods: array<string, string>` where value is the ignore reason
- `scopeAliases: array<string, string>` for prefix-to-service-scope compatibility

Minimum initial contents based on current repository audit:

- `methodAliases`
  - `rest.documentation.openapi` => `documentation`
- `ignoredMethods`
  - empty for the first iteration unless implementation proves a method must be excluded intentionally
- `scopeAliases`
  - `tasks` => `task`

Normalization rules order:

1. strip leading slash
2. normalize OA path to logical method candidate
3. apply explicit method alias if present
4. skip if method is explicitly ignored
5. deduplicate by final logical method name

This order matters because `rest.documentation.openapi` and `documentation` must collapse to the same logical method after aliasing.

## Audit Results — Current OA Snapshot vs Current SDK v3

Based on the checked-in `docs/open-api/openapi.json` and the current SDK methods marked with `ApiVersion::v3`:

### Aliasing audit

Confirmed required method alias:

- `rest.documentation.openapi` => `documentation`

No other currently implemented SDK v3 methods require OA-to-SDK renaming aliases.

### Scope compatibility audit

Direct scope matches:

- `main.eventlog.*` -> endpoint prefix `main`, service scope `main`

Known scope-prefix compatibility mismatch requiring explicit scope alias:

- `tasks.*` endpoint prefix -> service scope `task`

Special no-prefix case:

- `documentation` has no dotted method prefix and its service is declared with empty scope

This is not a method alias, but it must be handled in validation logic so that scope checks do not produce false mismatches.

## Coverage Model

### Covered

A method is considered covered when:

- it exists in OA schema after normalization
- and there is at least one SDK method metadata entry with:
  - matching normalized API method name
  - `apiVersion === ApiVersion::v3`

### Not covered

A method is considered not covered when:

- it exists in OA schema after normalization
- and no matching SDK v3 method exists

### SDK-only

A method is considered SDK-only when:

- it exists in SDK metadata with `apiVersion === ApiVersion::v3`
- and no matching logical method exists in OA schema after normalization and aliasing

These methods must not be folded into the uncovered OA count.
They are tracked separately as a diagnostic metric.

### Out of scope

- v1 SDK methods are ignored
- OA methods intentionally excluded by alias/ignore rules should be tracked explicitly
- batch wrappers are not counted as separate OA coverage unless OA itself describes them as separate API methods

## Output

### Default output

The command should print summary statistics such as:

- total OA methods
- total SDK v3 methods matched to OA
- uncovered OA methods
- SDK-only v3 methods
- coverage percentage

Add per-scope breakdown.

Scope derivation rule:

- derive scope from method prefix before the first dot
- for SDK methods, validate the endpoint prefix derived from `ApiEndpointMetadata.name` against the service scope from `ApiServiceMetadata.scope`
- apply explicit `scopeAliases` before deciding that a mismatch exists
- methods without a dot prefix, such as `documentation`, should be reported under a dedicated no-scope bucket such as `–`

### Optional output flag

Add a flag such as:

- `--show-uncovered`

When enabled, print the normalized method names that are present in OA schema but not implemented in SDK v3.

Add a second flag such as:

- `--show-sdk-only`

When enabled, print SDK v3 methods that do not have a matching logical OA method after normalization and aliasing.

Optional future extension:

- `--format=json`
- `--scope=main`

These are useful, but not required for the first iteration unless implementation stays small.

## Suggested Implementation Structure

### 1. OA schema method reader

Create a small dedicated reader/service, for example:

- `src/OpenApi/Domain/OaSchemaMethodReader.php`

Responsibilities:

- load `openapi.json`
- extract normalized OA method identifiers
- apply alias normalization
- derive scope from the logical method prefix before the first dot
- deduplicate final logical method names after aliasing
- expose ignored methods separately if ignore rules are added

Do not bury OA parsing logic directly inside the console command.

### 2. SDK v3 metadata reader

Reuse `AttributesParser` output and filter:

- `apiVersion === ApiVersion::v3`

Add a validation/helper layer that:

- derives the endpoint prefix from `SupportedInSdkApiMethod.name`
- compares that prefix with the service scope declared in `ApiServiceMetadata`
- applies `scopeAliases` before treating the pair as mismatched
- can surface mismatches as warnings or diagnostics in command output

### 3. Coverage calculator

Create a small pure service or local helper that compares:

- `list<string>` OA method names
- `list<SupportedInSdkApiMethod>` filtered to v3

and returns an immutable result object or structured summary array.

The result must include at least:

- total OA methods
- total covered methods
- uncovered OA methods list
- SDK-only methods list
- coverage percentage
- per-scope breakdown
- scope mismatch diagnostics, if any

### 4. Console command

Responsibilities:

- parse CLI options
- load OA methods
- load SDK v3 methods
- compute coverage
- print summary
- optionally print uncovered OA methods
- optionally print SDK-only v3 methods

## Files to Change

Minimum expected files:

- `src/Attributes/Services/SupportedInSdkApiMethod.php` — add `apiVersion`
- `src/Attributes/Services/AttributesParser.php` — populate `apiVersion`
- `src/OpenApi/Domain/OaToSdkMethodNormalizationPolicy.php`
- `src/OpenApi/Domain/OaSchemaMethodReader.php`
- `src/Infrastructure/Console/Commands/Documentation/ShowOaSdkCoverageCommand.php`
- `Makefile` — add `oa-sdk-coverage` target

Optional supporting files:

- dedicated coverage result VO or calculator service
- unit tests for OA method normalization
- unit tests for coverage calculation

## Test Plan

### Unit tests

Add focused tests for:

- OA path normalization using real checked-in OA method names from `docs/open-api/openapi.json`
- alias mapping for exceptional paths using real OA entries, especially `rest.documentation.openapi`
- v3 filtering from SDK metadata
- coverage percentage calculation
- uncovered method list generation
- SDK-only method list generation
- scope derivation from prefix before the first dot
- scope compatibility alias handling (`tasks` -> `task`)

Fixture strategy:

- use real OA data extracted from the checked-in `docs/open-api/openapi.json`
- for small unit tests, copy minimal verbatim OA fragments into dedicated fixtures instead of inventing synthetic method names
- use the full repository snapshot in command-level verification

### Integration / command verification

Run the command against the repository snapshot:

- `make oa-sdk-coverage`
- `make oa-sdk-coverage ARGS="--show-uncovered"` if the Make target is designed to forward args
- `make oa-sdk-coverage ARGS="--show-sdk-only"` if the Make target is designed to forward args

If Make argument forwarding is not added, verify with:

- `make composer "exec -- php bin/console b24-dev:show-oa-sdk-coverage --schema-file=docs/open-api/openapi.json"`
- `make composer "exec -- php bin/console b24-dev:show-oa-sdk-coverage --schema-file=docs/open-api/openapi.json --show-uncovered"`
- `make composer "exec -- php bin/console b24-dev:show-oa-sdk-coverage --schema-file=docs/open-api/openapi.json --show-sdk-only"`

## Non-Goals

- Do not fetch OA schema from the network in this stage
- Do not replace the existing live-webhook coverage command
- Do not auto-generate SDK services from OA schema in this stage
- Do not attempt to infer undocumented alias mappings heuristically without making them explicit

## Risks

- OA path names may not always match SDK method names directly
- OA schema may contain duplicate logical methods across multiple HTTP verbs
- The current OA snapshot may include documentation endpoints or helper endpoints that should be excluded or aliased explicitly
- If the Stage 2 update does not add `apiVersion` to the existing SDK metadata VO, the command will either duplicate attribute parsing or produce incorrect mixed v1/v3 statistics
- Scope prefixes in endpoint names and service scope declarations are not always identical, so false mismatch diagnostics are possible without an explicit compatibility map

## Acceptance Criteria

- A new CLI command exists and reads `docs/open-api/openapi.json` by default
- A required `make oa-sdk-coverage` target exists for the command
- The command reports OA-vs-SDK-v3 coverage statistics without requiring a webhook
- With `--show-uncovered`, the command prints OA-described methods that are not yet implemented in SDK v3
- With `--show-sdk-only`, the command prints SDK v3 methods that are not described in the OA snapshot
- Coverage logic is based on normalized logical method names, not raw path/verb pairs
- Known OA-to-SDK naming exceptions are handled through an explicit alias layer
- Per-scope statistics are derived from method prefixes and validated against service scope metadata with explicit scope compatibility aliases
