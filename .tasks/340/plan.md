# Plan: Create a deterministic SelectBuilder generator (issue #340)

## Context

The SDK already has `AbstractSelectBuilder` and concrete implementations (`TaskItemSelectBuilder`,
`EventLogSelectBuilder`). Writing these manually is tedious and error-prone. This issue asks for
a CLI command that reads the checked-in OpenAPI snapshot (`docs/open-api/openapi.json`), extracts
field metadata for a chosen entity, and generates a ready-to-use `*SelectBuilder` PHP class.

The OA schema carries entity DTOs in `components.schemas` (e.g. `bitrix.tasks.taskdto`,
`bitrix.main.eventlogdto`). Each DTO's `properties` key lists all selectable fields; nested
objects are represented as `$ref` references to sibling DTOs.

Field resolution strategy:
- Simple typed properties (`string`, `integer`, `boolean`, `date-time`, plain `array`) → flat field name
- `$ref` property → expand one level deep: `fieldName.subFieldName` for each property in the
  referenced DTO (mirrors the existing `TaskItemSelectBuilder.chat()` pattern)
- `array` with `$ref` items → flat field name only (no expansion — arrays of objects are
  selected as a unit)
- `id` property → placed in the constructor, not as a separate method

Code generation groups the flat field list by prefix:
- Fields with no dot → one zero-parameter method `fieldName(): self`
- Fields sharing a dot prefix (e.g. `chat.id`, `chat.entityId`) → one method `chat(): self` using
  `array_merge($this->select, [...])`

The command is `b24-dev:generate-select-builder`. Output goes to stdout by default so the developer
can redirect or review before saving.

---

## Files to Create

### 1. `src/OpenApi/Domain/OaSchemaEntityReader.php`

```php
namespace Bitrix24\SDK\OpenApi\Domain;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

readonly class OaSchemaEntityReader
{
    public function __construct(private Filesystem $filesystem) {}

    /** @return list<string>  entity keys from components.schemas */
    public function getEntityKeys(string $schemaFile): array;

    /**
     * Returns a flat, sorted list of selectable field names for the entity.
     * $ref properties are expanded one level deep using dot notation.
     * The special 'id' field is always first.
     *
     * @return list<string>
     */
    public function getSelectableFields(string $schemaFile, string $entityKey): array;

    /** @return array<string, mixed> */
    private function loadSchema(string $schemaFile): array;

    /** @return array<string, mixed> entity properties map */
    private function getEntityProperties(array $schema, string $entityKey): array;

    /** @return array<string, mixed> sub-properties of the $ref target */
    private function resolveRef(array $schema, string $ref): array;
}
```

Key rules:
- Throws `RuntimeException` if `$schemaFile` does not exist or entity key is absent
- `getEntityKeys` reads `schema['components']['schemas']`, returns sorted list
- `getSelectableFields` returns `['id', ...rest sorted]`; skips `id` from the rest

### 2. `src/OpenApi/Domain/SelectBuilderCodeGenerator.php`

```php
namespace Bitrix24\SDK\OpenApi\Domain;

readonly class SelectBuilderCodeGenerator
{
    /**
     * @param list<string> $selectableFields flat list including dot-notation fields
     */
    public function generate(
        string $namespace,
        string $className,
        array $selectableFields
    ): string;

    /** @return array<string, list<string>> prefix => list of full dot-notation or plain fields */
    private function groupByPrefix(array $selectableFields): array;

    private function renderSimpleMethod(string $fieldName): string;

    /** @param list<string> $dotFields full dot-notation strings like 'chat.id' */
    private function renderMergeMethod(string $prefix, array $dotFields): string;
}
```

Generated file format (deterministic — sorted field methods, fixed header):
```php
<?php

declare(strict_types=1);

namespace <namespace>;

use Bitrix24\SDK\Services\AbstractSelectBuilder;

class <ClassName> extends AbstractSelectBuilder
{
    public function __construct()
    {
        $this->select[] = 'id';
    }

    public function fieldName(): self
    {
        $this->select[] = 'fieldName';
        return $this;
    }

    public function chat(): self
    {
        $this->select = array_merge($this->select, ['chat.id', 'chat.entityId', 'chat.entityType']);
        return $this;
    }
}
```

Methods are emitted in alphabetical order (ensures determinism).

### 3. `src/Infrastructure/Console/Commands/Generator/GenerateSelectBuilderCommand.php`

