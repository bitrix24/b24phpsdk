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

namespace Bitrix24\SDK\Services\Sale\Property\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\Property\Result\PropertiesResult;
use Bitrix24\SDK\Services\Sale\Property\Result\PropertyAddResult;
use Bitrix24\SDK\Services\Sale\Property\Result\PropertyFieldsByTypeResult;
use Bitrix24\SDK\Services\Sale\Property\Result\PropertyResult;
use Bitrix24\SDK\Services\Sale\Property\Result\PropertyUpdateResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class Property extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds an order property.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-add.html
     *
     * @param array $fields Field values for creating an order property
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.property.add',
        'https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-add.html',
        'Adds an order property.'
    )]
    public function add(array $fields): PropertyAddResult
    {
        return new PropertyAddResult(
            $this->core->call('sale.property.add', [
                'fields' => $fields,
            ])
        );
    }

    /**
     * Updates the fields of an order property.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-update.html
     *
     * @param int   $id     Order property identifier
     * @param array $fields Field values for update
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.property.update',
        'https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-update.html',
        'Updates the fields of an order property.'
    )]
    public function update(int $id, array $fields): PropertyUpdateResult
    {
        return new PropertyUpdateResult(
            $this->core->call('sale.property.update', [
                'id' => $id,
                'fields' => $fields,
            ])
        );
    }

    /**
     * Returns the value of an order property by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.property.get',
        'https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-get.html',
        'Returns the order property.'
    )]
    public function get(int $id): PropertyResult
    {
        return new PropertyResult(
            $this->core->call('sale.property.get', [
                'id' => $id,
            ])
        );
    }

    /**
     * Returns a list of order properties.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter object
     * @param array $order  Sorting object
     * @param int   $start  Pagination start (offset)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.property.list',
        'https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-list.html',
        'Returns a list of order properties.'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], int $start = 0): PropertiesResult
    {
        return new PropertiesResult(
            $this->core->call('sale.property.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'start' => $start,
            ])
        );
    }

    /**
     * Deletes an order property.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.property.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-delete.html',
        'Deletes an order property.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.property.delete', [
                'id' => $id,
            ])
        );
    }

    /**
     * Returns the fields and settings of an order property for a specific property type.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-get-fields-by-type.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.property.getFieldsByType',
        'https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-get-fields-by-type.html',
        'Returns the fields and settings of an order property for a specific property type.'
    )]
    public function getFieldsByType(string $type): PropertyFieldsByTypeResult
    {
        return new PropertyFieldsByTypeResult(
            $this->core->call('sale.property.getFieldsByType', [
                'type' => $type,
            ])
        );
    }
}
