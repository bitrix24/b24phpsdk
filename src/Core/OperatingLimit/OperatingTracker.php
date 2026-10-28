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

use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Events\OperatingThresholdCrossedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Tracks method operating time from api responses and dispatches threshold events
 *
 * @see https://apidocs.bitrix24.com/limits.html
 */
class OperatingTracker
{
    /**
     * @var array<string, array<int, int|null>> method => [threshold => operating_reset_at of the last event], absent threshold is armed
     */
    private array $firedThresholds = [];

    /**
     * @var array<string, int> method => last seen operating_reset_at
     */
    private array $lastOperatingResetAt = [];

    public function __construct(
        private readonly OperatingLimitConfig $config,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param array<string, mixed> $responseBody decoded api response
     */
    public function observeResponse(Command $command, array $responseBody): void
    {
        if ($command->getApiMethod() !== 'batch') {
            if (isset($responseBody['time']) && is_array($responseBody['time'])) {
                $this->observe($command->getApiMethod(), $responseBody['time']);
            }

            return;
        }

        // outer batch call is not accounted in operating, only nested commands
        $resultTime = $responseBody['result']['result_time'] ?? null;
        if (!is_array($resultTime)) {
            return;
        }

        foreach ($resultTime as $batchCommandId => $time) {
            $apiMethod = self::resolveBatchCommandMethod($command, (string)$batchCommandId);
            if ($apiMethod === null || !is_array($time)) {
                continue;
            }

            $this->observe($apiMethod, $time);
        }
    }

    public function getLastOperatingResetAt(string $apiMethod): ?int
    {
        return $this->lastOperatingResetAt[$apiMethod] ?? null;
    }

    public static function resolveBatchCommandMethod(Command $command, string $batchCommandId): ?string
    {
        $commands = $command->getParameters()['cmd'] ?? null;
        if (!is_array($commands) || !isset($commands[$batchCommandId]) || !is_string($commands[$batchCommandId])) {
            return null;
        }

        return explode('?', $commands[$batchCommandId], 2)[0];
    }

    /**
     * @param array<string, mixed> $time
     */
    private function observe(string $apiMethod, array $time): void
    {
        $operating = (float)($time['operating'] ?? 0);
        $resetAt = isset($time['operating_reset_at']) ? (int)$time['operating_reset_at'] : null;
        if ($resetAt !== null) {
            $this->lastOperatingResetAt[$apiMethod] = $resetAt;
        }

        if ($operating <= 0) {
            return;
        }

        $load = $operating / $this->config->limitSeconds * 100;
        foreach ($this->config->thresholds as $threshold) {
            if ($load < $threshold) {
                unset($this->firedThresholds[$apiMethod][$threshold]);
                continue;
            }

            $isFired = array_key_exists($threshold, $this->firedThresholds[$apiMethod] ?? []);
            $firedResetAt = $this->firedThresholds[$apiMethod][$threshold] ?? null;
            // fire on crossing, or again when the window moved but load is still above the threshold
            if (!$isFired || ($resetAt !== null && $firedResetAt !== null && $resetAt > $firedResetAt)) {
                $this->firedThresholds[$apiMethod][$threshold] = $resetAt;
                $this->eventDispatcher->dispatch(new OperatingThresholdCrossedEvent(
                    $apiMethod,
                    $operating,
                    $resetAt,
                    $threshold,
                    $this->config->limitSeconds
                ));
            }
        }
    }
}
