<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */


declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\CallList\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class CallListResult extends AbstractResult
{
    public function calllist(): CallListItemResult
    {
        return new CallListItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
