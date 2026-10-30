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

use Bitrix24\SDK\Attributes\Services\AttributesParser;
use Bitrix24\SDK\Attributes\Services\SupportedInSdkApiMethod;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Deprecations\DeprecatedMethods;
use Bitrix24\SDK\Infrastructure\Console\Commands\Documentation\ShowCoverageStatisticsCommand;
use Bitrix24\SDK\Infrastructure\Console\Commands\SplashScreen;
use Bitrix24\SDK\Services\Main\MainServiceBuilder;
use Bitrix24\SDK\Services\Main\Service\Main;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Typhoon\Reflection\TyphoonReflector;

#[CoversClass(ShowCoverageStatisticsCommand::class)]
class ShowCoverageStatisticsCommandTest extends TestCase
{
    public function testSummaryMatchesUniqueLegacyMethods(): void
    {
        $tester = $this->runCommand(['TASKS.A', 'tasks.a', 'tasks.b'], ['0']);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
        self::assertStringContainsString('Portal methods: 2', $tester->getDisplay());
        self::assertStringContainsString('Covered by SDK: 1', $tester->getDisplay());
        self::assertStringContainsString('Not covered by SDK: 1', $tester->getDisplay());
        self::assertStringContainsString('SDK-only methods: 1', $tester->getDisplay());
        self::assertStringContainsString('Coverage: 50.00%', $tester->getDisplay());
    }

    public function testEmptyPortalIsReportedWithoutDivisionByZero(): void
    {
        $tester = $this->runCommand([], ['0']);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
        self::assertStringContainsString('Coverage: 0.00% (no available methods)', $tester->getDisplay());
        self::assertStringContainsString('SDK-only methods: 2', $tester->getDisplay());
    }

    public function testScopeTableUsesTheSameCalculation(): void
    {
        $tester = $this->runCommand(['tasks.a', 'tasks.b'], ['1', '0']);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
        self::assertMatchesRegularExpression('/\| task\s+\| 2\s+\| 1\s+\| 1\s+\| 1\s+\| 50\.00%\s+\|/', $tester->getDisplay());
        self::assertStringContainsString('Scope totals may overlap', $tester->getDisplay());
    }

    public function testUncoveredListExcludesV3AndNormalizesDeprecatedNames(): void
    {
        $scopeIndex = (string)(array_search('task', Scope::getAvailableScopeCodes(), true) + 1);
        $tester = $this->runCommand(['TASKS.A', 'tasks.b', 'TASKS.B', 'tasks.old'], ['2', $scopeIndex, '0']);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
        self::assertStringContainsString('Unsupported in SDK methods (with deprecated): 2', $tester->getDisplay());
        self::assertMatchesRegularExpression('/Unsupported in SDK methods: 1.*?tasks\.b/s', $tester->getDisplay());
        self::assertStringContainsString('SDK-only methods in scope: 1', $tester->getDisplay());
        self::assertStringContainsString('tasks.sdkonly', $tester->getDisplay());
    }

    public function testScopeTableCountsWrappersWithDifferentScopeMetadata(): void
    {
        $tester = $this->runCommand(['tasks.a'], ['1', '0'], $this->parserWithDifferentScopeMetadata());

        self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
        self::assertMatchesRegularExpression('/\| task\s+\| 1\s+\| 1\s+\| 0\s+\| 1\s+\| 100\.00%\s+\|/', $tester->getDisplay());
    }

    public function testUncoveredListCountsWrappersWithDifferentScopeMetadata(): void
    {
        $scopeIndex = (string)(array_search('task', Scope::getAvailableScopeCodes(), true) + 1);
        $tester = $this->runCommand(['tasks.a'], ['2', $scopeIndex, '0'], $this->parserWithDifferentScopeMetadata());

        self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
        self::assertStringContainsString('Unsupported in SDK methods (with deprecated): 0', $tester->getDisplay());
        self::assertStringContainsString('SDK-only methods in scope: 1', $tester->getDisplay());
        self::assertStringContainsString('tasks.sdkonly', $tester->getDisplay());
        self::assertStringNotContainsString('crm.unrelated', $tester->getDisplay());
    }

