<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\StageHistory\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class StageHistoryResult extends AbstractResult
{
    /**
     * @return StageHistoryItemResult[]
     * @throws BaseException
     */
    public function getStageHistoryItems(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new StageHistoryItemResult($item);
        }

        return $items;
    }

    /** @throws BaseException */
    public function getNextItem(): ?int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getNextItem();
    }

    /** @throws BaseException */
    public function getTotal(): ?int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
