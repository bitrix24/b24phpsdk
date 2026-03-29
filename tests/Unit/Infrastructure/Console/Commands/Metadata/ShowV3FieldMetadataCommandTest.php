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

namespace Bitrix24\SDK\Tests\Unit\Infrastructure\Console\Commands\Metadata;

use Bitrix24\SDK\Infrastructure\Console\Commands\Metadata\Bitrix24V3FieldMetadataFetcher;
use Bitrix24\SDK\Infrastructure\Console\Commands\Metadata\DevWebhookResolver;
use Bitrix24\SDK\Infrastructure\Console\Commands\Metadata\ShowV3FieldMetadataCommand;
use Bitrix24\SDK\Infrastructure\Console\Commands\ShowFieldsDescriptionCommand;
use Bitrix24\SDK\Infrastructure\Console\Commands\SplashScreen;
use Bitrix24\SDK\OpenApi\Domain\OaFieldListMethodResolver;
use Bitrix24\SDK\OpenApi\Domain\OaSchemaMethodReader;
use Bitrix24\SDK\OpenApi\Domain\OaToSdkMethodNormalizationPolicy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class SpyBitrix24V3FieldMetadataFetcher extends Bitrix24V3FieldMetadataFetcher
{
    public ?string $lastWebhook = null;

    public ?string $lastMethodName = null;

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(
        private readonly array $response,
    ) {
        parent::__construct(new NullLogger());
    }

    #[\Override]
    public function fetch(string $webhook, string $methodName): array
    {
        $this->lastWebhook = $webhook;
        $this->lastMethodName = $methodName;

        return $this->response;
    }
}

class ShowV3FieldMetadataCommandTest extends TestCase
{
    private const string SCHEMA_FIXTURE = __DIR__ . '/../../../../OpenApi/Domain/fixtures/openapi-field-list-methods.json';

    /**
     * @var array<string, string|null>
     */
    private array $originalEnvironment = [];

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        foreach ($this->getEnvNames() as $envName) {
            $value = getenv($envName);
            $this->originalEnvironment[$envName] = $value === false ? null : $value;
            $this->writeEnv($envName, null);
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->originalEnvironment as $envName => $value) {
            $this->writeEnv($envName, $value);
        }

