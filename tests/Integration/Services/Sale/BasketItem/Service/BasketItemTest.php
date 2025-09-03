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
use Bitrix24\SDK\Tests\Integration\Fabric;
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
    protected int $productId;
    protected int $iblockId;
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->basketItemService = $serviceBuilder->getSaleScope()->basketItem();
        
        // Получаем список каталогов
        $catalogs = $serviceBuilder->getCatalogScope()->catalog()->list([], [], ['id', 'iblockId'], 0)->getCatalogs();
        if (empty($catalogs)) {
            throw new \RuntimeException('Не найдено ни одного каталога товаров');
        }
        $this->iblockId = (int)$catalogs[0]->iblockId;
        
        // Создаем тестовый товар
        $productFields = [
            'name' => 'Test Product ' . uniqid(),
            'iblockId' => $this->iblockId,
            'iblockSectionId' => 0,
            'price' => 100.00,
            'currency' => 'USD',
            'vat' => 0,
            'vatIncluded' => 'Y',
            'active' => 'Y',
            'available' => 'Y',
            'canBuyZero' => 'Y',
            'type' => 1,
        ];
        $result = $serviceBuilder->getCatalogScope()->product()->add($productFields);
        $this->productId = (int)$result->product()->id;
        
        // Создаем тип плательщика
        $personTypeFields = [
            'name' => 'Test Person Type',
            'active' => 'Y',
            'xmlId' => uniqid('test_', true),
            'baseLang' => [
                'name' => 'Test Person Type'
            ]
        ];
        $this->personTypeId = $serviceBuilder->getSaleScope()->personType()->add($personTypeFields)->getId();
        
        // Создаем тестовый заказ
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
        $serviceBuilder = Fabric::getServiceBuilder();
        
        // Удаляем тестовый заказ
        $serviceBuilder->getSaleScope()->order()->delete($this->orderId);
        
        // Удаляем тип плательщика
        $serviceBuilder->getSaleScope()->personType()->delete($this->personTypeId);
        
        // Удаляем тестовый товар
        $serviceBuilder->getCatalogScope()->product()->delete($this->productId);
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
        // Создаем элемент корзины с минимально необходимыми полями
        $basketItemFields = [
            'orderId' => $this->orderId,
            'productId' => 0,
            'currency' => 'USD',
            'quantity' => 1.0,
            'name' => 'Test Product'
        ];

        $basketItemId = $this->basketItemService->add($basketItemFields)->getId();
        self::assertGreaterThan(0, $basketItemId);

        // Удаляем тестовый элемент корзины
        $this->basketItemService->delete($basketItemId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Создаем тестовый элемент корзины
        $basketItemFields = [
            'orderId' => $this->orderId,
            'productId' => 0,
            'currency' => 'USD',
            'quantity' => 1.0,
            'name' => 'Test Product'
        ];

        $basketItemId = $this->basketItemService->add($basketItemFields)->getId();

        // Обновляем элемент корзины
        $updateFields = [
            'quantity' => 2.0,
            'name' => 'Updated Test Product'
        ];

        $result = $this->basketItemService->update($basketItemId, $updateFields);
        self::assertTrue($result->isSuccess());

        // Проверяем, что изменения применились
        $basketItem = $this->basketItemService->get($basketItemId)->basketItem();
        self::assertEquals(2.0, $basketItem->quantity);
        self::assertEquals('Updated Test Product', $basketItem->name);

        // Удаляем тестовый элемент корзины
        $this->basketItemService->delete($basketItemId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Создаем тестовый элемент корзины
        $basketItemFields = [
            'orderId' => $this->orderId,
            'productId' => 0,
            'currency' => 'USD',
            'quantity' => 1.0,
            'name' => 'Test Product'
        ];

        $basketItemId = $this->basketItemService->add($basketItemFields)->getId();

        // Проверяем успешность удаления
        self::assertTrue($this->basketItemService->delete($basketItemId)->isSuccess());
    }
}
