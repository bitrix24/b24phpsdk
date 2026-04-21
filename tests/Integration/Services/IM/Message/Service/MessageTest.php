<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Message\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Message\Service\LikeAction;
use Bitrix24\SDK\Services\IM\Message\Service\Message;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Message::class)]
class MessageTest extends TestCase
{
    private Message $service;

    private int $currentUserId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getIMScope()->message();
        $this->currentUserId = (int)$this->service->core->call('PROFILE')
            ->getResponseData()->getResult()['ID'];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add sends a personal message and returns its ID')]
    public function testAdd(): void
    {
        $addedItemResult = $this->service->add(
            dialogId: (string)$this->currentUserId,
            message: sprintf('Test add at %s', time()),
        );
        $this->assertGreaterThan(0, $addedItemResult->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('update edits a previously sent message')]
    public function testUpdate(): void
    {
        $messageId = $this->service->add(
            (string)$this->currentUserId,
            sprintf('Before update at %s', time()),
        )->getId();
        $this->assertTrue(
            $this->service->update($messageId, 'Updated text')->isSuccess()
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('delete removes a previously sent message')]
    public function testDelete(): void
    {
        $messageId = $this->service->add(
            (string)$this->currentUserId,
            sprintf('To delete at %s', time()),
        )->getId();
        $this->assertTrue($this->service->delete($messageId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('like toggles the Like mark on a message')]
    public function testLike(): void
    {
        $messageId = $this->service->add(
            (string)$this->currentUserId,
            sprintf('To like at %s', time()),
        )->getId();
        $this->assertTrue(
            $this->service->like($messageId, LikeAction::plus)->isSuccess()
        );
    }

    #[Test]
    #[TestDox('command executes a chat-bot command')]
    public function testCommand(): void
    {
        $this->markTestSkipped(
            'im.message.command requires a registered chat bot with a command; '
            . 'skipped in standard integration suite. '
            . 'Re-enable when a bot fixture is available.'
        );
    }

    #[Test]
    #[TestDox('share creates an object based on a message')]
    public function testShare(): void
    {
        $this->markTestSkipped(
            'im.message.share requires a chat ID (not personal dialog); '
            . 'skipped in standard integration suite. '
            . 'Re-enable when a chat fixture is available.'
        );
    }
}
