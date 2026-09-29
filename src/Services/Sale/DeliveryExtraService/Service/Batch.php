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

namespace Bitrix24\SDK\Services\Sale\DeliveryExtraService\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['sale']))]
class Batch
{
    /**
     * Batch constructor
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch adding delivery extra services
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-add.html
     *
     * @param array<int, array> $extraServices Array of delivery extra service fields (DELIVERY_ID, TYPE, NAME, ...)
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.extra.service.add',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-add.html',
        'Batch adding delivery extra services'
    )]
    public function add(array $extraServices): Generator
    {
        foreach ($this->batch->addEntityItems('sale.delivery.extra.service.add', $extraServices) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update delivery extra services
     *
     * Update elements in array with structure
     * element_id => [ // delivery extra service id
     *  // delivery extra service fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-update.html
     *
     * @param array<int, array> $extraServices keyed by delivery extra service id
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.extra.service.update',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-update.html',
        'Batch update delivery extra services'
    )]
    public function update(array $extraServices): Generator
    {
        foreach ($this->batch->updateEntityItems('sale.delivery.extra.service.update', $extraServices) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete delivery extra services
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-delete.html
     *
     * @param int[] $extraServiceId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.extra.service.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-delete.html',
        'Batch delete delivery extra services'
    )]
    public function delete(array $extraServiceId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.delivery.extra.service.delete', $extraServiceId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
