<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\HumanResources\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\HumanResources\Result\NodeCommunicationEditResult;
use Bitrix24\SDK\Services\HumanResources\Result\NodeCommunicationResult;
use Bitrix24\SDK\Services\HumanResources\Service\NodeCommunication;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(NodeCommunication::class)]
class NodeCommunicationTest extends TestCase
{
    public function testEditBuildsParameters(): void
    {
        $core = $this->mockCore(
            'humanresources.node.communication.edit',
            [
                'nodeId' => 15,
                'communicationType' => 'CHAT',
                'ids' => [21],
                'removeIds' => [18],
            ]
        );

        self::assertInstanceOf(
            NodeCommunicationEditResult::class,
            (new NodeCommunication($core, new NullLogger()))->edit(15, 'CHAT', [
                'ids' => [21],
                'removeIds' => [18],
            ])
        );
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('humanresources.node.communication.list', ['id' => 15]);

        self::assertInstanceOf(NodeCommunicationResult::class, (new NodeCommunication($core, new NullLogger()))->list(15));
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
