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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Product\ProductService\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Result\ProductServiceItemResult;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Service\ProductService;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private ProductService $productServiceService;

    private Catalog $catalogService;

    private int $iblockId;

    /** @var int[] */
    private array $serviceIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->productServiceService = Fabric::getServiceBuilder()->getCatalogScope()->productService();
        $this->catalogService = Fabric::getServiceBuilder()->getCatalogScope()->catalog();
        $this->iblockId = $this->catalogService->list([], [], [], 0)->getCatalogs()[0]->iblockId;
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->serviceIds as $serviceId) {
            $this->productServiceService->delete($serviceId);
        }

        $this->serviceIds = [];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchAddAndList(): void
    {
        $items = [
            [
                'iblockId' => $this->iblockId,
                'name' => sprintf('sdk batch service 1 %s', time()),
            ],
            [
                'iblockId' => $this->iblockId,
                'name' => sprintf('sdk batch service 2 %s', time()),
            ],
        ];

        foreach ($this->productServiceService->batch->add($items) as $result) {
            self::assertGreaterThan(0, $result->getId());
            $this->serviceIds[] = $result->getId();
        }

        self::assertCount(2, $this->serviceIds);

        $found = [];
        foreach (
            $this->productServiceService->batch->list(
                ['id' => 'ASC'],
                ['iblockId' => $this->iblockId, '@id' => $this->serviceIds],
                ['id', 'iblockId', 'name']
            ) as $item
        ) {
            self::assertInstanceOf(ProductServiceItemResult::class, $item);
            $found[] = $item->id;
        }

        foreach ($this->serviceIds as $serviceId) {
            self::assertContains($serviceId, $found);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchUpdate(): void
    {
        $serviceId = $this->productServiceService->add([
            'iblockId' => $this->iblockId,
            'name' => sprintf('sdk batch service %s', time()),
        ])->productService()->id;
        $this->serviceIds[] = $serviceId;

        $updatedName = sprintf('sdk batch service updated %s', time());
        $entityItems = [
            $serviceId => [
                'fields' => [
                    'name' => $updatedName,
                ],
            ],
        ];

        foreach ($this->productServiceService->batch->update($entityItems) as $result) {
            self::assertEquals($updatedName, $result->productService()->name);
        }

        self::assertEquals(
            $updatedName,
            $this->productServiceService->get($serviceId)->productService()->name
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchDelete(): void
    {
        $serviceIds = [];
        for ($i = 0; $i < 2; ++$i) {
            $serviceIds[] = $this->productServiceService->add([
                'iblockId' => $this->iblockId,
                'name' => sprintf('sdk batch service to delete %s %s', $i, time()),
            ])->productService()->id;
        }

        $deletedCount = 0;
        foreach ($this->productServiceService->batch->delete($serviceIds) as $result) {
            self::assertTrue($result->isSuccess());
            ++$deletedCount;
        }

        self::assertSame(2, $deletedCount);
    }
}
