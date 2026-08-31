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

namespace Bitrix24\SDK\Services\Catalog\Product\Offer\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Product\Offer\Result\OfferResult;
use Bitrix24\SDK\Services\Catalog\Product\Offer\Result\OffersResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Offer extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * The method adds a product variation (offer) to the commercial catalog.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-add.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.offer.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-add.html',
        'The method adds a product variation (offer) to the commercial catalog.'
    )]
    public function add(array $fields): OfferResult
    {
        return new OfferResult($this->core->call('catalog.product.offer.add', ['fields' => $fields]));
    }

    /**
     * The method updates a product variation (offer) in the commercial catalog by its identifier.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-update.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.offer.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-update.html',
        'The method updates a product variation (offer) in the commercial catalog by its identifier.'
    )]
    public function update(int $offerId, array $fields): OfferResult
    {
        return new OfferResult($this->core->call('catalog.product.offer.update', [
            'id' => $offerId,
            'fields' => $fields,
        ]));
    }

    /**
     * The method gets field values of a product variation (offer) by ID.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-get.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.offer.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-get.html',
        'The method gets field values of a product variation (offer) by ID.'
    )]
    public function get(int $offerId): OfferResult
    {
        return new OfferResult($this->core->call('catalog.product.offer.get', ['id' => $offerId]));
    }

    /**
     * The method gets a list of product variations (offers) by filter.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-list.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.offer.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-list.html',
        'The method gets a list of product variations (offers) by filter.'
    )]
    public function list(array $select, array $filter, array $order = []): OffersResult
    {
        return new OffersResult($this->core->call('catalog.product.offer.list', [
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
        ]));
    }

    /**
     * The method deletes a product variation (offer) by ID.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-delete.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.offer.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-delete.html',
        'The method deletes a product variation (offer) by ID.'
    )]
    public function delete(int $offerId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.product.offer.delete', ['id' => $offerId]));
    }

    /**
     * The method returns product variation (offer) fields by filter.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-get-fields-by-filter.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.offer.getFieldsByFilter',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-get-fields-by-filter.html',
        'The method returns product variation (offer) fields by filter.'
    )]
    public function fieldsByFilter(int $iblockId): FieldsResult
    {
        return new FieldsResult($this->core->call('catalog.product.offer.getFieldsByFilter', [
            'filter' => ['iblockId' => $iblockId],
        ]));
    }

    /**
     * The method downloads product variation (offer) files by the given parameters.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-download.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.offer.download',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/offer/catalog-product-offer-download.html',
        'The method downloads product variation (offer) files by the given parameters.'
    )]
    public function download(int $fileId, int $productId, string $fieldName): Response
    {
        return $this->core->call('catalog.product.offer.download', [
            'fields' => [
                'fileId' => $fileId,
                'productId' => $productId,
                'fieldName' => $fieldName,
            ],
        ]);
    }
}
