<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\PropertyGroup\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\PropertyGroup\Result\PropertyGroupAddResult;
use Bitrix24\SDK\Services\Sale\PropertyGroup\Result\PropertyGroupFieldsResult;
use Bitrix24\SDK\Services\Sale\PropertyGroup\Result\PropertyGroupResult;
use Bitrix24\SDK\Services\Sale\PropertyGroup\Result\PropertyGroupsResult;
use Bitrix24\SDK\Services\Sale\PropertyGroup\Result\PropertyGroupUpdateResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class PropertyGroup extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new property group.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-propertygroup-add.html
     *
     * @param array{
     *   name: string,
     *   personTypeId: int,
     *   sort?: int,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertygroup.add',
        'https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-propertygroup-add.html',
        'Add new sale property group'
    )]
    public function add(array $fields): PropertyGroupAddResult
    {
        return new PropertyGroupAddResult(
            $this->core->call(
                'sale.propertygroup.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Updates an existing property group by id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-propertygroup-update.html
     *
     * @param array{
     *   name?: string,
     *   personTypeId?: int,
     *   sort?: int,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertygroup.update',
        'https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-propertygroup-update.html',
        'Update sale property group'
    )]
    public function update(int $id, array $fields): PropertyGroupUpdateResult
    {
        return new PropertyGroupUpdateResult(
            $this->core->call(
                'sale.propertygroup.update',
                [
                    'id' => $id,
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Returns a property group by id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-propertygroup-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertygroup.get',
        'https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-propertygroup-get.html',
        'Get sale property group by id'
    )]
    public function get(int $id): PropertyGroupResult
    {
        return new PropertyGroupResult(
            $this->core->call(
                'sale.propertygroup.get',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Returns list of property groups.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-property-group-list.html
     *
     * @param array<int, string>                                $select Select fields
     * @param array<string, scalar|array{0?: scalar, 1?:scalar}> $filter Filter map
     * @param array<string, 'asc'|'desc'|'ASC'|'DESC'>           $order  Order map
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertygroup.list',
        'https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-propertygroup-list.html',
        'Get list of sale property groups'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): PropertyGroupsResult
    {
        return new PropertyGroupsResult(
            $this->core->call(
                'sale.propertygroup.list',
                [
                    'select' => $select,
                    'filter' => $filter,
                    'order' => $order,
                ]
            )
        );
    }

    /**
     * Deletes a property group.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-propertygroup-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertygroup.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-propertygroup-delete.html',
        'Delete sale property group'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'sale.propertygroup.delete',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Returns available fields for property group entity.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-propertygroup-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertygroup.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-propertygroup-get-fields.html',
        'Get fields for sale property group'
    )]
    public function getFields(): PropertyGroupFieldsResult
    {
        return new PropertyGroupFieldsResult(
            $this->core->call('sale.propertygroup.getFields')
        );
    }
}
