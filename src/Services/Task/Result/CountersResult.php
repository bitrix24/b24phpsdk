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

namespace Bitrix24\SDK\Services\Task\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class CountersResult
 *
 * @package Bitrix24\SDK\Services\Task\Result
 */
class CountersResult extends AbstractResult
{
    /**
     * @return CounterItemResult[]
     * @throws BaseException
     */
    public function getCounters(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $key => $item) {
            $item['key'] = $key;
            $items[] = new CounterItemResult($item);
        }

        return $items;
    }
}
