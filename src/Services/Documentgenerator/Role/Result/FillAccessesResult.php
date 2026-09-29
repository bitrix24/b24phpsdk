<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Documentgenerator\Role\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class FillAccessesResult
 *
 * Result of documentgenerator.role.fillaccesses.
 * The API returns null on success; isSuccess() returns true when no exception was thrown.
 *
 * @package Bitrix24\SDK\Services\Documentgenerator\Role\Result
 */
class FillAccessesResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function isSuccess(): bool
    {
        // API returns null on success; array cast of [null] is truthy
        return (bool)$this->getCoreResponse()->getResponseData()->getResult();
    }
}
