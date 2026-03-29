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

use Bitrix24\SDK\Services\Task\Service\Task;
use Bitrix24\SDK\Services\Task\Service\TaskItemBuilder;
use Bitrix24\SDK\Services\Task\Service\TaskItemSelectBuilder;
use Bitrix24\SDK\Services\User\Service\User;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Task::class, 'get')]
#[CoversMethod(Task::class, 'add')]
#[CoversMethod(Task::class, 'delete')]
#[CoversMethod(Task::class, 'update')]
#[CoversClass(Task::class)]
class TaskTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Task $taskService;

    protected User $userService;

    #[\Override]
    protected function setUp(): void
    {
        $this->taskService = Factory::getServiceBuilder(false)->getTaskScope()->task();
        $this->userService = Factory::getServiceBuilder()->getUserScope()->user();
    }

    #[TestDox('Get task by id with all fields')]
    public function testGetTaskByIdWithAllFields(): void
    {
        $userItemResult = $this->userService->current()->user();
        $taskResult = $this->taskService->add(
            new TaskItemBuilder(
                sprintf('Test task %s', time()),
                $userItemResult->ID,
                $userItemResult->ID
            )
        );

        $res = $this->taskService->get($taskResult->task()->id);

        $this->assertEquals($taskResult->task()->id, $res->task()->id);
        $this->taskService->delete($taskResult->task()->id);
    }

    #[TestDox('Get task by id with selected fields from select builder')]
    public function testGetTaskByIdWithSelectedFields(): void
    {
        $userItemResult = $this->userService->current()->user();
        $taskResult = $this->taskService->add(
            new TaskItemBuilder(
                sprintf('Test task %s', time()),
                $userItemResult->ID,
                $userItemResult->ID
            )
        );

        $taskItemSelectBuilder = (new TaskItemSelectBuilder())
            ->title();

        $res = $this->taskService->get(
            $taskResult->task()->id,
            $taskItemSelectBuilder
        );

        $this->assertEquals(
            array_keys($res->getCoreResponse()->getResponseData()->getResult()['item']),
            $taskItemSelectBuilder->buildSelect()
        );

        $this->assertEquals($taskResult->task()->id, $res->task()->id);
        $this->taskService->delete($taskResult->task()->id);
    }

    #[TestDox('Add task with default fields')]
    public function testAddTaskWithDefaultFields(): void
    {
        $userItemResult = $this->userService->current()->user();
        $taskResult = $this->taskService->add(
            (new TaskItemBuilder(
                sprintf('Test task %s', time()),
                $userItemResult->ID,
                $userItemResult->ID
            ))
                ->description(sprintf('Test task description %s', time()))
        );

        $res = $this->taskService->get($taskResult->task()->id);

        $this->assertEquals($taskResult->task(), $res->task());

        $this->taskService->delete($taskResult->task()->id);
    }

    #[TestDox('Delete task with id')]
    public function testDeleteTask(): void
    {
        $userItemResult = $this->userService->current()->user();
        $taskResult = $this->taskService->add(
            new TaskItemBuilder(
                sprintf('Test task %s', time()),
                $userItemResult->ID,
                $userItemResult->ID
            )
        );
        $this->assertTrue($this->taskService->delete($taskResult->task()->id)->isSuccess());
    }

    #[TestDox('Update task')]
    public function testUpdateTask(): void
    {
        $userItemResult = $this->userService->current()->user();
        $taskResult = $this->taskService->add(
            (new TaskItemBuilder(
                sprintf('Test task %s', time()),
                $userItemResult->ID,
                $userItemResult->ID
            ))
                ->description(sprintf('Test task description %s', time()))
        );

        $this->assertTrue(
            $this->taskService->update(
                $taskResult->task()->id,
                (new TaskItemBuilder(
                    $taskResult->task()->title,
                    $userItemResult->ID,
                    $userItemResult->ID,
                ))
                    ->description('updated description')
            )->isSuccess()
        );

        $res = $this->taskService->get($taskResult->task()->id);

        $this->assertEquals('updated description', $res->task()->description);
        $this->taskService->delete($taskResult->task()->id);
    }
}
