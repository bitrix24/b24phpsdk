<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Automation\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\CRM\Automation\Result\TriggerItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch
{
    protected BatchOperationsInterface $batch;
    protected LoggerInterface $log;

    /**
     * Batch constructor.
     *
     * @param BatchOperationsInterface $batch
     * @param LoggerInterface          $log
     */
    public function __construct(BatchOperationsInterface $batch, LoggerInterface $log)
    {
        $this->batch = $batch;
        $this->log = $log;
    }

    /**
     * Batch list method for triggers
     *
     *
     * @return Generator<int, TriggerItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.automation.trigger.list',
        'https://apidocs.bitrix24.com/api-reference/crm/automation/triggers/crm-automation-trigger-list.html',
        'Batch list method for triggers'
    )]
    public function list(): Generator
    {
        foreach ($this->batch->getTraversableList('crm.automation.trigger.list') as $key => $value) {
            yield $key => new TriggerItemResult($value);
        }
    }

    /**
     * Batch adding triggers
     *
     * @param array <int, array{
     *   CODE?: string,
     *   NAME?: string,
     *   }> $triggers
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.automation.trigger.add',
        'https://apidocs.bitrix24.com/api-reference/crm/automation/triggers/crm-automation-trigger-add.html',
        'Batch adding triggers'
    )]
    public function add(array $triggers): Generator
    {
        foreach ($this->batch->addEntityItems('crm.automation.trigger.add', $triggers) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch delete triggers
     *
     * @param array <int, array{
     *                          CODE?: string
     *   }> $codes
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.automation.trigger.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/automation/triggers/crm-automation-trigger-delete.html',
        'Batch delete triggers'
    )]
    public function delete(array $codes): Generator
    {
        foreach ($this->batch->deleteEntityItems('crm.automation.trigger.delete', $codes) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
    
    /**
     * Batch execute triggers
     *
     * @param array <int, array{
     *                          CODE?: string,
     *                          OWNER_TYPE_ID?: string,
     *                          OWNER_ID?: int,
     *   }> $triggers
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.automation.trigger.execute',
        'https://apidocs.bitrix24.com/api-reference/crm/automation/triggers/crm-automation-trigger-delete.html',
        'Batch execute triggers'
    )]
    public function execute(array $triggers): Generator
    {
        foreach ($this->batch->executeItems('crm.automation.trigger.execute', $triggers) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}