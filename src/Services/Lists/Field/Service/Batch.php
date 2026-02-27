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

namespace Bitrix24\SDK\Services\Lists\Field\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Lists\Field\Result\AddedFieldBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\AbstractBatchService;
use Generator;

#[ApiBatchServiceMetadata(new Scope(['lists']))]
class Batch extends AbstractBatchService
{
    /**
     * Batch add method for fields
     *
     * @param array<array{IBLOCK_TYPE_ID: string, IBLOCK_ID?: int, IBLOCK_CODE?: string, FIELDS: array}> $fields Array of field data
     *
     * @return Generator<int, AddedFieldBatchResult, mixed, mixed>
     *
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'lists.field.add',
        'https://apidocs.bitrix24.com/api-reference/lists/fields/lists-field-add.html',
        'Creates fields for universal lists.'
    )]
    public function add(array $fields): Generator
    {
        foreach ($this->batch->addEntityItems('lists.field.add', $fields) as $key => $item) {
            yield $key => new AddedFieldBatchResult($item);
        }
    }

    /**
     * Batch update method for fields
     *
     * Update elements in array with structure:
     * [
     *  'IBLOCK_TYPE_ID' => string,
     *  'FIELD_ID' => string,
     *  'FIELDS' => [],              // Field parameters to update
     *  'IBLOCK_ID' => int,          // optional
     *  'IBLOCK_CODE' => string      // optional
     * ]
     *
     * @param array $fields Array of field data
     *
     * @return Generator<int, UpdatedItemBatchResult, mixed, mixed>
     *
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'lists.field.update',
        'https://apidocs.bitrix24.com/api-reference/lists/fields/lists-field-update.html',
        'Updates fields of universal lists.'
    )]
    public function update(array $fields): Generator
    {
        foreach ($this->batch->updateEntityItems('lists.field.update', $fields) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete method for fields
     *
     * @param array<array{IBLOCK_TYPE_ID: string, FIELD_ID: string, IBLOCK_ID?: int, IBLOCK_CODE?: string}> $fields Array of field parameters for deletion
     *
     * @return Generator<int, DeletedItemBatchResult, mixed, mixed>
     *
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'lists.field.delete',
        'https://apidocs.bitrix24.com/api-reference/lists/fields/lists-field-delete.html',
        'Deletes fields from universal lists.'
    )]
    public function delete(array $fields): Generator
    {
        foreach ($this->batch->deleteEntityItems('lists.field.delete', $fields) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
