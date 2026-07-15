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

namespace Bitrix24\SDK\Services\Catalog\PriceTypeLang\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Result\PriceTypeLangAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Result\PriceTypeLangUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected PriceTypeLang\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch adding price type name translations
     *
     * @param array<int, array> $priceTypeLangs
     *
     * @return Generator<int, PriceTypeLangAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.priceTypeLang.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-add.html',
        'Batch adding price type name translations'
    )]
    public function add(array $priceTypeLangs): Generator
    {
        $items = [];
        foreach ($priceTypeLangs as $priceTypeLang) {
            $items[] = ['fields' => $priceTypeLang];
        }

        foreach ($this->batch->addEntityItems('catalog.priceTypeLang.add', $items) as $key => $item) {
            yield $key => new PriceTypeLangAddedBatchResult($item);
        }
    }

    /**
     * Batch delete price type name translations
     *
     * @param int[] $priceTypeLangId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.priceTypeLang.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-delete.html',
        'Batch delete price type name translations'
    )]
    public function delete(array $priceTypeLangId): Generator
    {
        foreach ($this->batch->deletePriceTypeLangItems('catalog.priceTypeLang.delete', $priceTypeLangId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch update price type name translations
     *
     * @param array<int, array> $priceTypeLangs keyed by translation id
     *
     * @return Generator<int, PriceTypeLangUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.priceTypeLang.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-update.html',
        'Batch update price type name translations'
    )]
    public function update(array $priceTypeLangs): Generator
    {
        $items = [];
        foreach ($priceTypeLangs as $id => $priceTypeLang) {
            $items[$id] = ['fields' => $priceTypeLang];
        }

        foreach ($this->batch->updateEntityItems('catalog.priceTypeLang.update', $items) as $key => $item) {
            yield $key => new PriceTypeLangUpdatedBatchResult($item);
        }
    }
}
