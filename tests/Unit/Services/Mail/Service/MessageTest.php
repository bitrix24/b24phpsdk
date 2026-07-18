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

namespace Bitrix24\SDK\Tests\Unit\Services\Mail\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Mail\Result\BooleanResult;
use Bitrix24\SDK\Services\Mail\Result\CreateCalendarEventResult;
use Bitrix24\SDK\Services\Mail\Result\CreateChatResult;
use Bitrix24\SDK\Services\Mail\Result\CreateFeedPostResult;
use Bitrix24\SDK\Services\Mail\Result\CreateTaskResult;
use Bitrix24\SDK\Services\Mail\Result\MessageResult;
use Bitrix24\SDK\Services\Mail\Result\MessagesResult;
use Bitrix24\SDK\Services\Mail\Result\MessageThreadResult;
use Bitrix24\SDK\Services\Mail\Result\MoveToFolderResult;
use Bitrix24\SDK\Services\Mail\Result\SendMessageResult;
use Bitrix24\SDK\Services\Mail\Service\Batch;
use Bitrix24\SDK\Services\Mail\Service\Message;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Message::class)]
class MessageTest extends TestCase
{
    #[Test]
    public function testCreateCalendarEventCallsV3Api(): void
    {
        $dateFrom = CarbonImmutable::create(2026, 4, 15, 10, 0, 0, 'UTC');
        $dateTo = CarbonImmutable::create(2026, 4, 15, 11, 0, 0, 'UTC');
        $core = $this->createCoreExpectingCall(
            'mail.message.createcalendarevent',
            [
                'messageId' => 15,
                'dateFrom' => '2026-04-15 10:00:00',
                'dateTo' => '2026-04-15 11:00:00',
                'name' => 'Contract meeting',
                'description' => 'Discuss contract',
            ]
        );

        $this->assertInstanceOf(
            CreateCalendarEventResult::class,
            $this->createService($core)->createCalendarEvent(15, $dateFrom, $dateTo, 'Contract meeting', 'Discuss contract')
        );
    }

    #[Test]
    public function testCreateChatCallsV3Api(): void
    {
        $this->assertInstanceOf(
            CreateChatResult::class,
            $this->createService($this->createCoreExpectingCall('mail.message.createchat', ['messageId' => 15]))
                ->createChat(15)
        );
    }

    #[Test]
    public function testCreateCrmActivityCallsV3Api(): void
    {
        $this->assertInstanceOf(
            BooleanResult::class,
            $this->createService($this->createCoreExpectingCall('mail.message.createcrmactivity', ['messageId' => 15]))
                ->createCrmActivity(15)
        );
    }

    #[Test]
    public function testCreateFeedPostCallsV3Api(): void
    {
        $this->assertInstanceOf(
            CreateFeedPostResult::class,
            $this->createService($this->createCoreExpectingCall('mail.message.createfeedpost', [
                'messageId' => 15,
                'title' => 'Discussion',
            ]))->createFeedPost(15, 'Discussion')
        );
    }

    #[Test]
    public function testCreateTaskCallsV3Api(): void
    {
        $this->assertInstanceOf(
            CreateTaskResult::class,
            $this->createService($this->createCoreExpectingCall('mail.message.createtask', [
                'messageId' => 15,
                'title' => 'Prepare contract',
                'responsibleId' => 7,
                'description' => 'From email',
            ]))->createTask(15, 'Prepare contract', 7, 'From email')
        );
    }

    #[Test]
    public function testForwardCallsV3Api(): void
    {
        $this->assertInstanceOf(
            SendMessageResult::class,
            $this->createService($this->createCoreExpectingCall('mail.message.forward', [
                'forwardMessageId' => 15,
                'from' => 'user@example.com',
                'to' => ['manager@example.com'],
                'subject' => 'Fwd: Contract',
                'body' => 'Forwarding.',
                'cc' => ['copy@example.com'],
                'bcc' => ['hidden@example.com'],
            ]))->forward(15, 'user@example.com', ['manager@example.com'], 'Fwd: Contract', 'Forwarding.', ['copy@example.com'], ['hidden@example.com'])
        );
    }

