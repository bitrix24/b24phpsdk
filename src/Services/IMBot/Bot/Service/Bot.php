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

namespace Bitrix24\SDK\Services\IMBot\Bot\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\EmptyResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IMBot\Bot\BotBackground;
use Bitrix24\SDK\Services\IMBot\Bot\BotEventMode;
use Bitrix24\SDK\Services\IMBot\Bot\BotType;
use Bitrix24\SDK\Services\IMBot\Bot\Result\BotResult;
use Bitrix24\SDK\Services\IMBot\Bot\Result\BotsResult;

#[ApiServiceMetadata(new Scope(['imbot']))]
class Bot extends AbstractService
{
    /**
     * Register a new chat-bot.
     *
     * Idempotent: repeated calls with the same code return the existing bot without updating its data.
     *
     * @param array<string, mixed> $properties Bot profile properties (name, firstName, lastName, workPosition, color, gender, avatar).
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/bots/bot-register.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Bot.register',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/bots/bot-register.html',
        'Register a new chat-bot'
    )]
    public function register(
        string $code,
        array $properties,
        ?string $botToken = null,
        BotType $botType = BotType::bot,
        BotEventMode $botEventMode = BotEventMode::fetch,
        ?string $webhookUrl = null,
        bool $isHidden = false,
        bool $isReactionsEnabled = true,
        bool $isSupportOpenline = false,
        ?BotBackground $botBackground = null,
    ): BotResult {
        $fields = [
            'code' => $code,
            'properties' => $properties,
            'type' => $botType->value,
            'eventMode' => $botEventMode->value,
            'isHidden' => $isHidden,
            'isReactionsEnabled' => $isReactionsEnabled,
            'isSupportOpenline' => $isSupportOpenline,
        ];

        if ($botToken !== null) {
            $fields['botToken'] = $botToken;
        }

        if ($webhookUrl !== null) {
            $fields['webhookUrl'] = $webhookUrl;
        }

        if ($botBackground instanceof BotBackground) {
            $fields['backgroundId'] = $botBackground->value;
        }

        return new BotResult($this->core->call('imbot.v2.Bot.register', ['fields' => $fields]));
    }

    /**
     * Update an existing chat-bot.
     *
     * @param array<string, mixed> $fields Fields to update (code, properties, type, eventMode, webhookUrl, isHidden, isReactionsEnabled, isSupportOpenline, backgroundId).
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/bots/bot-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Bot.update',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/bots/bot-update.html',
        'Update an existing chat-bot'
    )]
    public function update(
        int $botId,
        array $fields,
        ?string $botToken = null,
    ): BotResult {
        $params = [
            'botId' => $botId,
            'fields' => $fields,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new BotResult($this->core->call('imbot.v2.Bot.update', $params));
    }

    /**
     * Get information about a chat-bot by its ID or code.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/bots/bot-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Bot.get',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/bots/bot-get.html',
        'Get information about a chat-bot'
    )]
    public function get(
        ?int $botId = null,
        ?string $code = null,
        ?string $botToken = null,
    ): BotResult {
        $params = [];

        if ($botId !== null) {
            $params['botId'] = $botId;
        }

        if ($code !== null) {
            $params['code'] = $code;
        }

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new BotResult($this->core->call('imbot.v2.Bot.get', $params));
    }

    /**
     * Get the list of chat-bots for the current application.
     *
     * @param array<string, mixed> $filter Filter parameters (e.g. ['type' => 'bot']).
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/bots/bot-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Bot.list',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/bots/bot-list.html',
        'Get the list of chat-bots for the current application'
    )]
    public function list(
        array $filter = [],
        int $limit = 50,
        int $offset = 0,
        ?string $botToken = null,
    ): BotsResult {
        $params = [
            'limit' => $limit,
            'offset' => $offset,
        ];

        if ($filter !== []) {
            $params['filter'] = $filter;
        }

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new BotsResult($this->core->call('imbot.v2.Bot.list', $params));
    }

    /**
     * Unregister (delete) a chat-bot.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/bots/bot-unregister.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Bot.unregister',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/bots/bot-unregister.html',
        'Unregister a chat-bot'
    )]
    public function unregister(
        int $botId,
        ?string $botToken = null,
    ): EmptyResult {
        $params = ['botId' => $botId];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new EmptyResult($this->core->call('imbot.v2.Bot.unregister', $params));
    }
}
