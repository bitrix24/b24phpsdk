# Plan: Audit all API v3 entities for SelectBuilder / ItemBuilder coverage

## Context

The repository currently has:
- REST v3 method vs SDK method coverage via `b24-dev:show-oa-sdk-coverage`
- A generator `b24-dev:generate-select-builder` for a single DTO
- Targeted unit assertions that verify a specific `SelectBuilder` against OpenAPI

What is missing is a CLI tool that answers:

> For every DTO in `docs/open-api/openapi.json`, does the SDK have a linked result class, and does that result class declare its builders?

A dedicated audit command is needed that:
- reads all DTOs from `components.schemas`
- finds SDK classes annotated with `#[OpenApiEntity]`
- builds the mapping `entityKey → resultClass → selectBuilder / itemBuilder`
- validates builder class references
- for `selectBuilder`: additionally checks field coverage via `allSystemFields()->buildSelect()`
- prints a summary and, controlled by flags, lists of issues

Before implementation, update the schema snapshot:

```bash
make oa-schema-build
```

---

## Goal

Add a CLI command that automatically audits builder coverage across all v3 OpenAPI snapshot entities and reports:

- how many DTOs exist in the OpenAPI snapshot
- how many DTOs are linked to SDK result classes via `#[OpenApiEntity]`
- which DTOs are missing a `selectBuilder`
- which DTOs are missing an `itemBuilder`
- which builder classes are referenced but do not exist or have the wrong base type
- which `SelectBuilder` instances do not fully cover the OpenAPI schema fields
- which SDK entity mappings reference a DTO key that is absent from the snapshot

---

## Proposed CLI

New command:

```bash
php bin/console b24-dev:show-v3-builder-coverage <scope>
```

Arguments:
- `scope` (**required**) — Bitrix24 scope name in lowercase; maps to `src/Services/<ucfirst(scope)>/` (e.g. `task` → `src/Services/Task/`)

Options:
- `--schema-file=docs/open-api/openapi.json`
- `--show-unmapped` — DTOs from OpenAPI without an SDK mapping
- `--show-missing-select` — DTOs / result classes without a `selectBuilder`
- `--show-missing-item` — DTOs / result classes without an `itemBuilder`
- `--show-invalid` — broken class references or wrong builder base types
- `--show-select-mismatches` — `SelectBuilder` does not cover all OpenAPI fields
- `--show-duplicates` — result classes that share the same `entityKey`
- `--format=table|json` — human-readable table output or machine-readable JSON

---

## Architecture

### Design decision: class discovery lives in the command

`V3BuilderCoverageAuditor` is a pure domain service. It receives an already-resolved list
of result class names and does not perform filesystem scanning itself.

The command is responsible for class discovery using the same pattern as
`ShowOaSdkCoverageCommand`:
1. Read the required `scope` argument (Symfony enforces presence automatically for `InputArgument::REQUIRED`)
2. Resolve the scan directory via `ucfirst()`: `task` → `src/Services/Task/`, `main` → `src/Services/Main/`
3. Return `Command::INVALID` if the resolved directory does not exist
4. Use `Finder` to iterate `*.php` files in the resolved directory
5. `require_once` each file to load it into the PHP runtime
6. Call `get_declared_classes()` and filter by the `Bitrix24\SDK` namespace prefix
7. Pass the resulting `list<class-string>` to the auditor

`scope` is declared via `InputArgument::REQUIRED`; Symfony will throw a runtime error before `execute()` if it is missing, so no manual validation is needed for the missing-argument case.

**Rationale:** keeping filesystem I/O out of the domain service makes it unit-testable
without touching the filesystem. The command already owns I/O.

### New domain service

```
src/OpenApi/Domain/V3BuilderCoverageAuditor.php
namespace: Bitrix24\SDK\OpenApi\Domain
```

```php
final readonly class V3BuilderCoverageAuditor
{
    public function __construct(private OpenApiSchemaEntityReader $schemaEntityReader) {}

    /**
     * @param list<class-string> $sdkClassNames  All PHP classes loaded from src/Services/
     */
    public function audit(string $schemaFile, array $sdkClassNames): V3BuilderCoverageReport;
}
```

