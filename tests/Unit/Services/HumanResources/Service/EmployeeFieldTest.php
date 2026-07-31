<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\HumanResources\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\HumanResources\EmployeeField\Result\EmployeeFieldResult;
use Bitrix24\SDK\Services\HumanResources\EmployeeField\Result\EmployeeFieldsResult;
use Bitrix24\SDK\Services\HumanResources\EmployeeField\Service\EmployeeField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(EmployeeField::class)]
class EmployeeFieldTest extends TestCase
{
    public function testGetBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.employee.field.get', ['name' => 'userId', 'select' => ['name', 'type']]);

        self::assertInstanceOf(EmployeeFieldResult::class, (new EmployeeField($core, new NullLogger()))->get('userId', ['name', 'type']));
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.employee.field.list', ['select' => ['name', 'type']]);

        self::assertInstanceOf(EmployeeFieldsResult::class, (new EmployeeField($core, new NullLogger()))->list(['name', 'type']));
    }

    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new EmployeeField($this->createStub(CoreInterface::class), new NullLogger()))->get('');
    }

    private function mockCore(string $method, array $parameters): CoreInterface
    {
        $response = $this->createStub(Response::class);
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())
            ->method('call')
            ->with($method, $parameters, ApiVersion::v3)
            ->willReturn($response);

        return $core;
    }
}
