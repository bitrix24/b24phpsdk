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

namespace Bitrix24\SDK\Services\Task\Elapseditem\Result;

use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

/**
 * Class UpdatedElapseditemResult
 *
 * @package Bitrix24\SDK\Services\Task\Elapseditem\Result
 */
class UpdatedElapseditemResult extends UpdatedItemResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function isSuccess(): bool
    {
        return is_null($this->getCoreResponse()->getResponseData()->getResult()[0]);
    }
}
