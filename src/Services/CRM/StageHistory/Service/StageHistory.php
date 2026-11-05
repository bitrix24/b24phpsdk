<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\StageHistory\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\StageHistory\Result\StageHistoryResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class StageHistory extends AbstractService
{
    /**
     * Lists stage changes for leads (1), deals (2), or a smart process entity type.
     *
     * Leads return STATUS_ID/STATUS_SEMANTIC_ID; deals and smart processes return
     * STAGE_ID/STAGE_SEMANTIC_ID and CATEGORY_ID. Omitted selected fields are null.
     *
     * @param array<string, string> $order
     * @param array<string, mixed> $filter
     * @param string[] $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.stagehistory.list',
        'https://apidocs.bitrix24.com/api-reference/crm/crm-stage-history-list.html',
        'Lists CRM stage history records'
    )]
    public function list(int $entityTypeId, array $order = [], array $filter = [], array $select = [], int $start = 0): StageHistoryResult
    {
        return new StageHistoryResult($this->core->call('crm.stagehistory.list', [
            'entityTypeId' => $entityTypeId,
            'order' => $order,
            'filter' => $filter,
            'select' => $select,
            'start' => $start,
        ]));
    }
}
