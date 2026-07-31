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

namespace Bitrix24\SDK\Services\Catalog\Document\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\Document;
use Bitrix24\SDK\Services\Catalog\Document\Result\DocumentAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\Document\Result\DocumentUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected Document\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch adding warehouse accounting documents
     *
     * @param array<int, array> $documents
     *
     * @return Generator<int, DocumentAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.document.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-add.html',
        'Batch adding warehouse accounting documents'
    )]
    public function add(array $documents): Generator
    {
        $items = [];
        foreach ($documents as $document) {
            $items[] = ['fields' => $document];
        }

        foreach ($this->batch->addEntityItems('catalog.document.add', $items) as $key => $item) {
            yield $key => new DocumentAddedBatchResult($item);
        }
    }

    /**
     * Batch delete warehouse accounting documents
     *
     * @param int[] $documentId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.document.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-delete.html',
        'Batch delete warehouse accounting documents'
    )]
    public function delete(array $documentId): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.document.delete', $documentId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch update warehouse accounting documents
     *
     * @param array<int, array> $documents keyed by document id
     *
     * @return Generator<int, DocumentUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.document.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-update.html',
        'Batch update warehouse accounting documents'
    )]
    public function update(array $documents): Generator
    {
        $items = [];
        foreach ($documents as $id => $document) {
            $items[$id] = ['fields' => $document];
        }

        foreach ($this->batch->updateEntityItems('catalog.document.update', $items) as $key => $item) {
            yield $key => new DocumentUpdatedBatchResult($item);
        }
    }
}
