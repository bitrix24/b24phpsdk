<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\HumanResources\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\HumanResources\Result\NodeMemberOperationResult;
use Bitrix24\SDK\Services\HumanResources\Result\NodeMemberRemoveResult;
use Bitrix24\SDK\Services\HumanResources\Service\NodeMember;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(NodeMember::class)]
class NodeMemberTest extends TestCase
{
    public function testAddBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.node.member.add', [
            'nodeId' => 15,
            'userIds' => [7, 12],
            'role' => 'MEMBER_EMPLOYEE',
        ]);

        self::assertInstanceOf(
            NodeMemberOperationResult::class,
            (new NodeMember($core, new NullLogger()))->add(15, [7, 12], 'MEMBER_EMPLOYEE')
        );
    }

    public function testMoveBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.node.member.move', [
            'nodeId' => 28,
            'userIds' => [12, 18],
            'role' => 'MEMBER_EMPLOYEE',
        ]);

        self::assertInstanceOf(
            NodeMemberOperationResult::class,
            (new NodeMember($core, new NullLogger()))->move(28, [12, 18], 'MEMBER_EMPLOYEE')
        );
    }

    public function testRemoveBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.node.member.remove', [
            'nodeId' => 15,
            'userIds' => [18, 25],
        ]);

        self::assertInstanceOf(NodeMemberRemoveResult::class, (new NodeMember($core, new NullLogger()))->remove(15, [18, 25]));
    }

    public function testSetBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.node.member.set', [
            'nodeId' => 15,
            'userIds' => [
                'MEMBER_HEAD' => [7],
                'MEMBER_EMPLOYEE' => [18, 25],
            ],
        ]);

        self::assertInstanceOf(
            NodeMemberOperationResult::class,
            (new NodeMember($core, new NullLogger()))->set(15, [
                'MEMBER_HEAD' => [7],
                'MEMBER_EMPLOYEE' => [18, 25],
            ])
        );
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
