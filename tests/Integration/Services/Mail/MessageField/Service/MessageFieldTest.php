<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Mail\MessageField\Service;

use Bitrix24\SDK\Services\Mail\MessageField\Service\MessageField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageField::class)]
class MessageFieldTest extends TestCase
{
    private MessageField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getMailScope()->messageField();
    }

    #[Test]
    public function testList(): void
    {
        $fields = $this->service->list()->getMessageFields();
        $this->assertNotEmpty($fields);
    }

    #[Test]
    public function testGet(): void
    {
        $field = $this->service->get('id')->messageField();
        $this->assertSame('id', $field->name);
        $this->assertNotEmpty($field->type);
    }
}
