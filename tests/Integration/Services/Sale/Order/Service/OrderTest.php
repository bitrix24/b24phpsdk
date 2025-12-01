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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\Order\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Sale\Order\Result\OrderItemResult;
use Bitrix24\SDK\Services\Sale\Order\Service\Order;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\Order\Service
 */
#[CoversMethod(Order::class,'add')]
#[CoversMethod(Order::class,'delete')]
#[CoversMethod(Order::class,'get')]
#[CoversMethod(Order::class,'list')]
#[CoversMethod(Order::class,'getFields')]
#[CoversMethod(Order::class,'update')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\Order\Service\Order::class)]
class OrderTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Order $orderService;
    
    protected function setUp(): void
    {
        $this->orderService = Factory::getServiceBuilder()->getSaleScope()->order();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->orderService->getFields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, OrderItemResult::class);
    }
    
    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->orderService->getFields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            OrderItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Создаем тестовый заказ с минимально необходимыми полями
        $personTypeId = $this->getPersonTypeId();
        $orderFields = [
            'lid' => 's1',
            'personTypeId' => $personTypeId,
            'currency' => 'USD', 
            'price' => 100.00
        ];
        
        $orderId = $this->orderService->add($orderFields)->getId();
        self::assertGreaterThan(0, $orderId);
        
        // Удаляем тестовый заказ
        $this->orderService->delete($orderId);
        $this->deletePersonType($personTypeId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $personTypeId = $this->getPersonTypeId();
        $orderFields = [
            'lid' => 's1',
            'personTypeId' => $personTypeId,
            'currency' => 'USD', 
            'price' => 100.00
        ];
        
        $orderId = $this->orderService->add($orderFields)->getId();
        
        // Проверяем успешность удаления
        self::assertTrue($this->orderService->delete($orderId)->isSuccess());
        $this->deletePersonType($personTypeId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        // Проверяем, что метод getFields возвращает массив с описанием полей
        self::assertIsArray($this->orderService->getFields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $personTypeId = $this->getPersonTypeId();
        $orderFields = [
            'lid' => 's1',
            'personTypeId' => $personTypeId,
            'currency' => 'USD', 
            'price' => 100.00
        ];
        
        $orderId = $this->orderService->add($orderFields)->getId();
        
        // Получаем заказ и проверяем его ID
        $order = $this->orderService->get($orderId)->order();
        self::assertEquals($orderId, $order->id);
        
        // Удаляем тестовый заказ
        $this->orderService->delete($orderId);
        $this->deletePersonType($personTypeId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $personTypeId = $this->getPersonTypeId();
        $orderFields = [
            'lid' => 's1',
            'personTypeId' => $personTypeId,
            'currency' => 'USD', 
            'price' => 100.00,
            'comments' => 'Test order',
        ];
        
        $orderId = $this->orderService->add($orderFields)->getId();
        
        // Обновляем заказ
        $newComments = 'New comments';
        $updateFields = ['comments' => $newComments];
        
        self::assertTrue($this->orderService->update($orderId, $updateFields)->isSuccess());
        
        // Проверяем, что изменения применились
        $orderItemResult = $this->orderService->get($orderId)->order();
        
        self::assertEquals($newComments, $orderItemResult->comments);
        
        // Удаляем тестовый заказ
        $this->orderService->delete($orderId);
        $this->deletePersonType($personTypeId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Создаем два тестовых заказа
        $orderIds = [];
        $personTypeId = $this->getPersonTypeId();
        for ($i = 0; $i < 2; $i++) {
            $orderFields = [
                'lid' => 's1',
                'personTypeId' => $personTypeId,
                'currency' => 'USD', 
                'price' => 100 * ($i + 1)
            ];

            $orderIds[] = $this->orderService->add($orderFields)->getId();
        }

        // Получаем список заказов и проверяем, что созданные заказы в нем присутствуют
        $orders = $this->orderService->list([], ['id' => 'desc'])->getOrders();

        // Создаем массив ID из полученных заказов
        $returnedIds = array_map(fn($order) => $order->id, $orders);

        // Проверяем, что все наши тестовые заказы присутствуют в списке
        foreach ($orderIds as $orderId) {
            self::assertContains($orderId, $returnedIds);
        }

        // Удаляем тестовые заказы
        foreach ($orderIds as $orderId) {
            $this->orderService->delete($orderId);
        }

        $this->deletePersonType($personTypeId);
    }
    
    protected function getPersonTypeId(): int
    {
        $core = Factory::getCore();
        return (int)$core->call('sale.persontype.add', [
            'fields' => [
                'name' => 'Test Person Type',
                'sort' => 100,
            ]
        ])->getResponseData()->getResult()['personType']['id'];
    }

    protected function deletePersonType(int $id): void
    {
        $core = Factory::getCore();
        $core->call('sale.persontype.delete', [
            'id' => $id
       ]);
    }
}
