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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\Service;

use Bitrix24\SDK\Services\Task\Result\TaskFieldItemResult;
use Bitrix24\SDK\Services\Task\Service\TaskField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversMethod(TaskField::class, 'get')]
#[CoversMethod(TaskField::class, 'list')]
class TaskFieldTest extends TestCase
{
    protected TaskField $taskFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->taskFieldService = Factory::getServiceBuilder(false)->getTaskScope()->taskField();
    }

    #[TestDox('Get available task fields metadata')]
    public function testGetTaskFields(): void
    {
        $taskFields = $this->taskFieldService->list()->fields();
//        var_dump($taskFields);


        var_dump($this->taskFieldService->get('userFields')->field());
//        $this->assertNotEmpty($taskFields);
//        $this->assertContainsOnlyInstancesOf(TaskFieldItemResult::class, $taskFields);
//        $this->assertNotNull($taskFields[0]->name);
    }

    #[TestDox('Get task field metadata by field name')]
    public function testGetTaskFieldByName(): void
    {
        $taskFields = $this->taskFieldService->list(['name'])->fields();
        $fieldName = (string)$taskFields[0]->name;

        $taskFieldItemResult = $this->taskFieldService->get($fieldName, ['name', 'type'])->field();

        $this->assertSame($fieldName, $taskFieldItemResult->name);
        $this->assertNotNull($taskFieldItemResult->type);
    }
}
