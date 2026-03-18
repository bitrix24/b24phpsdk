# Issue #391 Plan

## Summary
- Fix `AttributesParser` so SDK metadata extraction supports compound PHP return types instead of assuming every `ReflectionType` is a `ReflectionNamedType`.
- Preserve accurate service method signatures in SDK code. Do not rewrite service return types just to satisfy documentation tooling.
- Restore developer tooling built on top of `AttributesParser`, primarily SDK coverage statistics and coverage documentation generation.
- Keep the fix compatible with current OpenAPI/OA schema research for deterministic code generation under issue `#391`.

## Problem Statement
- `AttributesParser::getSupportedInSdkApiMethods()` currently calls `getName()` directly on the value returned by `ReflectionMethod::getReturnType()`.
- This works only for `ReflectionNamedType`.
- When a service method uses a union return type such as `int|string`, PHP returns `ReflectionUnionType`, which does not have `getName()`.
- As a result, the parser crashes and breaks:
  - `show-sdk-coverage-statistics`
  - `build-documentation`

## Goals
- Support these reflection return type shapes in parser metadata extraction:
  - `ReflectionNamedType`
  - `ReflectionUnionType`
  - `ReflectionIntersectionType` if encountered
- Keep backward compatibility for current consumers that expect a single `sdk_return_type_class` when the method truly has one named class return type.
- Add an explicit string representation of the declared return type so compound types are preserved without lossy conversion.

## Implementation Changes
- Introduce a dedicated helper in `AttributesParser` to normalize `ReflectionType` into metadata instead of calling `getName()` inline.
- The helper should:
  - return a declared type string for all supported reflection type shapes
  - return `sdk_return_type_class` only when the declared type is a single named class/interface/enum type
  - return `sdk_return_type_file_name` only when a single named class/interface/enum type can be resolved to a source file
  - return `null` for class/file metadata when the declared type is compound (`union` / `intersection`) or scalar-only
- Update `getSupportedInSdkApiMethods()` to consume this helper.
- Keep `getSupportedInSdkBatchMethods()` behavior unchanged unless the same unsafe assumption is found there during implementation.

## Data Model Changes
- Extend the API-method metadata array returned by `getSupportedInSdkApiMethods()` with a new field:
  - `sdk_return_type_declaration: string|null`
- Expected examples:
  - single class: `Bitrix24\\SDK\\Services\\Task\\Result\\TaskResult`
  - nullable named type: `?TaskResult` or `TaskResult|null` depending on reflection normalization choice
  - union type: `int|string`
  - intersection type: `Foo&Bar`
- Preserve existing fields:
  - `sdk_return_type_class`
  - `sdk_return_type_file_name`

## Test Changes
- Keep the new unit regression test for union return types in `tests/Unit/Attributes/Services/AttributesParserTest.php`.
- Expand the test suite to cover:
  - single named class return type
  - scalar named type
  - union return type
  - nullable named type if represented separately in reflection path
  - intersection type if a valid fixture can be added without artificial complexity
- Assert both:
  - parser does not throw
  - metadata fields are populated according to the new contract

## Command Verification
- Run the focused unit test file:
  - `make composer "exec -- phpunit tests/Unit/Attributes/Services/AttributesParserTest.php --display-warnings"`
- Re-run the coverage statistics command:
  - `make show-sdk-coverage-statistics`
- Re-run the coverage documentation generator at least to the point where metadata extraction succeeds:
  - `make build-documentation`

## Files To Change
- `src/Attributes/Services/AttributesParser.php`
- `tests/Unit/Attributes/Services/AttributesParserTest.php`
- Optionally, any developer-tool command that needs to consume `sdk_return_type_declaration`

## Non-Goals
- Do not change service method return types purely to avoid union handling.
- Do not alter REST/OpenAPI schema generation in this task.
- Do not redesign the overall coverage documentation format beyond what is needed to represent declared return types safely.

## Risks
- Existing consumers may implicitly rely on `sdk_return_type_class` always being a string when a return type exists.
- Introducing `sdk_return_type_declaration` may require small adjustments in coverage-documentation output if it should display compound types instead of only single class names.
- Intersection type support may need careful formatting even if not currently used in SDK services.

## Acceptance Criteria
- `AttributesParser` no longer throws on service methods with union return types.
- The union-type regression test passes.
- Coverage tooling starts successfully and reaches normal processing instead of crashing in `AttributesParser`.
- Metadata returned by the parser preserves compound return type information without degrading existing single-type behavior.
