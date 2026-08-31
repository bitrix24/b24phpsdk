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
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\EmptyResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IMBot\ChatMessage\Result\ChatMessageResult;
use Bitrix24\SDK\Services\IMBot\ChatMessage\Result\ChatMessageSentResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['imbot']))]
class ChatMessage extends AbstractService
{
    public function __construct(public readonly Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Send a message on behalf of the bot.
     *
     * @param array<array-key, mixed>|null $attach Message attachments.
     * @param array<array-key, mixed>|null $keyboard Keyboard buttons.
     * @param array<string, int>|null $forwardIds Messages to forward {uuid: messageId}.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-send.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.Message.send',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-send.html',
        'Send a message on behalf of the bot'
    )]
    public function send(
        int $botId,
        string $dialogId,
        ?string $message = null,
        ?array $attach = null,
        ?array $keyboard = null,
        bool $system = false,
        bool $urlPreview = true,
        ?int $replyId = null,
        ?string $templateId = null,
        ?array $forwardIds = null,
        ?string $botToken = null,
    ): ChatMessageSentResult {
        $fields = [
            'system' => $system,
            'urlPreview' => $urlPreview,
        ];

        if ($message !== null) {
            $fields['message'] = $message;
        }

        if ($attach !== null) {
            $fields['attach'] = $attach;
        }

        if ($keyboard !== null) {
            $fields['keyboard'] = $keyboard;
        }

        if ($replyId !== null) {
            $fields['replyId'] = $replyId;
        }

        if ($templateId !== null) {
            $fields['templateId'] = $templateId;
        }

        if ($forwardIds !== null) {
            $fields['forwardIds'] = $forwardIds;
        }

        $params = [
            'botId' => $botId,
            'dialogId' => $dialogId,
            'fields' => $fields,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new ChatMessageSentResult($this->core->call('imbot.v2.Chat.Message.send', $params));
    }

    /**
     * Update a previously sent message.
     *
     * @param array<array-key, mixed>|null $attach Updated attachments.
     * @param array<array-key, mixed>|null $keyboard Updated keyboard.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.Message.update',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-update.html',
        'Update a previously sent message'
    )]
    public function update(
        int $botId,
        int $messageId,
        ?string $message = null,
        ?array $attach = null,
        ?array $keyboard = null,
        ?bool $urlPreview = null,
        ?bool $isEdited = null,
        ?string $botToken = null,
    ): UpdatedItemResult {
        $fields = [];

        if ($message !== null) {
            $fields['message'] = $message;
        }

        if ($attach !== null) {
            $fields['attach'] = $attach;
        }

        if ($keyboard !== null) {
            $fields['keyboard'] = $keyboard;
        }

        if ($urlPreview !== null) {
            $fields['urlPreview'] = $urlPreview;
        }

        if ($isEdited !== null) {
            $fields['isEdited'] = $isEdited;
        }

        $params = [
            'botId' => $botId,
            'messageId' => $messageId,
            'fields' => $fields,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new UpdatedItemResult($this->core->call('imbot.v2.Chat.Message.update', $params));
    }

    /**
     * Delete a message sent by the bot.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.Message.delete',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-delete.html',
        'Delete a message sent by the bot'
    )]
    public function delete(
        int $botId,
        int $messageId,
        ?string $botToken = null,
    ): UpdatedItemResult {
        $params = [
            'botId' => $botId,
            'messageId' => $messageId,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new UpdatedItemResult($this->core->call('imbot.v2.Chat.Message.delete', $params));
    }

    /**
     * Mark a message as read.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-read.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.Message.read',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-read.html',
        'Mark a message as read'
    )]
    public function read(
        int $botId,
        int $messageId,
        ?string $botToken = null,
    ): UpdatedItemResult {
        $params = [
            'botId' => $botId,
            'messageId' => $messageId,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new UpdatedItemResult($this->core->call('imbot.v2.Chat.Message.read', $params));
    }

    /**
     * Get a message by its ID.
     *
     * Available only for bots of type supervisor and personal.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.Message.get',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-get.html',
        'Get a message by its ID'
    )]
    public function get(
        int $botId,
        int $messageId,
        ?string $botToken = null,
    ): ChatMessageResult {
        $params = [
            'botId' => $botId,
            'messageId' => $messageId,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new ChatMessageResult($this->core->call('imbot.v2.Chat.Message.get', $params));
    }

    /**
     * Get messages context around a given message.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-get-context.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Chat.Message.getContext',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-get-context.html',
        'Get messages context around a given message'
    )]
    public function getContext(
        int $botId,
        int $messageId,
        int $limit = 20,
        ?string $botToken = null,
    ): EmptyResult {
        $params = [
            'botId' => $botId,
            'messageId' => $messageId,
            'limit' => $limit,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new EmptyResult($this->core->call('imbot.v2.Chat.Message.getContext', $params));
    }
}
