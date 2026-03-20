# Issue #391 Plan

## Summary
- Fix `AttributesParser` so SDK metadata extraction supports compound PHP return types instead of assuming every `ReflectionType` is a `ReflectionNamedType`.
- Replace the current array-shape contract returned by `AttributesParser` with an explicit readonly value object with public fields.
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
  - `ReflectionIntersectionType`
- Replace untyped metadata arrays with a strongly typed readonly VO so consumers stop depending on fragile string keys.
- Use one VO class per API method metadata record, not different VO classes for different SDK methods.
- Make the collection contract explicit and consistent: `getSupportedInSdkApiMethods()` returns `list<SupportedInSdkApiMethod>`.
- Keep the existing semantic contract for single-class return types while extending it for compound type declarations.
- Add an explicit string representation of the declared return type so compound types are preserved without lossy conversion.
- Standardize the VO field naming convention to `camelCase` and use that naming consistently in all consumers and tests.

## Implementation Changes
- Introduce a dedicated readonly VO for API method metadata, for example `SupportedInSdkApiMethod`, with public readonly fields instead of associative array keys.
- Introduce a dedicated helper in `AttributesParser` to normalize `ReflectionType` into metadata instead of calling `getName()` inline.
- The helper should:
  - return `sdkReturnTypeDeclaration` for all supported reflection type shapes
  - return `sdkReturnTypeClass` only when the declared type is a single named class/interface/enum type
  - return `sdkReturnTypeFileName` only when a single named class/interface/enum type can be resolved to a source file
  - return `null` for class/file metadata when the declared type is compound (`union` / `intersection`) or scalar-only
- Update `getSupportedInSdkApiMethods()` to return `list<SupportedInSdkApiMethod>` instead of array shapes.
- Remove the current inconsistent collection behavior where the method sometimes behaves like a map keyed by API method name and sometimes like a numeric list after scope filtering.
- If a consumer needs lookup by API method name, build that index explicitly in the consumer instead of encoding two collection contracts in the parser.
- Update every current consumer of `getSupportedInSdkApiMethods()` to read VO fields instead of string keys:
  - `ShowCoverageStatisticsCommand`
  - `GenerateCoverageDocumentationCommand`
  - `GenerateExamplesForDocumentationCommand`
- Keep `getSupportedInSdkBatchMethods()` behavior unchanged unless the same unsafe assumption is found there during implementation.

## Data Model Changes
- Replace the current array-shape metadata with a readonly VO carrying explicit public fields.
- Naming convention:
  - all VO public fields use `camelCase`
  - plan text and implementation should stop referring to new VO fields by legacy `snake_case` names
- Collection contract:
  - `getSupportedInSdkApiMethods(): list<SupportedInSdkApiMethod>`
- Each list item represents one supported SDK API method entry.
- Minimum field set in the VO:
  - `sdkScope: string`
  - `name: string`
  - `documentationUrl: ?string`
  - `description: ?string`
  - `isDeprecated: bool`
  - `deprecationMessage: ?string`
  - `sdkMethodName: string`
  - `sdkMethodFileName: string`
  - `sdkMethodFileStartLine: int`
  - `sdkMethodFileEndLine: int`
  - `sdkClassName: string`
  - `sdkReturnTypeClass: ?string`
  - `sdkReturnTypeFileName: ?string`
  - `sdkReturnTypeDeclaration: ?string`
- Expected declaration examples:
  - single class: `Bitrix24\\SDK\\Services\\Task\\Result\\TaskResult`
  - nullable named type: `TaskResult|null`
  - union type: `int|string`
  - intersection type: `Foo&Bar`

## Test Changes
- Keep the new unit regression test for union return types in `tests/Unit/Attributes/Services/AttributesParserTest.php`.
- Expand the test suite to cover:
  - single named class return type
  - scalar named type
  - union return type
  - nullable named type
  - intersection type
- Assert both:
  - parser does not throw
  - returned value is the new readonly VO
  - VO public fields are populated according to the new contract

## Command Verification
- Run the focused unit test file:
  - `make composer "exec -- phpunit tests/Unit/Attributes/Services/AttributesParserTest.php --display-warnings"`
- Re-run the coverage statistics command:
  - `make show-sdk-coverage-statistics`
- Re-run the coverage documentation generator at least to the point where metadata extraction succeeds:
  - `make build-documentation`
- Run the examples generator through a real execution path that reaches parser metadata loading, not just `--help`:
  - use a narrow invocation of `b24-dev:generate-examples` with local fixture/template arguments sufficient to initialize the command and execute the branch that reads `getSupportedInSdkApiMethods()`
  - if that path is too environment-dependent for reliable local execution, add or run a focused automated test covering the migrated parser-consumer integration in `GenerateExamplesForDocumentationCommand`

## Files To Change
- `src/Attributes/Services/AttributesParser.php`
- `src/Attributes/Services/SupportedInSdkApiMethod.php`
- `tests/Unit/Attributes/Services/AttributesParserTest.php`
- `src/Infrastructure/Console/Commands/Documentation/ShowCoverageStatisticsCommand.php`
- `src/Infrastructure/Console/Commands/Documentation/GenerateCoverageDocumentationCommand.php`
- `src/Infrastructure/Console/Commands/Documentation/GenerateExamplesForDocumentationCommand.php`

## Non-Goals
- Do not change service method return types purely to avoid union handling.
- Do not alter REST/OpenAPI schema generation in this task.
- Do not redesign the overall coverage documentation format beyond what is needed to represent declared return types safely.

## Risks
- Existing consumers currently rely on associative array access and will all need to be migrated atomically to the VO contract.
- Consumers that currently rely on direct lookup by method name will need an explicit local indexing step after the parser starts returning a list.
- Existing consumers may implicitly rely on `sdkReturnTypeClass` always being a string when a return type exists.
- Introducing `sdkReturnTypeDeclaration` may require small adjustments in coverage-documentation output if it should display compound types instead of only single class names.
- Intersection type support may need a synthetic unit-test fixture if the current SDK services do not already declare one.

## Acceptance Criteria
- `AttributesParser` no longer throws on service methods with union return types.
- `AttributesParser::getSupportedInSdkApiMethods()` returns `list<SupportedInSdkApiMethod>` instead of associative arrays.
- The union-type regression test passes against the new VO contract.
- Nullable and intersection type test cases pass against the new VO contract.
- Coverage tooling starts successfully and reaches normal processing instead of crashing in `AttributesParser`.
- `GenerateExamplesForDocumentationCommand` remains compatible with the new parser contract.
- Metadata returned by the parser preserves compound return type information without degrading existing single-type behavior.
