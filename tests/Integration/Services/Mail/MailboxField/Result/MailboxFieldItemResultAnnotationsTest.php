<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Mail\MailboxField\Result;

use Bitrix24\SDK\Services\Mail\MailboxField\Result\MailboxFieldItemResult;
use Bitrix24\SDK\Services\Mail\MailboxField\Service\MailboxField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(MailboxFieldItemResult::class)]
class MailboxFieldItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private MailboxField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getMailScope()->mailboxField();
    }

    #[Test]
    #[TestDox('all fields in MailboxFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->service->get('id')->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), MailboxFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in MailboxFieldItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $this->service->get('id')->mailboxField(),
            MailboxFieldItemResult::class
        );
    }
}
