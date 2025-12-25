<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IMOpenLines\Bot\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Core\Result\EmptyResult;

use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['imopenlines', 'imbot']))]
class Bot extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Sends an automatic message via the chatbot
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chat-bots/imopenlines-bot-session-message-send.html
     *
     * @param int    $chatId  Identifier of the chat that the current operator is taking
     * @param string $message The message being sent
     * @param string $name    Type of message: WELCOME — welcome message, DEFAULT — regular message
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.bot.session.message.send',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chat-bots/imopenlines-bot-session-message-send.html',
        'Sends an automatic message via the chatbot'
    )]
    public function sendMessage(int $chatId, string $message, string $name = 'DEFAULT'): EmptyResult
    {
        return new EmptyResult(
            $this->core->call('imopenlines.bot.session.message.send', [
                'CHAT_ID' => $chatId,
                'MESSAGE' => $message,
                'NAME' => $name,
            ])
        );
    }

    /**
     * Switches the conversation to a free operator
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chat-bots/imopenlines-bot-session-operator.html
     *
     * @param int $chatId Chat identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.bot.session.operator',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chat-bots/imopenlines-bot-session-operator.html',
        'Switches the conversation to a free operator'
    )]
    public function transferToOperator(int $chatId): EmptyResult
    {
        return new EmptyResult(
            $this->core->call('imopenlines.bot.session.operator', [
                'CHAT_ID' => $chatId,
            ])
        );
    }

    /**
     * Transfers the conversation to a specific operator by user ID
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chat-bots/imopenlines-bot-session-transfer.html
     *
     * @param int    $chatId Chat identifier
     * @param int    $userId User identifier to whom the conversation is being redirected
     * @param string $leave  Y/N. If N is specified, the chatbot will not leave this chat after redirection and will remain until the user confirms
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.bot.session.transfer',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chat-bots/imopenlines-bot-session-transfer.html',
        'Transfers the conversation to a specific operator by user ID'
    )]
    public function transferToUser(int $chatId, int $userId, string $leave = 'N'): EmptyResult
    {
        return new EmptyResult(
            $this->core->call('imopenlines.bot.session.transfer', [
                'CHAT_ID' => $chatId,
                'USER_ID' => $userId,
                'LEAVE' => $leave,
            ])
        );
    }

    /**
     * Transfers the conversation to another open line queue
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chat-bots/imopenlines-bot-session-transfer.html
     *
     * @param int    $chatId  Chat identifier
     * @param int    $queueId Queue identifier to which the conversation is being redirected
     * @param string $leave   Y/N. If N is specified, the chatbot will not leave this chat after redirection and will remain until the user confirms
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.bot.session.transfer',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chat-bots/imopenlines-bot-session-transfer.html',
        'Transfers the conversation to another open line queue'
    )]
    public function transferToQueue(int $chatId, int $queueId, string $leave = 'N'): EmptyResult
    {
        return new EmptyResult(
            $this->core->call('imopenlines.bot.session.transfer', [
                'CHAT_ID' => $chatId,
                'QUEUE_ID' => $queueId,
                'LEAVE' => $leave,
            ])
        );
    }

    /**
     * Ends the current session
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chat-bots/imopenlines-bot-session-finish.html
     *
     * @param int $chatId Chat identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.bot.session.finish',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chat-bots/imopenlines-bot-session-finish.html',
        'Ends the current session'
    )]
    public function finishSession(int $chatId): EmptyResult
    {
        return new EmptyResult(
            $this->core->call('imopenlines.bot.session.finish', [
                'CHAT_ID' => $chatId,
            ])
        );
    }
}