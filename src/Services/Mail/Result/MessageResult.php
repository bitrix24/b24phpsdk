<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class MessageResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function message(): MessageItemResult
    {
        return new MessageItemResult($this->getCoreResponse()->getResponseData()->getResult()['item']);
    }
}
