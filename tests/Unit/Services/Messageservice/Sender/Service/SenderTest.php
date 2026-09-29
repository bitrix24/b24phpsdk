<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Messageservice\Sender\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Messageservice\Sender\Result\SenderAddResult;
use Bitrix24\SDK\Services\Messageservice\Sender\Result\SenderDeleteResult;
use Bitrix24\SDK\Services\Messageservice\Sender\Result\SenderUpdateResult;
use Bitrix24\SDK\Services\Messageservice\Sender\Result\SendersListResult;
use Bitrix24\SDK\Services\Messageservice\Sender\Service\Sender;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Sender::class)]
class SenderTest extends TestCase
{
    #[TestDox('Test Sender service can be instantiated')]
    public function testCanBeInstantiated(): void
    {
        $core = $this->createStub(CoreInterface::class);
        $sender = new Sender($core, new NullLogger());
        $this->assertInstanceOf(Sender::class, $sender);
    }

    #[TestDox('Test Sender::add builds correct parameters')]
    public function testAddBuildsCorrectParameters(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with(
                'messageservice.sender.add',
                [
                    'CODE' => 'provider1',
                    'TYPE' => 'SMS',
                    'HANDLER' => 'https://provider.example/api/handler',
                    'NAME' => 'Provider 1',
                    'DESCRIPTION' => 'Main SMS provider',
                ]
            )
            ->willReturn($response);

        $sender = new Sender($core, new NullLogger());
        $senderAddResult = $sender->add(
            code: 'provider1',
            type: 'SMS',
            handler: 'https://provider.example/api/handler',
            name: 'Provider 1',
            description: 'Main SMS provider'
        );

        $this->assertInstanceOf(SenderAddResult::class, $senderAddResult);
    }

    #[TestDox('Test Sender::add without optional description')]
    public function testAddWithoutDescription(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with(
                'messageservice.sender.add',
                [
                    'CODE' => 'provider1',
                    'TYPE' => 'SMS',
                    'HANDLER' => 'https://provider.example/api/handler',
                    'NAME' => 'Provider 1',
                ]
            )
            ->willReturn($response);

        $sender = new Sender($core, new NullLogger());
        $senderAddResult = $sender->add(
            code: 'provider1',
            type: 'SMS',
            handler: 'https://provider.example/api/handler',
            name: 'Provider 1'
        );

        $this->assertInstanceOf(SenderAddResult::class, $senderAddResult);
    }

    #[TestDox('Test Sender::update builds correct parameters')]
    public function testUpdateBuildsCorrectParameters(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with(
                'messageservice.sender.update',
                [
                    'CODE' => 'provider1',
                    'HANDLER' => 'https://provider.example/api/new-handler',
                    'NAME' => 'Provider 1 Updated',
                ]
            )
            ->willReturn($response);

        $sender = new Sender($core, new NullLogger());
        $senderUpdateResult = $sender->update(
            code: 'provider1',
            handler: 'https://provider.example/api/new-handler',
            name: 'Provider 1 Updated'
        );

        $this->assertInstanceOf(SenderUpdateResult::class, $senderUpdateResult);
    }

    #[TestDox('Test Sender::list calls correct API method')]
    public function testListCallsCorrectMethod(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('messageservice.sender.list', [])
            ->willReturn($response);

        $sender = new Sender($core, new NullLogger());
        $sendersListResult = $sender->list();

        $this->assertInstanceOf(SendersListResult::class, $sendersListResult);
    }

    #[TestDox('Test Sender::delete builds correct parameters')]
    public function testDeleteBuildsCorrectParameters(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('messageservice.sender.delete', ['CODE' => 'provider1'])
            ->willReturn($response);

        $sender = new Sender($core, new NullLogger());
        $senderDeleteResult = $sender->delete('provider1');

        $this->assertInstanceOf(SenderDeleteResult::class, $senderDeleteResult);
    }
}
