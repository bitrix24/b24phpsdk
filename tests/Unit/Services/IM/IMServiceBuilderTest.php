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

namespace Bitrix24\SDK\Tests\Unit\Services\IM;

use Bitrix24\SDK\Services\IM\Counters\Service\Counters;
use Bitrix24\SDK\Services\IM\IMServiceBuilder;
use Bitrix24\SDK\Services\IM\Placements\Placements;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBulkItemsReader;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(IMServiceBuilder::class)]
class IMServiceBuilderTest extends TestCase
{
    private IMServiceBuilder $serviceBuilder;

    public function testGetIMService(): void
    {
        $this::assertSame($this->serviceBuilder->notify(), $this->serviceBuilder->notify());
    }

    public function testGetChatService(): void
    {
        $this::assertSame($this->serviceBuilder->chat(), $this->serviceBuilder->chat());
    }

    public function testGetMessageService(): void
    {
        $this::assertSame($this->serviceBuilder->message(), $this->serviceBuilder->message());
    }

    public function testGetCountersService(): void
    {
        $this->assertInstanceOf(Counters::class, $this->serviceBuilder->counters());
        $this->assertSame($this->serviceBuilder->counters(), $this->serviceBuilder->counters());
    }

    public function testGetPlacementsService(): void
    {
        $this->assertInstanceOf(Placements::class, $this->serviceBuilder->placements());
        $this->assertSame($this->serviceBuilder->placements(), $this->serviceBuilder->placements());
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->serviceBuilder = (
        new ServiceBuilder(
            new NullCore(),
            new NullBatch(),
            new NullBulkItemsReader(),
            new NullLogger()
        ))->getIMScope();
    }
}
