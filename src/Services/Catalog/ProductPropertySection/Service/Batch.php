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

namespace Bitrix24\SDK\Services\Catalog\ProductPropertySection\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result\ProductPropertySectionItemResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result\ProductPropertySectionSetBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of section settings for product properties and variations
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-list.html
     *
     * @param array<string, 'asc'|'desc'|'ASC'|'DESC'>           $order
     * @param array<string, scalar|array{0?: scalar, 1?:scalar}> $filter
     * @param array<int, string>                                 $select
     *
     * @return Generator<int, ProductPropertySectionItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productPropertySection.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-list.html',
        'Batch list of section settings for product properties and variations'
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
                'catalog.productPropertySection.list',
                $order,
                $filter,
                $select,
                $limit
            ) as $key => $value
        ) {
            yield $key => new ProductPropertySectionItemResult($value);
        }
    }

    /**
     * Batch set section settings of product properties and variations
     *
     * The method creates the settings record when it does not exist yet and updates it otherwise,
     * so the batch call takes a plain list of items keyed by property id:
     * propertyId => [
     *   'smartFilter' => 'Y',
     *   'displayType' => 'F',
     *   ...
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-set.html
     *
     * @param array<int, array<string, mixed>> $productPropertySections
     *
     * @return Generator<int, ProductPropertySectionSetBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productPropertySection.set',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-set.html',
        'Batch set section settings of product properties and variations'
    )]
    public function set(array $productPropertySections): Generator
    {
        $items = [];
        foreach ($productPropertySections as $propertyId => $fields) {
            $items[] = [
                'propertyId' => $propertyId,
                'fields' => $fields,
            ];
        }

        foreach ($this->batch->addEntityItems('catalog.productPropertySection.set', $items) as $key => $item) {
            yield $key => new ProductPropertySectionSetBatchResult($item);
        }
    }
}
