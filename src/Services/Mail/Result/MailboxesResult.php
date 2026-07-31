<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class MailboxesResult extends AbstractResult
{
    /**
     * @return MailboxItemResult[]
     * @throws BaseException
     */
    public function getMailboxes(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        $items = $result['items'] ?? $result;

        return array_map(static fn(array $item): MailboxItemResult => new MailboxItemResult($item), $items);
    }
}
