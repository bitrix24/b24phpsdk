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

namespace Bitrix24\SDK\Tests\Unit\Services\Task\Service;

use Bitrix24\SDK\Services\Task\Result\TaskItemResult;
use Bitrix24\SDK\Services\Task\Service\TaskItemSelectBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\SelectBuilderAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskItemSelectBuilder::class)]
class TaskItemSelectBuilderTest extends TestCase
{
    #[Test]
    #[TestDox('TaskItemSelectBuilder covers all fields from OpenAPI schema for bitrix.tasks.taskdto')]
    public function testCoversAllOpenApiSchemaFields(): void
    {
        SelectBuilderAssertions::assertCoversOpenApiSchema(
            new TaskItemSelectBuilder(),
            TaskItemResult::class
        );
    }
}
