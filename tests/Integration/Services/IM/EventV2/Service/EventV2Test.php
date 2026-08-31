<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IM\EventV2\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\EventV2\Result\EventsV2Result;
use Bitrix24\SDK\Services\IM\EventV2\Service\EventV2;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventV2::class)]
class EventV2Test extends TestCase
{
    private EventV2 $eventService;

    #[\Override]
    protected function setUp(): void
    {
        $this->eventService = Fabric::getServiceBuilder()->getIMScope()->eventV2();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.v2.Event.subscribe returns success')]
    public function testSubscribe(): void
    {
        $emptyResult = $this->eventService->subscribe();

        // EmptyResult — no exception means success
        $this->assertNotNull($emptyResult->getCoreResponse());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.v2.Event.unsubscribe returns success')]
    public function testUnsubscribe(): void
    {
        $this->eventService->subscribe();
        $emptyResult = $this->eventService->unsubscribe();

        // EmptyResult — no exception means success
        $this->assertNotNull($emptyResult->getCoreResponse());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.v2.Event.get returns EventsV2Result with events, nextOffset and hasMore')]
    public function testGet(): void
    {
        $this->eventService->subscribe();
        $eventsV2Result = $this->eventService->get(0);

        $this->assertInstanceOf(EventsV2Result::class, $eventsV2Result);
        $this->assertIsArray($eventsV2Result->getEvents());
        $this->assertIsBool($eventsV2Result->isHasMore());
        // nextOffset may be null when there are no events
        $nextOffset = $eventsV2Result->getNextOffset();
        $this->assertTrue($nextOffset === null || is_int($nextOffset));
    }
}
