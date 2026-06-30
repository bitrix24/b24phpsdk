<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class MessageThreadResult extends AbstractResult
{
    /**
     * @return MessageItemResult[]
     * @throws BaseException
     */
    public function getMessages(): array
    {
        return array_map(
            static fn(array $item): MessageItemResult => new MessageItemResult($item),
            $this->getCoreResponse()->getResponseData()->getResult()
        );
    }
}
