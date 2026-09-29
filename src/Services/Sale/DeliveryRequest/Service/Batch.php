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

namespace Bitrix24\SDK\Services\Sale\DeliveryRequest\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['sale', 'delivery']))]
class Batch
{
    /**
     * Batch constructor
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch update delivery requests
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery-request/sale-delivery-request-update.html
     *
     * @param array<int, array{
     *     DELIVERY_ID: int,
     *     REQUEST_ID: string,
     *     FINALIZE?: string,
     *     STATUS?: array,
     *     PROPERTIES?: array,
     *     OVERWRITE_PROPERTIES?: string
     *   }> $requests
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.request.update',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery-request/sale-delivery-request-update.html',
        'Batch update delivery requests'
    )]
    public function update(array $requests): Generator
    {
        foreach ($this->batch->updateEntityItems('sale.delivery.request.update', $requests) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete delivery requests
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery-request/sale-delivery-request-delete.html
     *
     * @param array<int, array{DELIVERY_ID: int, REQUEST_ID: string}> $requests
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.request.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery-request/sale-delivery-request-delete.html',
        'Batch delete delivery requests'
    )]
    public function delete(array $requests): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.delivery.request.delete', $requests) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
