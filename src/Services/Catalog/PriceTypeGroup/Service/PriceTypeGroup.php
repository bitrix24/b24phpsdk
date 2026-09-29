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

namespace Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Result\PriceTypeGroupFieldsResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Result\PriceTypeGroupResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Result\PriceTypeGroupsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class PriceTypeGroup extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a price type binding to a purchasing group
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-group/catalog-price-type-group-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceTypeGroup.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-group/catalog-price-type-group-add.html',
        'Adds a price type binding to a purchasing group'
    )]
    public function add(array $fields): PriceTypeGroupResult
    {
        return new PriceTypeGroupResult($this->core->call('catalog.priceTypeGroup.add', ['fields' => $fields]));
    }

    /**
     * Returns a list of price type ↔ purchasing group bindings by filter
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-group/catalog-price-type-group-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceTypeGroup.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-group/catalog-price-type-group-list.html',
        'Returns a list of price type purchasing group bindings by filter'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], int $start = 0): PriceTypeGroupsResult
    {
        return new PriceTypeGroupsResult(
            $this->core->call(
                'catalog.priceTypeGroup.list',
                ['select' => $select, 'filter' => $filter, 'order' => $order, 'start' => $start]
            )
        );
    }

    /**
     * Deletes a price type ↔ purchasing group binding by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-group/catalog-price-type-group-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceTypeGroup.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-group/catalog-price-type-group-delete.html',
        'Deletes a price type purchasing group binding by identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.priceTypeGroup.delete', ['id' => $id]));
    }

    /**
     * Returns the fields of a price type ↔ purchasing group binding
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-group/catalog-price-type-group-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceTypeGroup.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-group/catalog-price-type-group-get-fields.html',
        'Returns the fields of a price type purchasing group binding'
    )]
    public function getFields(): PriceTypeGroupFieldsResult
    {
        return new PriceTypeGroupFieldsResult($this->core->call('catalog.priceTypeGroup.getFields'));
    }
}
