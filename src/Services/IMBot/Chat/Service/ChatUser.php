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

namespace Bitrix24\SDK\Services\IMBot\Chat\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IMBot\Chat\Result\ChatUserListResult;

#[ApiServiceMetadata(new Scope(['imbot']))]
class ChatUser extends AbstractService
{
    /**
     * Add users to a chat.
     *
     * @param int[] $userIds Array of user IDs to add.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-user-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.User.add',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-user-add.html',
        'Add users to a chat'
    )]
    public function add(
        int $botId,
        int $chatId,
        array $userIds,
        ?string $botToken = null,
    ): UpdatedItemResult {
        $params = [
            'botId' => $botId,
            'chatId' => $chatId,
            'userIds' => $userIds,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new UpdatedItemResult($this->core->call('imbot.v2.Chat.User.add', $params));
    }

    /**
     * Remove a user from a chat.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-user-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.User.delete',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-user-delete.html',
        'Remove a user from a chat'
    )]
    public function delete(
        int $botId,
        int $chatId,
        int $userId,
        ?string $botToken = null,
    ): UpdatedItemResult {
        $params = [
            'botId' => $botId,
            'chatId' => $chatId,
            'userId' => $userId,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new UpdatedItemResult($this->core->call('imbot.v2.Chat.User.delete', $params));
    }

    /**
     * Get the list of users in a chat.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-user-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.User.list',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-user-list.html',
        'Get the list of users in a chat'
    )]
    public function list(
        int $botId,
        int $chatId,
        ?string $botToken = null,
    ): ChatUserListResult {
        $params = [
            'botId' => $botId,
            'chatId' => $chatId,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new ChatUserListResult($this->core->call('imbot.v2.Chat.User.list', $params));
    }
}
