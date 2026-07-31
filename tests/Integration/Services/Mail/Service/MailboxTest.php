<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Mail\Service;

use Bitrix24\SDK\Services\Mail\Service\Mailbox;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Mailbox::class)]
class MailboxTest extends TestCase
{
    private Mailbox $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getMailScope()->mailbox();
    }

    #[Test]
    public function testList(): void
    {
        $this->assertIsArray($this->service->list()->getMailboxes());
    }

    #[Test]
    public function testSenders(): void
    {
        $this->assertIsArray($this->service->senders()->getSenders());
    }

    #[Test]
    public function testGet(): void
    {
        $mailboxes = $this->service->list()->getMailboxes();
        if ($mailboxes === []) {
            $this->markTestSkipped('The integration portal has no connected mailboxes.');
        }

        $mailbox = $this->service->get($mailboxes[0]->id)->mailbox();
        $this->assertSame($mailboxes[0]->id, $mailbox->id);
    }
}
