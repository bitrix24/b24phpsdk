<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Legacy\Services\Task\Result;

use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

/**
 * Legacy v1 API result for task update/status-change operations.
 *
 * v1 API returns the success flag under key ['task'],
 * whereas v3 uses ['result']. This class overrides isSuccess()
 * to match the v1 response shape.
 */
class UpdatedTaskResult extends UpdatedItemResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function isSuccess(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult()['task'];
    }
}
