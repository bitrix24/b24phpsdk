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
use Bitrix24\SDK\Core\Result\EmptyResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IMBot\Chat\ChatColor;
use Bitrix24\SDK\Services\IMBot\Chat\Result\ChatResult;

#[ApiServiceMetadata(new Scope(['imbot']))]
class Chat extends AbstractService
{
    /**
     * Create a new group chat on behalf of the bot.
     *
     * @param int[] $userIds Array of member user IDs.
     * @param array<string, mixed> $fields Additional fields (description, avatar, message, ownerId).
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.add',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-add.html',
        'Create a new group chat on behalf of the bot'
    )]
    public function add(
        int $botId,
        array $userIds,
        ?string $title = null,
        ?ChatColor $color = null,
        array $fields = [],
        ?string $botToken = null,
    ): ChatResult {
        $chatFields = array_merge($fields, ['userIds' => $userIds]);

        if ($title !== null) {
            $chatFields['title'] = $title;
        }

        if ($color instanceof ChatColor) {
            $chatFields['color'] = $color->value;
        }

        $params = [
            'botId' => $botId,
            'fields' => $chatFields,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new ChatResult($this->core->call('imbot.v2.Chat.add', $params));
    }

    /**
     * Get chat information by chat ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.get',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-get.html',
        'Get chat information by chat ID'
    )]
    public function get(
        int $botId,
        int $chatId,
        ?string $botToken = null,
    ): ChatResult {
        $params = [
            'botId' => $botId,
            'chatId' => $chatId,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new ChatResult($this->core->call('imbot.v2.Chat.get', $params));
    }

    /**
     * Update chat properties.
     *
     * @param array<string, mixed> $fields Fields to update (title, description, color, avatar, ownerId, textFieldEnabled, backgroundId).
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.update',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-update.html',
        'Update chat properties'
    )]
    public function update(
        int $botId,
        int $chatId,
        array $fields,
        ?string $botToken = null,
    ): ChatResult {
        $params = [
            'botId' => $botId,
            'chatId' => $chatId,
            'fields' => $fields,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new ChatResult($this->core->call('imbot.v2.Chat.update', $params));
    }

    /**
     * Remove the bot from a chat.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-leave.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.leave',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-leave.html',
        'Remove the bot from a chat'
    )]
    public function leave(
        int $botId,
        int $chatId,
        ?string $botToken = null,
    ): UpdatedItemResult {
        $params = [
            'botId' => $botId,
            'chatId' => $chatId,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new UpdatedItemResult($this->core->call('imbot.v2.Chat.leave', $params));
    }

    /**
     * Transfer chat ownership to another user.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-set-owner.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.setOwner',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-set-owner.html',
        'Transfer chat ownership to another user'
    )]
    public function setOwner(
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

        return new UpdatedItemResult($this->core->call('imbot.v2.Chat.setOwner', $params));
    }
}
