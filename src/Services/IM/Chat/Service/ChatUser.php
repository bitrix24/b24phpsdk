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
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Chat\Result\ChatUserListResult;

#[ApiServiceMetadata(new Scope(['im']))]
class ChatUser extends AbstractService
{
    /**
     * @param positive-int   $chatId
     * @param positive-int[] $userIds
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.user.add',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-users/im-chat-user-add.html',
        'Add participants to a chat'
    )]
    public function add(int $chatId, array $userIds, bool $hideHistory = true): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.user.add', [
            'CHAT_ID' => $chatId,
            'USERS' => $userIds,
            'HIDE_HISTORY' => $hideHistory ? 'Y' : 'N',
        ]));
    }

    /**
     * @param positive-int $chatId
     * @param positive-int $userId
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.user.delete',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-users/im-chat-user-delete.html',
        'Remove a participant from a chat'
    )]
    public function delete(int $chatId, int $userId): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.user.delete', [
            'CHAT_ID' => $chatId,
            'USER_ID' => $userId,
        ]));
    }

    /**
     * @param positive-int $chatId
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.user.list',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-users/im-chat-user-list.html',
        'List participant user IDs of a chat'
    )]
    public function list(int $chatId): ChatUserListResult
    {
        return new ChatUserListResult($this->core->call('im.chat.user.list', [
            'CHAT_ID' => $chatId,
        ]));
    }
}
