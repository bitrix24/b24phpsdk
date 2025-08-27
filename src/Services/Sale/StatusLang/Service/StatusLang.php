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

namespace Bitrix24\SDK\Services\Sale\StatusLang\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Sale\StatusLang\Result\LanguagesResult;
use Bitrix24\SDK\Services\Sale\StatusLang\Result\StatusLangAddResult;
use Bitrix24\SDK\Services\Sale\StatusLang\Result\StatusLangFieldsResult;
use Bitrix24\SDK\Services\Sale\StatusLang\Result\StatusLangsResult;
use Psr\Log\LoggerInterface;

/**
 * Class StatusLang - service for working with sale.statusLang.* methods
 *
 * @package Bitrix24\SDK\Services\Sale\StatusLang\Service
 */
#[ApiServiceMetadata(new Scope(['sale']))]
class StatusLang extends AbstractService
{
    /**
     * StatusLang constructor
     */
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Get list of available languages
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-statuslang-getlistlangs.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.statusLang.getListLangs',
        'https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-statuslang-getlistlangs.html',
        'Returns list of available languages'
    )]
    public function getListLangs(): LanguagesResult
    {
        return new LanguagesResult(
            $this->core->call('sale.statusLang.getListLangs', [])
        );
    }

    /**
     * Add a new status language
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-statuslang-add.html
     *
     * @param array $fields Fields for the new status language
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.statusLang.add',
        'https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-statuslang-add.html',
        'Adds a new status language'
    )]
    public function add(array $fields): StatusLangAddResult
    {
        return new StatusLangAddResult(
            $this->core->call(
                'sale.statusLang.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Get list of status languages
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-statuslang-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter parameters
     * @param array $order Order parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.statusLang.list',
        'https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-statuslang-list.html',
        'Returns a list of status languages'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): StatusLangsResult
    {
        return new StatusLangsResult(
            $this->core->call('sale.statusLang.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order
            ])
        );
    }

    /**
     * Delete status languages by filter
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-statuslang-deletebyfilter.html
     *
     * @param array $filter Filter parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.statusLang.deleteByFilter',
        'https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-statuslang-deletebyfilter.html',
        'Deletes status languages by filter'
    )]
    public function deleteByFilter(array $filter): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.statusLang.deleteByFilter', [
                'fields' => $filter
            ])
        );
    }

    /**
     * Get available status language fields
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-statuslang-getfields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.statusLang.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-statuslang-getfields.html',
        'Returns available fields and their settings'
    )]
    public function getFields(): StatusLangFieldsResult
    {
        return new StatusLangFieldsResult(
            $this->core->call('sale.statusLang.getFields', [])
        );
    }
}
