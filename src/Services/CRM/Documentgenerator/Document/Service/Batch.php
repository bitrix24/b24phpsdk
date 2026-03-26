<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <titarx@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result\AddedDocumentBatchResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result\UpdatedDocumentBatchResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result\DeletedDocumentBatchResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result\DocumentItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch
{
    /**
     * Batch constructor
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for documents
     *
     * @return Generator<int, DocumentItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.documentgenerator.document.list',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-list.html',
        'Batch list method for documents'
    )]
    public function list(?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'limit' => $limit,
            ]
        );

        // Use pagination-based traversable to avoid dependency on element ID field name
        $documentListGenerator = $this->batch->getTraversableListWithCount(
            'crm.documentgenerator.document.list',
            [],
            [],
            [],
            $limit
        );
        foreach ($documentListGenerator as $key => $value) {
            yield $key => new DocumentItemResult($value);
        }
    }

    /**
     * Batch adding documents
     *
     * @param array<int, array{
     *     templateId: int,
     *     entityTypeId: int,
     *     entityId: int,
     *     values?: array,
     *     stampsEnabled?: int
     *   }> $documents
     *
     * @return Generator<int, AddedDocumentBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.documentgenerator.document.add',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-add.html',
        'Batch adding documents'
    )]
    public function add(array $documents): Generator
    {
        foreach ($this->batch->addEntityItems('crm.documentgenerator.document.add', $documents) as $key => $item) {
            yield $key => new AddedDocumentBatchResult($item);
        }
    }

    /**
     * Batch update documents
     *
     * Update elements in array with structure
     * id => [  // Document id
     *     'values' => [],         // Document values to update
     *     'stampsEnabled' => int  // Optional: whether to apply stamps (1 = yes, 0 = no)
     * ]
     *
     * @param array<int, array{values: array, stampsEnabled?: int}> $entityItems
     *
     * @return Generator<int, UpdatedDocumentBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.documentgenerator.document.update',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-update.html',
        'Update in batch mode a list of documents'
    )]
    public function update(array $entityItems): Generator
    {
        foreach (
            $this->batch->updateEntityItems(
                'crm.documentgenerator.document.update',
                $entityItems
            ) as $key => $item
        ) {
            yield $key => new UpdatedDocumentBatchResult($item);
        }
    }

    /**
     * Batch delete documents
     *
     * @param int[] $documentId
     *
     * @return Generator<int, DeletedDocumentBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.documentgenerator.document.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-delete.html',
        'Batch delete documents'
    )]
    public function delete(array $documentId): Generator
    {
        foreach (
            $this->batch->deleteEntityItems(
                'crm.documentgenerator.document.delete',
                $documentId
            ) as $key => $item
        ) {
            yield $key => new DeletedDocumentBatchResult($item);
        }
    }
}
