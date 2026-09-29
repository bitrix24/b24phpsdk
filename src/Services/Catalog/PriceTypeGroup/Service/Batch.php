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

namespace Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Result\PriceTypeGroupAddedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected PriceTypeGroup\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch adding price type ↔ purchasing group bindings
     *
     * @param array<int, array> $priceTypeGroups
     *
     * @return Generator<int, PriceTypeGroupAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.priceTypeGroup.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-group/catalog-price-type-group-add.html',
        'Batch adding price type purchasing group bindings'
    )]
    public function add(array $priceTypeGroups): Generator
    {
        $items = [];
        foreach ($priceTypeGroups as $priceTypeGroup) {
            $items[] = ['fields' => $priceTypeGroup];
        }

        foreach ($this->batch->addEntityItems('catalog.priceTypeGroup.add', $items) as $key => $item) {
            yield $key => new PriceTypeGroupAddedBatchResult($item);
        }
    }

    /**
     * Batch delete price type ↔ purchasing group bindings
     *
     * @param int[] $priceTypeGroupId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.priceTypeGroup.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-group/catalog-price-type-group-delete.html',
        'Batch delete price type purchasing group bindings'
    )]
    public function delete(array $priceTypeGroupId): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.priceTypeGroup.delete', $priceTypeGroupId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
