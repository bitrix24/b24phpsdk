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

namespace Bitrix24\SDK\Services\Department\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Department\Result\DepartmentItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['department']))]
class Batch
{
    /**
     * Batch constructor.
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch get method for departments
     *
     * @param array{
     *   ID?: int,
     *   NAME?: string,
     *   SORT?: int,
     *   PARENT?: int,
     *   UF_HEAD?: int,
     *   } $filter
     *
     * @return Generator<int, DepartmentItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.department.get',
        'https://apidocs.bitrix24.com/api-reference/departments/department-get.html',
        'Batch get method for departments'
    )]
    public function get(array $filter=[], string $sort='ID', string $order='ASC', ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'order'  => $order,
                'filter' => $filter,
                'select' => $select,
                'limit'  => $limit,
            ]
        );
        foreach ($this->batch->getTraversableList('crm.department.get', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new DepartmentItemResult($value);
        }
    }

    /**
     * Batch adding departments
     *
     * @param array <int, array{
     *   NAME?: string,
     *   SORT?: int,
     *   PARENT?: int,
     *   UF_HEAD?: int,
     *   }> $departments
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.department.add',
        'https://apidocs.bitrix24.com/api-reference/departments/department-add.html',
        'Batch adding departments'
    )]
    public function add(array $departments): Generator
    {
        foreach ($this->batch->addEntityItems('crm.department.add', $departments) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update departments
     *
     * Update elements in array with structure
     * element_id => [  // department id
     *  'fields' => [] // department fields to update
     * ]
     *
     * @param array<int, array> $departmentItems
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.department.update',
        'https://apidocs.bitrix24.com/api-reference/departments/department-update.html',
        'Update in batch mode a list of departments'
    )]
    public function update(array $departmentItems): Generator
    {
        foreach ($this->batch->updateEntityItems('crm.department.update', $departmentItems) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete departments
     *
     * @param int[] $departmentIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.department.delete',
        'https://apidocs.bitrix24.com/api-reference/departments/department-delete.html',
        'Batch delete departments'
    )]
    public function delete(array $departmentIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('crm.department.delete', $departmentIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
