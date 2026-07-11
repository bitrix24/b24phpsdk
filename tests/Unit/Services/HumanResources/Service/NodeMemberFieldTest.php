<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\HumanResources\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\HumanResources\NodeMemberField\Result\NodeMemberFieldResult;
use Bitrix24\SDK\Services\HumanResources\NodeMemberField\Result\NodeMemberFieldsResult;
use Bitrix24\SDK\Services\HumanResources\NodeMemberField\Service\NodeMemberField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(NodeMemberField::class)]
class NodeMemberFieldTest extends TestCase
{
    public function testGetBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.node.member.field.get', ['name' => 'userId', 'select' => ['name', 'type']]);

        self::assertInstanceOf(NodeMemberFieldResult::class, (new NodeMemberField($core, new NullLogger()))->get('userId', ['name', 'type']));
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.node.member.field.list', ['select' => ['name', 'type']]);

        self::assertInstanceOf(NodeMemberFieldsResult::class, (new NodeMemberField($core, new NullLogger()))->list(['name', 'type']));
    }

    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new NodeMemberField($this->createStub(CoreInterface::class), new NullLogger()))->get('');
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