Internal steps:
1. Fetch all entity keys via `OpenApiSchemaEntityReader::getEntityKeys()`
2. Filter `$sdkClassNames` to those bearing `#[OpenApiEntity]`
3. Build the mapping `entityKey → resultClass`
4. Validate each mapping:
   - does `selectBuilder` exist as a class?
   - does `itemBuilder` exist as a class?
   - does `selectBuilder` extend `AbstractSelectBuilder`?
   - does `itemBuilder` extend `AbstractItemBuilder`?
   - does `selectBuilder` cover all OpenAPI fields (via `allSystemFields()->buildSelect()`)?
5. Detect `sdkOnlyMappings`: `#[OpenApiEntity]` pointing to an entityKey not in the snapshot
6. Wrap `new $selectBuilderClass()` in a try/catch; on `Throwable` add an `invalid` entry
7. Detect `duplicateEntityKeyMappings`: group `#[OpenApiEntity]` classes by `entityKey`; any key with more than one class is a duplicate

**SelectBuilder instantiation contract:**
The auditor calls `new $selectBuilderClass()` with no constructor arguments.
All `SelectBuilder` implementations must have a zero-argument constructor (both current
implementations satisfy this). If instantiation throws, record it as an `invalid` issue
rather than crashing.

**`OpenApiSchemaEntityReader` memoization:**
Remove the `readonly` modifier from the class declaration (keeping `readonly` on the `$filesystem` property).
Add `private array $schemaCache = []` and update `loadSchema()` to check the cache by `$schemaFile` key
before reading the file. This prevents repeated `file_get_contents()` + `json_decode()` per entity during the audit.

**Deptrac note:**
`src/OpenApi/Domain/` is not assigned to any deptrac layer (it is "uncovered").
Importing `AbstractSelectBuilder` and `AbstractItemBuilder` from `src/Services/` inside the
auditor will **not** produce a deptrac violation. Do **not** add a `skip_violations` entry.

### New report DTOs

```
src/OpenApi/Domain/V3BuilderCoverageReport.php
namespace: Bitrix24\SDK\OpenApi\Domain
```

```php
final class V3BuilderCoverageReport
{
    /**
     * @param list<string>                                                          $unmappedEntities
     * @param list<string>                                                          $missingSelectBuilders
     * @param list<string>                                                          $missingItemBuilders
     * @param list<array{entityKey: string, class: string, reason: string}>         $invalidBuilderReferences
     * @param list<array{entityKey: string, builderClass: string, missingFields: list<string>}> $selectCoverageMismatches
     * @param list<array{resultClass: string, entityKey: string}>                   $sdkOnlyMappings
     * @param list<array{entityKey: string, resultClasses: list<string>}>           $duplicateEntityKeyMappings
     */
    public function __construct(
        public readonly int   $totalOpenApiEntities,
        public readonly int   $mappedEntities,
        public readonly int   $entitiesWithSelectBuilder,
        public readonly int   $entitiesWithItemBuilder,
        public readonly array $unmappedEntities,
        public readonly array $missingSelectBuilders,
        public readonly array $missingItemBuilders,
        public readonly array $invalidBuilderReferences,
        public readonly array $selectCoverageMismatches,
        public readonly array $sdkOnlyMappings,
        public readonly array $duplicateEntityKeyMappings,
    ) {}
}
```

---

## Files to Create

| File | PHP namespace |
|---|---|
| `src/OpenApi/Domain/V3BuilderCoverageAuditor.php` | `Bitrix24\SDK\OpenApi\Domain` |
| `src/OpenApi/Domain/V3BuilderCoverageReport.php` | `Bitrix24\SDK\OpenApi\Domain` |
| `src/Infrastructure/Console/Commands/Documentation/ShowV3BuilderCoverageCommand.php` | `Bitrix24\SDK\Infrastructure\Console\Commands\Documentation` |
| `tests/Unit/OpenApi/Domain/V3BuilderCoverageAuditorTest.php` | `Bitrix24\SDK\Tests\Unit\OpenApi\Domain` |
| `tests/Unit/Infrastructure/Console/Commands/Documentation/ShowV3BuilderCoverageCommandTest.php` | `Bitrix24\SDK\Tests\Unit\Infrastructure\Console\Commands\Documentation` |

