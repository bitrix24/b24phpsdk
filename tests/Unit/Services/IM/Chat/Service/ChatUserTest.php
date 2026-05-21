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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Chat\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\IM\Chat\Service\ChatUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(ChatUser::class)]
class ChatUserTest extends TestCase
{
    private ChatUser $service;

    private CoreInterface&MockObject $coreMock;

    #[\Override]
    protected function setUp(): void
    {
        $this->coreMock = $this->createMock(CoreInterface::class);
        $this->service = new ChatUser($this->coreMock, new NullLogger());
    }

    #[Test]
    public function testAddHidesHistoryByDefault(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.chat.user.add', [
                'CHAT_ID' => 123,
                'USERS' => [45, 67],
                'HIDE_HISTORY' => 'Y',
            ])
            ->willReturn($response);

        self::assertInstanceOf(UpdatedItemResult::class, $this->service->add(123, [45, 67]));
    }

    #[Test]
    public function testAddCanKeepHistoryVisible(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.chat.user.add', [
                'CHAT_ID' => 123,
                'USERS' => [45, 67],
                'HIDE_HISTORY' => 'N',
            ])
            ->willReturn($response);

        self::assertInstanceOf(UpdatedItemResult::class, $this->service->add(123, [45, 67], false));
    }
}
