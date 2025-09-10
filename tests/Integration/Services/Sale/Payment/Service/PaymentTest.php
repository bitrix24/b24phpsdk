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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\Payment\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Sale\Order\Service\Order;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentItemResult;
use Bitrix24\SDK\Services\Sale\Payment\Service\Payment;
use Bitrix24\SDK\Services\Sale\Service\PersonType;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class PaymentTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\Payment\Service
 */
#[CoversMethod(Payment::class,'add')]
#[CoversMethod(Payment::class,'update')]
#[CoversMethod(Payment::class,'get')]
#[CoversMethod(Payment::class,'list')]
#[CoversMethod(Payment::class,'delete')]
#[CoversMethod(Payment::class,'getFields')]
#[CoversMethod(PaymentTest::class,'testAllSystemFieldsAnnotated')]
#[CoversMethod(PaymentTest::class,'testAllSystemFieldsHasValidTypeAnnotation')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\Payment\Service\Payment::class)]
class PaymentTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Payment $paymentService;

    protected int $orderId = 0;

    protected int $personTypeId = 0;

    protected int $paySystemId = 0;

    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->paymentService = $serviceBuilder->getSaleScope()->payment();
        $this->personTypeId = $this->getPersonTypeId();
        $this->paySystemId = $this->getPaySystemId();
        $this->orderId = $this->createTestOrder();
    }

    protected function tearDown(): void
    {
        // Clean up created resources
        $this->deleteTestOrder($this->orderId);
        $this->deletePersonType($this->personTypeId);
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->paymentService->getFields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, PaymentItemResult::class);
    }
    
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->paymentService->getFields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            PaymentItemResult::class);
    }

    /**
     * Helper method to create a person type for testing
     */
    protected function getPersonTypeId(): int
    {
        $personTypeService = Fabric::getServiceBuilder()->getSaleScope()->personType();
        return $personTypeService->add([
            'name' => 'Test Person Type for Payment',
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
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create a payment
        $paymentFields = [
            'orderId' => $this->orderId,
            'paySystemId' => $this->paySystemId,
            'sum' => 100.00,
            'currency' => 'USD'
        ];

        $paymentAddedResult = $this->paymentService->add($paymentFields);
        $paymentId = $paymentAddedResult->getId();

        self::assertGreaterThan(0, $paymentId);

        // Clean up
        $this->paymentService->delete($paymentId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a payment
        $paymentFields = [
            'orderId' => $this->orderId,
            'paySystemId' => $this->paySystemId,
            'sum' => 100.00,
            'currency' => 'USD'
        ];

        $paymentAddedResult = $this->paymentService->add($paymentFields);
        $paymentId = $paymentAddedResult->getId();

        // Update the payment
        $updateFields = [
            'paySystemId' => $this->paySystemId,
            'comments' => 'Updated Test Payment',
            'sum' => 150.00
        ];

        $this->paymentService->update($paymentId, $updateFields);

        // Verify the update
        $payment = $this->paymentService->get($paymentId)->payment();
        self::assertEquals('Updated Test Payment', $payment->comments);

        // Clean up
        $this->paymentService->delete($paymentId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Create a payment
        $paymentFields = [
            'orderId' => $this->orderId,
            'paySystemId' => $this->paySystemId,
            'sum' => 100.00,
            'currency' => 'USD',
            'comments' => 'Test Payment Comment'
        ];

        $paymentAddedResult = $this->paymentService->add($paymentFields);
        $paymentId = $paymentAddedResult->getId();

        // Get the payment
        $payment = $this->paymentService->get($paymentId)->payment();

        self::assertEquals($paymentId, $payment->id);
        self::assertEquals('Test Payment Comment', $payment->comments);

        // Clean up
        $this->paymentService->delete($paymentId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a payment
        $paymentFields = [
            'orderId' => $this->orderId,
            'paySystemId' => $this->paySystemId,
            'sum' => 100.00,
            'currency' => 'USD',
            'comments' => 'Test List Payment'
        ];

        $paymentAddedResult = $this->paymentService->add($paymentFields);
        $paymentId = $paymentAddedResult->getId();

        // List payments
        $filter = ['orderId' => $this->orderId];
        $paymentsResult = $this->paymentService->list([], $filter);
        $payments = $paymentsResult->getPayments();

        self::assertGreaterThan(0, count($payments));

        // Verify our payment is in the list
        $found = false;
        foreach ($payments as $payment) {
            if ((int)$payment->id === $paymentId) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found);

        // Clean up
        $this->paymentService->delete($paymentId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a payment
        $paymentFields = [
            'orderId' => $this->orderId,
            'paySystemId' => $this->paySystemId,
            'sum' => 100.00,
            'currency' => 'USD'
        ];

        $paymentAddedResult = $this->paymentService->add($paymentFields);
        $paymentId = $paymentAddedResult->getId();

        // Delete the payment
        $this->paymentService->delete($paymentId);

        // Verify payment no longer exists
        try {
            $this->paymentService->get($paymentId);
            self::fail('Expected exception when getting deleted payment');
        } catch (\Exception $exception) {
            // Expected exception - check for error message indicating payment doesn't exist
            self::assertStringContainsString('payment is not exists', $exception->getMessage());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        // Get fields for payment
        $paymentFieldsResult = $this->paymentService->getFields();
        $fields = $paymentFieldsResult->getFieldsDescription();

        // Verify fields structure
        self::assertIsArray($fields);
        // Verify basic payment fields are present
        self::assertArrayHasKey('orderId', $fields);
        self::assertArrayHasKey('paySystemId', $fields);
        self::assertArrayHasKey('sum', $fields);
    }
}
