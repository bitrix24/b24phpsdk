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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\PaymentItemBasket\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Sale\PaymentItemBasket\Result\PaymentItemBasketItemResult;
use Bitrix24\SDK\Services\Sale\PaymentItemBasket\Service\PaymentItemBasket;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class PaymentItemBasketTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\PaymentItemBasket\Service
 */
#[CoversMethod(PaymentItemBasket::class,'add')]
#[CoversMethod(PaymentItemBasket::class,'update')]
#[CoversMethod(PaymentItemBasket::class,'get')]
#[CoversMethod(PaymentItemBasket::class,'list')]
#[CoversMethod(PaymentItemBasket::class,'delete')]
#[CoversMethod(PaymentItemBasket::class,'getFields')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\PaymentItemBasket\Service\PaymentItemBasket::class)]
class PaymentItemBasketTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected PaymentItemBasket $paymentItemBasketService;

    protected int $orderId = 0;

    protected int $paymentId = 0;

    protected int $basketId = 0;

    protected int $personTypeId = 0;

    protected int $paySystemId = 0;

    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->paymentItemBasketService = $serviceBuilder->getSaleScope()->paymentItemBasket();
        $this->personTypeId = $this->getPersonTypeId();
        $this->paySystemId = $this->getPaySystemId();
        $this->orderId = $this->createTestOrder();
        $this->paymentId = $this->createTestPayment();
        $this->basketId = $this->createTestBasketItem();
    }

    protected function tearDown(): void
    {
        // Clean up created resources in reverse order
        $this->deleteTestBasketItem($this->basketId);
        $this->deleteTestPayment($this->paymentId);
        $this->deleteTestOrder($this->orderId);
        $this->deletePersonType($this->personTypeId);
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->paymentItemBasketService->getFields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, PaymentItemBasketItemResult::class);
    }
    
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->paymentItemBasketService->getFields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            PaymentItemBasketItemResult::class);
    }

    /**
     * Helper method to create a person type for testing
     */
    protected function getPersonTypeId(): int
    {
        $personTypeService = Fabric::getServiceBuilder()->getSaleScope()->personType();
        return $personTypeService->add([
            'name' => 'Test Person Type for PaymentItemBasket',
            'sort' => 100,
        ])->getId();
    }

    /**
     * Helper method to delete a person type after testing
     */
    protected function deletePersonType(int $id): void
    {
        $personTypeService = Fabric::getServiceBuilder()->getSaleScope()->personType();
        $personTypeService->delete($id);
    }

    /**
     * Helper method to get a payment system for testing
     * We fetch an existing one from the system
     */
    protected function getPaySystemId(): int
    {
        $core = Fabric::getCore();
        $response = $core->call('sale.paysystem.list', [
            'select' => ['id'],
            'filter' => ['active' => 'Y'],
            'order' => ['id' => 'ASC']
        ]);

        $result = $response->getResponseData()->getResult();
        $paySystems = (is_array($result)) ? $result : [];

        if ($paySystems === []) {
            $this->markTestSkipped('No payment systems available for testing');
        }

        return (int)$paySystems[0]['ID'];
    }

    /**
     * Helper method to create a test order
     */
    protected function createTestOrder(): int
    {
        $orderService = Fabric::getServiceBuilder()->getSaleScope()->order();
        $orderFields = [
            'lid' => 's1',
            'personTypeId' => $this->personTypeId,
            'currency' => 'USD',
            'price' => 100.00
        ];

        return $orderService->add($orderFields)->getId();
    }

    /**
     * Helper method to delete a test order
     */
    protected function deleteTestOrder(int $id): void
    {
        $orderService = Fabric::getServiceBuilder()->getSaleScope()->order();
        try {
            $orderService->delete($id);
        } catch (\Exception) {
            // Ignore if order doesn't exist
        }
    }

    /**
     * Helper method to create a test payment
     */
    protected function createTestPayment(): int
    {
        $paymentService = Fabric::getServiceBuilder()->getSaleScope()->payment();
        $paymentFields = [
            'orderId' => $this->orderId,
            'paySystemId' => $this->paySystemId,
            'sum' => 100.00,
            'currency' => 'USD'
        ];

        return $paymentService->add($paymentFields)->getId();
    }

    /**
     * Helper method to delete a test payment
     */
    protected function deleteTestPayment(int $id): void
    {
        $paymentService = Fabric::getServiceBuilder()->getSaleScope()->payment();
        try {
            $paymentService->delete($id);
        } catch (\Exception) {
            // Ignore if payment doesn't exist
        }
    }

    /**
     * Helper method to create a test basket item
     */
    protected function createTestBasketItem(): int
    {
        $basketItemService = Fabric::getServiceBuilder()->getSaleScope()->basketItem();
        $basketItemFields = [
            'orderId' => $this->orderId,
            'productId' => 0,
            'price' => 50.00,
            'quantity' => 2,
            'currency' => 'USD',
            'name' => 'Test Product for PaymentItemBasket'
        ];

        return $basketItemService->add($basketItemFields)->getId();
    }

    /**
     * Helper method to delete a test basket item
     */
    protected function deleteTestBasketItem(int $id): void
    {
        $basketItemService = Fabric::getServiceBuilder()->getSaleScope()->basketItem();
        try {
            $basketItemService->delete($id);
        } catch (\Exception) {
            // Ignore if basket item doesn't exist
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create a payment item basket binding
        $bindingFields = [
            'paymentId' => $this->paymentId,
            'basketId' => $this->basketId,
            'quantity' => 1.5,
            'xmlId' => 'TEST_BINDING_' . time()
        ];

        $bindingAddedResult = $this->paymentItemBasketService->add($bindingFields);
        $bindingId = $bindingAddedResult->getId();

        self::assertGreaterThan(0, $bindingId);

        // Clean up
        $this->paymentItemBasketService->delete($bindingId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a payment item basket binding
        $bindingFields = [
            'paymentId' => $this->paymentId,
            'basketId' => $this->basketId,
            'quantity' => 1.0,
            'xmlId' => 'TEST_UPDATE_BINDING_' . time()
        ];

        $bindingAddedResult = $this->paymentItemBasketService->add($bindingFields);
        $bindingId = $bindingAddedResult->getId();

        // Update the binding
        $updateFields = [
            'quantity' => 2.5,
            'xmlId' => 'UPDATED_BINDING_' . time()
        ];

        $updateResult = $this->paymentItemBasketService->update($bindingId, $updateFields);
        self::assertTrue($updateResult->isSuccess());

        // Verify the update
        $binding = $this->paymentItemBasketService->get($bindingId)->paymentItemBasket();
        self::assertEquals(2.5, (float)$binding->getQuantity());

        // Clean up
        $this->paymentItemBasketService->delete($bindingId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Create a payment item basket binding
        $xmlId = 'TEST_GET_BINDING_' . time();
        $bindingFields = [
            'paymentId' => $this->paymentId,
            'basketId' => $this->basketId,
            'quantity' => 1.0,
            'xmlId' => $xmlId
        ];

        $bindingAddedResult = $this->paymentItemBasketService->add($bindingFields);
        $bindingId = $bindingAddedResult->getId();

        // Get the binding
        $binding = $this->paymentItemBasketService->get($bindingId)->paymentItemBasket();

        self::assertEquals($bindingId, $binding->getId());
        self::assertEquals($this->paymentId, $binding->getPaymentId());
        self::assertEquals($this->basketId, $binding->getBasketId());
        self::assertEquals($xmlId, $binding->getXmlId());

        // Clean up
        $this->paymentItemBasketService->delete($bindingId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a payment item basket binding
        $bindingFields = [
            'paymentId' => $this->paymentId,
            'basketId' => $this->basketId,
            'quantity' => 1.0,
            'xmlId' => 'TEST_LIST_BINDING_' . time()
        ];

        $bindingAddedResult = $this->paymentItemBasketService->add($bindingFields);
        $bindingId = $bindingAddedResult->getId();

        // List bindings
        $filter = ['paymentId' => $this->paymentId];
        $bindingsResult = $this->paymentItemBasketService->list([], $filter);
        $bindings = $bindingsResult->getPaymentItemBaskets();

        self::assertGreaterThan(0, count($bindings));

        // Verify our binding is in the list
        $found = false;
        foreach ($bindings as $binding) {
            if ((int)$binding->getId() === $bindingId) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found);

        // Clean up
        $this->paymentItemBasketService->delete($bindingId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a payment item basket binding
        $bindingFields = [
            'paymentId' => $this->paymentId,
            'basketId' => $this->basketId,
            'quantity' => 1.0,
            'xmlId' => 'TEST_DELETE_BINDING_' . time()
        ];

        $bindingAddedResult = $this->paymentItemBasketService->add($bindingFields);
        $bindingId = $bindingAddedResult->getId();

        // Delete the binding
        $this->paymentItemBasketService->delete($bindingId);

        // Verify binding no longer exists
        try {
            $this->paymentItemBasketService->get($bindingId);
            self::fail('Expected exception when getting deleted payment item basket binding');
        } catch (\Exception $exception) {
            // Expected exception - check for error message indicating binding doesn't exist
            self::assertStringContainsString('payment item basket binding is not exists', $exception->getMessage());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        // Get fields for payment item basket binding
        $fieldsResult = $this->paymentItemBasketService->getFields();
        $fields = $fieldsResult->getFieldsDescription();

        // Verify fields structure
        self::assertIsArray($fields);
        // Verify basic payment item basket binding fields are present
        self::assertArrayHasKey('paymentId', $fields);
        self::assertArrayHasKey('basketId', $fields);
        self::assertArrayHasKey('quantity', $fields);
        self::assertArrayHasKey('xmlId', $fields);
        self::assertArrayHasKey('id', $fields);
        self::assertArrayHasKey('dateInsert', $fields);
    }
}