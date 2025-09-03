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

namespace Bitrix24\SDK\Services\Sale\BasketItem\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\BasketItem\Result\UpdatedBasketItemBatchResult;
use Bitrix24\SDK\Services\Sale\BasketItem\Result\AddedBasketItemBatchResult;
use Bitrix24\SDK\Services\Sale\BasketItem\Result\BasketItemItemResult;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['sale']))]
class Batch
{
    /**
     * Batch constructor.
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch add method for creating multiple basket items
     *
     * @param array $items Array of basket item fields
     *
     * @return Generator<int, AddedBasketItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.basketitem.add',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-add.html',
        'Creates new basket items'
    )]
    public function add(array $items): Generator
    {
        $fields = [];
        foreach ($items as $item) {
            $fields[] = [
                'fields' => $item
            ];
        }

        foreach ($this->batch->addEntityItems('sale.basketitem.add', $fields) as $key => $item) {
            yield $key => new AddedBasketItemBatchResult($item);
        }
    }

    /**
     * Batch update method for updating multiple basket items
     *
     * Update elements in array with structure
     * element_id => [  // basket item id
     *  'fields' => [] // basket item fields to update
     * ]
     *
     * @param array<int, array> $itemsData
     * @return Generator<int, UpdatedBasketItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.basketitem.update',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-update.html',
        'Updates multiple existing basket items'
    )]
    public function update(array $itemsData): Generator
    {
        foreach ($this->batch->updateEntityItems('sale.basketitem.update', $itemsData) as $key => $item) {
            yield $key => new UpdatedBasketItemBatchResult($item);
        }
    }

    /**
     * Batch delete basket items
     *
     * @param int[] $itemIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'sale.basketitem.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-delete.html',
        'Batch delete basket items'
    )]
    public function delete(array $itemIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.basketitem.delete', $itemIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch list method for retrieving multiple basket items
     *
     * @param array $filter Filter criteria
     * @param array $order Sort order
     * @param array $select Fields to select
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator|BasketItemItemResult[]
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.basketitem.list',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-list.html',
        'Retrieves a list of basket items'
    )]
    public function list(array $filter = [], array $order = [], array $select = [], ?int $limit = null): Generator
    {
        foreach ($this->batch->getTraversableList(
            'sale.basketitem.list',
            $order,
            $filter,
            $select,
            $limit
        ) as $key => $item) {
            yield $key => new BasketItemItemResult($item);
        }
    }
}
