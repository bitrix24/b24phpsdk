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

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Numerator\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Service\Numerator;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Numerator\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Documentgenerator\Numerator\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Numerator $numeratorService;

    private Faker\Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->numeratorService = Factory::getServiceBuilder()->getDocumentgeneratorScope()->numerator();
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
            'name' => 'SDK_BATCH_LIST_' . $this->faker->uuid(),
            'template' => 'BLIST-{NUMBER}',
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
        for ($i = 1; $i <= 3; $i++) {
            $items[] = [
                'name' => 'SDK_BATCH_ADD_' . $this->faker->uuid(),
                'template' => 'BADD-{NUMBER}',
            ];
        }

        $ids = [];
        $cnt = 0;
        foreach ($this->numeratorService->batch->add($items) as $added) {
            $cnt++;
            $ids[] = $added->getId();
        }

        self::assertEquals(count($items), $cnt);

        // Cleanup
        $delCnt = 0;
        foreach ($this->numeratorService->batch->delete($ids) as $deleted) {
            $delCnt++;
        }

        self::assertEquals(count($items), $delCnt);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update numerators')]
    public function testBatchUpdate(): void
    {
        $ids = [];
        for ($i = 1; $i <= 3; $i++) {
            $ids[] = $this->numeratorService->add([
                'name' => 'SDK_BATCH_UPD_' . $this->faker->uuid(),
                'template' => 'BUPD-{NUMBER}',
            ])->getId();
        }

        $updatePayload = [];
        foreach ($ids as $id) {
            $updatePayload[$id] = [
                'fields' => [
                    'name' => 'SDK_BATCH_UPD_UPDATED_' . $this->faker->uuid(),
                ],
            ];
        }

        foreach ($this->numeratorService->batch->update($updatePayload) as $updated) {
            $this->assertTrue($updated->isSuccess());
        }

        // Cleanup
        foreach ($this->numeratorService->batch->delete($ids) as $deleted) {
            unset($deleted); // consume generator to execute batch deletion
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete numerators')]
    public function testBatchDelete(): void
    {
        $ids = [];
        for ($i = 1; $i <= 3; $i++) {
            $ids[] = $this->numeratorService->add([
                'name' => 'SDK_BATCH_DEL_' . $this->faker->uuid(),
                'template' => 'BDEL-{NUMBER}',
            ])->getId();
        }

        $delCnt = 0;
        foreach ($this->numeratorService->batch->delete($ids) as $deleted) {
            $delCnt++;
        }

        self::assertEquals(count($ids), $delCnt);
    }
}



