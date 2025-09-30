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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\PaymentItemShipment\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Sale\PaymentItemShipment\Result\PaymentItemShipmentItemResult;
use Bitrix24\SDK\Services\Sale\PaymentItemShipment\Service\PaymentItemShipment;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class PaymentItemShipmentTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\PaymentItemShipment\Service
 */
#[CoversMethod(PaymentItemShipment::class,'add')]
#[CoversMethod(PaymentItemShipment::class,'update')]
#[CoversMethod(PaymentItemShipment::class,'get')]
#[CoversMethod(PaymentItemShipment::class,'list')]
#[CoversMethod(PaymentItemShipment::class,'delete')]
#[CoversMethod(PaymentItemShipment::class,'getFields')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\PaymentItemShipment\Service\PaymentItemShipment::class)]
class PaymentItemShipmentTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected PaymentItemShipment $paymentItemShipmentService;

    protected int $orderId = 0;

    protected int $paymentId = 0;

    protected int $shipmentId = 0;

    protected int $personTypeId = 0;

    protected int $paySystemId = 0;

    protected int $deliveryId = 0;

    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->paymentItemShipmentService = $serviceBuilder->getSaleScope()->paymentItemShipment();
        $this->personTypeId = $this->getPersonTypeId();
        $this->paySystemId = $this->getPaySystemId();
        $this->deliveryId = $this->getDeliveryId();
        $this->orderId = $this->createTestOrder();
        $this->paymentId = $this->createTestPayment();
        $this->shipmentId = $this->createTestShipment();
    }

    protected function tearDown(): void
    {
        // Clean up created resources in reverse order
        $this->deleteTestShipment($this->shipmentId);
        $this->deleteTestPayment($this->paymentId);
        $this->deleteTestOrder($this->orderId);
        $this->deletePersonType($this->personTypeId);
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->paymentItemShipmentService->getFields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, PaymentItemShipmentItemResult::class);
    }
    
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->paymentItemShipmentService->getFields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            PaymentItemShipmentItemResult::class);
    }

    /**
     * Helper method to create a person type for testing
     */
    protected function getPersonTypeId(): int
    {
        $personTypeService = Fabric::getServiceBuilder()->getSaleScope()->personType();
        return $personTypeService->add([
            'name' => 'Test Person Type for PaymentItemShipment',
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

        return (int)$paySystems[0]['ID'];
    }

    /**
     * Helper method to get a delivery service for testing
     * We fetch an existing one from the system
     */
    protected function getDeliveryId(): int
    {
        $core = Fabric::getCore();
        $response = $core->call('sale.delivery.getlist', [
            'SELECT' => ['ID'],
            'FILTER' => ['ACTIVE' => 'Y'],
            'ORDER' => ['ID' => 'ASC']
        ]);

        $result = $response->getResponseData()->getResult();
        $deliveryServices = (is_array($result)) ? $result : [];

        if ($deliveryServices === []) {
            throw new \RuntimeException('No active delivery services found on the portal');
        }

        return (int)$deliveryServices[0]['ID'];
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
     * Helper method to create a test shipment
     */
    protected function createTestShipment(): int
    {
        $core = Fabric::getCore();
        $response = $core->call('sale.shipment.add', [
            'fields' => [
                'orderId' => $this->orderId,
                'allowDelivery' => 'Y', // Required: delivery permission indicator
                'deducted' => 'N', // Required: shipment status (N - not shipped yet)
                'deliveryId' => $this->deliveryId, // Required: real delivery service identifier from portal
                'statusId' => 'DN', // Optional: default status
                'xmlId' => 'TEST_SHIPMENT_' . time()
            ]
        ]);

        $result = $response->getResponseData()->getResult();
        return (int)$result['shipment']['id'];
    }

    /**
     * Helper method to delete a test shipment
     */
    protected function deleteTestShipment(int $id): void
    {
        $core = Fabric::getCore();
        try {
            $core->call('sale.shipment.delete', ['id' => $id]);
        } catch (\Exception) {
            // Ignore if shipment doesn't exist
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create a payment item shipment binding
        $bindingFields = [
            'paymentId' => $this->paymentId,
            'shipmentId' => $this->shipmentId,
            'xmlId' => 'TEST_BINDING_' . time()
        ];

        $paymentItemShipmentAddedResult = $this->paymentItemShipmentService->add($bindingFields);
        $bindingId = $paymentItemShipmentAddedResult->getId();

        self::assertGreaterThan(0, $bindingId);

        // Clean up
        $this->paymentItemShipmentService->delete($bindingId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a payment item shipment binding
        $bindingFields = [
            'paymentId' => $this->paymentId,
            'shipmentId' => $this->shipmentId,
            'xmlId' => 'TEST_UPDATE_BINDING_' . time()
        ];

        $paymentItemShipmentAddedResult = $this->paymentItemShipmentService->add($bindingFields);
        $bindingId = $paymentItemShipmentAddedResult->getId();

        // Update the binding
        $updateFields = [
            'xmlId' => 'UPDATED_BINDING_' . time()
        ];

        $paymentItemShipmentUpdatedResult = $this->paymentItemShipmentService->update($bindingId, $updateFields);
        self::assertTrue($paymentItemShipmentUpdatedResult->isSuccess());

        // Verify the update
        $paymentItemShipmentItemResult = $this->paymentItemShipmentService->get($bindingId)->paymentItemShipment();
        self::assertEquals($updateFields['xmlId'], $paymentItemShipmentItemResult->xmlId);

        // Clean up
        $this->paymentItemShipmentService->delete($bindingId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Create a payment item shipment binding
        $xmlId = 'TEST_GET_BINDING_' . time();
        $bindingFields = [
            'paymentId' => $this->paymentId,
            'shipmentId' => $this->shipmentId,
            'xmlId' => $xmlId
        ];

        $paymentItemShipmentAddedResult = $this->paymentItemShipmentService->add($bindingFields);
        $bindingId = $paymentItemShipmentAddedResult->getId();

        // Get the binding
        $paymentItemShipmentItemResult = $this->paymentItemShipmentService->get($bindingId)->paymentItemShipment();

        self::assertEquals($bindingId, $paymentItemShipmentItemResult->id);
        self::assertEquals($this->paymentId, $paymentItemShipmentItemResult->paymentId);
        self::assertEquals($this->shipmentId, $paymentItemShipmentItemResult->shipmentId);
        self::assertEquals($xmlId, $paymentItemShipmentItemResult->xmlId);

        // Clean up
        $this->paymentItemShipmentService->delete($bindingId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a payment item shipment binding
        $bindingFields = [
            'paymentId' => $this->paymentId,
            'shipmentId' => $this->shipmentId,
            'xmlId' => 'TEST_LIST_BINDING_' . time()
        ];

        $paymentItemShipmentAddedResult = $this->paymentItemShipmentService->add($bindingFields);
        $bindingId = $paymentItemShipmentAddedResult->getId();

        // List bindings
        $filter = ['paymentId' => $this->paymentId];
        $paymentItemShipmentsResult = $this->paymentItemShipmentService->list([], $filter);
        $bindings = $paymentItemShipmentsResult->getPaymentItemShipments();

        self::assertGreaterThan(0, count($bindings));

        // Verify our binding is in the list
        $found = false;
        foreach ($bindings as $binding) {
            if ((int)$binding->id === $bindingId) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found);

        // Clean up
        $this->paymentItemShipmentService->delete($bindingId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a payment item shipment binding
        $bindingFields = [
            'paymentId' => $this->paymentId,
            'shipmentId' => $this->shipmentId,
            'xmlId' => 'TEST_DELETE_BINDING_' . time()
        ];

        $paymentItemShipmentAddedResult = $this->paymentItemShipmentService->add($bindingFields);
        $bindingId = $paymentItemShipmentAddedResult->getId();

        // Delete the binding
        self::assertTrue($this->paymentItemShipmentService->delete($bindingId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        // Get fields for payment item shipment binding
        $paymentItemShipmentFieldsResult = $this->paymentItemShipmentService->getFields();
        $fields = $paymentItemShipmentFieldsResult->getFieldsDescription();

        // Verify fields structure
        self::assertIsArray($fields);
        // Verify basic payment item shipment binding fields are present
        self::assertArrayHasKey('paymentId', $fields);
        self::assertArrayHasKey('shipmentId', $fields);
        self::assertArrayHasKey('xmlId', $fields);
        self::assertArrayHasKey('id', $fields);
        self::assertArrayHasKey('dateInsert', $fields);
    }
}
