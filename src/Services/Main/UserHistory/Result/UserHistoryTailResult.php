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

namespace Bitrix24\SDK\Services\Main\UserHistory\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;

class UserHistoryTailResult extends UserHistoriesResult
{
    /**
     * @throws BaseException
     */
    public function hasMore(): bool
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['hasMore'];
    }

    /**
     * Keep the previous checkpoint if the returned value is null.
     * Numeric strings are preserved to avoid integer overflow.
     *
     * @return array{field: string, value: int|string|null}|null
     * @throws BaseException
     */
    public function getCursor(): ?array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['cursor'] ?? null;
    }
}
