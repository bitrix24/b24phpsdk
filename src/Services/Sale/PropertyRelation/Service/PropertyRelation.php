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

namespace Bitrix24\SDK\Services\Sale\PropertyRelation\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\PropertyRelation\Result\PropertyRelationAddedResult;
use Bitrix24\SDK\Services\Sale\PropertyRelation\Result\PropertyRelationFieldsResult;
use Bitrix24\SDK\Services\Sale\PropertyRelation\Result\PropertyRelationsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class PropertyRelation extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a property binding.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-relation/sale-property-relation-add.html
     *
     * @param array $fields Field values for creating the property binding
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertyRelation.add',
        'https://apidocs.bitrix24.com/api-reference/sale/property-relation/sale-property-relation-add.html',
        'Creates a new property binding.'
    )]
    public function add(array $fields): PropertyRelationAddedResult
    {
        return new PropertyRelationAddedResult(
            $this->core->call('sale.propertyRelation.add', [
                'fields' => $fields
            ])
        );
    }

    /**
     * Retrieves a list of property bindings.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-relation/sale-property-relation-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order Sort order
     * @param int   $start Pagination start (offset)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertyRelation.list',
        'https://apidocs.bitrix24.com/api-reference/sale/property-relation/sale-property-relation-list.html',
        'Retrieves a list of property bindings.'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], int $start = 0): PropertyRelationsResult
    {
        return new PropertyRelationsResult(
            $this->core->call('sale.propertyRelation.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'start' => $start
            ])
        );
    }

    /**
     * Removes the property relation.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-relation/sale-property-relation-delete-by-filter.html
     *
     * @param array $fields Field values for removing the property relation
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertyRelation.deleteByFilter',
        'https://apidocs.bitrix24.com/api-reference/sale/property-relation/sale-property-relation-delete-by-filter.html',
        'Removes a property relation.'
    )]
    public function deleteByFilter(array $fields): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.propertyRelation.deleteByFilter', [
                'fields' => $fields
            ])
        );
    }

    /**
     * Returns the available fields for property binding.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-relation/sale-property-relation-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertyRelation.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/property-relation/sale-property-relation-get-fields.html',
        'Retrieves the description of property binding fields.'
    )]
    public function getFields(): PropertyRelationFieldsResult
    {
        return new PropertyRelationFieldsResult(
            $this->core->call('sale.propertyRelation.getFields', [])
        );
    }
}
