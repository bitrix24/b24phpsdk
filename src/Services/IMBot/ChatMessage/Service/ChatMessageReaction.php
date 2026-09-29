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

namespace Bitrix24\SDK\Services\IMBot\ChatMessage\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;

#[ApiServiceMetadata(new Scope(['imbot']))]
class ChatMessageReaction extends AbstractService
{
    /**
     * Add a reaction to a message.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-reaction-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.Message.Reaction.add',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-reaction-add.html',
        'Add a reaction to a message'
    )]
    public function add(
        int $botId,
        int $messageId,
        string $reaction,
        ?string $botToken = null,
    ): UpdatedItemResult {
        $params = [
            'botId' => $botId,
            'messageId' => $messageId,
            'reaction' => $reaction,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new UpdatedItemResult($this->core->call('imbot.v2.Chat.Message.Reaction.add', $params));
    }

    /**
     * Remove a reaction from a message.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-reaction-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.Message.Reaction.delete',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-reaction-delete.html',
        'Remove a reaction from a message'
    )]
    public function delete(
        int $botId,
        int $messageId,
        string $reaction,
        ?string $botToken = null,
    ): UpdatedItemResult {
        $params = [
            'botId' => $botId,
            'messageId' => $messageId,
            'reaction' => $reaction,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new UpdatedItemResult($this->core->call('imbot.v2.Chat.Message.Reaction.delete', $params));
    }
}
