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

use Bitrix24\SDK\Core\Exceptions\ValidationException;
use Bitrix24\SDK\Core\Response\DTO\ValidationError;
use Bitrix24\SDK\Services\Task\Service\Task;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationException::class)]
#[CoversClass(ValidationError::class)]
class TaskAddValidationTest extends TestCase
{
    private Task $taskService;

    #[\Override]
    protected function setUp(): void
    {
        $this->taskService = Factory::getServiceBuilder(false)->getTaskScope()->task();
    }

    #[Test]
    #[TestDox('tasks.task.add with invalid responsibleId throws ValidationException with field-level errors')]
    public function testAddWithInvalidResponsibleIdThrowsValidationException(): void
    {
        $exception = null;
        try {
            $this->taskService->add([
                'title' => sprintf('Test task %s', time()),
                'creatorId' => 1,
                'responsibleId' => -1,
            ]);
        } catch (ValidationException $validationException) {
            $exception = $validationException;
        }

        $this->assertInstanceOf(
            ValidationException::class,
            $exception,
            'Expected ValidationException for invalid responsibleId in tasks.task.add'
        );

        $validationErrors = $exception->getValidationErrors();
        $this->assertNotEmpty($validationErrors, 'ValidationException must carry at least one ValidationError');
        $this->assertContainsOnlyInstancesOf(ValidationError::class, $validationErrors);

        foreach ($validationErrors as $validationError) {
            $this->assertNotEmpty($validationError->field, 'ValidationError.field must not be empty');
            $this->assertNotEmpty($validationError->message, 'ValidationError.message must not be empty');
        }

        // The REST API v3 uses dot-notation field paths (e.g. "task.responsible.id"), not camelCase
        $fieldNames = array_map(static fn(ValidationError $validationError): string => $validationError->field, $validationErrors);
        $this->assertContains(
            'task.responsible.id',
            $fieldNames,
            sprintf(
                'Expected validation error for field "task.responsible.id", got: [%s]',
                implode(', ', $fieldNames)
            )
        );
    }
}
