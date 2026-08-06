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

namespace Bitrix24\SDK\Services\Catalog\DocumentContractor\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\DocumentContractor;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Result\DocumentContractorAddedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected DocumentContractor\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch binding vendors, contacts or companies, to warehouse accounting documents
     *
     * @param array<int, array> $documentContractors
     *
     * @return Generator<int, DocumentContractorAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.documentcontractor.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-add.html',
        'Batch binding vendors, contacts or companies, to warehouse accounting documents'
    )]
    public function add(array $documentContractors): Generator
    {
        $items = [];
        foreach ($documentContractors as $documentContractor) {
            $items[] = ['fields' => $documentContractor];
        }

        foreach ($this->batch->addEntityItems('catalog.documentcontractor.add', $items) as $key => $item) {
            yield $key => new DocumentContractorAddedBatchResult($item);
        }
    }

    /**
     * Batch delete vendor bindings from warehouse accounting documents
     *
     * @param int[] $documentContractorId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.documentcontractor.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-delete.html',
        'Batch delete vendor bindings from warehouse accounting documents'
    )]
    public function delete(array $documentContractorId): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.documentcontractor.delete', $documentContractorId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
