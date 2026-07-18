<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Mail\MailboxField\Service;

use Bitrix24\SDK\Services\Mail\MailboxField\Service\MailboxField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MailboxField::class)]
class MailboxFieldTest extends TestCase
{
    private MailboxField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getMailScope()->mailboxField();
    }

    #[Test]
    public function testList(): void
    {
        $fields = $this->service->list()->getMailboxFields();
        $this->assertNotEmpty($fields);
    }

    #[Test]
    public function testGet(): void
    {
        $field = $this->service->get('id')->mailboxField();
        $this->assertSame('id', $field->name);
        $this->assertNotEmpty($field->type);
    }
}
