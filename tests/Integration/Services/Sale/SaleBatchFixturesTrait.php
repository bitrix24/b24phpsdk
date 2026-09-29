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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Tests\Integration\Fabric;

/**
 * Shared fixtures for integration tests of Sale batch services
 */
trait SaleBatchFixturesTrait
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function createPersonType(): int
    {
        return Fabric::getServiceBuilder()->getSaleScope()->personType()->add([
            'name' => 'Test Person Type for batch ' . uniqid(),
            'sort' => 100,
        ])->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function createOrder(int $personTypeId): int
    {
        return Fabric::getServiceBuilder()->getSaleScope()->order()->add([
            'lid' => 's1',
            'personTypeId' => $personTypeId,
            'currency' => 'USD',
            'price' => 100.00,
        ])->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function createBasketItem(int $orderId): int
    {
        return Fabric::getServiceBuilder()->getSaleScope()->basketItem()->add([
            'orderId' => $orderId,
            'productId' => 0,
            'price' => 50.00,
            'quantity' => 2,
            'currency' => 'USD',
            'name' => 'Test Product for batch ' . uniqid(),
        ])->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function createPayment(int $orderId, int $paySystemId): int
    {
        return Fabric::getServiceBuilder()->getSaleScope()->payment()->add([
            'orderId' => $orderId,
            'paySystemId' => $paySystemId,
            'sum' => 10.00,
            'currency' => 'USD',
        ])->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function createShipment(int $orderId, int $deliveryId): int
    {
        return Fabric::getServiceBuilder()->getSaleScope()->shipment()->add([
            'orderId' => $orderId,
            'allowDelivery' => 'Y',
            'deducted' => 'N',
            'deliveryId' => $deliveryId,
        ])->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function createPropertyGroup(int $personTypeId): int
    {
        return Fabric::getServiceBuilder()->getSaleScope()->propertyGroup()->add([
            'personTypeId' => $personTypeId,
            'name' => 'Test Property Group for batch ' . uniqid(),
            'sort' => 100,
        ])->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function createProperty(int $personTypeId, int $propertyGroupId, string $type = 'STRING'): int
    {
        return Fabric::getServiceBuilder()->getSaleScope()->property()->add([
            'personTypeId' => $personTypeId,
            'propsGroupId' => $propertyGroupId,
            'name' => 'Test Property for batch ' . uniqid(),
            'type' => $type,
            'required' => 'N',
            'multiple' => 'N',
            'sort' => 100,
        ])->getId();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDeliveryHandlerFields(string $code): array
    {
        return [
            'NAME' => 'Test Delivery Handler for batch',
            'CODE' => $code,
            'SORT' => 100,
            'DESCRIPTION' => 'Test delivery handler for batch tests',
            'SETTINGS' => [
                'CALCULATE_URL' => 'https://example.com/calculate',
                'CREATE_DELIVERY_REQUEST_URL' => 'https://example.com/create',
                'CANCEL_DELIVERY_REQUEST_URL' => 'https://example.com/cancel',
                'HAS_CALLBACK_TRACKING_SUPPORT' => 'Y',
                'CONFIG' => [
                    [
                        'TYPE' => 'STRING',
                        'CODE' => 'API_KEY',
                        'NAME' => 'API Key',
                    ],
                ],
            ],
            'PROFILES' => [
                [
                    'NAME' => 'Standard',
                    'CODE' => 'STANDARD',
                    'DESCRIPTION' => 'Standard delivery profile',
                ],
            ],
        ];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function createDeliveryHandler(string $code): int
    {
        return Fabric::getServiceBuilder()->getSaleScope()->deliveryHandler()->add(
            $this->getDeliveryHandlerFields($code)
        )->getId();
    }

    /**
     * Returns the code of the delivery handler as it is stored on the portal
     *
     * @throws BaseException
     * @throws TransportException
     */
    protected function getDeliveryHandlerCode(int $handlerId): string
    {
        foreach (Fabric::getServiceBuilder()->getSaleScope()->deliveryHandler()->list()->getDeliveryHandlers() as $deliveryHandlerItemResult) {
            if ((int)$deliveryHandlerItemResult->ID === $handlerId) {
                return (string)$deliveryHandlerItemResult->CODE;
            }
        }

        $this->fail(sprintf('delivery handler %s not found', $handlerId));
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDeliveryFields(string $handlerCode): array
    {
        return [
            'REST_CODE' => $handlerCode,
            'NAME' => 'Test Delivery for batch',
            'CURRENCY' => 'USD',
            'DESCRIPTION' => 'Test delivery service for batch tests',
            'SORT' => 500,
            'ACTIVE' => 'Y',
            'CONFIG' => [
                [
                    'CODE' => 'API_KEY',
                    'VALUE' => 'test_api_key',
                ],
            ],
        ];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function createDelivery(string $handlerCode): int
    {
        return Fabric::getServiceBuilder()->getSaleScope()->delivery()->add(
            $this->getDeliveryFields($handlerCode)
        )->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function getActivePaySystemId(): int
    {
        $paySystems = Fabric::getCore()->call('sale.paysystem.list', [
            'select' => ['ID'],
            'filter' => ['ACTIVE' => 'Y'],
            'order' => ['ID' => 'ASC'],
        ])->getResponseData()->getResult();

        if ($paySystems === []) {
            $this->markTestSkipped('No active payment systems available for testing');
        }

        return (int)$paySystems[0]['ID'];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function getActiveDeliveryId(): int
    {
        $deliveries = Fabric::getCore()->call('sale.delivery.getlist', [
            'SELECT' => ['ID'],
            'FILTER' => ['ACTIVE' => 'Y'],
            'ORDER' => ['ID' => 'ASC'],
        ])->getResponseData()->getResult();

        if ($deliveries === []) {
            $this->markTestSkipped('No active delivery services available for testing');
        }

        return (int)$deliveries[0]['ID'];
    }

    /**
     * Runs cleanup callback and ignores errors, so one failed cleanup does not hide the test result
     */
    protected function cleanup(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable) {
            // ignore cleanup errors
        }
    }
}
