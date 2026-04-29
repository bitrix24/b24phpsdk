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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Notify\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\IM\Notify\Service\Notify;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Notify::class)]
final class NotifyTest extends TestCase
{
    #[Test]
    public function testSendCallsImNotify(): void
    {
        $response = $this->createStub(Response::class);
        $core = $this->createMock(CoreInterface::class);
        $notify = new Notify($core, new NullLogger());

        $core->expects($this->once())
            ->method('call')
            ->with('im.notify', [
                'USER_ID' => 42,
                'MESSAGE' => 'Hello',
                'TYPE' => 'USER',
                'MESSAGE_OUT' => null,
                'TAG' => null,
                'SUB_TAG' => null,
                'ATTACH' => null,
            ])
            ->willReturn($response);

        $notify->send(42, 'Hello');
    }

    #[Test]
    public function testGetListCallsImNotifyGet(): void
    {
        $response = $this->createStub(Response::class);
        $core = $this->createMock(CoreInterface::class);
        $notify = new Notify($core, new NullLogger());

        $core->expects($this->once())
            ->method('call')
            ->with('im.notify.get', [
                'LAST_ID' => 10,
                'LAST_TYPE' => 3,
                'LIMIT' => 20,
            ])
            ->willReturn($response);

        $notify->getList(10, 3, 20);
    }

    #[Test]
    public function testHistorySearchCallsImNotifyHistorySearch(): void
    {
        $response = $this->createStub(Response::class);
        $core = $this->createMock(CoreInterface::class);
        $notify = new Notify($core, new NullLogger());

        $from = CarbonImmutable::parse('2024-01-01T00:00:00+00:00');
        $to = CarbonImmutable::parse('2024-01-31T23:59:59+00:00');

        $core->expects($this->once())
            ->method('call')
            ->with('im.notify.history.search', [
                'SEARCH_TEXT' => 'test',
                'SEARCH_TYPES' => ['SYSTEM'],
                'SEARCH_DATE_FROM' => $from->toIso8601String(),
                'SEARCH_DATE_TO' => $to->toIso8601String(),
                'SEARCH_AUTHORS' => [1, 2],
                'LAST_ID' => 5,
                'LIMIT' => 25,
            ])
            ->willReturn($response);

        $notify->historySearch(
            searchText: 'test',
            searchTypes: ['SYSTEM'],
            searchDateFrom: $from,
            searchDateTo: $to,
            searchAuthors: [1, 2],
            lastId: 5,
            limit: 25
        );
    }

    #[Test]
    public function testMarkAllAsReadCallsImNotifyReadAll(): void
    {
        $response = $this->createStub(Response::class);
        $core = $this->createMock(CoreInterface::class);
        $notify = new Notify($core, new NullLogger());

        $core->expects($this->once())
            ->method('call')
            ->with('im.notify.read.all', [])
            ->willReturn($response);

        $notify->markAllAsRead();
    }

    #[Test]
    public function testMarkMessagesAsReadCallsImNotifyReadList(): void
    {
        $response = $this->createStub(Response::class);
        $core = $this->createMock(CoreInterface::class);
        $notify = new Notify($core, new NullLogger());

        $core->expects($this->once())
            ->method('call')
            ->with('im.notify.read.list', [
                'IDS' => [1, 2, 3],
                'ACTION' => 'Y',
            ])
            ->willReturn($response);

        $notify->markMessagesAsRead([1, 2, 3]);
    }

    #[Test]
    public function testMarkMessagesAsUnreadCallsImNotifyReadList(): void
    {
        $response = $this->createStub(Response::class);
        $core = $this->createMock(CoreInterface::class);
        $notify = new Notify($core, new NullLogger());

        $core->expects($this->once())
            ->method('call')
            ->with('im.notify.read.list', [
                'IDS' => [4, 5],
                'ACTION' => 'N',
            ])
            ->willReturn($response);

        $notify->markMessagesAsUnread([4, 5]);
    }

    #[Test]
    public function testGetSchemaCallsImNotifySchemaGet(): void
    {
        $response = $this->createStub(Response::class);
        $core = $this->createMock(CoreInterface::class);
        $notify = new Notify($core, new NullLogger());

        $core->expects($this->once())
            ->method('call')
            ->with('im.notify.schema.get', [])
            ->willReturn($response);

        $notify->getSchema();
    }
}
