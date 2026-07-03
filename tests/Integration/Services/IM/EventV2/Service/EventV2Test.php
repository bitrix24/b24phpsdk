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
        $this->eventService = Factory::getServiceBuilder()->getIMScope()->eventV2();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.v2.Event.subscribe returns success')]
    public function testSubscribe(): void
    {
        $result = $this->eventService->subscribe();

        // EmptyResult — no exception means success
        $this->assertNotNull($result->getCoreResponse());
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
        $result = $this->eventService->unsubscribe();

        // EmptyResult — no exception means success
        $this->assertNotNull($result->getCoreResponse());
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
        $result = $this->eventService->get(0);

        $this->assertInstanceOf(EventsV2Result::class, $result);
        $this->assertIsArray($result->getEvents());
        $this->assertIsBool($result->isHasMore());
        // nextOffset may be null when there are no events
        $nextOffset = $result->getNextOffset();
        $this->assertTrue($nextOffset === null || is_int($nextOffset));
    }
}
