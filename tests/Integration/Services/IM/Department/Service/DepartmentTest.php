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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Department\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Department\Result\DepartmentItemResult;
use Bitrix24\SDK\Services\IM\Department\Service\Department;
use Bitrix24\SDK\Services\IM\User\Result\UserItemResult;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Department::class)]
final class DepartmentTest extends TestCase
{
    private Department $departmentService;

    #[\Override]
    protected function setUp(): void
    {
        $this->departmentService = Factory::getServiceBuilder()->getIMScope()->department();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.department.get returns a list of DepartmentItemResult')]
    public function testGet(): void
    {
        $items = $this->departmentService->get([1], true)->items();

        if ($items === []) {
            $this->markTestSkipped('No department 1 payload available for im.department.get');
        }

        $this->assertInstanceOf(DepartmentItemResult::class, $items[0]);
        $this->assertSame(1, $items[0]->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.department.colleagues.list returns a paginated list of UserItemResult')]
    public function testColleaguesList(): void
    {
        $departmentUsersResult = $this->departmentService->colleaguesList(userData: true, limit: 3);
        $users = $departmentUsersResult->users();

        $this->assertIsArray($users);
        $this->assertGreaterThanOrEqual(0, $departmentUsersResult->total());
        $this->assertTrue($departmentUsersResult->next() === null || $departmentUsersResult->next() >= 0);

        if ($users === []) {
            $this->markTestSkipped('No colleagues available for im.department.colleagues.list');
        }

        $this->assertInstanceOf(UserItemResult::class, $users[0]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.department.employees.get returns users grouped by department ID')]
    public function testEmployeesGet(): void
    {
        $usersByDepartment = $this->departmentService->employeesGet([1], true)->usersByDepartment();

        $this->assertIsArray($usersByDepartment);

        if (($usersByDepartment[1] ?? []) === []) {
            $this->markTestSkipped('No users available in department 1 for im.department.employees.get');
        }

        $this->assertInstanceOf(UserItemResult::class, $usersByDepartment[1][0]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.department.managers.get returns users grouped by department ID or an empty result')]
    public function testManagersGet(): void
    {
        $usersByDepartment = $this->departmentService->managersGet([1], true)->usersByDepartment();

        $this->assertIsArray($usersByDepartment);

        if (($usersByDepartment[1] ?? []) !== []) {
            $this->assertInstanceOf(UserItemResult::class, $usersByDepartment[1][0]);
        }
    }
}
