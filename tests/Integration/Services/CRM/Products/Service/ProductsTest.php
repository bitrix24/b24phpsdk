<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Products\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Lead\Result\LeadItemResult;
use Bitrix24\SDK\Services\CRM\Product\Result\ProductItemResult;
use Bitrix24\SDK\Services\CRM\Product\Service\Product;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;

#[CoversClass(Product::class)]
#[CoversMethod(Product::class,'add')]
#[CoversMethod(Product::class,'delete')]
#[CoversMethod(Product::class,'list')]
#[CoversMethod(Product::class,'get')]
#[CoversMethod(Product::class,'fields')]
#[CoversMethod(Product::class,'update')]
#[CoversMethod(Product::class,'countByFilter')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Product\Service\Product::class)]
class ProductsTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Product $productService;

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(
            array_keys($this->productService->fields()->getFieldsDescription())
        );
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, ProductItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->productService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            ProductItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        self::assertGreaterThan(1, $this->productService->add(['NAME' => 'test product'])->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        self::assertTrue(
            $this->productService->delete($this->productService->add(['NAME' => 'test product'])->getId())->isSuccess()
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $product = [
            'NAME' => 'test product',
        ];
        $addProductResult = $this->productService->get($this->productService->add($product)->getId());
        self::assertGreaterThan(
            1,
            $addProductResult->product()->ID
        );
        self::assertEquals(
            $product['NAME'],
            $addProductResult->product()->NAME
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->productService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $this->productService->add(['NAME' => 'test']);
        self::assertGreaterThanOrEqual(1, $this->productService->list([], [], ['ID', 'NAME'])->getProducts());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $addedItemResult = $this->productService->add(['NAME' => 'test']);
        $newName = 'test2';

        self::assertTrue($this->productService->update($addedItemResult->getId(), ['NAME' => $newName])->isSuccess());
        self::assertEquals($newName, $this->productService->get($addedItemResult->getId())->product()->NAME);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchList(): void
    {
        $this->productService->add(['NAME' => 'test product']);
        $cnt = 0;

        foreach ($this->productService->batch->list([], ['>ID' => '1'], ['ID', 'NAME'], 1) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);
    }

    public function testBatchAdd(): void
    {
        $products = [];
        for ($i = 1; $i < 60; $i++) {
            $products[] = ['NAME' => 'NAME-' . $i];
        }

        $cnt = 0;
        foreach ($this->productService->batch->add($products) as $item) {
            $cnt++;
        }

        self::assertEquals(count($products), $cnt);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $productsCountBefore = $this->productService->countByFilter();
        $newProductsCount = 60;
        $products = [];
        for ($i = 1; $i <= $newProductsCount; $i++) {
            $products[] = ['NAME' => 'NAME-' . $i];
        }

        $cnt = 0;
        foreach ($this->productService->batch->add($products) as $item) {
            $cnt++;
        }

        self::assertEquals(count($products), $cnt);

        $productsCountAfter = $this->productService->countByFilter();
        $this->assertEquals($productsCountBefore + $newProductsCount, $productsCountAfter);
    }

    protected function setUp(): void
    {
        $this->productService = Factory::getServiceBuilder()->getCRMScope()->product();
    }
}