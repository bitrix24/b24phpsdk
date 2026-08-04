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

namespace Bitrix24\SDK\Services\Catalog\DocumentElement\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\DocumentElement;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Result\DocumentElementAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Result\DocumentElementUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected DocumentElement\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch adding warehouse accounting document line items
     *
     * @param array<int, array> $documentElements
     *
     * @return Generator<int, DocumentElementAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.document.element.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/catalog-document-element-add.html',
        'Batch adding warehouse accounting document line items'
    )]
    public function add(array $documentElements): Generator
    {
        $items = [];
        foreach ($documentElements as $documentElement) {
            $items[] = ['fields' => $documentElement];
        }

        foreach ($this->batch->addEntityItems('catalog.document.element.add', $items) as $key => $item) {
            yield $key => new DocumentElementAddedBatchResult($item);
        }
    }

    /**
     * Batch delete warehouse accounting document line items
     *
     * @param int[] $documentElementId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.document.element.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/catalog-document-element-delete.html',
        'Batch delete warehouse accounting document line items'
    )]
    public function delete(array $documentElementId): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.document.element.delete', $documentElementId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch update warehouse accounting document line items
     *
     * @param array<int, array> $documentElements keyed by document element id
     *
     * @return Generator<int, DocumentElementUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.document.element.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/catalog-document-element-update.html',
        'Batch update warehouse accounting document line items'
    )]
    public function update(array $documentElements): Generator
    {
        $items = [];
        foreach ($documentElements as $id => $documentElement) {
            $items[$id] = ['fields' => $documentElement];
        }

        foreach ($this->batch->updateEntityItems('catalog.document.element.update', $items) as $key => $item) {
            yield $key => new DocumentElementUpdatedBatchResult($item);
        }
    }
}
