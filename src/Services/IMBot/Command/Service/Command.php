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

namespace Bitrix24\SDK\Services\IMBot\Command\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\EmptyResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IMBot\Command\Result\CommandResult;
use Bitrix24\SDK\Services\IMBot\Command\Result\CommandsResult;

#[ApiServiceMetadata(new Scope(['imbot']))]
class Command extends AbstractService
{
    /**
     * Register a slash command for a bot.
     *
     * Idempotent: repeated calls with the same command return the existing command.
     *
     * @param array<string, string> $title Localised titles: { 'en' => 'Show help', 'ru' => 'Помощь' }.
     * @param array<string, string> $params Localised parameter hints.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/commands/command-register.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Command.register',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/commands/command-register.html',
        'Register a slash command for a bot'
    )]
    public function register(
        int $botId,
        string $command,
        array $title = [],
        array $params = [],
        bool $common = false,
        bool $hidden = false,
        bool $extranetSupport = false,
        ?string $botToken = null,
    ): CommandResult {
        $fields = [
            'command' => $command,
            'common' => $common,
            'hidden' => $hidden,
            'extranetSupport' => $extranetSupport,
        ];

        if ($title !== []) {
            $fields['title'] = $title;
        }

        if ($params !== []) {
            $fields['params'] = $params;
        }

        $apiParams = [
            'botId' => $botId,
            'fields' => $fields,
        ];

        if ($botToken !== null) {
            $apiParams['botToken'] = $botToken;
        }

        return new CommandResult($this->core->call('imbot.v2.Command.register', $apiParams));
    }

    /**
     * Update an existing slash command.
     *
     * @param array<string, mixed> $fields Fields to update.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/commands/command-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Command.update',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/commands/command-update.html',
        'Update an existing slash command'
    )]
    public function update(
        int $botId,
        int $commandId,
        array $fields,
        ?string $botToken = null,
    ): CommandResult {
        $params = [
            'botId' => $botId,
            'commandId' => $commandId,
            'fields' => $fields,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new CommandResult($this->core->call('imbot.v2.Command.update', $params));
    }

    /**
     * Get the list of commands registered for a bot.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/commands/command-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Command.list',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/commands/command-list.html',
        'Get the list of commands registered for a bot'
    )]
    public function list(
        int $botId,
        ?string $botToken = null,
    ): CommandsResult {
        $params = ['botId' => $botId];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new CommandsResult($this->core->call('imbot.v2.Command.list', $params));
    }

    /**
     * Unregister (delete) a slash command.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/commands/command-unregister.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Command.unregister',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/commands/command-unregister.html',
        'Unregister a slash command'
    )]
    public function unregister(
        int $botId,
        int $commandId,
        ?string $botToken = null,
    ): EmptyResult {
        $params = [
            'botId' => $botId,
            'commandId' => $commandId,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new EmptyResult($this->core->call('imbot.v2.Command.unregister', $params));
    }

    /**
     * Answer a command invocation with a message.
     *
     * @param array<array-key, mixed>|null $attach Attachments.
     * @param array<array-key, mixed>|null $keyboard Keyboard.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/commands/command-answer.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Command.answer',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/commands/command-answer.html',
        'Answer a command invocation with a message'
    )]
    public function answer(
        int $botId,
        int $messageId,
        string $dialogId,
        ?string $message = null,
        ?array $attach = null,
        ?array $keyboard = null,
        bool $system = false,
        bool $urlPreview = true,
        ?string $botToken = null,
    ): EmptyResult {
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

        $params = [
            'botId' => $botId,
            'messageId' => $messageId,
            'dialogId' => $dialogId,
            'fields' => $fields,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new EmptyResult($this->core->call('imbot.v2.Command.answer', $params));
    }
}
