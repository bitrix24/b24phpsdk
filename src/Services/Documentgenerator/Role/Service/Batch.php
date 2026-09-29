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

namespace Bitrix24\SDK\Services\Documentgenerator\Role\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Documentgenerator\Role\Result\AddedRoleBatchResult;
use Bitrix24\SDK\Services\Documentgenerator\Role\Result\DeletedRoleBatchResult;
use Bitrix24\SDK\Services\Documentgenerator\Role\Result\RoleItemResult;
use Bitrix24\SDK\Services\Documentgenerator\Role\Result\UpdatedRoleBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['documentgenerator']))]
class Batch
{
    /**
     * Batch constructor
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for roles
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-list.html
     *
     * @return Generator<int, RoleItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.role.list',
        'https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-list.html',
        'Batch list method for roles'
    )]
    public function list(?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'limit' => $limit,
            ]
        );

        $roleListGenerator = $this->batch->getTraversableListWithCount(
            'documentgenerator.role.list',
            [],
            [],
            [],
            $limit
        );
        foreach ($roleListGenerator as $key => $value) {
            yield $key => new RoleItemResult($value);
        }
    }

    /**
     * Batch adding roles
     *
     * @param array<int, array{
     *     name: string,
     *     code?: string,
     *     permissions?: array
     *   }> $roles
     *
     * @return Generator<int, AddedRoleBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.role.add',
        'https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-add.html',
        'Batch adding roles'
    )]
    public function add(array $roles): Generator
    {
        $items = [];
        foreach ($roles as $item) {
            $items[] = [
                'fields' => $item,
            ];
        }

        foreach ($this->batch->addEntityItems('documentgenerator.role.add', $items) as $key => $item) {
            yield $key => new AddedRoleBatchResult($item);
        }
    }

    /**
     * Batch update roles
     *
     * Update elements in array with structure:
     * id => [  // Role id
     *     'fields' => [] // Role fields to update
     * ]
     *
     * @param array<int, array> $entityItems
     *
     * @return Generator<int, UpdatedRoleBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.role.update',
        'https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-update.html',
        'Update in batch mode a list of roles'
    )]
    public function update(array $entityItems): Generator
    {
        foreach (
            $this->batch->updateEntityItems(
                'documentgenerator.role.update',
                $entityItems
            ) as $key => $item
        ) {
            yield $key => new UpdatedRoleBatchResult($item);
        }
    }

    /**
     * Batch delete roles
     *
     * @param int[] $roleId
     *
     * @return Generator<int, DeletedRoleBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.role.delete',
        'https://apidocs.bitrix24.com/api-reference/document-generator/role/document-generator-role-delete.html',
        'Batch delete roles'
    )]
    public function delete(array $roleId): Generator
    {
        foreach (
            $this->batch->deleteEntityItems(
                'documentgenerator.role.delete',
                $roleId
            ) as $key => $item
        ) {
            yield $key => new DeletedRoleBatchResult($item);
        }
    }
}
