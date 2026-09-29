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

namespace Bitrix24\SDK\Services\Sale\BasketProperty\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\BasketProperty\Result\BasketPropertyAddBatchResult;
use Bitrix24\SDK\Services\Sale\BasketProperty\Result\BasketPropertyItemResult;
use Bitrix24\SDK\Services\Sale\BasketProperty\Result\BasketPropertyUpdateBatchResult;
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
     * Batch list method for basket properties
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/basket-properties/sale-basket-properties-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, BasketPropertyItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.basketproperties.list',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-properties/sale-basket-properties-list.html',
        'Batch list method for basket properties'
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

        foreach ($this->batch->getTraversableList('sale.basketproperties.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new BasketPropertyItemResult($value);
        }
    }

    /**
     * Batch adding basket properties
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/basket-properties/sale-basket-properties-add.html
     *
     * @param array<int, array> $basketProperties Array of basket property fields
     *
     * @return Generator<int, BasketPropertyAddBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.basketproperties.add',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-properties/sale-basket-properties-add.html',
        'Batch adding basket properties'
    )]
    public function add(array $basketProperties): Generator
    {
        $items = [];
        foreach ($basketProperties as $basketProperty) {
            $items[] = ['fields' => $basketProperty];
        }

        foreach ($this->batch->addEntityItems('sale.basketproperties.add', $items) as $key => $item) {
            yield $key => new BasketPropertyAddBatchResult($item);
        }
    }

    /**
     * Batch update basket properties
     *
     * Update elements in array with structure
     * element_id => [ // basket property id
     *  // basket property fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/basket-properties/sale-basket-properties-update.html
     *
     * @param array<int, array> $basketProperties keyed by basket property id
     *
     * @return Generator<int, BasketPropertyUpdateBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.basketproperties.update',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-properties/sale-basket-properties-update.html',
        'Batch update basket properties'
    )]
    public function update(array $basketProperties): Generator
    {
        $items = [];
        foreach ($basketProperties as $id => $basketProperty) {
            $items[$id] = ['fields' => $basketProperty];
        }

        foreach ($this->batch->updateEntityItems('sale.basketproperties.update', $items) as $key => $item) {
            yield $key => new BasketPropertyUpdateBatchResult($item);
        }
    }

    /**
     * Batch delete basket properties
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/basket-properties/sale-basket-properties-delete.html
     *
     * @param int[] $basketPropertyId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.basketproperties.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-properties/sale-basket-properties-delete.html',
        'Batch delete basket properties'
    )]
    public function delete(array $basketPropertyId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.basketproperties.delete', $basketPropertyId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
