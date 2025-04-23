<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Automation\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Automation\Result\TriggerResult;
use Bitrix24\SDK\Services\CRM\Automation\Result\TriggersResult;
use Psr\Log\LoggerInterface;
#[ApiServiceMetadata(new Scope(['crm']))]
class Trigger extends AbstractService
{
    public Batch $batch;

    /**
     * Lead constructor.
     *
     * @param Batch           $batch
     * @param CoreInterface   $core
     * @param LoggerInterface $log
     */
    public function __construct(Batch $batch, CoreInterface $core, LoggerInterface $log)
    {
        parent::__construct($core, $log);
        $this->batch = $batch;
    }

    /**
     * add new trigger
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/automation/triggers/crm-automation-trigger-add.html
     *
     * @param string $code
     * @param string $name
     *
     * @return AddedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.automation.trigger.add',
        'https://apidocs.bitrix24.com/api-reference/crm/automation/triggers/crm-automation-trigger-add.html',
        'Method adds new trigger'
    )]
    public function add(string $code, string $name): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.automation.trigger.add',
                [
                    'CODE' => $code,
                    'NAME' => $name,
                ]
            )
        );
    }

    /**
     * Deletes the specified trigger
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/automation/triggers/crm-automation-trigger-delete.html
     *
     * @param string $code
     *
     * @return DeletedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.automation.trigger.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/automation/triggers/crm-automation-trigger-delete.html',
        'Deletes the specified trigger.'
    )]
    public function delete(string $code): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.automation.trigger.delete',
                [
                    'CODE' => $code,
                ]
            )
        );
    }

    /**
     * Get list of trigger items.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/automation/triggers/crm-automation-trigger-list.html
     *
     * @throws BaseException
     * @throws TransportException
     * @return TriggerResult
     */
    #[ApiEndpointMetadata(
        'crm.automation.trigger.list',
        'https://apidocs.bitrix24.com/api-reference/crm/automation/triggers/crm-automation-trigger-list.html',
        'Get list of trigger items.'
    )]
    public function list(): TriggerResult
    {
        return new TriggerResult(
            $this->core->call(
                'crm.automation.trigger.list',
                []
            )
        );
    }
    
    /**
     * Execute trigger
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/automation/triggers/crm-automation-trigger-execute.html
     *
     * @param string $code
     * @param int    $ownerTypeId
     * @param int    $ownerId
     *
     * @return AddedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.automation.trigger.execute',
        'https://apidocs.bitrix24.com/api-reference/crm/automation/triggers/crm-automation-trigger-execute.html',
        'Method adds new trigger'
    )]
    public function execute(string $code, int $ownerTypeId, int $ownerId): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.automation.trigger.execute',
                [
                    'CODE' => $code,
                    'OWNER_TYPE_ID' => $ownerTypeId,
                    'OWNER_ID' => $ownerId,
                ]
            )
        );
    }

}