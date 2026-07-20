<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Mail\Result;

use Bitrix24\SDK\Services\Mail\Result\RecipientItemResult;
use Bitrix24\SDK\Services\Mail\Service\Recipient;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecipientItemResult::class)]
class RecipientItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Recipient $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getMailScope()->recipient();
    }

    #[Test]
    #[TestDox('all fields in RecipientItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItems = $this->service->listEmployees('a')->getCoreResponse()->getResponseData()->getResult()['items'] ?? [];
        if ($rawItems === []) {
            $this->markTestSkipped('The integration portal returned no mail recipient employees for query `a`.');
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItems[0]), RecipientItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in RecipientItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $recipients = $this->service->listEmployees('a')->getRecipients();
        if ($recipients === []) {
            $this->markTestSkipped('The integration portal returned no mail recipient employees for query `a`.');
        }

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($recipients[0], RecipientItemResult::class);
    }
}
