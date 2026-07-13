<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\HumanResources\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\HumanResources\Result\EmployeeCountResult;
use Bitrix24\SDK\Services\HumanResources\Result\EmployeeMultidepartmentResult;
use Bitrix24\SDK\Services\HumanResources\Result\EmployeesResult;
use Bitrix24\SDK\Services\HumanResources\Result\EmployeeSubordinatesResult;
use Bitrix24\SDK\Services\HumanResources\Service\Employee;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Employee::class)]
class EmployeeTest extends TestCase
{
    public function testCountCallsV3Method(): void
    {
        $core = $this->mockCore('humanresources.employee.count', [], ApiVersion::v3);

        self::assertInstanceOf(EmployeeCountResult::class, (new Employee($core, new NullLogger()))->count());
    }

    public function testMultidepartmentCallsV3Method(): void
    {
        $core = $this->mockCore('humanresources.employee.multidepartment', [], ApiVersion::v3);

        self::assertInstanceOf(
            EmployeeMultidepartmentResult::class,
            (new Employee($core, new NullLogger()))->multidepartment()
        );
    }

    public function testSearchBuildsRequiredAndOptionalParameters(): void
    {
        $core = $this->mockCore(
            'humanresources.employee.search',
            [
                'name' => 'Ivan',
                'nodeId' => 42,
                'select' => ['userId', 'name'],
            ],
            ApiVersion::v3
        );

        self::assertInstanceOf(
            EmployeesResult::class,
            (new Employee($core, new NullLogger()))->search('Ivan', 42, ['userId', 'name'])
        );
    }

    public function testSubordinatesCallsV3Method(): void
    {
        $core = $this->mockCore('humanresources.employee.subordinates', ['id' => 7], ApiVersion::v3);

        self::assertInstanceOf(
            EmployeeSubordinatesResult::class,
            (new Employee($core, new NullLogger()))->subordinates(7)
        );
    }

    private function mockCore(string $method, array $parameters, ApiVersion $apiVersion): CoreInterface
    {
        $response = $this->createStub(Response::class);
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())
            ->method('call')
            ->with($method, $parameters, $apiVersion)
            ->willReturn($response);

        return $core;
    }
}
