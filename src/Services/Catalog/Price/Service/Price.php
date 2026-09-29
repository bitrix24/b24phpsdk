<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Price\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Price\Result\PriceFieldsResult;
use Bitrix24\SDK\Services\Catalog\Price\Result\PriceResult;
use Bitrix24\SDK\Services\Catalog\Price\Result\PricesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Price extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new product price
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.price.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-add.html',
        'Adds a new product price'
    )]
    public function add(array $fields): PriceResult
    {
        return new PriceResult($this->core->call('catalog.price.add', ['fields' => $fields]));
    }

    /**
     * Updates the price of a product by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.price.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-update.html',
        'Updates the price of a product by its identifier'
    )]
    public function update(int $id, array $fields): PriceResult
    {
        return new PriceResult($this->core->call('catalog.price.update', ['id' => $id, 'fields' => $fields]));
    }

    /**
     * Adds, updates and deletes a product price collection in a single request
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-modify.html
     *
     * @param array<int, array{catalogGroupId: int, currency: string, price: float, id?: int}> $prices
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.price.modify',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-modify.html',
        'Adds, updates and deletes a product price collection in a single request'
    )]
    public function modify(int $productId, array $prices): PricesResult
    {
        return new PricesResult(
            $this->core->call(
                'catalog.price.modify',
                ['fields' => ['product' => ['id' => $productId, 'prices' => $prices]]]
            )
        );
    }

    /**
     * Returns the fields of a product price
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.price.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-get-fields.html',
        'Returns the fields of a product price'
    )]
    public function getFields(): PriceFieldsResult
    {
        return new PriceFieldsResult($this->core->call('catalog.price.getFields'));
    }

    /**
     * Returns a list of product prices by filter
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.price.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-list.html',
        'Returns a list of product prices by filter'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): PricesResult
    {
        return new PricesResult(
            $this->core->call(
                'catalog.price.list',
                ['select' => $select, 'filter' => $filter, 'order' => $order]
            )
        );
    }

    /**
     * Returns product price information by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.price.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-get.html',
        'Returns product price information by identifier'
    )]
    public function get(int $id): PriceResult
    {
        return new PriceResult($this->core->call('catalog.price.get', ['id' => $id]));
    }

    /**
     * Deletes a product price by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.price.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/price/catalog-price-delete.html',
        'Deletes a product price by identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.price.delete', ['id' => $id]));
    }
}
