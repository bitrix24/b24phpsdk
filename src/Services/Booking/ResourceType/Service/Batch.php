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

namespace Bitrix24\SDK\Services\Booking\ResourceType\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Booking;
use Bitrix24\SDK\Services\Booking\ResourceType\Result\ResourceTypeItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['booking']))]
class Batch
{
    public function __construct(protected Booking\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of resource types
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-list.html
     *
     * @param array<string, mixed> $filter
     * @param array<string, string> $order
     *
     * @return Generator<int, ResourceTypeItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.resourceType.list',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-list.html',
        'Batch list of resource types'
    )]
    public function list(array $filter = [], array $order = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'filter' => $filter,
                'order' => $order,
                'limit' => $limit,
            ]
        );

        foreach ($this->batch->getTraversableList('booking.v1.resourceType.list', $order, $filter, [], $limit) as $key => $value) {
            yield $key => new ResourceTypeItemResult($value);
        }
    }

    /**
     * Batch adding resource types
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-add.html
     *
     * @param array<int, array<string, mixed>> $resourceTypes
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.resourceType.add',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-add.html',
        'Batch adding resource types'
    )]
    public function add(array $resourceTypes): Generator
    {
        $items = [];
        foreach ($resourceTypes as $resourceType) {
            $items[] = ['fields' => $resourceType];
        }

        foreach ($this->batch->addEntityItems('booking.v1.resourceType.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update resource types
     *
     * Update elements in array with structure
     * resource type id => [ // fields to update
     *  'name' => 'string',
     *  'code' => 'string'
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-update.html
     *
     * @param array<int, array<string, mixed>> $resourceTypes
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.resourceType.update',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-update.html',
        'Batch update resource types'
    )]
    public function update(array $resourceTypes): Generator
    {
        $items = [];
        foreach ($resourceTypes as $resourceTypeId => $fields) {
            $items[$resourceTypeId] = ['fields' => $fields];
        }

        foreach ($this->batch->updateEntityItems('booking.v1.resourceType.update', $items) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete resource types
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-delete.html
     *
     * @param int[] $resourceTypeIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.resourceType.delete',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-delete.html',
        'Batch delete resource types'
    )]
    public function delete(array $resourceTypeIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('booking.v1.resourceType.delete', $resourceTypeIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
