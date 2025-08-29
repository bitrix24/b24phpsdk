<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Status\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class StatusesResult - result of getting a list of statuses
 *
 * @package Bitrix24\SDK\Services\Sale\Status\Result
 */
class StatusesResult extends AbstractResult
{
    /**
     * Get array of status objects
     *
     * @return StatusItemResult[]
     * @throws BaseException
     */
    public function getStatuses(): array
    {
        $statuses = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['statuses'] as $status) {
            $statuses[] = new StatusItemResult($status);
        }

        return $statuses;
    }

    /**
     * Get total number of statuses
     *
     * @throws BaseException
     */
    public function getTotal(): int
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return isset($result['total']) ? (int)$result['total'] : count($this->getStatuses());
    }
}
