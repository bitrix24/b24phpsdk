# Plan: Stage 4 — local Spectral container for OA contract linting

## Context

Stage 2 and Stage 3 formalize local developer tooling around the checked-in OpenAPI snapshot
stored in `docs/open-api/openapi.json`.
This stage adds a dedicated contract validation step so the checked-in OA snapshot can be linted
for OpenAPI errors through a local Docker-based workflow consistent with the rest of the project.

The repository already uses:

- `docker-compose.yaml` as the local container entrypoint
- `Makefile` as the canonical developer interface for tooling
- `make oa-schema-build` to refresh `docs/open-api/openapi.json`

This stage must add Spectral as a local tool without introducing CI integration yet.

## Goal

Create a local Docker-based Spectral lint workflow that:

1. runs against the checked-in `docs/open-api/openapi.json`
2. is available through a dedicated `make` target
3. uses the standard Spectral OpenAPI ruleset as the baseline
4. can be tuned with minimal local overrides if the current Bitrix24 schema has known exceptions

## Proposed Developer Interface

Add a new `Makefile` target:

- `lint-openapi`

Expected usage flow:

1. `make oa-schema-build`
2. `make lint-openapi`

The command must fail with a non-zero exit code when Spectral finds contract errors.

## Container Integration

Add a dedicated `spectral` service to `docker-compose.yaml`.

Requirements:

- use a pinned ready-made Spectral image, not `latest`
- mount the repository into the container
- run from the repository root, consistent with `php-cli`
- support execution through:

```bash
docker compose run --rm spectral lint docs/open-api/openapi.json
```

The implementation should not require a separate custom Dockerfile for Spectral in this stage.

## Spectral Configuration

Add a repository-level Spectral config file:

- `.spectral.yaml`

Baseline rules:

- extend the standard OpenAPI ruleset:
  - `spectral:oas`

Adjustment policy:

- keep the default ruleset unless the checked-in OA snapshot fails for known structural reasons
- if exceptions are required, add only narrow documented overrides
- do not disable broad categories of validation without a concrete schema-driven reason

This stage is intended to catch real OA contract problems first, not to enforce style preferences.

## Makefile Changes

Update `Makefile` so that:

- `help` lists `lint-openapi`
- `lint-openapi` runs Spectral through `docker compose run --rm spectral ...`

At this stage:

- do not add `lint-openapi` to `lint-all`

Reason:

- the OA snapshot is a generated artifact refreshed separately via `make oa-schema-build`
- keeping this check explicit avoids coupling general PHP lint flow to OA refresh timing

## Documentation Changes

Update developer documentation in either `README.md` or `CONTRIBUTING.md` to document:

- that `docs/open-api/openapi.json` is the checked-in OA baseline
- that `make oa-schema-build` refreshes the snapshot
- that `make lint-openapi` validates the snapshot with Spectral

## Acceptance Criteria

- `docker-compose.yaml` contains a `spectral` service usable with `docker compose run --rm`
- `.spectral.yaml` exists and extends the standard OpenAPI Spectral ruleset
- `Makefile` exposes `lint-openapi`
- `make lint-openapi` validates `docs/open-api/openapi.json`
- Spectral errors cause command failure
- developer docs explain how to refresh and lint the OA snapshot
- no GitHub Actions workflow is added in this stage

## Test Scenarios

Verify:

1. `docker compose config` succeeds after adding the new service
2. `make lint-openapi` starts the Spectral container and reads `docs/open-api/openapi.json`
3. `make oa-schema-build` followed by `make lint-openapi` works as the intended developer flow
4. a deliberately invalid OpenAPI file or an existing real schema error produces a non-zero exit code

## Assumptions

- Spectral is required only for local developer workflow in Stage 4
- the standard OpenAPI ruleset is the correct starting point
- any local overrides should be minimal and justified by the checked-in schema, not by convenience
