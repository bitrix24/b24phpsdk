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

use Bitrix24\SDK\Events\OperatingThresholdCrossedEvent;
use Closure;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Ready-made listener: pauses the current process until the oldest operating basket is released
 *
 * Register it on the event dispatcher passed to ServiceBuilderFactory:
 * $dispatcher->addListener(OperatingThresholdCrossedEvent::class, new OperatingLimitSleepListener(90));
 */
final class OperatingLimitSleepListener
{
    private readonly LoggerInterface $logger;

    private readonly Closure $sleep;

    private readonly Closure $now;

    /**
     * @param int $sleepFromThresholdPercent sleep on events with threshold at or above this value
     * @param int $safetyMarginSeconds extra seconds to wait after operating_reset_at
     * @param Closure(int): void|null $sleep
     * @param Closure(): int|null $now current unix timestamp
     */
    public function __construct(
        private readonly int $sleepFromThresholdPercent = 90,
        private readonly int $safetyMarginSeconds = 1,
        ?LoggerInterface $logger = null,
        ?Closure $sleep = null,
        ?Closure $now = null
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->sleep = $sleep ?? static function (int $seconds): void {
            sleep($seconds);
        };
        $this->now = $now ?? time(...);
    }

    public function __invoke(OperatingThresholdCrossedEvent $event): void
    {
        if ($event->getThresholdPercent() < $this->sleepFromThresholdPercent) {
            return;
        }

        $resetAt = $event->getOperatingResetAt();
        if ($resetAt === null) {
            return;
        }

        $seconds = $resetAt->getTimestamp() + $this->safetyMarginSeconds - ($this->now)();
        if ($seconds <= $this->safetyMarginSeconds) {
            return;
        }

        $this->logger->info('operatingLimit.sleep', [
            'apiMethod' => $event->getApiMethod(),
            'operating' => $event->getOperating(),
            'thresholdPercent' => $event->getThresholdPercent(),
            'seconds' => $seconds,
        ]);
        ($this->sleep)($seconds);
    }
}
