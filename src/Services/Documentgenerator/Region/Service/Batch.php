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

namespace Bitrix24\SDK\Services\Documentgenerator\Region\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Documentgenerator\Region\Result\AddedRegionBatchResult;
use Bitrix24\SDK\Services\Documentgenerator\Region\Result\DeletedRegionBatchResult;
use Bitrix24\SDK\Services\Documentgenerator\Region\Result\RegionItemResult;
use Bitrix24\SDK\Services\Documentgenerator\Region\Result\UpdatedRegionBatchResult;
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
     * Batch list method for regions
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-list.html
     *
     * @return Generator<int, RegionItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.region.list',
        'https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-list.html',
        'Batch list method for regions'
    )]
    public function list(?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'limit' => $limit,
            ]
        );

        $regionListGenerator = $this->batch->getTraversableListWithCount(
            'documentgenerator.region.list',
            [],
            [],
            [],
            $limit
        );
        foreach ($regionListGenerator as $key => $value) {
            yield $key => new RegionItemResult($value);
        }
    }

    /**
     * Batch adding regions
     *
     * @param array<int, array{
     *     languageId: string,
     *     name: string,
     *     code: string
     *   }> $regions
     *
     * @return Generator<int, AddedRegionBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.region.add',
        'https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-add.html',
        'Batch adding regions'
    )]
    public function add(array $regions): Generator
    {
        $items = [];
        foreach ($regions as $item) {
            $items[] = [
                'fields' => $item,
            ];
        }

        foreach ($this->batch->addEntityItems('documentgenerator.region.add', $items) as $key => $item) {
            yield $key => new AddedRegionBatchResult($item);
        }
    }

    /**
     * Batch update regions
     *
     * Update elements in array with structure
     * id => [  // Region id
     *     'fields' => [] // Region fields to update
     * ]
     *
     * @param array<int, array> $entityItems
     *
     * @return Generator<int, UpdatedRegionBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.region.update',
        'https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-update.html',
        'Update in batch mode a list of regions'
    )]
    public function update(array $entityItems): Generator
    {
        foreach (
            $this->batch->updateEntityItems(
                'documentgenerator.region.update',
                $entityItems
            ) as $key => $item
        ) {
            yield $key => new UpdatedRegionBatchResult($item);
        }
    }

    /**
     * Batch delete regions
     *
     * @param int[] $regionId
     *
     * @return Generator<int, DeletedRegionBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.region.delete',
        'https://apidocs.bitrix24.com/api-reference/document-generator/region/document-generator-region-delete.html',
        'Batch delete regions'
    )]
    public function delete(array $regionId): Generator
    {
        foreach (
            $this->batch->deleteEntityItems(
                'documentgenerator.region.delete',
                $regionId
            ) as $key => $item
        ) {
            yield $key => new DeletedRegionBatchResult($item);
        }
    }
}

