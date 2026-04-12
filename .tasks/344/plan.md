# ItemBuilderInterface Support in Task Service — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Bring `ItemBuilder` infrastructure to full parity with `SelectBuilder` — add
`getSupportedFieldNames()` to `AbstractItemBuilder`, add `getWritableFields()` to the schema
reader, implement `ItemBuilderCodeGenerator` + CLI command, expand `TaskItemBuilder` to cover
all 78 writable fields from the OpenAPI schema, and fix the two `instanceof` checks in `Task.php`.

**Architecture:** Mirror the existing `SelectBuilder` stack exactly.
`AbstractItemBuilder::getSupportedFieldNames()` uses reflection to enumerate 1-param instance
methods in the concrete subclass (same idea as `allSystemFields()` for zero-param select methods).
`getWritableFields()` reads from `paths/{op}/post/requestBody/.../fields/properties`.
`ItemBuilderCodeGenerator` maps OpenAPI types to PHP types and emits typed setter methods.
`GenerateItemBuilderCommand` wraps the generator as a console command registered in `bin/console`.
`TaskItemBuilder` is expanded by appending the missing methods; the constructor, `createFromTask`,
and `CarbonInterface` date types are kept intact.

**Tech Stack:** PHP 8.3, PHPStan, php-cs-fixer, Rector, Deptrac, PHPUnit

---

### Task 1: Fix isinstance checks in Task.php

**Files:**
- Modify: `src/Services/Task/Service/Task.php`

**Context**

`Task.php` is in namespace `Bitrix24\SDK\Services\Task\Service`.
`TaskItemBuilder` lives in the same namespace — no `use` for it exists or is needed.
`ItemBuilderInterface` lives in `Bitrix24\SDK\Core\Contracts` — the import is MISSING.

Current `use` block (lines 16–28):
```php
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Contracts\SelectBuilderInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Task\Result\DeletedTaskResult;
use Bitrix24\SDK\Services\Task\Result\TaskResult;
use Bitrix24\SDK\Services\Task\Result\UpdatedTaskResult;
use Psr\Log\LoggerInterface;
```

**Step 1: Add the missing import between CoreInterface and SelectBuilderInterface**

Before:
```php
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Contracts\SelectBuilderInterface;
```
After:
```php
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Contracts\ItemBuilderInterface;
use Bitrix24\SDK\Core\Contracts\SelectBuilderInterface;
```

**Step 2: Fix add() — change instanceof check only (full method body shown)**

```php
    public function add(array|TaskItemBuilder $fields): TaskResult
    {
        if ($fields instanceof ItemBuilderInterface) {
            $fields = $fields->build();
        }

        return new TaskResult(
            $this->core->call(
                'tasks.task.add',
                [
                    'fields' => $fields
                ],
                ApiVersion::v3
            )
        );
    }
```

**Step 3: Fix update() — change instanceof check only (full method body shown)**

```php
    public function update(int $id, array|TaskItemBuilder $fields): UpdatedTaskResult
    {
        if ($fields instanceof ItemBuilderInterface) {
            $fields = $fields->build();
            unset($fields['creatorId']);
        }

        return new UpdatedTaskResult(
            $this->core->call(
                'tasks.task.update',
                [
                    'id' => $id,
                    'fields' => $fields
                ],
                ApiVersion::v3
            )
        );
    }
```

