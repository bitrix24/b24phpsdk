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

namespace Bitrix24\SDK\Services\Sale\BasketItem\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\BasketItem\Result\AddedBasketItemResult;
use Bitrix24\SDK\Services\Sale\BasketItem\Result\UpdatedBasketItemResult;
use Bitrix24\SDK\Services\Sale\BasketItem\Result\BasketItemResult;
use Bitrix24\SDK\Services\Sale\BasketItem\Result\BasketItemsResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Sale\BasketItem\Result\FieldsBasketItemResult;
use Bitrix24\SDK\Services\Sale\BasketItem\Result\AddedCatalogProductResult;
use Bitrix24\SDK\Services\Sale\BasketItem\Result\UpdatedCatalogProductResult;
use Bitrix24\SDK\Services\Sale\BasketItem\Result\FieldsCatalogProductResult;
use Psr\Log\LoggerInterface;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;

#[ApiServiceMetadata(new Scope(['sale']))]
class BasketItem extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Add new basket item to an existing order
     *
     * Required fields:
     * - orderId (int) Order identifier
     * - productId (int) Product/variation identifier
     * - currency (string) Currency of the price, must match order currency
     * - quantity (float) Quantity of the product
     * - name (string) Product name if product doesn't exist on the site
     *
     * Optional fields:
     * - sort (int) Position in the list of order items
     * - price (float) Price including markups and discounts
     * - basePrice (float) Original price excluding markups and discounts
     * - discountPrice (float) Amount of the final discount or markup
     * - customPrice (string) Is the price specified manually (Y/N)
     * And others, see documentation
     *
     * @param array $fields Array of basket item fields
     *
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-add.html
     */
    #[ApiEndpointMetadata(
        'sale.basketitem.add',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-add.html',
        'Add a new basket item to an existing order'
    )]
    public function add(array $fields): AddedBasketItemResult
    {
        $response = $this->core->call('sale.basketitem.add', [
            'fields' => $fields
        ]);

        return new AddedBasketItemResult($response);
    }

    /**
     * Update an existing basket item in an order
     *
     * Required fields:
     * - id (int) Identifier of the order item
     * - fields (array) Values of the fields to be modified
     *
     * Available fields to update:
     * - sort (int) Position in the list of order items
     * - price (float) Price including markups and discounts
     * - basePrice (float) Original price excluding markups and discounts
     * - discountPrice (float) Amount of the final discount or markup
     * - quantity (float) Quantity of the product
     * - xmlId (string) External code of the basket item
     * - name (string) Name of the product
     * - weight (int) Weight of the product
     * - dimensions (string) Dimensions of the product (serialized array)
     * - measureCode (string) Code of the product's unit of measure
     * - measureName (string) Name of the unit of measure
     * - canBuy (string) Availability flag (Y/N)
     * - vatRate (float) Tax rate in percentage
     * - vatIncluded (string) Flag indicating whether VAT is included (Y/N)
     * - catalogXmlId (string) External code of the product catalog
     * - productXmlId (string) External code of the product
     *
     * @param int   $id     Basket item identifier
     * @param array $fields Array of fields to update
     *
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-update.html
     */
    #[ApiEndpointMetadata(
        'sale.basketitem.update',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-update.html',
        'Update an existing basket item in an order'
    )]
    public function update(int $id, array $fields): UpdatedBasketItemResult
    {
        $response = $this->core->call('sale.basketitem.update', [
            'id' => $id,
            'fields' => $fields
        ]);

        return new UpdatedBasketItemResult($response);
    }

    /**
     * Get information about a specific basket item
     *
     * @param int $id Identifier of the basket item
     *
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-get.html
     */
    #[ApiEndpointMetadata(
        'sale.basketitem.get',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-get.html',
        'Get information about a specific basket item'
    )]
    public function get(int $id): BasketItemResult
    {
        $response = $this->core->call('sale.basketitem.get', [
            'id' => $id
        ]);

        return new BasketItemResult($response);
    }

    /**
     * Get list of basket items with optional filtering and sorting
     *
     * @param array|null  $select Fields to select
     * @param array|null  $filter Filter criteria
     * @param array|null  $order  Sort order
     * @param int|null    $start  Offset for pagination
     *
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-list.html
     */
    #[ApiEndpointMetadata(
        'sale.basketitem.list',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-list.html',
        'Get list of basket items with optional filtering and sorting'
    )]
    public function list(?array $select = null, ?array $filter = null, ?array $order = null, ?int $start = null): BasketItemsResult
    {
        $params = [];

        if ($select !== null) {
            $params['select'] = $select;
        }

        if ($filter !== null) {
            $params['filter'] = $filter;
        }

        if ($order !== null) {
            $params['order'] = $order;
        }

        if ($start !== null) {
            $params['start'] = $start;
        }

        $response = $this->core->call('sale.basketitem.list', $params);

        return new BasketItemsResult($response);
    }

    /**
     * Delete a basket item from an order
     *
     * @param int $id Identifier of the basket item
     *
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-delete.html
     */
    #[ApiEndpointMetadata(
        'sale.basketitem.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-delete.html',
        'Delete a basket item from an order'
    )]
    public function delete(int $id): DeletedItemResult
    {
        $response = $this->core->call('sale.basketitem.delete', [
            'id' => $id
        ]);

        return new DeletedItemResult($response);
    }

    /**
     * Get available fields for basket item with their descriptions
     *
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-get-fields.html
     */
    #[ApiEndpointMetadata(
        'sale.basketitem.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-get-fields.html',
        'Get available fields for basket item with their descriptions'
    )]
    public function getFields(): FieldsBasketItemResult
    {
        $response = $this->core->call('sale.basketitem.getFields');

        return new FieldsBasketItemResult($response);
    }

    /**
     * Add a product from catalog to basket of an existing order
     *
     * Required fields:
     * - orderId (int) Order identifier
     * - productId (int) Product/variation identifier
     * - currency (string) Currency of the price, must match order currency
     * - quantity (float) Quantity of the product
     * - name (string) Product name
     *
     * Optional fields:
     * - sort (int) Position in the list of order items
     * - price (float) Price including markups and discounts
     * - basePrice (float) Original price excluding markups and discounts
     * - discountPrice (float) Amount of the final discount or markup
     * - customPrice (string) Is the price specified manually (Y/N)
     * And others, see documentation
     *
     * @param array $fields Array of basket item fields
     *
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-add-catalog-product.html
     */
    #[ApiEndpointMetadata(
        'sale.basketitem.addCatalogProduct',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-add-catalog-product.html',
        'Add a product from catalog to basket of an existing order'
    )]
    public function addCatalogProduct(array $fields): AddedCatalogProductResult
    {
        $response = $this->core->call('sale.basketitem.addCatalogProduct', [
            'fields' => $fields
        ]);

        return new AddedCatalogProductResult($response);
    }

    /**
     * Update a catalog product in an order's basket
     *
     * Required fields:
     * - id (int) Identifier of the basket item
     * - quantity (float) Quantity of the product
     *
     * Optional fields:
     * - sort (int) Position in the order item list
     * - xmlId (string) External code of the basket item
     *
     * @param int   $id     Basket item identifier
     * @param array $fields Array of fields to update
     *
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-update-catalog-product.html
     */
    #[ApiEndpointMetadata(
        'sale.basketitem.updateCatalogProduct',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-update-catalog-product.html',
        "Update a catalog product in an order's basket"
    )]
    public function updateCatalogProduct(int $id, array $fields): UpdatedCatalogProductResult
    {
        $response = $this->core->call('sale.basketitem.updateCatalogProduct', [
            'id' => $id,
            'fields' => $fields
        ]);

        return new UpdatedCatalogProductResult($response);
    }

    /**
     * Get available fields for basket item (product from catalog) with their descriptions
     *
     * Unlike getFields(), this method returns the minimum necessary list of fields
     * specifically for working with products from the catalog module
     *
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-get-catalog-product-fields.html
     */
    #[ApiEndpointMetadata(
        'sale.basketitem.getFieldsCatalogProduct',
        'https://apidocs.bitrix24.com/api-reference/sale/basket-item/sale-basket-item-get-catalog-product-fields.html',
        'Get available fields for basket item (product from catalog) with their descriptions'
    )]
    public function getFieldsCatalogProduct(): FieldsCatalogProductResult
    {
        $response = $this->core->call('sale.basketitem.getFieldsCatalogProduct');

        return new FieldsCatalogProductResult($response);
    }
}
