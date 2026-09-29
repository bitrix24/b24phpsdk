<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Messageservice\Sender\Service;

use Bitrix24\SDK\Services\Messageservice\Sender\Service\Sender;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sender::class)]
class SenderTest extends TestCase
{
    private Sender $sender;

    private string $testSenderCode = 'test_sdk_sender_1';

    #[\Override]
    protected function setUp(): void
    {
        $this->sender = Fabric::getServiceBuilder(true)->getMessageserviceScope()->sender();
        // Ensure cleanup before each test
        try {
            $this->sender->delete($this->testSenderCode);
        } catch (\Throwable) {
            // Ignore if sender does not exist
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Cleanup after each test
        try {
            $this->sender->delete($this->testSenderCode);
        } catch (\Throwable) {
            // Ignore if sender was already deleted
        }
    }

    #[Test]
    #[TestDox('messageservice.sender.add registers a new sender successfully')]
    public function testAdd(): void
    {
        $senderAddResult = $this->sender->add(
            code: $this->testSenderCode,
            type: 'SMS',
            handler: 'https://provider.example/api/handler',
            name: 'Test SDK Sender'
        );

        $this->assertTrue($senderAddResult->isSuccess());
    }

    #[Test]
    #[TestDox('messageservice.sender.list returns array of sender codes')]
    public function testList(): void
    {
        // Register a sender first
        $this->sender->add(
            code: $this->testSenderCode,
            type: 'SMS',
            handler: 'https://provider.example/api/handler',
            name: 'Test SDK Sender'
        );

        $sendersListResult = $this->sender->list();
        $codes = $sendersListResult->getSenderCodes();

        $this->assertIsArray($codes);
        $this->assertContains($this->testSenderCode, $codes);
    }

    #[Test]
    #[TestDox('messageservice.sender.update updates an existing sender successfully')]
    public function testUpdate(): void
    {
        // Register a sender first
        $this->sender->add(
            code: $this->testSenderCode,
            type: 'SMS',
            handler: 'https://provider.example/api/handler',
            name: 'Test SDK Sender'
        );

        $senderUpdateResult = $this->sender->update(
            code: $this->testSenderCode,
            handler: 'https://provider.example/api/new-handler',
            name: 'Test SDK Sender Updated'
        );

        $this->assertTrue($senderUpdateResult->isSuccess());
    }

    #[Test]
    #[TestDox('messageservice.sender.delete removes an existing sender successfully')]
    public function testDelete(): void
    {
        // Register a sender first
        $this->sender->add(
            code: $this->testSenderCode,
            type: 'SMS',
            handler: 'https://provider.example/api/handler',
            name: 'Test SDK Sender'
        );

        $senderDeleteResult = $this->sender->delete($this->testSenderCode);

        $this->assertTrue($senderDeleteResult->isSuccess());
    }
}
