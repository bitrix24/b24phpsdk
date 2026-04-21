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
use Bitrix24\SDK\Services\IM\Chat\ChatType;
use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachPayloadInterface;
use Bitrix24\SDK\Services\IM\Message\Service\Message;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

abstract class MessageChatTestCase extends TestCase
{
    protected const DOCS_IMAGE_URL = 'https://apidocs.bitrix24.ru/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/attachments/_images/attach1.png';

    protected const DOCS_LINK_URL = 'https://apidocs.bitrix24.ru';

    protected const DOCS_FILE_URL = 'https://apidocs.bitrix24.ru/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/attachments/_images/attach1.png';

    protected Message $messageService;

    protected Chat $chatService;

    protected int $currentUserId;

    protected string $currentUserPhotoUrl = '';

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $imServiceBuilder = Factory::getServiceBuilder()->getIMScope();
        $this->messageService = $imServiceBuilder->message();
        $this->chatService = $imServiceBuilder->chat();
        $profile = $this->messageService->core->call('PROFILE')
            ->getResponseData()->getResult();
        $this->currentUserId = (int)$profile['ID'];
        $this->currentUserPhotoUrl = (string)($profile['PERSONAL_PHOTO'] ?? '');
    }

    /**
     * @throws BaseException
     */
    #[\Override]
    protected function tearDown(): void
    {
        // Cleanup is intentionally disabled so message/chat payloads remain visible in the portal
        // for manual inspection after the integration test run.
        // foreach ($createdChats as $chatId) {
        //     try {
        //         $this->chatService->leave($chatId);
        //     } catch (BaseException) {
        //         // Chat may already be left by the portal or another cleanup path.
        //     }
        // }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function sendMessage(
        ?string $message = null,
        array|string|AttachPayloadInterface|null $attach = null,
        array|string|null $keyboard = null,
        array|string|null $menu = null,
    ): int {
        return $this->messageService->add(
            dialogId: $this->createDialogId(),
            message: $message,
            attach: $attach,
            keyboard: $keyboard,
            menu: $menu,
        )->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function sendAttach(array|string|AttachPayloadInterface $attach, ?string $message = null): int
    {
        return $this->sendMessage(
            message: $message,
            attach: $attach,
        );
    }

    /**
     * @param list<array<string, mixed>> $blocks
     *
     * @return array<string, mixed>
     */
    protected function createFullAttach(
        array $blocks,
        int $id = 1,
        ?string $colorToken = null,
        ?string $color = null,
    ): array {
        $attach = [
            'ID' => $id,
            'BLOCKS' => $blocks,
        ];

        if ($colorToken !== null) {
            $attach['COLOR_TOKEN'] = $colorToken;
        }

        if ($color !== null) {
            $attach['COLOR'] = $color;
        }

        return $attach;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function createDialogId(): string
    {
        return $this->getDialogId($this->createChat());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function createChat(): int
    {
        return $this->chatService->add(
            users: [$this->currentUserId],
            chatType: ChatType::Closed,
            title: sprintf('Message payload %s', uniqid('', true)),
        )->getId();
    }

    protected function getDialogId(int $chatId): string
    {
        return sprintf('chat%d', $chatId);
    }
}
