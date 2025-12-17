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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\Userfield\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Task\Service\Task;
use Bitrix24\SDK\Services\User\Service\User;
use Bitrix24\SDK\Services\Task\Userfield\Service\Userfield;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Task\Userfield\Service\Userfield::class)]
class UserfieldUseCaseTest extends TestCase
{
    protected Task $taskService;

    protected Userfield $userfieldService;

    protected int $userfieldId;
    
    protected User $userService;
    
    /**
     * @throws \Bitrix24\SDK\Services\Task\Userfield\Exceptions\UserfieldNameIsTooLongException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     * @throws \Bitrix24\SDK\Core\Exceptions\InvalidArgumentException
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->taskService = Factory::getServiceBuilder()->getTaskScope()->task();
        $this->userService = Factory::getServiceBuilder()->getUserScope()->user();
        $this->userfieldService = Factory::getServiceBuilder()->getTaskScope()->userfield();

        $this->userfieldId = $this->userfieldService->add(
            [
                'FIELD_NAME'        => sprintf('%s%s', substr((string)random_int(0, PHP_INT_MAX), 0, 3), time()),
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'тест uf тип string',
                    'en' => 'test uf type string',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'тест uf тип string',
                    'en' => 'test uf type string',
                ],
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => 'b24phpsdk_type_string',
                'SETTINGS'          => [
                    'DEFAULT_VALUE' => 'hello world',
                ],
            ]
        )->getId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->userfieldService->delete($this->userfieldId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testOperationsWithUserfieldFromTaskItem(): void
    {
        // get userfield metadata
        $userfieldItemResult = $this->userfieldService->get($this->userfieldId)->userfield();
        $ufOriginalFieldName = $userfieldItemResult->getOriginalFieldName();
        $ufFieldName = $userfieldItemResult->FIELD_NAME;
        
        // add task with uf value
        $fieldNameValue = 'test field value';
        $userId = $this->userService->current()->user()->ID;
        $newTaskId = $this->taskService->add(
            [
                'TITLE' => 'Test userfields',
                'RESPONSIBLE_ID' => $userId,
                $ufFieldName => $fieldNameValue,
            ]
        )->getId();
        $task = $this->taskService->get($newTaskId, ['*', $ufFieldName])->task();
        $taskId = intval($task->id);
        $this->assertEquals($fieldNameValue, $task->getUserfieldByFieldName($ufOriginalFieldName));

        // update task userfield value
        $newUfValue = 'test 2';
        $this->assertTrue(
            $this->taskService->update(
                $taskId,
                [
                    $ufFieldName => $newUfValue,
                ]
            )->isSuccess()
        );
        $taskItemResult = $this->taskService->get($taskId, ['*', $ufFieldName])->task();
        $this->assertEquals($newUfValue, $taskItemResult->getUserfieldByFieldName($ufOriginalFieldName));
        
        $this->taskService->delete($taskId);
    }
    
}
