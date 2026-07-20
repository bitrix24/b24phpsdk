<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class BooleanResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function isSuccess(): bool
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return (bool)($result['result'] ?? $result);
    }
}
