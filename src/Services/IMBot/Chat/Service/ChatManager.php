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

#[ApiServiceMetadata(new Scope(['imbot']))]
class ChatManager extends AbstractService
{
    /**
     * Assign manager rights to a chat member.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-manager-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.Manager.add',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-manager-add.html',
        'Assign manager rights to a chat member'
    )]
    public function add(
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

        return new UpdatedItemResult($this->core->call('imbot.v2.Chat.Manager.add', $params));
    }

    /**
     * Revoke manager rights from a chat member.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-manager-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.Manager.delete',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-manager-delete.html',
        'Revoke manager rights from a chat member'
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

        return new UpdatedItemResult($this->core->call('imbot.v2.Chat.Manager.delete', $params));
    }
}
