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

namespace Bitrix24\SDK\Services\Sale\BasketProperty\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\BasketProperty\Result\BasketPropertiesResult;
use Bitrix24\SDK\Services\Sale\BasketProperty\Result\BasketPropertyAddResult;
use Bitrix24\SDK\Services\Sale\BasketProperty\Result\BasketPropertyFieldsResult;
use Bitrix24\SDK\Services\Sale\BasketProperty\Result\BasketPropertyResult;
use Bitrix24\SDK\Services\Sale\BasketProperty\Result\BasketPropertyUpdateResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class BasketProperty extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a basket property.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/sale/basket-properties/sale-basket-properties-add.html
     *
     * @param array $fields Field values for creating a basket property
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.basketproperties.add',
        'https://apidocs.bitrix24.ru/api-reference/sale/basket-properties/sale-basket-properties-add.html',
        'Adds a basket property.'
    )]
    public function add(array $fields): BasketPropertyAddResult
    {
        return new BasketPropertyAddResult(
            $this->core->call('sale.basketproperties.add', [
                'fields' => $fields,
            ])
        );
    }

    /**
     * Updates the fields of a basket property.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/sale/basket-properties/sale-basket-properties-update.html
     *
     * @param int   $id     Basket property identifier
     * @param array $fields Field values for update
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.basketproperties.update',
        'https://apidocs.bitrix24.ru/api-reference/sale/basket-properties/sale-basket-properties-update.html',
        'Updates the fields of a basket property.'
    )]
    public function update(int $id, array $fields): BasketPropertyUpdateResult
    {
        return new BasketPropertyUpdateResult(
            $this->core->call('sale.basketproperties.update', [
                'id' => $id,
                'fields' => $fields,
            ])
        );
    }

    /**
     * Returns the value of a basket property by its identifier.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/sale/basket-properties/sale-basket-properties-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.basketproperties.get',
        'https://apidocs.bitrix24.ru/api-reference/sale/basket-properties/sale-basket-properties-get.html',
        'Returns the basket property.'
    )]
    public function get(int $id): BasketPropertyResult
    {
        return new BasketPropertyResult(
            $this->core->call('sale.basketproperties.get', [
                'id' => $id,
            ])
        );
    }

    /**
     * Returns a list of basket properties.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/sale/basket-properties/sale-basket-properties-list.html
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
        'sale.basketproperties.list',
        'https://apidocs.bitrix24.ru/api-reference/sale/basket-properties/sale-basket-properties-list.html',
        'Returns a list of basket properties.'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], int $start = 0): BasketPropertiesResult
    {
        return new BasketPropertiesResult(
            $this->core->call('sale.basketproperties.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'start' => $start,
            ])
        );
    }

    /**
     * Deletes a basket property.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/sale/basket-properties/sale-basket-properties-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.basketproperties.delete',
        'https://apidocs.bitrix24.ru/api-reference/sale/basket-properties/sale-basket-properties-delete.html',
        'Deletes a basket property.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.basketproperties.delete', [
                'id' => $id,
            ])
        );
    }

    /**
     * Returns the fields of basket properties.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/sale/basket-properties/sale-basket-properties-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.basketproperties.getFields',
        'https://apidocs.bitrix24.ru/api-reference/sale/basket-properties/sale-basket-properties-get-fields.html',
        'Returns the fields of basket properties.'
    )]
    public function getFields(): BasketPropertyFieldsResult
    {
        return new BasketPropertyFieldsResult(
            $this->core->call('sale.basketproperties.getFields', [])
        );
    }
}
