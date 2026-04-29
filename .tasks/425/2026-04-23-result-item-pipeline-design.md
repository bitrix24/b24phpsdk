# ResultItem Pipeline Design

## Summary

This design replaces direct `ResultItem` generation from ad hoc OpenAPI, documentation HTML, or live API inference with a staged pipeline that produces a reviewable intermediate payload.

The pipeline is intentionally narrow in scope: it is designed only for `ResultItem` generation. The canonical input to generation becomes a YAML payload stored as a task artifact under `.tasks/<issue-id>/<method>/`.

## Goals

- Prefer `OpenAPI` schema as the primary source of field metadata.
- Fall back to `bitrix24/b24restdocs` markdown when `OpenAPI` is missing or incomplete.
- Produce a human-reviewable, machine-readable intermediate payload.
- Verify the payload against a real API response without mutating it automatically.
- Generate `ResultItem` classes only from the final reviewed payload.

## Non-Goals

- Do not generalize the payload format for builders or other code generators yet.
- Do not keep documentation HTML parsing in the critical path.
- Do not let live API verification silently rewrite the canonical payload.

## Branch and Task Conventions

- The issue id is derived from the current branch name.
- Supported branch patterns:
  - `feature/<id>-...`
  - `bugfix/<id>-...`
- Task artifacts are stored under:

```text
.tasks/<issue-id>/<method>/
```

For example:

```text
.tasks/425/im.dialog.get/
```

## Pipeline Stages

One command orchestrates the full workflow:

```text
b24-dev:result-item-generator <method> --stage=build|verify|apply|generate|all
```

### `build`

Builds the canonical payload:

```text
.tasks/<issue-id>/<method>/result-item.payload.yaml
```

Behavior:

- Read field metadata from `OpenAPI` first.
- If data is absent or incomplete, supplement it from `bitrix24/b24restdocs`.
- Normalize fields, sections, types, nullability, and descriptions into the payload format.

### `verify`

Builds the verification report:

```text
.tasks/<issue-id>/<method>/result-item.verification-report.yaml
```

Behavior:

- Read `result-item.payload.yaml`.
- Perform a real API call using test parameters.
- Compare runtime response structure with the payload.
- Record matches, missing fields, unexpected fields, nullability observations, and type mismatches.

Important:

- `verify` does not mutate `result-item.payload.yaml`.

### `apply`

Updates the payload using the verification report.

Behavior:

- Read `result-item.payload.yaml`.
- Read `result-item.verification-report.yaml`.
- Apply only safe updates.

Safe updates:

- Add new fields discovered in API but absent from payload.
- Mark fields as nullable when API returned `null`.
- Add verification notes.

Unsafe updates that must not be auto-applied:

- Replacing contract types from `OpenAPI` or docs with inferred runtime types when the change is ambiguous.
- Rewriting `required`.
- Changing `phpdoc_type` when the conflict is not deterministic.

### `generate`

Generates the `ResultItem` class from the canonical payload only.

Behavior:

- Read `result-item.payload.yaml`.
- Generate PHPDoc annotations and imports from normalized payload metadata.
- Do not read `OpenAPI`, docs, or API during this stage.

### `all`

Runs the stages in order:

```text
build -> verify -> apply -> generate
```

## Source Priority and Merge Rules

### Priority

1. `OpenAPI`
2. `bitrix24/b24restdocs`

### Merge Semantics

`OpenAPI` owns:

- field code
- requiredness when available
- base data type

`bitrix24/b24restdocs` supplements:

- description
- notes
- format
- nullable and optional semantics when not present in `OpenAPI`
- nested object and array-item structure when missing in `OpenAPI`

When both sources disagree:

- Keep the `OpenAPI` value in the payload.
- Preserve the conflict in `notes`.
- Track both sources in payload metadata.

## Canonical Payload Format

The canonical payload format is YAML because it is both human-readable and machine-readable.

Example:

```yaml
version: 1
method: im.dialog.get
object: result-item
generated_from:
  - openapi
  - b24restdocs

fields:
  - code: id
    source_type: integer
    phpdoc_type: int
    format: null
    required: true
    nullable: false
    source: openapi
    description: Chat identifier
    notes: null

  - code: date_create
    source_type: datetime
    phpdoc_type: Carbon\CarbonImmutable
    format: date-time
    required: true
    nullable: false
    source: b24restdocs
    description: Chat creation date in ATOM format
    notes: null

sections:
  - name: restrictions
    kind: object
    source: b24restdocs
    fields:
      - code: avatar
        source_type: boolean
        phpdoc_type: bool
        format: null
        required: true
        nullable: false
        source: b24restdocs
        description: Availability of avatar change
        notes: null

  - name: readed_list_item
    kind: object
    source: b24restdocs
    fields:
      - code: date
        source_type: datetime
        phpdoc_type: Carbon\CarbonImmutable|null
        format: date-time
        required: false
        nullable: true
        source: b24restdocs
        description: Read date
        notes: If not specified, the value is null
```

### Field Semantics

Each field stores:

- `code`: original field name from the API contract
- `source_type`: source-level type such as `integer`, `string`, `datetime`, `object`
- `phpdoc_type`: normalized SDK-facing PHPDoc type
- `format`: additional format metadata such as `date-time`
- `required`: whether the field is contractually required
- `nullable`: whether the field may be `null`
- `source`: dominant source for this field
- `description`: human-readable meaning
- `notes`: conflicts, caveats, or supplemental rules

