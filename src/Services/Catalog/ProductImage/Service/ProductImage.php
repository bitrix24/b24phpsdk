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

namespace Bitrix24\SDK\Services\Catalog\ProductImage\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\ProductImage\Result\ProductImageFieldsResult;
use Bitrix24\SDK\Services\Catalog\ProductImage\Result\ProductImageResult;
use Bitrix24\SDK\Services\Catalog\ProductImage\Result\ProductImagesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class ProductImage extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds an image to a product, parent product, variation, or service
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-add.html
     *
     * @param int $productId Product, parent product, variation, or service identifier
     * @param array{0: string, 1: string} $fileContent Two-element array: file name and file content encoded in base64
     * @param string|null $type Image type: DETAIL_PICTURE, PREVIEW_PICTURE, MORE_PHOTO (default: MORE_PHOTO)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productImage.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-add.html',
        'Adds an image to a product, parent product, variation, or service'
    )]
    public function add(int $productId, array $fileContent, ?string $type = null): ProductImageResult
    {
        $fields = ['productId' => $productId];
        if ($type !== null) {
            $fields['type'] = $type;
        }

        return new ProductImageResult(
            $this->core->call(
                'catalog.productImage.add',
                [
                    'fields' => $fields,
                    'fileContent' => $fileContent,
                ]
            )
        );
    }

    /**
     * Gets information about a product image by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-get.html
     *
     * @param int $productId Product, parent product, variation, or service identifier
     * @param int $id Image identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productImage.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-get.html',
        'Gets information about a product image by its identifier'
    )]
    public function get(int $productId, int $id): ProductImageResult
    {
        return new ProductImageResult(
            $this->core->call('catalog.productImage.get', ['productId' => $productId, 'id' => $id])
        );
    }

    /**
     * Gets the list of images for a product, parent product, variation, or service
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-list.html
     *
     * @param int $productId Product, parent product, variation, or service identifier
     * @param string[] $select Fields to select
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productImage.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-list.html',
        'Gets the list of images for a product, parent product, variation, or service'
    )]
    public function list(int $productId, array $select = []): ProductImagesResult
    {
        $params = ['productId' => $productId];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new ProductImagesResult($this->core->call('catalog.productImage.list', $params));
    }

    /**
     * Deletes a product image
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-delete.html
     *
     * @param int $productId Product, parent product, variation, or service identifier
     * @param int $id Image identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productImage.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-delete.html',
        'Deletes a product image'
    )]
    public function delete(int $productId, int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('catalog.productImage.delete', ['productId' => $productId, 'id' => $id])
        );
    }

    /**
     * Returns the description of product image fields
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productImage.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-get-fields.html',
        'Returns the description of product image fields'
    )]
    public function getFields(): ProductImageFieldsResult
    {
        return new ProductImageFieldsResult($this->core->call('catalog.productImage.getFields'));
    }
}
