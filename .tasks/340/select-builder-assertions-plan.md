# SelectBuilder OpenAPI Schema Assertions — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Replace the hidden-test-method trait `SelectBuilderOaSchemaCoverageTrait` with a static
assertion class `SelectBuilderAssertions extends Assert`, and rename all `Oa` prefixes to `OpenApi`.

**Architecture:** Four sequential tasks — (1) rename `OaEntity` → `OpenApiEntity`;
(2) rename `OaSchemaEntityReader` → `OpenApiSchemaEntityReader`; (3) create
`SelectBuilderAssertions` with a static `assertCoversOpenApiSchema()` method and its unit test;
(4) rewrite `*SelectBuilderTest` classes to use the static call and delete the old trait.
Each task follows RED → GREEN → commit.

**Tech Stack:** PHP 8.4, PHPUnit 12, `PHPUnit\Framework\Assert`, Symfony Filesystem,
`OpenApiSchemaEntityReader`, PHP 8 Attributes (`#[OpenApiEntity]`).

---

### Task 1: Rename OaEntity → OpenApiEntity

**Files:**
- Create: `src/Attributes/OpenApiEntity.php`
- Delete: `src/Attributes/OaEntity.php`
- Modify: `tests/Unit/Attributes/OaEntityAttributeTest.php`
- Modify: `src/Services/Task/Result/TaskItemResult.php`
- Modify: `src/Services/Main/Result/EventLogItemResult.php`

**Step 1: Update the test file — rename all references**

In `tests/Unit/Attributes/OaEntityAttributeTest.php`:
- Rename file class to `OpenApiEntityAttributeTest`
- Replace `use Bitrix24\SDK\Attributes\OaEntity;` → `use Bitrix24\SDK\Attributes\OpenApiEntity;`
- Replace `#[CoversClass(OaEntity::class)]` → `#[CoversClass(OpenApiEntity::class)]`
- Replace every `new OaEntity(` → `new OpenApiEntity(`
- Replace every `OaEntity::class` → `OpenApiEntity::class`
- Replace every `#[OaEntity(` → `#[OpenApiEntity(`

**Step 2: Run the test to verify it fails (RED)**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit tests/Unit/Attributes/OaEntityAttributeTest.php
```

Expected: FAIL — `Class "Bitrix24\SDK\Attributes\OpenApiEntity" not found`

**Step 3: Create `src/Attributes/OpenApiEntity.php`**

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

namespace Bitrix24\SDK\Attributes;

use Attribute;

/**
 * Links a *ItemResult class to its OpenAPI v3 entity key and related builder classes.
 *
 * Usage:
 *
 *   #[OpenApiEntity(
 *       entityKey:     'bitrix.tasks.taskdto',
 *       selectBuilder: TaskItemSelectBuilder::class,
 *       itemBuilder:   TaskItemBuilder::class,
 *   )]
 *   class TaskItemResult extends AbstractItem { ... }
 *
 * - entityKey:     key from components.schemas in docs/open-api/openapi.json
 * - selectBuilder: class that builds the select[] array for get/list calls (nullable until created)
 * - itemBuilder:   class that builds the fields[] array for add/update calls (nullable until created)
 */
#[Attribute(Attribute::TARGET_CLASS)]
readonly class OpenApiEntity
{
    public function __construct(
        public string  $entityKey,
        public ?string $selectBuilder = null,
        public ?string $itemBuilder = null,
    ) {
    }
}
```

**Step 4: Delete `src/Attributes/OaEntity.php`**

```bash
rm src/Attributes/OaEntity.php
```

**Step 5: Run the test to verify it passes (GREEN)**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit tests/Unit/Attributes/OaEntityAttributeTest.php
```

Expected: OK (3 tests)

**Step 6: Update `TaskItemResult.php`**

In `src/Services/Task/Result/TaskItemResult.php`:
- Replace `use Bitrix24\SDK\Attributes\OaEntity;` → `use Bitrix24\SDK\Attributes\OpenApiEntity;`
- Replace `#[OaEntity(` → `#[OpenApiEntity(`

**Step 7: Update `EventLogItemResult.php`**

In `src/Services/Main/Result/EventLogItemResult.php`:
- Replace `use Bitrix24\SDK\Attributes\OaEntity;` → `use Bitrix24\SDK\Attributes\OpenApiEntity;`
- Replace `#[OaEntity(` → `#[OpenApiEntity(`

