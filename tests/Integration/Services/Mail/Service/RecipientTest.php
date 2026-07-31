<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Mail\Service;

use Bitrix24\SDK\Services\Mail\Service\Recipient;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Recipient::class)]
class RecipientTest extends TestCase
{
    private Recipient $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getMailScope()->recipient();
    }

    #[Test]
    public function testListContacts(): void
    {
        $this->assertIsArray($this->service->listContacts()->getRecipients());
    }

    #[Test]
    public function testListEmployees(): void
    {
        $this->assertIsArray($this->service->listEmployees('a')->getRecipients());
    }
}
