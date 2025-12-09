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

namespace Bitrix24\SDK\Services\IMOpenLines\Config\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\IMOpenLines\Config\Result\GetResult;
use Bitrix24\SDK\Services\IMOpenLines\Config\Result\GetRevisionResult;
use Bitrix24\SDK\Services\IMOpenLines\Config\Result\OptionsResult;
use Bitrix24\SDK\Services\IMOpenLines\Config\Result\PathResult;

use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['imopenlines']))]
class Config extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new open line
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-config-add.html
     *
     * @param array{
     *   WELCOME_BOT_ENABLE: bool,
     *   WELCOME_BOT_JOIN: string,
     *   WELCOME_BOT_ID: int,
     *   WELCOME_BOT_TIME: int,
     *   WELCOME_BOT_LEFT: string,
     *   ACTIVE: bool,
     *   LINE_NAME: string,
     *   CRM: bool,
     *   CRM_CREATE: string,
     *   CRM_FORWARD: bool,
     *   CRM_SOURCE: string,
     *   CRM_TRANSFER_CHANGE: bool,
     *   QUEUE_TIME: int,
     *   NO_ANSWER_TIME: int,
     *   QUEUE_TYPE: string
     *   TIMEMAN: bool,
     *   CHECK_ONLINE: bool,
     *   CHECKING_OFFLINE: bool,
     *   WELCOME_MESSAGE: bool,
     *   WELCOME_MESSAGE_TEXT: string,
     *   AGREEMENT_MESSAGE: bool,
     *   AGREEMENT_ID: int,
     *   NO_ANSWER_RULE: string,
     *   NO_ANSWER_TEXT: string,
     *   WORKTIME_ENABLE: bool,
     *   WORKTIME_FROM: string,
     *   WORKTIME_TO: string,
     *   WORKTIME_TIMEZONE: string,
     *   WORKTIME_HOLIDAYS: string,
     *   WORKTIME_DAYOFF: array,
     *   WORKTIME_DAYOFF_RULE: string,
     *   WORKTIME_DAYOFF_TEXT: string,
     *   CLOSE_RULE: string,
     *   CLOSE_TEXT: string,
     *   FULL_CLOSE_TIME: int,
     *   AUTO_CLOSE_RULE: string,
     *   AUTO_CLOSE_TEXT: string,
     *   AUTO_CLOSE_TIME: int,
     *   VOTE_MESSAGE: bool,
     *   VOTE_CLOSING_DELAY: bool,
     *   VOTE_MESSAGE_1_TEXT: string,
     *   VOTE_MESSAGE_1_LIKE: string,
     *   VOTE_MESSAGE_1_DISLIKE: string,
     *   VOTE_MESSAGE_2_TEXT: string,
     *   VOTE_MESSAGE_2_LIKE: string,
     *   VOTE_MESSAGE_2_DISLIKE: string,
     *   QUICK_ANSWERS_IBLOCK_ID: int,
     *   LANGUAGE_ID: string,
     *   OPERATOR_DATA: string,
     *   DEFAULT_OPERATOR_DATA: array,
     *   QUEUE: array,
     *   QUEUE_OPERATOR_DATA: array
     * } $params
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.config.add',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-config-add.html',
        'Adds a new open line'
    )]
    public function add(array $params): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'imopenlines.config.add', 
                [
                    'PARAMS' => $params
                ]
            )
        );
    }

    /**
     * Deletes an open line
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-config-delete.html
     *
     * @param int $configId Open line ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.config.delete',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-config-delete.html',
        'Deletes an open line'
    )]
    public function delete(int $configId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('imopenlines.config.delete', [
                'CONFIG_ID' => $configId,
            ])
        );
    }

    /**
     * Retrieves an open line by Id
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-config-get.html
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
        'imopenlines.config.get',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-config-get.html',
        'Retrieves an open line by Id'
    )]
    public function get(int $configId, bool $withQueue=true, bool $showOffline = true): GetResult
    {
        return new GetResult(
            $this->core->call('imopenlines.config.get', 
                [
                    'CONFIG_ID' => $configId,
                    'WITH_QUEUE' => ($withQueue ? 'Y':'N'),
                    'SHOW_OFFLINE' => ($showOffline ? 'Y':'N'),
                ]
            )
        );
    }

    /**
     * Retrieves a list of open lines
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-config-list-get.html
     *
     * @param array|null $select
     * @param array|null $order
     * @param array|null $filter
     * @param array|null $options
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.config.list.get',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-config-list-get.html',
        'Retrieves a list of open lines'
    )]
    public function getList(?array $select = null, ?array $order = null, ?array $filter = null, ?array $options = null): OptionsResult
    {
        $params = [];
        $optionsParam = [];
        
        if ($select !== null) {
            $params['select'] = $select;
        }
        
        if ($order !== null) {
            $params['order'] = $order;
        }
        
        if ($filter !== null) {
            $params['filter'] = $filter;
        }
        
        if ($options !== null) {
            $optionsParam = $options;
        }
        
        return new OptionsResult(
            $this->core->call('imopenlines.config.list.get',
                [
                    'PARAMS' => $params,
                    'OPTIONS' => $optionsParam
                ]
            )
        );
    }

    /**
     * Gets a link to the public page of open lines in the account
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-config-path-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.config.path.get',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-config-path-get.html',
        'Gets a link to the public page of open lines in the account'
    )]
    public function getPath(): PathResult
    {
        return new PathResult(
            $this->core->call('imopenlines.config.path.get', [])
        );
    }

    /**
     * Modifies an open line
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-config-update.html
     *
     * @param string $id Connector unique identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.config.update',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-config-update.html',
        'Modifies an open line'
    )]
    public function update(int $id, array $params): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('imopenlines.config.update', [
                'CONFIG_ID' => $id, 
                'PARAMS' => $params
            ])
        );
    }

    /**
     * Connects an external open line to the account
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-network-join.html
     *
     * @param string $code Code for searching from the connectors page
     *
     * @return AddedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.network.join',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-network-join.html',
        'Connects an external open line to the account'
    )]
    public function joinNetwork(string $code): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('imopenlines.network.join', [
                'CODE' => $code
            ])
        );
    }

    /**
     * Retrieves information about API revisions
     *
     * @link https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-revision-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.revision.get',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/imopenlines-revision-get.html',
        'Retrieves information about API revisions'
    )]
    public function getRevision(): GetRevisionResult
    {
        return new GetRevisionResult(
            $this->core->call('imopenlines.revision.get', [])
        );
    }
    
}