        parent::tearDown();
    }

    #[Test]
    public function itBuildsInteractiveEntityChoicesFromTheOpenApiSnapshot(): void
    {
        $this->writeEnv('BITRIX24_WEBHOOK', 'https://fallback.example/rest/1/token/');
        $spyBitrix24V3FieldMetadataFetcher = new SpyBitrix24V3FieldMetadataFetcher([
            'ID' => ['title' => 'Task ID', 'type' => 'integer'],
        ]);
        $commandTester = new CommandTester($this->createCommand($spyBitrix24V3FieldMetadataFetcher));
        $commandTester->setInputs(['1']);

        $status = $commandTester->execute([
            '--schema-file' => self::SCHEMA_FIXTURE,
        ], [
            'interactive' => true,
            'decorated' => false,
        ]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertSame('tasks.task.field.list', $spyBitrix24V3FieldMetadataFetcher->lastMethodName);
        $this->assertStringContainsString('main.eventlog', $commandTester->getDisplay());
        $this->assertStringContainsString('tasks.task.access', $commandTester->getDisplay());
    }

    #[Test]
    public function itResolvesExactEntityKeyToTheExpectedMethod(): void
    {
        $spyBitrix24V3FieldMetadataFetcher = new SpyBitrix24V3FieldMetadataFetcher([
            'ID' => ['title' => 'Task ID', 'type' => 'integer'],
        ]);
        $commandTester = new CommandTester($this->createCommand($spyBitrix24V3FieldMetadataFetcher));

        $status = $commandTester->execute([
            'entity' => 'tasks.task',
            '--schema-file' => self::SCHEMA_FIXTURE,
            '--webhook' => 'https://cli.example/rest/1/token/',
        ], [
            'decorated' => false,
        ]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertSame('tasks.task.field.list', $spyBitrix24V3FieldMetadataFetcher->lastMethodName);
        $this->assertSame('https://cli.example/rest/1/token/', $spyBitrix24V3FieldMetadataFetcher->lastWebhook);
    }

    #[Test]
    public function itPrintsJsonOutputWithCompleteMetadataPayload(): void
    {
        $spyBitrix24V3FieldMetadataFetcher = new SpyBitrix24V3FieldMetadataFetcher([
            'ID' => ['title' => 'Task ID', 'type' => 'integer', 'isImmutable' => true],
            'XML_ID' => ['TYPE' => 'string'],
        ]);
        $commandTester = new CommandTester($this->createCommand($spyBitrix24V3FieldMetadataFetcher));

        $status = $commandTester->execute([
            'entity' => 'tasks.task',
            '--schema-file' => self::SCHEMA_FIXTURE,
            '--webhook' => 'https://cli.example/rest/1/token/',
        ], [
            'decorated' => false,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('"code": "ID"', $display);
        $this->assertStringContainsString('"title": "Task ID"', $display);
        $this->assertStringContainsString('"isImmutable": true', $display);
        $this->assertStringContainsString('"TYPE": "string"', $display);
    }

    #[Test]
    public function itUnwrapsSingleMetadataCollectionForJsonOutput(): void
    {
        $spyBitrix24V3FieldMetadataFetcher = new SpyBitrix24V3FieldMetadataFetcher([
            'items' => [
                [
                    'name' => 'id',
                    'type' => 'int',
                    'title' => 'id',
                    'editable' => false,
                    'validationRules' => [],
                ],
                [
                    'name' => 'title',
                    'type' => 'string',
                    'title' => 'title',
                    'editable' => true,
                    'requiredGroups' => ['add'],
                ],
            ],
        ]);
        $commandTester = new CommandTester($this->createCommand($spyBitrix24V3FieldMetadataFetcher));

        $status = $commandTester->execute([
            'entity' => 'tasks.task',
            '--schema-file' => self::SCHEMA_FIXTURE,
            '--webhook' => 'https://cli.example/rest/1/token/',
        ], [
            'decorated' => false,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringStartsWith("[\n", $display);
        $this->assertStringContainsString('"name": "id"', $display);
        $this->assertStringContainsString('"requiredGroups": [', $display);
        $this->assertStringNotContainsString('"code": "items"', $display);
        $this->assertStringNotContainsString('"metadata"', $display);
    }

    #[Test]
    public function itRendersTableOutputWithAgreedColumns(): void
    {
        $spyBitrix24V3FieldMetadataFetcher = new SpyBitrix24V3FieldMetadataFetcher([
            'ID' => ['title' => 'Task ID', 'type' => 'integer', 'isImmutable' => true],
        ]);
        $commandTester = new CommandTester($this->createCommand($spyBitrix24V3FieldMetadataFetcher));

        $status = $commandTester->execute([
            'entity' => 'tasks.task',
            '--schema-file' => self::SCHEMA_FIXTURE,
            '--webhook' => 'https://cli.example/rest/1/token/',
            '--format' => 'table',
        ], [
            'decorated' => false,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('code', $display);
        $this->assertStringContainsString('title', $display);
        $this->assertStringContainsString('metadata', $display);
        $this->assertStringContainsString('{"title":"Task ID","type":"integer","isImmutable":true}', $display);
    }

    #[Test]
    public function itRendersUnwrappedMetadataCollectionAsDirectTableRows(): void
    {
        $spyBitrix24V3FieldMetadataFetcher = new SpyBitrix24V3FieldMetadataFetcher([
            'items' => [
                [
                    'name' => 'id',
                    'type' => 'int',
                    'title' => 'id',
                    'editable' => false,
                    'validationRules' => [],
                ],
                [
                    'name' => 'title',
                    'type' => 'string',
                    'title' => 'title',
                    'editable' => true,
                    'requiredGroups' => ['add'],
                ],
            ],
        ]);
        $commandTester = new CommandTester($this->createCommand($spyBitrix24V3FieldMetadataFetcher));

        $status = $commandTester->execute([
            'entity' => 'tasks.task',
            '--schema-file' => self::SCHEMA_FIXTURE,
            '--webhook' => 'https://cli.example/rest/1/token/',
            '--format' => 'table',
        ], [
            'decorated' => false,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('name', $display);
        $this->assertStringContainsString('type', $display);
        $this->assertStringContainsString('editable', $display);
        $this->assertStringContainsString('validationRules', $display);
        $this->assertStringContainsString('requiredGroups', $display);
        $this->assertStringContainsString('title', $display);
        $this->assertStringContainsString('["add"]', $display);
        $this->assertStringNotContainsString('metadata', $display);
    }

    #[Test]
    public function itPrintsClearErrorWhenWebhookIsMissing(): void
    {
        $spyBitrix24V3FieldMetadataFetcher = new SpyBitrix24V3FieldMetadataFetcher([]);
        $commandTester = new CommandTester($this->createCommand($spyBitrix24V3FieldMetadataFetcher));

        $status = $commandTester->execute([
            'entity' => 'tasks.task',
            '--schema-file' => self::SCHEMA_FIXTURE,
        ], [
            'interactive' => false,
            'decorated' => false,
        ]);

        $this->assertSame(Command::INVALID, $status);
        $this->assertStringContainsString(
            'Webhook is not configured. Pass --webhook or set BITRIX24_WEBHOOK',
            $commandTester->getDisplay()
        );
        $this->assertStringContainsString(
            'tests/.env.local',
            $commandTester->getDisplay()
        );
    }

    #[Test]
    public function legacyFieldDescriptionHelpMarksTheCommandAsLegacy(): void
    {
        $application = new Application();
        $application->setAutoExit(false);
        $application->addCommand(new ShowFieldsDescriptionCommand(new SplashScreen(), new NullLogger()));

        $applicationTester = new ApplicationTester($application);
        $status = $applicationTester->run([
            'command' => 'b24-dev:show-fields-description',
            '--help' => true,
        ], [
            'decorated' => false,
        ]);

        $display = $applicationTester->getDisplay();

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('legacy', strtolower($display));
        $this->assertStringContainsString('b24-dev:show-v3-field-metadata', $display);
    }

    private function createCommand(SpyBitrix24V3FieldMetadataFetcher $spyBitrix24V3FieldMetadataFetcher): ShowV3FieldMetadataCommand
    {
        $showV3FieldMetadataCommand = new ShowV3FieldMetadataCommand(
            new OaFieldListMethodResolver(
                new OaSchemaMethodReader(new Filesystem(), new OaToSdkMethodNormalizationPolicy())
            ),
            new DevWebhookResolver(),
            $spyBitrix24V3FieldMetadataFetcher
        );

        $application = new Application();
        $application->setAutoExit(false);
        $application->addCommand($showV3FieldMetadataCommand);

        /** @var ShowV3FieldMetadataCommand $registeredCommand */
        $registeredCommand = $application->find('b24-dev:show-v3-field-metadata');

        return $registeredCommand;
    }

    /**
     * @return list<string>
     */
    private function getEnvNames(): array
    {
        return ['BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK', 'BITRIX24_WEBHOOK'];
    }

    private function writeEnv(string $envName, ?string $value): void
    {
        if ($value === null) {
            unset($_ENV[$envName], $_SERVER[$envName]);
            putenv($envName);

            return;
        }

        $_ENV[$envName] = $value;
        $_SERVER[$envName] = $value;
        putenv(sprintf('%s=%s', $envName, $value));
    }
}