    #[Test]
    public function testGetCallsV3Api(): void
    {
        $this->assertInstanceOf(
            MessageResult::class,
            $this->createService($this->createCoreExpectingCall('mail.message.get', [
                'id' => 15,
                'select' => ['id', 'subject'],
            ]))->get(15, ['id', 'subject'])
        );
    }

    #[Test]
    public function testListCallsV3Api(): void
    {
        $dateFrom = CarbonImmutable::create(2026, 1, 1, 0, 0, 0, 'UTC');
        $dateTo = CarbonImmutable::create(2026, 1, 31, 23, 59, 59, 'UTC');

        $this->assertInstanceOf(
            MessagesResult::class,
            $this->createService($this->createCoreExpectingCall('mail.message.list', [
                'mailboxId' => 1,
                'searchQuery' => 'contract',
                'dateFrom' => $dateFrom->format(CarbonImmutable::ATOM),
                'dateTo' => $dateTo->format(CarbonImmutable::ATOM),
                'isSeen' => true,
                'hasAttachments' => false,
                'folder' => 'Inbox',
                'pagination' => ['page' => 1, 'limit' => 20],
            ]))->list(1, 'contract', $dateFrom, $dateTo, true, false, 'Inbox', ['page' => 1, 'limit' => 20])
        );
    }

    #[Test]
    public function testMoveToFolderCallsV3Api(): void
    {
        $this->assertInstanceOf(
            MoveToFolderResult::class,
            $this->createService($this->createCoreExpectingCall('mail.message.movetofolder', [
                'messageIds' => [15, 16],
                'action' => 'move',
                'folder' => 'Archive',
            ]))->moveToFolder([15, 16], 'move', 'Archive')
        );
    }

    #[Test]
    public function testRemoveCrmActivityCallsV3Api(): void
    {
        $this->assertInstanceOf(
            BooleanResult::class,
            $this->createService($this->createCoreExpectingCall('mail.message.removecrmactivity', ['messageId' => 15]))
                ->removeCrmActivity(15)
        );
    }

    #[Test]
    public function testReplyCallsV3Api(): void
    {
        $this->assertInstanceOf(
            SendMessageResult::class,
            $this->createService($this->createCoreExpectingCall('mail.message.reply', [
                'replyToMessageId' => 15,
                'from' => 'user@example.com',
                'to' => ['client@example.com'],
                'subject' => 'Re: Contract',
                'body' => 'Received.',
            ]))->reply(15, 'user@example.com', ['client@example.com'], 'Re: Contract', 'Received.')
        );
    }

    #[Test]
    public function testSendCallsV3Api(): void
    {
        $this->assertInstanceOf(
            SendMessageResult::class,
            $this->createService($this->createCoreExpectingCall('mail.message.send', [
                'from' => 'user@example.com',
                'to' => ['client@example.com'],
                'subject' => 'Contract',
                'body' => 'Hello.',
            ]))->send('user@example.com', ['client@example.com'], 'Contract', 'Hello.')
        );
    }

    #[Test]
    public function testThreadCallsV3Api(): void
    {
        $this->assertInstanceOf(
            MessageThreadResult::class,
            $this->createService($this->createCoreExpectingCall('mail.message.thread', [
                'id' => 15,
                'limit' => 20,
            ]))->thread(15, 20)
        );
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function createCoreExpectingCall(string $method, array $parameters): CoreInterface
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with($method, $parameters, ApiVersion::v3)
            ->willReturn($response);

        return $core;
    }

    private function createService(CoreInterface $core): Message
    {
        return new Message(new Batch(new NullBatch(), new NullLogger()), $core, new NullLogger());
    }
}
