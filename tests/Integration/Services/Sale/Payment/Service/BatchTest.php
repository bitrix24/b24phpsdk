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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\Payment\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\Payment\Service\Batch;
use Bitrix24\SDK\Services\Sale\Payment\Service\Payment;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\Payment\Batch::class)]
class BatchTest extends TestCase
{
    private const ITEMS_COUNT = 55;

    private SaleServiceBuilder $saleServiceBuilder;

    private Payment $paymentService;

    private int $personTypeId;

    private int $paySystemId;

    private int $orderId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->saleServiceBuilder = Factory::getServiceBuilder()->getSaleScope();
        $this->paymentService = $this->saleServiceBuilder->payment();

        $paySystems = Factory::getCore()->call('sale.paysystem.list', [
            'select' => ['ID'],
            'filter' => ['ACTIVE' => 'Y'],
            'order' => ['ID' => 'ASC'],
        ])->getResponseData()->getResult();
        if ($paySystems === []) {
            $this->markTestSkipped('No payment systems available for testing');
        }

        $this->paySystemId = (int)$paySystems[0]['ID'];
        $this->personTypeId = $this->saleServiceBuilder->personType()->add([
            'name' => 'Test Person Type for batch ' . uniqid(),
            'sort' => 100,
        ])->getId();
        $this->orderId = $this->saleServiceBuilder->order()->add([
            'lid' => 's1',
            'personTypeId' => $this->personTypeId,
            'currency' => 'USD',
            'price' => 100.00,
        ])->getId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        if (isset($this->orderId)) {
            $this->saleServiceBuilder->order()->delete($this->orderId);
        }

        if (isset($this->personTypeId)) {
            $this->saleServiceBuilder->personType()->delete($this->personTypeId);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::list, Batch::delete payments')]
    public function testAddUpdateListDelete(): void
    {
        $items = [];
        for ($i = 1; $i <= self::ITEMS_COUNT; $i++) {
            $items[] = [
                'orderId' => $this->orderId,
                'paySystemId' => $this->paySystemId,
                'sum' => 1.00,
                'currency' => 'USD',
            ];
        }

        $addedIds = [];
        foreach ($this->paymentService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        foreach ($addedIds as $id) {
            $updates[$id] = ['paySystemId' => $this->paySystemId, 'comments' => 'Updated ' . $id];
        }

        foreach ($this->paymentService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->paymentService->batch->list(['id', 'comments'], ['orderId' => $this->orderId]) as $item) {
            $listed[(int)$item->id] = $item->comments;
        }

        foreach ($addedIds as $id) {
            $this->assertArrayHasKey($id, $listed);
            $this->assertSame('Updated ' . $id, $listed[$id]);
        }

        $deletedCount = 0;
        foreach ($this->paymentService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
