<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\BasketItem\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Sale\BasketItem\Result\BasketItemItemResult;
use Bitrix24\SDK\Services\Sale\BasketItem\Service\BasketItem;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\Catalog\Product\Service\Product;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class BasketItemTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\BasketItem\Service
 */
#[CoversMethod(BasketItem::class,'add')]
#[CoversMethod(BasketItem::class,'delete')]
#[CoversMethod(BasketItem::class,'get')]
#[CoversMethod(BasketItem::class,'list')]
#[CoversMethod(BasketItem::class,'getFields')]
#[CoversMethod(BasketItem::class,'update')]
#[CoversMethod(BasketItem::class,'addCatalogProduct')]
#[CoversMethod(BasketItem::class,'updateCatalogProduct')]
#[CoversMethod(BasketItem::class,'getFieldsCatalogProduct')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\BasketItem\Service\BasketItem::class)]
class BasketItemTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected BasketItem $basketItemService;

    protected int $orderId;

    protected int $personTypeId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->basketItemService = $serviceBuilder->getSaleScope()->basketItem();

        // Create person type
        $personTypeFields = [
            'name' => 'Test Person Type',
            'active' => 'Y',
            'xmlId' => uniqid('test_', true),
            'baseLang' => [
                'name' => 'Test Person Type'
            ]
        ];
        $this->personTypeId = $serviceBuilder->getSaleScope()->personType()->add($personTypeFields)->getId();

        // Create test order
        $orderFields = [
            'lid' => 's1',
            'personTypeId' => $this->personTypeId,
            'currency' => 'USD',
            'price' => 100.00
        ];
        $this->orderId = $serviceBuilder->getSaleScope()->order()->add($orderFields)->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function tearDown(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();

        // Delete test order
        $serviceBuilder->getSaleScope()->order()->delete($this->orderId);

        // Delete person type
        $serviceBuilder->getSaleScope()->personType()->delete($this->personTypeId);
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->basketItemService->getFields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, BasketItemItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->basketItemService->getFields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            BasketItemItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create basket item with minimum required fields
        $basketItemFields = [
            'orderId' => $this->orderId,
            'productId' => 0,
            'currency' => 'USD',
            'quantity' => 1.0,
            'name' => 'Test Product'
        ];

        $basketItemId = $this->basketItemService->add($basketItemFields)->getId();
        self::assertGreaterThan(0, $basketItemId);

        // Delete test basket item
        $this->basketItemService->delete($basketItemId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create test basket item
        $basketItemFields = [
            'orderId' => $this->orderId,
            'productId' => 0,
            'currency' => 'USD',
            'quantity' => 1.0,
            'name' => 'Test Product'
        ];

        $basketItemId = $this->basketItemService->add($basketItemFields)->getId();

        // Update basket item
        $updateFields = [
            'quantity' => 2.0,
            'name' => 'Updated Test Product'
        ];

        $updatedBasketItemResult = $this->basketItemService->update($basketItemId, $updateFields);
        self::assertTrue($updatedBasketItemResult->isSuccess());

        // Verify changes were applied
        $basketItem = $this->basketItemService->get($basketItemId)->basketItem();
        self::assertEquals(2.0, $basketItem->quantity);
        self::assertEquals('Updated Test Product', $basketItem->name);

        // Delete test basket item
        $this->basketItemService->delete($basketItemId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create two test basket items
        $basketItemIds = [];
        for ($i = 0; $i < 2; $i++) {
            $basketItemFields = [
                'orderId' => $this->orderId,
                'productId' => 0,
                'currency' => 'USD',
                'quantity' => 1.0,
                'name' => sprintf('Test Product %d', $i + 1)
            ];

            $basketItemIds[] = $this->basketItemService->add($basketItemFields)->getId();
        }

        // Get list of basket items and verify that created items are present
        $basketItems = $this->basketItemService->list(['id'], ['orderId' => $this->orderId])->getBasketItems();

        self::assertGreaterThanOrEqual(2, count($basketItems));
        $foundIds = [];
        foreach ($basketItems as $basketItem) {
            if (in_array($basketItem->id, $basketItemIds, true)) {
                $foundIds[] = $basketItem->id;
            }
        }

        self::assertEquals(2, count($foundIds));

        // Delete test basket items
        foreach ($basketItemIds as $basketItemId) {
            $this->basketItemService->delete($basketItemId);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create test basket item
        $basketItemFields = [
            'orderId' => $this->orderId,
            'productId' => 0,
            'currency' => 'USD',
            'quantity' => 1.0,
            'name' => 'Test Product'
        ];

        $basketItemId = $this->basketItemService->add($basketItemFields)->getId();

        // Verify deletion was successful
        self::assertTrue($this->basketItemService->delete($basketItemId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Create test basket item
        $basketItemFields = [
            'orderId' => $this->orderId,
            'productId' => 0,
            'currency' => 'USD',
            'quantity' => 1.0,
            'name' => 'Test Product'
        ];

        $basketItemId = $this->basketItemService->add($basketItemFields)->getId();

        // Get basket item and verify its fields
        $basketItem = $this->basketItemService->get($basketItemId)->basketItem();
        self::assertEquals($basketItemId, $basketItem->id);
        self::assertEquals($this->orderId, $basketItem->orderId);
        self::assertEquals('Test Product', $basketItem->name);
        self::assertEquals(1.0, $basketItem->quantity);
        self::assertEquals('USD', $basketItem->currency);

        // Delete test basket item
        $this->basketItemService->delete($basketItemId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        // Get fields description
        $fields = $this->basketItemService->getFields()->getFieldsDescription();

        // Verify presence of essential fields
        self::assertArrayHasKey('id', $fields);
        self::assertArrayHasKey('orderId', $fields);
        self::assertArrayHasKey('productId', $fields);
        self::assertArrayHasKey('name', $fields);
        self::assertArrayHasKey('price', $fields);
        self::assertArrayHasKey('currency', $fields);
        self::assertArrayHasKey('quantity', $fields);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFieldsCatalogProduct(): void
    {
        // Get fields description for catalog products
        $fields = $this->basketItemService->getFieldsCatalogProduct()->getFieldsDescription();

        // Verify presence of essential fields for catalog products
        self::assertArrayHasKey('id', $fields);
        self::assertArrayHasKey('quantity', $fields);
        self::assertArrayHasKey('xmlId', $fields);
        self::assertArrayHasKey('sort', $fields);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddCatalogProduct(): void
    {
        $productId = $this->getProductId();
        // Create basket item from catalog product with required fields
        $basketItemFields = [
            'orderId' => $this->orderId,
            'productId' => $productId,
            'currency' => 'USD',
            'quantity' => 1.0,
            'name' => 'Test Product',
        ];

        $addedCatalogProductResult = $this->basketItemService->addCatalogProduct($basketItemFields);
        $basketItemId = $addedCatalogProductResult->getId();

        // Verify that item was added successfully
        self::assertGreaterThan(0, $basketItemId);

        // Verify that added item contains correct product reference
        $basketItem = $this->basketItemService->get($basketItemId)->basketItem();
        self::assertEquals($productId, $basketItem->productId);
        self::assertEquals($this->orderId, $basketItem->orderId);
        self::assertEquals(1.0, $basketItem->quantity);
        self::assertEquals('USD', $basketItem->currency);

        // Delete test basket item and product
        $this->basketItemService->delete($basketItemId);
        $this->deleteProduct($productId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdateCatalogProduct(): void
    {
        $productId = $this->getProductId();
        // Create basket item from catalog product with required fields
        $basketItemFields = [
            'orderId' => $this->orderId,
            'productId' => $productId,
            'currency' => 'USD',
            'quantity' => 1.0,
            'name' => 'Test Product',
        ];

        $addedCatalogProductResult = $this->basketItemService->addCatalogProduct($basketItemFields);
        $basketItemId = $addedCatalogProductResult->getId();

        // Verify that item was added successfully
        self::assertGreaterThan(0, $basketItemId);

        $newQuantity = 2.0;
        // Verify update was successful
        self::assertTrue($this->basketItemService->updateCatalogProduct($basketItemId, ['quantity' => $newQuantity])->isSuccess());

        // Verify that added item contains correct product reference
        $basketItem = $this->basketItemService->get($basketItemId)->basketItem();
        self::assertEquals($newQuantity, $basketItem->quantity);

        // Delete test basket item and product
        $this->basketItemService->delete($basketItemId);
        $this->deleteProduct($productId);
    }

    protected function getProductId(): int
    {
        $iblockId = 0;
        $productId = 0;
        // Get list of catalogs
        $catalogs = Factory::getServiceBuilder()->getCatalogScope()->catalog()->list([], [], ['id', 'iblockId','productIblockId'], 0)->getCatalogs();
        if ($catalogs === []) {
            throw new \RuntimeException('No product catalogs found');
        }

        foreach ($catalogs as $catalog) {
            if (!empty($catalog->productIblockId)) {
                $iblockId = (int)$catalog->productIblockId;
            }
        }

        // Create test product
        $productFields = [
            'name' => 'Test Product ' . uniqid(),
            'iblockId' => $iblockId,
            'iblockSectionId' => 0,
            'price' => 100.00,
            'currency' => 'USD',
            'vat' => 0,
            'vatIncluded' => 'Y',
            'active' => 'Y',
            'available' => 'Y',
            'canBuyZero' => 'Y',
            'type' => 1,
            'quantity' => 1000,
        ];
        $productResult = Factory::getServiceBuilder()->getCatalogScope()->product()->add($productFields);
        $productId = (int)$productResult->product()->id;

        $core = Factory::getCore();
        // Get price types
        $priceTypeId = (int)$core->call('catalog.priceType.list', [])->getResponseData()->getResult()['priceTypes'][0]['id'];
        // Create product price
        $res = (bool)$core->call('catalog.price.add', [
            'fields' => [
                'productId' => $productId,
                'catalogGroupId' => $priceTypeId,
                'price' => 100.00,
                'currency' => 'USD',
            ]
        ])->getResponseData()->getResult()['price']['id'];

        return ($res) ? $productId : 0;
    }

    protected function deleteProduct(int $id) {
        // Delete test product
        Factory::getServiceBuilder()->getCatalogScope()->product()->delete($id);
    }

}
