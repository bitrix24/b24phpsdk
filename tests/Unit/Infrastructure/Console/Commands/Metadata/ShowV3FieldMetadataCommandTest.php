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

    public function fetch(string $webhook, string $methodName): array
    {
        $this->lastWebhook = $webhook;
        $this->lastMethodName = $methodName;

        return $this->response;
    }
}

class ShowV3FieldMetadataCommandTest extends TestCase
{
    private const SCHEMA_FIXTURE = __DIR__ . '/../../../../OpenApi/Domain/fixtures/openapi-field-list-methods.json';

    /**
     * @var array<string, string|null>
     */
    private array $originalEnvironment = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach ($this->getEnvNames() as $envName) {
            $value = getenv($envName);
            $this->originalEnvironment[$envName] = $value === false ? null : $value;
            $this->writeEnv($envName, null);
        }
    }

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
        $fetcher = new SpyBitrix24V3FieldMetadataFetcher([
            'ID' => ['title' => 'Task ID', 'type' => 'integer'],
        ]);
        $tester = new CommandTester($this->createCommand($fetcher));
        $tester->setInputs(['1']);

        $status = $tester->execute([
            '--schema-file' => self::SCHEMA_FIXTURE,
        ], [
            'interactive' => true,
            'decorated' => false,
        ]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertSame('tasks.task.field.list', $fetcher->lastMethodName);
        $this->assertStringContainsString('main.eventlog', $tester->getDisplay());
        $this->assertStringContainsString('tasks.task.access', $tester->getDisplay());
    }

    #[Test]
    public function itResolvesExactEntityKeyToTheExpectedMethod(): void
    {
        $fetcher = new SpyBitrix24V3FieldMetadataFetcher([
            'ID' => ['title' => 'Task ID', 'type' => 'integer'],
        ]);
        $tester = new CommandTester($this->createCommand($fetcher));

        $status = $tester->execute([
            'entity' => 'tasks.task',
            '--schema-file' => self::SCHEMA_FIXTURE,
            '--webhook' => 'https://cli.example/rest/1/token/',
        ], [
            'decorated' => false,
        ]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertSame('tasks.task.field.list', $fetcher->lastMethodName);
        $this->assertSame('https://cli.example/rest/1/token/', $fetcher->lastWebhook);
    }

    #[Test]
    public function itPrintsJsonOutputWithCompleteMetadataPayload(): void
    {
        $fetcher = new SpyBitrix24V3FieldMetadataFetcher([
            'ID' => ['title' => 'Task ID', 'type' => 'integer', 'isImmutable' => true],
            'XML_ID' => ['TYPE' => 'string'],
        ]);
        $tester = new CommandTester($this->createCommand($fetcher));

        $status = $tester->execute([
            'entity' => 'tasks.task',
            '--schema-file' => self::SCHEMA_FIXTURE,
            '--webhook' => 'https://cli.example/rest/1/token/',
        ], [
            'decorated' => false,
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('"code": "ID"', $display);
        $this->assertStringContainsString('"title": "Task ID"', $display);
        $this->assertStringContainsString('"isImmutable": true', $display);
        $this->assertStringContainsString('"TYPE": "string"', $display);
    }

    #[Test]
    public function itUnwrapsSingleMetadataCollectionForJsonOutput(): void
    {
        $fetcher = new SpyBitrix24V3FieldMetadataFetcher([
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
        $tester = new CommandTester($this->createCommand($fetcher));

        $status = $tester->execute([
            'entity' => 'tasks.task',
            '--schema-file' => self::SCHEMA_FIXTURE,
            '--webhook' => 'https://cli.example/rest/1/token/',
        ], [
            'decorated' => false,
        ]);

        $display = $tester->getDisplay();

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
        $fetcher = new SpyBitrix24V3FieldMetadataFetcher([
            'ID' => ['title' => 'Task ID', 'type' => 'integer', 'isImmutable' => true],
        ]);
        $tester = new CommandTester($this->createCommand($fetcher));

        $status = $tester->execute([
            'entity' => 'tasks.task',
            '--schema-file' => self::SCHEMA_FIXTURE,
            '--webhook' => 'https://cli.example/rest/1/token/',
            '--format' => 'table',
        ], [
            'decorated' => false,
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('code', $display);
        $this->assertStringContainsString('title', $display);
        $this->assertStringContainsString('metadata', $display);
        $this->assertStringContainsString('{"title":"Task ID","type":"integer","isImmutable":true}', $display);
    }

    #[Test]
    public function itRendersUnwrappedMetadataCollectionAsDirectTableRows(): void
    {
        $fetcher = new SpyBitrix24V3FieldMetadataFetcher([
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
        $tester = new CommandTester($this->createCommand($fetcher));

        $status = $tester->execute([
            'entity' => 'tasks.task',
            '--schema-file' => self::SCHEMA_FIXTURE,
            '--webhook' => 'https://cli.example/rest/1/token/',
            '--format' => 'table',
        ], [
            'decorated' => false,
        ]);

        $display = $tester->getDisplay();

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
        $fetcher = new SpyBitrix24V3FieldMetadataFetcher([]);
        $tester = new CommandTester($this->createCommand($fetcher));

        $status = $tester->execute([
            'entity' => 'tasks.task',
            '--schema-file' => self::SCHEMA_FIXTURE,
        ], [
            'interactive' => false,
            'decorated' => false,
        ]);

        $this->assertSame(Command::INVALID, $status);
        $this->assertStringContainsString(
            'Webhook is not configured. Pass --webhook or set BITRIX24_WEBHOOK',
            $tester->getDisplay()
        );
        $this->assertStringContainsString(
            'tests/.env.local',
            $tester->getDisplay()
        );
    }

    #[Test]
    public function legacyFieldDescriptionHelpMarksTheCommandAsLegacy(): void
    {
        $application = new Application();
        $application->setAutoExit(false);
        $application->addCommand(new ShowFieldsDescriptionCommand(new SplashScreen(), new NullLogger()));

        $tester = new ApplicationTester($application);
        $status = $tester->run([
            'command' => 'b24-dev:show-fields-description',
            '--help' => true,
        ], [
            'decorated' => false,
        ]);

        $display = $tester->getDisplay();

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('legacy', strtolower($display));
        $this->assertStringContainsString('b24-dev:show-v3-field-metadata', $display);
    }

    private function createCommand(SpyBitrix24V3FieldMetadataFetcher $fetcher): ShowV3FieldMetadataCommand
    {
        $command = new ShowV3FieldMetadataCommand(
            new OaFieldListMethodResolver(
                new OaSchemaMethodReader(new Filesystem(), new OaToSdkMethodNormalizationPolicy())
            ),
            new DevWebhookResolver(),
            $fetcher
        );

        $application = new Application();
        $application->setAutoExit(false);
        $application->addCommand($command);

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
