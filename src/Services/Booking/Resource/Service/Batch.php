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

namespace Bitrix24\SDK\Services\Booking\Resource\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Booking;
use Bitrix24\SDK\Services\Booking\Resource\Result\ResourceItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['booking']))]
class Batch
{
    public function __construct(protected Booking\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of resources
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-list.html
     *
     * @param array<string, mixed> $filter
     * @param array<string, string> $order
     *
     * @return Generator<int, ResourceItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.resource.list',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-list.html',
        'Batch list of resources'
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

        foreach ($this->batch->getTraversableList('booking.v1.resource.list', $order, $filter, [], $limit) as $key => $value) {
            yield $key => new ResourceItemResult($value);
        }
    }

    /**
     * Batch adding resources
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-add.html
     *
     * @param array<int, array<string, mixed>> $resources
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.resource.add',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-add.html',
        'Batch adding resources'
    )]
    public function add(array $resources): Generator
    {
        $items = [];
        foreach ($resources as $resource) {
            $items[] = ['fields' => $resource];
        }

        foreach ($this->batch->addEntityItems('booking.v1.resource.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update resources
     *
     * Update elements in array with structure
     * resource id => [ // fields to update
     *  'name' => 'string',
     *  'description' => 'string',
     *  'typeId' => 1,
     *  'isMain' => 'Y'
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-update.html
     *
     * @param array<int, array<string, mixed>> $resources
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.resource.update',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-update.html',
        'Batch update resources'
    )]
    public function update(array $resources): Generator
    {
        $items = [];
        foreach ($resources as $resourceId => $fields) {
            $items[$resourceId] = ['fields' => $fields];
        }

        foreach ($this->batch->updateEntityItems('booking.v1.resource.update', $items) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete resources
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-delete.html
     *
     * @param int[] $resourceIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.resource.delete',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-delete.html',
        'Batch delete resources'
    )]
    public function delete(array $resourceIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('booking.v1.resource.delete', $resourceIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
