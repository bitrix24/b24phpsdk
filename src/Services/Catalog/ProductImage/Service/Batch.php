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

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\ProductImage\Result\ProductImageItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch adding product images
     *
     * @param array<int, array{
     *     fields: array{productId: int, type?: string},
     *     fileContent: array{0: string, 1: string}
     * }> $productImages
     *
     * @return Generator<int, ProductImageItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productImage.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-add.html',
        'Batch adding product images'
    )]
    public function add(array $productImages): Generator
    {
        /** @var ResponseData $item */
        foreach ($this->batch->addEntityItems('catalog.productImage.add', $productImages) as $key => $item) {
            yield $key => new ProductImageItemResult($item->getResult()['productImage']);
        }
    }

    /**
     * Batch delete product images
     *
     * @param array<int, array{productId: int, id: int}> $items
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productImage.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-delete.html',
        'Batch delete product images'
    )]
    public function delete(array $items): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.productImage.delete', $items) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch list of product images for several products
     *
     * catalog.productImage.list has no filter/order/start parameters — it is scoped to a
     * single product, not paginated. This method registers one list command per product id
     * in a single batch round-trip and yields the images found for each product.
     *
     * @param int[] $productIds
     * @param string[] $select Fields to select
     *
     * @return Generator<int, ProductImageItemResult[]>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productImage.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-image/catalog-product-image-list.html',
        'Batch list of product images for several products'
    )]
    public function list(array $productIds, array $select = []): Generator
    {
        $items = array_map(
            static fn (int $productId): array => $select !== []
                ? ['productId' => $productId, 'select' => $select]
                : ['productId' => $productId],
            $productIds
        );

        /** @var ResponseData $item */
        foreach ($this->batch->addEntityItems('catalog.productImage.list', $items) as $key => $item) {
            yield $key => array_map(
                static fn (array $productImage): ProductImageItemResult => new ProductImageItemResult($productImage),
                $item->getResult()['productImages']
            );
        }
    }
}