**Step 8: Run unit tests to verify everything is GREEN**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit --testsuite unit_tests
```

Expected: all tests pass, 0 failures.

**Step 9: Commit**

```bash
git add src/Attributes/OpenApiEntity.php src/Attributes/OaEntity.php \
        src/Services/Task/Result/TaskItemResult.php \
        src/Services/Main/Result/EventLogItemResult.php \
        tests/Unit/Attributes/OaEntityAttributeTest.php
git commit -m "Rename OaEntity attribute to OpenApiEntity (#340)"
```

---

### Task 2: Rename OaSchemaEntityReader → OpenApiSchemaEntityReader

**Files:**
- Modify: `src/OpenApi/Domain/OaSchemaEntityReader.php` (rename class inside)
- Modify: `tests/Unit/OpenApi/Domain/OaSchemaEntityReaderTest.php`
- Modify: `src/Infrastructure/Console/Commands/Generator/GenerateSelectBuilderCommand.php`
- Modify: `bin/console`
- Modify: `tests/Unit/Services/SelectBuilderOaSchemaCoverageTrait.php`

**Step 1: Update the test file — rename all references**

In `tests/Unit/OpenApi/Domain/OaSchemaEntityReaderTest.php`:
- Rename file class to `OpenApiSchemaEntityReaderTest`
- Replace `use Bitrix24\SDK\OpenApi\Domain\OaSchemaEntityReader;` →
  `use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;`
- Replace `#[CoversClass(OaSchemaEntityReader::class)]` →
  `#[CoversClass(OpenApiSchemaEntityReader::class)]`
- Replace every `OaSchemaEntityReader` → `OpenApiSchemaEntityReader` (property, setUp, etc.)

**Step 2: Run the test to verify it fails (RED)**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/OaSchemaEntityReaderTest.php
```

Expected: FAIL — `Class "Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader" not found`

**Step 3: Rename the class inside `src/OpenApi/Domain/OaSchemaEntityReader.php`**

Change `readonly class OaSchemaEntityReader` → `readonly class OpenApiSchemaEntityReader`

Do NOT rename the file — PHP class names do not require matching filenames, but for consistency
also rename the file:

```bash
git mv src/OpenApi/Domain/OaSchemaEntityReader.php src/OpenApi/Domain/OpenApiSchemaEntityReader.php
```

**Step 4: Run the test to verify it passes (GREEN)**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/OaSchemaEntityReaderTest.php
```

Expected: OK (all tests pass)

**Step 5: Update `GenerateSelectBuilderCommand.php`**

In `src/Infrastructure/Console/Commands/Generator/GenerateSelectBuilderCommand.php`:
- Replace `use Bitrix24\SDK\OpenApi\Domain\OaSchemaEntityReader;` →
  `use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;`
- Replace every `OaSchemaEntityReader` → `OpenApiSchemaEntityReader`

**Step 6: Update `bin/console`**

In `bin/console`:
- Replace `use Bitrix24\SDK\OpenApi\Domain\OaSchemaEntityReader;` →
  `use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;`
- Replace `new OaSchemaEntityReader(` → `new OpenApiSchemaEntityReader(` (line ~105)

**Step 7: Update `SelectBuilderOaSchemaCoverageTrait.php`**

In `tests/Unit/Services/SelectBuilderOaSchemaCoverageTrait.php`:
- Replace `use Bitrix24\SDK\OpenApi\Domain\OaSchemaEntityReader;` →
  `use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;`
- Replace `new OaSchemaEntityReader(` → `new OpenApiSchemaEntityReader(`

**Step 8: Run unit tests**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit --testsuite unit_tests
```

Expected: all tests pass.

**Step 9: Commit**

```bash
git add src/OpenApi/Domain/OpenApiSchemaEntityReader.php \
        src/OpenApi/Domain/OaSchemaEntityReader.php \
        src/Infrastructure/Console/Commands/Generator/GenerateSelectBuilderCommand.php \
        bin/console \
        tests/Unit/OpenApi/Domain/OaSchemaEntityReaderTest.php \
        tests/Unit/Services/SelectBuilderOaSchemaCoverageTrait.php
