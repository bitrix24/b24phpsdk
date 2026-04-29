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

namespace Bitrix24\SDK\Services\IM\Notify\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class NotifyReadAllResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function isSuccess(): bool
    {
        return (bool)($this->getCoreResponse()->getResponseData()->getResult()['result'] ?? false);
    }

    /**
     * @throws BaseException
     */
    public function newCounter(): int
    {
        return (int)($this->getCoreResponse()->getResponseData()->getResult()['newCounter'] ?? 0);
    }
}
