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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\TaskField\Result;

use Bitrix24\SDK\Services\Task\TaskField\Result\TaskFieldItemResult;
use Bitrix24\SDK\Services\Task\TaskField\Service\TaskField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskFieldItemResult::class)]
class TaskFieldItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private TaskField $taskFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->taskFieldService = Factory::getServiceBuilder()->getTaskScope()->taskField();
    }

    #[Test]
    #[TestDox('all fields in TaskFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $allFields = $this->taskFieldService->get('id')->getCoreResponse()
            ->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($allFields), TaskFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in TaskFieldItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $taskFieldItemResult = $this->taskFieldService->get('id')->taskField();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $taskFieldItemResult,
            TaskFieldItemResult::class
        );
    }
}
