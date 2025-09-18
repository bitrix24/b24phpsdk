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

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Result\NumeratorItemResult;
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
     * Batch adding numerators
     *
     * @param array <int, array{
     *     name: string,
     *     template: string,
     *     settings?: array
     *   }> $numerators
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.documentgenerator.numerator.add',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/numerator/crm-document-generator-numerator-add.html',
        'Batch adding numerators'
    )]
    public function add(array $numerators): Generator
    {
        $items = [];
        foreach ($numerators as $item) {
            $items[] = [
                'fields' => $item,
            ];
        }

        foreach ($this->batch->addEntityItems('crm.documentgenerator.numerator.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update numerators
     *
     * Update elements in array with structure
     * id => [  // Numerator id
     *     'fields' => [] // Numerator fields to update
     * ]
     *
     * @param array<int, array> $entityItems
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.documentgenerator.numerator.update',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/numerator/crm-document-generator-numerator-update.html',
        'Update in batch mode a list of numerators'
    )]
    public function update(array $entityItems): Generator
    {
        foreach ($this->batch->updateEntityItems('crm.documentgenerator.numerator.update', $entityItems) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete numerators
     *
     * @param int[] $numeratorId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.documentgenerator.numerator.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/numerator/crm-document-generator-numerator-delete.html',
        'Batch delete numerators'
    )]
    public function delete(array $numeratorId): Generator
    {
        foreach ($this->batch->deleteEntityItems('crm.documentgenerator.numerator.delete', $numeratorId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
