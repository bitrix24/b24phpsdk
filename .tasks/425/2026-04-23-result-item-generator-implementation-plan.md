# ResultItem Generator Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace direct `ResultItem` generation from OpenAPI/live API with a staged `b24-dev:result-item-generator` pipeline that builds a reviewable payload in `.tasks/<issue-id>/<method>/`, verifies it against live API, applies safe updates, and generates code only from the canonical payload.

**Architecture:** Introduce a canonical YAML payload model for `ResultItem`, plus a single orchestration command with `build`, `verify`, `apply`, `generate`, and `all` stages. `OpenAPI` remains the primary source, `bitrix24/b24restdocs` markdown becomes the only fallback source for building payloads, and live API is restricted to verification and safe enrichment rather than generation-time inference.

**Tech Stack:** Symfony Console, Symfony Filesystem, Symfony Yaml, existing OpenAPI reader, existing Bitrix24 SDK core/webhook infrastructure, PHPUnit 12.

---

### Task 1: Replace the command entrypoint with staged orchestration

**Files:**
- Create: `src/Infrastructure/Console/Commands/Generator/BranchIssueIdResolver.php`
- Create: `src/OpenApi/Domain/ResultItemTaskPathResolver.php`
- Create: `src/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommand.php`
- Create: `tests/Unit/Infrastructure/Console/Commands/Generator/BranchIssueIdResolverTest.php`
- Create: `tests/Unit/OpenApi/Domain/ResultItemTaskPathResolverTest.php`
- Delete: `src/Infrastructure/Console/Commands/Generator/GenerateResultItemCommand.php`
- Modify: `bin/console`
- Test: `tests/Unit/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommandTest.php`

- [ ] **Step 1: Write the failing test for branch issue id extraction**

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Infrastructure\Console\Commands\Generator;

