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

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Role\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Role\Service\Role;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Role\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Documentgenerator\Role\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Role $roleService;

    private Faker\Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->roleService = Fabric::getServiceBuilder()->getDocumentgeneratorScope()->role();
        $this->faker = Faker\Factory::create();
    }

    /**
     * Helper: silently delete a role by id.
     */
    private function safeDelete(int $id): void
    {
        try {
            $this->roleService->delete($id);
        } catch (BaseException) {
            // Server-side error; ignored during cleanup
        }
    }

    /**
     * Helper: silently batch-delete roles by ids.
     */
    private function safeBatchDelete(array $ids): void
    {
        try {
            foreach ($this->roleService->batch->delete($ids) as $deleted) {
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
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list roles')]
    public function testBatchList(): void
    {
        $id = $this->roleService->add([
            'name' => 'SDK_BATCH_LIST_' . $this->faker->uuid(),
        ])->getId();

        $cnt = 0;
        foreach ($this->roleService->batch->list(1) as $item) {
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
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add roles')]
    public function testBatchAdd(): void
    {
        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $items[] = [
                'name' => 'SDK_BATCH_ADD_' . $this->faker->uuid(),
            ];
        }

        $ids = [];
        $cnt = 0;
        foreach ($this->roleService->batch->add($items) as $added) {
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
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update roles')]
    public function testBatchUpdate(): void
    {
        $ids = [];
        for ($i = 1; $i <= 3; $i++) {
            $ids[] = $this->roleService->add([
                'name' => 'SDK_BATCH_UPD_' . $this->faker->uuid(),
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

        foreach ($this->roleService->batch->update($updatePayload) as $updated) {
            $this->assertTrue($updated->isSuccess());
        }

        // Cleanup
        $this->safeBatchDelete($ids);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete roles')]
    public function testBatchDelete(): void
    {
        $ids = [];
        for ($i = 1; $i <= 3; $i++) {
            $ids[] = $this->roleService->add([
                'name' => 'SDK_BATCH_DEL_' . $this->faker->uuid(),
            ])->getId();
        }

        $delCnt = 0;
        foreach ($this->roleService->batch->delete($ids) as $deleted) {
            $delCnt++;
        }

        self::assertEquals(count($ids), $delCnt);
    }
}
