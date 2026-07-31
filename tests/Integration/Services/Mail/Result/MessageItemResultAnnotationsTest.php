<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Mail\Result;

use Bitrix24\SDK\Services\Mail\Result\MessageItemResult;
use Bitrix24\SDK\Services\Mail\Service\Mailbox;
use Bitrix24\SDK\Services\Mail\Service\Message;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageItemResult::class)]
class MessageItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

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
    #[TestDox('all fields in MessageItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->firstRawMessage();
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), MessageItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in MessageItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $mailboxes = $this->mailboxService->list()->getMailboxes();
        if ($mailboxes === []) {
            $this->markTestSkipped('The integration portal has no connected mailboxes.');
        }
        $messages = $this->messageService->list($mailboxes[0]->id)->getMessages();
        if ($messages === []) {
            $this->markTestSkipped('The first integration mailbox has no messages.');
        }

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($messages[0], MessageItemResult::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function firstRawMessage(): array
    {
        $mailboxes = $this->mailboxService->list()->getMailboxes();
        if ($mailboxes === []) {
            $this->markTestSkipped('The integration portal has no connected mailboxes.');
        }
        $rawItems = $this->messageService->list($mailboxes[0]->id)->getCoreResponse()->getResponseData()->getResult()['items'] ?? [];
        if ($rawItems === []) {
            $this->markTestSkipped('The first integration mailbox has no messages.');
        }

        return $rawItems[0];
    }
}
