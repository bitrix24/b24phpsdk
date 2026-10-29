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

namespace Bitrix24\SDK\Events;

use Carbon\CarbonImmutable;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Method operating time crossed a threshold of the Bitrix24 resource limit window
 *
 * Listeners are called synchronously, so sleeping in a listener pauses the current batch before the next request
 *
 * @see https://apidocs.bitrix24.com/limits.html
 */
class OperatingThresholdCrossedEvent extends Event
{
    public function __construct(
        private readonly string $apiMethod,
        private readonly float $operating,
        private readonly ?int $operatingResetAt,
        private readonly int $thresholdPercent,
        private readonly float $limitSeconds
    ) {
    }

    public function getApiMethod(): string
    {
        return $this->apiMethod;
    }

    /**
     * @return float accumulated method execution time in seconds
     */
    public function getOperating(): float
    {
        return $this->operating;
    }

    /**
     * @return CarbonImmutable|null moment when the oldest one-minute basket is excluded from operating
     */
    public function getOperatingResetAt(): ?CarbonImmutable
    {
        return $this->operatingResetAt === null ? null : CarbonImmutable::createFromTimestamp($this->operatingResetAt);
    }

    public function getThresholdPercent(): int
    {
        return $this->thresholdPercent;
    }

    public function getLimitSeconds(): float
    {
        return $this->limitSeconds;
    }
}
