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

namespace Bitrix24\SDK\Services\Sale\PersonType\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\PersonType\Result\AddedPersonTypeBatchResult;
use Bitrix24\SDK\Services\Sale\PersonType\Result\PersonTypeItemResult;
use Bitrix24\SDK\Services\Sale\PersonType\Result\UpdatedPersonTypeBatchResult;
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
     * Batch list method for person types
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, PersonTypeItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.persontype.list',
        'https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-list.html',
        'Batch list method for person types'
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

        foreach ($this->batch->getTraversableList('sale.persontype.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new PersonTypeItemResult($value);
        }
    }

    /**
     * Batch adding person types
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-add.html
     *
     * @param array<int, array> $personTypes Array of person type fields
     *
     * @return Generator<int, AddedPersonTypeBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.persontype.add',
        'https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-add.html',
        'Batch adding person types'
    )]
    public function add(array $personTypes): Generator
    {
        $items = [];
        foreach ($personTypes as $personType) {
            $items[] = ['fields' => $personType];
        }

        foreach ($this->batch->addEntityItems('sale.persontype.add', $items) as $key => $item) {
            yield $key => new AddedPersonTypeBatchResult($item);
        }
    }

    /**
     * Batch update person types
     *
     * Update elements in array with structure
     * element_id => [ // person type id
     *  // person type fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-update.html
     *
     * @param array<int, array> $personTypes keyed by person type id
     *
     * @return Generator<int, UpdatedPersonTypeBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.persontype.update',
        'https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-update.html',
        'Batch update person types'
    )]
    public function update(array $personTypes): Generator
    {
        $items = [];
        foreach ($personTypes as $id => $personType) {
            $items[$id] = ['fields' => $personType];
        }

        foreach ($this->batch->updateEntityItems('sale.persontype.update', $items) as $key => $item) {
            yield $key => new UpdatedPersonTypeBatchResult($item);
        }
    }

    /**
     * Batch delete person types
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-delete.html
     *
     * @param int[] $personTypeId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.persontype.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-delete.html',
        'Batch delete person types'
    )]
    public function delete(array $personTypeId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.persontype.delete', $personTypeId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
