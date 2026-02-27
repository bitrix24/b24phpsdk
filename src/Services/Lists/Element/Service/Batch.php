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

namespace Bitrix24\SDK\Services\Lists\Element\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\AbstractBatchService;
use Bitrix24\SDK\Services\Lists\Element\Result\ElementItemResult;
use Generator;

#[ApiBatchServiceMetadata(new Scope(['lists']))]
class Batch extends AbstractBatchService
{
    /**
     * Batch add method for universal list elements
     *
     * @param array<array{IBLOCK_TYPE_ID: string, IBLOCK_ID?: int, IBLOCK_CODE?: string, ELEMENT_CODE: string, FIELDS: array, IBLOCK_SECTION_ID?: int, LIST_ELEMENT_URL?: string}> $elements Array of element data
     *
     * @return Generator<int, AddedItemBatchResult, mixed, mixed>
     *
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'lists.element.add',
        'https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-add.html',
        'Creates universal list elements.'
    )]
    public function add(array $elements): Generator
    {
        foreach ($this->batch->addEntityItems('lists.element.add', $elements) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update method for universal list elements
     *
     * Update elements in array with structure:
     * element_id => [
     *  'IBLOCK_TYPE_ID' => string,
     *  'IBLOCK_ID' => int,              // or use IBLOCK_CODE
     *  'ELEMENT_ID' => int,             // or use ELEMENT_CODE
     *  'FIELDS' => array,               // Element fields to update
     *  'IBLOCK_CODE' => string,         // optional
     *  'ELEMENT_CODE' => string         // optional
     * ]
     *
     * @param array $elements Array of element data with IDs as keys
     *
     * @return Generator<int, UpdatedItemBatchResult, mixed, mixed>
     *
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'lists.element.update',
        'https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-update.html',
        'Updates universal list elements.'
    )]
    public function update(array $elements): Generator
    {
        foreach ($this->batch->updateEntityItems('lists.element.update', $elements) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete method for universal list elements
     *
     * @param array<array{IBLOCK_TYPE_ID: string, IBLOCK_ID?: int, IBLOCK_CODE?: string, ELEMENT_ID?: int, ELEMENT_CODE?: string}> $elements Array of element parameters for deletion
     *
     * @return Generator<int, DeletedItemBatchResult, mixed, mixed>
     *
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'lists.element.delete',
        'https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-delete.html',
        'Deletes universal list elements.'
    )]
    public function delete(array $elements): Generator
    {
        foreach ($this->batch->deleteEntityItems('lists.element.delete', $elements) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch method for getting list of universal list elements
     *
     * @param string $iblockTypeId Information block type identifier
     * @param int|string $iblock Information block ID or code
     * @param array $select Fields to select
     * @param array $filter Filter conditions
     * @param array $order Sorting configuration
     * @param int|null $limit Maximum number of items to retrieve
     *
     * @return Generator<int, ElementItemResult, mixed, mixed>
     *
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'lists.element.get',
        'https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-get.html',
        'Returns array with universal list elements.'
    )]
    public function get(
        string $iblockTypeId,
        int|string $iblock,
        array $select = [],
        array $filter = [],
        array $order = [],
        ?int $limit = null
    ): Generator {
        $additionalParams = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
        ];

        if (is_int($iblock)) {
            $additionalParams['IBLOCK_ID'] = $iblock;
        } else {
            $additionalParams['IBLOCK_CODE'] = $iblock;
        }

        foreach ($this->batch->getTraversableList('lists.element.get', $order, $filter, $select, $limit, $additionalParams) as $key => $value) {
            yield $key => new ElementItemResult($value);
        }
    }
}
