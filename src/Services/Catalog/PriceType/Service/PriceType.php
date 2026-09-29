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

namespace Bitrix24\SDK\Services\Catalog\PriceType\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\PriceType\Result\PriceTypeFieldsResult;
use Bitrix24\SDK\Services\Catalog\PriceType\Result\PriceTypeResult;
use Bitrix24\SDK\Services\Catalog\PriceType\Result\PriceTypesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class PriceType extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new price type
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceType.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-add.html',
        'Adds a new price type'
    )]
    public function add(array $fields): PriceTypeResult
    {
        return new PriceTypeResult($this->core->call('catalog.priceType.add', ['fields' => $fields]));
    }

    /**
     * Updates a price type by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceType.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-update.html',
        'Updates a price type by its identifier'
    )]
    public function update(int $id, array $fields): PriceTypeResult
    {
        return new PriceTypeResult($this->core->call('catalog.priceType.update', ['id' => $id, 'fields' => $fields]));
    }

    /**
     * Returns price type information by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceType.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-get.html',
        'Returns price type information by identifier'
    )]
    public function get(int $id): PriceTypeResult
    {
        return new PriceTypeResult($this->core->call('catalog.priceType.get', ['id' => $id]));
    }

    /**
     * Returns a list of price types by filter
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceType.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-list.html',
        'Returns a list of price types by filter'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): PriceTypesResult
    {
        return new PriceTypesResult(
            $this->core->call(
                'catalog.priceType.list',
                ['select' => $select, 'filter' => $filter, 'order' => $order]
            )
        );
    }

    /**
     * Deletes a price type by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceType.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-delete.html',
        'Deletes a price type by identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.priceType.delete', ['id' => $id]));
    }

    /**
     * Returns the fields of a price type
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceType.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/catalog-price-type-get-fields.html',
        'Returns the fields of a price type'
    )]
    public function getFields(): PriceTypeFieldsResult
    {
        return new PriceTypeFieldsResult($this->core->call('catalog.priceType.getFields'));
    }
}
