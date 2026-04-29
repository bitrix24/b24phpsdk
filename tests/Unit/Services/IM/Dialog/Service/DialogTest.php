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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Dialog\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogActionResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogMessageSearchResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogMessagesResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogReadResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogUsersResult;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Dialog::class)]
final class DialogTest extends TestCase
{
    private Dialog $service;

    private CoreInterface&MockObject $coreMock;

    #[\Override]
    protected function setUp(): void
    {
        $this->coreMock = $this->createMock(CoreInterface::class);
        $this->service = new Dialog($this->coreMock, new NullLogger());
    }

    #[Test]
    public function testGetCallsCorrectApiMethod(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.dialog.get', ['DIALOG_ID' => 'chat15'])
            ->willReturn($response);

        $dialogResult = $this->service->get('chat15');

        self::assertInstanceOf(DialogResult::class, $dialogResult);
    }

    #[Test]
    public function testMessagesGetMapsOptionalPaginationArguments(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.dialog.messages.get', [
                'DIALOG_ID' => 'chat15',
                'LAST_ID' => 40,
                'FIRST_ID' => 10,
                'LIMIT' => 25,
            ])
            ->willReturn($response);

        $dialogMessagesResult = $this->service->messagesGet('chat15', 40, 10, 25);

        self::assertInstanceOf(DialogMessagesResult::class, $dialogMessagesResult);
    }

    #[Test]
    public function testMessagesSearchMapsSearchFiltersAndOrder(): void
    {
        $response = $this->createStub(Response::class);
        $dateFrom = CarbonImmutable::parse('2026-04-01T00:00:00+00:00');
        $dateTo = CarbonImmutable::parse('2026-04-30T23:59:59+00:00');
        $date = CarbonImmutable::parse('2026-04-22T12:00:00+00:00');

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.dialog.messages.search', [
                'CHAT_ID' => 15,
                'SEARCH_MESSAGE' => 'invoice',
                'DATE_FROM' => '2026-04-01T00:00:00+00:00',
                'DATE_TO' => '2026-04-30T23:59:59+00:00',
                'DATE' => '2026-04-22T12:00:00+00:00',
                'ORDER' => ['ID' => 'ASC'],
                'LIMIT' => 100,
                'LAST_ID' => 77,
            ])
            ->willReturn($response);

        $dialogMessageSearchResult = $this->service->messagesSearch(
            15,
            'invoice',
            $dateFrom,
            $dateTo,
            $date,
            ['ID' => 'ASC'],
            100,
            77,
        );

        self::assertInstanceOf(DialogMessageSearchResult::class, $dialogMessageSearchResult);
    }

    #[Test]
    public function testReadMapsOptionalMessageId(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.dialog.read', [
                'DIALOG_ID' => 'chat15',
                'MESSAGE_ID' => 99,
            ])
            ->willReturn($response);

        $dialogReadResult = $this->service->read('chat15', 99);

        self::assertInstanceOf(DialogReadResult::class, $dialogReadResult);
    }

    #[Test]
    public function testReadAllCallsCorrectApiMethod(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.dialog.read.all', [])
            ->willReturn($response);

        $dialogActionResult = $this->service->readAll();

        self::assertInstanceOf(DialogActionResult::class, $dialogActionResult);
    }

    #[Test]
    public function testUnreadCallsCorrectApiMethod(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.dialog.unread', [
                'DIALOG_ID' => 'chat15',
                'MESSAGE_ID' => 99,
            ])
            ->willReturn($response);

        $dialogActionResult = $this->service->unread('chat15', 99);

        self::assertInstanceOf(DialogActionResult::class, $dialogActionResult);
    }

    #[Test]
    public function testUsersListMapsSkipExternalAndPagination(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.dialog.users.list', [
                'DIALOG_ID' => 'chat15',
                'SKIP_EXTERNAL' => 'Y',
                'SKIP_EXTERNAL_EXCEPT_TYPES' => 'bot,email',
                'LIMIT' => 50,
                'LAST_ID' => 41,
                'OFFSET' => 10,
            ])
            ->willReturn($response);

        $dialogUsersResult = $this->service->usersList('chat15', true, 'bot,email', 50, 41, 10);

        self::assertInstanceOf(DialogUsersResult::class, $dialogUsersResult);
    }

    #[Test]
    public function testWritingCallsCorrectApiMethod(): void
    {
        $response = $this->createStub(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('im.dialog.writing', ['DIALOG_ID' => 'chat15'])
            ->willReturn($response);

        $dialogActionResult = $this->service->writing('chat15');

        self::assertInstanceOf(DialogActionResult::class, $dialogActionResult);
    }
}
