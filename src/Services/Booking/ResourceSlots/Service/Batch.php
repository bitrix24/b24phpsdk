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

namespace Bitrix24\SDK\Services\Booking\ResourceSlots\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Booking;
use Bitrix24\SDK\Services\Booking\ResourceSlots\Result\ResourceSlotItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['booking']))]
class Batch
{
    public function __construct(protected Booking\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch read slots of several resources
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/slots/booking-v1-resource-slots-list.html
     *
     * @param int[] $resourceIds
     *
     * @return Generator<int, ResourceSlotItemResult[]> resource id => slots of this resource
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.resource.slots.list',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/slots/booking-v1-resource-slots-list.html',
        'Batch read slots of several resources'
    )]
    public function list(array $resourceIds): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'resourceIds' => $resourceIds,
            ]
        );

        $slotsGenerator = $this->batch->getEntityItemsByParentIds(
            'booking.v1.resource.slots.list',
            'resourceId',
            $resourceIds
        );

        foreach ($slotsGenerator as $resourceId => $slots) {
            yield $resourceId => array_map(
                static fn (array $slot): ResourceSlotItemResult => new ResourceSlotItemResult($slot),
                $slots
            );
        }
    }

    /**
     * Batch set slots for several resources
     *
     * Elements in array with structure
     * resource id => [ // slots replacing the ones currently stored for the resource
     *  ['from' => 32400, 'to' => 64800, 'slotSize' => 1800, 'weekDays' => ['MO', 'TU']]
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/slots/booking-v1-resource-slots-set.html
     *
     * @param array<int, array<int, array<string, mixed>>> $resourceSlots
     *
     * @return Generator<int, UpdatedItemBatchResult> resource id => set result
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.resource.slots.set',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/slots/booking-v1-resource-slots-set.html',
        'Batch set slots for several resources'
    )]
    public function set(array $resourceSlots): Generator
    {
        $setGenerator = $this->batch->setEntityItems(
            'booking.v1.resource.slots.set',
            'resourceId',
            'slots',
            $resourceSlots
        );

        foreach ($setGenerator as $resourceId => $item) {
            yield $resourceId => new UpdatedItemBatchResult($item);
        }
    }
}
