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

use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

/**
 * Class UpdatedTaskBatchResult
 *
 * @package Bitrix24\SDK\Services\Task\Result
 */
class UpdatedTaskBatchResult extends UpdatedItemBatchResult
{
    /**
     * @throws BaseException
     */
    public function isSuccess(): bool
    {
        return (bool)$this->getResponseData()->getResult()['task'];
    }
}
