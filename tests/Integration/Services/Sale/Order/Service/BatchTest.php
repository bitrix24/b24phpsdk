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
use Bitrix24\SDK\Services\Sale\Order\Service\Order;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\Order\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\Order\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Order $orderService;
    
    protected function setUp(): void
    {
        $this->orderService = Fabric::getServiceBuilder()->getSaleScope()->order();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add orders')]
    public function testBatchAdd(): void
    {
        $personTypeId = $this->getPersonTypeId();
        $items = [];
        for ($i = 1; $i < 10; $i++) {
            $items[] = [
                'lid' => 's1',
                'personTypeId' => $personTypeId,
                'currency' => 'USD', 
                'price' => 100 * ($i + 1)
            ];
        }

        $cnt = 0;
        $orderIds = [];
        foreach ($this->orderService->batch->add($items) as $item) {
            $cnt++;
            $orderIds[] = $item->getId();
        }

        self::assertEquals(count($items), $cnt);

        // Удаляем созданные заказы
        foreach ($orderIds as $orderId) {
            $this->orderService->delete($orderId);
        }

        $this->deletePersonType($personTypeId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update orders')]
    public function testBatchUpdate(): void
    {
        // Создаем несколько тестовых заказов
        $personTypeId = $this->getPersonTypeId();
        $items = [];
        for ($i = 1; $i < 10; $i++) {
            $items[] = [
                'lid' => 's1',
                'personTypeId' => $personTypeId,
                'currency' => 'USD', 
                'price' => 100 * ($i + 1)
            ];
        }

        $orderIds = [];
        foreach ($this->orderService->batch->add($items) as $item) {
            $orderIds[] = $item->getId();
        }

        // Подготавливаем данные для обновления
        $updates = [];
        foreach ($orderIds as $i => $orderId) {
            $updates[$orderId] = [
                'fields' => [
                    'price' => 200 * ($i + 1)
                ]
            ];
        }

        $cnt = 0;
        foreach ($this->orderService->batch->update($updates) as $cnt => $updateResult) {
            $cnt++;
            self::assertTrue($updateResult->isSuccess());
        }

        self::assertEquals(count($updates), $cnt);

        // Удаляем тестовые заказы
        foreach ($orderIds as $orderId) {
            $this->orderService->delete($orderId);
        }

        $this->deletePersonType($personTypeId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list orders')]
    public function testBatchList(): void
    {
        // Создаем несколько тестовых заказов
        $personTypeId = $this->getPersonTypeId();
        $items = [];
        for ($i = 1; $i < 10; $i++) {
            $items[] = [
                'lid' => 's1',
                'personTypeId' => $personTypeId,
                'currency' => 'USD', 
                'price' => 100 * ($i + 1),
                'comments' => 'Test order'.$i,
            ];
        }

        $orderIds = [];
        foreach ($this->orderService->batch->add($items) as $item) {
            $orderIds[] = $item->getId();
        }

        // Получаем заказы с фильтром по комментарию
        $filter = ['%comments' => 'Test order'];
        $cnt = 0;
        $foundIds = [];

        foreach ($this->orderService->batch->list($filter, ['id' => 'desc'], ['id', 'price', 'comments']) as $order) {
            $cnt++;
            $foundIds[] = $order->id;
        }

        // Проверяем, что все созданные заказы найдены
        self::assertGreaterThanOrEqual(count($orderIds), $cnt);
        foreach ($orderIds as $orderId) {
            self::assertContains($orderId, $foundIds);
        }

        // Удаляем тестовые заказы
        foreach ($orderIds as $orderId) {
            $this->orderService->delete($orderId);
        }

        $this->deletePersonType($personTypeId);
    }
    
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete orders')]
    public function testBatchDelete(): void
    {
        // Создаем несколько тестовых заказов
        $personTypeId = $this->getPersonTypeId();
        $items = [];
        for ($i = 1; $i < 10; $i++) {
            $items[] = [
                'lid' => 's1',
                'personTypeId' => $personTypeId,
                'currency' => 'USD', 
                'price' => 100 * ($i + 1),
                'comments' => 'Test order'.$i,
            ];
        }

        $cnt = 0;
        $orderIds = [];
        foreach ($this->orderService->batch->add($items) as $item) {
            $cnt++;
            $orderIds[] = $item->getId();
        }

        self::assertEquals(count($items), $cnt);

        $cnt = 0;
        foreach ($this->orderService->batch->delete($orderIds) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
        $this->deletePersonType($personTypeId);
    }
    
    protected function getPersonTypeId(): int
    {
        $core = Fabric::getCore();
        return (int)$core->call('sale.persontype.add', [
            'fields' => [
                'name' => 'Test Person Type',
                'sort' => 100,
            ]
        ])->getResponseData()->getResult()['personType']['id'];
    }

    protected function deletePersonType(int $id): void
    {
        $core = Fabric::getCore();
        $core->call('sale.persontype.delete', [
            'id' => $id
       ]);
    }
}
