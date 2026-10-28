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
use Bitrix24\SDK\Tests\Integration\Factory;
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

    #[\Override]
    protected function setUp(): void
    {
        $this->orderService = Factory::getServiceBuilder()->getSaleScope()->order();
    }

    public function testOwnedOrdersKeepDescendingAndPriceOrderAcrossPages(): void
    {
        $personTypeId = $this->getPersonTypeId();
        $ids = [];
        $prices = [];
        try {
            $fields = [];
            for ($i = 0; $i < 105; ++$i) {
                $fields[] = ['lid' => 's1', 'personTypeId' => $personTypeId, 'currency' => 'USD', 'price' => (105 - $i) * 10];
            }

            foreach ($this->orderService->batch->add($fields) as $position => $result) {
                $id = $result->getId();
                $ids[] = $id;
                $prices[$id] = $fields[$position]['price'];
            }

            self::assertCount(105, $ids);
            $filter = ['personTypeId' => $personTypeId];
            $expected = $ids;
            rsort($expected, SORT_NUMERIC);
            $actual = [];
            foreach ($this->orderService->batch->list($filter, ['id' => 'DESC'], ['id']) as $order) {
                $actual[] = $order->id;
            }

            self::assertSame($expected, $actual);
            asort($prices, SORT_NUMERIC);
            $actual = [];
            foreach ($this->orderService->batch->list($filter, ['price' => 'ASC'], ['id', 'price'], 51) as $order) {
                $actual[] = $order->id;
            }

            self::assertSame(array_slice(array_keys($prices), 0, 51), $actual);
        } finally {
            try {
                foreach ($ids as $id) {
                    $this->orderService->delete($id);
                }
            } finally {
                $this->deletePersonType($personTypeId);
            }
        }
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
