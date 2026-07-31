<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Mail\Result;

use Bitrix24\SDK\Services\Mail\Result\MailboxItemResult;
use Bitrix24\SDK\Services\Mail\Service\Mailbox;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(MailboxItemResult::class)]
class MailboxItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Mailbox $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getMailScope()->mailbox();
    }

    #[Test]
    #[TestDox('all fields in MailboxItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItems = $this->service->list()->getCoreResponse()->getResponseData()->getResult()['items'] ?? [];
        if ($rawItems === []) {
            $this->markTestSkipped('The integration portal has no connected mailboxes.');
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItems[0]), MailboxItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in MailboxItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $mailboxes = $this->service->list()->getMailboxes();
        if ($mailboxes === []) {
            $this->markTestSkipped('The integration portal has no connected mailboxes.');
        }

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($mailboxes[0], MailboxItemResult::class);
    }
}
