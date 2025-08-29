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
 * Class StatusResult - result of getting a single status
 *
 * @package Bitrix24\SDK\Services\Sale\Status\Result
 */
class StatusResult extends AbstractResult
{
    /**
     * Get status object
     *
     * @throws BaseException
     */
    public function getStatus(): StatusItemResult
    {
        return new StatusItemResult($this->getCoreResponse()->getResponseData()->getResult()['status']);
    }

    /**
     * Get status ID
     *
     * @throws BaseException
     */
    public function getId(): string
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['status']['id'];
    }
}
