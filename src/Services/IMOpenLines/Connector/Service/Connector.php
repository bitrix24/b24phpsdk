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

namespace Bitrix24\SDK\Services\IMOpenLines\Connector\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Result\ConnectorResult;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Result\ConnectorsResult;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Result\StatusResult;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Result\SendMessagesResult;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Result\ChatNameResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['imopenlines']))]
class Connector extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Register a new connector
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-register.html
     *
     * @param array{
     *   ID: string,
     *   NAME: string,
     *   ICON: array{
     *     DATA_IMAGE: string,
     *     COLOR?: string,
     *     SIZE?: string,
     *     POSITION?: string
     *   },
     *   PLACEMENT_HANDLER: string,
     *   ICON_DISABLED?: array,
     *   DEL_EXTERNAL_MESSAGES?: bool,
     *   EDIT_INTERNAL_MESSAGES?: bool,
     *   DEL_INTERNAL_MESSAGES?: bool,
     *   NEWSLETTER?: bool,
     *   NEED_SYSTEM_MESSAGES?: bool,
     *   NEED_SIGNATURE?: bool,
     *   CHAT_GROUP?: string,
     *   COMMENT?: string
     * } $connectorData
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imconnector.register',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-register.html',
        'Register a new connector'
    )]
    public function register(array $connectorData): ConnectorResult
    {
        return new ConnectorResult(
            $this->core->call('imconnector.register', $connectorData)
        );
    }

    /**
     * Activate or deactivate a connector
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-activate.html
     *
     * @param string $connector Connector ID
     * @param string $line      Open line ID
     * @param int    $active    1 to activate, 0 to deactivate
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imconnector.activate',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-activate.html',
        'Activate or deactivate a connector'
    )]
    public function activate(string $connector, string $line, int $active): ConnectorResult
    {
        return new ConnectorResult(
            $this->core->call('imconnector.activate', [
                'CONNECTOR' => $connector,
                'LINE' => $line,
                'ACTIVE' => $active
            ])
        );
    }

    /**
     * Get connector status
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-status.html
     *
     * @param string    $line        Open line ID
     * @param string    $connector   Connector ID
     * @param bool|null $error       Filter by error status
     * @param bool|null $configured  Filter by configured status
     * @param bool|null $status      Filter by status
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imconnector.status',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-status.html',
        'Get connector status'
    )]
    public function status(string $line, string $connector, ?bool $error = null, ?bool $configured = null, ?bool $status = null): StatusResult
    {
        $params = [
            'LINE' => $line,
            'CONNECTOR' => $connector
        ];

        if ($error !== null) {
            $params['ERROR'] = $error;
        }

        if ($configured !== null) {
            $params['CONFIGURED'] = $configured;
        }

        if ($status !== null) {
            $params['STATUS'] = $status;
        }

        return new StatusResult(
            $this->core->call('imconnector.status', $params)
        );
    }

    /**
     * Change connector settings
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-connector-data-set.html
     *
     * @param string $connector Connector identifier
     * @param string $line      Line identifier
     * @param array{
     *   id?: string,
     *   url?: string,
     *   url_im?: string,
     *   name?: string
     * } $data Data to save
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imconnector.connector.data.set',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-connector-data-set.html',
        'Change connector settings'
    )]
    public function setData(string $connector, string $line, array $data): ConnectorResult
    {
        return new ConnectorResult(
            $this->core->call('imconnector.connector.data.set', [
                'CONNECTOR' => $connector,
                'LINE' => $line,
                'DATA' => $data
            ])
        );
    }

    /**
     * Get list of connectors
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imconnector.list',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-list.html',
        'Get list of connectors'
    )]
    public function list(): ConnectorsResult
    {
        return new ConnectorsResult(
            $this->core->call('imconnector.list', [])
        );
    }

    /**
     * Unregister a connector
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-unregister.html
     *
     * @param string $id Connector unique identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imconnector.unregister',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-unregister.html',
        'Unregister a connector'
    )]
    public function unregister(string $id): ConnectorResult
    {
        return new ConnectorResult(
            $this->core->call('imconnector.unregister', [
                'ID' => $id
            ])
        );
    }

    /**
     * Send messages to Bitrix24
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-send-messages.html
     *
     * @param string $connector Connector ID
     * @param string $line      Open line ID
     * @param array  $messages  Array of messages
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imconnector.send.messages',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-send-messages.html',
        'Send messages to Bitrix24'
    )]
    public function sendMessages(string $connector, string $line, array $messages): SendMessagesResult
    {
        return new SendMessagesResult(
            $this->core->call('imconnector.send.messages', [
                'CONNECTOR' => $connector,
                'LINE' => $line,
                'MESSAGES' => $messages
            ])
        );
    }

    /**
     * Update sent messages
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-update-messages.html
     *
     * @param string $connector Connector ID
     * @param string $line      Open line ID
     * @param array  $messages  Array of messages
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imconnector.update.messages',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-update-messages.html',
        'Update sent messages'
    )]
    public function updateMessages(string $connector, string $line, array $messages): SendMessagesResult
    {
        return new SendMessagesResult(
            $this->core->call('imconnector.update.messages', [
                'CONNECTOR' => $connector,
                'LINE' => $line,
                'MESSAGES' => $messages
            ])
        );
    }

    /**
     * Delete sent messages
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-delete-messages.html
     *
     * @param string $connector Connector ID
     * @param string $line      Open line ID
     * @param array  $messages  Array of messages
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imconnector.delete.messages',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-delete-messages.html',
        'Delete sent messages'
    )]
    public function deleteMessages(string $connector, string $line, array $messages): ConnectorResult
    {
        return new ConnectorResult(
            $this->core->call('imconnector.delete.messages', [
                'CONNECTOR' => $connector,
                'LINE' => $line,
                'MESSAGES' => $messages
            ])
        );
    }

    /**
     * Update delivery status
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-send-status-delivery.html
     *
     * @param string $connector Connector ID
     * @param string $line      Open line ID
     * @param array  $messages  Array of messages
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imconnector.send.status.delivery',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-send-status-delivery.html',
        'Update delivery status'
    )]
    public function sendStatusDelivery(string $connector, string $line, array $messages): ConnectorResult
    {
        return new ConnectorResult(
            $this->core->call('imconnector.send.status.delivery', [
                'CONNECTOR' => $connector,
                'LINE' => $line,
                'MESSAGES' => $messages
            ])
        );
    }

    /**
     * Update reading status
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-send-status-reading.html
     *
     * @param string $connector Connector ID
     * @param string $line      Open line ID
     * @param array  $messages  Array of messages
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imconnector.send.status.reading',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-send-status-reading.html',
        'Update reading status'
    )]
    public function sendStatusReading(string $connector, string $line, array $messages): ConnectorResult
    {
        return new ConnectorResult(
            $this->core->call('imconnector.send.status.reading', [
                'CONNECTOR' => $connector,
                'LINE' => $line,
                'MESSAGES' => $messages
            ])
        );
    }

    /**
     * Set new chat name
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-chat-name-set.html
     *
     * @param string      $connector Connector identifier
     * @param string      $line      Open line identifier
     * @param string      $chatId    Chat identifier in external system
     * @param string      $name      New chat name
     * @param string|null $userId    User identifier (for non-group connectors)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imconnector.chat.name.set',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/imconnector/imconnector-chat-name-set.html',
        'Set new chat name'
    )]
    public function setChatName(string $connector, string $line, string $chatId, string $name, ?string $userId = null): ChatNameResult
    {
        $params = [
            'CONNECTOR' => $connector,
            'LINE' => $line,
            'CHAT_ID' => $chatId,
            'NAME' => $name
        ];

        if ($userId !== null) {
            $params['USER_ID'] = $userId;
        }

        return new ChatNameResult(
            $this->core->call('imconnector.chat.name.set', $params)
        );
    }
}