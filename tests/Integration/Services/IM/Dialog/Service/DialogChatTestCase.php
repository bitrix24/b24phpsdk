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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Chat\ChatType;
use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Bitrix24\SDK\Services\IM\Message\Service\Message;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

abstract class DialogChatTestCase extends TestCase
{
    protected Dialog $dialogService;

    protected Chat $chatService;

    protected Message $messageService;

    protected int $currentUserId;

    /**
     * @var list<int>
     */
    private array $createdChats = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $imServiceBuilder = Fabric::getServiceBuilder()->getIMScope();
        $this->dialogService = $imServiceBuilder->dialog();
        $this->chatService = $imServiceBuilder->chat();
        $this->messageService = $imServiceBuilder->message();
        $this->currentUserId = (int)$this->dialogService->core
            ->call('PROFILE')
            ->getResponseData()
            ->getResult()['ID'];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdChats as $createdChat) {
            try {
                $this->chatService->leave($createdChat);
            } catch (BaseException) {
                // chat may already be left by the test itself
            }
        }

        $this->createdChats = [];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function createChat(): int
    {
        $chatId = $this->chatService->add(
            users: [$this->currentUserId],
            chatType: ChatType::Closed,
            title: sprintf('Dialog test %s', uniqid('', true)),
        )->getId();

        $this->createdChats[] = $chatId;

        return $chatId;
    }

    protected function createDialogId(int $chatId): string
    {
        return sprintf('chat%d', $chatId);
    }

    /**
     * @param list<string> $messages
     *
     * @return list<int>
     *
     * @throws BaseException
     * @throws TransportException
     */
    protected function seedMessages(string $dialogId, array $messages): array
    {
        $messageIds = [];

        foreach ($messages as $message) {
            $messageIds[] = $this->messageService->add(
                dialogId: $dialogId,
                message: $message,
            )->getId();
        }

        return $messageIds;
    }
}
