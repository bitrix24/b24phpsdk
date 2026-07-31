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

namespace Bitrix24\SDK\Services\Catalog\PriceType\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\PriceType;
use Bitrix24\SDK\Services\Catalog\PriceType\Result\PriceTypeAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\PriceType\Result\PriceTypeUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected PriceType\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch adding price types
     *
     * @param array<int, array> $priceTypes
     *
     * @return Generator<int, PriceTypeAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.priceType.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-add.html',
        'Batch adding price types'
    )]
    public function add(array $priceTypes): Generator
    {
        $items = [];
        foreach ($priceTypes as $priceType) {
            $items[] = ['fields' => $priceType];
        }

        foreach ($this->batch->addEntityItems('catalog.priceType.add', $items) as $key => $item) {
            yield $key => new PriceTypeAddedBatchResult($item);
        }
    }

    /**
     * Batch delete price types
     *
     * @param int[] $priceTypeId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.priceType.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-delete.html',
        'Batch delete price types'
    )]
    public function delete(array $priceTypeId): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.priceType.delete', $priceTypeId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch update price types
     *
     * @param array<int, array> $priceTypes keyed by price type id
     *
     * @return Generator<int, PriceTypeUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.priceType.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-update.html',
        'Batch update price types'
    )]
    public function update(array $priceTypes): Generator
    {
        $items = [];
        foreach ($priceTypes as $id => $priceType) {
            $items[$id] = ['fields' => $priceType];
        }

        foreach ($this->batch->updateEntityItems('catalog.priceType.update', $items) as $key => $item) {
            yield $key => new PriceTypeUpdatedBatchResult($item);
        }
    }
}
