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

namespace Bitrix24\SDK\Services\Task\Stage\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class StagesResult
 *
 * @package Bitrix24\SDK\Services\Task\Stage\Result
 */
class StagesResult extends AbstractResult
{
    /**
     * @return StageItemResult[]
     * @throws BaseException
     */
    public function getStages(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new StageItemResult($item);
        }

        return $items;
    }
}
