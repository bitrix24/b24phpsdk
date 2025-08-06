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
 * Class TasksResult
 *
 * @package Bitrix24\SDK\Services\Task\Result
 */
class TasksResult extends AbstractResult
{
    /**
     * @return TaskItemResult[]
     * @throws BaseException
     */
    public function getTasks(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['tasks'] as $item) {
            $items[] = new TaskItemResult($item);
        }

        return $items;
    }
}
