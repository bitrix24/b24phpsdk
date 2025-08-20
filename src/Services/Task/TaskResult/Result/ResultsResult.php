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

namespace Bitrix24\SDK\Services\Task\TaskResult\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class ResultsResult
 *
 * @package Bitrix24\SDK\Services\Task\TaskResult\Result
 */
class ResultsResult extends AbstractResult
{
    /**
     * @return ResultItemResult[]
     * @throws BaseException
     */
    public function getResults(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new ResultItemResult($item);
        }

        return $items;
    }
}
