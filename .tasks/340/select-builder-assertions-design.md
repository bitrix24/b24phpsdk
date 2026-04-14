# Design: SelectBuilder OpenAPI Schema Assertions

## Context

The current `SelectBuilderOaSchemaCoverageTrait` hides test methods inside a trait and forces
test classes to implement abstract methods (`getItemResultClass`, `getSelectBuilder`) as a
contract. This is hard to read and inconsistent with how the rest of the codebase verifies
result item classes (via explicit calls in `CustomBitrix24Assertions`).

Additionally, the `Oa` prefix used across several classes is ambiguous — `OpenApi` is the
correct, unambiguous term used in the rest of the project.

---

## Goals

1. Replace the hidden-test-method trait with a static assertion class following PHPUnit's
   `Assert` extension pattern.
2. Rename `Oa` → `OpenApi` across all affected classes and attributes.
3. Make SelectBuilder tests fully explicit: no abstract methods, no hidden `#[Test]` methods.

---

## Design

### New class: `tests/CustomAssertions/SelectBuilderAssertions.php`

Extends `PHPUnit\Framework\Assert` — the standard PHPUnit pattern for custom assertion
libraries. Uses only static methods, so no `use` trait is needed in test classes.

```php
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
     * (resolved via #[OpenApiEntity] on $resultClass) is covered
     * by allSystemFields()->buildSelect() on $builder.
     *
     * @param class-string $resultClass  *ItemResult with #[OpenApiEntity] attribute
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
                    $field, $entityKey, $builder::class, $entityKey
                )
            );
        }
    }
}
```

### Resulting test class

```php
#[CoversClass(TaskItemSelectBuilder::class)]
class TaskItemSelectBuilderTest extends TestCase
{
    #[Test]
    #[TestDox('TaskItemSelectBuilder covers all fields from OpenAPI schema')]
    public function testCoversAllOpenApiSchemaFields(): void
    {
        SelectBuilderAssertions::assertCoversOpenApiSchema(
            new TaskItemSelectBuilder(),
            TaskItemResult::class
        );
    }
}
```

No `use` statements for traits, no abstract method contract. A plain static call.

---

## Renames: Oa → OpenApi

| Old name | New name | File |
|---|---|---|
| `OaEntity` | `OpenApiEntity` | `src/Attributes/OaEntity.php` → `OpenApiEntity.php` |
| `OaSchemaEntityReader` | `OpenApiSchemaEntityReader` | `src/OpenApi/Domain/OaSchemaEntityReader.php` → `OpenApiSchemaEntityReader.php` |
| `#[OaEntity(...)]` usages | `#[OpenApiEntity(...)]` | `TaskItemResult.php`, `EventLogItemResult.php` |
| `assertCoversOaSchema` | `assertCoversOpenApiSchema` | new class, no migration needed |
| `OaEntityAttributeTest` | `OpenApiEntityAttributeTest` | `tests/Unit/Attributes/` |
| `OaSchemaEntityReaderTest` | `OpenApiSchemaEntityReaderTest` | `tests/Unit/OpenApi/Domain/` |

---

## Files to Create

- `tests/CustomAssertions/SelectBuilderAssertions.php`
- `src/Attributes/OpenApiEntity.php`

## Files to Rename / Rewrite

- `src/Attributes/OaEntity.php` → delete after creating `OpenApiEntity.php`
- `src/OpenApi/Domain/OaSchemaEntityReader.php` → rename class to `OpenApiSchemaEntityReader`
- `tests/Unit/Attributes/OaEntityAttributeTest.php` → rename to `OpenApiEntityAttributeTest.php`
- `tests/Unit/OpenApi/Domain/OaSchemaEntityReaderTest.php` → rename class + usages

## Files to Delete

- `tests/Unit/Services/SelectBuilderOaSchemaCoverageTrait.php`

## Files to Update (usages)

- `src/Services/Task/Result/TaskItemResult.php` — `use OpenApiEntity`, `#[OpenApiEntity(...)]`
- `src/Services/Main/Result/EventLogItemResult.php` — `use OpenApiEntity`, `#[OpenApiEntity(...)]`
- `tests/Unit/Services/Task/Service/TaskItemSelectBuilderTest.php` — remove trait, static call
- `tests/Unit/Services/Main/Service/EventLogSelectBuilderTest.php` — remove trait, static call
- `bin/console` — update `OaSchemaEntityReader` → `OpenApiSchemaEntityReader`
- `src/Infrastructure/Console/Commands/Generator/GenerateSelectBuilderCommand.php` — update reader class reference

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```
