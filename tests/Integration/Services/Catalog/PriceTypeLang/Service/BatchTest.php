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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\PriceTypeLang\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\PriceType\Service\PriceType;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Service\Batch;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Service\PriceTypeLang;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private PriceTypeLang $priceTypeLangService;

    private PriceType $priceTypeService;

    private int $priceTypeId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->priceTypeLangService = $serviceBuilder->getCatalogScope()->priceTypeLang();
        $this->priceTypeService = $serviceBuilder->getCatalogScope()->priceType();

        $this->priceTypeId = $this->priceTypeService->add([
            'name' => sprintf('test price type for batch lang %s', time()),
            'sort' => 50,
        ])->priceType()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->priceTypeService->delete($this->priceTypeId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Batch::add, Batch::update, Batch::delete')]
    public function testAddUpdateDelete(): void
    {
        $addedIds = [];
        foreach ($this->priceTypeLangService->batch->add([
            ['catalogGroupId' => $this->priceTypeId, 'lang' => 'kz', 'name' => 'PRICE'],
        ]) as $addedItemResult) {
            $addedIds[] = $addedItemResult->priceTypeLang()->id;
        }

        $this->assertCount(1, $addedIds);

        $updatePayload = [];
        foreach ($addedIds as $id) {
            $updatePayload[$id] = ['name' => 'Updated Price'];
        }

        $updatedCount = 0;
        foreach ($this->priceTypeLangService->batch->update($updatePayload) as $updatedItemResult) {
            $this->assertSame('Updated Price', $updatedItemResult->priceTypeLang()->name);
            $updatedCount++;
        }

        $this->assertSame(1, $updatedCount);

        $deletedCount = 0;
        foreach ($this->priceTypeLangService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(1, $deletedCount);
    }
}
