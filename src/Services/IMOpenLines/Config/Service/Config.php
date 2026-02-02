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
     * @param array<string, mixed> $params Configuration parameters for the open line.
     *                                     Available parameters include:
     *                                     - WELCOME_BOT_ENABLE (bool): Enable welcome bot
     *                                     - WELCOME_BOT_JOIN (string): Welcome bot join message  
     *                                     - ACTIVE (bool): Line active status
     *                                     - LINE_NAME (string): Open line name
     *                                     - CRM (bool): Enable CRM integration
     *                                     - QUEUE_TYPE (string): Queue type
     *                                     - LANGUAGE_ID (string): Language ID
     *                                     and many other configuration options
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
     * @param int  $configId     Open line configuration ID
     * @param bool $withQueue    Include queue information
     * @param bool $showOffline  Show offline operators
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
     * @param int   $id     Configuration ID to update
     * @param array $params Parameters to update
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
