<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Mail\RecipientField\Service;

use Bitrix24\SDK\Services\Mail\RecipientField\Service\RecipientField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecipientField::class)]
class RecipientFieldTest extends TestCase
{
    private RecipientField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getMailScope()->recipientField();
    }

    #[Test]
    public function testList(): void
    {
        $fields = $this->service->list()->getRecipientFields();
        $this->assertNotEmpty($fields);
    }

    #[Test]
    public function testGet(): void
    {
        $field = $this->service->get('email')->recipientField();
        $this->assertSame('email', $field->name);
        $this->assertNotEmpty($field->type);
    }
}