git commit -m "Rename OaSchemaEntityReader to OpenApiSchemaEntityReader (#340)"
```

---

### Task 3: Create SelectBuilderAssertions

**Files:**
- Create: `tests/CustomAssertions/SelectBuilderAssertions.php`
- Create: `tests/Unit/CustomAssertions/SelectBuilderAssertionsTest.php`

**Step 1: Write the failing test**

Create `tests/Unit/CustomAssertions/SelectBuilderAssertionsTest.php`:

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

namespace Bitrix24\SDK\Tests\Unit\CustomAssertions;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Services\AbstractSelectBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\SelectBuilderAssertions;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(SelectBuilderAssertions::class)]
class SelectBuilderAssertionsTest extends TestCase
{
    #[Test]
    #[TestDox('passes when builder covers all schema fields declared in #[OpenApiEntity]')]
    public function testPassesWhenBuilderCoversAllSchemaFields(): void
    {
        // Uses real TaskItemSelectBuilder + TaskItemResult which carry
        // a real #[OpenApiEntity] and real OA schema — a full smoke test.
        SelectBuilderAssertions::assertCoversOpenApiSchema(
            new \Bitrix24\SDK\Services\Task\Service\TaskItemSelectBuilder(),
            \Bitrix24\SDK\Services\Task\Result\TaskItemResult::class
        );
    }

    #[Test]
    #[TestDox('fails when result class has no #[OpenApiEntity] attribute')]
    public function testFailsWhenNoOpenApiEntityAttribute(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageMatches('/has no #\[OpenApiEntity\] attribute/');

        $classWithNoAttr = new class extends \Bitrix24\SDK\Core\Result\AbstractItem {};

        SelectBuilderAssertions::assertCoversOpenApiSchema(
            new \Bitrix24\SDK\Services\Task\Service\TaskItemSelectBuilder(),
            $classWithNoAttr::class
        );
    }

    #[Test]
    #[TestDox('fails when builder does not cover a field from the schema')]
    public function testFailsWhenBuilderMissesAField(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageMatches('/is not covered by/');

        // Anonymous class with #[OpenApiEntity] pointing to a real entity.
        // The builder returns only 'id' — will be missing all other fields.
        $resultClass = new
        #[OpenApiEntity('bitrix.tasks.taskdto')]
        class extends \Bitrix24\SDK\Core\Result\AbstractItem {};

        $emptyBuilder = new class extends AbstractSelectBuilder {
            public function __construct() {
                $this->select[] = 'id';
            }
        };

        SelectBuilderAssertions::assertCoversOpenApiSchema($emptyBuilder, $resultClass::class);
    }
}
```

**Step 2: Run the test to verify it fails (RED)**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit tests/Unit/CustomAssertions/SelectBuilderAssertionsTest.php
```

Expected: FAIL — `Class "Bitrix24\SDK\Tests\CustomAssertions\SelectBuilderAssertions" not found`

**Step 3: Implement `SelectBuilderAssertions`**

Create `tests/CustomAssertions/SelectBuilderAssertions.php`:

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

namespace Bitrix24\SDK\Tests\CustomAssertions;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\OpenApi\Domain\OpenApiSchemaEntityReader;
use Bitrix24\SDK\Services\AbstractSelectBuilder;
use PHPUnit\Framework\Assert;
use Symfony\Component\Filesystem\Filesystem;

class SelectBuilderAssertions extends Assert
{
    private const string SCHEMA_FILE = 'docs/open-api/openapi.json';

    /**
     * Assert that every field from the OpenAPI schema entity
     * (resolved via #[OpenApiEntity] on $resultClass) is selectable
     * via allSystemFields()->buildSelect() on $builder.
     *
     * @param class-string $resultClass  *ItemResult annotated with #[OpenApiEntity]
     */
    public static function assertCoversOpenApiSchema(
        AbstractSelectBuilder $builder,
        string $resultClass
    ): void {
        $attrs = (new \ReflectionClass($resultClass))->getAttributes(OpenApiEntity::class);

        self::assertNotEmpty(
            $attrs,
            sprintf('Class %s has no #[OpenApiEntity] attribute', $resultClass)
        );

        /** @var OpenApiEntity $openApiEntity */
        $openApiEntity = $attrs[0]->newInstance();
        $entityKey = $openApiEntity->entityKey;

        $schemaFields = (new OpenApiSchemaEntityReader(new Filesystem()))
            ->getSelectableFields(self::SCHEMA_FILE, $entityKey);

        $selected = $builder->allSystemFields()->buildSelect();

        foreach ($schemaFields as $field) {
            self::assertContains(
                $field,
                $selected,
                sprintf(
                    'field «%s» from OpenAPI schema «%s» is not covered by %s — ' .
                    'run: php bin/console b24-dev:generate-select-builder %s',
                    $field,
                    $entityKey,
                    $builder::class,
                    $entityKey
                )
            );
        }
    }
}
```