    public function testScopeStatisticsJsonHasOnlyOneDocument(): void
    {
        $tester = $this->runCommand(['TASKS.A', 'tasks.a', 'tasks.b'], [], null, [
            '--show' => 'scope-stat', '--scope' => 'task', '--format' => 'json',
        ], false);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getErrorOutput());
        $report = json_decode($tester->getDisplay(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('v1', $report['apiVersion']);
        self::assertSame('configured-portal', $report['basis']);
        self::assertSame('scope-stat', $report['show']);
        self::assertSame([
            'totalPortalMethods' => 2,
            'coveredMethodsCount' => 1,
            'uncoveredMethodsCount' => 1,
            'sdkOnlyMethodsCount' => 1,
            'coveragePercentage' => 50.0,
        ], $report['summary']);
        self::assertSame([['scope' => 'task', ...$report['summary']]], $report['scopes']);
        self::assertSame('', $tester->getErrorOutput());
    }

    public function testScopeStatisticsTableExitsWithoutInput(): void
    {
        $tester = $this->runCommand(['tasks.a', 'tasks.b'], [], null, ['--show' => 'scope-stat', '--scope' => 'task'], false);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getErrorOutput());
        self::assertMatchesRegularExpression('/\\| task\\s+\\| 2\\s+\\| 1\\s+\\| 1\\s+\\| 1\\s+\\| 50\\.00%\\s+\\|/', $tester->getDisplay());
        self::assertStringNotContainsString('Please, select', $tester->getDisplay());
    }

