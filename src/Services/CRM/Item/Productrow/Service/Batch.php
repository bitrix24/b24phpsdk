<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Item\Productrow\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\CRM\Item\Productrow\Result\ProductrowItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch
{
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for product items
     *
     * @return Generator<int, ProductrowItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.item.productrow.list',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-list.html',
        'Retrieves a list of product items.'
    )]
    public function list(array $order, array $filter, ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'order' => $order,
                'filter' => $filter,
                'limit' => $limit,
            ]
        );
        foreach ($this->batch->getTraversableList('crm.item.productrow.list', $order, $filter, [], $limit) as $key => $value) {
            yield $key => new ProductrowItemResult($value);
        }
    }

    /**
     * Batch adding product items
     *
     * @return Generator<int, ProductrowItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.item.productrow.add',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-add.html',
        'Batch adding product items.'
    )]
    public function add(array $items): Generator
    {
        $rawItems = [];
        foreach ($items as $item) {
            $rawItems[] = [
                'fields' => $item,
            ];
        }

        foreach ($this->batch->addEntityItems('crm.item.productrow.add', $rawItems) as $key => $item) {
            yield $key => new ProductrowItemResult($item->getResult()['productRow'][0]);
        }
    }
    
    /**
     * Batch delete product items
     *
     * @param int[] $productRowId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.item.productrow.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-delete.html',
        'Batch delete leads'
    )]
    public function delete(array $productRowId): Generator
    {
        foreach ($this->batch->deleteEntityItems('crm.item.productrow.delete', $productRowId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
