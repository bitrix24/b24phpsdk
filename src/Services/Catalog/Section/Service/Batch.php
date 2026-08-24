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

namespace Bitrix24\SDK\Services\Catalog\Section\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\Section;
use Bitrix24\SDK\Services\Catalog\Section\Result\SectionAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\Section\Result\SectionUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected Section\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch adding trade-catalog sections
     *
     * @param array<int, array> $sections
     *
     * @return Generator<int, SectionAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.section.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-add.html',
        'Batch adding trade-catalog sections'
    )]
    public function add(array $sections): Generator
    {
        $items = [];
        foreach ($sections as $section) {
            $items[] = ['fields' => $section];
        }

        foreach ($this->batch->addEntityItems('catalog.section.add', $items) as $key => $item) {
            yield $key => new SectionAddedBatchResult($item);
        }
    }

    /**
     * Batch delete trade-catalog sections
     *
     * @param int[] $sectionId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.section.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-delete.html',
        'Batch delete trade-catalog sections'
    )]
    public function delete(array $sectionId): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.section.delete', $sectionId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch update trade-catalog sections
     *
     * @param array<int, array> $sections keyed by section id
     *
     * @return Generator<int, SectionUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.section.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-update.html',
        'Batch update trade-catalog sections'
    )]
    public function update(array $sections): Generator
    {
        $items = [];
        foreach ($sections as $id => $section) {
            $items[$id] = ['fields' => $section];
        }

        foreach ($this->batch->updateEntityItems('catalog.section.update', $items) as $key => $item) {
            yield $key => new SectionUpdatedBatchResult($item);
        }
    }
}
