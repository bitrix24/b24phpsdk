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

namespace Bitrix24\SDK\Services\IMOpenLines\CRMChat\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result\ChatListResult;
use Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result\ChatLastIdResult;
use Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result\ChatUserAddedResult;
use Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result\ChatUserDeletedResult;

use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['imopenlines']))]
class Chat extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Retrieves chats for a CRM object
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chats/imopenlines-crm-chat-get.html
     *
     * @param string $crmEntityType Type of CRM object: lead, deal, company, contact
     * @param int $crmEntity Identifier of the CRM object
     * @param bool|null $activeOnly Return only active chats. Y - only active chats (default), N - all chats
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.crm.chat.get',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chats/imopenlines-crm-chat-get.html',
        'Retrieves chats for a CRM object'
    )]
    public function get(string $crmEntityType, int $crmEntity, ?bool $activeOnly = null): ChatListResult
    {
        $params = [
            'CRM_ENTITY_TYPE' => $crmEntityType,
            'CRM_ENTITY' => $crmEntity,
        ];
        
        if ($activeOnly !== null) {
            $params['ACTIVE_ONLY'] = $activeOnly ? 'Y' : 'N';
        }
        
        return new ChatListResult(
            $this->core->call('imopenlines.crm.chat.get', $params)
        );
    }

    /**
     * Retrieves the ID of the last chat associated with a CRM entity
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chats/imopenlines-crm-chat-get-last-id.html
     *
     * @param string $crmEntityType Type of CRM entity: LEAD, DEAL, COMPANY, CONTACT
     * @param int $crmEntity Identifier of the CRM entity
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.crm.chat.getLastId',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chats/imopenlines-crm-chat-get-last-id.html',
        'Retrieves the ID of the last chat associated with a CRM entity'
    )]
    public function getLastId(string $crmEntityType, int $crmEntity): ChatLastIdResult
    {
        return new ChatLastIdResult(
            $this->core->call('imopenlines.crm.chat.getLastId', [
                'CRM_ENTITY_TYPE' => $crmEntityType,
                'CRM_ENTITY' => $crmEntity,
            ])
        );
    }

    /**
     * Adds a user to a CRM entity chat
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chats/imopenlines-crm-chat-user-add.html
     *
     * @param string $crmEntityType Type of CRM entity: lead, deal, company, contact
     * @param int $crmEntity Identifier of the CRM entity
     * @param int $userId Identifier of the user or bot to add to the chat
     * @param int|null $chatId Identifier of the chat. If not specified, the last chat linked to the CRM entity will be used
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.crm.chat.user.add',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chats/imopenlines-crm-chat-user-add.html',
        'Adds a user to a CRM entity chat'
    )]
    public function addUser(string $crmEntityType, int $crmEntity, int $userId, ?int $chatId = null): ChatUserAddedResult
    {
        $params = [
            'CRM_ENTITY_TYPE' => $crmEntityType,
            'CRM_ENTITY' => $crmEntity,
            'USER_ID' => $userId,
        ];
        
        if ($chatId !== null) {
            $params['CHAT_ID'] = $chatId;
        }
        
        return new ChatUserAddedResult(
            $this->core->call('imopenlines.crm.chat.user.add', $params)
        );
    }

    /**
     * Removes a user from the CRM entity chat
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chats/imopenlines-crm-chat-user-delete.html
     *
     * @param string $crmEntityType Type of CRM entity: lead, deal, company, contact
     * @param int $crmEntity Identifier of the CRM entity
     * @param int $userId Identifier of the user or bot to remove from the chat
     * @param int|null $chatId Identifier of the chat. If not specified, the last chat linked to the CRM entity will be used
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.crm.chat.user.delete',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/chats/imopenlines-crm-chat-user-delete.html',
        'Removes a user from the CRM entity chat'
    )]
    public function deleteUser(string $crmEntityType, int $crmEntity, int $userId, ?int $chatId = null): ChatUserDeletedResult
    {
        $params = [
            'CRM_ENTITY_TYPE' => $crmEntityType,
            'CRM_ENTITY' => $crmEntity,
            'USER_ID' => $userId,
        ];
        
        if ($chatId !== null) {
            $params['CHAT_ID'] = $chatId;
        }
        
        return new ChatUserDeletedResult(
            $this->core->call('imopenlines.crm.chat.user.delete', $params)
        );
    }
}