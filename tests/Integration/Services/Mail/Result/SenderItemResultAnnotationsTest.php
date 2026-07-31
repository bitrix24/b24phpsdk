<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Mail\Result;

use Bitrix24\SDK\Services\Mail\Result\SenderItemResult;
use Bitrix24\SDK\Services\Mail\Service\Mailbox;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(SenderItemResult::class)]
class SenderItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Mailbox $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getMailScope()->mailbox();
    }

    #[Test]
    #[TestDox('all fields in SenderItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItems = $this->service->senders()->getCoreResponse()->getResponseData()->getResult()['items'] ?? [];
        if ($rawItems === []) {
            $this->markTestSkipped('The integration portal has no available mail senders.');
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItems[0]), SenderItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in SenderItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $senders = $this->service->senders()->getSenders();
        if ($senders === []) {
            $this->markTestSkipped('The integration portal has no available mail senders.');
        }

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($senders[0], SenderItemResult::class);
    }
}
