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
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\Task\Result\TaskItemResult;
use Bitrix24\SDK\Services\Task\Service\Task;
use Bitrix24\SDK\Services\Task\Service\TaskItemBuilder;
use Bitrix24\SDK\Services\Task\Service\TaskItemSelectBuilder;
use Bitrix24\SDK\Services\User\Service\User;
use Bitrix24\SDK\Tests\Builders\Services\Task\TaskBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Class TaskTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Task\Service
 */
#[CoversMethod(Task::class, 'get')]
#[CoversMethod(Task::class, 'add')]
#[CoversMethod(Task::class, 'delete')]
#[CoversClass(Task::class)]
class TaskTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Task $taskService;

    protected User $userService;

    protected ServiceBuilder $serviceBuilder;

    #[\Override]
    protected function setUp(): void
    {
        $this->taskService = Factory::getServiceBuilder(false)->getTaskScope()->task();
        $this->userService = Factory::getServiceBuilder()->getUserScope()->user();
        $this->serviceBuilder = Factory::getServiceBuilder();
    }

    #[TestDox('Get task by id with all fields')]
    public function testGetTaskByIdWithAllFields(): void
    {
        $curUser = $this->userService->current()->user();
        $addedTask = $this->taskService->add(
            new TaskItemBuilder(
                sprintf('Test task %s', time()),
                $curUser->ID,
                $curUser->ID
            )
        );

        $res = $this->taskService->get($addedTask->task()->id);

        $this->assertEquals($addedTask->task(), $res->task());

        $this->taskService->delete($addedTask->task()->id);
    }

    #[TestDox('Get task by id with selected fields from select builder')]
    public function testGetTaskByIdWithSelectedFields(): void
    {
        $curUser = $this->userService->current()->user();
        $addedTask = $this->taskService->add(
            new TaskItemBuilder(
                sprintf('Test task %s', time()),
                $curUser->ID,
                $curUser->ID
            )
        );

        $select = new TaskItemSelectBuilder()
            ->title();

        $res = $this->taskService->get(
            $addedTask->task()->id,
            $select
        );

        $this->assertEquals(
            array_keys($res->getCoreResponse()->getResponseData()->getResult()['item']),
            $select->buildSelect()
        );

        $this->assertEquals($addedTask->task()->id, $res->task()->id);
        $this->taskService->delete($addedTask->task()->id);
    }

    #[TestDox('Add task with default fields')]
    public function testAddTaskWithDefaultFields(): void
    {
        $curUser = $this->userService->current()->user();
        $addedTask = $this->taskService->add(
            new TaskItemBuilder(
                sprintf('Test task %s', time()),
                $curUser->ID,
                $curUser->ID
            )
                ->description(sprintf('Test task description %s', time()))
        );

        $res = $this->taskService->get($addedTask->task()->id);

        $this->assertEquals($addedTask->task(), $res->task());

        $this->taskService->delete($addedTask->task()->id);
    }

    #[TestDox('Delete task with id')]
    public function testDeleteTask(): void
    {
        $curUser = $this->userService->current()->user();
        $addedTask = $this->taskService->add(
            new TaskItemBuilder(
                sprintf('Test task %s', time()),
                $curUser->ID,
                $curUser->ID
            )
        );
        $this->assertTrue($this->taskService->delete($addedTask->task()->id)->isSuccess());
    }

    #[TestDox('Update task')]
    public function testUpdateTask(): void
    {
        $curUser = $this->userService->current()->user();
        $addedTask = $this->taskService->add(
            new TaskItemBuilder(
                sprintf('Test task %s', time()),
                $curUser->ID,
                $curUser->ID
            )
                ->description(sprintf('Test task description %s', time()))
        );

//        var_dump($addedTask->getCoreResponse()->getResponseData()->getResult());

//        var_dump(
//            $this->taskService->get($addedTask->task()->id, ['id', 'title', 'description', 'creatorId', 'responsibleId'])->getCoreResponse()->getResponseData(
//            )->getResult()
//        );

        $this->assertTrue(
            $this->taskService->update(
                $addedTask->task()->id,
                new TaskItemBuilder(
                    $addedTask->task()->title,
                    $curUser->ID,
                    $curUser->ID,
                )
                    ->description('updated description')
            )->isSuccess()
        );

        $res = $this->taskService->get($addedTask->task()->id);

        $this->assertEquals('updated description', $res->task()->description);
        // $this->taskService->delete($addedTask->task()->id);
    }
}
