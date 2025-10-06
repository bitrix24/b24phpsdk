<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Calendar\Event\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Services\Calendar\Event\Batch as CalendarEventBatch;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Calendar\Event\Result\UpdatedEventBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['calendar']))]
class Batch
{
    /**
     * Batch constructor.
     */
    public function __construct(protected CalendarEventBatch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch add method for creating multiple calendar events
     *
     * @param array $events Array of event fields
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'calendar.event.add',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-event/calendar-event-add.html',
        'Creates new calendar events'
    )]
    public function add(array $events): Generator
    {
        $fields = $events;
        foreach ($this->batch->addEntityItems('calendar.event.add', $fields) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update method for updating multiple calendar events
     *
     * Update elements in array with structure
     * element_id => [  // event id and other fields
     *  'id' => 123,
     *  'name' => 'Updated Event Name',
     *  // other event fields to update
     * ]
     *
     * @param array<int, array> $eventsData
     * @return Generator<int, UpdatedEventBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'calendar.event.update',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-event/calendar-event-update.html',
        'Updates multiple existing calendar events'
    )]
    public function update(array $eventsData): Generator
    {
        foreach ($this->batch->updateEntityItems('calendar.event.update', $eventsData) as $key => $item) {
            yield $key => new UpdatedEventBatchResult($item);
        }
    }

    /**
     * Batch delete calendar events
     *
     * @param int[] $eventIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'calendar.event.delete',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-event/calendar-event-delete.html',
        'Batch delete calendar events'
    )]
    public function delete(array $eventIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('calendar.event.delete', $eventIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
