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

namespace Bitrix24\SDK\Services\Task\Checklistitem\Result;

use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

/**
 * Class UpdatedChecklistitemResult
 *
 * @package Bitrix24\SDK\Services\Task\Checklistitem\Result
 */
class UpdatedChecklistitemResult extends UpdatedItemResult
{
    /**
     * @throws BaseException
     */
    public function isSuccess(): bool
    {
        return is_null($this->getCoreResponse()->getResponseData()->getResult()[0]);
    }
}
