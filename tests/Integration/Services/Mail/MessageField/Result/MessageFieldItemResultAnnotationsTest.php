<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Mail\MessageField\Result;

use Bitrix24\SDK\Services\Mail\MessageField\Result\MessageFieldItemResult;
use Bitrix24\SDK\Services\Mail\MessageField\Service\MessageField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageFieldItemResult::class)]
class MessageFieldItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private MessageField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getMailScope()->messageField();
    }

    #[Test]
    #[TestDox('all fields in MessageFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->service->get('id')->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), MessageFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in MessageFieldItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $this->service->get('id')->messageField(),
            MessageFieldItemResult::class
        );
    }
}
