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

namespace Bitrix24\SDK\Services\Sale\PropertyVariant\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\PropertyVariant\Result\PropertyVariantAddResult;
use Bitrix24\SDK\Services\Sale\PropertyVariant\Result\PropertyVariantFieldsResult;
use Bitrix24\SDK\Services\Sale\PropertyVariant\Result\PropertyVariantsResult;
use Bitrix24\SDK\Services\Sale\PropertyVariant\Result\PropertyVariantResult;
use Bitrix24\SDK\Services\Sale\PropertyVariant\Result\PropertyVariantUpdateResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class PropertyVariant extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a variant of an order property.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-propertyvariant-add.html
     *
     * @param array $fields Field values for creating a property variant
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertyvariant.add',
        'https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-propertyvariant-add.html',
        'Adds a variant of an order property.'
    )]
    public function add(array $fields): PropertyVariantAddResult
    {
        return new PropertyVariantAddResult(
            $this->core->call('sale.propertyvariant.add', [
                'fields' => $fields,
            ])
        );
    }

    /**
     * Updates the fields of a property variant.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-propertyvariant-update.html
     *
     * @param int   $id     Property variant identifier
     * @param array $fields Field values for update
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertyvariant.update',
        'https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-propertyvariant-update.html',
        'Updates the fields of a property variant.'
    )]
    public function update(int $id, array $fields): PropertyVariantUpdateResult
    {
        return new PropertyVariantUpdateResult(
            $this->core->call('sale.propertyvariant.update', [
                'id' => $id,
                'fields' => $fields,
            ])
        );
    }

    /**
     * Returns the value of a property variant by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-propertyvariant-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertyvariant.get',
        'https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-propertyvariant-get.html',
        'Returns the property variant by ID.'
    )]
    public function get(int $id): PropertyVariantResult
    {
        return new PropertyVariantResult(
            $this->core->call('sale.propertyvariant.get', [
                'id' => $id,
            ])
        );
    }

    /**
     * Returns a list of property variants.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-propertyvariant-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter object
     * @param array $order  Sorting object
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertyvariant.list',
        'https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-propertyvariant-list.html',
        'Returns a list of property variants.'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): PropertyVariantsResult
    {
        return new PropertyVariantsResult(
            $this->core->call('sale.propertyvariant.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
            ])
        );
    }

    /**
     * Deletes a property variant.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-propertyvariant-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertyvariant.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-propertyvariant-delete.html',
        'Deletes a property variant.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.propertyvariant.delete', [
                'id' => $id,
            ])
        );
    }

    /**
     * Returns the fields and settings of property variants.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-propertyvariant-getfields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.propertyvariant.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-propertyvariant-getfields.html',
        'Returns the fields and settings of property variants.'
    )]
    public function getFields(): PropertyVariantFieldsResult
    {
        return new PropertyVariantFieldsResult(
            $this->core->call('sale.propertyvariant.getFields', [])
        );
    }
}
