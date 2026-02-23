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
use Bitrix24\SDK\Services\Task\Service\TaskAccess;
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
#[CoversMethod(TaskAccess::class, 'get')]
class TaskAccessTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Task $taskService;

    protected TaskAccess $taskAccessService;

    protected ServiceBuilder $serviceBuilder;

    #[\Override]
    protected function setUp(): void
    {
        $this->taskService = Factory::getServiceBuilder(false)->getTaskScope()->task();
        $this->taskAccessService = Factory::getServiceBuilder(false)->getTaskScope()->taskAccess();
        $this->serviceBuilder = Factory::getServiceBuilder();
    }

    #[TestDox('get access list for task')]
    public function testGetTaskByIdWithAllFields(): void
    {
        $userItemResult = $this->serviceBuilder->getUserScope()->user()->current()->user();
        $taskResult = $this->taskService->add(
            new TaskItemBuilder(
                sprintf('Test task %s', time()),
                $userItemResult->ID,
                $userItemResult->ID
            )
        );

        $accesses = $this->taskAccessService->get($taskResult->task()->id)->getAccesses();

        $this->assertNotEmpty($accesses);
        $this->assertContainsOnlyInstancesOf(
            \Bitrix24\SDK\Services\Task\Result\AccessItemResult::class,
            $accesses
        );
        // current user must be able to read a task they created
        $this->assertTrue($accesses[0]->read);

        $this->taskService->delete($taskResult->task()->id);
    }
}
