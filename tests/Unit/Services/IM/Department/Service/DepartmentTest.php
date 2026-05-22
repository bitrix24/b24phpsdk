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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Department\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\IM\Department\Result\DepartmentsResult;
use Bitrix24\SDK\Services\IM\Department\Result\DepartmentUsersByDepartmentResult;
use Bitrix24\SDK\Services\IM\Department\Result\DepartmentUsersResult;
use Bitrix24\SDK\Services\IM\Department\Service\Department;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Department::class)]
final class DepartmentTest extends TestCase
{
    private Department $service;

    private CoreInterface&MockObject $coreMock;

    #[\Override]
    protected function setUp(): void
    {
        $this->coreMock = $this->createMock(CoreInterface::class);
        $this->service = new Department($this->coreMock, new NullLogger());
    }

    #[Test]
    public function testGetMapsDepartmentIdsAndUserDataFlag(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.department.get', [
                'ID' => [1, 5],
                'USER_DATA' => 'Y',
            ])
            ->willReturn($response);

        $departmentsResult = $this->service->get([1, 5], true);

        self::assertInstanceOf(DepartmentsResult::class, $departmentsResult);
    }

    #[Test]
    public function testColleaguesListMapsUserDataAndPaginationArguments(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.department.colleagues.list', [
                'USER_DATA' => 'Y',
                'OFFSET' => 10,
                'LIMIT' => 25,
            ])
            ->willReturn($response);

        $departmentUsersResult = $this->service->colleaguesList(true, 10, 25);

        self::assertInstanceOf(DepartmentUsersResult::class, $departmentUsersResult);
    }

    #[Test]
    public function testColleaguesListOmitsNullPaginationArguments(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.department.colleagues.list', [
                'USER_DATA' => 'N',
            ])
            ->willReturn($response);

        $departmentUsersResult = $this->service->colleaguesList();

        self::assertInstanceOf(DepartmentUsersResult::class, $departmentUsersResult);
    }

    #[Test]
    public function testEmployeesGetMapsDepartmentIdsAndUserDataFlag(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.department.employees.get', [
                'ID' => [1, 5],
                'USER_DATA' => 'Y',
            ])
            ->willReturn($response);

        $departmentUsersByDepartmentResult = $this->service->employeesGet([1, 5], true);

        self::assertInstanceOf(DepartmentUsersByDepartmentResult::class, $departmentUsersByDepartmentResult);
    }

    #[Test]
    public function testManagersGetMapsDepartmentIdsAndUserDataFlag(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.department.managers.get', [
                'ID' => [1, 5],
                'USER_DATA' => 'N',
            ])
            ->willReturn($response);

        $departmentUsersByDepartmentResult = $this->service->managersGet([1, 5]);

        self::assertInstanceOf(DepartmentUsersByDepartmentResult::class, $departmentUsersByDepartmentResult);
    }
}
