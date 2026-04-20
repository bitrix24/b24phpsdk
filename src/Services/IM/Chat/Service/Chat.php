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

namespace Bitrix24\SDK\Services\IM\Chat\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Chat\ChatColor;
use Bitrix24\SDK\Services\IM\Chat\ChatEntityType;
use Bitrix24\SDK\Services\IM\Chat\ChatType;
use Bitrix24\SDK\Services\IM\Chat\Result\ChatResult;

#[ApiServiceMetadata(new Scope(['im']))]
class Chat extends AbstractService
{
    /**
     * @param positive-int[] $users
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.add',
        'https://apidocs.bitrix24.com/api-reference/chats/im-chat-add.html',
        'Create a new chat'
    )]
    public function add(
        array $users,
        ?ChatType $chatType = null,
        ?string $title = null,
        ?string $description = null,
        ?ChatColor $chatColor = null,
        ?string $message = null,
        ?string $avatar = null,
        ?ChatEntityType $chatEntityType = null,
        ?string $entityId = null,
        ?string $copilotMainRole = null,
    ): AddedItemResult {
        $payload = ['USERS' => $users];
        if ($chatType instanceof ChatType) {
            $payload['TYPE'] = $chatType->value;
        }

        if ($title !== null) {
            $payload['TITLE'] = $title;
        }

        if ($description !== null) {
            $payload['DESCRIPTION'] = $description;
        }

        if ($chatColor instanceof ChatColor) {
            $payload['COLOR'] = $chatColor->value;
        }

        if ($message !== null) {
            $payload['MESSAGE'] = $message;
        }

        if ($avatar !== null) {
            $payload['AVATAR'] = $avatar;
        }

        if ($chatEntityType instanceof ChatEntityType) {
            $payload['ENTITY_TYPE'] = $chatEntityType->value;
        }

        if ($entityId !== null) {
            $payload['ENTITY_ID'] = $entityId;
        }

        if ($copilotMainRole !== null) {
            $payload['COPILOT_MAIN_ROLE'] = $copilotMainRole;
        }

        return new AddedItemResult($this->core->call('im.chat.add', $payload));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.get',
        'https://apidocs.bitrix24.com/api-reference/chats/im-chat-get.html',
        'Get chat id by linked entity type and id'
    )]
    public function get(ChatEntityType $chatEntityType, string $entityId): ChatResult
    {
        return new ChatResult($this->core->call('im.chat.get', [
            'ENTITY_TYPE' => $chatEntityType->value,
            'ENTITY_ID' => $entityId,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.leave',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-users/im-chat-leave.html',
        'Remove the current user from a chat'
    )]
    public function leave(int $chatId): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.leave', [
            'CHAT_ID' => $chatId,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.mute',
        'https://apidocs.bitrix24.com/api-reference/chats/special-operations/im-chat-mute.html',
        'Mute/unmute notifications in a chat by chat id'
    )]
    public function mute(int $chatId, bool $mute = true): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.mute', [
            'CHAT_ID' => $chatId,
            'MUTE' => $mute ? 'Y' : 'N',
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.mute',
        'https://apidocs.bitrix24.com/api-reference/chats/special-operations/im-chat-mute.html',
        'Mute/unmute notifications in a chat by dialog id'
    )]
    public function muteByDialog(string $dialogId, bool $mute = true): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.mute', [
            'DIALOG_ID' => $dialogId,
            'MUTE' => $mute ? 'Y' : 'N',
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.setOwner',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-update/im-chat-set-owner.html',
        'Change chat owner'
    )]
    public function setOwner(int $chatId, int $userId): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.setOwner', [
            'CHAT_ID' => $chatId,
            'USER_ID' => $userId,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.updateAvatar',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-update/im-chat-update-avatar.html',
        'Update chat avatar'
    )]
    public function updateAvatar(int $chatId, string $avatar): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.updateAvatar', [
            'CHAT_ID' => $chatId,
            'AVATAR' => $avatar,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.updateColor',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-update/im-chat-update-color.html',
        'Update chat color (mobile app)'
    )]
    public function updateColor(int $chatId, ChatColor $chatColor): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.updateColor', [
            'CHAT_ID' => $chatId,
            'COLOR' => $chatColor->value,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.updateTitle',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-update/im-chat-update-title.html',
        'Update chat title'
    )]
    public function updateTitle(int $chatId, string $title): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.updateTitle', [
            'CHAT_ID' => $chatId,
            'TITLE' => $title,
        ]));
    }
}
