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

    private function buildCommand(V3BuilderCoverageReport $report): ShowV3BuilderCoverageCommand
    {
        $auditor = $this->createStub(V3BuilderCoverageAuditor::class);
        $auditor->method('audit')->willReturn($report);

        return new ShowV3BuilderCoverageCommand($auditor, new Finder(), new NullLogger());
    }

    #[Test]
    public function testSummaryCountersAppearInDefaultOutput(): void
    {
        $tester = new CommandTester($this->buildCommand($this->cleanReport));
        $tester->execute(['scope' => 'task']);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('OpenAPI DTO count:', $tester->getDisplay());
        self::assertStringContainsString('Mapped SDK entities:', $tester->getDisplay());
    }

    #[Test]
    public function testFormatJsonOutputsValidJson(): void
    {
        $tester = new CommandTester($this->buildCommand($this->cleanReport));
        $tester->execute(['scope' => 'task', '--format' => 'json']);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        $decoded = json_decode($tester->getDisplay(), true);
        self::assertIsArray($decoded);
        self::assertArrayHasKey('totalOpenApiEntities', $decoded);
        self::assertArrayHasKey('duplicateEntityKeyMappings', $decoded);
    }

    #[Test]
    public function testShowUnmappedPrintsTable(): void
    {
        $tester = new CommandTester($this->buildCommand($this->reportWithIssues));
        $tester->execute(['scope' => 'task', '--show-unmapped' => true]);

        self::assertStringContainsString('some.unmapped.dto', $tester->getDisplay());
    }

    #[Test]
    public function testShowMissingSelectPrintsTable(): void
    {
        $tester = new CommandTester($this->buildCommand($this->reportWithIssues));
        $tester->execute(['scope' => 'task', '--show-missing-select' => true]);

        self::assertStringContainsString('some.missing.select', $tester->getDisplay());
    }

    #[Test]
    public function testShowMissingItemPrintsTable(): void
    {
        $tester = new CommandTester($this->buildCommand($this->reportWithIssues));
        $tester->execute(['scope' => 'task', '--show-missing-item' => true]);

        self::assertStringContainsString('some.missing.item', $tester->getDisplay());
    }

    #[Test]
    public function testShowInvalidPrintsTable(): void
    {
        $tester = new CommandTester($this->buildCommand($this->reportWithIssues));
        $tester->execute(['scope' => 'task', '--show-invalid' => true]);

        self::assertStringContainsString('BadClass', $tester->getDisplay());
        self::assertStringContainsString('class does not exist', $tester->getDisplay());
    }

    #[Test]
    public function testShowSelectMismatchesPrintsTable(): void
    {
        $tester = new CommandTester($this->buildCommand($this->reportWithIssues));
        $tester->execute(['scope' => 'task', '--show-select-mismatches' => true]);

        self::assertStringContainsString('partial.entity', $tester->getDisplay());
        self::assertStringContainsString('title', $tester->getDisplay());
    }

    #[Test]
    public function testShowDuplicatesPrintsTable(): void
    {
        $tester = new CommandTester($this->buildCommand($this->reportWithIssues));
        $tester->execute(['scope' => 'task', '--show-duplicates' => true]);

        self::assertStringContainsString('dup.entity', $tester->getDisplay());
        self::assertStringContainsString('ResultA', $tester->getDisplay());
    }

    #[Test]
    public function testScopeTaskScansRealDirectoryAndCallsAuditor(): void
    {
        $auditor = $this->createMock(V3BuilderCoverageAuditor::class);
        $auditor->expects($this->once())
            ->method('audit')
            ->willReturn($this->cleanReport);

        $command = new ShowV3BuilderCoverageCommand($auditor, new Finder(), new NullLogger());
        $tester = new CommandTester($command);
        $tester->execute(['scope' => 'task']);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
    }

    #[Test]
    public function testNonExistentScopeReturnsInvalid(): void
    {
        $tester = new CommandTester($this->buildCommand($this->cleanReport));
        $tester->execute(['scope' => 'nonexistentscope99']);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
    }

    #[Test]
    public function testInvalidFormatReturnsInvalid(): void
    {
        $tester = new CommandTester($this->buildCommand($this->cleanReport));
        $tester->execute(['scope' => 'task', '--format' => 'xml']);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
    }
}
