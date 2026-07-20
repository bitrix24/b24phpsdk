<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class MessagesResult extends AbstractResult
{
    /**
     * @return MessageItemResult[]
     * @throws BaseException
     */
    public function getMessages(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        $items = $result['items'] ?? $result;

        return array_map(static fn(array $item): MessageItemResult => new MessageItemResult($item), $items);
    }
}
