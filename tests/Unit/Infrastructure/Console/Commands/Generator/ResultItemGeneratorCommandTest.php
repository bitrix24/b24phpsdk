<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Infrastructure\Console\Commands\Generator;

use Bitrix24\SDK\CodeGenerator\ResultItemCodeGenerator;
use Bitrix24\SDK\Infrastructure\Console\Commands\Generator\ApiEndpointDocumentationUrlResolver;
use Bitrix24\SDK\Infrastructure\Console\Commands\Generator\BranchIssueIdResolver;
use Bitrix24\SDK\Infrastructure\Console\Commands\Generator\DefaultResultItemGeneratorWorkflow;
use Bitrix24\SDK\Infrastructure\Console\Commands\Generator\ResultItemGeneratorCommand;
use Bitrix24\SDK\Infrastructure\Console\Commands\Metadata\Bitrix24MethodResultFetcher;
use Bitrix24\SDK\Infrastructure\Console\Commands\Metadata\DevWebhookResolver;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\OpenApiResultItemPayloadProvider;
use Bitrix24\SDK\OpenApi\Domain\Schema\OpenApiSchemaEntityReader;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadSerializer;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadBuilder;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadVerifier;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPhpDocTypeResolver;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemTaskPathResolver;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemVerificationApplier;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemVerificationReportSerializer;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\RestDocsResultItemPayloadProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class ResultItemGeneratorCommandTest extends TestCase
{
    private const string METHOD_NAME = 'im.dialog.get';
    private const string ISSUE_ID = '425';
    private const string IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV = 'BITRIX24_PHP_SDK_IM_DIALOG_GET_SAMPLE_DIALOG_ID';
    private const string UNIT_SAMPLE_DIALOG_ID = 'chat42';

    private Filesystem $filesystem;
    private string $tempDirectory;
    private ?string $originalWebhook = null;
    private ?string $originalSampleDialogId = null;
    private FakeBitrix24MethodResultFetcher $resultFetcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();
        $this->tempDirectory = sys_get_temp_dir() . '/result-item-generator-command-test-' . uniqid('', true);
        $this->filesystem->mkdir($this->tempDirectory);
        $this->filesystem->mkdir($this->tempDirectory . '/src/Services/IM/Dialog/Result');
        $this->resultFetcher = new FakeBitrix24MethodResultFetcher([
            'id' => 7,
            'date_create' => '2022-06-07T21:56:05+03:00',
            'description' => 'Verified description',
            'background_id' => null,
            'status' => 'open',
        ]);

        $this->originalWebhook = $_ENV['BITRIX24_WEBHOOK'] ?? $_SERVER['BITRIX24_WEBHOOK'] ?? getenv('BITRIX24_WEBHOOK') ?: null;
        putenv('BITRIX24_WEBHOOK=https://unit.test/rest/1/token/');
        $_ENV['BITRIX24_WEBHOOK'] = 'https://unit.test/rest/1/token/';
        $_SERVER['BITRIX24_WEBHOOK'] = 'https://unit.test/rest/1/token/';
        $this->originalSampleDialogId = $_ENV[self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV]
            ?? $_SERVER[self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV]
            ?? getenv(self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV)
            ?: null;
        putenv(self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV . '=' . self::UNIT_SAMPLE_DIALOG_ID);
        $_ENV[self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV] = self::UNIT_SAMPLE_DIALOG_ID;
        $_SERVER[self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV] = self::UNIT_SAMPLE_DIALOG_ID;

        $this->filesystem->dumpFile($this->schemaFixturePath(), <<<'JSON'
{
  "openapi": "3.0.0",
  "paths": {
    "/im.dialog.get": {
      "post": {
        "summary": "im.dialog.get",
        "description": "im.dialog.get",
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/bitrix.example.dialogdto"
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
      "bitrix.example.dialogdto": {
        "type": "object",
        "required": ["id", "date_create"],
        "properties": {
          "id": {
            "type": "integer",
            "description": "Chat identifier"
          },
          "date_create": {
            "type": "string",
            "format": "date-time",
            "description": "Created at"
          },
          "description": {
            "type": "string",
            "description": "Dialog description from schema"
          },
          "background_id": {
            "type": "integer",
            "nullable": true,
            "description": "Background identifier"
          }
        }
      }
    }
  }
}
JSON);

        $this->filesystem->dumpFile($this->markdownFixturePath(), <<<'MARKDOWN'
#### Object result-item {#result-item}

#|
|| **Name**
`Type` | Description ||
|| **id**
`integer` | Chat identifier ||
|| **date_create**
`datetime` | Chat creation date in ATOM format ||
|| **description**
`string` | Dialog description text ||
|| **background_id**
`integer` | Identifier of the chat background. If not specified, the value is `null` ||
|#
MARKDOWN);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->tempDirectory);

        if ($this->originalWebhook === null) {
            putenv('BITRIX24_WEBHOOK');
            unset($_ENV['BITRIX24_WEBHOOK'], $_SERVER['BITRIX24_WEBHOOK']);
        } else {
            putenv('BITRIX24_WEBHOOK=' . $this->originalWebhook);
            $_ENV['BITRIX24_WEBHOOK'] = $this->originalWebhook;
            $_SERVER['BITRIX24_WEBHOOK'] = $this->originalWebhook;
        }

        if ($this->originalSampleDialogId === null) {
            putenv(self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV);
            unset($_ENV[self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV], $_SERVER[self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV]);
        } else {
            putenv(self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV . '=' . $this->originalSampleDialogId);
            $_ENV[self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV] = $this->originalSampleDialogId;
            $_SERVER[self::IM_DIALOG_GET_SAMPLE_DIALOG_ID_ENV] = $this->originalSampleDialogId;
        }

        parent::tearDown();
    }

    #[Test]
    public function itRejectsUnsupportedStageValues(): void
    {
        $commandTester = new CommandTester($this->createCommand());

        $status = $commandTester->execute([
            'method-name' => self::METHOD_NAME,
            '--stage' => 'unknown',
        ], ['decorated' => false]);

        self::assertSame(Command::INVALID, $status);
        self::assertStringContainsString('Unsupported stage', $commandTester->getDisplay());
    }

    #[Test]
    public function buildStageWritesTheCanonicalPayloadArtifactUsingTheRealWorkflow(): void
    {
        $commandTester = new CommandTester($this->createCommand());

        $status = $commandTester->execute([
            'method-name' => self::METHOD_NAME,
            '--stage' => 'build',
        ], ['decorated' => false]);

        self::assertSame(Command::SUCCESS, $status);

        $payload = (new ResultItemPayloadSerializer())->decode((string) file_get_contents($this->payloadPath()));
        self::assertSame(['openapi', 'b24restdocs'], $payload->generatedFrom);
        self::assertSame('Dialog description text', $this->findField($payload->fields, 'description')?->description);
        self::assertTrue($this->findField($payload->fields, 'background_id')?->nullable ?? false);
        self::assertSame([], $this->resultFetcher->calls);
    }

    #[Test]
    public function buildStageFailsFastWhenDocsSourceIsUnavailable(): void
    {
        $commandTester = new CommandTester($this->createCommand(
            $this->createWorkflow(
                documentationMarkdownPathResolver: static fn(string $methodName): ?string => null,
            ),
        ));

        $status = $commandTester->execute([
            'method-name' => self::METHOD_NAME,
            '--stage' => 'build',
        ], ['decorated' => false]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('REST docs payload is required', $commandTester->getDisplay());
        self::assertFileDoesNotExist($this->payloadPath());
    }

    #[Test]
    public function verifyStageWritesTheVerificationReportArtifactUsingTheRealWorkflow(): void
    {
        $this->runStage('build');

        $commandTester = new CommandTester($this->createCommand());

        $status = $commandTester->execute([
            'method-name' => self::METHOD_NAME,
            '--stage' => 'verify',
        ], ['decorated' => false]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertFileExists($this->reportPath());
        self::assertSame([[
            'webhook' => 'https://unit.test/rest/1/token/',
            'methodName' => self::METHOD_NAME,
            'params' => ['DIALOG_ID' => self::UNIT_SAMPLE_DIALOG_ID],
        ]], $this->resultFetcher->calls);
        self::assertStringContainsString('Wrote verification report:', $commandTester->getDisplay());
    }

    #[Test]
    public function applyStageUpdatesThePayloadArtifactUsingTheRealWorkflow(): void
    {
        $this->runStage('build');
        $this->runStage('verify');

        $commandTester = new CommandTester($this->createCommand());

        $status = $commandTester->execute([
            'method-name' => self::METHOD_NAME,
            '--stage' => 'apply',
        ], ['decorated' => false]);

        self::assertSame(Command::SUCCESS, $status);

        $payload = (new ResultItemPayloadSerializer())->decode((string) file_get_contents($this->payloadPath()));
        $dateCreate = $this->findField($payload->fields, 'date_create');
        self::assertNotNull($dateCreate);
        self::assertFalse($dateCreate->nullable);
        self::assertSame('Carbon\\CarbonImmutable', $dateCreate->phpdocType);

        $statusField = $this->findField($payload->fields, 'status');
        self::assertNotNull($statusField);
        self::assertSame('string', $statusField->phpdocType);
        self::assertStringContainsString('Applied verification report to payload:', $commandTester->getDisplay());
    }

    #[Test]
    public function generateStageWritesThePhpClassArtifactUsingTheRealWorkflow(): void
    {
        $this->runStage('build');
        $this->runStage('verify');
        $this->runStage('apply');

        $commandTester = new CommandTester($this->createCommand());

        $status = $commandTester->execute([
            'method-name' => self::METHOD_NAME,
            '--stage' => 'generate',
        ], ['decorated' => false]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertFileExists($this->generatedPath());
        self::assertStringContainsString('@property-read Carbon\\CarbonImmutable $date_create', (string) file_get_contents($this->generatedPath()));
        self::assertStringContainsString('Generated ResultItem class:', $commandTester->getDisplay());
    }

    #[Test]
    public function stageAllRunsTheRealWorkflowEndToEndInBuildVerifyApplyGenerateOrder(): void
    {
        $commandTester = new CommandTester($this->createCommand());

        $status = $commandTester->execute([
            'method-name' => self::METHOD_NAME,
            '--stage' => 'all',
        ], ['decorated' => false]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertFileExists($this->payloadPath());
        self::assertFileExists($this->reportPath());
        self::assertFileExists($this->generatedPath());

        $payload = (new ResultItemPayloadSerializer())->decode((string) file_get_contents($this->payloadPath()));
        $dateCreate = $this->findField($payload->fields, 'date_create');
        self::assertNotNull($dateCreate);
        self::assertFalse($dateCreate->nullable);
        self::assertSame('Carbon\\CarbonImmutable', $dateCreate->phpdocType);

        $statusField = $this->findField($payload->fields, 'status');
        self::assertNotNull($statusField);
        self::assertSame('Observed in live API verification report', $statusField->notes);

        $generatedCode = (string) file_get_contents($this->generatedPath());
        self::assertStringContainsString('@property-read Carbon\\CarbonImmutable $date_create', $generatedCode);
        self::assertStringContainsString('@property-read string $status', $generatedCode);
        self::assertSame([[
            'webhook' => 'https://unit.test/rest/1/token/',
            'methodName' => self::METHOD_NAME,
            'params' => ['DIALOG_ID' => self::UNIT_SAMPLE_DIALOG_ID],
        ]], $this->resultFetcher->calls);

        $display = $commandTester->getDisplay();
        $buildPosition = strpos($display, 'Built payload:');
        $verifyPosition = strpos($display, 'Wrote verification report:');
        $applyPosition = strpos($display, 'Applied verification report to payload:');
        $generatePosition = strpos($display, 'Generated ResultItem class:');

        self::assertIsInt($buildPosition);
        self::assertIsInt($verifyPosition);
        self::assertIsInt($applyPosition);
        self::assertIsInt($generatePosition);
        self::assertTrue($buildPosition < $verifyPosition && $verifyPosition < $applyPosition && $applyPosition < $generatePosition);
    }

    #[Test]
    public function itReturnsFailureWhenCurrentBranchResolutionFails(): void
    {
        $commandTester = new CommandTester(new class(
            new BranchIssueIdResolver(),
            new ResultItemTaskPathResolver($this->tempDirectory . '/.tasks'),
            $this->createWorkflow(),
        ) extends ResultItemGeneratorCommand {
            #[\Override]
            protected function resolveCurrentBranch(): string
            {
                throw new RuntimeException('git is unavailable');
            }
        });

        $status = $commandTester->execute([
            'method-name' => self::METHOD_NAME,
        ], ['decorated' => false]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('git is unavailable', $commandTester->getDisplay());
    }

    private function createCommand(?object $workflow = null): ResultItemGeneratorCommand
    {
        return new class(
            new BranchIssueIdResolver(),
            new ResultItemTaskPathResolver($this->tempDirectory . '/.tasks'),
            $workflow ?? $this->createWorkflow(),
        ) extends ResultItemGeneratorCommand {
            #[\Override]
            protected function resolveCurrentBranch(): string
            {
                return 'feature/425-add-result-item-generator';
            }
        };
    }

    private function runStage(string $stage): void
    {
        $status = (new CommandTester($this->createCommand()))->execute([
            'method-name' => self::METHOD_NAME,
            '--stage' => $stage,
        ], ['decorated' => false]);

        self::assertSame(Command::SUCCESS, $status);
    }

    private function createWorkflow(
        ?\Closure $documentationMarkdownPathResolver = null,
        ?\Closure $generationTargetResolver = null,
    ): object
    {
        return new DefaultResultItemGeneratorWorkflow(
            new OpenApiResultItemPayloadProvider(
                new OpenApiSchemaEntityReader($this->filesystem),
                new ResultItemPhpDocTypeResolver(),
            ),
            new RestDocsResultItemPayloadProvider(new ResultItemPhpDocTypeResolver()),
            new ResultItemPayloadBuilder(),
            new ResultItemPayloadSerializer(),
            new ResultItemPayloadVerifier($this->resultFetcher, new ResultItemPhpDocTypeResolver()),
            new ResultItemVerificationReportSerializer(),
            new ResultItemVerificationApplier(new ResultItemPhpDocTypeResolver()),
            new ResultItemCodeGenerator(),
            new ApiEndpointDocumentationUrlResolver(new Finder(), $this->tempDirectory . '/src/Services'),
            new DevWebhookResolver(),
            $this->filesystem,
            $this->schemaFixturePath(),
            $documentationMarkdownPathResolver ?? fn(string $methodName): ?string => self::METHOD_NAME === $methodName ? $this->markdownFixturePath() : null,
            $generationTargetResolver ?? fn(string $methodName): array => [
                'namespace' => 'Bitrix24\\SDK\\Services\\IM\\Dialog\\Result',
                'className' => 'DialogItemResult',
                'path' => $this->generatedPath(),
            ],
        );
    }

    private function payloadPath(): string
    {
        return $this->tempDirectory . '/.tasks/' . self::ISSUE_ID . '/' . self::METHOD_NAME . '/result-item.payload.yaml';
    }

    private function reportPath(): string
    {
        return $this->tempDirectory . '/.tasks/' . self::ISSUE_ID . '/' . self::METHOD_NAME . '/result-item.verification-report.yaml';
    }

    private function generatedPath(): string
    {
        return $this->tempDirectory . '/src/Services/IM/Dialog/Result/DialogItemResult.php';
    }

    private function schemaFixturePath(): string
    {
        return $this->tempDirectory . '/result-item-openapi.json';
    }

    private function markdownFixturePath(): string
    {
        return $this->tempDirectory . '/im-dialog-get.md';
    }

    /**
     * @param list<\Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadField> $fields
     */
    private function findField(array $fields, string $code): ?\Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPayloadField
    {
        foreach ($fields as $field) {
            if ($field->code === $code) {
                return $field;
            }
        }

        return null;
    }
}

final class FakeBitrix24MethodResultFetcher extends Bitrix24MethodResultFetcher
{
    /**
     * @var list<array{webhook: string, methodName: string, params: array<string, mixed>}>
     */
    public array $calls = [];

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(
        private readonly array $response,
    ) {
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function fetch(string $webhook, string $methodName, array $params = []): array
    {
        $this->calls[] = [
            'webhook' => $webhook,
            'methodName' => $methodName,
            'params' => $params,
        ];

        return $this->response;
    }
}
