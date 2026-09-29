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

namespace Bitrix24\SDK\Services\CRM\Userfieldconfig\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\CRM\Userfieldconfig;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\AddedUserfieldConfigItemBatchResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\DeletedUserfieldConfigItemBatchResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\UpdatedUserfieldConfigItemBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['crm', 'userfieldconfig']))]
class Batch
{
    public function __construct(protected Userfieldconfig\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch adding custom fields
     *
     * @param non-empty-string $moduleId
     * @param array<int, array<string, mixed>> $fields
     *
     * @return Generator<int, AddedUserfieldConfigItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'userfieldconfig.add',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-add.html',
        'Batch adding custom fields'
    )]
    public function add(string $moduleId, array $fields): Generator
    {
        $items = [];
        foreach ($fields as $field) {
            $items[] = [
                'moduleId' => $moduleId,
                'field' => $field,
            ];
        }

        foreach ($this->batch->addEntityItems('userfieldconfig.add', $items) as $key => $item) {
            yield $key => new AddedUserfieldConfigItemBatchResult($item);
        }
    }

    /**
     * Batch updating custom fields
     *
     * @param non-empty-string $moduleId
     * @param array<int, array<string, mixed>> $fields keyed by userfieldconfig id
     *
     * @return Generator<int, UpdatedUserfieldConfigItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'userfieldconfig.update',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-update.html',
        'Batch updating custom fields'
    )]
    public function update(string $moduleId, array $fields): Generator
    {
        foreach ($this->batch->updateUserfieldConfigItems('userfieldconfig.update', $moduleId, $fields) as $key => $item) {
            yield $key => new UpdatedUserfieldConfigItemBatchResult($item);
        }
    }

    /**
     * Batch deleting custom fields
     *
     * @param non-empty-string $moduleId
     * @param positive-int[] $ids
     *
     * @return Generator<int, DeletedUserfieldConfigItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'userfieldconfig.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/userfieldconfig-delete.html',
        'Batch deleting custom fields'
    )]
    public function delete(string $moduleId, array $ids): Generator
    {
        foreach ($this->batch->deleteUserfieldConfigItems('userfieldconfig.delete', $moduleId, $ids) as $key => $item) {
            yield $key => new DeletedUserfieldConfigItemBatchResult($item);
        }
    }
}
