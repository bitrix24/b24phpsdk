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
use Bitrix24\SDK\Services\Sale\BasketItem\Service\BasketItem;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\BasketItem\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\BasketItem\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected BasketItem $basketItemService;

    protected int $orderId;

    protected int $personTypeId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
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

    public function testOwnedBasketItemsKeepUppercaseAndNameOrderAcrossPages(): void
    {
        $ids = [];
        $names = [];
        try {
            $fields = [];
            for ($i = 0; $i < 105; ++$i) {
                $fields[] = ['orderId' => $this->orderId, 'productId' => 0, 'currency' => 'USD', 'quantity' => 1.0, 'name' => sprintf('sdk-363-%03d', 105 - $i)];
            }

            foreach ($this->basketItemService->batch->add($fields) as $position => $result) {
                $id = $result->getId();
                $ids[] = $id;
                $names[$id] = $fields[$position]['name'];
            }

            self::assertCount(105, $ids);
            $filter = ['orderId' => $this->orderId];
            $expected = $ids;
            sort($expected, SORT_NUMERIC);
            $actual = [];
            foreach ($this->basketItemService->batch->list($filter, ['id' => 'ASC'], ['id']) as $item) {
                $actual[] = $item->id;
            }

            self::assertSame($expected, $actual);
            asort($names, SORT_STRING);
            $actual = [];
            foreach ($this->basketItemService->batch->list($filter, ['name' => 'ASC'], ['id', 'name'], 51) as $item) {
                $actual[] = $item->id;
            }

            self::assertSame(array_slice(array_keys($names), 0, 51), $actual);
        } finally {
            foreach ($ids as $id) {
                $this->basketItemService->delete($id);
            }
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();

        // Delete test order
        $serviceBuilder->getSaleScope()->order()->delete($this->orderId);

        // Delete person type
        $serviceBuilder->getSaleScope()->personType()->delete($this->personTypeId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add basket items')]
    public function testBatchAdd(): void
    {
        $items = [];
        for ($i = 1; $i < 60; $i++) {
            $items[] = [
                'orderId' => $this->orderId,
                'productId' => 0,
                'currency' => 'USD',
                'quantity' => 1.0,
                'name' => sprintf('Test Product %d', $i)
            ];
        }

        $cnt = 0;
        $basketItemIds = [];
        foreach ($this->basketItemService->batch->add($items) as $item) {
            $cnt++;
            $basketItemIds[] = $item->getId();
        }

        self::assertEquals(count($items), $cnt);

        // Delete created basket items
        foreach ($basketItemIds as $basketItemId) {
            $this->basketItemService->delete($basketItemId);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update basket items')]
    public function testBatchUpdate(): void
    {
        // Create several test basket items
        $items = [];
        for ($i = 1; $i < 60; $i++) {
            $items[] = [
                'orderId' => $this->orderId,
                'productId' => 0,
                'currency' => 'USD',
                'quantity' => 1.0,
                'name' => sprintf('Test Product %d', $i)
            ];
        }

        $basketItemIds = [];
        foreach ($this->basketItemService->batch->add($items) as $item) {
            $basketItemIds[] = $item->getId();
        }

        // Prepare update data
        $updates = [];
        foreach ($basketItemIds as $i => $basketItemId) {
            $updates[$basketItemId] = [
                'fields' => [
                    'quantity' => 2.0,
                    'name' => sprintf('Updated Test Product %d', $i + 1)
                ]
            ];
        }

        $cnt = 0;
        foreach ($this->basketItemService->batch->update($updates) as $cnt => $updateResult) {
            $cnt++;
            self::assertTrue($updateResult->isSuccess());
        }

        self::assertEquals(count($updates), $cnt);

        // Delete test basket items
        foreach ($basketItemIds as $basketItemId) {
            $this->basketItemService->delete($basketItemId);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list basket items')]
    public function testBatchList(): void
    {
        // Create several test basket items
        $items = [];
        for ($i = 1; $i < 53; $i++) {
            $items[] = [
                'orderId' => $this->orderId,
                'productId' => 0,
                'currency' => 'USD',
                'quantity' => 1.0,
                'name' => sprintf('Test Product %d', $i)
            ];
        }

        $basketItemIds = [];
        foreach ($this->basketItemService->batch->add($items) as $item) {
            $basketItemIds[] = $item->getId();
        }

        // Get basket items with filter by order
        $filter = ['orderId' => $this->orderId];
        $cnt = 0;
        $foundIds = [];

        foreach ($this->basketItemService->batch->list($filter, ['id' => 'desc'], ['id', 'quantity', 'name']) as $basketItem) {
            $cnt++;
            $foundIds[] = $basketItem->id;
        }

        // Verify that all created items are found
        self::assertGreaterThanOrEqual(count($basketItemIds), $cnt);
        foreach ($basketItemIds as $basketItemId) {
            self::assertContains($basketItemId, $foundIds);
        }

        // Delete test basket items
        foreach ($basketItemIds as $basketItemId) {
            $this->basketItemService->delete($basketItemId);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete basket items')]
    public function testBatchDelete(): void
    {
        // Create several test basket items
        $items = [];
        for ($i = 1; $i < 60; $i++) {
            $items[] = [
                'orderId' => $this->orderId,
                'productId' => 0,
                'currency' => 'USD',
                'quantity' => 1.0,
                'name' => sprintf('Test Product %d', $i)
            ];
        }

        $cnt = 0;
        $basketItemIds = [];
        foreach ($this->basketItemService->batch->add($items) as $item) {
            $cnt++;
            $basketItemIds[] = $item->getId();
        }

        self::assertEquals(count($items), $cnt);

        $cnt = 0;
        foreach ($this->basketItemService->batch->delete($basketItemIds) as $cnt => $deleteResult) {
            $cnt++;
            self::assertTrue($deleteResult->isSuccess());
        }

        self::assertEquals(count($basketItemIds), $cnt);
    }
}
