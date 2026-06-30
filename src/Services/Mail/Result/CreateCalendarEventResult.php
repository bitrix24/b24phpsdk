<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class CreateCalendarEventResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getResult(): CreateCalendarEventItemResult
    {
        return new CreateCalendarEventItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
