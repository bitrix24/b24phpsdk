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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Measure\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Measure\Result\MeasureItemResult;
use Bitrix24\SDK\Services\Catalog\Measure\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Measure\Service\Measure;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Faker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Measure $measureService;

    private Faker\Generator $faker;

    /** @var int[] */
    private array $measureIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->measureService = Fabric::getServiceBuilder()->getCatalogScope()->measure();
        $this->faker = Faker\Factory::create();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->measureIds as $measureId) {
            $this->measureService->delete($measureId);
        }

        $this->measureIds = [];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchAddAndList(): void
    {
        $items = [
            [
                'code' => $this->faker->unique()->numberBetween(100000, 999999),
                'measureTitle' => 'SDK_BATCH_TEST_' . $this->faker->uuid(),
                'isDefault' => 'N',
            ],
            [
                'code' => $this->faker->unique()->numberBetween(100000, 999999),
                'measureTitle' => 'SDK_BATCH_TEST_' . $this->faker->uuid(),
                'isDefault' => 'N',
            ],
        ];

        foreach ($this->measureService->batch->add($items) as $result) {
            self::assertGreaterThan(0, $result->getId());
            $this->measureIds[] = $result->getId();
        }

        self::assertCount(2, $this->measureIds);

        $found = [];
        foreach ($this->measureService->batch->list(['id' => 'ASC'], [], ['id', 'measureTitle']) as $item) {
            self::assertInstanceOf(MeasureItemResult::class, $item);
            $found[] = $item->id;
        }

        foreach ($this->measureIds as $measureId) {
            self::assertContains($measureId, $found);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchUpdate(): void
    {
        $measureId = $this->measureService->add([
            'code' => $this->faker->unique()->numberBetween(100000, 999999),
            'measureTitle' => 'SDK_BATCH_TEST_' . $this->faker->uuid(),
            'isDefault' => 'N',
        ])->getId();
        $this->measureIds[] = $measureId;

        $updatedTitle = 'SDK_BATCH_TEST_UPDATED_' . $this->faker->uuid();
        $entityItems = [
            $measureId => [
                'fields' => [
                    'measureTitle' => $updatedTitle,
                ],
            ],
        ];

        foreach ($this->measureService->batch->update($entityItems) as $result) {
            self::assertTrue($result->isSuccess());
        }

        self::assertEquals($updatedTitle, $this->measureService->get($measureId)->measure()->measureTitle);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchDelete(): void
    {
        $measureIds = [];
        for ($i = 0; $i < 2; ++$i) {
            $measureIds[] = $this->measureService->add([
                'code' => $this->faker->unique()->numberBetween(100000, 999999),
                'measureTitle' => 'SDK_BATCH_TEST_' . $this->faker->uuid(),
                'isDefault' => 'N',
            ])->getId();
        }

        $deletedCount = 0;
        foreach ($this->measureService->batch->delete($measureIds) as $result) {
            self::assertTrue($result->isSuccess());
            ++$deletedCount;
        }

        self::assertSame(2, $deletedCount);
    }
}
