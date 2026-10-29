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

namespace Bitrix24\SDK\Core\OperatingLimit;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

/**
 * Settings for tracking method operating time against the Bitrix24 resource limit window
 *
 * @see https://apidocs.bitrix24.com/limits.html
 */
final readonly class OperatingLimitConfig
{
    /**
     * @var list<int> thresholds in percent of the limit, sorted ascending, unique
     */
    public array $thresholds;

    /**
     * @param float $limitSeconds method operating time limit in seconds within the 10-minute window
     * @param array<int> $thresholds percents of the limit, each in range 1..99
     * @throws InvalidArgumentException
     */
    public function __construct(
        public float $limitSeconds = 480.0,
        array $thresholds = [10, 20, 30, 40, 50, 60, 70, 80, 90]
    ) {
        if ($limitSeconds <= 0) {
            throw new InvalidArgumentException(sprintf('operating limit must be greater than zero, %s given', $limitSeconds));
        }

        if ($thresholds === []) {
            throw new InvalidArgumentException('operating thresholds list cannot be empty');
        }

        foreach ($thresholds as $threshold) {
            if (!is_int($threshold)) {
                throw new InvalidArgumentException(sprintf('operating threshold must be integer, %s given', get_debug_type($threshold)));
            }

            if ($threshold <= 0 || $threshold >= 100) {
                throw new InvalidArgumentException(sprintf('operating threshold must be in range 1..99, %s given', $threshold));
            }
        }

        $thresholds = array_values(array_unique($thresholds));
        sort($thresholds);
        $this->thresholds = $thresholds;
    }
}
