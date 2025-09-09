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
use Bitrix24\SDK\Tests\Integration\Fabric;
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
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
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

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function tearDown(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();

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
