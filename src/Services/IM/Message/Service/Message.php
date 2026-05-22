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

namespace Bitrix24\SDK\Services\IM\Message\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachPayloadInterface;
use Bitrix24\SDK\Services\AbstractService;

#[ApiServiceMetadata(new Scope(['im']))]
class Message extends AbstractService
{
    /**
     * @param array<array-key, mixed>|string|AttachPayloadInterface|null $attach
     *        Raw JSON string payloads are deprecated; prefer AttachPayloadInterface for typed construction
     *        or array payloads for backward-compatible raw structures.
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.message.add',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-add.html',
        'Send a message to a chat'
    )]
    public function add(
        string $dialogId,
        ?string $message = null,
        array|string|AttachPayloadInterface|null $attach = null,
        array|string|null $keyboard = null,
        array|string|null $menu = null,
        bool $isSystem = false,
        bool $urlPreview = true,
        ?int $replyId = null,
    ): AddedItemResult {
        return new AddedItemResult($this->core->call(
            'im.message.add',
            [
                'DIALOG_ID' => $dialogId,
                'MESSAGE' => $message,
                'ATTACH' => $this->normalizeAttach($attach),
                'KEYBOARD' => $keyboard,
                'MENU' => $menu,
                'SYSTEM' => $isSystem ? 'Y' : 'N',
                'URL_PREVIEW' => $urlPreview ? 'Y' : 'N',
                'REPLY_ID' => $replyId,
            ]
        ));
    }

    /**
     * @param array<array-key, mixed>|string|AttachPayloadInterface|null $attach
     *        Raw JSON string payloads are deprecated; prefer AttachPayloadInterface for typed construction
     *        or array payloads for backward-compatible raw structures.
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.message.update',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-update.html',
        'Update text and parameters of a sent message'
    )]
    public function update(
        int $messageId,
        ?string $message = null,
        array|string|AttachPayloadInterface|null $attach = null,
        array|string|null $keyboard = null,
        array|string|null $menu = null,
        ?bool $urlPreview = null,
        ?bool $isEdited = null,
    ): UpdatedItemResult {
        return new UpdatedItemResult($this->core->call(
            'im.message.update',
            [
                'MESSAGE_ID' => $messageId,
                'MESSAGE' => $message,
                'ATTACH' => $this->normalizeAttach($attach),
                'KEYBOARD' => $keyboard,
                'MENU' => $menu,
                'URL_PREVIEW' => $urlPreview === null ? null : ($urlPreview ? 'Y' : 'N'),
                'IS_EDITED' => $isEdited === null ? null : ($isEdited ? 'Y' : 'N'),
            ]
        ));
    }

    /**
     * @param array<array-key, mixed>|string|AttachPayloadInterface|null $attach
     *        Raw JSON string payloads are deprecated and remain supported only for backward compatibility.
     *
     * @return array<array-key, mixed>|string|null
     */
    private function normalizeAttach(array|string|AttachPayloadInterface|null $attach): array|string|null
    {
        if ($attach instanceof AttachPayloadInterface) {
            return $attach->build();
        }

        return $attach;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.message.delete',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-delete.html',
        'Delete a message'
    )]
    public function delete(int $messageId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call(
            'im.message.delete',
            ['MESSAGE_ID' => $messageId]
        ));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.message.like',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-like.html',
        'Toggle the "Like" mark on a message'
    )]
    public function like(int $messageId, LikeAction $likeAction = LikeAction::auto): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call(
            'im.message.like',
            [
                'MESSAGE_ID' => $messageId,
                'ACTION' => $likeAction->value,
            ]
        ));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.message.share',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-share.html',
        'Create an object (chat/task/post/calendar event) based on a message'
    )]
    public function share(int $messageId, string $dialogId, ShareType $shareType): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call(
            'im.message.share',
            [
                'MESSAGE_ID' => $messageId,
                'DIALOG_ID' => $dialogId,
                'TYPE' => $shareType->value,
            ]
        ));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.message.command',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-command.html',
        'Invoke a chat-bot command in the context of a message'
    )]
    public function command(
        int $messageId,
        int $botId,
        string $command,
        ?string $commandParams = null,
    ): UpdatedItemResult {
        return new UpdatedItemResult($this->core->call(
            'im.message.command',
            [
                'MESSAGE_ID' => $messageId,
                'BOT_ID' => $botId,
                'COMMAND' => $command,
                'COMMAND_PARAMS' => $commandParams,
            ]
        ));
    }
}
