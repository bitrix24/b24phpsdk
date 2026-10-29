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

namespace Bitrix24\SDK\Tests\Integration\Legacy\Services\Task\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Legacy\Services\Task\Service\Batch;
use Bitrix24\SDK\Legacy\Services\Task\Service\Task;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated Tests for the legacy v1 Task batch service. Will be removed once v3 reaches feature parity.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    protected Task $taskService;

    protected int $userId = 0;

    protected function setUp(): void
    {
        $this->taskService = Factory::getServiceBuilder()->getLegacyServiceBuilder()->getTaskScope()->task();
        if (intval($this->userId) == 0) {
            $this->userId = Factory::getServiceBuilder()->getUserScope()->user()->current()->user()->ID;
        }
    }

    public function testOwnedTasksKeepDescendingAndTitleOrderAcrossPages(): void
    {
        $prefix = 'sdk-363-' . bin2hex(random_bytes(8));
        $ids = [];
        $titles = [];
        try {
            $fields = [];
            for ($i = 0; $i < 105; ++$i) {
                $fields[] = ['TITLE' => $prefix . sprintf('-%03d', 105 - $i), 'RESPONSIBLE_ID' => $this->userId];
            }

            foreach ($this->taskService->batch->add($fields) as $position => $result) {
                $id = $result->getId();
                $ids[] = $id;
                $titles[$id] = $fields[$position]['TITLE'];
            }

            self::assertCount(105, $ids);
            $filter = ['%TITLE' => $prefix];
            $expected = $ids;
            rsort($expected, SORT_NUMERIC);
            $actual = [];
            foreach ($this->taskService->batch->list(['ID' => 'DESC'], $filter, ['ID', 'TITLE']) as $task) {
                $actual[] = (int) $task->id;
            }

            self::assertSame($expected, $actual);
            asort($titles, SORT_STRING);
            $actual = [];
            foreach ($this->taskService->batch->list(['TITLE' => 'ASC'], $filter, ['ID', 'TITLE'], 51) as $task) {
                $actual[] = (int) $task->id;
            }

            self::assertSame(array_slice(array_keys($titles), 0, 51), $actual);
        } finally {
            foreach ($ids as $id) {
                $this->taskService->delete($id);
            }
        }
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

        for ($i = 0; $i < $taskNum; $i++) {
            $taskIds[] = $this->taskService->add([
                'TITLE'          => 'Test #-' . $i,
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
     * @throws BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add tasks')]
    public function testBatchAdd(): void
    {
        $taskNum = 60;
        $items = [];
        for ($i = 1; $i < $taskNum; $i++) {
            $items[] = [
                'TITLE'          => 'Test #--' . $i,
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
     * @throws BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete tasks')]
    public function testBatchDelete(): void
    {
        $taskNum = 60;
        $items = [];
        for ($i = 1; $i < $taskNum; $i++) {
            $items[] = [
                'TITLE'          => 'Test #-#' . $i,
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
     * @throws BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update tasks')]
    public function testBatchUpdate(): void
    {
        $taskNum = 60;
        $items = [];
        for ($i = 1; $i < $taskNum; $i++) {
            $items[] = [
                'TITLE'          => 'Test #' . $i,
                'RESPONSIBLE_ID' => $this->userId,
            ];
        }

        $taskIds = [];
        foreach ($this->taskService->batch->add($items) as $item) {
            $taskIds[] = $item->getId();
        }

        $updates = [];
        foreach ($taskIds as $taskId) {
            $updates[$taskId] = ['TITLE' => 'Test ##' . $taskId];
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
