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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\StoreProduct\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\StoreProduct\Service\StoreProduct;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(StoreProduct::class)]
class StoreProductTest extends TestCase
{
    private StoreProduct $storeProductService;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test StoreProduct::list finds existing storeProduct records')]
    public function testList(): void
    {
        $items = $this->storeProductService->list()->getStoreProducts();
        $this->assertNotEmpty($items, 'integration portal must have at least one product with stock');
        $this->assertGreaterThan(0, $this->storeProductService->list()->getTotal());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test StoreProduct::list with select, filter and order')]
    public function testListWithSelectFilterOrder(): void
    {
        $anyItem = $this->storeProductService->list()->getStoreProducts()[0];

        $items = $this->storeProductService->list(
            ['id', 'productId', 'storeId', 'amount'],
            ['productId' => $anyItem->productId],
            ['id' => 'ASC']
        )->getStoreProducts();

        $this->assertNotEmpty($items);
        foreach ($items as $item) {
            $this->assertEquals($anyItem->productId, $item->productId);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test StoreProduct::get returns the same record as list')]
    public function testGet(): void
    {
        $listItem = $this->storeProductService->list()->getStoreProducts()[0];
        $getItem = $this->storeProductService->get($listItem->id)->storeProduct();

        $this->assertEquals($listItem->id, $getItem->id);
        $this->assertEquals($listItem->productId, $getItem->productId);
        $this->assertEquals($listItem->storeId, $getItem->storeId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test StoreProduct::getFields')]
    public function testGetFields(): void
    {
        $fields = $this->storeProductService->getFields()->getFieldsDescription();

        $this->assertArrayHasKey('id', $fields);
        $this->assertArrayHasKey('productId', $fields);
        $this->assertArrayHasKey('storeId', $fields);
        $this->assertArrayHasKey('amount', $fields);
        $this->assertArrayHasKey('quantityReserved', $fields);
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->storeProductService = Fabric::getServiceBuilder()->getCatalogScope()->storeProduct();
    }
}
