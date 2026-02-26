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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\Service;

use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Main\Service\EventLog;
use Bitrix24\SDK\Services\Main\Service\EventLogFilter;
use Bitrix24\SDK\Services\Main\Service\EventLogSelectBuilder;
use Bitrix24\SDK\Services\Main\Service\EventLogTailCursor;
use Bitrix24\SDK\Tests\Integration\Factory;
use Carbon\CarbonImmutable;
use Darsyn\IP\Version\Multi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventLog::class)]
class EventLogTest extends TestCase
{
    private EventLog $eventLog;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('list returns event log items with typed fields')]
    public function testList(): void
    {
        $eventLogsResult = $this->eventLog->list(
            (new EventLogSelectBuilder())
                ->timestampX()
                ->severity()
                ->auditTypeId()
                ->moduleId()
                ->userId(),
            (new EventLogFilter())
                ->timestampX()->gte(new \DateTime('-1 day')),
            ['timestampX' => SortOrder::Descending],
            ['limit' => 5]
        );

        $items = $eventLogsResult->getEventLogItems();
        $this->assertIsArray($items);

        if ($items !== []) {
            $item = $items[0];
            $this->assertGreaterThan(0, $item->id);
            $this->assertInstanceOf(CarbonImmutable::class, $item->timestampX);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('list returns event log items with array select and filter')]
    public function testListWithArrayArguments(): void
    {
        $eventLogsResult = $this->eventLog->list(
            ['id', 'timestampX', 'severity'],
            [],
            [],
            ['limit' => 3]
        );

        $this->assertIsArray($eventLogsResult->getEventLogItems());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('tail returns new entries after a cursor point')]
    public function testTail(): void
    {
        $eventLogsResult = $this->eventLog->tail(
            (new EventLogSelectBuilder())
                ->timestampX()
                ->severity()
                ->auditTypeId()
                ->userId(),
            new EventLogFilter(),
            new EventLogTailCursor(value: 0, order: SortOrder::Ascending, limit: 10)
        );

        $items = $eventLogsResult->getEventLogItems();
        $this->assertIsArray($items);

        if ($items !== []) {
            $firstItem = $items[0];
            $this->assertGreaterThan(0, $firstItem->id);
            $this->assertInstanceOf(CarbonImmutable::class, $firstItem->timestampX);

            // fetch next page using the last item's ID as cursor
            $lastId = $items[count($items) - 1]->id;
            $nextResult = $this->eventLog->tail(
                (new EventLogSelectBuilder())->timestampX()->severity(),
                new EventLogFilter(),
                new EventLogTailCursor(value: $lastId, order: SortOrder::Ascending, limit: 10)
            );
            $this->assertIsArray($nextResult->getEventLogItems());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('get returns a single event log entry by ID')]
    public function testGet(): void
    {
        // fetch one item first to get a valid ID
        $items = $this->eventLog->list(
            (new EventLogSelectBuilder())
                ->allSystemFields(),
            new EventLogFilter(),
            [],
            ['limit' => 1]
        )->getEventLogItems();

        if ($items === []) {
            $this->markTestSkipped('No event log entries available on this portal.');
        }

        $id = $items[0]->id;

        $eventLogItemResult = $this->eventLog->get(
            $id,
            (new EventLogSelectBuilder())
                ->timestampX()
                ->severity()
                ->auditTypeId()
                ->moduleId()
                ->userId()
                ->remoteAddr()
                ->description()
        )->eventLogItem();

        $this->assertSame($id, $eventLogItemResult->id);
        $this->assertInstanceOf(CarbonImmutable::class, $eventLogItemResult->timestampX);
        $this->assertIsString($eventLogItemResult->severity);
        if ($eventLogItemResult->remoteAddr !== null) {
            $this->assertInstanceOf(Multi::class, $eventLogItemResult->remoteAddr);
        }
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->eventLog = Factory::getServiceBuilder()->getMainScope()->eventLog();
    }
}