---

## Files to Modify

- `src/OpenApi/Domain/OpenApiSchemaEntityReader.php` — add internal schema cache (memoize `loadSchema()`)
- `bin/console` — register the new command (see wiring details below)
- `Makefile` — add make target `sdk-builder-coverage-v3-show`
- `CHANGELOG.md` — add a feature entry under `3.1.0 Unreleased`

---

## `bin/console` wiring

Add after the existing `ShowOaSdkCoverageCommand` block:

```php
use Bitrix24\SDK\Infrastructure\Console\Commands\Documentation\ShowV3BuilderCoverageCommand;
use Bitrix24\SDK\OpenApi\Domain\V3BuilderCoverageAuditor;

$application->addCommand(
    new ShowV3BuilderCoverageCommand(
        new V3BuilderCoverageAuditor(
            new OpenApiSchemaEntityReader(new Symfony\Component\Filesystem\Filesystem())
        ),
        new Symfony\Component\Finder\Finder(),
        $log
    )
);
```

`ShowV3BuilderCoverageCommand` constructor signature:

```php
public function __construct(
    private readonly V3BuilderCoverageAuditor $auditor,
    private readonly Finder                   $finder,
    private readonly LoggerInterface          $logger,
)
```

---

## `phpunit.xml.dist`

**No changes required.** The `unit_tests` suite already points to `./tests/Unit` recursively,
so any new test file placed under `tests/Unit/` is auto-discovered.

---

## Task Breakdown

### Task 1: Add domain audit model

**Goal:** collect pure domain logic, no console / UI dependencies.

Implementation steps:
1. Create `V3BuilderCoverageReport` with typed fields as specified above
2. Create `V3BuilderCoverageAuditor` with the two-argument `audit()` method
3. Reuse `OpenApiSchemaEntityReader` (already exists)
4. Use PHP Reflection to discover `#[OpenApiEntity]` on the filtered class list
5. Extract coverage comparison logic from `SelectBuilderAssertions::assertCoversOpenApiSchema()`
   into a private domain method that returns `list<string>` of missing fields instead of
   throwing a PHPUnit assertion — **do not import `PHPUnit\Framework\Assert` in production code**
6. Wrap `new $selectBuilderClass()` in a try/catch; on `Throwable` add an `invalid` entry
7. Detect `duplicateEntityKeyMappings`: group `#[OpenApiEntity]` classes by `entityKey`; any key with more than one class is a duplicate

Do **not** import `PHPUnit\Framework\Assert` in any production class.

### Task 2: Implement command

**Goal:** build a thin CLI layer on top of the audit service.

Command behaviour:
- Reads required `scope` argument; resolves scan directory `src/Services/<ucfirst(scope)>/`
- Returns `Command::INVALID` if the resolved directory does not exist
- Loads all `*.php` files in the resolved directory via Finder + `require_once`
- Filters declared classes to the `Bitrix24\SDK` prefix
- Calls `$auditor->audit($schemaFile, $sdkClassNames)`
- Prints summary counters
- Controlled by flags, prints dedicated issue tables
- `--format=json` outputs the full report as JSON (use `json_encode` with `JSON_PRETTY_PRINT`)
- `--show-duplicates` prints result classes sharing the same `entityKey`
- Invalid `--format` value should produce `$io->error(...)` and return `Command::INVALID`

Summary example output:

```text
OpenAPI DTO count:          187
Mapped SDK entities:         24
Entities with selectBuilder: 12
Entities with itemBuilder:    7
Unmapped OpenAPI DTOs:       163
Missing select builders:      12
Missing item builders:        17
Invalid builder references:    0
Select coverage mismatches:    1
SDK-only mappings:             0
Duplicate entity keys:         0
```

### Task 3: Add unit tests for audit logic

**Goal:** cover core math and edge cases.

