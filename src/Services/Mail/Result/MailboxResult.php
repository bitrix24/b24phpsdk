<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class MailboxResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function mailbox(): MailboxItemResult
    {
        return new MailboxItemResult($this->getCoreResponse()->getResponseData()->getResult()['item']);
    }
}
