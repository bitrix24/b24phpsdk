<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Task\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Task\Service\Task;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Task\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Task\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Task $taskService;
    
    protected function setUp(): void
    {
        $this->taskService = Fabric::getServiceBuilder()->getTaskScope()->task();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch get departments')]
    public function testBatchGet(): void
    {
        $depId = $this->taskService->add('Test depart', $this->rootTaskId)->getId();
        $cnt = 0;
        foreach ($this->taskService->batch->get(['ID' => $depId]) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);

        $this->taskService->delete($depId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add department')]
    public function testBatchAdd(): void
    {
        $items = [];
        for ($i = 1; $i < 60; $i++) {
            $items[] = [
                'NAME' => 'Dep-' . $i,
                'PARENT' => $this->rootTaskId
            ];
        }

        $cnt = 0;
        $depId = [];
        foreach ($this->taskService->batch->add($items) as $item) {
            $cnt++;
            $depId[] = $item->getId();
        }

        self::assertEquals(count($items), $cnt);

        $cnt = 0;
        foreach ($this->taskService->batch->delete($depId) as $cnt => $deleteResult) {
            $cnt++;
        }
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete departments')]
    public function testBatchDelete(): void
    {
        $items = [];
        for ($i = 1; $i < 60; $i++) {
            $items[] = [
                'NAME' => 'Dep-' . $i,
                'PARENT' => $this->rootTaskId
            ];
        }

        $cnt = 0;
        $depId = [];
        foreach ($this->taskService->batch->add($items) as $item) {
            $cnt++;
            $depId[] = $item->getId();
        }

        $cnt = 0;
        foreach ($this->taskService->batch->delete($depId) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }
    
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update departments')]
    public function testBatchUpdate(): void
    {
        $items = [];
        for ($i = 1; $i < 60; $i++) {
            $items[] = [
                'NAME' => 'Dep-' . $i,
                'PARENT' => $this->rootTaskId
            ];
        }

        $cnt = 0;
        $depIds = [];
        foreach ($this->taskService->batch->add($items) as $item) {
            $cnt++;
            $depIds[] = $item->getId();
        }
        
        $updates = [];
        foreach ($depIds as $depId) {
            $updates[$depId] = [
                'NAME' => 'Updated '.$depId,
            ];
        }

        $cnt = 0;
        foreach ($this->taskService->batch->update($updates) as $cnt => $updateResult) {
            $cnt++;
            self::assertTrue($updateResult->isSuccess());
        }

        self::assertEquals(count($updates), $cnt);
        
        $cnt = 0;
        foreach ($this->taskService->batch->delete($depIds) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }

}
