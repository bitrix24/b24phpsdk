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

namespace Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Result\ShipmentPropertyValueItemResult;
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
     * Batch list method for shipment property values
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-property-value-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, ShipmentPropertyValueItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipmentpropertyvalue.list',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-property-value-list.html',
        'Batch list method for shipment property values'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'limit' => $limit,
            ]
        );

        foreach ($this->batch->getTraversableList('sale.shipmentpropertyvalue.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new ShipmentPropertyValueItemResult($value);
        }
    }

    /**
     * Batch delete shipment property values
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-propertyvalue-delete.html
     *
     * @param int[] $propertyValueId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipmentpropertyvalue.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-propertyvalue-delete.html',
        'Batch delete shipment property values'
    )]
    public function delete(array $propertyValueId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.shipmentpropertyvalue.delete', $propertyValueId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
