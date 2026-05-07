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

namespace Bitrix24\SDK\Tests\Unit\Infrastructure\Console\Commands\Documentation;

use Bitrix24\SDK\Infrastructure\Console\Commands\Documentation\ShowV3BuilderCoverageCommand;
use Bitrix24\SDK\OpenApi\Domain\V3BuilderCoverageAuditor;
use Bitrix24\SDK\OpenApi\Domain\V3BuilderCoverageReport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Finder\Finder;

#[CoversClass(ShowV3BuilderCoverageCommand::class)]
class ShowV3BuilderCoverageCommandTest extends TestCase
{
    private V3BuilderCoverageReport $cleanReport;

    private V3BuilderCoverageReport $reportWithIssues;

    #[\Override]
    protected function setUp(): void
    {
        $this->cleanReport = new V3BuilderCoverageReport(
            totalOpenApiEntities: 10,
            mappedEntities: 2,
            entitiesWithSelectBuilder: 2,
            entitiesWithItemBuilder: 2,
            unmappedEntities: [],
            missingSelectBuilders: [],
            missingItemBuilders: [],
            invalidBuilderReferences: [],
            selectCoverageMismatches: [],
            sdkOnlyMappings: [],
            duplicateEntityKeyMappings: [],
        );

        $this->reportWithIssues = new V3BuilderCoverageReport(
            totalOpenApiEntities: 10,
            mappedEntities: 2,
            entitiesWithSelectBuilder: 1,
            entitiesWithItemBuilder: 1,
            unmappedEntities: ['some.unmapped.dto'],
            missingSelectBuilders: ['some.missing.select'],
            missingItemBuilders: ['some.missing.item'],
            invalidBuilderReferences: [['entityKey' => 'bad.entity', 'class' => 'BadClass', 'reason' => 'class does not exist']],
            selectCoverageMismatches: [['entityKey' => 'partial.entity', 'builderClass' => 'PartialBuilder', 'missingFields' => ['title']]],
            sdkOnlyMappings: [['resultClass' => 'SomeResult', 'entityKey' => 'sdk.only.entity']],
            duplicateEntityKeyMappings: [['entityKey' => 'dup.entity', 'resultClasses' => ['ResultA', 'ResultB']]],
        );
    }

    private function buildCommand(V3BuilderCoverageReport $v3BuilderCoverageReport): ShowV3BuilderCoverageCommand
    {
        $auditor = $this->createStub(V3BuilderCoverageAuditor::class);
        $auditor->method('audit')->willReturn($v3BuilderCoverageReport);

        return new ShowV3BuilderCoverageCommand($auditor, new Finder(), new NullLogger());
    }

    #[Test]
    public function testSummaryCountersAppearInDefaultOutput(): void
    {
        $commandTester = new CommandTester($this->buildCommand($this->cleanReport));
        $commandTester->execute(['scope' => 'task']);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        self::assertStringContainsString('OpenAPI DTO count:', $commandTester->getDisplay());
        self::assertStringContainsString('Mapped SDK entities:', $commandTester->getDisplay());
    }

    #[Test]
    public function testFormatJsonOutputsValidJson(): void
    {
        $commandTester = new CommandTester($this->buildCommand($this->cleanReport));
        $commandTester->execute(['scope' => 'task', '--format' => 'json']);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $decoded = json_decode($commandTester->getDisplay(), true);
        self::assertIsArray($decoded);
        self::assertArrayHasKey('totalOpenApiEntities', $decoded);
        self::assertArrayHasKey('duplicateEntityKeyMappings', $decoded);
    }

    #[Test]
    public function testShowUnmappedPrintsTable(): void
    {
        $commandTester = new CommandTester($this->buildCommand($this->reportWithIssues));
        $commandTester->execute(['scope' => 'task', '--show-unmapped' => true]);

        self::assertStringContainsString('some.unmapped.dto', $commandTester->getDisplay());
    }

    #[Test]
    public function testShowMissingSelectPrintsTable(): void
    {
        $commandTester = new CommandTester($this->buildCommand($this->reportWithIssues));
        $commandTester->execute(['scope' => 'task', '--show-missing-select' => true]);

        self::assertStringContainsString('some.missing.select', $commandTester->getDisplay());
    }

    #[Test]
    public function testShowMissingItemPrintsTable(): void
    {
        $commandTester = new CommandTester($this->buildCommand($this->reportWithIssues));
        $commandTester->execute(['scope' => 'task', '--show-missing-item' => true]);

        self::assertStringContainsString('some.missing.item', $commandTester->getDisplay());
    }

    #[Test]
    public function testShowInvalidPrintsTable(): void
    {
        $commandTester = new CommandTester($this->buildCommand($this->reportWithIssues));
        $commandTester->execute(['scope' => 'task', '--show-invalid' => true]);

        self::assertStringContainsString('BadClass', $commandTester->getDisplay());
        self::assertStringContainsString('class does not exist', $commandTester->getDisplay());
    }

    #[Test]
    public function testShowSelectMismatchesPrintsTable(): void
    {
        $commandTester = new CommandTester($this->buildCommand($this->reportWithIssues));
        $commandTester->execute(['scope' => 'task', '--show-select-mismatches' => true]);

        self::assertStringContainsString('partial.entity', $commandTester->getDisplay());
        self::assertStringContainsString('title', $commandTester->getDisplay());
    }

    #[Test]
    public function testShowDuplicatesPrintsTable(): void
    {
        $commandTester = new CommandTester($this->buildCommand($this->reportWithIssues));
        $commandTester->execute(['scope' => 'task', '--show-duplicates' => true]);

        self::assertStringContainsString('dup.entity', $commandTester->getDisplay());
        self::assertStringContainsString('ResultA', $commandTester->getDisplay());
    }

    #[Test]
    public function testScopeTaskScansRealDirectoryAndCallsAuditor(): void
    {
        $auditor = $this->createMock(V3BuilderCoverageAuditor::class);
        $auditor->expects($this->once())
            ->method('audit')
            ->willReturn($this->cleanReport);

        $showV3BuilderCoverageCommand = new ShowV3BuilderCoverageCommand($auditor, new Finder(), new NullLogger());
        $commandTester = new CommandTester($showV3BuilderCoverageCommand);
        $commandTester->execute(['scope' => 'task']);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    #[Test]
    public function testNonExistentScopeReturnsInvalid(): void
    {
        $commandTester = new CommandTester($this->buildCommand($this->cleanReport));
        $commandTester->execute(['scope' => 'nonexistentscope99']);

        self::assertSame(Command::INVALID, $commandTester->getStatusCode());
    }

    #[Test]
    public function testInvalidFormatReturnsInvalid(): void
    {
        $commandTester = new CommandTester($this->buildCommand($this->cleanReport));
        $commandTester->execute(['scope' => 'task', '--format' => 'xml']);

        self::assertSame(Command::INVALID, $commandTester->getStatusCode());
    }
}
