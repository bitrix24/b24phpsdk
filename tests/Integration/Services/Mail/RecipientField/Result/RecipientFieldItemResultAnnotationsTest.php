<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Mail\RecipientField\Result;

use Bitrix24\SDK\Services\Mail\RecipientField\Result\RecipientFieldItemResult;
use Bitrix24\SDK\Services\Mail\RecipientField\Service\RecipientField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecipientFieldItemResult::class)]
class RecipientFieldItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private RecipientField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getMailScope()->recipientField();
    }

    #[Test]
    #[TestDox('all fields in RecipientFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->service->get('email')->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), RecipientFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in RecipientFieldItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $this->service->get('email')->recipientField(),
            RecipientFieldItemResult::class
        );
    }
}
