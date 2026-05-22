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

namespace Bitrix24\SDK\Services\IM\Department\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;
use Bitrix24\SDK\Services\IM\User\Result\UserItemResult;

class DepartmentUsersResult extends AbstractResult
{
    /**
     * @return int[]
     * @throws BaseException
     */
    public function userIds(): array
    {
        return array_values(array_filter(
            $this->getCoreResponse()->getResponseData()->getResult(),
            'is_int'
        ));
    }

    /**
     * @return UserItemResult[]
     * @throws BaseException
     */
    public function users(): array
    {
        return array_values(array_map(
            static fn(array $user): UserItemResult => new UserItemResult($user),
            array_filter($this->getCoreResponse()->getResponseData()->getResult(), 'is_array')
        ));
    }

    /**
     * @throws BaseException
     */
    public function total(): int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getTotal() ?? 0;
    }

    /**
     * @throws BaseException
     */
    public function next(): ?int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getNextItem();
    }
}
