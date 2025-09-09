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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\BasketProperty\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Sale\BasketProperty\Service\BasketProperty;
use Bitrix24\SDK\Services\Sale\BasketItem\Service\BasketItem;
use Bitrix24\SDK\Services\Sale\Order\Service\Order;
use Bitrix24\SDK\Services\Sale\PersonType\Service\PersonType;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class BasketPropertyTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\BasketProperty\Service
 */
#[CoversMethod(BasketProperty::class,'add')]
#[CoversMethod(BasketProperty::class,'update')]
#[CoversMethod(BasketProperty::class,'get')]
#[CoversMethod(BasketProperty::class,'list')]
#[CoversMethod(BasketProperty::class,'delete')]
#[CoversMethod(BasketProperty::class,'getFields')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\BasketProperty\Service\BasketProperty::class)]
class BasketPropertyTest extends TestCase
{
    protected BasketProperty $basketPropertyService;

    protected BasketItem $basketItemService;

    protected Order $orderService;

    protected PersonType $personTypeService;

    protected int $basketItemId;

    protected int $orderId;

    protected int $personTypeId;

    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $saleServiceBuilder = $serviceBuilder->getSaleScope();

        $this->basketPropertyService = $saleServiceBuilder->basketProperty();
        $this->basketItemService = $saleServiceBuilder->basketItem();
        $this->orderService = $saleServiceBuilder->order();
        $this->personTypeService = $saleServiceBuilder->personType();

