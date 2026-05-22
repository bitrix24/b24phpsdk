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

class DepartmentUsersByDepartmentResult extends AbstractResult
{
    /**
     * @return array<int, int[]>
     * @throws BaseException
     */
    public function userIdsByDepartment(): array
    {
        $usersByDepartment = [];

        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $departmentId => $items) {
            if (!is_array($items)) {
                continue;
            }

            $usersByDepartment[(int)$departmentId] = array_values(array_filter($items, 'is_int'));
        }

        return $usersByDepartment;
    }

    /**
     * @return array<int, UserItemResult[]>
     * @throws BaseException
     */
    public function usersByDepartment(): array
    {
        $usersByDepartment = [];

        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $departmentId => $items) {
            if (!is_array($items)) {
                continue;
            }

            $usersByDepartment[(int)$departmentId] = array_values(array_map(
                static fn(array $user): UserItemResult => new UserItemResult($user),
                array_filter($items, 'is_array')
            ));
        }

        return $usersByDepartment;
    }
}
