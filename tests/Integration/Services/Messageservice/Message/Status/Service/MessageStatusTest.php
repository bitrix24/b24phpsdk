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

namespace Bitrix24\SDK\Tests\Integration\Services\Messageservice\Message\Status\Service;

use Bitrix24\SDK\Services\Messageservice\Message\Status\Service\MessageStatus;
use Bitrix24\SDK\Services\Messageservice\Sender\Service\Sender;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageStatus::class)]
class MessageStatusTest extends TestCase
{
    private MessageStatus $messageStatus;

    private Sender $sender;

    private string $testSenderCode = 'test_sdk_msg_status_1';

    #[\Override]
    protected function setUp(): void
    {
        $messageserviceServiceBuilder = Factory::getServiceBuilder(true)->getMessageserviceScope();
        $this->messageStatus = $messageserviceServiceBuilder->messageStatus();
        $this->sender = $messageserviceServiceBuilder->sender();

        // Ensure cleanup before test
        try {
            $this->sender->delete($this->testSenderCode);
        } catch (\Throwable) {
            // Ignore if sender does not exist
        }

        // Register a sender to use in tests
        $this->sender->add(
            code: $this->testSenderCode,
            type: 'SMS',
            handler: 'https://provider.example/api/handler',
            name: 'Test SDK Message Status Sender'
        );
    }

    #[\Override]
    protected function tearDown(): void
    {
        try {
            $this->sender->delete($this->testSenderCode);
        } catch (\Throwable) {
            // Ignore
        }
    }

    #[Test]
    #[TestDox('messageservice.message.status.update returns error for unknown message id (expected API error)')]
    public function testUpdateReturnsExpectedErrorForUnknownMessage(): void
    {
        // The messageservice.message.status.update requires a real MESSAGE_ID from an actual sent message.
        // Since integration tests cannot send real SMS, we verify that the API correctly rejects
        // an unknown message ID with the expected error code.
        $this->expectException(\Bitrix24\SDK\Core\Exceptions\BaseException::class);

        $this->messageStatus->update(
            code: $this->testSenderCode,
            messageId: 'nonexistent_message_id_for_testing',
            status: 'delivered'
        );
    }
}
