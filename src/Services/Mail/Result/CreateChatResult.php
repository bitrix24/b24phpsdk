<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class CreateChatResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getResult(): CreateChatItemResult
    {
        return new CreateChatItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
