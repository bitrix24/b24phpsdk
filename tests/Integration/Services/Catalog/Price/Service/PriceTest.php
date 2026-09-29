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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Price\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Price\Service\Price;
use Bitrix24\SDK\Services\Catalog\Product\Service\Product;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Price::class)]
class PriceTest extends TestCase
{
    private Price $priceService;

    private Product $productService;

    private int $productId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->priceService = $serviceBuilder->getCatalogScope()->price();
        $this->productService = $serviceBuilder->getCatalogScope()->product();

        $catalogService = $serviceBuilder->getCatalogScope()->catalog();
        $iblockId = $catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $this->productId = $this->productService->add([
            'iblockId' => $iblockId,
            'name' => sprintf('test product for price %s', time()),
        ])->product()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->productService->delete($this->productId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Price::add, Price::get, Price::delete')]
    public function testAddGetDelete(): void
    {
        $priceResult = $this->priceService->add([
            'productId' => $this->productId,
            'catalogGroupId' => 1,
            'price' => 100.5,
            'currency' => 'USD',
        ]);
        $priceId = $priceResult->price()->id;
        $this->assertSame($this->productId, $priceResult->price()->productId);
        $this->assertSame(100.5, $priceResult->price()->price);

        $getResult = $this->priceService->get($priceId);
        $this->assertSame($priceId, $getResult->price()->id);

        $this->assertTrue($this->priceService->delete($priceId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Price::update')]
    public function testUpdate(): void
    {
        $priceId = $this->priceService->add([
            'productId' => $this->productId,
            'catalogGroupId' => 1,
            'price' => 100.5,
            'currency' => 'USD',
        ])->price()->id;

        $priceResult = $this->priceService->update($priceId, ['price' => 200.0, 'currency' => 'USD']);
        $this->assertSame(200.0, $priceResult->price()->price);

        $this->priceService->delete($priceId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Price::list')]
    public function testList(): void
    {
        $priceId = $this->priceService->add([
            'productId' => $this->productId,
            'catalogGroupId' => 1,
            'price' => 100.5,
            'currency' => 'USD',
        ])->price()->id;

        $pricesResult = $this->priceService->list([], ['productId' => $this->productId], ['id' => 'ASC']);
        $this->assertGreaterThanOrEqual(1, count($pricesResult->getPrices()));

        $this->priceService->delete($priceId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Price::modify')]
    public function testModify(): void
    {
        $pricesResult = $this->priceService->modify($this->productId, [
            ['catalogGroupId' => 1, 'currency' => 'USD', 'price' => 300.0],
        ]);
        $prices = $pricesResult->getPrices();
        $this->assertCount(1, $prices);
        $this->assertSame(300.0, $prices[0]->price);

        $this->priceService->delete($prices[0]->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Price::getFields')]
    public function testGetFields(): void
    {
        $this->assertIsArray($this->priceService->getFields()->getFieldsDescription());
    }
}
