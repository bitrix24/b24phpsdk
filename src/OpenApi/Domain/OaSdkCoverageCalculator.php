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

namespace Bitrix24\SDK\OpenApi\Domain;

use Bitrix24\SDK\Attributes\Services\SupportedInSdkApiMethod;
use Bitrix24\SDK\Core\Contracts\ApiVersion;

readonly class OaSdkCoverageCalculator
{
    public function __construct(
        private OaToSdkMethodNormalizationPolicy $normalizationPolicy,
    ) {
    }

    /**
     * @param list<string> $oaMethodNames
     * @param list<SupportedInSdkApiMethod> $supportedInSdkApiMethods
     */
    public function calculate(array $oaMethodNames, array $supportedInSdkApiMethods): OaSdkCoverageResult
    {
        $sdkV3Methods = array_values(array_filter(
            $supportedInSdkApiMethods,
            static fn (SupportedInSdkApiMethod $supportedInSdkApiMethod): bool => $supportedInSdkApiMethod->apiVersion === ApiVersion::v3
        ));

        $sdkMethodNames = [];
        $scopeMismatchDiagnostics = [];
        foreach ($sdkV3Methods as $supportedInSdkApiMethod) {
            $sdkMethodNames[$supportedInSdkApiMethod->name] = true;

            $scopeMismatchDiagnostic = $this->normalizationPolicy->getScopeMismatchDiagnostic(
                $supportedInSdkApiMethod->name,
                $supportedInSdkApiMethod->sdkScope
            );
            if ($scopeMismatchDiagnostic !== null) {
                $scopeMismatchDiagnostics[] = $scopeMismatchDiagnostic;
            }
        }

        $coveredMethods = array_values(array_intersect($oaMethodNames, array_keys($sdkMethodNames)));
        $uncoveredMethods = array_values(array_diff($oaMethodNames, array_keys($sdkMethodNames)));
        $sdkOnlyMethods = array_values(array_diff(array_keys($sdkMethodNames), $oaMethodNames));

        sort($coveredMethods);
        sort($uncoveredMethods);
        sort($sdkOnlyMethods);
        sort($scopeMismatchDiagnostics);

        $coveragePercentage = 0.0;
        if (count($oaMethodNames) > 0) {
            $coveragePercentage = round((count($coveredMethods) * 100) / count($oaMethodNames), 2);
        }

        return new OaSdkCoverageResult(
            totalOaMethods: count($oaMethodNames),
            totalCoveredMethods: count($coveredMethods),
            uncoveredMethods: $uncoveredMethods,
            sdkOnlyMethods: $sdkOnlyMethods,
            coveragePercentage: $coveragePercentage,
            scopeBreakdown: $this->buildScopeBreakdown($oaMethodNames, $coveredMethods),
            scopeMismatchDiagnostics: $scopeMismatchDiagnostics,
        );
    }

    /**
     * @param list<string> $oaMethodNames
     * @param list<string> $coveredMethods
     * @return array<string, array{
     *     totalOaMethods: int,
     *     coveredMethods: int,
     *     uncoveredMethods: int,
     *     coveragePercentage: float
     * }>
     */
    private function buildScopeBreakdown(array $oaMethodNames, array $coveredMethods): array
    {
        $coveredMethodNames = array_fill_keys($coveredMethods, true);
        $scopeBreakdown = [];

        foreach ($oaMethodNames as $oaMethodName) {
            $scope = $this->normalizationPolicy->deriveScope($oaMethodName);
            if (!array_key_exists($scope, $scopeBreakdown)) {
                $scopeBreakdown[$scope] = [
                    'totalOaMethods' => 0,
                    'coveredMethods' => 0,
                    'uncoveredMethods' => 0,
                    'coveragePercentage' => 0.0,
                ];
            }

            $scopeBreakdown[$scope]['totalOaMethods']++;
            if (array_key_exists($oaMethodName, $coveredMethodNames)) {
                $scopeBreakdown[$scope]['coveredMethods']++;
            } else {
                $scopeBreakdown[$scope]['uncoveredMethods']++;
            }
        }

        ksort($scopeBreakdown);
        foreach ($scopeBreakdown as &$scopeMetrics) {
            $scopeMetrics['coveragePercentage'] = round(
                ($scopeMetrics['coveredMethods'] * 100) / $scopeMetrics['totalOaMethods'],
                2
            );
        }
        unset($scopeMetrics);

        return $scopeBreakdown;
    }
}
