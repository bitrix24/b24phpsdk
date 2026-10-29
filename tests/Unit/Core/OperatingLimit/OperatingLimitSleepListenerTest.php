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

use Bitrix24\SDK\Core\OperatingLimit\OperatingLimitSleepListener;
use Bitrix24\SDK\Events\OperatingThresholdCrossedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[CoversClass(OperatingLimitSleepListener::class)]
class OperatingLimitSleepListenerTest extends TestCase
{
    /**
     * @var list<int>
     */
    private array $sleeps = [];

    private OperatingLimitSleepListener $listener;

    protected function setUp(): void
    {
        $this->sleeps = [];
        $this->listener = new OperatingLimitSleepListener(
            90,
            1,
            new NullLogger(),
            function (int $seconds): void {
                $this->sleeps[] = $seconds;
            },
            static fn(): int => 1000
        );
    }

    #[Test]
    public function testSleepsUntilResetPlusMargin(): void
    {
        ($this->listener)($this->event(90, 1030));

        $this->assertSame([31], $this->sleeps);
    }

    #[Test]
    public function testIgnoresLowerThresholds(): void
    {
        ($this->listener)($this->event(80, 1030));

        $this->assertSame([], $this->sleeps);
    }

    #[Test]
    public function testNoSleepWhenResetAtNullOrPast(): void
    {
        ($this->listener)($this->event(90, null));
        ($this->listener)($this->event(90, 990));

        $this->assertSame([], $this->sleeps);
    }

    #[Test]
    public function testWorksAsDispatcherListener(): void
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(OperatingThresholdCrossedEvent::class, $this->listener);
        $eventDispatcher->dispatch($this->event(90, 1010));

        $this->assertSame([11], $this->sleeps);
    }

    private function event(int $threshold, ?int $resetAt): OperatingThresholdCrossedEvent
    {
        return new OperatingThresholdCrossedEvent('crm.deal.update', 440.0, $resetAt, $threshold, 480.0);
    }
}