        // Create test data
        $this->personTypeId = $this->createPersonType();
        $this->orderId = $this->createOrder($this->personTypeId);
        $this->basketItemId = $this->createBasketItem($this->orderId);
    }

    protected function tearDown(): void
    {
        // Clean up created resources in reverse order
        if (isset($this->basketItemId)) {
            $this->deleteBasketItem($this->basketItemId);
        }

        if (isset($this->orderId)) {
            $this->deleteOrder($this->orderId);
        }

        if (isset($this->personTypeId)) {
            $this->deletePersonType($this->personTypeId);
        }
    }

    /**
     * Helper method to create a person type for testing
     * 
     * @return int Person type ID
     * @throws BaseException
     * @throws TransportException
     */
    protected function createPersonType(): int
    {
        $personTypeFields = [
            'name' => 'Test Person Type ' . uniqid(),
            'active' => 'Y',
            'sort' => 100,
        ];

        $addedPersonTypeResult = $this->personTypeService->add($personTypeFields);
        return $addedPersonTypeResult->getId();
    }

    /**
     * Helper method to delete a person type
     * 
     * @param int $id Person type ID to delete
     */
    protected function deletePersonType(int $id): void
    {
        try {
            $this->personTypeService->delete($id);
        } catch (\Exception) {
            // Ignore errors during cleanup
        }
    }

    /**
     * Helper method to create an order for testing
     * 
     * @param int $personTypeId Person type ID to use for the order
     * @return int Order ID
     * @throws BaseException
     * @throws TransportException
     */
    protected function createOrder(int $personTypeId): int
    {
        $orderFields = [
            'price' => 100.00,
            'currency' => 'RUB',
            'personTypeId' => $personTypeId,
            'userId' => 1, // Using admin user
            'lid' => 's1',
        ];

        $orderAddedResult = $this->orderService->add($orderFields);
        return $orderAddedResult->getId();
    }

    /**
     * Helper method to delete an order
     * 
     * @param int $id Order ID to delete
     */
    protected function deleteOrder(int $id): void
    {
        try {
            $this->orderService->delete($id);
        } catch (\Exception) {
            // Ignore errors during cleanup
        }
    }

    /**
     * Helper method to create a basket item for testing
     * 
     * @param int $orderId Order ID to which the basket item belongs
     * @return int Basket item ID
     * @throws BaseException
     * @throws TransportException
     */
    protected function createBasketItem(int $orderId): int
    {
        $basketItemFields = [
            'orderId' => $orderId,
            'quantity' => 1,
            'price' => 100.00,
            'currency' => 'RUB',
            'name' => 'Test Product ' . uniqid(),
            'productId' => 0,
            'xmlId' => 'TEST_PRODUCT_' . uniqid()
        ];

        $addedBasketItemResult = $this->basketItemService->add($basketItemFields);
        return $addedBasketItemResult->getId();
    }

    /**
     * Helper method to delete a basket item after testing
     * 
     * @param int $id Basket item ID to delete
     */
    protected function deleteBasketItem(int $id): void
    {
        try {
            $this->basketItemService->delete($id);
        } catch (\Exception) {
            // Ignore errors during cleanup
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create a basket property
        $propertyFields = [
            'basketId' => $this->basketItemId,
            'name' => 'Test Property',
            'value' => 'Test Value',
            'code' => 'TEST_CODE',
        ];

        $basketPropertyAddResult = $this->basketPropertyService->add($propertyFields);
        $propertyId = $basketPropertyAddResult->getId();

        self::assertGreaterThan(0, $propertyId);

        // Clean up
        $this->basketPropertyService->delete($propertyId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a basket property
        $propertyFields = [
            'basketId' => $this->basketItemId,
            'name' => 'Test Property',
            'value' => 'Test Value',
            'code' => 'TEST_CODE',
        ];

        $basketPropertyAddResult = $this->basketPropertyService->add($propertyFields);
        $propertyId = $basketPropertyAddResult->getId();

        // Update the property
        $updateFields = [
            'value' => 'Updated Test Value',
            'name' => 'Test Property',
            'code' => 'TEST_CODE',
        ];

        $this->basketPropertyService->update($propertyId, $updateFields);

        // Verify the update
        $basketPropertyItemResult = $this->basketPropertyService->get($propertyId)->getBasketProperty();
        self::assertEquals('Updated Test Value', $basketPropertyItemResult->value);

        // Clean up
        $this->basketPropertyService->delete($propertyId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Create a basket property
        $propertyFields = [
            'basketId' => $this->basketItemId,
            'name' => 'Test Property',
            'value' => 'Test Value',
            'code' => 'TEST_CODE',
        ];

        $basketPropertyAddResult = $this->basketPropertyService->add($propertyFields);
        $propertyId = $basketPropertyAddResult->getId();

        // Get the property
        $basketPropertyItemResult = $this->basketPropertyService->get($propertyId)->getBasketProperty();

        self::assertEquals($propertyId, $basketPropertyItemResult->id);
        self::assertEquals('Test Property', $basketPropertyItemResult->name);
        self::assertEquals('Test Value', $basketPropertyItemResult->value);
        self::assertEquals('TEST_CODE', $basketPropertyItemResult->code);

        // Clean up
        $this->basketPropertyService->delete($propertyId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a basket property
        $propertyFields = [
            'basketId' => $this->basketItemId,
            'name' => 'Test List Property',
            'value' => 'Test List Value',
            'code' => 'TEST_LIST_CODE',
        ];

        $basketPropertyAddResult = $this->basketPropertyService->add($propertyFields);
        $propertyId = $basketPropertyAddResult->getId();

        // List properties
        $filter = ['basketId' => $this->basketItemId];
        $basketPropertiesResult = $this->basketPropertyService->list([], $filter);
        $properties = $basketPropertiesResult->getBasketProperties();

        self::assertGreaterThan(0, count($properties));

        // Verify our property is in the list
        $found = false;
        foreach ($properties as $property) {
            if ((int)$property->id === $propertyId) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found);

        // Clean up
        $this->basketPropertyService->delete($propertyId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a basket property
        $propertyFields = [
            'basketId' => $this->basketItemId,
            'name' => 'Test Delete Property',
            'value' => 'Test Delete Value',
            'code' => 'TEST_DELETE_CODE',
        ];

        $basketPropertyAddResult = $this->basketPropertyService->add($propertyFields);
        $propertyId = $basketPropertyAddResult->getId();

        // Delete the property
        $deletedItemResult = $this->basketPropertyService->delete($propertyId);

        // Verify deletion was successful
        self::assertTrue($deletedItemResult->isSuccess());

        // Verify property no longer exists
        try {
            $this->basketPropertyService->get($propertyId);
            self::fail('Expected exception when getting deleted property');
        } catch (\Exception $exception) {
            // Expected exception
            self::assertStringContainsString('not exists', $exception->getMessage());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        // Get fields
        $basketPropertyFieldsResult = $this->basketPropertyService->getFields();
        $fields = $basketPropertyFieldsResult->getFieldsDescription();

        // Verify fields structure
        self::assertIsArray($fields);
        self::assertNotEmpty($fields);

        // Check for expected fields in basket properties
        self::assertArrayHasKey('basketId', $fields);
        self::assertArrayHasKey('name', $fields);
        self::assertArrayHasKey('value', $fields);
        self::assertArrayHasKey('code', $fields);
    }
}
