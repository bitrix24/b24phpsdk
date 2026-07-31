<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class SendMessageResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getResult(): SendMessageItemResult
    {
        return new SendMessageItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
