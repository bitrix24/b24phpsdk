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

namespace Bitrix24\SDK\Tests\Integration\Infrastructure\Console;

use Bitrix24\SDK\Attributes\Services\AttributesParser;
use Bitrix24\SDK\Deprecations\DeprecatedMethods;
use Bitrix24\SDK\Infrastructure\Console\Commands\Documentation\ShowCoverageStatisticsCommand;
use Bitrix24\SDK\Infrastructure\Console\Commands\SplashScreen;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Typhoon\Reflection\TyphoonReflector;

#[CoversClass(ShowCoverageStatisticsCommand::class)]
class ShowCoverageStatisticsCommandTest extends TestCase
{
    public function testReadOnlyScopeReportRunsWithoutInteractiveInput(): void
    {
        $scopeReport = $this->runReport('scope-stat');
        self::assertSame('v1', $scopeReport['apiVersion']);
        self::assertSame('configured-portal', $scopeReport['basis']);
        self::assertSame('scope-stat', $scopeReport['show']);
        self::assertCount(1, $scopeReport['scopes']);
        self::assertSame('crm', $scopeReport['scopes'][0]['scope']);
        foreach ([$scopeReport['summary'], $scopeReport['scopes'][0]] as $summary) {
            self::assertSame($summary['totalPortalMethods'], $summary['coveredMethodsCount'] + $summary['uncoveredMethodsCount']);
            self::assertGreaterThanOrEqual(0, $summary['sdkOnlyMethodsCount']);
            self::assertSame($summary['totalPortalMethods'] === 0 ? 0.0 : round(100 * $summary['coveredMethodsCount'] / $summary['totalPortalMethods'], 2), $summary['coveragePercentage']);
        }

        $uncovered = $this->runReport('uncovered');
        self::assertSame('crm', $uncovered['scope']);
        self::assertCount($scopeReport['scopes'][0]['uncoveredMethodsCount'], $uncovered['uncoveredMethodsIncludingDeprecated']);
        self::assertCount($scopeReport['scopes'][0]['sdkOnlyMethodsCount'], $uncovered['sdkOnlyMethods']);
        self::assertSame([], array_values(array_diff($uncovered['uncoveredMethods'], $uncovered['uncoveredMethodsIncludingDeprecated'])));
        foreach (['uncoveredMethods', 'uncoveredMethodsIncludingDeprecated', 'sdkOnlyMethods'] as $key) {
            $normalized = array_values(array_unique(array_map(strtolower(...), $uncovered[$key])));
            sort($normalized);
            self::assertSame($normalized, $uncovered[$key]);
        }
    }

    private function runReport(string $show): array
    {
        $nullLogger = new NullLogger();
        $showCoverageStatisticsCommand = new ShowCoverageStatisticsCommand(
            new AttributesParser(TyphoonReflector::build(), new Filesystem()),
            new ServiceBuilderFactory(new EventDispatcher(), $nullLogger),
            new Finder(),
            new SplashScreen(),
            new DeprecatedMethods(),
            $nullLogger,
        );
        new Application()->addCommand($showCoverageStatisticsCommand);
        $commandTester = new CommandTester($showCoverageStatisticsCommand);
        $commandTester->execute([
            '--webhook' => $_ENV['BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK'] ?? $_ENV['BITRIX24_WEBHOOK'],
            '--show' => $show,
            '--scope' => 'crm',
            '--format' => 'json',
        ], ['interactive' => false, 'capture_stderr_separately' => true]);
        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode(), $commandTester->getErrorOutput());
        self::assertSame('', $commandTester->getErrorOutput());

        return json_decode($commandTester->getDisplay(), true, 512, JSON_THROW_ON_ERROR);
    }
}
