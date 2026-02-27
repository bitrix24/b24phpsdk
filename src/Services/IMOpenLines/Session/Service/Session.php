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

namespace Bitrix24\SDK\Services\IMOpenLines\Session\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\EmptyResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IMOpenLines\Session\Result\DialogResult;
use Bitrix24\SDK\Services\IMOpenLines\Session\Result\HistoryResult;
use Bitrix24\SDK\Services\IMOpenLines\Session\Result\OpenResult;
use Bitrix24\SDK\Services\IMOpenLines\Session\Result\PinAllResult;
use Bitrix24\SDK\Services\IMOpenLines\Session\Result\UnpinAllResult;

use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['imopenlines']))]
class Session extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Creates a lead based on the dialogue
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-crm-lead-create.html
     *
     * @param int $chatId Identifier of the chat
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.crm.lead.create',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-crm-lead-create.html',
        'Creates a lead based on the dialogue'
    )]
    public function createCrmLead(int $chatId): EmptyResult
    {
        return new EmptyResult(
            $this->core->call('imopenlines.crm.lead.create', [
                'CHAT_ID' => $chatId,
            ])
        );
    }

    /**
     * Retrieves information about the operator's dialogue (chat) in the open line
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-dialog-get.html
     *
     * @param int|null    $chatId    Numeric identifier of the chat
     * @param string|null $dialogId  Identifier of the dialogue
     * @param int|null    $sessionId Identifier of the session within the open channel
     * @param string|null $userCode  String identifier of the open channel user
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.dialog.get',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-dialog-get.html',
        "Retrieves information about the operator's dialogue (chat) in the open line"
    )]
    public function getDialog(?int $chatId = null, ?string $dialogId = null, ?int $sessionId = null, ?string $userCode = null): DialogResult
    {
        $params = [];

        if ($chatId !== null) {
            $params['CHAT_ID'] = $chatId;
        }

        if ($dialogId !== null) {
            $params['DIALOG_ID'] = $dialogId;
        }

        if ($sessionId !== null) {
            $params['SESSION_ID'] = $sessionId;
        }

        if ($userCode !== null) {
            $params['USER_CODE'] = $userCode;
        }

        return new DialogResult(
            $this->core->call('imopenlines.dialog.get', $params)
        );
    }

    /**
     * Starts a new dialogue based on a message
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-message-session-start.html
     *
     * @param int $chatId    Identifier of the chat
     * @param int $messageId Identifier of the message
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.message.session.start',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-message-session-start.html',
        'Starts a new dialogue based on a message'
    )]
    public function startMessageSession(int $chatId, int $messageId): EmptyResult
    {
        return new EmptyResult(
            $this->core->call('imopenlines.message.session.start', [
                'CHAT_ID' => $chatId,
                'MESSAGE_ID' => $messageId,
            ])
        );
    }

    /**
     * Rates the employee's performance in the dialogue
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-head-vote.html
     *
     * @param int         $sessionId Session identifier
     * @param int|null    $rating    Number of stars from 1 to 5
     * @param string|null $comment   Supervisor's comment
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.session.head.vote',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-head-vote.html',
        "Rates the employee's performance in the dialogue"
    )]
    public function voteHead(int $sessionId, ?int $rating = null, ?string $comment = null): EmptyResult
    {
        $params = ['SESSION_ID' => $sessionId];

        if ($rating !== null) {
            $params['RATING'] = $rating;
        }

        if ($comment !== null) {
            $params['COMMENT'] = $comment;
        }

        return new EmptyResult(
            $this->core->call('imopenlines.session.head.vote', $params)
        );
    }

    /**
     * Retrieves chat and dialogue messages
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-history-get.html
     *
     * @param int $chatId    Chat identifier
     * @param int $sessionId Session identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.session.history.get',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-history-get.html',
        'Retrieves chat and dialogue messages'
    )]
    public function getHistory(int $chatId, int $sessionId): HistoryResult
    {
        return new HistoryResult(
            $this->core->call('imopenlines.session.history.get', [
                'CHAT_ID' => $chatId,
                'SESSION_ID' => $sessionId,
            ])
        );
    }

    /**
     * Takes the dialogue from the current operator
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-intercept.html
     *
     * @param int $chatId Identifier of the chat
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.session.intercept',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-intercept.html',
        'Takes the dialogue from the current operator'
    )]
    public function intercept(int $chatId): EmptyResult
    {
        return new EmptyResult(
            $this->core->call('imopenlines.session.intercept', [
                'CHAT_ID' => $chatId,
            ])
        );
    }

    /**
     * Joins the dialogue
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-join.html
     *
     * @param int $chatId Identifier of the chat
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.session.join',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-join.html',
        'Joins the dialogue'
    )]
    public function join(int $chatId): EmptyResult
    {
        return new EmptyResult(
            $this->core->call('imopenlines.session.join', [
                'CHAT_ID' => $chatId,
            ])
        );
    }

    /**
     * Pins all available dialogues to the operator
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-mode-pin-all.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.session.mode.pinAll',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-mode-pin-all.html',
        'Pins all available dialogues to the operator'
    )]
    public function pinAll(): PinAllResult
    {
        return new PinAllResult(
            $this->core->call('imopenlines.session.mode.pinAll')
        );
    }

    /**
     * Pins or unpins the dialogue
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-mode-pin.html
     *
     * @param int  $chatId   Identifier of the chat
     * @param bool $activate Activation flag (true to pin, false to unpin)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.session.mode.pin',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-mode-pin.html',
        'Pins or unpins the dialogue'
    )]
    public function pin(int $chatId, bool $activate = false): EmptyResult
    {
        return new EmptyResult(
            $this->core->call('imopenlines.session.mode.pin', [
                'CHAT_ID' => $chatId,
                'ACTIVATE' => $activate ? 'Y' : 'N',
            ])
        );
    }

    /**
     * Switches the dialogue to "hidden" mode
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-mode-silent.html
     *
     * @param int  $chatId   Identifier of the chat
     * @param bool $activate Activation flag (true to enable silent mode, false to disable)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.session.mode.silent',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-mode-silent.html',
        'Switches the dialogue to "hidden" mode'
    )]
    public function setSilent(int $chatId, bool $activate = false): EmptyResult
    {
        return new EmptyResult(
            $this->core->call('imopenlines.session.mode.silent', [
                'CHAT_ID' => $chatId,
                'ACTIVATE' => $activate ? 'Y' : 'N',
            ])
        );
    }

    /**
     * Unpins all dialogues from the operator
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-mode-unpin-all.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.session.mode.unpinAll',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-mode-unpin-all.html',
        'Unpins all dialogues from the operator'
    )]
    public function unpinAll(): UnpinAllResult
    {
        return new UnpinAllResult(
            $this->core->call('imopenlines.session.mode.unpinAll')
        );
    }

    /**
     * Retrieves the chat by symbolic code
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-open.html
     *
     * @param string $userCode Chat code, can be found in ENTITY_ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.session.open',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-open.html',
        'Retrieves the chat by symbolic code'
    )]
    public function open(string $userCode): OpenResult
    {
        return new OpenResult(
            $this->core->call('imopenlines.session.open', [
                'USER_CODE' => $userCode,
            ])
        );
    }

    /**
     * Starts a new dialogue
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-start.html
     *
     * @param int $chatId Identifier of the chat
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.session.start',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-session-start.html',
        'Starts a new dialogue'
    )]
    public function start(int $chatId): EmptyResult
    {
        return new EmptyResult(
            $this->core->call('imopenlines.session.start', [
                'CHAT_ID' => $chatId,
            ])
        );
    }
}