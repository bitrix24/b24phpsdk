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

namespace Bitrix24\SDK\Services\Documentgenerator\Numerator\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Result\AddedNumeratorBatchResult;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Result\DeletedNumeratorBatchResult;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Result\NumeratorItemResult;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Result\UpdatedNumeratorBatchResult;
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
     * Batch list method for numerators
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-list.html
     *
     * @return Generator<int, NumeratorItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.numerator.list',
        'https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-list.html',
        'Batch list method for numerators'
    )]
    public function list(?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'limit' => $limit,
            ]
        );

        $numeratorListGenerator = $this->batch->getTraversableListWithCount(
            'documentgenerator.numerator.list',
            [],
            [],
            [],
            $limit
        );
        foreach ($numeratorListGenerator as $key => $value) {
            yield $key => new NumeratorItemResult($value);
        }
    }

    /**
     * Batch adding numerators
     *
     * @param array<int, array{
     *     name: string,
     *     template: string,
     *     settings?: array
     *   }> $numerators
     *
     * @return Generator<int, AddedNumeratorBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.numerator.add',
        'https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-add.html',
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

        foreach ($this->batch->addEntityItems('documentgenerator.numerator.add', $items) as $key => $item) {
            yield $key => new AddedNumeratorBatchResult($item);
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
     *
     * @return Generator<int, UpdatedNumeratorBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.numerator.update',
        'https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-update.html',
        'Update in batch mode a list of numerators'
    )]
    public function update(array $entityItems): Generator
    {
        foreach (
            $this->batch->updateEntityItems(
                'documentgenerator.numerator.update',
                $entityItems
            ) as $key => $item
        ) {
            yield $key => new UpdatedNumeratorBatchResult($item);
        }
    }

    /**
     * Batch delete numerators
     *
     * @param int[] $numeratorId
     *
     * @return Generator<int, DeletedNumeratorBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.numerator.delete',
        'https://apidocs.bitrix24.com/api-reference/document-generator/numerators/document-generator-numerator-delete.html',
        'Batch delete numerators'
    )]
    public function delete(array $numeratorId): Generator
    {
        foreach (
            $this->batch->deleteEntityItems(
                'documentgenerator.numerator.delete',
                $numeratorId
            ) as $key => $item
        ) {
            yield $key => new DeletedNumeratorBatchResult($item);
        }
    }
}
