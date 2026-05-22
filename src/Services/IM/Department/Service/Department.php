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

namespace Bitrix24\SDK\Services\IM\Department\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Department\Result\DepartmentsResult;
use Bitrix24\SDK\Services\IM\Department\Result\DepartmentUsersByDepartmentResult;
use Bitrix24\SDK\Services\IM\Department\Result\DepartmentUsersResult;

#[ApiServiceMetadata(new Scope(['im']))]
class Department extends AbstractService
{
    /**
     * @param int[] $departmentIds
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.department.get',
        'https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-get.html',
        'Get IM department data by IDs'
    )]
    public function get(array $departmentIds, bool $userData = false): DepartmentsResult
    {
        return new DepartmentsResult($this->core->call('im.department.get', [
            'ID' => $departmentIds,
            'USER_DATA' => $userData ? 'Y' : 'N',
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.department.colleagues.list',
        'https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-colleagues-list.html',
        'List colleagues of the current user'
    )]
    public function colleaguesList(
        bool $userData = false,
        ?int $offset = null,
        ?int $limit = null,
    ): DepartmentUsersResult {
        $payload = [
            'USER_DATA' => $userData ? 'Y' : 'N',
            'OFFSET' => $offset,
            'LIMIT' => $limit,
        ];

        return new DepartmentUsersResult($this->core->call('im.department.colleagues.list', array_filter(
            $payload,
            static fn(mixed $value): bool => $value !== null
        )));
    }

    /**
     * @param int[] $departmentIds
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.department.employees.get',
        'https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-employees-get.html',
        'Get employees for IM departments'
    )]
    public function employeesGet(array $departmentIds, bool $userData = false): DepartmentUsersByDepartmentResult
    {
        return new DepartmentUsersByDepartmentResult($this->core->call('im.department.employees.get', [
            'ID' => $departmentIds,
            'USER_DATA' => $userData ? 'Y' : 'N',
        ]));
    }

    /**
     * @param int[] $departmentIds
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.department.managers.get',
        'https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-managers-get.html',
        'Get managers for IM departments'
    )]
    public function managersGet(array $departmentIds, bool $userData = false): DepartmentUsersByDepartmentResult
    {
        return new DepartmentUsersByDepartmentResult($this->core->call('im.department.managers.get', [
            'ID' => $departmentIds,
            'USER_DATA' => $userData ? 'Y' : 'N',
        ]));
    }
}
