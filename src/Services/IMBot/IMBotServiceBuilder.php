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

namespace Bitrix24\SDK\Services\IMBot;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\IMBot\Bot\Service\Bot;
use Bitrix24\SDK\Services\IMBot\Chat\Service\Chat;
use Bitrix24\SDK\Services\IMBot\Chat\Service\ChatInputAction;
use Bitrix24\SDK\Services\IMBot\Chat\Service\ChatManager;
use Bitrix24\SDK\Services\IMBot\Chat\Service\ChatTextField;
use Bitrix24\SDK\Services\IMBot\Chat\Service\ChatUser;
use Bitrix24\SDK\Services\IMBot\ChatMessage\Service\Batch as ChatMessageBatch;
use Bitrix24\SDK\Services\IMBot\ChatMessage\Service\ChatMessage;
use Bitrix24\SDK\Services\IMBot\ChatMessage\Service\ChatMessageReaction;
use Bitrix24\SDK\Services\IMBot\Command\Service\Command;
use Bitrix24\SDK\Services\IMBot\Event\Service\Event;
use Bitrix24\SDK\Services\IMBot\File\Service\File;
use Bitrix24\SDK\Services\IMBot\Revision\Service\Revision;

/**
 * Service builder for the imbot scope.
 *
 * Provides access to all imbot.v2.* REST API services.
 *
 * @see https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/index.html
 */
#[ApiServiceBuilderMetadata(new Scope(['imbot']))]
class IMBotServiceBuilder extends AbstractServiceBuilder
{
    /**
     * Bot management: imbot.v2.Bot.register, imbot.v2.Bot.update, imbot.v2.Bot.get,
     * imbot.v2.Bot.list, imbot.v2.Bot.unregister.
     */
    public function bot(): Bot
    {
        $this->serviceCache[__METHOD__] ??= new Bot($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Chat management: imbot.v2.Chat.add, imbot.v2.Chat.get, imbot.v2.Chat.update,
     * imbot.v2.Chat.leave, imbot.v2.Chat.setOwner.
     */
    public function chat(): Chat
    {
        $this->serviceCache[__METHOD__] ??= new Chat($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Chat users: imbot.v2.Chat.User.add, imbot.v2.Chat.User.delete, imbot.v2.Chat.User.list.
     */
    public function chatUser(): ChatUser
    {
        $this->serviceCache[__METHOD__] ??= new ChatUser($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Chat managers: imbot.v2.Chat.Manager.add, imbot.v2.Chat.Manager.delete.
     */
    public function chatManager(): ChatManager
    {
        $this->serviceCache[__METHOD__] ??= new ChatManager($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Chat messages: imbot.v2.Chat.Message.send, imbot.v2.Chat.Message.update,
     * imbot.v2.Chat.Message.delete, imbot.v2.Chat.Message.read,
     * imbot.v2.Chat.Message.get, imbot.v2.Chat.Message.getContext.
     * Batch: send, delete, update.
     */
    public function chatMessage(): ChatMessage
    {
        $this->serviceCache[__METHOD__] ??= new ChatMessage(
            new ChatMessageBatch($this->batch, $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Message reactions: imbot.v2.Chat.Message.Reaction.add, imbot.v2.Chat.Message.Reaction.delete.
     */
    public function chatMessageReaction(): ChatMessageReaction
    {
        $this->serviceCache[__METHOD__] ??= new ChatMessageReaction($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Input action: imbot.v2.Chat.InputAction.notify.
     */
    public function chatInputAction(): ChatInputAction
    {
        $this->serviceCache[__METHOD__] ??= new ChatInputAction($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Text field: imbot.v2.Chat.TextField.enabled.
     */
    public function chatTextField(): ChatTextField
    {
        $this->serviceCache[__METHOD__] ??= new ChatTextField($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Commands: imbot.v2.Command.register, imbot.v2.Command.update, imbot.v2.Command.list,
     * imbot.v2.Command.unregister, imbot.v2.Command.answer.
     */
    public function command(): Command
    {
        $this->serviceCache[__METHOD__] ??= new Command($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Bot events (fetch mode): imbot.v2.Event.get.
     */
    public function event(): Event
    {
        $this->serviceCache[__METHOD__] ??= new Event($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    /**
     * File operations: imbot.v2.File.upload, imbot.v2.File.download.
     */
    public function file(): File
    {
        $this->serviceCache[__METHOD__] ??= new File($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Revision info: imbot.v2.Revision.get.
     */
    public function revision(): Revision
    {
        $this->serviceCache[__METHOD__] ??= new Revision($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }
}
