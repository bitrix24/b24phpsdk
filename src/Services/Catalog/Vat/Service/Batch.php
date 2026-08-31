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

namespace Bitrix24\SDK\Services\Catalog\Vat\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\Vat;
use Bitrix24\SDK\Services\Catalog\Vat\Result\VatAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\Vat\Result\VatUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected Vat\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch adding VAT rates
     *
     * @param array<int, array> $vats
     *
     * @return Generator<int, VatAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.vat.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-add.html',
        'Batch adding VAT rates'
    )]
    public function add(array $vats): Generator
    {
        $items = [];
        foreach ($vats as $vat) {
            $items[] = ['fields' => $vat];
        }

        foreach ($this->batch->addEntityItems('catalog.vat.add', $items) as $key => $item) {
            yield $key => new VatAddedBatchResult($item);
        }
    }

    /**
     * Batch delete VAT rates
     *
     * @param int[] $vatId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.vat.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-delete.html',
        'Batch delete VAT rates'
    )]
    public function delete(array $vatId): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.vat.delete', $vatId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch update VAT rates
     *
     * @param array<int, array> $vats keyed by VAT rate id
     *
     * @return Generator<int, VatUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.vat.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-update.html',
        'Batch update VAT rates'
    )]
    public function update(array $vats): Generator
    {
        $items = [];
        foreach ($vats as $id => $vat) {
            $items[$id] = ['fields' => $vat];
        }

        foreach ($this->batch->updateEntityItems('catalog.vat.update', $items) as $key => $item) {
            yield $key => new VatUpdatedBatchResult($item);
        }
    }
}
