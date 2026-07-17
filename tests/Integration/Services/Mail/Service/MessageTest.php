<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Mail\Service;

use Bitrix24\SDK\Services\Mail\Service\Mailbox;
use Bitrix24\SDK\Services\Mail\Service\Message;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Message::class)]
class MessageTest extends TestCase
{
    private Mailbox $mailboxService;
    private Message $messageService;

    #[\Override]
    protected function setUp(): void
    {
        $mailScope = Factory::getServiceBuilder()->getMailScope();
        $this->mailboxService = $mailScope->mailbox();
        $this->messageService = $mailScope->message();
    }

    #[Test]
    public function testList(): void
    {
        $mailboxes = $this->mailboxService->list()->getMailboxes();
        if ($mailboxes === []) {
            $this->markTestSkipped('The integration portal has no connected mailboxes.');
        }

        $this->assertIsArray($this->messageService->list($mailboxes[0]->id)->getMessages());
    }

    #[Test]
    public function testGet(): void
    {
        $mailboxes = $this->mailboxService->list()->getMailboxes();
        if ($mailboxes === []) {
            $this->markTestSkipped('The integration portal has no connected mailboxes.');
        }

        $messages = $this->messageService->list($mailboxes[0]->id)->getMessages();
        if ($messages === []) {
            $this->markTestSkipped('The first integration mailbox has no messages.');
        }

        $message = $this->messageService->get($messages[0]->id)->message();
        $this->assertSame($messages[0]->id, $message->id);
    }
}
