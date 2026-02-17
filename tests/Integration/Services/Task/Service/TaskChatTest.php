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
use Bitrix24\SDK\Infrastructure\Filesystem\Base64Encoder;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\Task\Result\TaskItemResult;
use Bitrix24\SDK\Services\Task\Service\Task;
use Bitrix24\SDK\Services\Task\Service\TaskChat;
use Bitrix24\SDK\Services\Task\Service\TaskFile;
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
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class TaskTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Task\Service
 */
#[CoversMethod(TaskChat::class, 'sendMessage')]
class TaskChatTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Task $taskService;
    protected TaskChat $taskChatService;

    protected ServiceBuilder $serviceBuilder;

    #[\Override]
    protected function setUp(): void
    {
        $this->taskService = Factory::getServiceBuilder(false)->getTaskScope()->task();
        $this->taskChatService = Factory::getServiceBuilder(false)->getTaskScope()->taskChat();
        $this->serviceBuilder = Factory::getServiceBuilder();
    }

    #[TestDox('Send message to task chat')]
    public function testGetTaskByIdWithAllFields(): void
    {
        $curUser = $this->serviceBuilder->getUserScope()->user()->current()->user();
        $addedTask = $this->taskService->add(
            new TaskItemBuilder(
                sprintf('Test task %s', time()),
                $curUser->ID,
                $curUser->ID
            )
        );

        $this->assertTrue($this->taskChatService->sendMessage($addedTask->task()->id, 'Hello world')->isSuccess());
        $this->taskService->delete($addedTask->task()->id);
    }
}
