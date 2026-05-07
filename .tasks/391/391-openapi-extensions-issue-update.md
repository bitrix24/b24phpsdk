# Issue #391 Update: OpenAPI Extensions Research

## Summary

Add a dedicated section to `#391` describing the standard OpenAPI mechanism for vendor-specific metadata and link the relevant official documentation. The section should establish that the correct way to extend the schema is via OpenAPI Specification Extensions (`x-...`) and reserve a project-local namespace such as `x-b24-*` for SDK/codegen metadata.

## Issue Text To Add

```md
## OpenAPI specification extension mechanism

The standard way to extend OpenAPI with vendor-specific metadata is to use **Specification Extensions**, that is, custom fields whose names start with `x-`.

For this task, this means that additional metadata required for deterministic SDK code generation can be added to the OA schema without breaking OpenAPI compatibility, as long as it is expressed through project-specific extension fields such as `x-b24-*`.

Important notes:

- extension property names must start with `x-`
- extension values may be primitives, objects, arrays, or `null`
- support for these attributes depends on the downstream tooling: OpenAPI allows them, but generators/validators may ignore unknown extensions unless they are explicitly handled
- prefixes `x-oai-` and `x-oas-` are reserved by the OpenAPI Initiative and should not be used for project-specific metadata
- for this SDK, the preferred namespace should be project-local, for example `x-b24-*`

Example:

```yaml
paths:
  /tasks:
    get:
      operationId: getTasks
      x-b24-sdk-service: Task
      x-b24-sdk-method: list
      x-b24-api-version: v3
```

## Relevant documentation

- OpenAPI Specification 3.1, Specification Extensions:
  https://spec.openapis.org/oas/v3.1.0#specification-extensions
- OpenAPI Specification 3.1:
  https://spec.openapis.org/oas/v3.1.0
- Swagger docs: OpenAPI Extensions:
  https://swagger.io/docs/specification/v3_0/openapi-extensions/
- OpenAPI Initiative publications and registries:
  https://spec.openapis.org/
```

## Acceptance

- The new section is appended to `#391` as a standalone research/result block.
- The text explicitly states that `x-...` is the standard OpenAPI extension mechanism.
- The text explicitly recommends a project namespace like `x-b24-*`.
- The section includes official links to OAS 3.1 and Swagger extension docs.
- The section notes that `x-oai-` and `x-oas-` are reserved.
- The section includes one minimal YAML example.

## Assumptions

- The purpose of the update is documentation/research capture inside the issue, not a final schema design.
- No concrete `x-b24-*` key set is fixed yet beyond recommending the namespace and mechanism.
