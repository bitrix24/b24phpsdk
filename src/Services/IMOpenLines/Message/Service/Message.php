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

namespace Bitrix24\SDK\Services\IMOpenLines\Message\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IMOpenLines\Message\Result\CrmMessageAddResult;
use Bitrix24\SDK\Services\IMOpenLines\Message\Result\QuickSaveResult;
use Bitrix24\SDK\Services\IMOpenLines\Message\Result\SessionStartResult;

use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['imopenlines']))]
class Message extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Sends a message to the open line on behalf of an employee or bot in a chat linked to a CRM entity
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/messages/imopenlines-crm-message-add.html
     *
     * @param string $crmEntityType Type of the CRM object: lead|deal|company|contact
     * @param int $crmEntity Identifier of the CRM entity linked to the chat
     * @param int $userId Identifier of the message sender — user or bot
     * @param int $chatId Identifier of the open channel chat linked to the CRM entity
     * @param string $message The text of the message that will be displayed in the chat
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.crm.message.add',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/messages/imopenlines-crm-message-add.html',
        'Sends a message to the open line on behalf of an employee or bot in a chat linked to a CRM entity.'
    )]
    public function addCrmMessage(string $crmEntityType, int $crmEntity, int $userId, int $chatId, string $message): CrmMessageAddResult
    {
        return new CrmMessageAddResult(
            $this->core->call('imopenlines.crm.message.add', [
                'CRM_ENTITY_TYPE' => $crmEntityType,
                'CRM_ENTITY' => $crmEntity,
                'USER_ID' => $userId,
                'CHAT_ID' => $chatId,
                'MESSAGE' => $message,
            ])
        );
    }

    /**
     * Saves a message from the open line chat to the list of quick answers
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/messages/imopenlines-message-quick-save.html
     *
     * @param int $chatId Identifier of the open line chat from which the message needs to be saved
     * @param int $messageId Identifier of the message to be added to quick answers
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.message.quick.save',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/messages/imopenlines-message-quick-save.html',
        'Saves a message from the open line chat to the list of quick answers.'
    )]
    public function quickSave(int $chatId, int $messageId): QuickSaveResult
    {
        return new QuickSaveResult(
            $this->core->call('imopenlines.message.quick.save', [
                'CHAT_ID' => $chatId,
                'MESSAGE_ID' => $messageId,
            ])
        );
    }

    /**
     * Starts a new dialogue session based on a message
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-message-session-start.html
     *
     * @param int $chatId Identifier of the chat
     * @param int $messageId Identifier of the message
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.message.session.start',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/sessions/imopenlines-message-session-start.html',
        'Starts a new dialogue session based on a message.'
    )]
    public function sessionStart(int $chatId, int $messageId): SessionStartResult
    {
        return new SessionStartResult(
            $this->core->call('imopenlines.message.session.start', [
                'CHAT_ID' => $chatId,
                'MESSAGE_ID' => $messageId,
            ])
        );
    }
}