    public function testUncoveredJsonPreservesDeprecationAndScopeInventory(): void
    {
        $tester = $this->runCommand(['TASKS.OLD', 'tasks.z', 'TASKS.A', 'tasks.b', 'TASKS.B'], [], null, [
            '--show' => 'uncovered', '--scope' => 'task', '--format' => 'json',
        ], false);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getErrorOutput());
        $report = json_decode($tester->getDisplay(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('uncovered', $report['show']);
        self::assertSame('task', $report['scope']);
        self::assertSame(['tasks.b', 'tasks.z'], $report['uncoveredMethods']);
        self::assertSame(['tasks.b', 'tasks.old', 'tasks.z'], $report['uncoveredMethodsIncludingDeprecated']);
        self::assertSame(['tasks.sdkonly'], $report['sdkOnlyMethods']);
        self::assertSame(4, $report['summary']['totalPortalMethods']);
    }

    public function testScopeJsonUsesPortalMembershipInsteadOfWrapperScope(): void
    {
        $tester = $this->runCommand(['tasks.a'], [], $this->parserWithDifferentScopeMetadata(), [
            '--show' => 'uncovered', '--scope' => 'task', '--format' => 'json',
        ], false);

        $report = json_decode($tester->getDisplay(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([], $report['uncoveredMethods']);
        self::assertSame(['tasks.sdkonly'], $report['sdkOnlyMethods']);
    }

    public function testJsonHandlesEmptyPortalAndScopes(): void
    {
        $tester = $this->runCommand([], [], null, ['--show' => 'scope-stat', '--format' => 'json'], false);
        $report = json_decode($tester->getDisplay(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(0.0, $report['summary']['coveragePercentage']);
        self::assertCount(count(Scope::getAvailableScopeCodes()), $report['scopes']);
        foreach ($report['scopes'] as $scope) {
            self::assertSame(0, $scope['totalPortalMethods']);
            self::assertSame(0.0, $scope['coveragePercentage']);
        }

        $tester = $this->runCommand([], [], null, ['--show' => 'uncovered', '--scope' => 'crm', '--format' => 'json'], false);
        $report = json_decode($tester->getDisplay(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([], $report['uncoveredMethods']);
        self::assertSame([], $report['uncoveredMethodsIncludingDeprecated']);
        self::assertSame([], $report['sdkOnlyMethods']);
    }

    public function testInvalidOptionsFailBeforePortalInitialization(): void
    {
        foreach ([
            [], ['--show' => 'other'], ['--show' => 'scope-stat', '--format' => 'xml'],
            ['--show' => 'uncovered'], ['--show' => 'uncovered', '--scope' => 'other'],
            ['--show' => 'scope-stat', '--scope' => 'other'], ['--scope' => 'crm'], ['--format' => 'table'],
            ['--show' => ''], ['--show' => 'scope-stat', '--scope' => ''],
        ] as $arguments) {
            $tester = $this->runCommand([], [], null, $arguments, false, true);
            self::assertSame(Command::INVALID, $tester->getStatusCode(), $tester->getErrorOutput());
            self::assertSame('', $tester->getDisplay());
            self::assertNotSame('', $tester->getErrorOutput());
        }
    }

    public function testRuntimeFailureDoesNotExposeWebhookOrTrace(): void
    {
        $factory = $this->createStub(ServiceBuilderFactory::class);
        $factory->method('initFromWebhook')->willThrowException(new \RuntimeException('Request failed: https://example.com/rest/1/test/'));
        $tester = $this->runCommand([], [], null, ['--show' => 'scope-stat', '--format' => 'json'], false, false, $factory);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertSame('', $tester->getDisplay());
        self::assertStringContainsString('Unable to generate coverage report', $tester->getErrorOutput());
        self::assertStringNotContainsString('example.com', $tester->getErrorOutput());
        self::assertStringNotContainsString('Stack trace', $tester->getErrorOutput());
    }

    public function testJsonErrorsRemainParseableWithoutSeparateErrorOutput(): void
    {
        $tester = $this->runCommand([], [], null, ['--show' => 'uncovered', '--format' => 'json'], false, true, null, false);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        $error = json_decode($tester->getDisplay(), true, 512, JSON_THROW_ON_ERROR);
        self::assertStringContainsString('--scope', $error['error']);
    }

    private function parserWithDifferentScopeMetadata(): AttributesParser
    {
        $methods = [];
        foreach ([['telephony', 'tasks.a'], ['task', 'tasks.sdkonly'], ['crm', 'crm.unrelated']] as [$scope, $name]) {
            $methods[] = new SupportedInSdkApiMethod($scope, $name, null, null, false, null, 'method', 'test.php', 1, 2, 'Example', ApiVersion::v1, null, null, null);
        }

        $parser = $this->createStub(AttributesParser::class);
        $parser->method('getSupportedInSdkApiMethods')->willReturnCallback(static fn (array $classes, string $base, ?Scope $scope = null): array => $scope instanceof \Bitrix24\SDK\Core\Credentials\Scope ? array_values(array_filter($methods, static fn (SupportedInSdkApiMethod $method): bool => $scope->contains($method->sdkScope))) : $methods);
        $parser->method('getSupportedInSdkBatchMethods')->willReturn([]);

        return $parser;
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDiscoveryIncludesLegacyTaskService(): void
    {
        $attributesParser = new AttributesParser(TyphoonReflector::build(), new Filesystem());
        $tester = $this->runCommand(['tasks.task.get'], ['0'], $attributesParser);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
        self::assertTrue(class_exists(\Bitrix24\SDK\Legacy\Services\Task\Service\Task::class, false));
        self::assertStringContainsString('Covered by SDK: 1', $tester->getDisplay());
        self::assertStringContainsString('Coverage: 100.00%', $tester->getDisplay());
    }

    /** @param list<string> $portalMethods
     *  @param list<string> $inputs
     */
    private function runCommand(array $portalMethods, array $inputs, ?AttributesParser $parser = null, array $arguments = [], bool $interactive = true, bool $expectNoPortal = false, ?ServiceBuilderFactory $factory = null, bool $captureStderr = true): CommandTester
    {
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn(new ResponseData($portalMethods, Time::initWithZeroValues(), new Pagination(null, null)));
        $emptyResponse = $this->createStub(Response::class);
        $emptyResponse->method('getResponseData')->willReturn(new ResponseData([], Time::initWithZeroValues(), new Pagination(null, null)));
        $main = $this->createStub(Main::class);
        $main->method('getAvailableMethods')->willReturn($response);
        $main->method('getMethodsByScope')->willReturnCallback(static fn (string $scope): Response => $scope === 'task' ? $response : $emptyResponse);
        $mainBuilder = $this->createStub(MainServiceBuilder::class);
        $mainBuilder->method('main')->willReturn($main);
        $builder = $this->createStub(ServiceBuilder::class);
        $builder->method('getMainScope')->willReturn($mainBuilder);
        if ($expectNoPortal) {
            $factory = $this->createMock(ServiceBuilderFactory::class);
            $factory->expects(self::never())->method('initFromWebhook');
        } elseif (!$factory instanceof ServiceBuilderFactory) {
            $factory = $this->createStub(ServiceBuilderFactory::class);
            $factory->method('initFromWebhook')->willReturn($builder);
        }

        if (!$parser instanceof AttributesParser) {
            $methods = [];
            foreach ([['tasks.a', ApiVersion::v1], ['TASKS.A', ApiVersion::v1], ['tasks.b', ApiVersion::v3], ['tasks.sdkonly', ApiVersion::v1]] as [$name, $version]) {
                $methods[] = new SupportedInSdkApiMethod('task', $name, null, null, false, null, 'method', 'test.php', 1, 2, 'Example', $version, null, null, null);
            }

            $parser = $this->createStub(AttributesParser::class);
            $parser->method('getSupportedInSdkApiMethods')->willReturnCallback(static fn (array $classes, string $base, ?Scope $scope = null): array => !$scope instanceof \Bitrix24\SDK\Core\Credentials\Scope || $scope->contains('task') ? $methods : []);
            $parser->method('getSupportedInSdkBatchMethods')->willReturn([]);
        }

        $deprecated = $this->createStub(DeprecatedMethods::class);
        $deprecated->method('get')->willReturn(['TASKS.OLD']);
        $showCoverageStatisticsCommand = new ShowCoverageStatisticsCommand($parser, $factory, new Finder(), new SplashScreen(), $deprecated, new NullLogger());
        new Application()->addCommand($showCoverageStatisticsCommand);
        $commandTester = new CommandTester($showCoverageStatisticsCommand);
        $commandTester->setInputs($inputs);
        $commandTester->execute(['--webhook' => 'https://example.com/rest/1/test/', ...$arguments], ['interactive' => $interactive, 'capture_stderr_separately' => $captureStderr]);

        return $commandTester;
    }
}
