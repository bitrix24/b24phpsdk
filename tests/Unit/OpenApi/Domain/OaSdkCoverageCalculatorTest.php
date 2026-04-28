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

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\Attributes\Services\SupportedInSdkApiMethod;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\OpenApi\Domain\OaSdkCoverageCalculator;
use Bitrix24\SDK\OpenApi\Domain\OaToSdkMethodNormalizationPolicy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OaSdkCoverageCalculatorTest extends TestCase
{
    #[Test]
    public function itCalculatesCoverageFromOpenApiMethodsAndSdkV3Methods(): void
    {
        $oaSdkCoverageCalculator = new OaSdkCoverageCalculator(new OaToSdkMethodNormalizationPolicy());

        $oaSdkCoverageResult = $oaSdkCoverageCalculator->calculate(
            ['documentation', 'main.eventlog.list', 'tasks.task.get'],
            [
                $this->createMethod('documentation', '', ApiVersion::v3),
                $this->createMethod('tasks.task.get', 'task', ApiVersion::v3),
                $this->createMethod('legacy.scope.method', 'legacy', ApiVersion::v1),
                $this->createMethod('tasks.task.add', 'task', ApiVersion::v3),
            ]
        );

        $this->assertSame(3, $oaSdkCoverageResult->totalOaMethods);
        $this->assertSame(2, $oaSdkCoverageResult->totalCoveredMethods);
        $this->assertSame(['main.eventlog.list'], $oaSdkCoverageResult->uncoveredMethods);
        $this->assertSame(['tasks.task.add'], $oaSdkCoverageResult->sdkOnlyMethods);
        $this->assertSame(66.67, $oaSdkCoverageResult->coveragePercentage);
        $this->assertSame(1, $oaSdkCoverageResult->scopeBreakdown['main']['uncoveredMethods']);
        $this->assertSame(1, $oaSdkCoverageResult->scopeBreakdown['tasks']['coveredMethods']);
        $this->assertSame(1, $oaSdkCoverageResult->scopeBreakdown['–']['coveredMethods']);
        $this->assertSame([], $oaSdkCoverageResult->scopeMismatchDiagnostics);
    }

    #[Test]
    public function itReportsScopeMismatchDiagnostics(): void
    {
        $oaSdkCoverageCalculator = new OaSdkCoverageCalculator(new OaToSdkMethodNormalizationPolicy());

        $oaSdkCoverageResult = $oaSdkCoverageCalculator->calculate(
            ['main.eventlog.list'],
            [
                $this->createMethod('main.eventlog.list', 'task', ApiVersion::v3),
            ]
        );

        $this->assertCount(1, $oaSdkCoverageResult->scopeMismatchDiagnostics);
        $this->assertStringContainsString('main.eventlog.list', $oaSdkCoverageResult->scopeMismatchDiagnostics[0]);
    }

    private function createMethod(string $name, string $sdkScope, ApiVersion $apiVersion): SupportedInSdkApiMethod
    {
        return new SupportedInSdkApiMethod(
            sdkScope: $sdkScope,
            name: $name,
            documentationUrl: 'https://example.com/' . $name,
            description: null,
            isDeprecated: false,
            deprecationMessage: null,
            sdkMethodName: 'methodName',
            sdkMethodFileName: 'src/Services/Example.php',
            sdkMethodFileStartLine: 10,
            sdkMethodFileEndLine: 20,
            sdkClassName: 'Bitrix24\\SDK\\Services\\Example',
            apiVersion: $apiVersion,
            sdkReturnTypeClass: null,
            sdkReturnTypeFileName: null,
            sdkReturnTypeDeclaration: null,
        );
    }
}
