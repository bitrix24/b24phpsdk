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
use Bitrix24\SDK\Services\User\Service\User;
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
    
    protected int $userId;
    
    protected function setUp(): void
    {
        $this->taskService = Fabric::getServiceBuilder()->getTaskScope()->task();
        $this->userId = Fabric::getServiceBuilder()->getUserScope()->user()->current()->user()->ID;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch get tasks list')]
    public function testBatchList(): void
    {
        $taskNum = 60;
        $taskIds = [];
        
        for ($i=0;$i<$taskNum;$i++) {
            $taskIds[] = $this->taskService->add([
                'TITLE' => 'Test #'.$i,
                'RESPONSIBLE_ID' => $this->userId,
            ])->getId();
        }
        $cnt = 0;
        foreach ($this->taskService->batch->list([], ['RESPONSIBLE_ID' => $this->userId]) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual($taskNum, $cnt);

        $cnt = 0;
        foreach ($this->taskService->batch->delete($taskIds) as $cnt => $deleteResult) {
            $cnt++;
        }
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add tasks')]
    public function testBatchAdd(): void
    {
        $taskNum = 60;
        $items = [];
        for ($i = 1; $i < $taskNum; $i++) {
            $items[] = [
                'TITLE' => 'Test #'.$i,
                'RESPONSIBLE_ID' => $this->userId,
            ];
        }

        $cnt = 0;
        $taskIds = [];
        foreach ($this->taskService->batch->add($items) as $item) {
            $cnt++;
            $taskIds[] = $item->getId();
        }

        self::assertEquals(count($items), $cnt);

        $cnt = 0;
        foreach ($this->taskService->batch->delete($taskIds) as $cnt => $deleteResult) {
            $cnt++;
        }
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete tasks')]
    public function testBatchDelete(): void
    {
        $taskNum = 60;
        $items = [];
        for ($i = 1; $i < $taskNum; $i++) {
            $items[] = [
                'TITLE' => 'Test #'.$i,
                'RESPONSIBLE_ID' => $this->userId,
            ];
        }

        $taskIds = [];
        foreach ($this->taskService->batch->add($items) as $item) {
            $taskIds[] = $item->getId();
        }

        $cnt = 0;
        foreach ($this->taskService->batch->delete($taskIds) as $cnt => $deleteResult) {
            $cnt++;
        }
        
        self::assertEquals(count($items), $cnt);
    }
    
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update tasks')]
    public function testBatchUpdate(): void
    {
        $taskNum = 60;
        $items = [];
        for ($i = 1; $i < $taskNum; $i++) {
            $items[] = [
                'TITLE' => 'Test #'.$i,
                'RESPONSIBLE_ID' => $this->userId,
            ];
        }

        $taskIds = [];
        foreach ($this->taskService->batch->add($items) as $item) {
            $taskIds[] = $item->getId();
        }
        
        $updates = [];
        foreach ($taskIds as $taskId) {
            $updates[$taskId] = [
                'TITLE' => 'Test #'.$taskId,
            ];
        }

        $cnt = 0;
        foreach ($this->taskService->batch->update($updates) as $cnt => $updateResult) {
            $cnt++;
            self::assertTrue($updateResult->isSuccess());
        }

        self::assertEquals(count($updates), $cnt);
        
        $cnt = 0;
        foreach ($this->taskService->batch->delete($taskIds) as $cnt => $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }
}
