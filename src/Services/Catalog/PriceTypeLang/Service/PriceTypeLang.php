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

namespace Bitrix24\SDK\Services\Catalog\PriceTypeLang\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Result\LanguagesResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Result\PriceTypeLangFieldsResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Result\PriceTypeLangResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Result\PriceTypeLangsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class PriceTypeLang extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new price type name translation
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceTypeLang.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-add.html',
        'Adds a new price type name translation'
    )]
    public function add(array $fields): PriceTypeLangResult
    {
        return new PriceTypeLangResult($this->core->call('catalog.priceTypeLang.add', ['fields' => $fields]));
    }

    /**
     * Updates a price type name translation by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceTypeLang.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-update.html',
        'Updates a price type name translation by its identifier'
    )]
    public function update(int $id, array $fields): PriceTypeLangResult
    {
        return new PriceTypeLangResult(
            $this->core->call('catalog.priceTypeLang.update', ['id' => $id, 'fields' => $fields])
        );
    }

    /**
     * Returns price type name translation information by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceTypeLang.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-get.html',
        'Returns price type name translation information by identifier'
    )]
    public function get(int $id): PriceTypeLangResult
    {
        return new PriceTypeLangResult($this->core->call('catalog.priceTypeLang.get', ['id' => $id]));
    }

    /**
     * Returns a list of price type name translations by filter
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceTypeLang.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-list.html',
        'Returns a list of price type name translations by filter'
    )]
    public function list(array $select = [], array $filter = []): PriceTypeLangsResult
    {
        return new PriceTypeLangsResult(
            $this->core->call('catalog.priceTypeLang.list', ['select' => $select, 'filter' => $filter])
        );
    }

    /**
     * Deletes a price type name translation by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceTypeLang.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-delete.html',
        'Deletes a price type name translation by identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.priceTypeLang.delete', ['id' => $id]));
    }

    /**
     * Returns the list of languages available for price type translation
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-get-languages.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceTypeLang.getLanguages',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-get-languages.html',
        'Returns the list of languages available for price type translation'
    )]
    public function getLanguages(): LanguagesResult
    {
        return new LanguagesResult($this->core->call('catalog.priceTypeLang.getLanguages'));
    }

    /**
     * Returns the fields of a price type name translation
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.priceTypeLang.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/price-type/price-type-lang/catalog-price-type-lang-get-fields.html',
        'Returns the fields of a price type name translation'
    )]
    public function getFields(): PriceTypeLangFieldsResult
    {
        return new PriceTypeLangFieldsResult($this->core->call('catalog.priceTypeLang.getFields'));
    }
}
