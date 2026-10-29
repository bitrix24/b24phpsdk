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

namespace Bitrix24\SDK\Tests\Unit\Core\OperatingLimit;

use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\OperatingLimit\OperatingLimitConfig;
use Bitrix24\SDK\Core\OperatingLimit\OperatingTracker;
use Bitrix24\SDK\Events\OperatingThresholdCrossedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[CoversClass(OperatingTracker::class)]
#[CoversClass(OperatingLimitConfig::class)]
#[CoversClass(OperatingThresholdCrossedEvent::class)]
class OperatingTrackerTest extends TestCase
{
    /**
     * @var list<OperatingThresholdCrossedEvent>
     */
    private array $events = [];

    private OperatingTracker $tracker;

    protected function setUp(): void
    {
        $this->events = [];
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(OperatingThresholdCrossedEvent::class, function (OperatingThresholdCrossedEvent $operatingThresholdCrossedEvent): void {
            $this->events[] = $operatingThresholdCrossedEvent;
        });
        $this->tracker = new OperatingTracker(new OperatingLimitConfig(100.0), $eventDispatcher);
    }

    #[Test]
    public function testSingleCallCrossesThresholds(): void
    {
        $this->tracker->observeResponse(new Command('crm.contact.update', []), $this->single(25, 1000));

        $this->assertSame([10, 20], $this->thresholds());
        $this->assertSame('crm.contact.update', $this->events[0]->getApiMethod());
        $this->assertSame(25.0, $this->events[0]->getOperating());
        $this->assertSame(1000, $this->events[0]->getOperatingResetAt()?->getTimestamp());
        $this->assertSame(100.0, $this->events[0]->getLimitSeconds());
    }

    #[Test]
    public function testMultipleThresholdsInOneResponseAscending(): void
    {
        $command = new Command('crm.deal.update', []);
        $this->tracker->observeResponse($command, $this->single(5, 1000));
        $this->assertSame([], $this->thresholds());

        $this->tracker->observeResponse($command, $this->single(47, 1000));
        $this->assertSame([10, 20, 30, 40], $this->thresholds());
    }

    #[Test]
    public function testThresholdFiresOnceWhileResetAtUnchanged(): void
    {
        $command = new Command('crm.deal.update', []);
        $this->tracker->observeResponse($command, $this->single(15, 1000));
        $this->tracker->observeResponse($command, $this->single(15, 1000));

        $this->assertSame([10], $this->thresholds());
    }

    #[Test]
    public function testRefiresWhenResetAtMovesAndLoadStillHigh(): void
    {
        $command = new Command('crm.deal.update', []);
        $this->tracker->observeResponse($command, $this->single(92, 1000));
        $this->tracker->observeResponse($command, $this->single(91, 1060));

        $all = [10, 20, 30, 40, 50, 60, 70, 80, 90];
        $this->assertSame(array_merge($all, $all), $this->thresholds());
    }

    #[Test]
    public function testRefiresWhenFirstEventHadNoResetAt(): void
    {
        $command = new Command('crm.deal.update', []);
        $this->tracker->observeResponse($command, $this->single(15, null));
        $this->tracker->observeResponse($command, $this->single(15, 1000));
        $this->tracker->observeResponse($command, $this->single(15, 1000));
        $this->tracker->observeResponse($command, $this->single(15, 1060));

        $this->assertSame([10, 10, 10], $this->thresholds());
    }

    #[Test]
    public function testRearmsAfterDrop(): void
    {
        $command = new Command('crm.deal.update', []);
        $this->tracker->observeResponse($command, $this->single(15, 1000));
        $this->tracker->observeResponse($command, $this->single(5, 1000));
        $this->tracker->observeResponse($command, $this->single(15, 1000));

        $this->assertSame([10, 10], $this->thresholds());
    }

    #[Test]
    public function testNullResetAtFiresOnce(): void
    {
        $command = new Command('crm.deal.update', []);
        $this->tracker->observeResponse($command, $this->single(50, null));
        $this->tracker->observeResponse($command, $this->single(50, null));

        $this->assertSame([10, 20, 30, 40, 50], $this->thresholds());
        $this->assertNull($this->events[0]->getOperatingResetAt());
    }

    #[Test]
    public function testZeroOrMissingOperatingFiresNothing(): void
    {
        $command = new Command('crm.deal.update', []);
        $this->tracker->observeResponse($command, $this->single(0, 1000));
        $this->tracker->observeResponse($command, ['result' => [], 'time' => $this->time(null, null)]);
        $this->tracker->observeResponse($command, ['result' => []]);

        $this->assertSame([], $this->thresholds());
    }

    #[Test]
    public function testMethodsTrackedIndependently(): void
    {
        $command = new Command('batch', ['halt' => 0, 'cmd' => ['a' => 'crm.deal.update?ID=1', 'b' => 'crm.contact.update?ID=2']]);
        $this->tracker->observeResponse($command, $this->batch(['a' => $this->time(30, 1000), 'b' => $this->time(10, 1000)]));

        $byMethod = [];
        foreach ($this->events as $event) {
            $byMethod[$event->getApiMethod()][] = $event->getThresholdPercent();
        }

        $this->assertSame(['crm.deal.update' => [10, 20, 30], 'crm.contact.update' => [10]], $byMethod);
    }

