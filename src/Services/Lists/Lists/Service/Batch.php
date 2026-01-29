<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Lists\Lists\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\AbstractBatchService;
use Generator;

#[ApiBatchServiceMetadata(new Scope(['lists']))]
class Batch extends AbstractBatchService
{
    /**
     * Batch add method for universal lists
     *
     * @param array<array{IBLOCK_TYPE_ID: string, IBLOCK_CODE: string, FIELDS: array, MESSAGES?: array, RIGHTS?: array, SOCNET_GROUP_ID?: int}> $lists Array of list data
     *
     * @return Generator<int, AddedItemBatchResult, mixed, mixed>
     *
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'lists.add',
        'https://apidocs.bitrix24.com/api-reference/lists/lists/lists-add.html',
        'Creates universal lists.'
    )]
    public function add(array $lists): Generator
    {
        foreach ($this->batch->addEntityItems('lists.add', $lists) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update method for universal lists
     *
     * Update elements in array with structure:
     * element_id => [
     *  'IBLOCK_TYPE_ID' => string,
     *  'FIELDS' => [],              // List fields to update
     *  'IBLOCK_CODE' => string,     // optional
     *  'MESSAGES' => array,         // optional
     *  'RIGHTS' => array,           // optional
     *  'SOCNET_GROUP_ID' => int     // optional
     * ]
     *
     * @param array $lists Array of list data with IDs as keys
     *
     * @return Generator<int, UpdatedItemBatchResult, mixed, mixed>
     *
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'lists.update',
        'https://apidocs.bitrix24.com/api-reference/lists/lists/lists-update.html',
        'Updates universal lists.'
    )]
    public function update(array $lists): Generator
    {
        foreach ($this->batch->updateEntityItems('lists.update', $lists) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete method for universal lists
     *
     * @param array<array{IBLOCK_TYPE_ID: string, IBLOCK_ID?: int, IBLOCK_CODE?: string, SOCNET_GROUP_ID?: int}> $lists Array of list parameters for deletion
     *
     * @return Generator<int, DeletedItemBatchResult, mixed, mixed>
     *
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'lists.delete',
        'https://apidocs.bitrix24.com/api-reference/lists/lists/lists-delete.html',
        'Deletes universal lists.'
    )]
    public function delete(array $lists): Generator
    {
        foreach ($this->batch->deleteEntityItems('lists.delete', $lists) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
