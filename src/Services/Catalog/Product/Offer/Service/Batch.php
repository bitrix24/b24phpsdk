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

namespace Bitrix24\SDK\Services\Catalog\Product\Offer\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\Product\Offer\Result\OfferAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\Product\Offer\Result\OfferItemResult;
use Bitrix24\SDK\Services\Catalog\Product\Offer\Result\OfferUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of product variations (offers)
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-list.html
     *
     * @param array<string, 'asc'|'desc'|'ASC'|'DESC'>           $order
     * @param array<string, scalar|array{0?: scalar, 1?:scalar}> $filter
     * @param array<int, string>                                 $select
     *
     * @return Generator<int, OfferItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.product.offer.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-list.html',
        'Batch list of product variations (offers)'
    )]
    public function list(array $order = [], array $filter = [], array $select = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'order' => $order,
                'filter' => $filter,
                'select' => $select,
                'limit' => $limit,
            ]
        );

        foreach (
            $this->batch->getTraversableListWithCount(
                'catalog.product.offer.list',
                $order,
                $filter,
                $select,
                $limit
            ) as $key => $value
        ) {
            yield $key => new OfferItemResult($value);
        }
    }

    /**
     * Batch adding product variations (offers)
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-add.html
     *
     * @param array<int, array> $offers
     *
     * @return Generator<int, OfferAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.product.offer.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-add.html',
        'Batch adding product variations (offers)'
    )]
    public function add(array $offers): Generator
    {
        $items = [];
        foreach ($offers as $offer) {
            $items[] = ['fields' => $offer];
        }

        foreach ($this->batch->addEntityItems('catalog.product.offer.add', $items) as $key => $item) {
            yield $key => new OfferAddedBatchResult($item);
        }
    }

    /**
     * Batch update product variations (offers)
     *
     * Update elements in array with structure:
     * id => [
     *   'fields' => [] // fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-update.html
     *
     * @param array<int, array{fields: array}> $entityItems
     *
     * @return Generator<int, OfferUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.product.offer.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-update.html',
        'Batch update product variations (offers)'
    )]
    public function update(array $entityItems): Generator
    {
        foreach ($this->batch->updateEntityItems('catalog.product.offer.update', $entityItems) as $key => $item) {
            yield $key => new OfferUpdatedBatchResult($item);
        }
    }

    /**
     * Batch delete product variations (offers)
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-delete.html
     *
     * @param int[] $offerIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.product.offer.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-delete.html',
        'Batch delete product variations (offers)'
    )]
    public function delete(array $offerIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.product.offer.delete', $offerIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
