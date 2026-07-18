<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class CreateTaskResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getResult(): CreateTaskItemResult
    {
        return new CreateTaskItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
