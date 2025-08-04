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
use Bitrix24\SDK\Services\Task\Result\TaskItemResult;
use Bitrix24\SDK\Services\Task\Service\Task;
use Bitrix24\SDK\Services\User\Service\User;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class TaskTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Task\Service
 */
#[CoversMethod(Task::class,'add')]
#[CoversMethod(Task::class,'delete')]
#[CoversMethod(Task::class,'get')]
#[CoversMethod(Task::class,'list')]
#[CoversMethod(Task::class,'fields')]
#[CoversMethod(Task::class,'update')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Task\Service\Task::class)]
class TaskTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Task $taskService;
    
    protected User $userService;
    
    
    protected function setUp(): void
    {
        $this->taskService = Fabric::getServiceBuilder()->getTaskScope()->task();
        $this->userService = Fabric::getServiceBuilder()->getUserScope()->user();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->taskService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, TaskItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->taskService->fields()->getFieldsDescription();
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
        $taskId = $this->taskService->add(['TITLE' => 'Test task'])->getId();
        self::assertGreaterThan(1, $taskId);
        
        $this->taskService->delete($taskId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        self::assertTrue($this->taskService->delete($this->taskService->add((['TITLE' => 'Test task'])->getId())->isSuccess());
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
        $taskId = $this->taskService->add(['TITLE' => 'Test task'])->getId();
        self::assertGreaterThan(
            1,
            $this->taskService->get($taskId)->task()->ID
        );
        
        $this->taskService->delete($taskId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $taskId = $this->taskService->add(['TITLE' => 'Test task'])->getId();
        $this->assertEquals(
            1,
            $this->taskService->list(['ID'=>'ASC'], ['ID'=> $taskId])->getCoreResponse()->getResponseData()->getPagination()->getTotal()
        );
        
        $this->taskService->delete($taskId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $taskId = $this->taskService->add(['TITLE' => 'Test task'])->getId();
        $newTitle = 'Test2 task';

        self::assertTrue($this->taskService->update($taskId, ['TITLE' => $newTitle])->isSuccess());
        self::assertEquals($newTitle, $this->taskService->get($taskId)->task()->TITLE);
        
        $this->taskService->delete($taskId);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testCountByFilter(): void
    {
        $before = $this->taskService->countByFilter();
        $taskId = $this->taskService->add(['TITLE' => 'Test task'])->getId();
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
        $taskId = $this->taskService->add(['TITLE' => 'Test task 1'])->getId();
        $task2Id = $this->taskService->add(['TITLE' => 'Test task 2'])->getId();
        
        $this->taskService->addDependence($taskId, $task2Id, 0);
        
        $this->assertEquals($taskId, $this->taskService->get($task2Id)->task()->PARENT_ID);
        
        $this->taskService->deleteDependence($taskId, $task2Id, 0);
        
        $this->assertEquals(0, $this->taskService->get($task2Id)->task()->PARENT_ID);
        
        $this->taskService->delete($task2Id);
        $this->taskService->delete($taskId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelegate(): void
    {
        $taskId = $this->taskService->add(['TITLE' => 'Test task'])->getId();
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
        self::assertIsArray($this->taskService->getCounters($userId)->getCounters());
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetAccess(): void
    {
        $taskId = $this->taskService->add(['TITLE' => 'Test task'])->getId();
        $userId = $this->userService->current()->user()->ID;
        $user2Id = $this->getUserId();

        $this->assertGreaterThanOrEqual(
            1,
            $this->taskService->getAccess($taskId, [$userId, $user2Id])->getCoreResponse()->getResponseData()->getPagination()->getTotal()
        );
        
        $this->taskService->delete($taskId);
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testChangeStatus(): void
    {
        $taskId = $this->taskService->add(['TITLE' => 'Test task'])->getId();

        self::assertTrue($this->taskService->start($taskId)->isSuccess());
        self::assertTrue($this->taskService->pause($taskId)->isSuccess());
        self::assertTrue($this->taskService->defer($taskId)->isSuccess());
        self::assertTrue($this->taskService->start($taskId)->isSuccess());
        self::assertTrue($this->taskService->startwatch($taskId)->isSuccess());
        self::assertTrue($this->taskService->stopwatch($taskId)->isSuccess());
        self::assertTrue($this->taskService->mute($taskId)->isSuccess());
        self::assertTrue($this->taskService->unmute($taskId)->isSuccess());
        self::assertTrue($this->taskService->addFavorite($taskId)->isSuccess());
        self::assertTrue($this->taskService->removeFavorite($taskId)->isSuccess());
        
        self::assertTrue($this->taskService->complete($taskId)->isSuccess());
        self::assertTrue($this->taskService->renew($taskId)->isSuccess());
        self::assertTrue($this->taskService->approve($taskId)->isSuccess());
        self::assertTrue($this->taskService->disapprove($taskId)->isSuccess());
        
        $this->assertGreaterThanOrEqual(
            1,
            $this->taskService->historyList($taskId)->getCoreResponse()->getResponseData()->getPagination()->getTotal()
        );
        
        $this->taskService->delete($taskId);
    }
    
    protected function getUserId() {
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
}