**Step 4: Run the test to verify it passes (GREEN)**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit tests/Unit/CustomAssertions/SelectBuilderAssertionsTest.php
```

Expected: OK (3 tests)

**Step 5: Commit**

```bash
git add tests/CustomAssertions/SelectBuilderAssertions.php \
        tests/Unit/CustomAssertions/SelectBuilderAssertionsTest.php
git commit -m "Add SelectBuilderAssertions with assertCoversOpenApiSchema() (#340)"
```

---

### Task 4: Rewrite SelectBuilder tests and delete the old trait

**Files:**
- Modify: `tests/Unit/Services/Task/Service/TaskItemSelectBuilderTest.php`
- Modify: `tests/Unit/Services/Main/Service/EventLogSelectBuilderTest.php`
- Delete: `tests/Unit/Services/SelectBuilderOaSchemaCoverageTrait.php`

**Step 1: Rewrite `TaskItemSelectBuilderTest.php`**

Replace the entire file content:

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

namespace Bitrix24\SDK\Tests\Unit\Services\Task\Service;

use Bitrix24\SDK\Services\Task\Result\TaskItemResult;
use Bitrix24\SDK\Services\Task\Service\TaskItemSelectBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\SelectBuilderAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskItemSelectBuilder::class)]
class TaskItemSelectBuilderTest extends TestCase
{
    #[Test]
    #[TestDox('TaskItemSelectBuilder covers all fields from OpenAPI schema for bitrix.tasks.taskdto')]
    public function testCoversAllOpenApiSchemaFields(): void
    {
        SelectBuilderAssertions::assertCoversOpenApiSchema(
            new TaskItemSelectBuilder(),
            TaskItemResult::class
        );
    }
}
```

**Step 2: Rewrite `EventLogSelectBuilderTest.php`**

Replace the entire file content:

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

namespace Bitrix24\SDK\Tests\Unit\Services\Main\Service;

use Bitrix24\SDK\Services\Main\Result\EventLogItemResult;
use Bitrix24\SDK\Services\Main\Service\EventLogSelectBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\SelectBuilderAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventLogSelectBuilder::class)]
class EventLogSelectBuilderTest extends TestCase
{
    #[Test]
    #[TestDox('EventLogSelectBuilder covers all fields from OpenAPI schema for bitrix.main.eventlogdto')]
    public function testCoversAllOpenApiSchemaFields(): void
    {
        SelectBuilderAssertions::assertCoversOpenApiSchema(
            new EventLogSelectBuilder(),
            EventLogItemResult::class
        );
    }
}
```

**Step 3: Delete the old trait**

```bash
rm tests/Unit/Services/SelectBuilderOaSchemaCoverageTrait.php
```

**Step 4: Run the full unit test suite**

```bash
docker-compose run --rm php-cli vendor/bin/phpunit --testsuite unit_tests
```

Expected: all tests pass, 0 failures, 0 warnings.

**Step 5: Run the full quality gate**

```bash
docker-compose run --rm php-cli vendor/bin/php-cs-fixer check --diff
docker-compose run --rm php-cli vendor/bin/rector process --dry-run
docker-compose run --rm php-cli vendor/bin/phpstan analyse --memory-limit=1G
docker-compose run --rm php-cli vendor/bin/deptrac analyse --no-progress
```

Fix any issues found before proceeding.

**Step 6: Commit**

```bash
git add tests/Unit/Services/Task/Service/TaskItemSelectBuilderTest.php \
        tests/Unit/Services/Main/Service/EventLogSelectBuilderTest.php \
        tests/Unit/Services/SelectBuilderOaSchemaCoverageTrait.php
git commit -m "Replace SelectBuilderOaSchemaCoverageTrait with SelectBuilderAssertions (#340)"
```