**Step 4: Run linters**

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
```

Expected: all pass. If `lint-cs-fixer` fails, run `make lint-cs-fixer-fix`, then re-run.

**Step 5: Run unit tests**

```bash
make test-unit
```

Expected: green.

**Step 6: Commit**

```bash
git add src/Services/Task/Service/Task.php
git commit -m "Fix instanceof checks in Task::add and Task::update to use ItemBuilderInterface (#344)"
```

---

### Task 2: Add AbstractItemBuilder::getSupportedFieldNames()

**Files:**
- Modify: `src/Services/AbstractItemBuilder.php`
- Create: `tests/Unit/Services/AbstractItemBuilderTest.php`

**Context**

`AbstractSelectBuilder::allSystemFields()` (zero-param methods, calls them all) is the pattern.
For `AbstractItemBuilder`, methods have one typed parameter (the field value). We cannot call
them without values, so instead we RETURN their names. The filter: public, non-static,
not in base class, exactly 1 parameter (using `getNumberOfParameters()`).
Using `getNumberOfParameters()` (not `getNumberOfRequiredParameters()`) ensures that methods
with a default value (e.g. `needsControl(bool $v = false)`) are also included.

**Step 1: Add method to AbstractItemBuilder**

Open `src/Services/AbstractItemBuilder.php`. After `withUserField()`, add:

```php
    /**
     * Returns field names supported by the concrete builder.
     *
     * Discovers all public non-static methods declared in the concrete subclass
     * (not inherited from AbstractItemBuilder) with exactly one parameter.
     * These are the typed setter methods generated for each writable field.
     *
     * @return list<string>
     */
    public function getSupportedFieldNames(): array
    {
        $baseMethodNames = array_map(
            static fn(\ReflectionMethod $m): string => $m->getName(),
            (new \ReflectionClass(self::class))->getMethods(\ReflectionMethod::IS_PUBLIC)
        );

        $fieldNames = [];
        foreach ((new \ReflectionClass(static::class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (in_array($method->getName(), $baseMethodNames, true)) {
                continue;
            }
            if ($method->isStatic()) {
                continue;
            }
            if ($method->getNumberOfParameters() !== 1) {
                continue;
            }
            $fieldNames[] = $method->getName();
        }

        sort($fieldNames);
        return $fieldNames;
    }
```

**Step 2: Write failing unit test**

Create `tests/Unit/Services/AbstractItemBuilderTest.php`:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services;

use Bitrix24\SDK\Services\AbstractItemBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractItemBuilder::class)]
class AbstractItemBuilderTest extends TestCase
{
    #[Test]
    #[TestDox('getSupportedFieldNames() returns names of all 1-param instance methods in the concrete class')]
    public function testGetSupportedFieldNamesReturnsAllFieldMethods(): void
    {
        $builder = new class extends AbstractItemBuilder {
            public function title(string $title): self { $this->fields['title'] = $title; return $this; }
            public function active(bool $active): self { $this->fields['active'] = $active; return $this; }
            public function count(int $count): self { $this->fields['count'] = $count; return $this; }
        };

        $this->assertSame(['active', 'count', 'title'], $builder->getSupportedFieldNames());
    }

    #[Test]
    #[TestDox('getSupportedFieldNames() excludes inherited methods (build, withUserField)')]
    public function testGetSupportedFieldNamesExcludesInheritedMethods(): void
    {
        $builder = new class extends AbstractItemBuilder {
            public function title(string $title): self { $this->fields['title'] = $title; return $this; }
        };

        $names = $builder->getSupportedFieldNames();
        $this->assertNotContains('build', $names);
        $this->assertNotContains('withUserField', $names);
    }

    #[Test]
    #[TestDox('getSupportedFieldNames() excludes static factory methods')]
    public function testGetSupportedFieldNamesExcludesStaticMethods(): void
    {
        $builder = new class extends AbstractItemBuilder {
            public function title(string $title): self { $this->fields['title'] = $title; return $this; }
            public static function fromArray(array $data): static { $b = new static(); $b->fields = $data; return $b; }
        };

        $this->assertNotContains('fromArray', $builder->getSupportedFieldNames());
    }

    #[Test]
    #[TestDox('getSupportedFieldNames() includes methods with a default parameter value')]
    public function testGetSupportedFieldNamesIncludesMethodsWithDefaultParam(): void
    {
        $builder = new class extends AbstractItemBuilder {
            public function needsControl(bool $v = false): self { $this->fields['needsControl'] = $v; return $this; }
        };

        $this->assertContains('needsControl', $builder->getSupportedFieldNames());
    }

    #[Test]
    #[TestDox('getSupportedFieldNames() excludes methods with 0 or 2+ parameters')]
    public function testGetSupportedFieldNamesExcludesWrongParamCount(): void
    {
        $builder = new class extends AbstractItemBuilder {
            public function validField(string $v): self { $this->fields['validField'] = $v; return $this; }
            public function noParams(): self { return $this; }
            public function twoParams(string $a, int $b): self { return $this; }
        };

        $names = $builder->getSupportedFieldNames();
        $this->assertContains('validField', $names);
        $this->assertNotContains('noParams', $names);
        $this->assertNotContains('twoParams', $names);
    }

    #[Test]
    #[TestDox('getSupportedFieldNames() returns names sorted alphabetically')]
    public function testGetSupportedFieldNamesSortedAlphabetically(): void
    {
        $builder = new class extends AbstractItemBuilder {
            public function zzz(string $v): self { return $this; }
            public function aaa(string $v): self { return $this; }
            public function mmm(string $v): self { return $this; }
        };

        $this->assertSame(['aaa', 'mmm', 'zzz'], $builder->getSupportedFieldNames());
    }
}
```

**Step 3: Run test to confirm it fails (method does not exist yet)**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/AbstractItemBuilderTest.php
```

Expected: FAIL — `getSupportedFieldNames()` does not exist.

**Step 4: Implement (Step 1 code is the implementation — you already wrote it)**

Method should now exist. Run again:

```bash
docker-compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/AbstractItemBuilderTest.php
```

Expected: all tests pass (green).

**Step 5: Run full linter + unit suite**

```bash
make lint-cs-fixer && make lint-phpstan && make lint-deptrac && make test-unit
```

Expected: all green.

**Step 6: Commit**

```bash
git add src/Services/AbstractItemBuilder.php tests/Unit/Services/AbstractItemBuilderTest.php
git commit -m "Add AbstractItemBuilder::getSupportedFieldNames() with unit tests (#344)"
```

---

### Task 3: Add OpenApiSchemaEntityReader::getWritableFields()

**Files:**
- Modify: `src/OpenApi/Domain/OpenApiSchemaEntityReader.php`
- Create: `tests/Unit/OpenApi/Domain/Fixtures/openapi-writable-fields.json`
- Modify: `tests/Unit/OpenApi/Domain/OpenApiSchemaEntityReaderTest.php`

**Context**

Reads `paths/{operationPath}/post/requestBody/content/application/json/schema/properties/fields/properties`.
This is the Bitrix24 convention: every `*.add` / `*.update` method wraps its input in a `fields` key.
Returns `array<string, string>` where the value is the raw OpenAPI type string
(`integer`, `string`, `boolean`, `array`) or `object` for `$ref` entries.
The caller (generator command) is responsible for mapping to PHP types.

**Step 1: Create fixture file**

Create `tests/Unit/OpenApi/Domain/Fixtures/openapi-writable-fields.json`:

```json
{
  "openapi": "3.0.0",
  "info": { "title": "Test", "version": "1.0.0" },
  "paths": {
    "/test.entity.add": {
      "post": {
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "properties": {
                  "fields": {
                    "type": "object",
                    "properties": {
                      "title":  { "type": "string" },
                      "active": { "type": "boolean" },
                      "count":  { "type": "integer" },
                      "tags":   { "type": "array" },
                      "nested": { "$ref": "#/components/schemas/test.nested" }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "schemas": {
      "test.nested": {
        "type": "object",
        "properties": {
          "id": { "type": "integer" }
        }
      }
    }
  }
}
```

**Step 2: Write failing test**

Add the following test methods to `OpenApiSchemaEntityReaderTest`:

```php
    private const string WRITABLE_FIXTURE = __DIR__ . '/Fixtures/openapi-writable-fields.json';

    #[Test]
    #[TestDox('getWritableFields returns scalar field names with their OpenAPI types')]
    public function testGetWritableFieldsReturnsScalarFields(): void
    {
        $fields = $this->reader->getWritableFields(self::WRITABLE_FIXTURE, '/test.entity.add');

        $this->assertSame('string',  $fields['title']);
        $this->assertSame('boolean', $fields['active']);
        $this->assertSame('integer', $fields['count']);
        $this->assertSame('array',   $fields['tags']);
    }

    #[Test]
    #[TestDox('getWritableFields returns "object" for $ref fields')]
    public function testGetWritableFieldsReturnsObjectForRefFields(): void
    {
        $fields = $this->reader->getWritableFields(self::WRITABLE_FIXTURE, '/test.entity.add');

        $this->assertSame('object', $fields['nested']);
    }

    #[Test]
    #[TestDox('getWritableFields throws RuntimeException for unknown operation path')]
    public function testGetWritableFieldsThrowsOnUnknownPath(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->reader->getWritableFields(self::WRITABLE_FIXTURE, '/does.not.exist');
    }
```

**Step 3: Run to confirm failure**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit \
  tests/Unit/OpenApi/Domain/OpenApiSchemaEntityReaderTest.php --filter testGetWritableFields
```

Expected: FAIL — method does not exist.

**Step 4: Implement getWritableFields() in OpenApiSchemaEntityReader**

Add after `getEntityKeysUsedInApiPaths()`:

```php
    /**
     * Returns writable fields for the given operation path (typically a *.add method).
     *
     * Reads paths/{operationPath}/post/requestBody/content/application/json/schema
     *      /properties/fields/properties
     * and returns a map of field name → OpenAPI type string.
     * $ref fields are returned as 'object'. Fields without a type or $ref are skipped.
     *
     * @return array<string, string>  fieldName → openapi type
     * @throws \RuntimeException when the operation path is not found in the schema
     */
    public function getWritableFields(string $schemaFile, string $operationPath): array
    {
        $schema = $this->loadSchema($schemaFile);

        if (!isset($schema['paths'][$operationPath]['post']['requestBody'])) {
            throw new \RuntimeException(
                sprintf('Operation path "%s" not found in OpenAPI schema', $operationPath)
            );
        }

        $fieldsProperties = $schema['paths'][$operationPath]['post']
            ['requestBody']['content']['application/json']
            ['schema']['properties']['fields']['properties'] ?? [];

        $result = [];
        foreach ($fieldsProperties as $name => $definition) {
            if (isset($definition['$ref'])) {
                $result[$name] = 'object';
            } elseif (isset($definition['type'])) {
                $result[$name] = (string) $definition['type'];
            }
        }

        return $result;
    }
```

**Step 5: Run test — confirm green**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit \
  tests/Unit/OpenApi/Domain/OpenApiSchemaEntityReaderTest.php
```

Expected: all tests pass.

**Step 6: Lint + full unit suite**

```bash
make lint-cs-fixer && make lint-phpstan && make lint-deptrac && make test-unit
```

**Step 7: Commit**

```bash
git add src/OpenApi/Domain/OpenApiSchemaEntityReader.php \
        tests/Unit/OpenApi/Domain/OpenApiSchemaEntityReaderTest.php \
        tests/Unit/OpenApi/Domain/Fixtures/openapi-writable-fields.json
git commit -m "Add OpenApiSchemaEntityReader::getWritableFields() with unit tests (#344)"
```

---

### Task 4: Create ItemBuilderCodeGenerator + ItemBuilder.tpl.php

**Files:**
- Create: `src/CodeGenerator/ItemBuilderCodeGenerator.php`
- Create: `src/CodeGenerator/Templates/ItemBuilder.tpl.php`
- Create: `tests/Unit/OpenApi/Domain/ItemBuilderCodeGeneratorTest.php`

**Context**

Mirror of `SelectBuilderCodeGenerator`. Key difference: each field has a PHP type.
Type mapping: `string→string`, `integer→int`, `boolean→bool`, `array→array`, `object→skip`
(object/$ref fields are skipped — they require complex types not derivable from the schema alone).
The generated class has no constructor — developers add required fields manually if needed.

**Step 1: Write failing test**

Create `tests/Unit/OpenApi/Domain/ItemBuilderCodeGeneratorTest.php`:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\CodeGenerator\ItemBuilderCodeGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ItemBuilderCodeGenerator::class)]
class ItemBuilderCodeGeneratorTest extends TestCase
{
    private ItemBuilderCodeGenerator $generator;

    #[\Override]
    protected function setUp(): void
    {
        $this->generator = new ItemBuilderCodeGenerator();
    }

    #[Test]
    #[TestDox('generated class has correct namespace, class name and extends AbstractItemBuilder')]
    public function testGeneratesCorrectClassSignature(): void
    {
        $code = $this->generator->generate(
            'Bitrix24\SDK\Services\Task\Service',
            'TaskItemBuilder',
            ['title' => 'string']
        );

        $this->assertStringContainsString('namespace Bitrix24\SDK\Services\Task\Service;', $code);
        $this->assertStringContainsString('class TaskItemBuilder extends AbstractItemBuilder', $code);
        $this->assertStringContainsString('use Bitrix24\SDK\Services\AbstractItemBuilder;', $code);
    }

    #[Test]
    #[TestDox('string field generates method with string parameter')]
    public function testStringFieldGeneratesTypedMethod(): void
    {
        $code = $this->generator->generate('A\B', 'Foo', ['title' => 'string']);

        $this->assertStringContainsString('public function title(string $title): self', $code);
        $this->assertStringContainsString("\$this->fields['title'] = \$title;", $code);
    }

    #[Test]
    #[TestDox('integer field generates method with int parameter')]
    public function testIntegerFieldGeneratesIntMethod(): void
    {
        $code = $this->generator->generate('A\B', 'Foo', ['count' => 'integer']);

        $this->assertStringContainsString('public function count(int $count): self', $code);
    }

    #[Test]
    #[TestDox('boolean field generates method with bool parameter')]
    public function testBooleanFieldGeneratesBoolMethod(): void
    {
        $code = $this->generator->generate('A\B', 'Foo', ['active' => 'boolean']);

        $this->assertStringContainsString('public function active(bool $active): self', $code);
    }

    #[Test]
    #[TestDox('array field generates method with array parameter')]
    public function testArrayFieldGeneratesArrayMethod(): void
    {
        $code = $this->generator->generate('A\B', 'Foo', ['tags' => 'array']);

        $this->assertStringContainsString('public function tags(array $tags): self', $code);
    }

    #[Test]
    #[TestDox('object ($ref) fields are skipped — not emitted as methods')]
    public function testObjectFieldsAreSkipped(): void
    {
        $code = $this->generator->generate('A\B', 'Foo', ['nested' => 'object', 'title' => 'string']);

        $this->assertStringNotContainsString('public function nested(', $code);
        $this->assertStringContainsString('public function title(', $code);
    }

    #[Test]
    #[TestDox('methods are emitted in alphabetical order for determinism')]
    public function testMethodsAreEmittedAlphabetically(): void
    {
        $code = $this->generator->generate('A\B', 'Foo', ['zzz' => 'string', 'aaa' => 'string']);

        $posAaa = strpos($code, 'function aaa(');
        $posZzz = strpos($code, 'function zzz(');
        $this->assertNotFalse($posAaa);
        $this->assertNotFalse($posZzz);
        $this->assertLessThan($posZzz, $posAaa);
    }
}
```

**Step 2: Run test — confirm failure**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit \
  tests/Unit/OpenApi/Domain/ItemBuilderCodeGeneratorTest.php
```

Expected: FAIL — class does not exist.

**Step 3: Create ItemBuilder.tpl.php**

Create `src/CodeGenerator/Templates/ItemBuilder.tpl.php`:

```php
<?php
/**
 * @var string $namespace
 * @var string $className
 * @var string $operationPath
 * @var array<string, string> $fields  fieldName => phpType
 */
?>
<?= "<?php\n" ?>

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

// This class was automatically generated by the b24-dev:generate-item-builder command.
// Source: OpenAPI schema snapshot (docs/open-api/openapi.json).
// To regenerate, run: php bin/console b24-dev:generate-item-builder <?= $operationPath . "\n\n" ?>
declare(strict_types=1);

namespace <?= $namespace ?>;

use Bitrix24\SDK\Services\AbstractItemBuilder;

class <?= $className ?> extends AbstractItemBuilder
{
<?php foreach ($fields as $fieldName => $phpType): ?>
    public function <?= $fieldName ?>(<?= $phpType ?> $<?= $fieldName ?>): self
    {
        $this->fields['<?= $fieldName ?>'] = $<?= $fieldName ?>;
        return $this;
    }

<?php endforeach ?>}
```

**Step 4: Create ItemBuilderCodeGenerator.php**

Create `src/CodeGenerator/ItemBuilderCodeGenerator.php`:

```php
<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\CodeGenerator;

readonly class ItemBuilderCodeGenerator
{
    /** @var array<string, string> */
    private const array TYPE_MAP = [
        'string'  => 'string',
        'integer' => 'int',
        'boolean' => 'bool',
        'array'   => 'array',
    ];

    private string $templatePath;

    public function __construct(?string $templatePath = null)
    {
        $this->templatePath = $templatePath ?? __DIR__ . '/Templates/ItemBuilder.tpl.php';
    }

    /**
     * Generates a PHP source file for an *ItemBuilder class.
     *
     * Methods are emitted in alphabetical order for determinism.
     * Fields with type 'object' ($ref entries) are skipped.
     *
     * @param array<string, string> $writableFields  fieldName → openAPIType from getWritableFields()
     */
    public function generate(
        string $namespace,
        string $className,
        array $writableFields,
        string $operationPath = ''
    ): string {
        $fields = $this->mapToPhpTypes($writableFields);

        ob_start();
        extract([
            'namespace'     => $namespace,
            'className'     => $className,
            'fields'        => $fields,
            'operationPath' => $operationPath,
        ]);
        include $this->templatePath;

        return (string) ob_get_clean();
    }

    /**
     * Maps OpenAPI types to PHP types and sorts alphabetically.
     * Fields with unsupported types (e.g. 'object') are skipped.
     *
     * @param array<string, string> $writableFields
     * @return array<string, string>
     */
    private function mapToPhpTypes(array $writableFields): array
    {
        $result = [];
        foreach ($writableFields as $name => $openApiType) {
            $phpType = self::TYPE_MAP[$openApiType] ?? null;
            if ($phpType === null) {
                continue; // skip 'object' and any unknown types
            }
            $result[$name] = $phpType;
        }
        ksort($result);
        return $result;
    }
}
```

**Step 5: Run test — confirm green**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit \
  tests/Unit/OpenApi/Domain/ItemBuilderCodeGeneratorTest.php
```

Expected: all tests pass.

**Step 6: Lint + full unit suite**

```bash
make lint-cs-fixer && make lint-phpstan && make lint-deptrac && make test-unit
```

**Step 7: Commit**

```bash
git add src/CodeGenerator/ItemBuilderCodeGenerator.php \
        src/CodeGenerator/Templates/ItemBuilder.tpl.php \
        tests/Unit/OpenApi/Domain/ItemBuilderCodeGeneratorTest.php
git commit -m "Add ItemBuilderCodeGenerator and ItemBuilder.tpl.php template (#344)"
```

---

### Task 5: Create GenerateItemBuilderCommand + register in bin/console

**Files:**
- Create: `src/Infrastructure/Console/Commands/Generator/GenerateItemBuilderCommand.php`
- Modify: `bin/console`

**Context**

Mirror of `GenerateSelectBuilderCommand`. Argument is the operation path (e.g. `/tasks.task.add`).
Options: `--namespace`, `--class-name`, `--output`, `--schema-file`.
Namespace + class-name defaults are derived from the operation path:
`/tasks.task.add` → namespace `Bitrix24\SDK\Services\Task\Service`, class `TaskItemBuilder`.

**Step 1: Create GenerateItemBuilderCommand.php**

Create `src/Infrastructure/Console/Commands/Generator/GenerateItemBuilderCommand.php`:

```php
<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Infrastructure\Console\Commands\Generator;

use Bitrix24\SDK\CodeGenerator\ItemBuilderCodeGenerator;
use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

#[AsCommand(
    name: 'b24-dev:generate-item-builder',
    description: 'Generate an ItemBuilder class for a v3 entity from the OpenAPI schema',
    hidden: false
)]
class GenerateItemBuilderCommand extends Command
{
    private const string OPERATION_PATH = 'operation-path';
    private const string NAMESPACE      = 'namespace';
    private const string CLASS_NAME     = 'class-name';
    private const string OUTPUT         = 'output';
    private const string SCHEMA_FILE    = 'schema-file';

    public function __construct(
        private readonly OpenApiSchemaEntityReader $entityReader,
        private readonly ItemBuilderCodeGenerator  $codeGenerator,
        private readonly Filesystem                $filesystem,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp(
                'Reads the checked-in OpenAPI snapshot, extracts writable fields from the ' .
                'requestBody of the given operation, and generates a ready-to-use *ItemBuilder ' .
                'PHP class. Object/$ref fields are skipped. Date fields use plain "string" — ' .
                'upgrade to CarbonInterface manually as needed.'
            )
            ->addArgument(
                self::OPERATION_PATH,
                InputArgument::REQUIRED,
                'Operation path from the OpenAPI schema, e.g. /tasks.task.add'
            )
            ->addOption(
                self::NAMESPACE,
                null,
                InputOption::VALUE_REQUIRED,
                'Target PHP namespace for the generated class (default: derived from operation path)'
            )
            ->addOption(
                self::CLASS_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Class name for the generated builder (default: derived from operation path)'
            )
            ->addOption(
                self::OUTPUT,
                null,
                InputOption::VALUE_REQUIRED,
                'Output file path; prints to stdout when omitted'
            )
            ->addOption(
                self::SCHEMA_FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the OpenAPI schema snapshot',
                'docs/open-api/openapi.json'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $schemaFile    = trim((string) $input->getOption(self::SCHEMA_FILE));
            $operationPath = trim((string) $input->getArgument(self::OPERATION_PATH));
            $fields        = $this->entityReader->getWritableFields($schemaFile, $operationPath);

            $namespace = $this->resolveNamespace($input, $operationPath);
            $className = $this->resolveClassName($input, $operationPath);

            $code = $this->codeGenerator->generate($namespace, $className, $fields, $operationPath);

            $outputPath = $input->getOption(self::OUTPUT);
            if (is_string($outputPath) && $outputPath !== '') {
                $this->filesystem->dumpFile($outputPath, $code);
                $io->success(sprintf('Generated %s → %s', $className, $outputPath));
            } else {
                $output->write($code);
            }

            return self::SUCCESS;
        } catch (InvalidArgumentException | RuntimeException $e) {
            $io->error($e->getMessage());
            return self::INVALID;
        } catch (Throwable $e) {
            $io->error(sprintf('Runtime error: %s', $e->getMessage()));
            return self::FAILURE;
        }
    }

    private function resolveNamespace(InputInterface $input, string $operationPath): string
    {
        $ns = $input->getOption(self::NAMESPACE);
        if (is_string($ns) && $ns !== '') {
            return $ns;
        }

        // /tasks.task.add → module=tasks → Bitrix24\SDK\Services\Tasks\Service
        $parts = explode('.', ltrim($operationPath, '/'));
        $module = isset($parts[0]) ? ucfirst($parts[0]) : 'Unknown';

        return sprintf('Bitrix24\\SDK\\Services\\%s\\Service', $module);
    }

    private function resolveClassName(InputInterface $input, string $operationPath): string
    {
        $cn = $input->getOption(self::CLASS_NAME);
        if (is_string($cn) && $cn !== '') {
            return $cn;
        }

        // /tasks.task.add → entity=task → TaskItemBuilder
        $parts = explode('.', ltrim($operationPath, '/'));
        $entity = isset($parts[1]) ? ucfirst($parts[1]) : 'Entity';

        return $entity . 'ItemBuilder';
    }
}
```

**Step 2: Register in bin/console**

Open `bin/console`. Find the block where `GenerateSelectBuilderCommand` is registered:

```php
$application->addCommand(
    new GenerateSelectBuilderCommand(
        new OpenApiSchemaEntityReader(new Symfony\Component\Filesystem\Filesystem()),
        new SelectBuilderCodeGenerator(),
        new Symfony\Component\Filesystem\Filesystem()
    )
);
```

Add the `use` import at the top of the file (with other use statements):
```php
use Bitrix24\SDK\Infrastructure\Console\Commands\Generator\GenerateItemBuilderCommand;
use Bitrix24\SDK\CodeGenerator\ItemBuilderCodeGenerator;
```

Add the new command registration immediately after the `GenerateSelectBuilderCommand` block:
```php
$application->addCommand(
    new GenerateItemBuilderCommand(
        new OpenApiSchemaEntityReader(new Symfony\Component\Filesystem\Filesystem()),
        new ItemBuilderCodeGenerator(),
        new Symfony\Component\Filesystem\Filesystem()
    )
);
```

**Step 3: Smoke-test the command**

```bash
docker-compose run --rm php-cli php bin/console b24-dev:generate-item-builder /tasks.task.add
```

Expected: PHP class code printed to stdout without errors.
Spot-check the output: it should contain `class TaskItemBuilder extends AbstractItemBuilder`,
methods like `public function title(string $title): self`, etc.

**Step 4: Lint + full unit suite**

```bash
make lint-cs-fixer && make lint-phpstan && make lint-deptrac && make test-unit
```

**Step 5: Commit**

```bash
git add src/Infrastructure/Console/Commands/Generator/GenerateItemBuilderCommand.php bin/console
git commit -m "Add GenerateItemBuilderCommand (b24-dev:generate-item-builder) (#344)"
```

---

### Task 6: Expand TaskItemBuilder with all writable fields

**Files:**
- Modify: `src/Services/Task/Service/TaskItemBuilder.php`

**Context**

The current `TaskItemBuilder` has 10 methods. The OpenAPI schema for `/tasks.task.add` has 78
writable fields. After filtering out `object` types (skipped) and deriving PHP types, there are
~68 additional methods to add. The generator output serves as the canonical field list.
The constructor, `createFromTask()` factory, and `CarbonInterface` date types must be preserved.

**Step 1: Generate the reference output**

```bash
docker-compose run --rm php-cli php bin/console b24-dev:generate-item-builder \
  /tasks.task.add \
  --namespace="Bitrix24\SDK\Services\Task\Service" \
  --class-name="TaskItemBuilder"
```

Copy the stdout output to a scratch file. This is the complete list of all field methods.

**Step 2: Identify missing methods**

Compare the generated output with the current `TaskItemBuilder`. Methods already present:
`title`, `description`, `deadline`, `needsControl`, `startPlan`, `endPlan`, `groupId`,
`stageId`, `creatorId`, `responsibleId`.

All other methods in the generated output are MISSING from `TaskItemBuilder`.

**Step 3: Add missing methods to TaskItemBuilder**

Open `src/Services/Task/Service/TaskItemBuilder.php`. After the `responsibleId()` method
(last existing method), insert all missing methods from the generator output.

Rules:
- Keep `string` for the following date fields (they represent ISO 8601 strings in the API):
  `created`, `statusChanged`, `started`, `changed`, `closed`, `activity`, `exchangeModified`,
  `maxDeadlineChangeDate`.
  EXCEPTION: `deadline`, `startPlan`, `endPlan` already use `CarbonInterface` — keep as-is.
- Keep all other generated PHP types (`int`, `bool`, `array`, `string`) as generated.
- Keep the existing constructor and `createFromTask()` method intact.
- Maintain alphabetical order within the file (optional but preferred for readability).

**Step 4: Run full linter + unit suite**

```bash
make lint-cs-fixer && make lint-rector && make lint-phpstan && make lint-deptrac && make test-unit
```

Expected: all green. If `lint-cs-fixer` reports issues, run `make lint-cs-fixer-fix`.

**Step 5: Commit**

```bash
git add src/Services/Task/Service/TaskItemBuilder.php
git commit -m "Expand TaskItemBuilder to cover all writable fields from OpenAPI schema (#344)"
```

---

### Task 7: Update CHANGELOG.md

**Files:**
- Modify: `CHANGELOG.md`

**Step 1: Find the insertion point**

Open `CHANGELOG.md`. Find `## 3.1.0 Unreleased` → `### Added`.
The last entry in `### Added` is currently:

```markdown
- Added `TaskField` service for `tasks.task.field.get` and `tasks.task.field.list` support ([#395](...))
```

Insert AFTER that line:

```markdown
- Added `AbstractItemBuilder::getSupportedFieldNames()`: returns a sorted list of writable field names via reflection over 1-parameter public instance methods in the concrete subclass ([#344](https://github.com/bitrix24/b24phpsdk/issues/344))
- Added `OpenApiSchemaEntityReader::getWritableFields()`: reads writable field names and OpenAPI types from `requestBody` of any operation path in the schema snapshot ([#344](https://github.com/bitrix24/b24phpsdk/issues/344))
- Added `ItemBuilderCodeGenerator` and `ItemBuilder.tpl.php`: generates typed `*ItemBuilder` PHP classes from OpenAPI writable-field maps, mapping `string/integer/boolean/array` to PHP types and skipping `$ref` fields ([#344](https://github.com/bitrix24/b24phpsdk/issues/344))
- Added `b24-dev:generate-item-builder` console command: generates a `*ItemBuilder` class for any v3 `*.add` operation from the OpenAPI schema snapshot ([#344](https://github.com/bitrix24/b24phpsdk/issues/344))
- Expanded `TaskItemBuilder` to cover all writable fields from `tasks.task.add` OpenAPI schema; `Task::add()` and `Task::update()` now use `instanceof ItemBuilderInterface` internally while keeping `array|TaskItemBuilder` in the method signature for IDE discoverability ([#344](https://github.com/bitrix24/b24phpsdk/issues/344))
```

**Step 2: Commit**

```bash
git add CHANGELOG.md
git commit -m "Update CHANGELOG.md for #344"
```

---

### Task 8: Final quality gate, push, and PR

**Step 1: Run all linters and unit tests (final gate)**

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

All must be green before proceeding.

**Step 2: Push the branch**

```bash
git push -u origin feature/344-add-item-builder-interface
```

**Step 3: Read PR template**

```bash
cat .github/PULL_REQUEST_TEMPLATE.md
```

**Step 4: Create PR**

```bash
gh pr create \
  --repo bitrix24/b24phpsdk \
  --title "Add ItemBuilder infrastructure: generator, getSupportedFieldNames, expand TaskItemBuilder" \
  --base v3-dev \
  --assignee mesilov \
  --body "$(cat <<'EOF'
## Summary

- Fixed `instanceof TaskItemBuilder` → `instanceof ItemBuilderInterface` in `Task::add()` and `Task::update()`
- Added `AbstractItemBuilder::getSupportedFieldNames()`: reflection-based field name discovery for concrete builders
- Added `OpenApiSchemaEntityReader::getWritableFields()`: reads writable fields + types from OpenAPI requestBody
- Added `ItemBuilderCodeGenerator` + `ItemBuilder.tpl.php`: generates typed `*ItemBuilder` classes from schema
- Added `b24-dev:generate-item-builder` console command (mirrors `b24-dev:generate-select-builder`)
- Expanded `TaskItemBuilder` from 10 to all writable fields defined in the OpenAPI schema

## Test plan

- [x] `make lint-cs-fixer` — passed
- [x] `make lint-rector` — passed
- [x] `make lint-phpstan` — passed
- [x] `make lint-deptrac` — passed
- [x] `make test-unit` — passed

Closes #344

🤖 Generated with [Claude Code](https://claude.ai/claude-code)
EOF
)"
```

**Step 5: Output the PR URL** and confirm to the user.