Conventions (from `docs/testing.md`):
- Use `#[CoversClass(V3BuilderCoverageAuditor::class)]`
- Use `#[DataProvider]` for the scenario matrix
- Use a real temporary JSON schema file: create in `setUp()` via `sys_get_temp_dir() . '/test_openapi_' . uniqid() . '.json'`, delete in `tearDown()`
- Instantiate `new OpenApiSchemaEntityReader(new Filesystem())` directly — no mocks for the reader

Test cases (implement as a single DataProvider method):

| Scenario | Expected |
|---|---|
| All entities mapped and valid | zero issues in all arrays |
| DTO in snapshot, no `#[OpenApiEntity]` mapping | appears in `unmappedEntities` |
| Mapping present, `selectBuilder` is null | appears in `missingSelectBuilders` |
| Mapping present, `itemBuilder` is null | appears in `missingItemBuilders` |
| `selectBuilder` class does not exist | appears in `invalidBuilderReferences` |
| `itemBuilder` class does not exist | appears in `invalidBuilderReferences` |
| Class exists but does not extend `AbstractSelectBuilder` | appears in `invalidBuilderReferences` |
| `selectBuilder` missing some OpenAPI fields | appears in `selectCoverageMismatches` with correct `missingFields` |
| `#[OpenApiEntity]` points to unknown entityKey | appears in `sdkOnlyMappings` |
| Two result classes with same `entityKey` | appears in `duplicateEntityKeyMappings` |

### Task 4: Add command tests

**Goal:** verify the CLI contract.

Conventions:
- Use `#[CoversClass(ShowV3BuilderCoverageCommand::class)]`
- Use Symfony `CommandTester` for command execution
- Stub `V3BuilderCoverageAuditor` via `createStub()` — no real filesystem scan

Test cases:
- Summary counters appear in default (table) output
- `--format=json` outputs valid JSON with all report fields
- `--show-unmapped` prints the unmapped DTOs table
- `--show-missing-select` prints the missing select builders table
- `--show-missing-item` prints the missing item builders table
- `--show-invalid` prints the invalid references table
- `--show-select-mismatches` prints the coverage mismatch table
- `--show-duplicates` prints the duplicate entity key table
- `scope=task` scans real `src/Services/Task/`; use `createMock(V3BuilderCoverageAuditor::class)` with `expects($this->once())->method('audit')` to confirm the auditor is called
- `scope=NonExistent` returns `Command::INVALID` when the resolved directory does not exist
- Invalid `--format` value returns `Command::INVALID`

### Task 5: Wire into repo tooling

1. Register the command in `bin/console` as specified in the wiring section above
2. Add to `Makefile`:

```makefile
sdk-builder-coverage-v3-show:
	docker compose run --rm php-cli php bin/console b24-dev:show-v3-builder-coverage task \
		--show-unmapped --show-missing-select --show-missing-item \
		--show-invalid --show-select-mismatches --show-duplicates
```

3. Add help-text entry in the `help:` target echo block
4. Add a `CHANGELOG.md` entry under `3.1.0 Unreleased`:

```markdown
### Added
- `b24-dev:show-v3-builder-coverage` CLI command: audits SelectBuilder / ItemBuilder
  coverage for all OpenAPI v3 entities and reports unmapped, missing, invalid,
  field-coverage-mismatch, and duplicate entity key cases (`make sdk-builder-coverage-v3-show`)
```

---

## Verification

Minimal:

```bash
make oa-schema-build
make test-unit
```

Recommended:

```bash
make oa-schema-build
make lint-cs-fixer
make lint-phpstan
make lint-rector
make lint-deptrac
make test-unit
make sdk-builder-coverage-v3-show
docker compose run --rm php-cli php bin/console b24-dev:show-v3-builder-coverage task \
    --show-unmapped --show-missing-select --show-missing-item \
    --show-invalid --show-select-mismatches --show-duplicates
```

---

## Done Criteria

The task is complete when:
- The new CLI command exists and is registered in `bin/console`
- The command iterates all DTOs from `docs/open-api/openapi.json`
- It reports unmapped / missing / invalid / mismatch cases correctly
- Unit tests cover the domain auditor and the CLI contract
- The make target `sdk-builder-coverage-v3-show` works
- `CHANGELOG.md` is updated
- `make lint-all` and `make test-unit` both pass with no new violations
