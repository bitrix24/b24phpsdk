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

namespace Bitrix24\SDK\Services\Catalog\Price\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\Price;
use Bitrix24\SDK\Services\Catalog\Price\Result\PriceAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\Price\Result\PriceUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected Price\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch adding product prices
     *
     * @param array<int, array> $prices
     *
     * @return Generator<int, PriceAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.price.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-add.html',
        'Batch adding product prices'
    )]
    public function add(array $prices): Generator
    {
        $items = [];
        foreach ($prices as $price) {
            $items[] = ['fields' => $price];
        }

        foreach ($this->batch->addEntityItems('catalog.price.add', $items) as $key => $item) {
            yield $key => new PriceAddedBatchResult($item);
        }
    }

    /**
     * Batch delete product prices
     *
     * @param int[] $priceId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.price.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-delete.html',
        'Batch delete product prices'
    )]
    public function delete(array $priceId): Generator
    {
        foreach ($this->batch->deletePriceItems('catalog.price.delete', $priceId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch update product prices
     *
     * @param array<int, array> $prices keyed by price id
     *
     * @return Generator<int, PriceUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.price.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-update.html',
        'Batch update product prices'
    )]
    public function update(array $prices): Generator
    {
        $items = [];
        foreach ($prices as $id => $price) {
            $items[$id] = ['fields' => $price];
        }

        foreach ($this->batch->updateEntityItems('catalog.price.update', $items) as $key => $item) {
            yield $key => new PriceUpdatedBatchResult($item);
        }
    }
}
