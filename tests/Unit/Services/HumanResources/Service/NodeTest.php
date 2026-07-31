<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\HumanResources\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\HumanResources\Result\NodeCountResult;
use Bitrix24\SDK\Services\HumanResources\Result\NodeResult;
use Bitrix24\SDK\Services\HumanResources\Result\NodesResult;
use Bitrix24\SDK\Services\HumanResources\Service\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Node::class)]
class NodeTest extends TestCase
{
    public function testAddBuildsParameters(): void
    {
        $core = $this->mockCore(
            'humanresources.node.add',
            [
                'type' => 'DEPARTMENT',
                'name' => 'Marketing',
                'parentId' => 1,
                'description' => 'Handles promotion',
            ]
        );

        self::assertInstanceOf(
            NodeResult::class,
            (new Node($core, new NullLogger()))->add('DEPARTMENT', 'Marketing', 1, [
                'description' => 'Handles promotion',
            ])
        );
    }

    public function testChildrenBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.node.children', ['id' => 1, 'select' => ['id', 'name']]);

        self::assertInstanceOf(NodesResult::class, (new Node($core, new NullLogger()))->children(1, ['id', 'name']));
    }

    public function testCountCallsV3Method(): void
    {
        $core = $this->mockCore('humanresources.node.count', []);

        self::assertInstanceOf(NodeCountResult::class, (new Node($core, new NullLogger()))->count());
    }

    public function testEditBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.node.edit', ['id' => 44, 'name' => 'B2B Sales']);

        self::assertInstanceOf(NodeResult::class, (new Node($core, new NullLogger()))->edit(44, ['name' => 'B2B Sales']));
    }

    public function testGetBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.node.get', ['id' => 1, 'select' => ['id', 'name']]);

        self::assertInstanceOf(NodeResult::class, (new Node($core, new NullLogger()))->get(1, ['id', 'name']));
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore(
            'humanresources.node.list',
            [
                'type' => 'DEPARTMENT',
                'select' => ['id', 'name'],
                'pagination' => ['limit' => 20],
            ]
        );

        self::assertInstanceOf(
            NodesResult::class,
            (new Node($core, new NullLogger()))->list('DEPARTMENT', ['id', 'name'], ['limit' => 20])
        );
    }

    public function testMoveBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.node.move', ['id' => 44, 'parentId' => 2]);

        self::assertInstanceOf(NodeResult::class, (new Node($core, new NullLogger()))->move(44, 2));
    }

    public function testSearchBuildsParameters(): void
    {
        $core = $this->mockCore(
            'humanresources.node.search',
            [
                'type' => 'DEPARTMENT',
                'name' => 'Sales',
                'parentId' => 1,
                'pagination' => ['limit' => 20],
            ]
        );

        self::assertInstanceOf(
            NodesResult::class,
            (new Node($core, new NullLogger()))->search('DEPARTMENT', 'Sales', 1, ['limit' => 20])
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