use Bitrix24\SDK\Infrastructure\Console\Commands\Generator\BranchIssueIdResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BranchIssueIdResolverTest extends TestCase
{
    #[Test]
    public function itExtractsIssueIdFromFeatureBranch(): void
    {
        $resolver = new BranchIssueIdResolver();

        self::assertSame('425', $resolver->resolve('feature/425-add-im-dialog-service'));
    }

    #[Test]
    public function itExtractsIssueIdFromBugfixBranch(): void
    {
        $resolver = new BranchIssueIdResolver();

        self::assertSame('512', $resolver->resolve('bugfix/512-fix-generator'));
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Infrastructure/Console/Commands/Generator/BranchIssueIdResolverTest.php`

Expected: FAIL with `Class "Bitrix24\SDK\Infrastructure\Console\Commands\Generator\BranchIssueIdResolver" not found`.

- [ ] **Step 3: Write the minimal resolver implementation**

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Infrastructure\Console\Commands\Generator;

use InvalidArgumentException;

final readonly class BranchIssueIdResolver
{
    public function resolve(string $branchName): string
    {
        if (preg_match('~^(feature|bugfix)/(?P<id>\d+)-~', $branchName, $matches) === 1) {
            return $matches['id'];
        }

        throw new InvalidArgumentException(sprintf(
            'Unable to extract issue id from branch "%s". Expected feature/<id>-... or bugfix/<id>-...',
            $branchName
        ));
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Infrastructure/Console/Commands/Generator/BranchIssueIdResolverTest.php`

Expected: PASS.

- [ ] **Step 5: Write the failing test for task path resolution**

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\ResultItemTaskPathResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResultItemTaskPathResolverTest extends TestCase
{
    #[Test]
    public function itBuildsTaskArtifactPaths(): void
    {
        $resolver = new ResultItemTaskPathResolver('.tasks');

        self::assertSame(
            '.tasks/425/im.dialog.get/result-item.payload.yaml',
            $resolver->payloadPath('425', 'im.dialog.get')
        );
        self::assertSame(
            '.tasks/425/im.dialog.get/result-item.verification-report.yaml',
            $resolver->verificationReportPath('425', 'im.dialog.get')
        );
    }
}
```

- [ ] **Step 6: Run the test to verify it fails**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/ResultItemTaskPathResolverTest.php`

Expected: FAIL with missing class.

- [ ] **Step 7: Write the minimal task path resolver**

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain;

final readonly class ResultItemTaskPathResolver
{
    public function __construct(private string $tasksRoot = '.tasks')
    {
    }

    public function methodDirectory(string $issueId, string $methodName): string
    {
        return sprintf('%s/%s/%s', $this->tasksRoot, $issueId, $methodName);
    }

    public function payloadPath(string $issueId, string $methodName): string
    {
        return $this->methodDirectory($issueId, $methodName) . '/result-item.payload.yaml';
    }

    public function verificationReportPath(string $issueId, string $methodName): string
    {
        return $this->methodDirectory($issueId, $methodName) . '/result-item.verification-report.yaml';
    }
}
```

- [ ] **Step 8: Run the test to verify it passes**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/ResultItemTaskPathResolverTest.php`

Expected: PASS.

- [ ] **Step 9: Write the failing command test for `--stage` orchestration**

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Infrastructure\Console\Commands\Generator;

use Bitrix24\SDK\Infrastructure\Console\Commands\Generator\ResultItemGeneratorCommand;
use Bitrix24\SDK\OpenApi\Domain\ResultItemTaskPathResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ResultItemGeneratorCommandTest extends TestCase
{
    #[Test]
    public function itRequiresAValidStageOption(): void
    {
        $commandTester = new CommandTester(new ResultItemGeneratorCommand(
            new class {},
            new class {},
            new class {
                public function resolve(string $branchName): string
                {
                    return '425';
                }
            },
            new ResultItemTaskPathResolver('.tasks'),
            new \Symfony\Component\Filesystem\Filesystem(),
        ));

        $status = $commandTester->execute([
            'method-name' => 'im.dialog.get',
            '--stage' => 'unknown',
        ], ['decorated' => false]);

        self::assertSame(Command::INVALID, $status);
        self::assertStringContainsString('Unsupported stage', $commandTester->getDisplay());
    }
}
```

Add a second test in the same file that executes the command without `--stage` and asserts the resolver receives `all`.

- [ ] **Step 10: Run the command test to verify it fails for the new assertions**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommandTest.php`

Expected: FAIL because the current command is still `b24-dev:generate-result-item` and has no stage orchestration.

- [ ] **Step 11: Implement the staged command shell**

Implementation targets:
- rename the command class to `ResultItemGeneratorCommand`
- rename the command name to `b24-dev:result-item-generator`
- add `--stage=build|verify|apply|generate|all`
- resolve current branch with `git branch --show-current`
- extract issue id using `BranchIssueIdResolver`
- compute artifact paths using `ResultItemTaskPathResolver`

Implementation code shape:

```php
#[AsCommand(
    name: 'b24-dev:result-item-generator',
    description: 'Build, verify, apply, and generate ResultItem payloads',
)]
final class ResultItemGeneratorCommand extends Command
{
    private const string STAGE = 'stage';

    protected function configure(): void
    {
        $this
            ->addArgument('method-name', InputArgument::REQUIRED)
            ->addOption(self::STAGE, null, InputOption::VALUE_REQUIRED, 'Pipeline stage', 'all');
    }
}
```

- [ ] **Step 12: Run the command tests to verify they pass**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommandTest.php tests/Unit/Infrastructure/Console/Commands/Generator/BranchIssueIdResolverTest.php tests/Unit/OpenApi/Domain/ResultItemTaskPathResolverTest.php`

Expected: PASS.

- [ ] **Step 13: Commit Task 1**

```bash
git add \
  src/Infrastructure/Console/Commands/Generator/BranchIssueIdResolver.php \
  src/OpenApi/Domain/ResultItemTaskPathResolver.php \
  src/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommand.php \
  bin/console \
  tests/Unit/Infrastructure/Console/Commands/Generator/BranchIssueIdResolverTest.php \
  tests/Unit/OpenApi/Domain/ResultItemTaskPathResolverTest.php \
  tests/Unit/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommandTest.php
git rm src/Infrastructure/Console/Commands/Generator/GenerateResultItemCommand.php
git commit -m "feat: add staged result item generator command"
```

### Task 2: Introduce the canonical YAML payload model

**Files:**
- Create: `src/OpenApi/Domain/ResultItemPayload.php`
- Create: `src/OpenApi/Domain/ResultItemPayloadField.php`
- Create: `src/OpenApi/Domain/ResultItemPayloadSection.php`
- Create: `src/OpenApi/Domain/ResultItemPayloadSerializer.php`
- Create: `tests/Unit/OpenApi/Domain/ResultItemPayloadSerializerTest.php`
- Create: `tests/Unit/OpenApi/Domain/Fixtures/result-item-payload.yaml`

- [ ] **Step 1: Write the failing serializer round-trip test**

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\ResultItemPayload;
use Bitrix24\SDK\OpenApi\Domain\ResultItemPayloadField;
use Bitrix24\SDK\OpenApi\Domain\ResultItemPayloadSection;
use Bitrix24\SDK\OpenApi\Domain\ResultItemPayloadSerializer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResultItemPayloadSerializerTest extends TestCase
{
    #[Test]
    public function itSerializesAndDeserializesCanonicalPayload(): void
    {
        $serializer = new ResultItemPayloadSerializer();
        $payload = new ResultItemPayload(
            method: 'im.dialog.get',
            object: 'result-item',
            generatedFrom: ['openapi', 'b24restdocs'],
            fields: [
                new ResultItemPayloadField('id', 'integer', 'int', null, true, false, 'openapi', 'Chat identifier', null),
            ],
            sections: [
                new ResultItemPayloadSection('restrictions', 'object', 'b24restdocs', []),
            ],
        );

        $yaml = $serializer->encode($payload);
        $decoded = $serializer->decode($yaml);

        self::assertSame('im.dialog.get', $decoded->method);
        self::assertCount(1, $decoded->fields);
        self::assertCount(1, $decoded->sections);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/ResultItemPayloadSerializerTest.php`

Expected: FAIL with missing classes.

- [ ] **Step 3: Add the payload value objects**

Implementation code:

```php
final readonly class ResultItemPayloadField
{
    public function __construct(
        public string $code,
        public string $sourceType,
        public string $phpdocType,
        public ?string $format,
        public bool $required,
        public bool $nullable,
        public string $source,
        public ?string $description,
        public ?string $notes,
    ) {
    }
}
```

```php
final readonly class ResultItemPayloadSection
{
    /**
     * @param list<ResultItemPayloadField> $fields
     */
    public function __construct(
        public string $name,
        public string $kind,
        public string $source,
        public array $fields,
    ) {
    }
}
```

```php
final readonly class ResultItemPayload
{
    /**
     * @param list<string> $generatedFrom
     * @param list<ResultItemPayloadField> $fields
     * @param list<ResultItemPayloadSection> $sections
     */
    public function __construct(
        public string $method,
        public string $object,
        public array $generatedFrom,
        public array $fields,
        public array $sections,
        public int $version = 1,
    ) {
    }
}
```

- [ ] **Step 4: Add the YAML serializer**

Implementation code:

```php
final class ResultItemPayloadSerializer
{
    public function encode(ResultItemPayload $payload): string
    {
        return Yaml::dump([
            'version' => $payload->version,
            'method' => $payload->method,
            'object' => $payload->object,
            'generated_from' => $payload->generatedFrom,
            'fields' => array_map($this->fieldToArray(...), $payload->fields),
            'sections' => array_map($this->sectionToArray(...), $payload->sections),
        ], 6, 2);
    }

    public function decode(string $yaml): ResultItemPayload
    {
        $decoded = Yaml::parse($yaml);

        return new ResultItemPayload(
            method: (string) $decoded['method'],
            object: (string) $decoded['object'],
            generatedFrom: array_map('strval', $decoded['generated_from'] ?? []),
            fields: array_map(
                static fn(array $field): ResultItemPayloadField => new ResultItemPayloadField(
                    code: (string) $field['code'],
                    sourceType: (string) $field['source_type'],
                    phpdocType: (string) $field['phpdoc_type'],
                    format: isset($field['format']) ? (string) $field['format'] : null,
                    required: (bool) $field['required'],
                    nullable: (bool) $field['nullable'],
                    source: (string) $field['source'],
                    description: isset($field['description']) ? (string) $field['description'] : null,
                    notes: isset($field['notes']) ? (string) $field['notes'] : null,
                ),
                $decoded['fields'] ?? [],
            ),
            sections: [],
            version: (int) ($decoded['version'] ?? 1),
        );
    }
}
```

- [ ] **Step 5: Run the serializer test to verify it passes**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/ResultItemPayloadSerializerTest.php`

Expected: PASS.

- [ ] **Step 6: Commit Task 2**

```bash
git add \
  src/OpenApi/Domain/ResultItemPayload.php \
  src/OpenApi/Domain/ResultItemPayloadField.php \
  src/OpenApi/Domain/ResultItemPayloadSection.php \
  src/OpenApi/Domain/ResultItemPayloadSerializer.php \
  tests/Unit/OpenApi/Domain/ResultItemPayloadSerializerTest.php \
  tests/Unit/OpenApi/Domain/Fixtures/result-item-payload.yaml
git commit -m "feat: add canonical result item payload model"
```

### Task 3: Build payloads from OpenAPI with docs fallback

**Files:**
- Create: `src/OpenApi/Domain/OpenApiResultItemPayloadProvider.php`
- Create: `src/OpenApi/Domain/RestDocsResultItemPayloadProvider.php`
- Create: `src/OpenApi/Domain/ResultItemPayloadBuilder.php`
- Create: `tests/Unit/OpenApi/Domain/OpenApiResultItemPayloadProviderTest.php`
- Create: `tests/Unit/OpenApi/Domain/RestDocsResultItemPayloadProviderTest.php`
- Create: `tests/Unit/OpenApi/Domain/ResultItemPayloadBuilderTest.php`
- Create: `tests/Unit/OpenApi/Domain/Fixtures/im-dialog-get.md`
- Modify: `src/OpenApi/Domain/OpenApiSchemaEntityReader.php`

- [ ] **Step 1: Write the failing OpenAPI provider test**

```php
#[Test]
public function itBuildsPayloadFieldsFromOpenApiSchema(): void
{
    $provider = new OpenApiResultItemPayloadProvider(
        new OpenApiSchemaEntityReader(new Filesystem())
    );

    $payload = $provider->provide(
        'tests/Unit/OpenApi/Domain/Fixtures/result-item-openapi.json',
        'bitrix.example.dialogdto',
        'im.dialog.get'
    );

    self::assertSame('im.dialog.get', $payload->method);
    self::assertSame('date_create', $payload->fields[1]->code);
    self::assertSame('date-time', $payload->fields[1]->format);
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/OpenApiResultItemPayloadProviderTest.php`

Expected: FAIL with missing class.

- [ ] **Step 3: Implement the OpenAPI provider minimally**

Use existing `OpenApiSchemaEntityReader::getResultFields()` as the starting point, then map each `ResultFieldDescriptor` into `ResultItemPayloadField`.

Implementation mapping:

```php
new ResultItemPayloadField(
    code: $field->name,
    sourceType: $field->format === 'date-time' ? 'datetime' : $field->type,
    phpdocType: $this->typeResolver->resolveFromSourceType($field->type, $field->format, $field->nullable),
    format: $field->format,
    required: !$field->nullable,
    nullable: $field->nullable,
    source: 'openapi',
    description: $field->description,
    notes: null,
)
```

- [ ] **Step 4: Write the failing docs repo fallback test**

Use the raw markdown fixture based on `bitrix24/b24restdocs/api-reference/chats/im-dialog-get.md`.

Assertions:
- top-level `fields` contain `date_create`
- nested `sections` contain `restrictions`, `entity_link`, `permissions`, `readed_list_item`, `last_message_views`
- `date_create` resolves to `phpdoc_type: Carbon\CarbonImmutable`
- `readed_list_item.date` resolves to `phpdoc_type: Carbon\CarbonImmutable|null`

- [ ] **Step 5: Run the docs repo test to verify it fails**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/RestDocsResultItemPayloadProviderTest.php`

Expected: FAIL with missing class.

- [ ] **Step 6: Implement the markdown provider**

Do not parse rendered HTML. Parse markdown table blocks and section headers from repo markdown.

```php
final class RestDocsResultItemPayloadProvider
{
    public function provideFromMarkdown(string $markdown, string $methodName, string $objectName): ResultItemPayload
    {
        preg_match('~#### Object ' . preg_quote($objectName, '~') . '.*?(#\\|.*?\\|#)~s', $markdown, $rootMatch);
        preg_match_all('~#### Object (?P<name>[a-zA-Z0-9_\\-]+).*?(?P<table>#\\|.*?\\|#)~s', $markdown, $sectionMatches, PREG_SET_ORDER);

        return new ResultItemPayload(
            method: $methodName,
            object: $objectName,
            generatedFrom: ['b24restdocs'],
            fields: $this->parseTable($rootMatch[1] ?? ''),
            sections: $this->parseSections($sectionMatches),
        );
    }
}
```

- [ ] **Step 7: Write the failing builder merge test**

Assertions:
- `OpenAPI` wins for base type and requiredness
- docs supplement `description`, `notes`, `format`, and sections
- conflicts are recorded in `notes`

- [ ] **Step 8: Run the builder test to verify it fails**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/ResultItemPayloadBuilderTest.php`

Expected: FAIL because no merge builder exists yet.

- [ ] **Step 9: Implement the payload builder**

```php
final class ResultItemPayloadBuilder
{
    public function build(
        ?ResultItemPayload $openApiPayload,
        ?ResultItemPayload $docsPayload,
        string $methodName,
        string $objectName = 'result-item',
    ): ResultItemPayload {
        $openApiFields = $this->indexByCode($openApiPayload?->fields ?? []);
        $docsFields = $this->indexByCode($docsPayload?->fields ?? []);
        $mergedFields = $this->mergeFields($openApiFields, $docsFields);

        return new ResultItemPayload(
            method: $methodName,
            object: $objectName,
            generatedFrom: array_values(array_unique(array_filter([
                $openApiPayload !== null ? 'openapi' : null,
                $docsPayload !== null ? 'b24restdocs' : null,
            ]))),
            fields: array_values($mergedFields),
            sections: $this->mergeSections($openApiPayload?->sections ?? [], $docsPayload?->sections ?? []),
        );
    }
}
```

- [ ] **Step 10: Run the provider and builder tests to verify they pass**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/OpenApiResultItemPayloadProviderTest.php tests/Unit/OpenApi/Domain/RestDocsResultItemPayloadProviderTest.php tests/Unit/OpenApi/Domain/ResultItemPayloadBuilderTest.php`

Expected: PASS.

- [ ] **Step 11: Commit Task 3**

```bash
git add \
  src/OpenApi/Domain/OpenApiResultItemPayloadProvider.php \
  src/OpenApi/Domain/RestDocsResultItemPayloadProvider.php \
  src/OpenApi/Domain/ResultItemPayloadBuilder.php \
  src/OpenApi/Domain/OpenApiSchemaEntityReader.php \
  tests/Unit/OpenApi/Domain/OpenApiResultItemPayloadProviderTest.php \
  tests/Unit/OpenApi/Domain/RestDocsResultItemPayloadProviderTest.php \
  tests/Unit/OpenApi/Domain/ResultItemPayloadBuilderTest.php \
  tests/Unit/OpenApi/Domain/Fixtures/im-dialog-get.md
git commit -m "feat: build result item payloads from openapi and docs"
```

### Task 4: Add verification report and safe apply behavior

**Files:**
- Create: `src/OpenApi/Domain/ResultItemVerificationReport.php`
- Create: `src/OpenApi/Domain/ResultItemVerificationReportSerializer.php`
- Create: `src/OpenApi/Domain/ResultItemPayloadVerifier.php`
- Create: `src/OpenApi/Domain/ResultItemVerificationApplier.php`
- Create: `tests/Unit/OpenApi/Domain/ResultItemPayloadVerifierTest.php`
- Create: `tests/Unit/OpenApi/Domain/ResultItemVerificationApplierTest.php`
- Modify: `src/OpenApi/Domain/LiveApiResultFieldProvider.php`

- [ ] **Step 1: Write the failing verifier test**

Use a payload fixture and a runtime response fixture.

Assertions:
- extra runtime field appears in `unexpected_fields`
- payload field missing at runtime appears in `missing_fields`
- nullable observation is recorded when runtime value is `null`
- matching fields appear in `confirmed_fields`

- [ ] **Step 2: Run the verifier test to verify it fails**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/ResultItemPayloadVerifierTest.php`

Expected: FAIL with missing classes.

- [ ] **Step 3: Implement the verification report model and serializer**

Implementation shape:

```php
final readonly class ResultItemVerificationReport
{
    /**
     * @param list<array<string, mixed>> $confirmedFields
     * @param list<array<string, mixed>> $missingFields
     * @param list<array<string, mixed>> $unexpectedFields
     * @param list<array<string, mixed>> $typeMismatches
     */
    public function __construct(
        public string $method,
        public array $confirmedFields,
        public array $missingFields,
        public array $unexpectedFields,
        public array $typeMismatches,
        public array $nullabilityObservations,
    ) {
    }
}
```

- [ ] **Step 4: Implement the verifier**

Use `Bitrix24MethodResultFetcher` to obtain `result`, then compare it with the canonical payload rather than inferring a replacement field collection.

- [ ] **Step 5: Write the failing applier test**

Assertions:
- safe addition of a new field updates payload
- `nullable: true` is applied when report confirms nullability
- conflicting type replacement is left unchanged

- [ ] **Step 6: Run the applier test to verify it fails**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/ResultItemVerificationApplierTest.php`

Expected: FAIL with missing class.

- [ ] **Step 7: Implement the safe applier**

```php
if ($reportItem['action'] === 'mark_nullable') {
    $field = $payload->fieldByCode($reportItem['code'])->withNullable(true);
}

if ($reportItem['action'] === 'add_field') {
    $payload = $payload->withAddedField(...);
}
```

- [ ] **Step 8: Run verifier and applier tests to verify they pass**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/ResultItemPayloadVerifierTest.php tests/Unit/OpenApi/Domain/ResultItemVerificationApplierTest.php`

Expected: PASS.

- [ ] **Step 9: Commit Task 4**

```bash
git add \
  src/OpenApi/Domain/ResultItemVerificationReport.php \
  src/OpenApi/Domain/ResultItemVerificationReportSerializer.php \
  src/OpenApi/Domain/ResultItemPayloadVerifier.php \
  src/OpenApi/Domain/ResultItemVerificationApplier.php \
  src/OpenApi/Domain/LiveApiResultFieldProvider.php \
  tests/Unit/OpenApi/Domain/ResultItemPayloadVerifierTest.php \
  tests/Unit/OpenApi/Domain/ResultItemVerificationApplierTest.php
git commit -m "feat: add result item verification and safe apply"
```

### Task 5: Generate code from payload and wire the full command flow

**Files:**
- Modify: `src/CodeGenerator/ResultItemCodeGenerator.php`
- Modify: `src/CodeGenerator/Templates/ResultItem.tpl.php`
- Modify: `src/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommand.php`
- Modify: `bin/console`
- Create: `tests/Unit/OpenApi/Domain/ResultItemCodeGeneratorFromPayloadTest.php`
- Create: `tests/Unit/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommandTest.php`
- Create: `.tasks/425/im.dialog.get/result-item.payload.yaml`
- Create: `.tasks/425/im.dialog.get/result-item.verification-report.yaml`

- [ ] **Step 1: Write the failing generator-from-payload test**

Assertions:
- generator reads canonical payload
- `date_create` becomes `Carbon\CarbonImmutable`
- `description` and `background_id` preserve canonical nullable/required metadata from payload
- `// Source: payload` marker is emitted

- [ ] **Step 2: Run the generator test to verify it fails**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/OpenApi/Domain/ResultItemCodeGeneratorFromPayloadTest.php`

Expected: FAIL because the generator still consumes `ResultFieldDescriptor[]`.

- [ ] **Step 3: Refactor the generator to consume payload**

Implementation API:

```php
final readonly class ResultItemCodeGenerator
{
    public function generateFromPayload(
        string $namespace,
        string $className,
        ResultItemPayload $payload,
        string $sourceName = 'payload',
    ): string {
        $phpDocFields = array_map(
            static fn(ResultItemPayloadField $field): array => [
                'name' => $field->code,
                'phpType' => $field->phpdocType,
            ],
            $payload->fields,
        );
    }
}
```

- [ ] **Step 4: Write the failing command integration test for staged flow**

Assertions:
- `--stage=build` writes `.tasks/<issue-id>/<method>/result-item.payload.yaml`
- `--stage=verify` writes `.tasks/<issue-id>/<method>/result-item.verification-report.yaml`
- `--stage=apply` updates payload safely
- `--stage=generate` writes the PHP class using payload only

- [ ] **Step 5: Run the command integration test to verify it fails**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommandTest.php`

Expected: FAIL because the command does not yet orchestrate the payload stages end-to-end.

- [ ] **Step 6: Wire the full command**

Implementation responsibilities:
- `build`: use `OpenApiResultItemPayloadProvider`, `RestDocsResultItemPayloadProvider`, and `ResultItemPayloadBuilder`
- `verify`: use `ResultItemPayloadVerifier` and report serializer
- `apply`: use `ResultItemVerificationApplier` and payload serializer
- `generate`: use `ResultItemCodeGenerator::generateFromPayload()`
- `all`: run the stages in order

- [ ] **Step 7: Run the command integration tests to verify they pass**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommandTest.php tests/Unit/OpenApi/Domain/ResultItemCodeGeneratorFromPayloadTest.php`

Expected: PASS.

- [ ] **Step 8: Commit Task 5**

```bash
git add \
  src/CodeGenerator/ResultItemCodeGenerator.php \
  src/CodeGenerator/Templates/ResultItem.tpl.php \
  src/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommand.php \
  bin/console \
  tests/Unit/OpenApi/Domain/ResultItemCodeGeneratorFromPayloadTest.php \
  tests/Unit/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommandTest.php \
  .tasks/425/im.dialog.get/result-item.payload.yaml \
  .tasks/425/im.dialog.get/result-item.verification-report.yaml
git commit -m "feat: generate result items from canonical payload"
```

### Task 6: End-to-end validation on `im.dialog.get`

**Files:**
- Modify: `src/Services/IM/Dialog/Result/DialogItemResult.php`
- Create: `tests/Functional/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommandE2ETest.php`

- [ ] **Step 1: Write the failing end-to-end test**

The test should:
- run `b24-dev:result-item-generator im.dialog.get --stage=all`
- assert that `.tasks/425/im.dialog.get/result-item.payload.yaml` exists
- assert that `.tasks/425/im.dialog.get/result-item.verification-report.yaml` exists
- assert that `src/Services/IM/Dialog/Result/DialogItemResult.php` contains `@property-read Carbon\CarbonImmutable $date_create`

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker compose run --rm php-cli vendor/bin/phpunit tests/Functional/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommandE2ETest.php`

Expected: FAIL until all stages are wired and the task artifacts are generated correctly.

- [ ] **Step 3: Make the minimal fixes needed for the full flow**

This step is limited to:
- path bugs
- serializer ordering issues
- command wiring errors
- payload merge edge cases specific to `im.dialog.get`

- [ ] **Step 4: Run the end-to-end test and targeted regression suite**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit \
  tests/Unit/Infrastructure/Console/Commands/Generator/BranchIssueIdResolverTest.php \
  tests/Unit/OpenApi/Domain/ResultItemTaskPathResolverTest.php \
  tests/Unit/OpenApi/Domain/ResultItemPayloadSerializerTest.php \
  tests/Unit/OpenApi/Domain/OpenApiResultItemPayloadProviderTest.php \
  tests/Unit/OpenApi/Domain/RestDocsResultItemPayloadProviderTest.php \
  tests/Unit/OpenApi/Domain/ResultItemPayloadBuilderTest.php \
  tests/Unit/OpenApi/Domain/ResultItemPayloadVerifierTest.php \
  tests/Unit/OpenApi/Domain/ResultItemVerificationApplierTest.php \
  tests/Unit/OpenApi/Domain/ResultItemCodeGeneratorFromPayloadTest.php \
  tests/Unit/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommandTest.php \
  tests/Functional/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommandE2ETest.php
```

Expected: PASS.

- [ ] **Step 5: Commit Task 6**

```bash
git add \
  src/Services/IM/Dialog/Result/DialogItemResult.php \
  tests/Functional/Infrastructure/Console/Commands/Generator/ResultItemGeneratorCommandE2ETest.php \
  .tasks/425
git commit -m "test: validate result item generator end to end"
```
