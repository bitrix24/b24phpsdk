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

namespace Bitrix24\SDK\Infrastructure\Console\Commands\Documentation;

final readonly class LegacySdkCoverageResult
{
    /**
     * @param list<string> $coveredMethods
     * @param list<string> $uncoveredMethods
     * @param list<string> $sdkOnlyMethods
     */
    public function __construct(
        public int $totalPortalMethods,
        public array $coveredMethods,
        public array $uncoveredMethods,
        public array $sdkOnlyMethods,
        public float $coveragePercentage,
    ) {
    }
}
