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

namespace Bitrix24\SDK\Tests\Unit\Services\Messageservice\Message\Status\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Messageservice\Message\Status\Result\MessageStatusUpdateResult;
use Bitrix24\SDK\Services\Messageservice\Message\Status\Service\MessageStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(MessageStatus::class)]
class MessageStatusTest extends TestCase
{
    #[TestDox('Test MessageStatus service can be instantiated')]
    public function testCanBeInstantiated(): void
    {
        $core = $this->createStub(CoreInterface::class);
        $messageStatus = new MessageStatus($core, new NullLogger());
        $this->assertInstanceOf(MessageStatus::class, $messageStatus);
    }

    #[TestDox('Test MessageStatus::update builds correct parameters')]
    public function testUpdateBuildsCorrectParameters(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with(
                'messageservice.message.status.update',
                [
                    'CODE' => 'provider1',
                    'MESSAGE_ID' => '65575980fa531ac284c2ee68f81ebebd',
                    'STATUS' => 'delivered',
                ]
            )
            ->willReturn($response);

        $messageStatus = new MessageStatus($core, new NullLogger());
        $messageStatusUpdateResult = $messageStatus->update(
            code: 'provider1',
            messageId: '65575980fa531ac284c2ee68f81ebebd',
            status: 'delivered'
        );

        $this->assertInstanceOf(MessageStatusUpdateResult::class, $messageStatusUpdateResult);
    }
}
