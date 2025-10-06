<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 * © Dmitriy Ignatenko <titarx@gmail.com>
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Documentgenerator\Numerator\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Service\Numerator;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Documentgenerator\Numerator\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Numerator $numeratorService;

    private Faker\Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    protected function setUp(): void
    {
        $this->numeratorService = Fabric::getServiceBuilder()->getCRMScope()->documentgeneratorNumerator();
        $this->faker = Faker\Factory::create();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list numerators')]
    public function testBatchList(): void
    {
        $id = $this->numeratorService->add([
            'name' => 'num-' . $this->faker->uuid(),
            'template' => 'BL-{NUMBER}',
        ])->getId();

        $cnt = 0;
        foreach ($this->numeratorService->batch->list(1) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);

        // Cleanup
        $this->numeratorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add numerators')]
    public function testBatchAdd(): void
    {
        $items = [];
        for ($i = 1; $i <= 5; $i++) {
            $items[] = [
                'name' => 'num-' . $this->faker->uuid(),
                'template' => 'BA-{NUMBER}',
            ];
        }

        $ids = [];
        $cnt = 0;
        foreach ($this->numeratorService->batch->add($items) as $added) {
            $cnt++;
            $ids[] = $added->getId();
        }

        self::assertEquals(count($items), $cnt);

        $delCnt = 0;
        foreach ($this->numeratorService->batch->delete($ids) as $deleted) {
            $delCnt++;
        }

        self::assertEquals(count($items), $delCnt);
    }

    /**
     * @throws BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete numerators')]
    public function testBatchDelete(): void
    {
        $items = [];
        for ($i = 1; $i <= 5; $i++) {
            $items[] = [
                'name' => 'num-' . $this->faker->uuid(),
                'template' => 'BD-{NUMBER}',
            ];
        }

        $ids = [];
        foreach ($this->numeratorService->batch->add($items) as $added) {
            $ids[] = $added->getId();
        }

        $delCnt = 0;
        foreach ($this->numeratorService->batch->delete($ids) as $deleted) {
            $delCnt++;
        }

        self::assertEquals(count($items), $delCnt);
    }

    /**
     * @throws BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update numerators')]
    public function testBatchUpdate(): void
    {
        $items = [];
        for ($i = 1; $i <= 5; $i++) {
            $items[] = [
                'name' => 'num-' . $this->faker->uuid(),
                'template' => 'BU-{NUMBER}',
            ];
        }

        $updatePayload = [];
        foreach ($this->numeratorService->batch->add($items) as $added) {
            $id = $added->getId();
            $updatePayload[$id] = [
                'fields' => [
                    'name' => 'updated-' . $id,
                    'template' => 'BU2-{NUMBER}',
                ],
            ];
        }

        foreach ($this->numeratorService->batch->update($updatePayload) as $updated) {
            $this->assertTrue($updated->isSuccess());
        }

        // Cleanup
        $ids = array_keys($updatePayload);
        $deletedCount = 0;
        foreach ($this->numeratorService->batch->delete($ids) as $deleted) {
            $deletedCount++;
        }

        self::assertEquals(count($ids), $deletedCount);

        self::assertTrue(true);
    }
}