    #[Test]
    public function testBatchTopLevelTimeTrackedAsBatchMethod(): void
    {
        // real portals report operating only in the top-level time of a batch response
        $command = new Command('batch', ['halt' => 0, 'cmd' => ['a' => 'crm.deal.update?ID=1']]);
        $body = $this->batch(['a' => $this->time(null, null)]);
        $body['time'] = $this->time(25, 1000);
        $this->tracker->observeResponse($command, $body);

        $this->assertSame([10, 20], $this->thresholds());
        $this->assertSame('batch', $this->events[0]->getApiMethod());
        $this->assertSame(1000, $this->tracker->getLastOperatingResetAt('batch'));
    }

    #[Test]
    public function testBatchSkipsCommandsWithoutTime(): void
    {
        $command = new Command('batch', ['halt' => 0, 'cmd' => ['a' => 'crm.deal.update?ID=1', 'b' => 'crm.deal.update?ID=2']]);
        $body = $this->batch(['a' => $this->time(15, 1000)]);
        $body['result']['result_error'] = ['b' => ['error' => 'OPERATION_TIME_LIMIT', 'error_description' => 'limit']];
        $this->tracker->observeResponse($command, $body);

        $this->assertSame([10], $this->thresholds());
    }

    #[Test]
    public function testBatchUnknownCommandIdIgnored(): void
    {
        $command = new Command('batch', ['halt' => 0, 'cmd' => ['a' => 'crm.deal.update?ID=1']]);
        $this->tracker->observeResponse($command, $this->batch(['zzz' => $this->time(50, 1000)]));

        $noCmd = new Command('batch', ['halt' => 0]);
        $this->tracker->observeResponse($noCmd, $this->batch(['a' => $this->time(50, 1000)]));

        $badCmd = new Command('batch', ['halt' => 0, 'cmd' => 'crm.deal.update']);
        $this->tracker->observeResponse($badCmd, $this->batch(['a' => $this->time(50, 1000)]));

        $this->assertSame([], $this->thresholds());
    }

    #[Test]
    public function testGetLastOperatingResetAt(): void
    {
        $command = new Command('crm.deal.update', []);
        $this->tracker->observeResponse($command, $this->single(5, 1000));
        $this->tracker->observeResponse($command, $this->single(5, null));

        $this->assertSame(1000, $this->tracker->getLastOperatingResetAt('crm.deal.update'));
        $this->assertNull($this->tracker->getLastOperatingResetAt('crm.contact.update'));
    }

    #[Test]
    public function testResolveBatchCommandMethod(): void
    {
        $command = new Command('batch', ['cmd' => ['a' => 'crm.deal.update?ID=1', 'b' => 'user.current']]);

        $this->assertSame('crm.deal.update', OperatingTracker::resolveBatchCommandMethod($command, 'a'));
        $this->assertSame('user.current', OperatingTracker::resolveBatchCommandMethod($command, 'b'));
        $this->assertNull(OperatingTracker::resolveBatchCommandMethod($command, 'c'));
    }

    #[Test]
    public function testConfigNormalisesAndValidates(): void
    {
        $this->assertSame([10, 30], (new OperatingLimitConfig(100.0, [30, 10, 10]))->thresholds);
        $this->assertSame(480.0, (new OperatingLimitConfig())->limitSeconds);
        $this->assertSame([10, 20, 30, 40, 50, 60, 70, 80, 90], (new OperatingLimitConfig())->thresholds);

        foreach ([[100.0, [0]], [100.0, [100]], [100.0, []], [0.0, [10]], [100.0, [12.5]], [100.0, ['50']]] as [$limit, $thresholds]) {
            try {
                new OperatingLimitConfig($limit, $thresholds);
                $this->fail(sprintf('expected exception for limit %s thresholds %s', $limit, json_encode($thresholds)));
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    /**
     * @return list<int>
     */
    private function thresholds(): array
    {
        return array_map(static fn(OperatingThresholdCrossedEvent $operatingThresholdCrossedEvent): int => $operatingThresholdCrossedEvent->getThresholdPercent(), $this->events);
    }

    /**
     * @return array<string, mixed>
     */
    private function single(float|int $operating, ?int $resetAt): array
    {
        return ['result' => ['ID' => 1], 'time' => $this->time($operating, $resetAt)];
    }

    /**
     * @param array<string, array<string, mixed>> $resultTime
     * @return array<string, mixed>
     */
    private function batch(array $resultTime): array
    {
        return [
            'result' => [
                'result' => array_map(static fn(): bool => true, $resultTime),
                'result_error' => [],
                'result_total' => [],
                'result_next' => [],
                'result_time' => $resultTime,
            ],
            'time' => $this->time(null, null),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function time(float|int|null $operating, ?int $resetAt): array
    {
        $time = [
            'start' => 1,
            'finish' => 2,
            'duration' => 1,
            'processing' => 0,
            'date_start' => '2026-01-01T00:00:00+00:00',
            'date_finish' => '2026-01-01T00:00:01+00:00',
        ];
        if ($operating !== null) {
            $time['operating'] = $operating;
        }

        if ($resetAt !== null) {
            $time['operating_reset_at'] = $resetAt;
        }

        return $time;
    }
}
