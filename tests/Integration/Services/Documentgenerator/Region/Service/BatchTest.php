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

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Region\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Region\Service\Region;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Region\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Documentgenerator\Region\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Region $regionService;

    private Faker\Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->regionService = Fabric::getServiceBuilder()->getDocumentgeneratorScope()->region();
        $this->faker = Faker\Factory::create();
    }

    /**
     * Helper: silently delete a region by id.
     * documentgenerator.region.delete has a known server-side bug on some portals.
     */
    private function safeDelete(int $id): void
    {
        try {
            $this->regionService->delete($id);
        } catch (BaseException) {
            // Server-side delete bug; ignored during cleanup
        }
    }

    /**
     * Helper: silently batch-delete regions by ids.
     */
    private function safeBatchDelete(array $ids): void
    {
        try {
            foreach ($this->regionService->batch->delete($ids) as $deleted) {
                unset($deleted);
            }
        } catch (BaseException) {
            // Server-side delete bug; ignored during cleanup
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list regions')]
    public function testBatchList(): void
    {
        $id = $this->regionService->add([
            'languageId' => 'en',
            'title' => 'SDK_BATCH_LIST_' . $this->faker->uuid(),
        ])->getId();

        $cnt = 0;
        foreach ($this->regionService->batch->list(1) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);

        // Cleanup
        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add regions')]
    public function testBatchAdd(): void
    {
        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $items[] = [
                'languageId' => 'en',
                'title' => 'SDK_BATCH_ADD_' . $this->faker->uuid(),
            ];
        }

        $ids = [];
        $cnt = 0;
        foreach ($this->regionService->batch->add($items) as $added) {
            $cnt++;
            $ids[] = $added->getId();
        }

        self::assertEquals(count($items), $cnt);

        // Cleanup
        $this->safeBatchDelete($ids);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update regions')]
    public function testBatchUpdate(): void
    {
        $ids = [];
        for ($i = 1; $i <= 3; $i++) {
            $ids[] = $this->regionService->add([
                'languageId' => 'en',
                'title' => 'SDK_BATCH_UPD_' . $this->faker->uuid(),
            ])->getId();
        }

        $updatePayload = [];
        foreach ($ids as $id) {
            $updatePayload[$id] = [
                'fields' => [
                    'title' => 'SDK_BATCH_UPD_UPDATED_' . $this->faker->uuid(),
                ],
            ];
        }

        foreach ($this->regionService->batch->update($updatePayload) as $updated) {
            $this->assertTrue($updated->isSuccess());
        }

        // Cleanup
        $this->safeBatchDelete($ids);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete regions')]
    public function testBatchDelete(): void
    {
        $ids = [];
        for ($i = 1; $i <= 3; $i++) {
            $ids[] = $this->regionService->add([
                'languageId' => 'en',
                'title' => 'SDK_BATCH_DEL_' . $this->faker->uuid(),
            ])->getId();
        }

        try {
            $delCnt = 0;
            foreach ($this->regionService->batch->delete($ids) as $deleted) {
                $delCnt++;
            }

            self::assertEquals(count($ids), $delCnt);
        } catch (BaseException $baseException) {
            $this->markTestSkipped(
                'documentgenerator.region.delete has a known server-side bug on this portal: ' . $baseException->getMessage()
            );
        }
    }
}
