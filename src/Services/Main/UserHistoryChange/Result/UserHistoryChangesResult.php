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

namespace Bitrix24\SDK\Services\Main\UserHistoryChange\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class UserHistoryChangesResult extends AbstractResult
{
    /**
     * @return UserHistoryChangeItemResult[]
     * @throws BaseException
     */
    public function getUserHistoryChanges(): array
    {
        return array_map(
            static fn (array $item): UserHistoryChangeItemResult => new UserHistoryChangeItemResult($item),
            $this->getCoreResponse()->getResponseData()->getResult()['items']
        );
    }
}
