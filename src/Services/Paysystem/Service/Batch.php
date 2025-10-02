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

namespace Bitrix24\SDK\Services\Paysystem\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\AbstractBatchService;
use Bitrix24\SDK\Services\Paysystem\Result\PaysystemItemResult;
use Generator;

#[ApiBatchServiceMetadata(new Scope(['pay_system']))]
class Batch extends AbstractBatchService
{
    /**
     * Batch add method for payment systems
     *
     * @param array<array> $paysystems Array of payment system data
     *
     * @return Generator<int, AddedItemBatchResult, mixed, mixed>
     *
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.paysystem.add',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-add.html',
        'Adds payment systems.'
    )]
    public function add(array $paysystems): Generator
    {
        foreach ($this->batch->addEntityItems('sale.paysystem.add', $paysystems) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update method for payment systems
     *
     * Update elements in array with structure
     * element_id => [  // PS item id
     *  'fields' => [] // PS item fields to update
     * ]
     *
     * @return Generator<int, UpdatedItemBatchResult, mixed, mixed>
     *
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.paysystem.update',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-update.html',
        'Updates payment systems.'
    )]
    public function update(array $paysystems): Generator
    {
        foreach ($this->batch->updateEntityItems('sale.paysystem.update', $paysystems) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete method for payment systems
     *
     * @param array<int> $paysystemIds Array of payment system identifiers
     *
     * @return Generator<int, DeletedItemBatchResult, mixed, mixed>
     *
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.paysystem.delete',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-delete.html',
        'Deletes payment systems.'
    )]
    public function delete(array $paysystemIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.paysystem.delete', $paysystemIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
