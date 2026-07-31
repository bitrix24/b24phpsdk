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

namespace Bitrix24\SDK\Services\HumanResources\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\HumanResources\Result\EmployeeCountResult;
use Bitrix24\SDK\Services\HumanResources\Result\EmployeeMultidepartmentResult;
use Bitrix24\SDK\Services\HumanResources\Result\EmployeesResult;
use Bitrix24\SDK\Services\HumanResources\Result\EmployeeSubordinatesResult;

#[ApiServiceMetadata(new Scope(['humanresources']))]
class Employee extends AbstractService
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.employee.count',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/employee/humanresources-employee-count.html',
        'Get company employee count',
        ApiVersion::v3
    )]
    public function count(): EmployeeCountResult
    {
        return new EmployeeCountResult(
            $this->core->call('humanresources.employee.count', [], ApiVersion::v3)
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.employee.multidepartment',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/employee/humanresources-employee-multidepartment.html',
        'Get employees assigned to several departments',
        ApiVersion::v3
    )]
    public function multidepartment(): EmployeeMultidepartmentResult
    {
        return new EmployeeMultidepartmentResult(
            $this->core->call('humanresources.employee.multidepartment', [], ApiVersion::v3)
        );
    }

    /**
     * @param string[] $select
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.employee.search',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/employee/humanresources-employee-search.html',
        'Search employees by name',
        ApiVersion::v3
    )]
    public function search(string $name, ?int $nodeId = null, array $select = []): EmployeesResult
    {
        $this->guardNonEmptyString($name, 'employee name must not be empty');

        $params = ['name' => $name];
        if ($nodeId !== null) {
            $params['nodeId'] = $nodeId;
        }

        if ($select !== []) {
            $params['select'] = $select;
        }

        return new EmployeesResult(
            $this->core->call('humanresources.employee.search', $params, ApiVersion::v3)
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.employee.subordinates',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/employee/humanresources-employee-subordinates.html',
        'Get employee subordinates',
        ApiVersion::v3
    )]
    public function subordinates(int $id): EmployeeSubordinatesResult
    {
        $this->guardPositiveId($id);

        return new EmployeeSubordinatesResult(
            $this->core->call('humanresources.employee.subordinates', ['id' => $id], ApiVersion::v3)
        );
    }
}
