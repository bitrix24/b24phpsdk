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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class CallListStatusesResult
 *
 * @package Bitrix24\SDK\Services\CRM\CallList\Result
 */
class CallListStatusesResult extends AbstractResult
{
    /**
     * @return \Bitrix24\SDK\Services\CRM\CallList\Result\CallListStatusItemResult[]
     * @throws BaseException
     */
    public function getStatuses(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $res[] = new CallListStatusItemResult($item);
        }

        return $res;
    }
}
