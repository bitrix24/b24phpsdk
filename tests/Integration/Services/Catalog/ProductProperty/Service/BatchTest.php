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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductProperty\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Service\ProductProperty;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductProperty\Service
 */
#[CoversClass(\Bitrix24\SDK\Services\Catalog\ProductProperty\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected ProductProperty $productPropertyService;

    protected Catalog $catalogService;

    private Generator $faker;

    private int $iblockId;

    /**
     * @throws InvalidArgumentException
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->productPropertyService = Fabric::getServiceBuilder()->getCatalogScope()->productProperty();
        $this->catalogService = Fabric::getServiceBuilder()->getCatalogScope()->catalog();
        $this->faker = FakerFactory::create();
        $this->iblockId = $this->catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;
    }

    /**
     * Helper: silently delete a product property by id.
     */
    private function safeDelete(int $id): void
    {
        try {
            $this->productPropertyService->delete($id);
        } catch (BaseException) {
            // Server-side error; ignored during cleanup
        }
    }

    /**
     * Helper: silently batch-delete product properties by ids.
     *
     * @param int[] $ids
     */
    private function safeBatchDelete(array $ids): void
    {
        try {
            foreach ($this->productPropertyService->batch->delete($ids) as $deleted) {
                unset($deleted);
            }
        } catch (BaseException) {
            // Server-side error; ignored during cleanup
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch list product properties')]
    public function testBatchList(): void
    {
        $id = $this->productPropertyService->add([
            'iblockId' => $this->iblockId,
            'name' => 'SDK_BATCH_LIST_' . $this->faker->uuid(),
            'propertyType' => 'S',
        ])->productProperty()->id;

        $cnt = 0;
        foreach ($this->productPropertyService->batch->list([], ['iblockId' => $this->iblockId]) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);

        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch add product properties')]
    public function testBatchAdd(): void
    {
        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $items[] = [
                'iblockId' => $this->iblockId,
                'name' => 'SDK_BATCH_ADD_' . $this->faker->uuid(),
                'propertyType' => 'S',
            ];
        }

        $ids = [];
        $cnt = 0;
        foreach ($this->productPropertyService->batch->add($items) as $added) {
            $cnt++;
            $ids[] = $added->getId();
        }

        self::assertEquals(count($items), $cnt);

        $this->safeBatchDelete($ids);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch update product properties')]
    public function testBatchUpdate(): void
    {
        $ids = [];
        for ($i = 1; $i <= 3; $i++) {
            $ids[] = $this->productPropertyService->add([
                'iblockId' => $this->iblockId,
                'name' => 'SDK_BATCH_UPD_' . $this->faker->uuid(),
                'propertyType' => 'S',
            ])->productProperty()->id;
        }

        $updatePayload = [];
        foreach ($ids as $id) {
            $updatePayload[$id] = [
                'fields' => [
                    'iblockId' => $this->iblockId,
                    'name' => 'SDK_BATCH_UPD_UPDATED_' . $this->faker->uuid(),
                ],
            ];
        }

        foreach ($this->productPropertyService->batch->update($updatePayload) as $updated) {
            $this->assertTrue($updated->isSuccess());
        }

        $this->safeBatchDelete($ids);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch delete product properties')]
    public function testBatchDelete(): void
    {
        $ids = [];
        for ($i = 1; $i <= 3; $i++) {
            $ids[] = $this->productPropertyService->add([
                'iblockId' => $this->iblockId,
                'name' => 'SDK_BATCH_DEL_' . $this->faker->uuid(),
                'propertyType' => 'S',
            ])->productProperty()->id;
        }

        $delCnt = 0;
        foreach ($this->productPropertyService->batch->delete($ids) as $deleted) {
            $delCnt++;
        }

        self::assertEquals(count($ids), $delCnt);
    }
}
