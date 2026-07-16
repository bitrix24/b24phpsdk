<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\HumanResources\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\HumanResources\NodeField\Result\NodeFieldResult;
use Bitrix24\SDK\Services\HumanResources\NodeField\Result\NodeFieldsResult;
use Bitrix24\SDK\Services\HumanResources\NodeField\Service\NodeField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(NodeField::class)]
class NodeFieldTest extends TestCase
{
    public function testGetBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.node.field.get', ['name' => 'name', 'select' => ['name', 'type']]);

        self::assertInstanceOf(NodeFieldResult::class, (new NodeField($core, new NullLogger()))->get('name', ['name', 'type']));
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.node.field.list', ['select' => ['name', 'type']]);

        self::assertInstanceOf(NodeFieldsResult::class, (new NodeField($core, new NullLogger()))->list(['name', 'type']));
    }

    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new NodeField($this->createStub(CoreInterface::class), new NullLogger()))->get('');
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