### Section Semantics

Sections are represented as an explicit ordered list, not a map, so that:

- review order is deterministic
- later metadata can be added without changing shape
- the format remains stable for LLM-driven edits

Each section stores:

- `name`
- `kind`
- `source`
- `fields`

## Verification Report Format

The verification report is a YAML artifact separate from the canonical payload.

It records:

- confirmed fields
- fields missing from runtime response
- fields found in runtime response but absent from payload
- type mismatches
- nullability observations
- section-level findings

The report is diagnostic and review-oriented. It is not a direct generator input.

## Command Responsibilities

### New command

- `src/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommand.php`

Responsibilities:

- Parse `--stage`
- Resolve current issue id from branch
- Resolve task artifact paths
- Orchestrate `build`, `verify`, `apply`, `generate`

### Branch issue id resolver

- `src/Infrastructure/Console/Commands/Generator/BranchIssueIdResolver.php`

Responsibilities:

- Read current branch name
- Support:
  - `feature/<id>-...`
  - `bugfix/<id>-...`
- Throw a clear error for unsupported branch names

### Task path resolver

- `src/OpenApi/Domain/ResultItemTaskPathResolver.php`

Responsibilities:

- Build canonical task paths under `.tasks/<issue-id>/<method>/`

## Domain Model

### Payload value objects

- `src/OpenApi/Domain/ResultItemPayload.php`
- `src/OpenApi/Domain/ResultItemPayloadField.php`
- `src/OpenApi/Domain/ResultItemPayloadSection.php`

Responsibilities:

- Hold canonical payload structure
- Keep field and section semantics explicit

### Payload serialization

- `src/OpenApi/Domain/ResultItemPayloadSerializer.php`

Responsibilities:

- Read and write YAML
- Preserve stable ordering and human readability

### Payload builder

- `src/OpenApi/Domain/ResultItemPayloadBuilder.php`

Responsibilities:

- Merge `OpenAPI` and `b24restdocs`
- Normalize source types and PHPDoc types
- Build the canonical payload object

### OpenAPI provider

- `src/OpenApi/Domain/OpenApiResultItemPayloadProvider.php`

Responsibilities:

- Extract root fields and nested structures from schema data when available

### REST docs provider

- `src/OpenApi/Domain/RestDocsResultItemPayloadProvider.php`

Responsibilities:

- Read markdown from `bitrix24/b24restdocs`
- Extract fields, types, nullability hints, descriptions, and nested sections
- Avoid parsing rendered HTML

## Verification Domain

### Payload verifier

- `src/OpenApi/Domain/ResultItemPayloadVerifier.php`

Responsibilities:

- Compare canonical payload with runtime response
- Produce a structured verification report

### Verification report model

- `src/OpenApi/Domain/ResultItemVerificationReport.php`
- `src/OpenApi/Domain/ResultItemVerificationReportSerializer.php`

Responsibilities:

- Represent and serialize verification findings

### Verification applier

- `src/OpenApi/Domain/ResultItemVerificationApplier.php`

Responsibilities:

- Read canonical payload and verification report
- Apply only safe updates

## Generator Integration

The existing `ResultItemCodeGenerator` should stop consuming ad hoc field collections as the primary contract and instead consume the canonical payload model.

Generation should:

- read top-level `fields`
- map `phpdoc_type` directly into annotations
- add `Carbon\CarbonImmutable` import only when needed
- remain ignorant of upstream source resolution

## What Leaves the Critical Path

The new design removes the following from the generation critical path:

- rendered documentation HTML parsing
- direct generation from documentation pages
- direct generation from live API responses

Live API remains a verification input, not a generation source of truth.

## Testing Strategy

Required tests:

- unit tests for branch issue id extraction
- unit tests for task path resolution
- unit tests for YAML serialization and deserialization
- unit tests for OpenAPI payload extraction
- unit tests for `b24restdocs` markdown extraction
- unit tests for merge rules between `OpenAPI` and docs
- unit tests for verification report generation
- unit tests for safe apply behavior
- unit tests for generation from canonical payload
- one end-to-end scenario for `im.dialog.get`

## Example End-to-End Scenario

Method:

```text
im.dialog.get
```

Expected artifact directory:

```text
.tasks/425/im.dialog.get/
```

Expected files:

```text
result-item.payload.yaml
result-item.verification-report.yaml
```

Generation target:

```text
src/Services/IM/Dialog/Result/DialogItemResult.php
```

## Risks and Mitigations

### Risk: incomplete `OpenAPI`

Mitigation:

- use `b24restdocs` markdown as the declared fallback

### Risk: docs and schema conflict

Mitigation:

- prefer `OpenAPI`
- record disagreement in `notes`

### Risk: runtime response is narrower than the documented contract

Mitigation:

- use runtime only for verification
- do not let runtime silently replace contract metadata

### Risk: payload drift through repeated apply operations

Mitigation:

- keep `verification-report.yaml` separate
- limit auto-apply to safe updates only

## Open Questions Resolved

- Payload format: `yaml`
- Scope: narrow, `ResultItem` only
- Artifact location: `.tasks/<issue-id>/<method>/`
- Issue id source: current branch
- Branch patterns:
  - `feature/<id>-...`
  - `bugfix/<id>-...`
- Sections representation: explicit ordered list
- Command model: one command with `stage`
- Preferred workflow: `build -> verify -> apply -> generate`
