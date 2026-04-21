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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Message\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\IM\Message\Attach\RawAttach;
use Bitrix24\SDK\Services\IM\Message\Service\Message;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Message::class)]
final class MessageTest extends TestCase
{
    #[Test]
    public function addBuildsRawAttachPayloadObjectBeforeCoreCall(): void
    {
        $payload = [
            ['MESSAGE' => 'Raw message'],
            ['VENDOR_BLOCK' => ['FLAG' => 'Y']],
        ];
        $response = $this->createStub(Response::class);
        $coreMock = $this->createMock(CoreInterface::class);
        $message = new Message($coreMock, new NullLogger());

        $coreMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'im.message.add',
                [
                    'DIALOG_ID' => 'chat123',
                    'MESSAGE' => 'Payload with raw attach',
                    'ATTACH' => $payload,
                    'KEYBOARD' => null,
                    'MENU' => null,
                    'SYSTEM' => 'N',
                    'URL_PREVIEW' => 'Y',
                    'REPLY_ID' => null,
                ]
            )
            ->willReturn($response);

        self::assertInstanceOf(
            \Bitrix24\SDK\Core\Result\AddedItemResult::class,
            $message->add(
                dialogId: 'chat123',
                message: 'Payload with raw attach',
                attach: RawAttach::fromArray($payload),
            )
        );
    }
}
