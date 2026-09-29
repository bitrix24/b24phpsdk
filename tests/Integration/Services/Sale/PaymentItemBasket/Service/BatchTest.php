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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\PaymentItemBasket\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\PaymentItemBasket\Service\Batch;
use Bitrix24\SDK\Services\Sale\PaymentItemBasket\Service\PaymentItemBasket;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\Sale\SaleBatchFixturesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\PaymentItemBasket\Batch::class)]
class BatchTest extends TestCase
{
    use SaleBatchFixturesTrait;

    private const ITEMS_COUNT = 3;

    private SaleServiceBuilder $saleServiceBuilder;

    private PaymentItemBasket $paymentItemBasketService;

    private int $personTypeId;

    private int $orderId;

    private int $paymentId;

    /**
     * @var int[]
     */
    private array $basketItemIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->saleServiceBuilder = Factory::getServiceBuilder()->getSaleScope();
        $this->paymentItemBasketService = $this->saleServiceBuilder->paymentItemBasket();

        $paySystemId = $this->getActivePaySystemId();
        $this->personTypeId = $this->createPersonType();
        $this->orderId = $this->createOrder($this->personTypeId);
        $this->paymentId = $this->createPayment($this->orderId, $paySystemId);
        for ($i = 0; $i < self::ITEMS_COUNT; $i++) {
            $this->basketItemIds[] = $this->createBasketItem($this->orderId);
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->cleanup(fn () => $this->saleServiceBuilder->payment()->delete($this->paymentId));
        foreach ($this->basketItemIds as $basketItemId) {
            $this->cleanup(fn () => $this->saleServiceBuilder->basketItem()->delete($basketItemId));
        }

        $this->cleanup(fn () => $this->saleServiceBuilder->order()->delete($this->orderId));
        $this->cleanup(fn () => $this->saleServiceBuilder->personType()->delete($this->personTypeId));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::list, Batch::delete payment item basket bindings')]
    public function testAddUpdateListDelete(): void
    {
        $items = [];
        foreach ($this->basketItemIds as $basketItemId) {
            $items[] = [
                'paymentId' => $this->paymentId,
                'basketId' => $basketItemId,
                'quantity' => 1.0,
            ];
        }

        $addedIds = [];
        foreach ($this->paymentItemBasketService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        foreach ($addedIds as $id) {
            $updates[$id] = ['quantity' => 2.0];
        }

        foreach ($this->paymentItemBasketService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->paymentItemBasketService->batch->list(['id', 'quantity'], ['paymentId' => $this->paymentId]) as $item) {
            $listed[(int)$item->id] = (float)$item->quantity;
        }

        foreach ($addedIds as $id) {
            $this->assertArrayHasKey($id, $listed);
            $this->assertSame(2.0, $listed[$id]);
        }

        $deletedCount = 0;
        foreach ($this->paymentItemBasketService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
