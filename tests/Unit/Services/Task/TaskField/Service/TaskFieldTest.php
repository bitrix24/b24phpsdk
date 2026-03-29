<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Task\TaskField\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Task\TaskField\Result\TaskFieldResult;
use Bitrix24\SDK\Services\Task\TaskField\Result\TaskFieldsResult;
use Bitrix24\SDK\Services\Task\TaskField\Service\TaskField;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(TaskField::class)]
class TaskFieldTest extends TestCase
{
    private TaskField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new TaskField(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetReturnsTaskFieldResult(): void
    {
        $this->assertInstanceOf(
            TaskFieldResult::class,
            $this->service->get('id')
        );
    }

    #[Test]
    public function testListReturnsTaskFieldsResult(): void
    {
        $this->assertInstanceOf(
            TaskFieldsResult::class,
            $this->service->list()
        );
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        $this->service->get('');
    }
}