```php
namespace Bitrix24\SDK\Infrastructure\Console\Commands\Generator;

#[AsCommand(
    name: 'b24-dev:generate-select-builder',
    description: 'Generate a SelectBuilder class for a v3 entity from the OpenAPI schema',
)]
class GenerateSelectBuilderCommand extends Command
{
    private const ENTITY     = 'entity';
    private const NAMESPACE  = 'namespace';
    private const CLASS_NAME = 'class-name';
    private const OUTPUT     = 'output';
    private const SCHEMA_FILE = 'schema-file';

    public function __construct(
        private readonly OaSchemaEntityReader $entityReader,
        private readonly SelectBuilderCodeGenerator $codeGenerator,
    ) { parent::__construct(); }
}
```

Options / arguments:
- `entity` — optional positional argument (interactive `ChoiceQuestion` if omitted)
- `--namespace` — default derived: `Bitrix24\SDK\Services\<PascalModule>\Service`
- `--class-name` — default derived: `<PascalEntity>SelectBuilder`
- `--output` — file path; if omitted, prints to stdout
- `--schema-file` — default `docs/open-api/openapi.json`

Default derivation from entity key `bitrix.<module>.<entity>dto`:
- module = second dotted segment → `ucfirst(scopeAlias(module))` (e.g. `tasks` → `Task`)
- entity = third dotted segment without `dto` suffix → `ucfirst(entity)` (e.g. `taskdto` → `Task`)
- default namespace: `Bitrix24\SDK\Services\<PascalModule>\Service`
- default class name: `<PascalEntity>SelectBuilder`

When `--output` is given the file is written with `Symfony\Component\Filesystem\Filesystem::dumpFile`.

### 4. `tests/Unit/OpenApi/Domain/OaSchemaEntityReaderTest.php`

```php
#[CoversClass(OaSchemaEntityReader::class)]
class OaSchemaEntityReaderTest extends TestCase
{
    // Uses a real minimal fixture JSON under tests/Unit/OpenApi/Domain/fixture/
    // or the actual docs/open-api/openapi.json as a read-only reference.

    #[Test] public function testGetEntityKeysReturnsAllDtoSchemas(): void
    #[Test] public function testGetSelectableFieldsFlatFieldsForSimpleDto(): void
    #[Test] public function testGetSelectableFieldsExpandsRefOneLevel(): void
    #[Test] public function testGetSelectableFieldsDoesNotExpandArrayRefItems(): void
    #[Test] public function testGetEntityKeysThrowsOnMissingFile(): void
}
```

### 5. `tests/Unit/OpenApi/Domain/SelectBuilderCodeGeneratorTest.php`

```php
#[CoversClass(SelectBuilderCodeGenerator::class)]
class SelectBuilderCodeGeneratorTest extends TestCase
{
    #[Test] public function testGenerateSimpleFields(): void          // no $ref
    #[Test] public function testGenerateDotNotationGroupedMethod(): void  // chat.id pattern
    #[Test] public function testGenerateIdInConstructorNotAsMethod(): void
    #[Test] public function testMethodsAreSortedAlphabetically(): void
    #[Test] public function testGenerateWithEmptyFieldList(): void    // only id
}
```

---

## Files to Modify

### 1. `bin/console`

Add after the `ShowV3FieldMetadataCommand` block:

```php
use Bitrix24\SDK\Infrastructure\Console\Commands\Generator\GenerateSelectBuilderCommand;
use Bitrix24\SDK\OpenApi\Domain\SelectBuilderCodeGenerator;

// ...

$application->addCommand(
    new GenerateSelectBuilderCommand(
        new OaSchemaEntityReader(new Symfony\Component\Filesystem\Filesystem()),
        new SelectBuilderCodeGenerator()
    )
);
```

### 2. `CHANGELOG.md`

Under `## 3.1.0 Unreleased` → `### Added`:

```markdown
- Added `b24-dev:generate-select-builder` console command that reads the OpenAPI snapshot and
  generates a deterministic `*SelectBuilder` PHP class for any v3 entity
  ([#340](https://github.com/bitrix24/b24phpsdk/issues/340))
```

---

## Deptrac compliance

- `OaSchemaEntityReader` and `SelectBuilderCodeGenerator` live in `src/OpenApi/Domain/` — this
  directory is **not** tracked by Deptrac (not in any named layer), so no violations introduced.
- `GenerateSelectBuilderCommand` lives in `src/Infrastructure/` (Infrastructure layer). It imports
  from `src/OpenApi/Domain/` — which is outside all layers, so Deptrac ignores that import.
- `Infrastructure` may depend on `Core` and `Services` per ruleset — no new violations.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

No integration test suite is required — this command is a code-generation developer tool
that only reads the local OA schema file, makes no HTTP calls, and writes to stdout or
a local file. All observable behaviour is covered by unit tests.
