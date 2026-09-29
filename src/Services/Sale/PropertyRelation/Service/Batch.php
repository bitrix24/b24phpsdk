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

namespace Bitrix24\SDK\Services\Sale\PropertyRelation\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Sale\PropertyRelation\Result\PropertyRelationAddedBatchResult;
use Bitrix24\SDK\Services\Sale\PropertyRelation\Result\PropertyRelationItemResult;
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
     * Batch list method for property relations
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-relation/sale-property-relation-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, PropertyRelationItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.propertyRelation.list',
        'https://apidocs.bitrix24.com/api-reference/sale/property-relation/sale-property-relation-list.html',
        'Batch list method for property relations'
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

        foreach ($this->batch->getTraversableListWithCount('sale.propertyRelation.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new PropertyRelationItemResult($value);
        }
    }

    /**
     * Batch adding property relations
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-relation/sale-property-relation-add.html
     *
     * @param array<int, array{entityId: int, entityType: string, propertyId: int}> $propertyRelations
     *
     * @return Generator<int, PropertyRelationAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.propertyRelation.add',
        'https://apidocs.bitrix24.com/api-reference/sale/property-relation/sale-property-relation-add.html',
        'Batch adding property relations'
    )]
    public function add(array $propertyRelations): Generator
    {
        $items = [];
        foreach ($propertyRelations as $propertyRelation) {
            $items[] = ['fields' => $propertyRelation];
        }

        foreach ($this->batch->addEntityItems('sale.propertyRelation.add', $items) as $key => $item) {
            yield $key => new PropertyRelationAddedBatchResult($item);
        }
    }
}
