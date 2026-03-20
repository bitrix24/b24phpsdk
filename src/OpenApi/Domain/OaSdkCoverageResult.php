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

readonly class OaSdkCoverageResult
{
    /**
     * @param list<string> $uncoveredMethods
     * @param list<string> $sdkOnlyMethods
     * @param array<string, array{
     *     totalOaMethods: int,
     *     coveredMethods: int,
     *     uncoveredMethods: int,
     *     coveragePercentage: float
     * }> $scopeBreakdown
     * @param list<string> $scopeMismatchDiagnostics
     */
    public function __construct(
        public int $totalOaMethods,
        public int $totalCoveredMethods,
        public array $uncoveredMethods,
        public array $sdkOnlyMethods,
        public float $coveragePercentage,
        public array $scopeBreakdown,
        public array $scopeMismatchDiagnostics,
    ) {
    }
}
