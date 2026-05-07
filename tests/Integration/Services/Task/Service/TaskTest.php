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

<<<<<<< HEAD

=======
    #[\Override]
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
    protected function setUp(): void
    {
        $this->taskService = Factory::getServiceBuilder(false)->getTaskScope()->task();
        $this->userService = Factory::getServiceBuilder()->getUserScope()->user();
    }

    #[TestDox('Get task by id with all fields')]
    public function testGetTaskByIdWithAllFields(): void
    {
<<<<<<< HEAD
        $fields = $this->normalizeFieldKeys($this->taskService->fields()->getFieldsDescription());
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($fields));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, TaskItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->normalizeFieldKeys($this->taskService->fields()->getFieldsDescription());
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            TaskItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $taskId = $this->getTaskId();
        self::assertGreaterThan(1, $taskId);

        $this->taskService->delete($taskId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $taskId = $this->getTaskId();
        self::assertTrue($this->taskService->delete($taskId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->taskService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $taskId = $this->getTaskId();
        self::assertGreaterThan(
            1,
            $this->taskService->get($taskId)->task()->id
        );

        $this->taskService->delete($taskId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
=======
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
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
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

<<<<<<< HEAD
        $this->taskService->delete($taskId);
=======
        $this->assertEquals($taskResult->task()->id, $res->task()->id);
        $this->taskService->delete($taskResult->task()->id);
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
    }

    #[TestDox('Add task with default fields')]
    public function testAddTaskWithDefaultFields(): void
    {
<<<<<<< HEAD
        $taskId = $this->getTaskId();
        $newTitle = 'Test2 task';

        self::assertTrue($this->taskService->update($taskId, ['TITLE' => $newTitle])->isSuccess());
        self::assertEquals($newTitle, $this->taskService->get($taskId)->task()->title);

        $this->taskService->delete($taskId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $before = $this->taskService->countByFilter();
        $taskId = $this->getTaskId();
        $after = $this->taskService->countByFilter();
        $this->assertEquals($before + 1, $after);

        $this->taskService->delete($taskId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testAddRemoveDependence(): void
    {
        $taskId = $this->getTaskId('Test task 1');
        $task2Id = $this->getTaskId('Test task 2');

        self::assertTrue($this->taskService->addDependence($taskId, $task2Id, 0)->isSuccess());
        self::assertTrue($this->taskService->deleteDependence($taskId, $task2Id)->isSuccess());

        $this->taskService->delete($task2Id);
        $this->taskService->delete($taskId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelegate(): void
    {
        $taskId = $this->getTaskId();
        $userId = $this->getUserId();

        self::assertTrue($this->taskService->delegate($taskId, $userId)->isSuccess());

        $this->taskService->delete($taskId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetCounters(): void
    {
        $userId = $this->userService->current()->user()->ID;
        $this->assertEquals(
            'expired',
            $this->taskService->getCounters($userId)->getCounters()[0]->key
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetAccess(): void
    {
        $taskId = $this->getTaskId();
        $userId = $this->userService->current()->user()->ID;
        $user2Id = $this->getUserId();

        $this->assertGreaterThanOrEqual(
            1,
            $this->taskService->getAccess($taskId, [$userId, $user2Id])->getAccesses()[0]->getUserId()
        );

        $this->taskService->delete($taskId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testChangeStatus(): void
    {
        $taskId = $this->getTaskId();

        self::assertTrue($this->taskService->start($taskId)->isSuccess());
        self::assertTrue($this->taskService->pause($taskId)->isSuccess());
        self::assertTrue($this->taskService->defer($taskId)->isSuccess());
        self::assertTrue($this->taskService->startwatch($taskId)->isSuccess());
        self::assertTrue($this->taskService->stopwatch($taskId)->isSuccess());
        self::assertTrue($this->taskService->mute($taskId)->isSuccess());
        self::assertTrue($this->taskService->unmute($taskId)->isSuccess());
        self::assertTrue($this->taskService->addFavorite($taskId)->isSuccess());
        self::assertTrue($this->taskService->removeFavorite($taskId)->isSuccess());
        self::assertTrue($this->taskService->complete($taskId)->isSuccess());

        self::assertTrue($this->taskService->renew($taskId)->isSuccess());
        self::assertTrue($this->taskService->start($taskId)->isSuccess());
        self::assertTrue($this->taskService->complete($taskId)->isSuccess());
        // no access to approve
        //self::assertTrue($this->taskService->approve($taskId)->isSuccess());

        // no access to disapprove
        // self::assertTrue($this->taskService->disapprove($taskId)->isSuccess());

        self::assertIsArray(
            $this->taskService->historyList($taskId)->getHistories()[0]->value
        );

        $this->taskService->delete($taskId);
    }

    protected function getTaskId(string $title = 'Test task'): int {
        static $userId;

        if (intval($userId) == 0) {
            $userId = $this->userService->current()->user()->ID;
        }

        return $this->taskService->add(
            [
                'TITLE' => $title,
                'RESPONSIBLE_ID' => $userId,
            ]
        )->getId();
    }

    protected function getUserId(): int {
        static $userId;
        if (intval($userId) == 0) {
            $xmlId = 'PHP-SDK-TEST-USER';
            $user = $this->userService->get(['ID' => 'ASC'], ['XML_ID' => $xmlId], true)->getUsers()[0];
            if ($user && intval($user->ID) > 0) {
                $userId = intval($user->ID);
            }
            else {
                $newUser = [
                    'NAME' => 'Test',
                    'XML_ID' => $xmlId,
                    'EMAIL' => sprintf('%s.test@test.com', time()),
                    'EXTRANET' => 'N',
                    'UF_DEPARTMENT' => [1]
                ];
                $userId = $this->userService->add($newUser)->getId();
            }
        }

        return $userId;
    }

    protected function normalizeFieldKeys(array $fields): array {
        $result = [];
        foreach ($fields as $key => $value) {
            if (str_starts_with($key, 'UF_') && !in_array($key, ['UF_CRM_TASK', 'UF_TASK_WEBDAV_FILES','UF_MAIL_MESSAGE'])) {

                continue;
            }

            $testStr = strtolower($key);
            $testArr = explode('_', $testStr);
            $testStr = array_shift($testArr) . implode('', array_map('ucfirst', $testArr));
            $result[$testStr] = $value;
        }

        return $result;
=======
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
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
    }
}
